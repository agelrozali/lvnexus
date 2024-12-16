<?php


$vendorDir = dirname(dirname(__FILE__));
$baseDir = dirname($vendorDir);

return array(
    'chillerlan\\Settings\\' => array($vendorDir . '/chillerlan/php-settings-container/src'),
    'chillerlan\\QRCode\\' => array($vendorDir . '/chillerlan/php-qrcode/src'),
    'UkrSolution\\BarcodesDigital\\' => array($baseDir . '/class'),
    'Salla\\ZATCA\\' => array($vendorDir . '/salla/zatca/src'),
    'SVG\\' => array($vendorDir . '/meyfa/php-svg/src'),
    'Milon\\Barcode\\' => array($vendorDir . '/barcode_generator'),
    'Melgrati\\CodeValidator\\' => array($vendorDir . '/barcode_validator'),
    'Kreativekorp\\' => array($vendorDir . '/kreativekorp'),
    'Com\\Tecnick\\Color\\' => array($vendorDir . '/tecnickcom/tc-lib-color', $vendorDir . '/tecnickcom/tc-lib-color/src'),
    'Com\\Tecnick\\Barcode\\' => array($vendorDir . '/tecnickcom/tc-lib-barcode', $vendorDir . '/tecnickcom/tc-lib-barcode/src'),
);
