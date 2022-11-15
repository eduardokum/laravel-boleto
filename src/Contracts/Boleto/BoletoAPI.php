<?php
namespace Eduardokum\LaravelBoleto\Contracts\Boleto;

use Eduardokum\LaravelBoleto\Boleto\AbstractBoleto;

interface BoletoAPI extends Boleto
{
    /**
     * Return boleto as a Array.
     *
     * @return array
     */
    public function toArrayAPI();

    /**
     * @param $boleto
     * @param $appends
     *
     * @return AbstractBoleto
     */
    public static function createFromAPI($boleto, $appends);
}

