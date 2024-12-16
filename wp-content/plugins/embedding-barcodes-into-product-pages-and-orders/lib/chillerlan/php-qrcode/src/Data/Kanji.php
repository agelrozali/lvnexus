<?php

namespace chillerlan\QRCode\Data;

use chillerlan\QRCode\QRCode;

use function mb_strlen, ord, sprintf, strlen;

final class Kanji extends QRDataAbstract{

	protected int $datamode = QRCode::DATA_KANJI;

	protected array $lengthBits = [8, 10, 12];

	protected function getLength(string $data):int{
		return mb_strlen($data, 'SJIS');
	}

	protected function write(string $data):void{
		$len = strlen($data);

		for($i = 0; $i + 1 < $len; $i += 2){
			$c = ((0xff & ord($data[$i])) << 8) | (0xff & ord($data[$i + 1]));

			if($c >= 0x8140 && $c <= 0x9FFC){
				$c -= 0x8140;
			}
			elseif($c >= 0xE040 && $c <= 0xEBBF){
				$c -= 0xC140;
			}
			else{
				throw new QRCodeDataException(sprintf('illegal char at %d [%d]', $i + 1, $c));
			}

			$this->bitBuffer->put(((($c >> 8) & 0xff) * 0xC0) + ($c & 0xff), 13);

		}

		if($i < $len){
			throw new QRCodeDataException(sprintf('illegal char at %d', $i + 1));
		}

	}

}
