<?php

namespace Eduardokum\LaravelBoleto\Contracts\Cnab\Retorno\Cnab240;


interface DetalheSegmentoU
{

    /**
     * @return mixed
     */
    public function getLoteServico();

    /**
     * @return mixed
     */
    public function getJurosMultaEncargos();

    /**
     * @return mixed
     */
    public function getDescontoConcedido();

    /**
     * @return mixed
     */
    public function getValorPagoSacado();

    /**
     * @return mixed
     */
    public function getValorLiquidoCreditado();

    /**
     * @return mixed
     */
    public function getValorOutrasDespesas();

    /**
     * @return mixed
     */
    public function getValorOutrosCreditos();

    /**
     * @return mixed
     */
    public function getCodigoOcorrenciaSacado();

    /**
     * @return mixed
     */
    public function getDataOcorrenciaSacado();

    /**
     * @return mixed
     */
    public function getValorOcorrenciaSacado();

    /**
     * @return mixed
     */
    public function getComplementoOcorrenciaSacado();

}