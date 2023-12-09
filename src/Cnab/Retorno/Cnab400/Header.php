<?php

namespace Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab400;

use Carbon\Carbon;
use Eduardokum\LaravelBoleto\MagicTrait;
use Eduardokum\LaravelBoleto\Contracts\Cnab\Retorno\Cnab400\Header as HeaderContract;

class Header implements HeaderContract
{
    use MagicTrait;

    /**
     * @var string
     */
    protected $operacaoCodigo;

    /**
     * @var string
     */
    protected $operacao;

    /**
     * @var string
     */
    protected $servicoCodigo;

    /**
     * @var string
     */
    protected $servico;

    /**
     * @var string
     */
    protected $agencia;

    /**
     * @var string
     */
    protected $agenciaDv;

    /**
     * @var string
     */
    protected $conta;

    /**
     * @var string
     */
    protected $contaDv;

    /**
     * @var Carbon
     */
    protected $data;

    /**
     * @var string
     */
    protected $convenio;

    /**
     * @var string
     */
    protected $codigoCliente;

    /**
     * @return string
     */
    public function getOperacaoCodigo()
    {
        return $this->operacaoCodigo;
    }

    /**
     * @param string $operacaoCodigo
     *
     * @return Header
     */
    public function setOperacaoCodigo($operacaoCodigo)
    {
        $this->operacaoCodigo = $operacaoCodigo;

        return $this;
    }

    /**
     * @return string
     */
    public function getOperacao()
    {
        return $this->operacao;
    }

    /**
     * @param string $operacao
     *
     * @return Header
     */
    public function setOperacao($operacao)
    {
        $this->operacao = $operacao;

        return $this;
    }

    /**
     * @return string
     */
    public function getServicoCodigo()
    {
        return $this->servicoCodigo;
    }

    /**
     * @param string $servicoCodigo
     *
     * @return Header
     */
    public function setServicoCodigo($servicoCodigo)
    {
        $this->servicoCodigo = $servicoCodigo;

        return $this;
    }

    /**
     * @return string
     */
    public function getServico()
    {
        return $this->servico;
    }

    /**
     * @param string $servico
     *
     * @return Header
     */
    public function setServico($servico)
    {
        $this->servico = $servico;

        return $this;
    }

    /**
     * @return string
     */
    public function getAgencia()
    {
        return $this->agencia;
    }

    /**
     * @param string $agencia
     *
     * @return Header
     */
    public function setAgencia($agencia)
    {
        $this->agencia = ltrim(trim($agencia, ' '), '0');

        return $this;
    }

    /**
     * @return string
     */
    public function getAgenciaDv()
    {
        return $this->agenciaDv;
    }

    /**
     * @param string $agenciaDv
     *
     * @return Header
     */
    public function setAgenciaDv($agenciaDv)
    {
        $this->agenciaDv = $agenciaDv;

        return $this;
    }

    /**
     * @return string
     */
    public function getConta()
    {
        return $this->conta;
    }

    /**
     * @param string $conta
     *
     * @return Header
     */
    public function setConta($conta)
    {
        $this->conta = ltrim(trim($conta, ' '), '0');

        return $this;
    }

    /**
     * @return string
     */
    public function getContaDv()
    {
        return $this->contaDv;
    }

    /**
     * @param string $contaDv
     *
     * @return Header
     */
    public function setContaDv($contaDv)
    {
        $this->contaDv = $contaDv;

        return $this;
    }

    /**
     * @param string $format
     *
     * @return string
     */
    public function getData($format = 'd/m/Y')
    {
        return $this->data instanceof Carbon
            ? $format === false ? $this->data : $this->data->format($format)
            : null;
    }

    /**
     * @param string $data
     *
     * @return Header
     */
    public function setData($data, $format = 'dmy')
    {
        $this->data = trim($data, '0 ') ? Carbon::createFromFormat($format, $data) : null;

        return $this;
    }

    /**
     * @return string
     */
    public function getConvenio()
    {
        return $this->convenio;
    }

    /**
     * @param string $convenio
     *
     * @return Header
     */
    public function setConvenio($convenio)
    {
        $this->convenio = $convenio;

        return $this;
    }

    /**
     * @return string
     */
    public function getCodigoCliente()
    {
        return $this->codigoCliente;
    }

    /**
     * @param string $codigoCliente
     *
     * @return Header
     */
    public function setCodigoCliente($codigoCliente)
    {
        $this->codigoCliente = ltrim(trim($codigoCliente, ' '), '0');

        return $this;
    }
}
