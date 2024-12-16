<?php

namespace SVG\Nodes\Shapes;

use SVG\Nodes\SVGNodeContainer;
use SVG\Rasterization\SVGRasterizer;

class SVGPath extends SVGNodeContainer
{
    const TAG_NAME = 'path';

    public function __construct($d = null)
    {
        parent::__construct();

        $this->setAttribute('d', $d);
    }

    public function getDescription()
    {
        return $this->getAttribute('d');
    }

    public function setDescription($d)
    {
        return $this->setAttribute('d', $d);
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

        $d = $this->getDescription();
        if (!isset($d)) {
            return;
        }

        $commands = $rasterizer->getPathParser()->parse($d);
        $subpaths = $rasterizer->getPathApproximator()->approximate($commands);

        foreach ($subpaths as $subpath) {
            $rasterizer->render('polygon', array(
                'open'      => true,
                'points'    => $subpath,
            ), $this);
        }
    }
}
