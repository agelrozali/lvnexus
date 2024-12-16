<?php

namespace Com\Tecnick\Barcode\Type\Square\Datamatrix;

class ErrorCorrection
{
    protected function getGFProduct($numa, $numb, $log, $alog, $ngf)
    {
        if (($numa == 0) || ($numb == 0)) {
            return 0;
        }
        return ($alog[($log[$numa] + $log[$numb]) % ($ngf - 1)]);
    }

    public function getErrorCorrection($wdc, $nbk, $ncw, $ncc, $ngf = 256, $vpp = 301)
    {
        $log = array(0);
        $alog = array(1);
        $this->genLogs($log, $alog, $ngf, $vpp);

        $plc = array_fill(0, ($ncc + 1), 0);
        $plc[0] = 1;
        for ($i = 1; $i <= $ncc; ++$i) {
            $plc[$i] = $plc[($i-1)];
            for ($j = ($i - 1); $j >= 1; --$j) {
                $plc[$j] = $plc[($j - 1)] ^ $this->getGFProduct($plc[$j], $alog[$i], $log, $alog, $ngf);
            }
            $plc[0] = $this->getGFProduct($plc[0], $alog[$i], $log, $alog, $ngf);
        }
        ksort($plc);

        $num_wd = ($nbk * $ncw);
        $num_we = ($nbk * $ncc);
        for ($b = 0; $b < $nbk; ++$b) {
            $block = array();
            for ($n = $b; $n < $num_wd; $n += $nbk) {
                $block[] = $wdc[$n];
            }
            $wec = array_fill(0, ($ncc + 1), 0);
            for ($i = 0; $i < $ncw; ++$i) {
                $ker = ($wec[0] ^ $block[$i]);
                for ($j = 0; $j < $ncc; ++$j) {
                    $wec[$j] = ($wec[($j + 1)] ^ $this->getGFProduct($ker, $plc[($ncc - $j - 1)], $log, $alog, $ngf));
                }
            }
            $j = 0;
            for ($i = $b; $i < $num_we; $i += $nbk) {
                $wdc[($num_wd + $i)] = $wec[$j];
                ++$j;
            }
        }
        ksort($wdc);
        return $wdc;
    }

    protected function genLogs(&$log, &$alog, $ngf, $vpp)
    {
        for ($i = 1; $i < $ngf; ++$i) {
            $alog[$i] = ($alog[($i - 1)] * 2);
            if ($alog[$i] >= $ngf) {
                $alog[$i] ^= $vpp;
            }
            $log[$alog[$i]] = $i;
        }
        ksort($log);
    }
}
