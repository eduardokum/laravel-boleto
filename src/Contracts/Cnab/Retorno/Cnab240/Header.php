<?php

namespace Eduardokum\LaravelBoleto\Contracts\Cnab\Retorno\Cnab240;

interface Header
{
    /**
     * @return string
     */
    public function getLoteServico();

    /**
     * @return string
     */
    public function getTipoRegistro();

    /**
     * @return string
     */
    public function getTipoInscricao();

    /**
     * @return string
     */
    public function getAgencia();

    /**
     * @return string
     */
    public function getAgenciaDv();

    /**
     * @return string
     */
    public function getNomeEmpresa();

    /**
     * @return string
     */
    public function getHoraGeracao();

    /**
     * @return string
     */
    public function getNumeroSequencialArquivo();

    /**
     * @return string
     */
    public function getVersaoLayoutArquivo();

    /**
     * @return string
     */
    public function getNumeroInscricao();

    /**
     * @return string
     */
    public function getConta();

    /**
     * @return string
     */
    public function getContaDv();

    /**
     * @return string
     */
    public function getCodigoCedente();

    /**
     * @param string $format
     *
     * @return string
     */
    public function getData($format = 'd/m/Y');

    /**
     * @return string
     */
    public function getConvenio();

    /**
     * @return int
     */
    public function getCodBanco();

    /**
     * @return int
     */
    public function getCodigoRemessaRetorno();

    /**
     * @return string
     */
    public function getNomeBanco();

    /**
     * @return array
     */
    public function toArray();
}
