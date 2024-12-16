<?php

namespace SVG\Nodes\Shapes;

use SVG\Rasterization\SVGRasterizer;

class SVGPolyline extends SVGPolygonalShape
{
    const TAG_NAME = 'polyline';

    public function __construct($points = array())
    {
        parent::__construct($points);
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

        $rasterizer->render('polygon', array(
            'open'      => true,
            'points'    => $this->getPoints(),
        ), $this);
    }
}
