<?php

namespace SVG\Nodes\Shapes;

use SVG\Nodes\SVGNodeContainer;
use SVG\Rasterization\SVGRasterizer;

class SVGEllipse extends SVGNodeContainer
{
    const TAG_NAME = 'ellipse';

    public function __construct($cx = null, $cy = null, $rx = null, $ry = null)
    {
        parent::__construct();

        $this->setAttribute('cx', $cx);
        $this->setAttribute('cy', $cy);
        $this->setAttribute('rx', $rx);
        $this->setAttribute('ry', $ry);
    }

    public function getCenterX()
    {
        return $this->getAttribute('cx');
    }

    public function setCenterX($cx)
    {
        return $this->setAttribute('cx', $cx);
    }

    public function getCenterY()
    {
        return $this->getAttribute('cy');
    }

    public function setCenterY($cy)
    {
        return $this->setAttribute('cy', $cy);
    }

    public function getRadiusX()
    {
        return $this->getAttribute('rx');
    }

    public function setRadiusX($rx)
    {
        return $this->setAttribute('rx', $rx);
    }

    public function getRadiusY()
    {
        return $this->getAttribute('ry');
    }

    public function setRadiusY($ry)
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

        $rasterizer->render('ellipse', array(
            'cx'    => $this->getCenterX(),
            'cy'    => $this->getCenterY(),
            'rx'    => $this->getRadiusX(),
            'ry'    => $this->getRadiusY(),
        ), $this);
    }
}
