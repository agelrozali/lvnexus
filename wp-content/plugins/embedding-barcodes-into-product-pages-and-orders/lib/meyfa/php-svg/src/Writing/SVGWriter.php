<?php

namespace SVG\Writing;

use SVG\Nodes\SVGNode;
use SVG\Nodes\SVGNodeContainer;
use SVG\Nodes\Structures\SVGStyle;

class SVGWriter
{
    private $outString = '';

    public function __construct($isStandalone = true)
    {
        if ($isStandalone) {
            $this->outString = '<?xml version="1.0" encoding="utf-8"?>';
        }
    }

    public function getString()
    {
        return $this->outString;
    }

    public function writeNode(SVGNode $node)
    {
        $this->outString .= '<'.$node->getName();

        $this->appendAttributes($node->getSerializableAttributes());
        $this->appendStyles($node->getSerializableStyles());

        $textContent = htmlspecialchars($node->getValue());

        if ($node instanceof SVGStyle) {
            $this->outString .= '>';
            $this->writeCdata($node->getCss());
            $this->outString .= $textContent.'</'.$node->getName().'>';
            return;
        }

        if ($node instanceof SVGNodeContainer && $node->countChildren() > 0) {
            $this->outString .= '>';
            for ($i = 0, $n = $node->countChildren(); $i < $n; ++$i) {
                $this->writeNode($node->getChild($i));
            }
            $this->outString .= $textContent.'</'.$node->getName().'>';
            return;
        }

        if (!empty($textContent)) {
            $this->outString .= '>' . $textContent . '</'.$node->getName().'>';
            return;
        }

        $this->outString .= ' />';
    }

    private function appendStyles(array $styles)
    {
        if (empty($styles)) {
            return;
        }

        $string = '';
        $prependSemicolon = false;
        foreach ($styles as $key => $value) {
            if ($prependSemicolon) {
                $string .= '; ';
            }
            $prependSemicolon = true;
            $string .= $key.': '.$value;
        }

        $this->appendAttribute('style', $string);
    }

    private function appendAttributes(array $attrs)
    {
        foreach ($attrs as $key => $value) {
            $this->appendAttribute($key, $value);
        }
    }

    private function appendAttribute($attrName, $attrValue)
    {
        $xml1 = defined('ENT_XML1') ? ENT_XML1 : 16;

        $attrName = htmlspecialchars($attrName, $xml1 | ENT_COMPAT);
        $attrValue = htmlspecialchars($attrValue, $xml1 | ENT_COMPAT);

        $this->outString .= ' '.$attrName.'="'.$attrValue.'"';
    }

    private function writeCdata($cdata)
    {
        $xml1 = defined('ENT_XML1') ? ENT_XML1 : 16;

        $cdata = htmlspecialchars($cdata, $xml1 | ENT_COMPAT);

        $this->outString .= '<![CDATA[' . $cdata . ']]>';
    }
}
