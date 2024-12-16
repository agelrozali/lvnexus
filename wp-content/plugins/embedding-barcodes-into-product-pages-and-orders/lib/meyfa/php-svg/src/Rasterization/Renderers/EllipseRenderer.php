<?php

namespace SVG\Rasterization\Renderers;

use SVG\Rasterization\SVGRasterizer;

class EllipseRenderer extends Renderer
{
    protected function prepareRenderParams(SVGRasterizer $rasterizer, array $options)
    {
        return array(
            'cx'        => self::prepareLengthX($options['cx'], $rasterizer) + $rasterizer->getOffsetX(),
            'cy'        => self::prepareLengthY($options['cy'], $rasterizer) + $rasterizer->getOffsetY(),
            'width'     => self::prepareLengthX($options['rx'], $rasterizer) * 2,
            'height'    => self::prepareLengthY($options['ry'], $rasterizer) * 2,
        );
    }

    protected function renderFill($image, array $params, $color)
    {
        imagefilledellipse($image, $params['cx'], $params['cy'], $params['width'], $params['height'], $color);
    }

    protected function renderStroke($image, array $params, $color, $strokeWidth)
    {
        imagesetthickness($image, $strokeWidth);

        $width = $params['width'];
        if ($width % 2 === 0) {
            $width += 1;
        }
        $height = $params['height'];
        if ($height % 2 === 0) {
            $height += 1;
        }

        imagearc($image, $params['cx'], $params['cy'], $width, $height, 0, 360, $color);
    }
}
