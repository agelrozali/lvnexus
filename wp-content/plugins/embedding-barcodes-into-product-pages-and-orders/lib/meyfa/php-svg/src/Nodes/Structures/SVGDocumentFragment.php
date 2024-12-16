<?php

namespace SVG\Nodes\Structures;

use SVG\Nodes\SVGNodeContainer;
use SVG\Rasterization\SVGRasterizer;
use SVG\Utilities\Units\Length;

class SVGDocumentFragment extends SVGNodeContainer
{
    const TAG_NAME = 'svg';

    private static $initialStyles = array(
        'fill' => '#000000',
        'stroke' => 'none',
        'stroke-width' => '1',
        'opacity' => '1',
        'x' => '0',
        'y' => '0',
    );

    private $namespaces;

    public function __construct($width = null, $height = null, array $namespaces = array())
    {
        parent::__construct();

        $this->namespaces = $namespaces;

        $this->setAttribute('width', $width);
        $this->setAttribute('height', $height);
    }

    public function isRoot()
    {
        return $this->getParent() === null;
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

    public function getComputedStyle($name)
    {
        $style = parent::getComputedStyle($name);

        if (isset($style) || !isset(self::$initialStyles[$name])) {
            return $style;
        }

        return self::$initialStyles[$name];
    }

    public function rasterize(SVGRasterizer $rasterizer)
    {
        if ($this->isRoot()) {
            parent::rasterize($rasterizer);
            return;
        }

        $subRasterizer = new SVGRasterizer(
            $this->getWidth(), 
            $this->getHeight(), 
            $this->getViewBox(), 
            Length::convert($this->getWidth() ?: '100%', $rasterizer->getWidth()),
            Length::convert($this->getHeight() ?: '100%', $rasterizer->getHeight())
        );

        parent::rasterize($subRasterizer);
        $img = $subRasterizer->finish();

        imagecopy(
            $rasterizer->getImage(), 
            $img, 
            0, 0, 
            0, 0, 
            $subRasterizer->getWidth(), 
            $subRasterizer->getHeight() 
        );
        imagedestroy($img);
    }

    public function getSerializableAttributes()
    {
        $attrs = parent::getSerializableAttributes();

        if ($this->isRoot()) {
            $attrs['xmlns'] = 'http://www.w3.org/2000/svg';
            $attrs['xmlns:xlink'] = 'http://www.w3.org/1999/xlink';
            foreach ($this->namespaces as $namespace => $uri) {
                $namespace = $this->serializeNamespace($namespace);
                $attrs[$namespace] = $uri;
            }
        }

        if (isset($attrs['width']) && $attrs['width'] === '100%') {
            unset($attrs['width']);
        }
        if (isset($attrs['height']) && $attrs['height'] === '100%') {
            unset($attrs['height']);
        }

        return $attrs;
    }

    private function serializeNamespace($namespace)
    {
        if ($namespace === '' || $namespace === 'xmlns') {
            return 'xmlns';
        }
        if (substr($namespace, 0, 6) !== 'xmlns:') {
            return 'xmlns:' . $namespace;
        }
        return $namespace;
    }

    public function getElementById($id)
    {
        $stack = array($this);

        while (!empty($stack)) {
            $elem = array_pop($stack);
            if ($elem->getAttribute('id') === $id) {
                return $elem;
            }
            if ($elem instanceof SVGNodeContainer) {
                for ($i = $elem->countChildren() - 1; $i >= 0; --$i) {
                    array_push($stack, $elem->getChild($i));
                }
            }
        }

        return null;
    }
}
