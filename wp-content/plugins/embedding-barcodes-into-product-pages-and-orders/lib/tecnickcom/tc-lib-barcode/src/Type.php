<?php

namespace Com\Tecnick\Barcode;

use \Com\Tecnick\Barcode\Exception as BarcodeException;
use \Com\Tecnick\Color\Exception as ColorException;

abstract class Type extends \Com\Tecnick\Barcode\Type\Convert
{
    protected $type = '';

    protected $format = '';

    protected $params;

    protected $code = '';

    protected $extcode = '';

    protected $ncols = 0;

    protected $nrows = 1;

    protected $bars = array();

    protected $width;

    protected $height;

    protected $padding = array('T' => 0, 'R' => 0, 'B' => 0, 'L' => 0);

    protected $width_ratio;

    protected $height_ratio;

    protected $color_obj;

    protected $bg_color_obj;

    public function __construct(
        $code,
        $width = -1,
        $height = -1,
        $color = 'black',
        $params = array(),
        $padding = array(0, 0, 0, 0)
    ) {
        $this->code = $code;
        $this->extcode = $code;
        $this->params = $params;
        $this->setParameters();
        $this->setBars();
        $this->setSize($width, $height, $padding);
        $this->setColor($color);
    }

    protected function setParameters()
    {
    }

    abstract protected function setBars();

    public function setSize($width, $height, $padding = array(0, 0, 0, 0))
    {
        $this->width = intval($width);
        if ($this->width <= 0) {
            $this->width = (abs(min(-1, $this->width)) * $this->ncols);
        }

        $this->height = intval($height);
        if ($this->height <= 0) {
            $this->height = (abs(min(-1, $this->height)) * $this->nrows);
        }

        $this->width_ratio = ($this->width / $this->ncols);
        $this->height_ratio = ($this->height / $this->nrows);

        $this->setPadding($padding);

        return $this;
    }

    protected function setPadding($padding)
    {
        if (!is_array($padding) || (count($padding) != 4)) {
            throw new BarcodeException('Invalid padding, expecting an array of 4 numbers (top, right, bottom, left)');
        }
        $map = array(
            array('T', $this->height_ratio),
            array('R', $this->width_ratio),
            array('B', $this->height_ratio),
            array('L', $this->width_ratio)
        );
        foreach ($padding as $key => $val) {
            $val = intval($val);
            if ($val < 0) {
                $val = (abs(min(-1, $val)) * $map[$key][1]);
            }
            $this->padding[$map[$key][0]] = $val;
        }

        return $this;
    }

    public function setColor($color)
    {
        $this->color_obj = $this->getRgbColorObject($color);
        if ($this->color_obj === null) {
            throw new BarcodeException('The foreground color cannot be empty or transparent');
        }
        return $this;
    }

    public function setBackgroundColor($color)
    {
        $this->bg_color_obj = $this->getRgbColorObject($color);
        return $this;
    }

    protected function getRgbColorObject($color)
    {
        $conv = new \Com\Tecnick\Color\Pdf();
        $cobj = $conv->getColorObject($color);
        if ($cobj !== null) {
            return new \Com\Tecnick\Color\Model\Rgb($cobj->toRgbArray());
        }
        return null;
    }

    public function getArray()
    {
        return array(
            'type'         => $this->type,
            'format'       => $this->format,
            'params'       => $this->params,
            'code'         => $this->code,
            'extcode'      => $this->extcode,
            'ncols'        => $this->ncols,
            'nrows'        => $this->nrows,
            'width'        => $this->width,
            'height'       => $this->height,
            'width_ratio'  => $this->width_ratio,
            'height_ratio' => $this->height_ratio,
            'padding'      => $this->padding,
            'full_width'   => ($this->width + $this->padding['L'] + $this->padding['R']),
            'full_height'  => ($this->height + $this->padding['T'] + $this->padding['B']),
            'color_obj'    => $this->color_obj,
            'bg_color_obj' => $this->bg_color_obj,
            'bars'         => $this->bars
        );
    }

    public function getExtendedCode()
    {
        return $this->extcode;
    }

    public function getSvg()
    {
        $data = $this->getSvgCode();
        header('Content-Type: application/svg+xml');
        header('Cache-Control: private, must-revalidate, post-check=0, pre-check=0, max-age=1');
        header('Pragma: public');
        header('Expires: Thu, 04 jan 1973 00:00:00 GMT'); 
        header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
        header('Content-Disposition: inline; filename="'.md5($data).'.svg";');
        if (empty($_SERVER['HTTP_ACCEPT_ENCODING'])) {
            header('Content-Length: '.strlen($data));
        }
        echo $data;
    }

    public function getSvgCode()
    {
        $hflag = ENT_NOQUOTES;
        if (defined('ENT_XML1') && defined('ENT_DISALLOWED')) {
            $hflag = ENT_XML1 | ENT_DISALLOWED;
        }
        $width = sprintf('%F', ($this->width + $this->padding['L'] + $this->padding['R']));
        $height = sprintf('%F', ($this->height + $this->padding['T'] + $this->padding['B']));
        $svg = '<?xml version="1.0" standalone="no" ?>'."\n"
            .'<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">'."\n"
            .'<svg'
            .' width="'.$width.'"'
            .' height="'.$height.'"'
            .' viewBox="0 0 '.$width.' '.$height.'"'
            .' version="1.1"'
            .' xmlns="http://www.w3.org/2000/svg"'
            .'>'."\n"
            ."\t".'<desc>'.htmlspecialchars($this->code, $hflag, 'UTF-8').'</desc>'."\n";
        if ($this->bg_color_obj !== null) {
            $svg .= "\t".'<rect'
                .' x="0"'
                .' y="0"'
                .' width="'.$width.'"'
                .' height="'.$height.'"'
                .' fill="'.$this->bg_color_obj->getRgbHexColor().'"'
                .' stroke="none"'
                .' stroke-width="0"'
                .' stroke-linecap="square"'
                .' />'."\n";
        }
        $svg .= "\t".'<g'
            .' id="bars"'
            .' fill="'.$this->color_obj->getRgbHexColor().'"'
            .' stroke="none"'
            .' stroke-width="0"'
            .' stroke-linecap="square"'
            .'>'."\n";
        $bars = $this->getBarsArray('XYWH');
        foreach ($bars as $rect) {
            $svg .= "\t\t".'<rect'
                .' x="'.sprintf('%F', $rect[0]).'"'
                .' y="'.sprintf('%F', $rect[1]).'"'
                .' width="'.sprintf('%F', $rect[2]).'"'
                .' height="'.sprintf('%F', $rect[3]).'"'
                .' />'."\n";
        }
        $svg .= "\t".'</g>'."\n"
            .'</svg>'."\n";
        return $svg;
    }

    public function getHtmlDiv()
    {
        $html = '<div style="'
            .'width:'.sprintf('%F', ($this->width + $this->padding['L'] + $this->padding['R'])).'px;'
            .'height:'.sprintf('%F', ($this->height + $this->padding['T'] + $this->padding['B'])).'px;'
            .'position:relative;'
            .'font-size:0;'
            .'border:none;'
            .'padding:0;'
            .'margin:0;';
        if ($this->bg_color_obj !== null) {
            $html .= 'background-color:'.$this->bg_color_obj->getCssColor().';';
        }
        $html .= '">'."\n";
        $bars = $this->getBarsArray('XYWH');
        foreach ($bars as $rect) {
            $html .= "\t".'<div style="background-color:'.$this->color_obj->getCssColor().';'
                .'left:'.sprintf('%F', $rect[0]).'px;'
                .'top:'.sprintf('%F', $rect[1]).'px;'
                .'width:'.sprintf('%F', $rect[2]).'px;'
                .'height:'.sprintf('%F', $rect[3]).'px;'
                .'position:absolute;'
                .'border:none;'
                .'padding:0;'
                .'margin:0;'
                .'">&nbsp;</div>'."\n";
        }
        $html .= '</div>'."\n";
        return $html;
    }

    public function getPng()
    {
        $data = $this->getPngData();
        header('Content-Type: image/png');
        header('Cache-Control: private, must-revalidate, post-check=0, pre-check=0, max-age=1');
        header('Pragma: public');
        header('Expires: Thu, 04 jan 1973 00:00:00 GMT'); 
        header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
        header('Content-Disposition: inline; filename="'.md5($data).'.png";');
        if (empty($_SERVER['HTTP_ACCEPT_ENCODING'])) {
            header('Content-Length: '.strlen($data));
        }
        echo $data;
    }

    public function getPngData($imagick = true)
    {
        if ($imagick && extension_loaded('imagick')) {
            return $this->getPngDataImagick();
        }
        $img = $this->getGd();
        ob_start();
        imagepng($img);
        return ob_get_clean();
    }

    public function getPngDataImagick()
    {
        $img = new \Imagick();
        $width = ceil($this->width + $this->padding['L'] + $this->padding['R']);
        $height = ceil($this->height + $this->padding['T'] + $this->padding['B']);
        $img->newImage($width, $height, 'none', 'png');
        $barcode = new \imagickdraw();
        if ($this->bg_color_obj !== null) {
            $rgbcolor = $this->bg_color_obj->getNormalizedArray(255);
            $bg_color = new \imagickpixel('rgb('.$rgbcolor['R'].','.$rgbcolor['G'].','.$rgbcolor['B'].')');
            $barcode->setfillcolor($bg_color);
            $barcode->rectangle(0, 0, $width, $height);
        }
        $rgbcolor = $this->color_obj->getNormalizedArray(255);
        $bar_color = new \imagickpixel('rgb('.$rgbcolor['R'].','.$rgbcolor['G'].','.$rgbcolor['B'].')');
        $barcode->setfillcolor($bar_color);
        $bars = $this->getBarsArray('XYXY');
        foreach ($bars as $rect) {
            $barcode->rectangle($rect[0], $rect[1], $rect[2], $rect[3]);
        }
        $img->drawimage($barcode);
        return $img->getImageBlob();
    }

    public function getGd()
    {
        $width = ceil($this->width + $this->padding['L'] + $this->padding['R']);
        $height = ceil($this->height + $this->padding['T'] + $this->padding['B']);
        $img = imagecreate($width, $height);
        if ($this->bg_color_obj === null) {
            $bgobj = clone $this->color_obj;
            $rgbcolor = $bgobj->invertColor()->getNormalizedArray(255);
            $background_color = imagecolorallocate($img, $rgbcolor['R'], $rgbcolor['G'], $rgbcolor['B']);
            imagecolortransparent($img, $background_color);
        } else {
            $rgbcolor = $this->bg_color_obj->getNormalizedArray(255);
            $bg_color = imagecolorallocate($img, $rgbcolor['R'], $rgbcolor['G'], $rgbcolor['B']);
            imagefilledrectangle($img, 0, 0, $width, $height, $bg_color);
        }
        $rgbcolor = $this->color_obj->getNormalizedArray(255);
        $bar_color = imagecolorallocate($img, $rgbcolor['R'], $rgbcolor['G'], $rgbcolor['B']);
        $bars = $this->getBarsArray('XYXY');
        foreach ($bars as $rect) {
            imagefilledrectangle($img, floor($rect[0]), floor($rect[1]), floor($rect[2]), floor($rect[3]), $bar_color);
        }
        return $img;
    }

    public function getGrid($space_char = '0', $bar_char = '1')
    {
        $raw = $this->getGridArray($space_char, $bar_char);
        $grid = '';
        foreach ($raw as $row) {
            $grid .= implode($row)."\n";
        }
        return $grid;
    }

    public function getBarsArray($type = 'XYXY')
    {
        $mtd = 'getBarRect'.$type;
        $rect = array();
        foreach ($this->bars as $bar) {
            if (($bar[2] > 0) && ($bar[3] > 0)) {
                $rect[] = $this->$mtd($bar);
            }
        }
        if ($this->nrows > 1) {
            $rot = $this->getRotatedBarArray();
            foreach ($rot as $bar) {
                if (($bar[2] > 0) && ($bar[3] > 0)) {
                    $rect[] = $this->$mtd($bar);
                }
            }
        }
        return $rect;
    }
}
