<?php

namespace SVG\Nodes\Embedded;

use \RuntimeException;

use SVG\Nodes\SVGNodeContainer;
use SVG\Rasterization\SVGRasterizer;

class SVGImage extends SVGNodeContainer
{
    const TAG_NAME = 'image';

    public function __construct($href = null, $x = null, $y = null, $width = null, $height = null)
    {
        parent::__construct();

        $this->setAttribute('xlink:href', $href);
        $this->setAttribute('x', $x);
        $this->setAttribute('y', $y);
        $this->setAttribute('width', $width);
        $this->setAttribute('height', $height);
    }

    public static function fromFile($path, $mimeType, $x = null, $y = null, $width = null, $height = null)
    {
        $imageContent = file_get_contents($path);
        if ($imageContent === false) {
            throw new RuntimeException('Image file "' . $path . '" could not be read.');
        }

        return self::fromString(
            $imageContent,
            $mimeType,
            $x,
            $y,
            $width,
            $height
        );
    }

    public static function fromString(
        $imageContent,
        $mimeType,
        $x = null,
        $y = null,
        $width = null,
        $height = null
    ) {
        return new self(
            sprintf(
                'data:%s;base64,%s',
                $mimeType,
                base64_encode($imageContent)
            ),
            $x,
            $y,
            $width,
            $height
        );
    }

    public function getHref()
    {
        return $this->getAttribute('xlink:href') ?: $this->getAttribute('href');
    }

    public function setHref($href)
    {
        return $this->setAttribute('xlink:href', $href);
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

    public function rasterize(SVGRasterizer $rasterizer)
    {
        if ($this->getComputedStyle('display') === 'none') {
            return;
        }

        $visibility = $this->getComputedStyle('visibility');
        if ($visibility === 'hidden' || $visibility === 'collapse') {
            return;
        }

        $rasterizer->render('image', array(
            'href'      => $this->getHref(),
            'x'         => $this->getX(),
            'y'         => $this->getY(),
            'width'     => $this->getWidth(),
            'height'    => $this->getHeight(),
        ), $this);
    }
}
