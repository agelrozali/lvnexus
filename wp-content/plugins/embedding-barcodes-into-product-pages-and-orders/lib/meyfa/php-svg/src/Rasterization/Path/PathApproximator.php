<?php

namespace SVG\Rasterization\Path;

class PathApproximator
{
    private static $commands = array(
        'M' => 'moveTo',                    'm' => 'moveTo',
        'L' => 'lineTo',                    'l' => 'lineTo',
        'H' => 'lineToHorizontal',          'h' => 'lineToHorizontal',
        'V' => 'lineToVertical',            'v' => 'lineToVertical',
        'C' => 'curveToCubic',              'c' => 'curveToCubic',
        'S' => 'curveToCubicSmooth',        's' => 'curveToCubicSmooth',
        'Q' => 'curveToQuadratic',          'q' => 'curveToQuadratic',
        'T' => 'curveToQuadraticSmooth',    't' => 'curveToQuadraticSmooth',
        'A' => 'arcTo',                     'a' => 'arcTo',
        'Z' => 'closePath',                 'z' => 'closePath',
    );

    private static $bezier, $arc;

    private $previousCommand, $cubicOld, $quadraticOld;

    public function __construct()
    {
        if (isset(self::$bezier)) {
            return;
        }
        self::$bezier = new BezierApproximator();
        self::$arc    = new ArcApproximator();
    }

    public function approximate(array $cmds)
    {
        $subpaths = array();

        $posX = 0;
        $posY = 0;

        $sp = array();

        foreach ($cmds as $cmd) {
            if (($cmd['id'] === 'M' || $cmd['id'] === 'm') && !empty($sp)) {
                $subpaths[] = $this->approximateSubpath($sp, $posX, $posY);
                $sp = array();
            }
            $sp[] = $cmd;
        }

        if (!empty($sp)) {
            $subpaths[] = $this->approximateSubpath($sp, $posX, $posY);
        }

        return $subpaths;
    }

    private function approximateSubpath(array $cmds, &$posX, &$posY)
    {
        $builder = new PolygonBuilder($posX, $posY);

        foreach ($cmds as $cmd) {
            $id = $cmd['id'];
            if (!isset(self::$commands[$id])) {
                return false;
            }
            $funcName = self::$commands[$id];
            $this->$funcName($id, $cmd['args'], $builder);
            $this->previousCommand = $id;
        }

        $pos  = $builder->getPosition();
        $posX = $pos[0];
        $posY = $pos[1];

        return $builder->build();
    }

    private static function reflectPoint($p, $r)
    {
        return array(
            2 * $r[0] - $p[0],
            2 * $r[1] - $p[1],
        );
    }

    private function moveTo($id, $args, PolygonBuilder $builder)
    {
        if ($id === 'm') {
            $builder->addPointRelative($args[0], $args[1]);
            return;
        }
        $builder->addPoint($args[0], $args[1]);
    }

    private function lineTo($id, $args, PolygonBuilder $builder)
    {
        if ($id === 'l') {
            $builder->addPointRelative($args[0], $args[1]);
            return;
        }
        $builder->addPoint($args[0], $args[1]);
    }

    private function lineToHorizontal($id, $args, PolygonBuilder $builder)
    {
        if ($id === 'h') {
            $builder->addPointRelative($args[0], null);
            return;
        }
        $builder->addPoint($args[0], null);
    }

    private function lineToVertical($id, $args, PolygonBuilder $builder)
    {
        if ($id === 'v') {
            $builder->addPointRelative(null, $args[0]);
            return;
        }
        $builder->addPoint(null, $args[0]);
    }

    private function curveToCubic($id, $args, PolygonBuilder $builder)
    {
        $p0 = $builder->getPosition();
        $p1 = array($args[0], $args[1]);
        $p2 = array($args[2], $args[3]);
        $p3 = array($args[4], $args[5]);

        if ($id === 'c') {
            $p1[0] += $p0[0];
            $p1[1] += $p0[1];

            $p2[0] += $p0[0];
            $p2[1] += $p0[1];

            $p3[0] += $p0[0];
            $p3[1] += $p0[1];
        }

        $approx = self::$bezier->cubic($p0, $p1, $p2, $p3);
        $builder->addPoints($approx);

        $this->cubicOld = $p2;
    }

    private function curveToCubicSmooth($id, $args, PolygonBuilder $builder)
    {
        $p0 = $builder->getPosition();
        $p1 = $p0; 
        $p2 = array($args[0], $args[1]);
        $p3 = array($args[2], $args[3]);

        if ($id === 's') {
            $p2[0] += $p0[0];
            $p2[1] += $p0[1];

            $p3[0] += $p0[0];
            $p3[1] += $p0[1];
        }

        $prev = strtolower($this->previousCommand);
        if ($prev === 'c' || $prev === 's') {
            $p1 = self::reflectPoint($this->cubicOld, $p0);
        }

        $approx = self::$bezier->cubic($p0, $p1, $p2, $p3);
        $builder->addPoints($approx);

        $this->cubicOld = $p2;
    }

    private function curveToQuadratic($id, $args, PolygonBuilder $builder)
    {
        $p0 = $builder->getPosition();
        $p1 = array($args[0], $args[1]);
        $p2 = array($args[2], $args[3]);

        if ($id === 'q') {
            $p1[0] += $p0[0];
            $p1[1] += $p0[1];

            $p2[0] += $p0[0];
            $p2[1] += $p0[1];
        }

        $approx = self::$bezier->quadratic($p0, $p1, $p2);
        $builder->addPoints($approx);

        $this->quadraticOld = $p1;
    }

    private function curveToQuadraticSmooth($id, $args, PolygonBuilder $builder)
    {
        $p0 = $builder->getPosition();
        $p1 = $p0; 
        $p2 = array($args[0], $args[1]);

        if ($id === 't') {
            $p2[0] += $p0[0];
            $p2[1] += $p0[1];
        }

        $prev = strtolower($this->previousCommand);
        if ($prev === 'q' || $prev === 't') {
            $p1 = self::reflectPoint($this->quadraticOld, $p0);
        }

        $approx = self::$bezier->quadratic($p0, $p1, $p2);
        $builder->addPoints($approx);

        $this->quadraticOld = $p1;
    }

    private function arcTo($id, $args, PolygonBuilder $builder)
    {
        $p0 = $builder->getPosition();
        $p1 = array($args[5], $args[6]);
        $rx = $args[0];
        $ry = $args[1];
        $xa = deg2rad($args[2]);
        $fa = (bool) $args[3];
        $fs = (bool) $args[4];

        if ($id === 'a') {
            $p1[0] += $p0[0];
            $p1[1] += $p0[1];
        }

        $approx = self::$arc->approximate($p0, $p1, $fa, $fs, $rx, $ry, $xa);
        $builder->addPoints($approx);
    }

    private function closePath($id, $args, PolygonBuilder $builder)
    {
        $first = $builder->getFirstPoint();
        $builder->addPoint($first[0], $first[1]);
    }
}
