<?php
/**
 *   Copyright (c) 2016 Eduardo Gusmão
 *
 *   Permission is hereby granted, free of charge, to any person obtaining a
 *   copy of this software and associated documentation files (the "Software"),
 *   to deal in the Software without restriction, including without limitation
 *   the rights to use, copy, modify, merge, publish, distribute, sublicense,
 *   and/or sell copies of the Software, and to permit persons to whom the
 *   Software is furnished to do so, subject to the following conditions:
 *
 *   The above copyright notice and this permission notice shall be included in all
 *   copies or substantial portions of the Software.
 *
 *   THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 *   INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR
 *   PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 *   COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 *   WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR
 *   IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace Eduardokum\LaravelBoleto\Cnab\Retorno;

use Carbon\Carbon;
use Eduardokum\LaravelBoleto\Contracts\Cnab\Retorno\Header as HeaderContract;

class Header implements HeaderContract
{

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
    protected $agenciaDigito;
    /**
     * @var string
     */
    protected $conta;
    /**
     * @var string
     */
    protected $contaDigito;
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
    public function getAgenciaDigito()
    {
        return $this->agenciaDigito;
    }

    /**
     * @param string $agenciaDigito
     *
     * @return Header
     */
    public function setAgenciaDigito($agenciaDigito)
    {
        $this->agenciaDigito = $agenciaDigito;

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
    public function getContaDigito()
    {
        return $this->contaDigito;
    }

    /**
     * @param string $contaDigito
     *
     * @return Header
     */
    public function setContaDigito($contaDigito)
    {
        $this->contaDigito = $contaDigito;

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
    public function setData($data)
    {
        $this->data = trim($data, '0 ') ? Carbon::createFromFormat('dmy', $data) : null;

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

    /**
     * @return array
     */
    public function toArray()
    {
        $vars = array_keys(get_class_vars(self::class));
        $aRet = [];
        foreach($vars as $var)
        {
            $methodName = 'get' . ucfirst($var);
            $aRet[$var] = method_exists($this, $methodName)
                ? $this->$methodName()
                : $this->$var;
        }
        return $aRet;
    }

    /**
     * Fast set method.
     *
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        if(property_exists($this, $name))
        {
            $this->$name = $value;
        }
    }

    /**
     * Fast get method.
     *
     * @param $name
     */
    public function __get($name)
    {
        if(property_exists($this, $name))
        {
            $method = 'get' . ucwords($name);
            return $this->{$method}();
        }
    }

    /**
     * Determine if an attribute exists on the header.
     *
     * @param  string  $key
     * @return bool
     */
    public function __isset($key)
    {
        return isset($this->$key);
    }
}