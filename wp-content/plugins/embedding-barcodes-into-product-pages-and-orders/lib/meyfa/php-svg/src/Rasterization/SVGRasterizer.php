<?php

namespace SVG\Rasterization;

use SVG\Nodes\SVGNode;
use SVG\Utilities\Colors\Color;
use SVG\Utilities\Units\Length;
use \InvalidArgumentException;

class SVGRasterizer
{
    private static $renderers;
    private static $pathParser;
    private static $pathApproximator;

    private $viewBox;
    private $width, $height;
    private $outImage;


    private $docWidth, $docHeight;
    private $scaleX, $scaleY;
    private $offsetX, $offsetY;

    public function __construct($docWidth, $docHeight, $viewBox, $width, $height, $background = null)
    {
        $this->viewBox = empty($viewBox) ? null : $viewBox;

        $this->width = $width;
        $this->height = $height;


        $this->docWidth = Length::convert($docWidth ?: '100%', $width);
        $this->docHeight = Length::convert($docHeight ?: '100%', $height);

        $this->scaleX = $width / (!empty($viewBox) ? $viewBox[2] : $this->docWidth);
        $this->scaleY = $height / (!empty($viewBox) ? $viewBox[3] : $this->docHeight);

        $this->offsetX = !empty($viewBox) ? -($viewBox[0] * $this->scaleX) : 0;
        $this->offsetY = !empty($viewBox) ? -($viewBox[1] * $this->scaleY) : 0;


        $this->outImage = self::createImage($width, $height, $background);

        self::createDependencies();
    }

    private static function createImage($width, $height, $background)
    {
        $img = imagecreatetruecolor($width, $height);

        imagealphablending($img, true);
        imagesavealpha($img, true);

        $bgRgb = 0x7F000000;
        if (!empty($background)) {
            $bgColor = Color::parse($background);

            $alpha = 127 - (int) ($bgColor[3] * 127 / 255);
            $bgRgb = ($alpha << 24) + ($bgColor[0] << 16) + ($bgColor[1] << 8) + ($bgColor[2]);
        }
        imagefill($img, 0, 0, $bgRgb);

        return $img;
    }

    private static function createDependencies()
    {
        if (isset(self::$renderers)) {
            return;
        }

        self::$renderers = array(
            'rect' => new Renderers\RectRenderer(),
            'line' => new Renderers\LineRenderer(),
            'ellipse' => new Renderers\EllipseRenderer(),
            'polygon' => new Renderers\PolygonRenderer(),
            'image' => new Renderers\ImageRenderer(),
            'text' => new Renderers\TextRenderer(),
        );

        self::$pathParser = new Path\PathParser();
        self::$pathApproximator = new Path\PathApproximator();
    }

    private static function getRenderer($id)
    {
        if (!isset(self::$renderers[$id])) {
            throw new InvalidArgumentException("no such renderer: " . $id);
        }
        return self::$renderers[$id];
    }

    public function getPathParser()
    {
        return self::$pathParser;
    }

    public function getPathApproximator()
    {
        return self::$pathApproximator;
    }

    public function render($rendererId, array $params, SVGNode $context)
    {
        $renderer = self::getRenderer($rendererId);
        return $renderer->render($this, $params, $context);
    }

    public function getDocumentWidth()
    {
        return $this->docWidth;
    }

    public function getDocumentHeight()
    {
        return $this->docHeight;
    }

    public function getWidth()
    {
        return $this->width;
    }

    public function getHeight()
    {
        return $this->height;
    }

    public function getScaleX()
    {
        return $this->scaleX;
    }

    public function getScaleY()
    {
        return $this->scaleY;
    }

    public function getOffsetX()
    {
        return $this->offsetX;
    }

    public function getOffsetY()
    {
        return $this->offsetY;
    }

    public function getViewBox()
    {
        return $this->viewBox;
    }

    public function finish()
    {
        return $this->outImage;
    }

    public function getImage()
    {
        return $this->outImage;
    }
}
