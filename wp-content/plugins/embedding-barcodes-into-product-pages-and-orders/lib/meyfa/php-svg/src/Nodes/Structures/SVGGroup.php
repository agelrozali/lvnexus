<?php

namespace SVG\Nodes\Structures;

use SVG\Nodes\SVGNodeContainer;

class SVGGroup extends SVGNodeContainer
{
    const TAG_NAME = 'g';

    public function __construct()
    {
        parent::__construct();
    }
}
