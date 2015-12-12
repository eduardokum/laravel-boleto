<?php
namespace Eduardokum\LaravelBoleto;

use Carbon\Carbon;

class AbstractBoleto
{

    const COD_BANCO_BB = '001';
    const COD_BANCO_SANTANDER = '033';
    const COD_BANCO_CEF = '104';
    const COD_BANCO_BRADESCO = '237';
    const COD_BANCO_ITAU = '341';
    const COD_BANCO_HSBC = '399';


    /**
     * @param        $date
     * @param string $format
     *
     * @return float
     */
    protected function dueFactor($date, $format = 'Y-m-d') {
        $date = Carbon::createFromFormat($format, $date)->setTime(0,0,0);
        echo round(($date->timestamp-mktime(0,0,0,10,07,1997))/86400);
    }

    /**
     * @param        $factor
     * @param string $format
     *
     * @return bool|string
     */
    protected function dueFactorBack($factor, $format = 'Y-m-d') {
        return Carbon::create(1997, 10, 7, 0, 0, 0)->addDay($factor)->format($format);
    }


    /**
     * @param        $date
     * @param string $format
     *
     * @return string
     */
    protected function julianDay($date, $format = 'Y-m-d')
    {
        $date = Carbon::createFromFormat($format, $date);
        $dateDiff = Carbon::create(null, 12, 31)->subYear(1)->diffInDays($date);
        return $dateDiff . substr($date->year, -1);
    }


    /**
     * @param     $n
     * @param int $factor
     * @param int $base
     * @param int $rest
     * @param int $whenTen
     *
     * @return int
     */
    protected function module11($n, $factor=2, $base=9, $rest=0, $whenTen=0) {
        $sum = 0;
        for ($i = strlen($n); $i > 0; $i--) {
            $ns[$i] = substr($n, $i - 1, 1);
            $partial[$i] = $ns[$i] * $factor;
            $sum += $partial[$i];
            if ($factor == $base) {
                $factor = 1;
            }
            $factor++;
        }

        if ($rest == 0) {
            $sum *= 10;
            $digito = $sum % 11;
            if ($digito == 10) {
                $digito = $whenTen;
            }
            return $digito;
        }
        return $sum % 11;
    }

    /**
     * @param     $n
     * @param int $earlyFactor
     * @param int $lastFactor
     *
     * @return int
     */
    protected function reverseModule11($n, $earlyFactor = 2, $lastFactor = 9) {
        $factor = $lastFactor;
        $sum = 0;
        for ($i = strlen($n); $i > 0; $i--) {
            $sum += substr($n, $i - 1, 1) * $factor;
            if (--$factor < $earlyFactor)
                $factor = $lastFactor;
        }

        $module = $sum % 11;
        if ($module > 9)
        {
            return 0;
        }

        return $module;
    }

    /**
     * @param $n
     *
     * @return int
     */
    protected function module10($n) {
        $chars = array_reverse(str_split($n, 1));
        $odd = array_intersect_key($chars, array_fill_keys(range(1, count($chars), 2), null));
        $even = array_intersect_key($chars, array_fill_keys(range(0, count($chars), 2), null));
        $even = array_map(function($n) { return ($n >= 5)?2 * $n - 9:2 * $n; }, $even);
        $total = array_sum($odd) + array_sum($even);
        return ((floor($total / 10) + 1) * 10 - $total) % 10;
    }
}