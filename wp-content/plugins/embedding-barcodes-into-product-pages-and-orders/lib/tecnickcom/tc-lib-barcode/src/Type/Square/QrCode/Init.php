<?php

namespace Com\Tecnick\Barcode\Type\Square\QrCode;

use \Com\Tecnick\Barcode\Exception as BarcodeException;
use \Com\Tecnick\Barcode\Type\Square\QrCode\Data;
use \Com\Tecnick\Barcode\Type\Square\QrCode\Spec;

abstract class Init extends \Com\Tecnick\Barcode\Type\Square\QrCode\Mask
{
    protected function init($spec)
    {
        $dlv = $this->spc->rsDataCodes1($spec);
        $elv = $this->spc->rsEccCodes1($spec);
        $rsv = $this->initRs(8, 0x11d, 0, 1, $elv, 255 - $dlv - $elv);
        $blockNo = 0;
        $dataPos = 0;
        $eccPos = 0;
        $endfor = $this->spc->rsBlockNum1($spec);
        $this->initLoop($endfor, $dlv, $elv, $rsv, $eccPos, $blockNo, $dataPos, $ecc);
        if ($this->spc->rsBlockNum2($spec) == 0) {
            return;
        }
        $dlv = $this->spc->rsDataCodes2($spec);
        $elv = $this->spc->rsEccCodes2($spec);
        $rsv = $this->initRs(8, 0x11d, 0, 1, $elv, 255 - $dlv - $elv);
        if ($rsv == null) {
            throw new BarcodeException('Empty RS');
        }
        $endfor = $this->spc->rsBlockNum2($spec);
        $this->initLoop($endfor, $dlv, $elv, $rsv, $eccPos, $blockNo, $dataPos, $ecc);
    }

    protected function initLoop($endfor, $dlv, $elv, $rsv, &$eccPos, &$blockNo, &$dataPos, &$ecc)
    {
        for ($idx = 0; $idx < $endfor; ++$idx) {
            $ecc = array_slice($this->ecccode, $eccPos);
            $this->rsblocks[$blockNo] = array();
            $this->rsblocks[$blockNo]['dataLength'] = $dlv;
            $this->rsblocks[$blockNo]['data'] = array_slice($this->datacode, $dataPos);
            $this->rsblocks[$blockNo]['eccLength'] = $elv;
            $ecc = $this->encodeRsChar($rsv, $this->rsblocks[$blockNo]['data'], $ecc);
            $this->rsblocks[$blockNo]['ecc'] = $ecc;
            $this->ecccode = array_merge(array_slice($this->ecccode, 0, $eccPos), $ecc);
            $dataPos += $dlv;
            $eccPos += $elv;
            $blockNo++;
        }
    }

    protected function initRs($symsize, $gfpoly, $fcr, $prim, $nroots, $pad)
    {
        foreach ($this->rsitems as $rsv) {
            if (($rsv['pad'] != $pad)
                || ($rsv['nroots'] != $nroots)
                || ($rsv['mm'] != $symsize)
                || ($rsv['gfpoly'] != $gfpoly)
                || ($rsv['fcr'] != $fcr)
                || ($rsv['prim'] != $prim)) {
                continue;
            }
            return $rsv;
        }
        $rsv = $this->initRsChar($symsize, $gfpoly, $fcr, $prim, $nroots, $pad);
        array_unshift($this->rsitems, $rsv);
        return $rsv;
    }

    protected function modnn($rsv, $xpos)
    {
        while ($xpos >= $rsv['nn']) {
            $xpos -= $rsv['nn'];
            $xpos = (($xpos >> $rsv['mm']) + ($xpos & $rsv['nn']));
        }
        return $xpos;
    }

    protected function checkRsCharParamsA($symsize, $fcr, $prim)
    {
        $shfsymsize = (1 << $symsize);
        if (($symsize < 0)
            || ($symsize > 8)
            || ($fcr < 0)
            || ($fcr >= $shfsymsize)
            || ($prim <= 0)
            || ($prim >= $shfsymsize)
        ) {
            throw new BarcodeException('Invalid parameters');
        }
    }

    protected function checkRsCharParamsB($symsize, $nroots, $pad)
    {
        $shfsymsize = (1 << $symsize);
        if (($nroots < 0)
            || ($nroots >= $shfsymsize)
            || ($pad < 0)
            || ($pad >= ($shfsymsize - 1 - $nroots))
        ) {
            throw new BarcodeException('Invalid parameters');
        }
    }
    protected function initRsChar($symsize, $gfpoly, $fcr, $prim, $nroots, $pad)
    {
        $this->checkRsCharParamsA($symsize, $fcr, $prim);
        $this->checkRsCharParamsB($symsize, $nroots, $pad);
        $rsv = array();
        $rsv['mm'] = $symsize;
        $rsv['nn'] = ((1 << $symsize) - 1);
        $rsv['pad'] = $pad;
        $rsv['alpha_to'] = array_fill(0, ($rsv['nn'] + 1), 0);
        $rsv['index_of'] = array_fill(0, ($rsv['nn'] + 1), 0);
        $nnv =& $rsv['nn'];
        $azv =& $nnv;
        $rsv['index_of'][0] = $azv; 
        $rsv['alpha_to'][$azv] = 0; 
        $srv = 1;
        for ($idx = 0; $idx <$rsv['nn']; ++$idx) {
            $rsv['index_of'][$srv] = $idx;
            $rsv['alpha_to'][$idx] = $srv;
            $srv <<= 1;
            if ($srv & (1 << $symsize)) {
                $srv ^= $gfpoly;
            }
            $srv &= $rsv['nn'];
        }
        if ($srv != 1) {
            throw new BarcodeException('field generator polynomial is not primitive!');
        }
        $rsv['genpoly'] = array_fill(0, ($nroots + 1), 0);
        $rsv['fcr'] = $fcr;
        $rsv['prim'] = $prim;
        $rsv['nroots'] = $nroots;
        $rsv['gfpoly'] = $gfpoly;
        for ($iprim = 1; ($iprim % $prim) != 0; $iprim += $rsv['nn']) {
            ; 
        }
        $rsv['iprim'] = (int)($iprim / $prim);
        $rsv['genpoly'][0] = 1;
        for ($idx = 0, $root = ($fcr * $prim); $idx < $nroots; ++$idx, $root += $prim) {
            $rsv['genpoly'][($idx + 1)] = 1;
            for ($jdx = $idx; $jdx > 0; --$jdx) {
                if ($rsv['genpoly'][$jdx] != 0) {
                    $rsv['genpoly'][$jdx] = ($rsv['genpoly'][($jdx - 1)]
                        ^ $rsv['alpha_to'][$this->modnn($rsv, $rsv['index_of'][$rsv['genpoly'][$jdx]] + $root)]);
                } else {
                    $rsv['genpoly'][$jdx] = $rsv['genpoly'][($jdx - 1)];
                }
            }
            $rsv['genpoly'][0] = $rsv['alpha_to'][$this->modnn($rsv, $rsv['index_of'][$rsv['genpoly'][0]] + $root)];
        }
        for ($idx = 0; $idx <= $nroots; ++$idx) {
            $rsv['genpoly'][$idx] = $rsv['index_of'][$rsv['genpoly'][$idx]];
        }
        return $rsv;
    }

    protected function encodeRsChar($rsv, $data, $parity)
    {
        $nnv =& $rsv['nn'];
        $alphato =& $rsv['alpha_to'];
        $indexof =& $rsv['index_of'];
        $genpoly =& $rsv['genpoly'];
        $nroots =& $rsv['nroots'];
        $pad =& $rsv['pad'];
        $azv =& $nnv;
        $parity = array_fill(0, $nroots, 0);
        for ($idx = 0; $idx < ($nnv - $nroots - $pad); ++$idx) {
            $feedback = $indexof[$data[$idx] ^ $parity[0]];
            if ($feedback != $azv) {
                $feedback = $this->modnn($rsv, ($nnv - $genpoly[$nroots] + $feedback));
                for ($jdx = 1; $jdx < $nroots; ++$jdx) {
                    $parity[$jdx] ^= $alphato[$this->modnn($rsv, $feedback + $genpoly[($nroots - $jdx)])];
                }
            }
            array_shift($parity);
            if ($feedback != $azv) {
                array_push($parity, $alphato[$this->modnn($rsv, $feedback + $genpoly[0])]);
            } else {
                array_push($parity, 0);
            }
        }
        return $parity;
    }
}
