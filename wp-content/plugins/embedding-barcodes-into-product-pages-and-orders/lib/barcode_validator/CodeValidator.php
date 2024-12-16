<?php

namespace Melgrati\CodeValidator;

	if (!class_exists('CodeValidator'))
	{
		class CodeValidator
		{
			public function __construct()
			{

			}

			protected static function calculateEANCheckDigit($code)
			{
				preg_match_all('/(\d)(\d){0,1}/', $code, $digits);
				$odd_digits = array_sum($digits[1]);
				$even_digits = array_sum($digits[2]);

				$sum = $odd_digits*3 + $even_digits;
				$modulo = $sum % 10;
				$checkdigit = ($modulo === 0) ? 0 : 10 - $modulo;

				return $checkdigit;
			}

			protected static function validateEANCheckDigit($code, $length)
			{
                $code = str_replace('-','',$code);
				if (strlen($code) == $length)
				{
					if (preg_match('/^[0-9]+$/', $code))
					{
						if (CodeValidator::calculateEANCheckDigit(substr($code, 0, -1)) == substr($code, -1, 1))
						{
							return true;
						}
						else
						{
							return false;
						}
					}
					else 
					{
						return false;
					}
				}
				else 
				{
					return false;
				}
			}
			protected static function validateEANCheckDigit2($code, $length)
			{
				$code = str_replace('-', '', $code);
				if (strlen($code) == $length) {
					if (preg_match('/^[0-9]+$/', $code)) {
						if (CodeValidator::calculateEANCheckDigit(strrev(substr($code, 0, -1))) == substr($code, -1, 1)) {
							return true;
						} else {
							return false;
						}
					} else {
						return false;
					}
				} else {
					return false;
				}
			}
			protected static function sumAllDigits(&$number, $index = 0)
			{
				$number = array_sum(str_split(abs(2 * $number)));
			}

			protected static function calculateLuhnCheckDigit($code)
			{
				preg_match_all('/(\d)(\d){0,1}/', strrev($code), $digits);
				$odd_digits = array_sum($digits[2]);

				array_walk($digits[1], 'CodeValidator::sumAllDigits');
				$even_digits = array_sum($digits[1]);

				$checkdigit = (10 - (($odd_digits + $even_digits) % 10)) % 10;

				return $checkdigit;
			}

			protected static function validateLuhnCheckDigit($code, $length)
			{
				if (strlen($code) == $length)
				{
					if (preg_match('/^[0-9]+$/', $code))
					{
						if (CodeValidator::calculateLuhnCheckDigit(substr($code, 0, -1)) == substr($code, -1, 1))
						{
							return true;
						}
						else
						{
							return false;
						}
					}
					else 
					{
						return false;
					}
				}
				else 
				{
					return false;
				}
			}

			protected static function calculateISBNCheckDigit($code)
			{
				if (strlen($code) == 13)
				{
					$checkdigit = CodeValidator::calculateEANCheckDigit($code);
				}
				else
				{
					$checkdigit = 11 - ((10 * $code[0] + 9 * $code[1] + 8 * $code[2] + 7 * $code[3] + 6 * $code[4] + 5 * $code[5] + 4 * $code[6] + 3 * $code[7] + 2 * $code[8]) %
						11);
					if ($checkdigit == 10)
					{
						$checkdigit = 'X';
					}
				}

				return $checkdigit;
			}

			protected static function validateISBNCheckDigit($code)
			{
                if (strlen($code) == 10 && preg_match('/^[0-9]+$/', $code))
				{
					if (CodeValidator::calculateISBNCheckDigit(substr($code, 0, -1)) == substr($code, -1, 1))
					{
						return true;
					}
					else
					{
						return false;
					}
				}
				else 
				{
					return false;
				}
			}

			public static function IsValidISBN($code = '')
			{
                $code = str_replace('-','',$code);
                if (strlen($code) == 13)
				{
					return CodeValidator::validateEANCheckDigit($code, 13);
				}
				else
				{
					return CodeValidator::validateISBNCheckDigit($code, 10);
				}
			}

			public static function IsValidEAN8($code = '')
			{
                $code = str_replace('-','',$code);

				return CodeValidator::validateEANCheckDigit2($code, 8);
			}

			public static function IsValidEAN13($code = '')
			{
                $code = str_replace('-','',$code);

                return CodeValidator::validateEANCheckDigit2($code, 13);
			}

			public static function IsValidEAN14($code = '')
			{
                $code = str_replace('-','',$code);
                return CodeValidator::validateEANCheckDigit($code, 14);
			}

			public static function IsValidUPCA($code = '')
			{
                $code = str_replace('-','',$code);
                return CodeValidator::validateEANCheckDigit($code, 12);
			}

			public static function IsValidUPCE($code = '')
			{
                $code = str_replace('-','',$code);

				if (!preg_match('/^[0-9]+$/', $code))
				{
					return false;
				}

				$len = strlen($code);
				if ($len == 6) 
				{
					return true;
				}

				switch (strlen($code))
				{
					case 7: 
						$upc_e_code = substr($code, 0, 6);
						$check_code = $code[6];
						break;
					case 8: 
						if ($code[0] != 0)
						{
							return false;
						}
						$upc_e_code = substr($code, 1, 6);
						$check_code = $code[7];
						break;
					default: 
						return false;
						break;
				}
				switch ($upc_e_code[5])
				{
					case "0":
						$ManufacturerNumber = ($upc_e_code[0] . $upc_e_code[1] . $upc_e_code[5] . "00");
						$ItemNumber = ("00" . $upc_e_code[2] . $upc_e_code[3] . $upc_e_code[4]);
						break;

					case "1":
						$ManufacturerNumber = ($upc_e_code[0] . $upc_e_code[1] . $upc_e_code[5] . "00");
						$ItemNumber = "00" . $upc_e_code[2] . $upc_e_code[3] . $upc_e_code[4];
						break;

					case "2":
						$ManufacturerNumber = $upc_e_code[0] . $upc_e_code[1] . $upc_e_code[5] . "00";
						$ItemNumber = "00" . $upc_e_code[2] . $upc_e_code[3] . $upc_e_code[4];
						break;

					case "3":
						$ManufacturerNumber = $upc_e_code[0] . $upc_e_code[1] . $upc_e_code[2] . "00";
						$ItemNumber = "000" . $upc_e_code[3] . $upc_e_code[4];
						break;

					case "4":
						$ManufacturerNumber = $upc_e_code[0] . $upc_e_code[1] . $upc_e_code[2] . $upc_e_code[3] . "0";
						$ItemNumber = "0000" . $upc_e_code[4];
						break;

					default:
						$ManufacturerNumber = ($upc_e_code[0] . $upc_e_code[1] . $upc_e_code[2] . $upc_e_code[3] . $upc_e_code[4]);
						$ItemNumber = ("0000" . $upc_e_code[5]);
						break;

				} 

				return CodeValidator::validateEANCheckDigit("0" . $ManufacturerNumber . $ItemNumber . $check_code, 12);
			}

			public static function IsValidGSIN($code = '')
			{
                $code = str_replace('-','',$code);
                return CodeValidator::validateEANCheckDigit($code, 17);
			}

			public static function IsValidSSCC($code = '')
			{
                $code = str_replace('-','',$code);
                return CodeValidator::validateEANCheckDigit($code, 18);
			}

			public static function IsValidGLN($code = '')
			{
                $code = str_replace('-','',$code);
                return CodeValidator::validateEANCheckDigit($code, 13);
			}

			public static function IsValidIMEI($code = '')
			{
                $code = str_replace('-','',$code);
                if (preg_match('/^[0-9]+$/', $code))
				{
					switch (strlen($code))
					{
						case 14: 
							return true;
							break;
						case 15: 
							return CodeValidator::validateLuhnCheckDigit($code, 15);
							break;
						case 16: 
							return true;
							break;
					}

				}
			}
		}

	}

?>
