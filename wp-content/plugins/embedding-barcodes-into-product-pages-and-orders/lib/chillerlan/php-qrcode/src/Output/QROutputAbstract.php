<?php

namespace chillerlan\QRCode\Output;

use chillerlan\QRCode\{Data\QRMatrix, QRCode};
use chillerlan\Settings\SettingsContainerInterface;

use function call_user_func_array, dirname, file_put_contents, get_called_class, in_array, is_writable, sprintf;

abstract class QROutputAbstract implements QROutputInterface{

	protected int $moduleCount;

	protected string $outputMode;

	protected string $defaultMode;

	protected int $scale;

	protected int $length;

	protected array $moduleValues;

	protected QRMatrix $matrix;

	protected SettingsContainerInterface $options;

	public function __construct(SettingsContainerInterface $options, QRMatrix $matrix){
		$this->options     = $options;
		$this->matrix      = $matrix;
		$this->moduleCount = $this->matrix->size();
		$this->scale       = $this->options->scale;
		$this->length      = $this->moduleCount * $this->scale;

		$class = get_called_class();

		if(isset(QRCode::OUTPUT_MODES[$class]) && in_array($this->options->outputType, QRCode::OUTPUT_MODES[$class])){
			$this->outputMode = $this->options->outputType;
		}

		$this->setModuleValues();
	}

	abstract protected function setModuleValues():void;

	protected function saveToFile(string $data, string $file):bool{

		if(!is_writable(dirname($file))){
			throw new QRCodeOutputException(sprintf('Could not write data to cache file: %s', $file));
		}

		return (bool)file_put_contents($file, $data);
	}

	public function dump(string $file = null){
		$file ??= $this->options->cachefile;

		$data = call_user_func_array([$this, $this->outputMode ?? $this->defaultMode], [$file]);

		if($file !== null){
			$this->saveToFile($data, $file);
		}

		return $data;
	}

}
