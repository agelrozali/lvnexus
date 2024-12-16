<?php
namespace SVG\Nodes\Texts;

use SVG\Nodes\Structures\SVGFont;
use SVG\Nodes\SVGNodeContainer;
use SVG\Rasterization\SVGRasterizer;

class SVGText extends SVGNodeContainer
{
    const TAG_NAME = 'text';

    private $font;

    public function __construct($text = '', $x = 0, $y = 0)
    {
        parent::__construct();
        $this->setValue($text);

        if ($this->font === null) {
            $font = dirname(__FILE__) . "/../../../../../assets/fonts/arial.ttf";
            $SVGFont = new SVGFont('Open Sans', $font);
            $this->setFont($SVGFont);
        }

        $this->setAttribute('x', $x);
        $this->setAttribute('y', $y);
    }

    public function setFont(SVGFont $font)
    {
        $this->font = $font;
        $this->setStyle('font-family', $font->getFontName());
        return $this;
    }

    public function setSize($size)
    {
        $this->setStyle('font-size', $size);
        return $this;
    }

    public function getComputedStyle($name)
    {
        if ($name === 'paint-order') {
            return 'stroke fill';
        }

        return parent::getComputedStyle($name);
    }

    public function rasterize(SVGRasterizer $rasterizer)
    {
        if (empty($this->font)) {
            return;
        }

        $rasterizer->render('text', array(
            'x' => $this->getAttribute('x'),
            'y' => $this->getAttribute('y'),
            'size' => $this->getComputedStyle('font-size'),
            'text' => $this->getValue(),
            'font_path' => $this->font->getFontPath(),
        ), $this);
    }
}
