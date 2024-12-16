<?php

namespace SVG\Nodes\Shapes;

use SVG\Nodes\SVGNodeContainer;
use SVG\Rasterization\SVGRasterizer;

class SVGCircle extends SVGNodeContainer
{
    const TAG_NAME = 'circle';

    public function __construct($cx = null, $cy = null, $r = null)
    {
        parent::__construct();

        $this->setAttribute('cx', $cx);
        $this->setAttribute('cy', $cy);
        $this->setAttribute('r', $r);
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

    public function getRadius()
    {
        return $this->getAttribute('r');
    }

    public function setRadius($r)
    {
        return $this->setAttribute('r', $r);
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

        $r = $this->getRadius();
        $rasterizer->render('ellipse', array(
            'cx'    => $this->getCenterX(),
            'cy'    => $this->getCenterY(),
            'rx'    => $r,
            'ry'    => $r,
        ), $this);
    }
}
