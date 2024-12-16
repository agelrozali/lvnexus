<?php

namespace Com\Tecnick\Barcode;

use \Com\Tecnick\Barcode\Exception as BarcodeException;

class Barcode
{
    protected static $typeclass = array(
        'C128A'      => 'Linear\\CodeOneTwoEight\\CodeOneTwoEightA',  
        'C128B'      => 'Linear\\CodeOneTwoEight\\CodeOneTwoEightB', 
        'C128C'      => 'Linear\\CodeOneTwoEight\\CodeOneTwoEightC', 
        'C128'       => 'Linear\\CodeOneTwoEight',            
        'C39E+'      => 'Linear\\CodeThreeNineExtCheck',      
        'C39E'       => 'Linear\\CodeThreeNineExt',           
        'C39+'       => 'Linear\\CodeThreeNineCheck',         
        'C39'        => 'Linear\\CodeThreeNine',              
        'C93'        => 'Linear\\CodeNineThree',              
        'CODABAR'    => 'Linear\\Codabar',                    
        'CODE11'     => 'Linear\\CodeOneOne',                 
        'EAN13'      => 'Linear\\EanOneThree',                
        'EAN2'       => 'Linear\\EanTwo',                     
        'EAN5'       => 'Linear\\EanFive',                    
        'EAN8'       => 'Linear\\EanEight',                   
        'I25+'       => 'Linear\\InterleavedTwoOfFiveCheck',  
        'I25'        => 'Linear\\InterleavedTwoOfFive',       
        'IMB'        => 'Linear\\Imb',                        
        'IMBPRE'     => 'Linear\\ImbPre',                     
        'KIX'        => 'Linear\\KlantIndex',                 
        'MSI+'       => 'Linear\\MsiCheck',                   
        'MSI'        => 'Linear\\Msi',                        
        'PHARMA2T'   => 'Linear\\PharmaTwoTracks',            
        'PHARMA'     => 'Linear\\Pharma',                     
        'PLANET'     => 'Linear\\Planet',                     
        'POSTNET'    => 'Linear\\Postnet',                    
        'RMS4CC'     => 'Linear\\RoyalMailFourCc',            
        'S25+'       => 'Linear\\StandardTwoOfFiveCheck',     
        'S25'        => 'Linear\\StandardTwoOfFive',          
        'UPCA'       => 'Linear\\UpcA',                       
        'UPCE'       => 'Linear\\UpcE',                       
        'DATAMATRIX' => 'Square\\Datamatrix',                 
        'PDF417'     => 'Square\\PdfFourOneSeven',            
        'QRCODE'     => 'Square\\QrCode',                     
        'LRAW'       => 'Linear\\Raw',                        
        'SRAW'       => 'Square\\Raw',                        
    );

    public function getTypes()
    {
        return array_keys(self::$typeclass);
    }

    public function getBarcodeObj(
        $type,
        $code,
        $width = -1,
        $height = -1,
        $color = 'black',
        $padding = array(0, 0, 0, 0)
    ) {
        $params = explode(',', $type);
        $type = array_shift($params);

                if (empty(self::$typeclass[$type])) {
            throw new BarcodeException('Unsupported barcode type: '.$type);
        }
        $bclass = '\\Com\\Tecnick\\Barcode\\Type\\'.self::$typeclass[$type];
        return new $bclass($code, $width, $height, $color, $params, $padding);
    }
}
