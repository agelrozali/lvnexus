<?php

namespace SVG\Rasterization\Path;

class PathParser
{
    private static $commandLengths = array(
        'M' => 2,   'm' => 2,   
        'L' => 2,   'l' => 2,   
        'H' => 1,   'h' => 1,   
        'V' => 1,   'v' => 1,   
        'C' => 6,   'c' => 6,   
        'S' => 4,   's' => 4,   
        'Q' => 4,   'q' => 4,   
        'T' => 2,   't' => 2,   
        'A' => 7,   'a' => 7,   
        'Z' => 0,   'z' => 0,   
    );

    public function parse($description)
    {
        $commands = array();

        $matches  = array();
        $idString = implode('', array_keys(self::$commandLengths));
        preg_match_all('/(['.$idString.'])([^'.$idString.']*)/', $description, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $id   = $match[1];
            $args = $this->splitArguments($match[2]);

            $success = $this->parseCommandChain($id, $args, $commands);
            if (!$success) {
                break;
            }
        }

        return $commands;
    }

    private function splitArguments($str)
    {
        $str = trim($str);

        $args = array();
        if (!empty($str)) {
            preg_match_all('/[+-]?(\d*\.\d+|\d+)(e[+-]?\d+)?/', $str, $args);
            $args = $args[0];
        }

        return $args;
    }

    private function parseCommandChain($id, array $args, array &$commands)
    {
        if (!isset(self::$commandLengths[$id])) {
            return false;
        }

        $length = self::$commandLengths[$id];

        if ($length === 0) {
            if (count($args) > 0) {
                return false;
            }
            $commands[] = array(
                'id'    => $id,
                'args'  => $args,
            );
            return true;
        }

        foreach (array_chunk($args, $length) as $subArgs) {
            if (count($subArgs) !== $length) {
                return false;
            }
            $commands[] = array(
                'id'    => $id,
                'args'  => array_map('floatval', $subArgs),
            );
            if ($id === 'M') {
                $id = 'L';
            } elseif ($id === 'm') {
                $id = 'l';
            }
        }

        return true;
    }
}
