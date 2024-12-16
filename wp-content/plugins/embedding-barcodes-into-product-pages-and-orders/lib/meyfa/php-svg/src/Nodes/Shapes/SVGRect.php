<?php

namespace SVG\Nodes\Shapes;

use SVG\Nodes\SVGNodeContainer;
use SVG\Rasterization\SVGRasterizer;

class SVGRect extends SVGNodeContainer
{
    const TAG_NAME = 'rect';

    public function __construct($x = null, $y = null, $width = null, $height = null)
    {
        parent::__construct();

        $this->setAttribute('x', $x);
        $this->setAttribute('y', $y);
        $this->setAttribute('width', $width);
        $this->setAttribute('height', $height);
    }

    public function getX()
    {
        return $this->getAttribute('x');
    }

    public function setX($x)
    {
        return $this->setAttribute('x', $x);
    }

    public function getY()
    {
        return $this->getAttribute('y');
    }

    public function setY($y)
    {
        return $this->setAttribute('y', $y);
    }

    public function getWidth()
    {
        return $this->getAttribute('width');
    }

    public function setWidth($width)
    {
        return $this->setAttribute('width', $width);
    }

    public function getHeight()
    {
        return $this->getAttribute('height');
    }

    public function setHeight($height)
    {
        return $this->setAttribute('height', $height);
    }

    public function getRX()
    {
        return $this->getAttribute('rx');
    }

    public function setRX($rx)
    {
        return $this->setAttribute('rx', $rx);
    }

    public function getRY()
    {
        return $this->getAttribute('ry');
    }

    public function setRY($ry)
    {
        return $this->setAttribute('ry', $ry);
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

        $rasterizer->render('rect', array(
            'x'         => $this->getX(),
            'y'         => $this->getY(),
            'width'     => $this->getWidth(),
            'height'    => $this->getHeight(),
            'rx'        => $this->getRX(),
            'ry'        => $this->getRY(),
        ), $this);
    }
}
