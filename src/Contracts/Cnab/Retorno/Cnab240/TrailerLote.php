<?php

namespace Eduardokum\LaravelBoleto\Contracts\Cnab\Retorno\Cnab240;

interface TrailerLote
{
    /**
     * @return mixed
     */
    public function getLoteServico();

    /**
     * @return mixed
     */
    public function getNumeroAvisoLancamento();

    /**
     * @return mixed
     */
    public function getQtdRegistroLote();

    /**
     * @return mixed
     */
    public function getQtdTitulosCobrancaCaucionada();

    /**
     * @return mixed
     */
    public function getQtdTitulosCobrancaDescontada();

    /**
     * @return mixed
     */
    public function getQtdTitulosCobrancaSimples();

    /**
     * @return mixed
     */
    public function getQtdTitulosCobrancaVinculada();

    /**
     * @return mixed
     */
    public function getTipoRegistro();

    /**
     * @return mixed
     */
    public function getValorTotalTitulosCobrancaSimples();

    /**
     * @return mixed
     */
    public function getValorTotalTitulosCobrancaCaucionada();

    /**
     * @return mixed
     */
    public function getValorTotalTitulosCobrancaDescontada();

    /**
     * @return mixed
     */
    public function getValorTotalTitulosCobrancaVinculada();

    /**
     * @return array
     */
    public function toArray();
}
