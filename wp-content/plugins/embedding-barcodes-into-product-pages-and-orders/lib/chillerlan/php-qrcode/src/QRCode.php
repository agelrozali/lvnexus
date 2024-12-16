<?php

namespace chillerlan\QRCode;

use chillerlan\QRCode\Data\{
	AlphaNum, Byte, Kanji, MaskPatternTester, Number, QRCodeDataException, QRDataInterface, QRMatrix
};
use chillerlan\QRCode\Output\{
	QRCodeOutputException, QRFpdf, QRImage, QRImagick, QRMarkup, QROutputInterface, QRString
};
use chillerlan\Settings\SettingsContainerInterface;

use function call_user_func_array, class_exists, in_array, ord, strlen, strtolower, str_split;

class QRCode{

	public const VERSION_AUTO       = -1;
	public const MASK_PATTERN_AUTO  = -1;


	public const DATA_NUMBER   = 0b0001;
	public const DATA_ALPHANUM = 0b0010;
	public const DATA_BYTE     = 0b0100;
	public const DATA_KANJI    = 0b1000;

	public const DATA_MODES = [
		self::DATA_NUMBER   => 0,
		self::DATA_ALPHANUM => 1,
		self::DATA_BYTE     => 2,
		self::DATA_KANJI    => 3,
	];


	public const ECC_L = 0b01; 
	public const ECC_M = 0b00; 
	public const ECC_Q = 0b11; 
	public const ECC_H = 0b10; 

	public const ECC_MODES = [
		self::ECC_L => 0,
		self::ECC_M => 1,
		self::ECC_Q => 2,
		self::ECC_H => 3,
	];

	public const OUTPUT_MARKUP_HTML = 'html';
	public const OUTPUT_MARKUP_SVG  = 'svg';
	public const OUTPUT_IMAGE_PNG   = 'png';
	public const OUTPUT_IMAGE_JPG   = 'jpg';
	public const OUTPUT_IMAGE_GIF   = 'gif';
	public const OUTPUT_STRING_JSON = 'json';
	public const OUTPUT_STRING_TEXT = 'text';
	public const OUTPUT_IMAGICK     = 'imagick';
	public const OUTPUT_FPDF        = 'fpdf';
	public const OUTPUT_CUSTOM      = 'custom';

	public const OUTPUT_MODES = [
		QRMarkup::class => [
			self::OUTPUT_MARKUP_SVG,
			self::OUTPUT_MARKUP_HTML,
		],
		QRImage::class => [
			self::OUTPUT_IMAGE_PNG,
			self::OUTPUT_IMAGE_GIF,
			self::OUTPUT_IMAGE_JPG,
		],
		QRString::class => [
			self::OUTPUT_STRING_JSON,
			self::OUTPUT_STRING_TEXT,
		],
		QRImagick::class => [
			self::OUTPUT_IMAGICK,
		],
		QRFpdf::class => [
			self::OUTPUT_FPDF
		]
	];

	protected const DATA_INTERFACES = [
		'number'   => Number::class,
		'alphanum' => AlphaNum::class,
		'kanji'    => Kanji::class,
		'byte'     => Byte::class,
	];

	protected SettingsContainerInterface $options;

	protected QRDataInterface $dataInterface;

	public function __construct(SettingsContainerInterface $options = null){
		$this->options = $options ?? new QROptions;
	}

	public function render(string $data, string $file = null){
		return $this->initOutputInterface($data)->dump($file);
	}

	public function getMatrix(string $data):QRMatrix{

		if(empty($data)){
			throw new QRCodeDataException('QRCode::getMatrix() No data given.');
		}

		$this->dataInterface = $this->initDataInterface($data);

		$maskPattern = $this->options->maskPattern === $this::MASK_PATTERN_AUTO
			? (new MaskPatternTester($this->dataInterface))->getBestMaskPattern()
			: $this->options->maskPattern;

		$matrix = $this->dataInterface->initMatrix($maskPattern);

		if((bool)$this->options->addQuietzone){
			$matrix->setQuietZone($this->options->quietzoneSize);
		}

		return $matrix;
	}

	public function initDataInterface(string $data):QRDataInterface{

		$interface = $this::DATA_INTERFACES[strtolower($this->options->dataModeOverride)] ?? null;

		if($interface !== null){
			return new $interface($this->options, $data);
		}

		foreach($this::DATA_INTERFACES as $mode => $dataInterface){

			if(call_user_func_array([$this, 'is'.$mode], [$data])){
				return new $dataInterface($this->options, $data);
			}

		}

		throw new QRCodeDataException('invalid data type'); 
	}

	protected function initOutputInterface(string $data):QROutputInterface{

		if($this->options->outputType === $this::OUTPUT_CUSTOM && class_exists($this->options->outputInterface)){
			return new $this->options->outputInterface($this->options, $this->getMatrix($data));
		}

		foreach($this::OUTPUT_MODES as $outputInterface => $modes){

			if(in_array($this->options->outputType, $modes, true) && class_exists($outputInterface)){
				return new $outputInterface($this->options, $this->getMatrix($data));
			}

		}

		throw new QRCodeOutputException('invalid output type');
	}

	public function isNumber(string $string):bool{
		return $this->checkString($string, QRDataInterface::CHAR_MAP_NUMBER);
	}

	public function isAlphaNum(string $string):bool{
		return $this->checkString($string, QRDataInterface::CHAR_MAP_ALPHANUM);
	}

	protected function checkString(string $string, array $charmap):bool{

		foreach(str_split($string) as $chr){
			if(!isset($charmap[$chr])){
				return false;
			}
		}

		return true;
	}

	public function isKanji(string $string):bool{
		$i   = 0;
		$len = strlen($string);

		while($i + 1 < $len){
			$c = ((0xff & ord($string[$i])) << 8) | (0xff & ord($string[$i + 1]));

			if(!($c >= 0x8140 && $c <= 0x9FFC) && !($c >= 0xE040 && $c <= 0xEBBF)){
				return false;
			}

			$i += 2;
		}

		return $i >= $len;
	}

	public function isByte(string $data):bool{
		return !empty($data);
	}

}
