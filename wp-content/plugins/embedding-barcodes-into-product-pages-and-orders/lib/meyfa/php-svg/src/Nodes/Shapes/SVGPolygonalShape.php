<?php

namespace SVG\Nodes\Shapes;

use SVG\Nodes\SVGNodeContainer;

abstract class SVGPolygonalShape extends SVGNodeContainer
{
    private $points;

    public function __construct($points)
    {
        parent::__construct();

        $this->points = $points;
    }

    public static function constructFromAttributes($attrs)
    {
        $points = array();

        if (isset($attrs['points'])) {
            $coords = preg_split('/[\s,]+/', trim($attrs['points']));
            for ($i = 0, $n = count($coords); $i < $n; $i += 2) {
                $points[] = array(
                    (float) $coords[$i],
                    (float) $coords[$i + 1],
                );
            }
        }

        return new static($points);
    }

    public function addPoint($a, $b = null)
    {
        if (!is_array($a)) {
            $a = array($a, $b);
        }

        $this->points[] = $a;
        return $this;
    }

    public function removePoint($index)
    {
        array_splice($this->points, $index, 1);
        return $this;
    }

    public function countPoints()
    {
        return count($this->points);
    }

    public function getPoints()
    {
        return $this->points;
    }

    public function getPoint($index)
    {
        return $this->points[$index];
    }

    public function setPoint($index, $point)
    {
        $this->points[$index] = $point;
        return $this;
    }

    public function getSerializableAttributes()
    {
        $attrs = parent::getSerializableAttributes();

        $points = '';
        for ($i = 0, $n = count($this->points); $i < $n; ++$i) {
            $point = $this->points[$i];
            if ($i > 0) {
                $points .= ' ';
            }
            $points .= $point[0].','.$point[1];
        }
        $attrs['points'] = $points;

        return $attrs;
    }
}
