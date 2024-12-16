<?php

namespace SVG\Utilities\Units;

final class Angle
{
    public static function convert($unit)
    {
        $regex = '/^([+-]?\d*\.?\d*)(deg|rad|grad|turn)?$/';
        if (!preg_match($regex, $unit, $matches) || $matches[1] === '') {
            return null;
        }

        $factors = array(
            'deg'  => (1),          
            'rad'  => (180 / M_PI), 
            'grad' => (9 / 10),     
            'turn' => (360),        
        );

        $value = (float) $matches[1];
        $unit  = empty($matches[2]) ? 'deg' : $matches[2];

        return $value * $factors[$unit];
    }
}
