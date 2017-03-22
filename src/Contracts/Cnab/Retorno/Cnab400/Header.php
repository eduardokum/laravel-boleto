<?php
namespace Eduardokum\LaravelBoleto\Contracts\Cnab\Retorno\Cnab400;

interface Header
{
    /**
     * @return mixed
     */
    public function getOperacaoCodigo();

    /**
     * @return mixed
     */
    public function getOperacao();

    /**
     * @return mixed
     */
    public function getServicoCodigo();

    /**
     * @return mixed
     */
    public function getServico();

    /**
     * @return mixed
     */
    public function getAgencia();

    /**
     * @return mixed
     */
    public function getAgenciaDv();

    /**
     * @return mixed
     */
    public function getConta();

    /**
     * @return mixed
     */
    public function getContaDv();

    /**
     * @param string $format
     *
     * @return \Carbon\Carbon
     */
    public function getData($format = 'd/m/Y');

    /**
     * @return mixed
     */
    public function getConvenio();

    /**
     * @return mixed
     */
    public function getCodigoCliente();

    /**
     * @return array
     */
    public function toArray();
}
