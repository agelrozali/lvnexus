<?php

namespace SVG\Rasterization\Renderers;

use SVG\Nodes\SVGNode;
use SVG\Rasterization\SVGRasterizer;
use SVG\Utilities\Units\Length;
use SVG\Utilities\Colors\Color;

abstract class Renderer
{
    public function render(SVGRasterizer $rasterizer, array $options, SVGNode $context)
    {
        $params = $this->prepareRenderParams($rasterizer, $options);

        $paintOrder = $this->getPaintOrder($context);
        foreach ($paintOrder as $paint) {
            if ($paint === 'fill') {
                $this->paintFill($rasterizer, $context, $params);
            } elseif ($paint === 'stroke') {
                $this->paintStroke($rasterizer, $context, $params);
            }
        }
    }

    private function paintStroke(SVGRasterizer $rasterizer, SVGNode $context, $params)
    {
        $stroke = $context->getComputedStyle('stroke');
        if (isset($stroke) && $stroke !== 'none') {
            $stroke      = self::prepareColor($stroke, $context);
            $strokeWidth = $context->getComputedStyle('stroke-width');
            $strokeWidth = self::prepareLengthX($strokeWidth, $rasterizer);

            $this->renderStroke($rasterizer->getImage(), $params, $stroke, $strokeWidth);
        }
    }

    private function paintFill(SVGRasterizer $rasterizer, SVGNode $context, $params)
    {
        $fill = $context->getComputedStyle('fill');
        if (isset($fill) && $fill !== 'none') {
            $fill = self::prepareColor($fill, $context);

            $this->renderFill($rasterizer->getImage(), $params, $fill);
        }
    }

    private function getPaintOrder(SVGNode $context)
    {
        $paintOrder = $context->getComputedStyle('paint-order');
        $paintOrder = preg_replace('#\s{2,}#', ' ', trim($paintOrder));

        $defaultOrder = array('fill', 'stroke', 'markers');

        if ($paintOrder === 'normal' || empty($paintOrder)) {
            return $defaultOrder;
        }

        $paintOrder = array_intersect(explode(' ', $paintOrder), $defaultOrder);
        $paintOrder = array_merge($paintOrder, array_diff($defaultOrder, $paintOrder));

        return $paintOrder;
    }

    private static function prepareColor($color, SVGNode $context)
    {
        $color = Color::parse($color);
        $rgb   = ($color[0] << 16) + ($color[1] << 8) + ($color[2]);

        $opacity = self::calculateTotalOpacity($context);
        $a = 127 - $opacity * (int) ($color[3] * 127 / 255);

        return $rgb | ($a << 24);
    }

    private static function getNodeOpacity(SVGNode $node)
    {
        $opacity = $node->getStyle('opacity');

        if (is_numeric($opacity)) {
            return (float) $opacity;
        } elseif ($opacity === 'inherit') {
            $parent = $node->getParent();
            if (isset($parent)) {
                return self::getNodeOpacity($parent);
            }
        }

        return 1;
    }

    private static function calculateTotalOpacity(SVGNode $node)
    {
        $opacity = self::getNodeOpacity($node);

        $parent  = $node->getParent();
        if (isset($parent)) {
            return $opacity * self::calculateTotalOpacity($parent);
        }

        return $opacity;
    }

    protected static function prepareLengthX($len, SVGRasterizer $ras)
    {
        $doc   = $ras->getDocumentWidth();
        $scale = $ras->getScaleX();

        return Length::convert($len, $doc) * $scale;
    }

    protected static function prepareLengthY($len, SVGRasterizer $ras)
    {
        $doc   = $ras->getDocumentWidth();
        $scale = $ras->getScaleY();

        return Length::convert($len, $doc) * $scale;
    }

    abstract protected function prepareRenderParams(SVGRasterizer $rasterizer, array $options);

    abstract protected function renderFill($image, array $params, $color);

    abstract protected function renderStroke($image, array $params, $color, $strokeWidth);
}
