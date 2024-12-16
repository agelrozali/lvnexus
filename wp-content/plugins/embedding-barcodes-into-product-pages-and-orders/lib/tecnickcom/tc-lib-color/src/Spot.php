<?php

namespace Com\Tecnick\Color;

use \Com\Tecnick\Color\Exception as ColorException;
use \Com\Tecnick\Color\Model\Cmyk;

class Spot extends \Com\Tecnick\Color\Web
{
    protected static $default_spot_colors = array (
        'none' => array('name' => 'None',
            'color' => array('cyan' => 0, 'magenta' => 0, 'yellow' => 0, 'key' => 0, 'alpha' => 1)),
        'all' => array('name' => 'All',
            'color' => array('cyan' => 1, 'magenta' => 1, 'yellow' => 1, 'key' => 1, 'alpha' => 1)),
        'cyan' => array('name' => 'Cyan',
            'color' => array('cyan' => 1, 'magenta' => 0, 'yellow' => 0, 'key' => 0, 'alpha' => 1)),
        'magenta' => array('name' => 'Magenta',
            'color' => array('cyan' => 0, 'magenta' => 1, 'yellow' => 0, 'key' => 0, 'alpha' => 1)),
        'yellow' => array('name' => 'Yellow',
            'color' => array('cyan' => 0, 'magenta' => 0, 'yellow' => 1, 'key' => 0, 'alpha' => 1)),
        'key' => array('name' => 'Key',
            'color' => array('cyan' => 0, 'magenta' => 0, 'yellow' => 0, 'key' => 1, 'alpha' => 1)),
        'white' => array('name' => 'White',
            'color' => array('cyan' => 0, 'magenta' => 0, 'yellow' => 0, 'key' => 0, 'alpha' => 1)),
        'black' => array('name' => 'Black',
            'color' => array('cyan' => 0, 'magenta' => 0, 'yellow' => 0, 'key' => 1, 'alpha' => 1)),
        'red' => array('name' => 'Red',
            'color' => array('cyan' => 0, 'magenta' => 1, 'yellow' => 1, 'key' => 0, 'alpha' => 1)),
        'green' => array('name' => 'Green',
            'color' => array('cyan' => 1, 'magenta' => 0, 'yellow' => 1, 'key' => 0, 'alpha' => 1)),
        'blue' => array('name' => 'Blue',
            'color' => array('cyan' => 1, 'magenta' => 1, 'yellow' => 0, 'key' => 0, 'alpha' => 1)),
    );

    protected $spot_colors = array();

    public function getSpotColors()
    {
        return $this->spot_colors;
    }

    public function normalizeSpotColorName($name)
    {
        return preg_replace('/[^a-z0-9]*/', '', strtolower($name));
    }

    public function getSpotColor($name)
    {
        $key = $this->normalizeSpotColorName($name);
        if (empty($this->spot_colors[$key])) {
            if (empty(self::$default_spot_colors[$key])) {
                throw new ColorException('unable to find the spot color: '.$key);
            }
            $this->addSpotColor($key, new Cmyk(self::$default_spot_colors[$key]['color']));
        }
        return $this->spot_colors[$key];
    }

    public function getSpotColorObj($name)
    {
        $spot = $this->getSpotColor($name);
        return $spot['color'];
    }

    public function addSpotColor($name, Cmyk $color)
    {
        $key = $this->normalizeSpotColorName($name);
        if (isset($this->spot_colors[$key])) {
            $num = $this->spot_colors[$key]['i'];
        } else {
            $num = (count($this->spot_colors) + 1);
        }
        $this->spot_colors[$key] = array(
            'i'     => $num,   
            'n'     => 0,      
            'name'  => $name,  
            'color' => $color, 
        );
    }

    public function getPdfSpotObjects(&$pon)
    {
        $out = '';
        foreach ($this->spot_colors as $name => $color) {
            $out .= (++$pon).' 0 obj'."\n";
            $this->spot_colors[$name]['n'] = $pon;
            $out .= '[/Separation /'.str_replace(' ', '#20', $name)
                .' /DeviceCMYK <<'
                .'/Range [0 1 0 1 0 1 0 1]'
                .' /C0 [0 0 0 0]'
                .' /C1 ['.$color['color']->getComponentsString().']'
                .' /FunctionType 2'
                .' /Domain [0 1]'
                .' /N 1'
                .'>>]'."\n"
                .'endobj'."\n";
        }
        return $out;
    }

    public function getPdfSpotResources()
    {
        if (empty($this->spot_colors)) {
            return '';
        }
        $out = '/ColorSpace << ';
        foreach ($this->spot_colors as $color) {
            $out .= '/CS'.$color['i'].' '.$color['n'].' 0 R ';
        }
        $out .= '>>'."\n";
        return $out;
    }
}
