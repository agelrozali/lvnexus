<?php

namespace SVG\Rasterization\Renderers;

use SVG\SVG;
use SVG\Nodes\SVGNode;
use SVG\Rasterization\SVGRasterizer;

class ImageRenderer extends Renderer
{
    protected function prepareRenderParams(SVGRasterizer $rasterizer, array $options)
    {
        return array(
            'href'      => $options['href'],
            'x'         => self::prepareLengthX($options['x'], $rasterizer) + $rasterizer->getOffsetX(),
            'y'         => self::prepareLengthY($options['y'], $rasterizer) + $rasterizer->getOffsetY(),
            'width'     => self::prepareLengthX($options['width'], $rasterizer),
            'height'    => self::prepareLengthY($options['height'], $rasterizer),
        );
    }

    public function render(SVGRasterizer $rasterizer, array $options, SVGNode $context)
    {
        $params = $this->prepareRenderParams($rasterizer, $options);
        $image = $rasterizer->getImage();

        $img = $this->loadImage($params['href'], $params['width'], $params['height']);

        if (!empty($img) && is_resource($img)) {
            imagecopyresampled(
                $image,             $img,               
                $params['x'],       $params['y'],       
                0,                  0,                  
                $params['width'],   $params['height'],  
                imagesx($img),      imagesy($img)       
            );
        }
    }

    protected function renderFill($image, array $params, $color)
    {
    }

    protected function renderStroke($image, array $params, $color, $strokeWidth)
    {
    }

    private function loadImage($href, $w, $h)
    {
        $content = $this->loadImageContent($href);

        if (strpos($content, '<svg') !== false && strrpos($content, '</svg>') !== false) {
            $svg = SVG::fromString($content);
            return $svg->toRasterImage($w, $h);
        }

        return imagecreatefromstring($content);
    }

    private function loadImageContent($href)
    {
        $dataPrefix = 'data:';

        if (substr($href, 0, strlen($dataPrefix)) === $dataPrefix) {
            $commaPos = strpos($href, ',');
            $metadata = substr($href, 0, $commaPos);
            $content  = substr($href, $commaPos + 1);

            if (strpos($metadata, ';base64') !== false) {
                $content = base64_decode($content);
            }

            return $content;
        }

        return file_get_contents($href);
    }
}
