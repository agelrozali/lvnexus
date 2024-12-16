<?php

namespace chillerlan\QRCode\Data;

use function abs, array_search, call_user_func_array, min;

final class MaskPatternTester{

	protected QRDataInterface $dataInterface;

	public function __construct(QRDataInterface $dataInterface){
		$this->dataInterface = $dataInterface;
	}

	public function getBestMaskPattern():int{
		$penalties = [];

		for($pattern = 0; $pattern < 8; $pattern++){
			$penalties[$pattern] = $this->testPattern($pattern);
		}

		return array_search(min($penalties), $penalties, true);
	}

	public function testPattern(int $pattern):int{
		$matrix  = $this->dataInterface->initMatrix($pattern, true);
		$penalty = 0;

		for($level = 1; $level <= 4; $level++){
			$penalty += call_user_func_array([$this, 'testLevel'.$level], [$matrix->matrix(true), $matrix->size()]);
		}

		return (int)$penalty;
	}

	protected function testLevel1(array $m, int $size):int{
		$penalty = 0;

		foreach($m as $y => $row){
			foreach($row as $x => $val){
				$count = 0;

				for($ry = -1; $ry <= 1; $ry++){

					if($y + $ry < 0 || $size <= $y + $ry){
						continue;
					}

					for($rx = -1; $rx <= 1; $rx++){

						if(($ry === 0 && $rx === 0) || (($x + $rx) < 0 || $size <= ($x + $rx))){
							continue;
						}

						if($m[$y + $ry][$x + $rx] === $val){
							$count++;
						}

					}
				}

				if($count > 5){
					$penalty += (3 + $count - 5);
				}

			}
		}

		return $penalty;
	}

	protected function testLevel2(array $m, int $size):int{
		$penalty = 0;

		foreach($m as $y => $row){

			if($y > $size - 2){
				break;
			}

			foreach($row as $x => $val){

				if($x > $size - 2){
					break;
				}

				if(
					   $val === $m[$y][$x + 1]
					&& $val === $m[$y + 1][$x]
					&& $val === $m[$y + 1][$x + 1]
				){
					$penalty++;
				}
			}
		}

		return 3 * $penalty;
	}

	protected function testLevel3(array $m, int $size):int{
		$penalties = 0;

		foreach($m as $y => $row){
			foreach($row as $x => $val){

				if(
					$x + 6 < $size
					&&  $val
					&& !$m[$y][$x + 1]
					&&  $m[$y][$x + 2]
					&&  $m[$y][$x + 3]
					&&  $m[$y][$x + 4]
					&& !$m[$y][$x + 5]
					&&  $m[$y][$x + 6]
				){
					$penalties++;
				}

				if(
					$y + 6 < $size
					&&  $val
					&& !$m[$y + 1][$x]
					&&  $m[$y + 2][$x]
					&&  $m[$y + 3][$x]
					&&  $m[$y + 4][$x]
					&& !$m[$y + 5][$x]
					&&  $m[$y + 6][$x]
				){
					$penalties++;
				}

			}
		}

		return $penalties * 40;
	}

	protected function testLevel4(array $m, int $size):float{
		$count = 0;

		foreach($m as $y => $row){
			foreach($row as $x => $val){
				if($val){
					$count++;
				}
			}
		}

		return (abs(100 * $count / $size / $size - 50) / 5) * 10;
	}

}
