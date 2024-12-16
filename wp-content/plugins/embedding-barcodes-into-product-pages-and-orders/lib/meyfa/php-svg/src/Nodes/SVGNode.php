<?php

namespace SVG\Nodes;

use SVG\Rasterization\SVGRasterizer;

abstract class SVGNode
{
    protected $parent;
    protected $styles;
    protected $attributes;
    protected $value;

    public function __construct()
    {
        $this->styles     = array();
        $this->attributes = array();
        $this->value      = '';
    }

    public static function constructFromAttributes($attrs)
    {
        return new static();
    }

    public function getName()
    {
        return static::TAG_NAME;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function getValue()
    {
        return isset($this->value) ? $this->value : '';
    }

    public function setValue($value)
    {
        if (!isset($value)) {
            unset($this->value);
            return $this;
        }
        $this->value = (string) $value;
        return $this;
    }

    public function getStyle($name)
    {
        return isset($this->styles[$name]) ? $this->styles[$name] : null;
    }

    public function setStyle($name, $value)
    {
        $value = (string) $value;
        if (strlen($value) === 0) {
            unset($this->styles[$name]);
            return $this;
        }
        $this->styles[$name] = $value;
        return $this;
    }

    public function removeStyle($name)
    {
        unset($this->styles[$name]);
        return $this;
    }

    public function getComputedStyle($name)
    {
        $style = $this->getStyle($name);

        if ($style === null && isset($this->parent)) {
            $containerStyles = $this->parent->getContainerStyleForNode($this);
            $style = isset($containerStyles[$name]) ? $containerStyles[$name] : null;
        }

        if (($style === null || $style === 'inherit') && isset($this->parent)) {
            return $this->parent->getComputedStyle($name);
        }

        return $style !== 'inherit' ? $style : null;
    }

    public function getAttribute($name)
    {
        return isset($this->attributes[$name]) ? $this->attributes[$name] : null;
    }

    public function setAttribute($name, $value)
    {
        if (!isset($value)) {
            unset($this->attributes[$name]);
            return $this;
        }
        $this->attributes[$name] = (string) $value;
        return $this;
    }

    public function removeAttribute($name)
    {
        unset($this->attributes[$name]);
        return $this;
    }

    public function getSerializableAttributes()
    {
        return $this->attributes;
    }

    public function getSerializableStyles()
    {
        return $this->styles;
    }

    public function getIdAndClassPattern()
    {
        $id = $this->getAttribute('id');
        $class = $this->getAttribute('class');

        $pattern = '';
        if (!empty($id)) {
            $pattern = '#'.$id.'|#'.$id;
        }
        if (!empty($class)) {
            if (!empty($pattern)) {
                $pattern .= '.'.$class.'|';
            }
            $pattern .= '.'.$class;
        }

        return empty($pattern) ? null : '/('.$pattern.')/';
    }

    public function getViewBox()
    {
        $attr = $this->getAttribute('viewBox');
        if (empty($attr)) {
            return null;
        }

        $result = preg_split('/[\s,]+/', $attr);
        if (count($result) !== 4) {
            return null;
        }

        return array_map('floatval', $result);
    }

    abstract public function rasterize(SVGRasterizer $rasterizer);

    public function getElementsByTagName($tagName, array &$result = array())
    {
        return $result;
    }

    public function getElementsByClassName($className, array &$result = array())
    {
        return $result;
    }
}
