<?php

namespace Com\Tecnick\Barcode\Type\Square\Datamatrix;

use \Com\Tecnick\Barcode\Exception as BarcodeException;

abstract class Placement
{
    protected function placeModule($marr, $nrow, $ncol, $row, $col, $chr, $bit)
    {
        if ($row < 0) {
            $row += $nrow;
            $col += (4 - (($nrow + 4) % 8));
        }
        if ($col < 0) {
            $col += $ncol;
            $row += (4 - (($ncol + 4) % 8));
        }
        $marr[(($row * $ncol) + $col)] = ((10 * $chr) + $bit);
        return $marr;
    }

    protected function placeUtah($marr, $nrow, $ncol, $row, $col, $chr)
    {
        $marr = $this->placeModule($marr, $nrow, $ncol, $row-2, $col-2, $chr, 1);
        $marr = $this->placeModule($marr, $nrow, $ncol, $row-2, $col-1, $chr, 2);
        $marr = $this->placeModule($marr, $nrow, $ncol, $row-1, $col-2, $chr, 3);
        $marr = $this->placeModule($marr, $nrow, $ncol, $row-1, $col-1, $chr, 4);
        $marr = $this->placeModule($marr, $nrow, $ncol, $row-1, $col, $chr, 5);
        $marr = $this->placeModule($marr, $nrow, $ncol, $row, $col-2, $chr, 6);
        $marr = $this->placeModule($marr, $nrow, $ncol, $row, $col-1, $chr, 7);
        $marr = $this->placeModule($marr, $nrow, $ncol, $row, $col, $chr, 8);
        return $marr;
    }

    protected function placeCornerA($marr, $nrow, $ncol, &$chr, $row, $col)
    {
        if (($row != $nrow) || ($col != 0)) {
            return $marr;
        }
        $marr = $this->placeModule($marr, $nrow, $ncol, $nrow-1, 0, $chr, 1);
        $marr = $this->placeModule($marr, $nrow, $ncol, $nrow-1, 1, $chr, 2);
        $marr = $this->placeModule($marr, $nrow, $ncol, $nrow-1, 2, $chr, 3);
        $marr = $this->placeModule($marr, $nrow, $ncol, 0, $ncol-2, $chr, 4);
        $marr = $this->placeModule($marr, $nrow, $ncol, 0, $ncol-1, $chr, 5);
        $marr = $this->placeModule($marr, $nrow, $ncol, 1, $ncol-1, $chr, 6);
        $marr = $this->placeModule($marr, $nrow, $ncol, 2, $ncol-1, $chr, 7);
        $marr = $this->placeModule($marr, $nrow, $ncol, 3, $ncol-1, $chr, 8);
        ++$chr;
        return $marr;
    }

    protected function placeCornerB($marr, $nrow, $ncol, &$chr, $row, $col)
    {
        if (($row != ($nrow - 2)) || ($col != 0) || (($ncol % 4) == 0)) {
            return $marr;
        }
        $marr = $this->placeModule($marr, $nrow, $ncol, $nrow-3, 0, $chr, 1);
        $marr = $this->placeModule($marr, $nrow, $ncol, $nrow-2, 0, $chr, 2);
        $marr = $this->placeModule($marr, $nrow, $ncol, $nrow-1, 0, $chr, 3);
        $marr = $this->placeModule($marr, $nrow, $ncol, 0, $ncol-4, $chr, 4);
        $marr = $this->placeModule($marr, $nrow, $ncol, 0, $ncol-3, $chr, 5);
        $marr = $this->placeModule($marr, $nrow, $ncol, 0, $ncol-2, $chr, 6);
        $marr = $this->placeModule($marr, $nrow, $ncol, 0, $ncol-1, $chr, 7);
        $marr = $this->placeModule($marr, $nrow, $ncol, 1, $ncol-1, $chr, 8);
        ++$chr;
        return $marr;
    }

    protected function placeCornerC($marr, $nrow, $ncol, &$chr, $row, $col)
    {
        if (($row != ($nrow - 2)) || ($col != 0) || (($ncol % 8) != 4)) {
            return $marr;
        }
        $marr = $this->placeModule($marr, $nrow, $ncol, $nrow-3, 0, $chr, 1);
        $marr = $this->placeModule($marr, $nrow, $ncol, $nrow-2, 0, $chr, 2);
        $marr = $this->placeModule($marr, $nrow, $ncol, $nrow-1, 0, $chr, 3);
        $marr = $this->placeModule($marr, $nrow, $ncol, 0, $ncol-2, $chr, 4);
        $marr = $this->placeModule($marr, $nrow, $ncol, 0, $ncol-1, $chr, 5);
        $marr = $this->placeModule($marr, $nrow, $ncol, 1, $ncol-1, $chr, 6);
        $marr = $this->placeModule($marr, $nrow, $ncol, 2, $ncol-1, $chr, 7);
        $marr = $this->placeModule($marr, $nrow, $ncol, 3, $ncol-1, $chr, 8);
        ++$chr;
        return $marr;
    }

    protected function placeCornerD($marr, $nrow, $ncol, &$chr, $row, $col)
    {
        if (($row != ($nrow + 4)) || ($col != 2) || ($ncol % 8)) {
            return $marr;
        }
        $marr = $this->placeModule($marr, $nrow, $ncol, $nrow-1, 0, $chr, 1);
        $marr = $this->placeModule($marr, $nrow, $ncol, $nrow-1, $ncol-1, $chr, 2);
        $marr = $this->placeModule($marr, $nrow, $ncol, 0, $ncol-3, $chr, 3);
        $marr = $this->placeModule($marr, $nrow, $ncol, 0, $ncol-2, $chr, 4);
        $marr = $this->placeModule($marr, $nrow, $ncol, 0, $ncol-1, $chr, 5);
        $marr = $this->placeModule($marr, $nrow, $ncol, 1, $ncol-3, $chr, 6);
        $marr = $this->placeModule($marr, $nrow, $ncol, 1, $ncol-2, $chr, 7);
        $marr = $this->placeModule($marr, $nrow, $ncol, 1, $ncol-1, $chr, 8);
        ++$chr;
        return $marr;
    }



    protected function placeSweepUpward($marr, $nrow, $ncol, &$chr, &$row, &$col)
    {
        do {
            if (($row < $nrow) && ($col >= 0) && (!$marr[(($row * $ncol) + $col)])) {
                $marr = $this->placeUtah($marr, $nrow, $ncol, $row, $col, $chr);
                ++$chr;
            }
            $row -= 2;
            $col += 2;
        } while (($row >= 0) && ($col < $ncol));
        ++$row;
        $col += 3;
        return $marr;
    }

    protected function placeSweepDownward($marr, $nrow, $ncol, &$chr, &$row, &$col)
    {
        do {
            if (($row >= 0) && ($col < $ncol) && (!$marr[(($row * $ncol) + $col)])) {
                $marr = $this->placeUtah($marr, $nrow, $ncol, $row, $col, $chr);
                ++$chr;
            }
            $row += 2;
            $col -= 2;
        } while (($row < $nrow) && ($col >= 0));
        $row += 3;
        ++$col;
        return $marr;
    }

    public function getPlacementMap($nrow, $ncol)
    {
        $marr = array_fill(0, ($nrow * $ncol), 0);
        $chr = 1;
        $row = 4;
        $col = 0;
        do {
            $marr = $this->placeCornerA($marr, $nrow, $ncol, $chr, $row, $col);
            $marr = $this->placeCornerB($marr, $nrow, $ncol, $chr, $row, $col);
            $marr = $this->placeCornerC($marr, $nrow, $ncol, $chr, $row, $col);
            $marr = $this->placeCornerD($marr, $nrow, $ncol, $chr, $row, $col);
            $marr = $this->placeSweepUpward($marr, $nrow, $ncol, $chr, $row, $col);
            $marr = $this->placeSweepDownward($marr, $nrow, $ncol, $chr, $row, $col);
        } while (($row < $nrow) || ($col < $ncol));
        if (!$marr[(($nrow * $ncol) - 1)]) {
            $marr[(($nrow * $ncol) - 1)] = 1;
            $marr[(($nrow * $ncol) - $ncol - 2)] = 1;
        }
        return $marr;
    }
}
