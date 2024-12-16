<?php

namespace SVG\Utilities\Units;

final class Length
{
    public static function convert($unit, $viewLength)
    {
        $regex = '/^([+-]?\d*\.?\d*)(px|pt|pc|cm|mm|in|%)?$/';
        if (!preg_match($regex, $unit, $matches) || $matches[1] === '') {
            return null;
        }

        $factors = array(
            'px' => (1),                    
            'pt' => (16 / 12),              
            'pc' => (16),                   
            'in' => (96),                   
            'cm' => (96 / 2.54),            
            'mm' => (96 / 25.4),            
            '%'  => ($viewLength / 100),    
        );

        $value = (float) $matches[1];
        $unit  = empty($matches[2]) ? 'px' : $matches[2];

        return $value * $factors[$unit];
    }
}
