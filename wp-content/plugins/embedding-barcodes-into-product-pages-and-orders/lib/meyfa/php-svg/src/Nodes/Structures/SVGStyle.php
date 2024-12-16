<?php

namespace SVG\Nodes\Structures;

use SVG\Nodes\SVGNodeContainer;
use SVG\Rasterization\SVGRasterizer;

class SVGStyle extends SVGNodeContainer
{
    const TAG_NAME = 'style';

    private $css = '';

    public function __construct($css = '', $type = 'text/css')
    {
        parent::__construct();

        $this->css = $css;
        $this->setAttribute('type', $type);
    }

    public static function constructFromAttributes($attr)
    {
        $cdata = trim(preg_replace('/^\s*\/\/<!\[CDATA\[([\s\S]*)\/\/\]\]>\s*\z/', '$1', $attr));

        return new static($cdata);
    }

    public function getType()
    {
        return $this->getAttribute('type');
    }

    public function setType($type)
    {
        return $this->setAttribute('type', $type);
    }

    public function getCss()
    {
        return $this->css;
    }

    public function setCss($css)
    {
        $this->css = $css;

        return $this;
    }

    public function rasterize(SVGRasterizer $rasterizer)
    {
    }
}
