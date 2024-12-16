<?php

namespace SVG\Nodes\Structures;

use SVG\Nodes\SVGNodeContainer;

class SVGLinkGroup extends SVGNodeContainer
{
    const TAG_NAME = 'a';

    public function __construct()
    {
        parent::__construct();
    }
}
