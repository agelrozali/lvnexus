<?php

namespace chillerlan\QRCode\Data;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\Helpers\{BitBuffer, Polynomial};
use chillerlan\Settings\SettingsContainerInterface;

use function array_fill, array_merge, count, max, mb_convert_encoding, mb_detect_encoding, range, sprintf, strlen;

abstract class QRDataAbstract implements QRDataInterface{

	protected ?int $strlen = null;

	protected int $datamode;

	protected array $lengthBits = [0, 0, 0];

	protected int $version;

	protected array $ecdata;

	protected array $dcdata;

	protected SettingsContainerInterface $options;

	protected BitBuffer $bitBuffer;

	public function __construct(SettingsContainerInterface $options, string $data = null){
		$this->options = $options;

		if($data !== null){
			$this->setData($data);
		}
	}

	public function setData(string $data):QRDataInterface{

		if($this->datamode === QRCode::DATA_KANJI){
			$data = mb_convert_encoding($data, 'SJIS', mb_detect_encoding($data));
		}

		$this->strlen  = $this->getLength($data);
		$this->version = $this->options->version === QRCode::VERSION_AUTO
			? $this->getMinimumVersion()
			: $this->options->version;

		$this->writeBitBuffer($data);

		return $this;
	}

	public function initMatrix(int $maskPattern, bool $test = null):QRMatrix{
		return (new QRMatrix($this->version, $this->options->eccLevel))
			->init($maskPattern, $test)
			->mapData($this->maskECC(), $maskPattern)
		;
	}

	protected function getLengthBits():int{

		 foreach([9, 26, 40] as $key => $breakpoint){
			 if($this->version <= $breakpoint){
				 return $this->lengthBits[$key];
			 }
		 }

		throw new QRCodeDataException(sprintf('invalid version number: %d', $this->version));
	}

	protected function getLength(string $data):int{
		return strlen($data);
	}

	protected function getMinimumVersion():int{
		$maxlength = 0;

		$dataMode = QRCode::DATA_MODES[$this->datamode];
		$eccMode  = QRCode::ECC_MODES[$this->options->eccLevel];

		foreach(range($this->options->versionMin, $this->options->versionMax) as $version){
			$maxlength = $this::MAX_LENGTH[$version][$dataMode][$eccMode];

			if($this->strlen <= $maxlength){
				return $version;
			}
		}

		throw new QRCodeDataException(sprintf('data exceeds %d characters', $maxlength));
	}

	abstract protected function write(string $data):void;

	protected function writeBitBuffer(string $data):void{
		$this->bitBuffer = new BitBuffer;

		$MAX_BITS = $this::MAX_BITS[$this->version][QRCode::ECC_MODES[$this->options->eccLevel]];

		$this->bitBuffer
			->put($this->datamode, 4)
			->put($this->strlen, $this->getLengthBits())
		;

		$this->write($data);

		if($this->bitBuffer->getLength() > $MAX_BITS){
			throw new QRCodeDataException(sprintf('code length overflow. (%d > %d bit)', $this->bitBuffer->getLength(), $MAX_BITS));
		}

		if($this->bitBuffer->getLength() + 4 <= $MAX_BITS){
			$this->bitBuffer->put(0, 4);
		}

		while($this->bitBuffer->getLength() % 8 !== 0){
			$this->bitBuffer->putBit(false);
		}

		while(true){

			if($this->bitBuffer->getLength() >= $MAX_BITS){
				break;
			}

			$this->bitBuffer->put(0xEC, 8);

			if($this->bitBuffer->getLength() >= $MAX_BITS){
				break;
			}

			$this->bitBuffer->put(0x11, 8);
		}

	}

	protected function maskECC():array{
		[$l1, $l2, $b1, $b2] = $this::RSBLOCKS[$this->version][QRCode::ECC_MODES[$this->options->eccLevel]];

		$rsBlocks     = array_fill(0, $l1, [$b1, $b2]);
		$rsCount      = $l1 + $l2;
		$this->ecdata = array_fill(0, $rsCount, []);
		$this->dcdata = $this->ecdata;

		if($l2 > 0){
			$rsBlocks = array_merge($rsBlocks, array_fill(0, $l2, [$b1 + 1, $b2 + 1]));
		}

		$totalCodeCount = 0;
		$maxDcCount     = 0;
		$maxEcCount     = 0;
		$offset         = 0;

		$bitBuffer = $this->bitBuffer->getBuffer();

		foreach($rsBlocks as $key => $block){
			[$rsBlockTotal, $dcCount] = $block;

			$ecCount            = $rsBlockTotal - $dcCount;
			$maxDcCount         = max($maxDcCount, $dcCount);
			$maxEcCount         = max($maxEcCount, $ecCount);
			$this->dcdata[$key] = array_fill(0, $dcCount, null);

			foreach($this->dcdata[$key] as $a => $_z){
				$this->dcdata[$key][$a] = 0xff & $bitBuffer[$a + $offset];
			}

			[$num, $add] = $this->poly($key, $ecCount);

			foreach($this->ecdata[$key] as $c => $_){
				$modIndex               = $c + $add;
				$this->ecdata[$key][$c] = $modIndex >= 0 ? $num[$modIndex] : 0;
			}

			$offset         += $dcCount;
			$totalCodeCount += $rsBlockTotal;
		}

		$data  = array_fill(0, $totalCodeCount, null);
		$index = 0;

		$mask = function(array $arr, int $count) use (&$data, &$index, $rsCount):void{
			for($x = 0; $x < $count; $x++){
				for($y = 0; $y < $rsCount; $y++){
					if($x < count($arr[$y])){
						$data[$index] = $arr[$y][$x];
						$index++;
					}
				}
			}
		};

		$mask($this->dcdata, $maxDcCount);
		$mask($this->ecdata, $maxEcCount);

		return $data;
	}

	protected function poly(int $key, int $count):array{
		$rsPoly  = new Polynomial;
		$modPoly = new Polynomial;

		for($i = 0; $i < $count; $i++){
			$modPoly->setNum([1, $modPoly->gexp($i)]);
			$rsPoly->multiply($modPoly->getNum());
		}

		$rsPolyCount = count($rsPoly->getNum());

		$modPoly
			->setNum($this->dcdata[$key], $rsPolyCount - 1)
			->mod($rsPoly->getNum())
		;

		$this->ecdata[$key] = array_fill(0, $rsPolyCount - 1, null);
		$num                = $modPoly->getNum();

		return [
			$num,
			count($num) - count($this->ecdata[$key]),
		];
	}

}
