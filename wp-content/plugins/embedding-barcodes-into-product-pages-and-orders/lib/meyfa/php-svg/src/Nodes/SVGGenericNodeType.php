<?php

namespace SVG\Nodes;

use SVG\Nodes\SVGNodeContainer;
use SVG\Rasterization\SVGRasterizer;

class SVGGenericNodeType extends SVGNodeContainer
{
    private $tagName;

    public function __construct($tagName)
    {
        parent::__construct();
        $this->tagName = $tagName;
    }

    public function getName()
    {
        return $this->tagName;
    }

    public function rasterize(SVGRasterizer $rasterizer)
    {
    }
}
