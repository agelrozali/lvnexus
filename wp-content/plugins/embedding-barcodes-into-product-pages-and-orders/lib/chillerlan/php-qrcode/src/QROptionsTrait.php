<?php

namespace chillerlan\QRCode;

use function array_values, count, in_array, is_numeric, max, min, sprintf, strtolower;

trait QROptionsTrait{

	protected int $version = QRCode::VERSION_AUTO;

	protected int $versionMin = 1;

	protected int $versionMax = 40;

	protected int $eccLevel = QRCode::ECC_L;

	protected int $maskPattern = QRCode::MASK_PATTERN_AUTO;

	protected bool $addQuietzone = true;

	protected int $quietzoneSize = 4;

	protected string $dataModeOverride = '';

	protected string $outputType = QRCode::OUTPUT_IMAGE_PNG;

	protected ?string $outputInterface = null;

	protected ?string $cachefile = null;

	protected string $eol = PHP_EOL;

	protected int $scale = 5;

	protected string $cssClass = '';

	protected float $svgOpacity = 1.0;

	protected string $svgDefs = '<style>rect{shape-rendering:crispEdges}</style>';

	protected ?int $svgViewBoxSize = null;

	protected string $textDark = '🔴';

	protected string $textLight = '⭕';

	protected string $markupDark = '#000';

	protected string $markupLight = '#fff';

	protected bool $returnResource = false;

	protected bool $imageBase64 = true;

	protected bool $imageTransparent = true;

	protected array $imageTransparencyBG = [255, 255, 255];

	protected int $pngCompression = -1;

	protected int $jpegQuality = 85;

	protected string $imagickFormat = 'png';

	protected ?string $imagickBG = null;

	protected string $fpdfMeasureUnit = 'pt';

	protected ?array $moduleValues = null;

	protected function setMinMaxVersion(int $versionMin, int $versionMax):void{
		$min = max(1, min(40, $versionMin));
		$max = max(1, min(40, $versionMax));

		$this->versionMin = min($min, $max);
		$this->versionMax = max($min, $max);
	}

	protected function set_versionMin(int $version):void{
		$this->setMinMaxVersion($version, $this->versionMax);
	}

	protected function set_versionMax(int $version):void{
		$this->setMinMaxVersion($this->versionMin, $version);
	}

	protected function set_eccLevel(int $eccLevel):void{

		if(!isset(QRCode::ECC_MODES[$eccLevel])){
			throw new QRCodeException(sprintf('Invalid error correct level: %s', $eccLevel));
		}

		$this->eccLevel = $eccLevel;
	}

	protected function set_maskPattern(int $maskPattern):void{

		if($maskPattern !== QRCode::MASK_PATTERN_AUTO){
			$this->maskPattern = max(0, min(7, $maskPattern));
		}

	}

	protected function set_imageTransparencyBG(array $imageTransparencyBG):void{

		if(count($imageTransparencyBG) < 3){
			$this->imageTransparencyBG = [255, 255, 255];

			return;
		}

		foreach($imageTransparencyBG as $k => $v){

			if($k > 2){
				break;
			}

			if(!is_numeric($v)){
				throw new QRCodeException('Invalid RGB value.');
			}

			$this->imageTransparencyBG[$k] = max(0, min(255, (int)$v));
		}

		$this->imageTransparencyBG = array_values($this->imageTransparencyBG);
	}

	protected function set_version(int $version):void{

		if($version !== QRCode::VERSION_AUTO){
			$this->version = max(1, min(40, $version));
		}

	}

	protected function set_fpdfMeasureUnit(string $unit):void{
		$unit = strtolower($unit);

		if(in_array($unit, ['cm', 'in', 'mm', 'pt'], true)){
			$this->fpdfMeasureUnit = $unit;
		}

	}

}
