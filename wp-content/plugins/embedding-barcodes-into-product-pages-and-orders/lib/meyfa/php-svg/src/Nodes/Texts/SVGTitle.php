<?php
namespace SVG\Nodes\Texts;

use SVG\Nodes\SVGNodeContainer;
use SVG\Rasterization\SVGRasterizer;

class SVGTitle extends SVGNodeContainer
{
    const TAG_NAME = 'title';

    public function __construct($text = '')
    {
        parent::__construct();
        $this->setValue($text);
    }

    public function rasterize(SVGRasterizer $rasterizer)
    {
    }
}
