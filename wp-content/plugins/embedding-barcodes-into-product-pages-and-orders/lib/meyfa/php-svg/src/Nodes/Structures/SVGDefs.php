<?php

namespace SVG\Nodes\Structures;

use SVG\Nodes\SVGNodeContainer;
use SVG\Rasterization\SVGRasterizer;

class SVGDefs extends SVGNodeContainer
{
    const TAG_NAME = 'defs';

    public function __construct()
    {
        parent::__construct();
    }

    public function rasterize(SVGRasterizer $rasterizer)
    {
    }
}
