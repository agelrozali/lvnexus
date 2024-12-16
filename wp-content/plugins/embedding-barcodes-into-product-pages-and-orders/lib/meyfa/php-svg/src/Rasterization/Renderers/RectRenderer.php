<?php

namespace SVG\Rasterization\Renderers;

use SVG\Rasterization\SVGRasterizer;

class RectRenderer extends Renderer
{
    protected function prepareRenderParams(SVGRasterizer $rasterizer, array $options)
    {
        $w  = self::prepareLengthX($options['width'], $rasterizer);
        $h  = self::prepareLengthY($options['height'], $rasterizer);

        if ($w <= 0 || $h <= 0) {
            return array('empty' => true);
        }

        $x1 = self::prepareLengthX($options['x'], $rasterizer) + $rasterizer->getOffsetX();
        $y1 = self::prepareLengthY($options['y'], $rasterizer) + $rasterizer->getOffsetY();

        $rx = empty($options['rx']) ? 0 : self::prepareLengthX($options['rx'], $rasterizer);
        if ($rx > ($w - 1) / 2) {
            $rx = floor(($w - 1) / 2);
        }
        if ($rx < 0) {
            $rx = 0;
        }
        $ry = empty($options['ry']) ? 0 : self::prepareLengthY($options['ry'], $rasterizer);
        if ($ry > ($h - 1) / 2) {
            $ry = floor(($h - 1) / 2);
        }
        if ($ry < 0) {
            $ry = 0;
        }

        return array(
            'empty' => false,
            'x1' => $x1,
            'y1' => $y1,
            'x2' => $x1 + $w - 1,
            'y2' => $y1 + $h - 1,
            'rx' => $rx,
            'ry' => $ry,
        );
    }

    protected function renderFill($image, array $params, $color)
    {
        if ($params['empty']) {
            return;
        }

        if ($params['rx'] !== 0 || $params['ry'] !== 0) {
            $this->renderFillRounded($image, $params, $color);
            return;
        }

        imagefilledrectangle(
            $image,
            $params['x1'], $params['y1'],
            $params['x2'], $params['y2'],
            $color
        );
    }

    private function renderFillRounded($image, array $params, $color)
    {
        $x1 = $params['x1'];
        $y1 = $params['y1'];
        $x2 = $params['x2'];
        $y2 = $params['y2'];
        $rx = $params['rx'];
        $ry = $params['ry'];


        imagefilledrectangle($image, $x1 + $rx, $y1, $x2 - $rx, $y2, $color);
        imagefilledrectangle($image, $x1, $y1 + $ry, $x1 + $rx - 1, $y2 - $ry, $color);
        imagefilledrectangle($image, $x2 - $rx + 1, $y1 + $ry, $x2, $y2 - $ry, $color);


        $corners = imagecreatetruecolor($rx * 2 + 1, $ry * 2 + 1);
        imagealphablending($corners, true);
        imagesavealpha($corners, true);
        imagefill($corners, 0, 0, 0x7F000000);

        imagefilledellipse($corners, $rx, $ry, $rx * 2, $ry * 2, $color);

        imagecopy($image, $corners, $x1, $y1, 0, 0, $rx, $ry);
        imagecopy($image, $corners, $x2 - $rx + 1, $y1, $rx + 1, 0, $rx, $ry);
        imagecopy($image, $corners, $x1, $y2 - $ry + 1, 0, $ry + 1, $rx, $ry);
        imagecopy($image, $corners, $x2 - $rx + 1, $y2 - $ry + 1, $rx + 1, $ry + 1, $rx, $ry);

        imagedestroy($corners);
    }

    protected function renderStroke($image, array $params, $color, $strokeWidth)
    {
        if ($params['empty']) {
            return;
        }

        imagesetthickness($image, $strokeWidth);

        if ($params['rx'] !== 0 || $params['ry'] !== 0) {
            $this->renderStrokeRounded($image, $params, $color, $strokeWidth);
            return;
        }

        $x1 = $params['x1'];
        $y1 = $params['y1'];
        $x2 = $params['x2'];
        $y2 = $params['y2'];


        $halfStrokeFloor = floor($strokeWidth / 2);
        $halfStrokeCeil  = ceil($strokeWidth / 2);

        imagefilledrectangle(
            $image,
            $x1 - $halfStrokeFloor,     $y1 - $halfStrokeFloor,
            $x2 + $halfStrokeFloor,     $y1 + $halfStrokeCeil - 1,
            $color
        );
        imagefilledrectangle(
            $image,
            $x1 - $halfStrokeFloor,     $y2 - $halfStrokeCeil + 1,
            $x2 + $halfStrokeFloor,     $y2 + $halfStrokeFloor,
            $color
        );
        imagefilledrectangle(
            $image,
            $x1 - $halfStrokeFloor,     $y1 + $halfStrokeCeil,
            $x1 + $halfStrokeCeil - 1,  $y2 - $halfStrokeCeil,
            $color
        );
        imagefilledrectangle(
            $image,
            $x2 - $halfStrokeCeil + 1,  $y1 + $halfStrokeCeil,
            $x2 + $halfStrokeFloor,     $y2 - $halfStrokeCeil,
            $color
        );
    }

    private function renderStrokeRounded($image, array $params, $color, $strokeWidth)
    {
        $x1 = $params['x1'];
        $y1 = $params['y1'];
        $x2 = $params['x2'];
        $y2 = $params['y2'];
        $rx = $params['rx'];
        $ry = $params['ry'];

        $halfStrokeFloor = floor($strokeWidth / 2);
        $halfStrokeCeil  = ceil($strokeWidth / 2);

        imagefilledrectangle(
            $image,
            $x1 + $rx + 1,  $y1 - $halfStrokeFloor,
            $x2 - $rx - 1,  $y1 + $halfStrokeCeil - 1,
            $color
        );
        imagefilledrectangle(
            $image,
            $x1 + $rx + 1,  $y2 - $halfStrokeCeil + 1,
            $x2 - $rx - 1,  $y2 + $halfStrokeFloor,
            $color
        );
        imagefilledrectangle(
            $image,
            $x1 - $halfStrokeFloor,     $y1 + $ry + 1,
            $x1 + $halfStrokeCeil - 1,  $y2 - $ry - 1,
            $color
        );
        imagefilledrectangle(
            $image,
            $x2 - $halfStrokeCeil + 1,  $y1 + $ry + 1,
            $x2 + $halfStrokeFloor,     $y2 - $ry - 1,
            $color
        );

        imagesetthickness($image, 1);

        for ($sw = -$halfStrokeFloor; $sw < $halfStrokeCeil; ++$sw) {
            $arcW = $rx * 2 + 1 + $sw * 2;
            $arcH = $ry * 2 + 1 + $sw * 2;
            imagearc($image, $x1 + $rx, $y1 + $ry, $arcW, $arcH, 180, 270, $color);
            imagearc($image, $x2 - $rx, $y1 + $ry, $arcW, $arcH, 270, 360, $color);
            imagearc($image, $x1 + $rx, $y2 - $ry, $arcW, $arcH, 90, 180, $color);
            imagearc($image, $x2 - $rx, $y2 - $ry, $arcW, $arcH, 0, 90, $color);
        }
    }
}
