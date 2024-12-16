<?php

namespace SVG\Nodes\Structures;

use SVG\Nodes\SVGNodeContainer;
use SVG\Rasterization\SVGRasterizer;

class SVGClipPath extends SVGNodeContainer
{
    const TAG_NAME = 'clipPath';

    public function __construct($id)
    {
        parent::__construct();

        $this->setAttribute('id', $id);
    }

    public function rasterize(SVGRasterizer $rasterizer)
    {
    }
}
