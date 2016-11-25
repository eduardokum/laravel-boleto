<?php

namespace Eduardokum\LaravelBoleto\Contracts\Cnab\Retorno\Cnab240;

interface HeaderLote
{
    /**
     * @return mixed
     */
    public function getTipoRegistro();

    /**
     * @return mixed
     */
    public function getTipoOperacao();

    /**
     * @return mixed
     */
    public function getTipoServico();

    /**
     * @return mixed
     */
    public function getVersaoLayoutLote();

    /**
     * @return mixed
     */
    public function getCodigoInscricao();

    /**
     * @return mixed
     */
    public function getNumeroInscricao();

    /**
     * @return mixed
     */
    public function getCodigoCedente();

    /**
     * @return mixed
     */
    public function getNomeEmpresa();

    /**
     * @return mixed
     */
    public function getAgencia();

    /**
     * @return mixed
     */
    public function getAgenciaDigito();

    /**
     * @return mixed
     */
    public function getConta();

    /**
     * @return string
     */
    public function getNumeroRetorno();

    /**
     * @return mixed
     */
    public function getContaDigito();

    /**
     * @param string $format
     *
     * @return string
     */
    public function getDataGravacao($format = 'd/m/Y');

    /**
     * @return array
     */
    public function toArray();

}