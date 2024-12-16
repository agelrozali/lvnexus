<?php

namespace chillerlan\QRCode\Output;

use chillerlan\QRCode\Data\QRMatrix;

interface QROutputInterface{

	const DEFAULT_MODULE_VALUES = [
		QRMatrix::M_NULL            => false, 
		QRMatrix::M_DATA            => false, 
		QRMatrix::M_FINDER          => false, 
		QRMatrix::M_SEPARATOR       => false, 
		QRMatrix::M_ALIGNMENT       => false, 
		QRMatrix::M_TIMING          => false, 
		QRMatrix::M_FORMAT          => false, 
		QRMatrix::M_VERSION         => false, 
		QRMatrix::M_QUIETZONE       => false, 
		QRMatrix::M_LOGO            => false, 
		QRMatrix::M_TEST            => false, 
		QRMatrix::M_DARKMODULE << 8 => true,  
		QRMatrix::M_DATA << 8       => true,  
		QRMatrix::M_FINDER << 8     => true,  
		QRMatrix::M_ALIGNMENT << 8  => true,  
		QRMatrix::M_TIMING << 8     => true,  
		QRMatrix::M_FORMAT << 8     => true,  
		QRMatrix::M_VERSION << 8    => true,  
		QRMatrix::M_FINDER_DOT << 8 => true,  
		QRMatrix::M_TEST << 8       => true,  
	];

	public function dump(string $file = null);

}
