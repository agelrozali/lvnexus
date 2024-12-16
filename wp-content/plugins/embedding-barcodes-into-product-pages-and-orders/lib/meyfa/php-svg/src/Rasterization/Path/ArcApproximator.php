<?php

namespace SVG\Rasterization\Path;

class ArcApproximator
{
    private static $EPSILON = 0.0000001;

    public function approximate($start, $end, $large, $sweep, $radiusX, $radiusY, $rotation)
    {
        if (self::pointsClose($start, $end)) {
            return array();
        }
        $radiusX = abs($radiusX);
        $radiusY = abs($radiusY);
        if ($radiusX < self::$EPSILON || $radiusY < self::$EPSILON) {
            return array($start, $end);
        }

        $cosr = cos($rotation);
        $sinr = sin($rotation);

        list($center, $radiusX, $radiusY, $angleStart, $angleDelta) = self::endpointToCenter(
            $start, $end, $large, $sweep, $radiusX, $radiusY, $cosr, $sinr);

        $dist = abs($end[0] - $start[0]) + abs($end[1] - $start[1]);
        $numSteps = max(2, ceil(abs($angleDelta * $dist)));
        $stepSize = $angleDelta / $numSteps;

        $points = array();

        for ($i = 0; $i <= $numSteps; ++$i) {
            $angle = $angleStart + $stepSize * $i;
            $first = $radiusX * cos($angle);
            $second = $radiusY * sin($angle);

            $points[] = array(
                $cosr * $first - $sinr * $second + $center[0],
                $sinr * $first + $cosr * $second + $center[1],
            );
        }

        return $points;
    }

    private static function endpointToCenter($start, $end, $large, $sweep, $radiusX, $radiusY, $cosr, $sinr)
    {
        $xsubhalf = ($start[0] - $end[0]) / 2;
        $ysubhalf = ($start[1] - $end[1]) / 2;
        $x1prime  = $cosr * $xsubhalf + $sinr * $ysubhalf;
        $y1prime  = -$sinr * $xsubhalf + $cosr * $ysubhalf;

        $rx2 = $radiusX * $radiusX;
        $ry2 = $radiusY * $radiusY;
        $x1prime2 = $x1prime * $x1prime;
        $y1prime2 = $y1prime * $y1prime;

        $lambdaSqrt = sqrt($x1prime2 / $rx2 + $y1prime2 / $ry2);
        if ($lambdaSqrt > 1) {
            $radiusX *= $lambdaSqrt;
            $radiusY *= $lambdaSqrt;
            $rx2 = $radiusX * $radiusX;
            $ry2 = $radiusY * $radiusY;
        }

        $cxfactor = ($large != $sweep ? 1 : -1) * sqrt(abs(
            ($rx2*$ry2 - $rx2*$y1prime2 - $ry2*$x1prime2) / ($rx2*$y1prime2 + $ry2*$x1prime2)
        ));
        $cxprime = $cxfactor *  $radiusX * $y1prime / $radiusY;
        $cyprime = $cxfactor * -$radiusY * $x1prime / $radiusX;

        $centerX = $cosr * $cxprime - $sinr * $cyprime + ($start[0] + $end[0]) / 2;
        $centerY = $sinr * $cxprime + $cosr * $cyprime + ($start[1] + $end[1]) / 2;

        $angleStart = self::vectorAngle(
            ($x1prime - $cxprime) / $radiusX,
            ($y1prime - $cyprime) / $radiusY
        );
        $angleDelta = self::vectorAngle2(
            ( $x1prime - $cxprime) / $radiusX,
            ( $y1prime - $cyprime) / $radiusY,
            (-$x1prime - $cxprime) / $radiusX,
            (-$y1prime - $cyprime) / $radiusY
        );

        if (!$sweep && $angleDelta > 0) {
            $angleDelta -= M_PI * 2;
        } elseif ($sweep && $angleDelta < 0) {
            $angleDelta += M_PI * 2;
        }

        return array(array($centerX, $centerY), $radiusX, $radiusY, $angleStart, $angleDelta);
    }

    private static function vectorAngle($vecx, $vecy)
    {
        $norm = sqrt($vecx * $vecx + $vecy * $vecy);
        return ($vecy >= 0 ? 1 : -1) * acos($vecx / $norm);
    }

    private static function vectorAngle2($vec1x, $vec1y, $vec2x, $vec2y)
    {
        $dotprod = $vec1x * $vec2x + $vec1y * $vec2y;
        $norm = sqrt($vec1x * $vec1x + $vec1y * $vec1y) * sqrt($vec2x * $vec2x + $vec2y * $vec2y);

        $sign = ($vec1x * $vec2y - $vec1y * $vec2x) >= 0 ? 1 : -1;

        return $sign * acos($dotprod / $norm);
    }

    private static function pointsClose($vec1, $vec2)
    {
        $distanceX = abs($vec1[0] - $vec2[0]);
        $distanceY = abs($vec1[1] - $vec2[1]);

        return $distanceX < self::$EPSILON && $distanceY < self::$EPSILON;
    }
}
