<?php

namespace Com\Tecnick\Color\Model;

interface Template
{
    public function getArray();

    public function getNormalizedArray($max);

    public function getCssColor();

    public function getJsPdfColor();

    public function getComponentsString();

    public function getPdfColor();

    public function toGrayArray();

    public function toRgbArray();

    public function toHslArray();

    public function toCmykArray();

    public function invertColor();
}
