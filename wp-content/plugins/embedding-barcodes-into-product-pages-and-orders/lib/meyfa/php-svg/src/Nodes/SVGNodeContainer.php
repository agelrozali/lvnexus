<?php

namespace SVG\Nodes;

use SVG\Nodes\Structures\SVGStyle;
use SVG\Rasterization\SVGRasterizer;
use SVG\Utilities\SVGStyleParser;

abstract class SVGNodeContainer extends SVGNode
{
    protected $children;

    protected $containerStyles;

    public function __construct()
    {
        parent::__construct();

        $this->containerStyles = array();
        $this->children = array();
    }

    public function addChild(SVGNode $node, $index = null)
    {
        if ($node === $this || $node->parent === $this) {
            return $this;
        }

        if (isset($node->parent)) {
            $node->parent->removeChild($node);
        }

        $index = ($index !== null) ? $index : count($this->children);

        array_splice($this->children, $index, 0, array($node));
        $node->parent = $this;

        if ($node instanceof SVGStyle) {
            $this->addContainerStyle($node);
        }

        return $this;
    }

    public function removeChild($child)
    {
        $index = $this->resolveChildIndex($child);
        if ($index === false) {
            return $this;
        }

        $node         = $this->children[$index];
        $node->parent = null;

        array_splice($this->children, $index, 1);

        return $this;
    }

    public function setChild($child, SVGNode $node)
    {
        $index = $this->resolveChildIndex($child);
        if ($index === false) {
            return $this;
        }

        $this->removeChild($index);
        $this->addChild($node, $index);

        return $this;
    }

    private function resolveChildIndex($nodeOrIndex)
    {
        if (is_int($nodeOrIndex)) {
            return $nodeOrIndex;
        } elseif ($nodeOrIndex instanceof SVGNode) {
            return array_search($nodeOrIndex, $this->children, true);
        }

        return false;
    }

    public function countChildren()
    {
        return count($this->children);
    }

    public function getChild($index)
    {
        return $this->children[$index];
    }

    public function addContainerStyle(SVGStyle $styleNode)
    {
        $newStyles = SVGStyleParser::parseCss($styleNode->getCss());
        $this->containerStyles = array_merge($this->containerStyles, $newStyles);

        return $this;
    }


    public function rasterize(SVGRasterizer $rasterizer)
    {
        if ($this->getComputedStyle('display') === 'none') {
            return;
        }


        foreach ($this->children as $child) {
            $child->rasterize($rasterizer);
        }
    }

    public function getContainerStyleForNode(SVGNode $node)
    {
        $pattern = $node->getIdAndClassPattern();

        return $this->getContainerStyleByPattern($pattern);
    }

    public function getContainerStyleByPattern($pattern)
    {
        if ($pattern === null) {
            return array();
        }

        $nodeStyles = array();
        if (!empty($this->parent)) {
            $nodeStyles = $this->parent->getContainerStyleByPattern($pattern);
        }

        $keys = $this->pregGrepStyle($pattern);
        foreach ($keys as $key) {
            $nodeStyles = array_merge($nodeStyles, $this->containerStyles[$key]);
        }

        return $nodeStyles;
    }

    private function pregGrepStyle($pattern)
    {
        return preg_grep($pattern, array_keys($this->containerStyles));
    }

    public function getElementsByTagName($tagName, array &$result = array())
    {
        foreach ($this->children as $child) {
            if ($tagName === '*' || $child->getName() === $tagName) {
                $result[] = $child;
            }
            $child->getElementsByTagName($tagName, $result);
        }

        return $result;
    }

    public function getElementsByClassName($className, array &$result = array())
    {
        if (!is_array($className)) {
            $className = preg_split('/\s+/', trim($className));
        }
        if (empty($className) || $className[0] === '') {
            return $result;
        }

        foreach ($this->children as $child) {
            $class = ' '.$child->getAttribute('class').' ';
            $allMatch = true;
            foreach ($className as $cn) {
                if (strpos($class, ' '.$cn.' ') === false) {
                    $allMatch = false;
                    break;
                }
            }
            if ($allMatch) {
                $result[] = $child;
            }
            $child->getElementsByClassName($className, $result);
        }

        return $result;
    }
}
