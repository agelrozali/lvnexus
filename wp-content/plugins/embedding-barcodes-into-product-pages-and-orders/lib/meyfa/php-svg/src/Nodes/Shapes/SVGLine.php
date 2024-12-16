<?php

namespace SVG\Nodes\Shapes;

use SVG\Nodes\SVGNodeContainer;
use SVG\Rasterization\SVGRasterizer;

class SVGLine extends SVGNodeContainer
{
    const TAG_NAME = 'line';

    public function __construct($x1 = null, $y1 = null, $x2 = null, $y2 = null)
    {
        parent::__construct();

        $this->setAttribute('x1', $x1);
        $this->setAttribute('y1', $y1);
        $this->setAttribute('x2', $x2);
        $this->setAttribute('y2', $y2);
    }

    public function getX1()
    {
        return $this->getAttribute('x1');
    }

    public function setX1($x1)
    {
        return $this->setAttribute('x1', $x1);
    }

    public function getY1()
    {
        return $this->getAttribute('y1');
    }

    public function setY1($y1)
    {
        return $this->setAttribute('y1', $y1);
    }

    public function getX2()
    {
        return $this->getAttribute('x2');
    }

    public function setX2($x2)
    {
        return $this->setAttribute('x2', $x2);
    }

    public function getY2()
    {
        return $this->getAttribute('y2');
    }

    public function setY2($y2)
    {
        return $this->setAttribute('y2', $y2);
    }

    public function rasterize(SVGRasterizer $rasterizer)
    {
        if ($this->getComputedStyle('display') === 'none') {
            return;
        }

        $visibility = $this->getComputedStyle('visibility');
        if ($visibility === 'hidden' || $visibility === 'collapse') {
            return;
        }

        $rasterizer->render('line', array(
            'x1'    => $this->getX1(),
            'y1'    => $this->getY1(),
            'x2'    => $this->getX2(),
            'y2'    => $this->getY2(),
        ), $this);
    }
}
