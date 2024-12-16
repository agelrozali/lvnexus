<?php

namespace chillerlan\QRCode\Output;

use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\QRCodeException;
use chillerlan\Settings\SettingsContainerInterface;
use Imagick, ImagickDraw, ImagickPixel;

use function extension_loaded, is_string;

class QRImagick extends QROutputAbstract{

	protected Imagick $imagick;

	public function __construct(SettingsContainerInterface $options, QRMatrix $matrix){

		if(!extension_loaded('imagick')){
			throw new QRCodeException('ext-imagick not loaded'); 
		}

		parent::__construct($options, $matrix);
	}

	protected function setModuleValues():void{

		foreach($this::DEFAULT_MODULE_VALUES as $type => $defaultValue){
			$v = $this->options->moduleValues[$type] ?? null;

			if(!is_string($v)){
				$this->moduleValues[$type] = $defaultValue
					? new ImagickPixel($this->options->markupDark)
					: new ImagickPixel($this->options->markupLight);
			}
			else{
				$this->moduleValues[$type] = new ImagickPixel($v);
			}
		}
	}

	public function dump(string $file = null){
		$file ??= $this->options->cachefile;
		$this->imagick = new Imagick;

		$this->imagick->newImage(
			$this->length,
			$this->length,
			new ImagickPixel($this->options->imagickBG ?? 'transparent'),
			$this->options->imagickFormat
		);

		$this->drawImage();

		if($this->options->returnResource){
			return $this->imagick;
		}

		$imageData = $this->imagick->getImageBlob();

		if($file !== null){
			$this->saveToFile($imageData, $file);
		}

		return $imageData;
	}

	protected function drawImage():void{
		$draw = new ImagickDraw;

		foreach($this->matrix->matrix() as $y => $row){
			foreach($row as $x => $M_TYPE){
				$draw->setStrokeColor($this->moduleValues[$M_TYPE]);
				$draw->setFillColor($this->moduleValues[$M_TYPE]);
				$draw->rectangle(
					$x * $this->scale,
					$y * $this->scale,
					($x + 1) * $this->scale,
					($y + 1) * $this->scale
				);

			}
		}

		$this->imagick->drawImage($draw);
	}

}
