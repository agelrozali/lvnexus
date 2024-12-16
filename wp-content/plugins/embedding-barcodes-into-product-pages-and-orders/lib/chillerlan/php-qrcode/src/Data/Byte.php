<?php

namespace chillerlan\QRCode\Data;

use chillerlan\QRCode\QRCode;

use function ord;

final class Byte extends QRDataAbstract{

	protected int $datamode = QRCode::DATA_BYTE;

	protected array $lengthBits = [8, 16, 16];

	protected function write(string $data):void{
		$i = 0;

		while($i < $this->strlen){
			$this->bitBuffer->put(ord($data[$i]), 8);
			$i++;
		}

	}

}
