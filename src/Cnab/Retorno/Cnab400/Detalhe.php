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

namespace Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab400;

use Carbon\Carbon;
use Eduardokum\LaravelBoleto\Contracts\Cnab\Retorno\Cnab400\Detalhe as DetalheContract;

class Detalhe implements DetalheContract
{

    /**
     * @var string
     */
    private $nossoNumero;
    /**
     * @var string
     */
    private $numeroDocumento;
    /**
     * @var string
     */
    private $ocorrencia;
    /**
     * @var string
     */
    private $ocorrenciaTipo;
    /**
     * @var string
     */
    private $ocorrenciaDescricao;
    /**
     * @var Carbon
     */
    private $dataOcorrencia;
    /**
     * @var Carbon
     */
    private $dataVencimento;
    /**
     * @var Carbon
     */
    private $dataCredito;
    /**
     * @var string
     */
    private $valor;
    /**
     * @var string
     */
    private $valorTarifa;
    /**
     * @var string
     */
    private $valorIOF;
    /**
     * @var string
     */
    private $valorAbatimento;
    /**
     * @var string
     */
    private $valorDesconto;
    /**
     * @var string
     */
    private $valorRecebido;
    /**
     * @var string
     */
    private $valorMora;
    /**
     * @var string
     */
    private $valorMulta;
    /**
     * @var string
     */
    private $error;

    /**
     * @return mixed
     */
    public function getNossoNumero()
    {
        return $this->nossoNumero;
    }

    /**
     * @param mixed $nossoNumero
     *
     * @return Detalhe
     */
    public function setNossoNumero($nossoNumero)
    {
        $this->nossoNumero = $nossoNumero;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getNumeroDocumento()
    {
        return $this->numeroDocumento;
    }

    /**
     * @param mixed $numeroDocumento
     *
     * @return Detalhe
     */
    public function setNumeroDocumento($numeroDocumento)
    {
        $this->numeroDocumento = ltrim(trim($numeroDocumento, ' '), '0');

        return $this;
    }

    /**
     * @return boolean
     */
    public function hasOcorrencia()
    {
        $ocorrencias = func_get_args();

        if (count($ocorrencias) == 0 && empty($this->getOcorrencia())) {
            return true;
        }

        if (count($ocorrencias) == 1 && is_array(func_get_arg(0))) {
            $ocorrencias = func_get_arg(0);
        }

        if (in_array($this->getOcorrencia(), $ocorrencias)) {
            return true;
        }

        return false;
    }

    /**
     * @return mixed
     */
    public function getOcorrencia()
    {
        return $this->ocorrencia;
    }

    /**
     * @param mixed $ocorrencia
     *
     * @return Detalhe
     */
    public function setOcorrencia($ocorrencia)
    {
        $this->ocorrencia = sprintf('%02s', $ocorrencia);

        return $this;
    }

    /**
     * @return string
     */
    public function getOcorrenciaDescricao()
    {
        return $this->ocorrenciaDescricao;
    }

    /**
     * @param string $ocorrenciaDescricao
     *
     * @return Detalhe
     */
    public function setOcorrenciaDescricao($ocorrenciaDescricao)
    {
        $this->ocorrenciaDescricao = $ocorrenciaDescricao;

        return $this;
    }

    /**
     * @return string
     */
    public function getOcorrenciaTipo()
    {
        return $this->ocorrenciaTipo;
    }

    /**
     * @param string $ocorrenciaTipo
     *
     * @return Detalhe
     */
    public function setOcorrenciaTipo($ocorrenciaTipo)
    {
        $this->ocorrenciaTipo = $ocorrenciaTipo;

        return $this;
    }

    /**
     * @param string $format
     *
     * @return mixed
     */
    public function getDataOcorrencia($format = 'd/m/Y')
    {
        return $this->dataOcorrencia instanceof Carbon
        ? $format === false ? $this->dataOcorrencia : $this->dataOcorrencia->format($format)
        : null;
    }

    /**
     * @param mixed $dataOcorrencia
     *
     * @return Detalhe
     */
    public function setDataOcorrencia($dataOcorrencia)
    {
        $this->dataOcorrencia = trim($dataOcorrencia, '0 ') ? Carbon::createFromFormat('dmy', $dataOcorrencia) : null;

        return $this;
    }

    /**
     * @param string $format
     *
     * @return mixed
     */
    public function getDataVencimento($format = 'd/m/Y')
    {
        return $this->dataVencimento instanceof Carbon
        ? $format === false ? $this->dataVencimento : $this->dataVencimento->format($format)
        : null;
    }

    /**
     * @param mixed $dataVencimento
     *
     * @return Detalhe
     */
    public function setDataVencimento($dataVencimento)
    {
        $this->dataVencimento = trim($dataVencimento, '0 ') ? Carbon::createFromFormat('dmy', $dataVencimento) : null;

        return $this;
    }

    /**
     * @param string $format
     *
     * @return mixed
     */
    public function getDataCredito($format = 'd/m/Y')
    {
        return $this->dataCredito instanceof Carbon
        ? $format === false ? $this->dataCredito : $this->dataCredito->format($format)
        : null;
    }

    /**
     * @param mixed $dataCredito
     *
     * @return Detalhe
     */
    public function setDataCredito($dataCredito)
    {
        $this->dataCredito = trim($dataCredito, '0 ') ? Carbon::createFromFormat('dmy', $dataCredito) : null;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getValor()
    {
        return $this->valor;
    }

    /**
     * @param mixed $valor
     *
     * @return Detalhe
     */
    public function setValor($valor)
    {
        $this->valor = $valor;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getValorTarifa()
    {
        return $this->valorTarifa;
    }

    /**
     * @param mixed $valorTarifa
     *
     * @return Detalhe
     */
    public function setValorTarifa($valorTarifa)
    {
        $this->valorTarifa = $valorTarifa;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getValorIOF()
    {
        return $this->valorIOF;
    }

    /**
     * @param mixed $valorIOF
     *
     * @return Detalhe
     */
    public function setValorIOF($valorIOF)
    {
        $this->valorIOF = $valorIOF;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getValorAbatimento()
    {
        return $this->valorAbatimento;
    }

    /**
     * @param mixed $valorAbatimento
     *
     * @return Detalhe
     */
    public function setValorAbatimento($valorAbatimento)
    {
        $this->valorAbatimento = $valorAbatimento;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getValorDesconto()
    {
        return $this->valorDesconto;
    }

    /**
     * @param mixed $valorDesconto
     *
     * @return Detalhe
     */
    public function setValorDesconto($valorDesconto)
    {
        $this->valorDesconto = $valorDesconto;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getValorRecebido()
    {
        return $this->valorRecebido;
    }

    /**
     * @param mixed $valorRecebido
     *
     * @return Detalhe
     */
    public function setValorRecebido($valorRecebido)
    {
        $this->valorRecebido = $valorRecebido;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getValorMora()
    {
        return $this->valorMora;
    }

    /**
     * @param mixed $valorMora
     *
     * @return Detalhe
     */
    public function setValorMora($valorMora)
    {
        $this->valorMora = $valorMora;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getValorMulta()
    {
        return $this->valorMulta;
    }

    /**
     * @param mixed $valorMulta
     *
     * @return Detalhe
     */
    public function setValorMulta($valorMulta)
    {
        $this->valorMulta = $valorMulta;

        return $this;
    }

    /**
     * Retorna se tem erro.
     *
     * @return bool
     */
    public function hasError()
    {
        return $this->getOcorrencia() == self::OCORRENCIA_ERRO;
    }

    /**
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @param string $error
     *
     * @return Detalhe
     */
    public function setError($error)
    {
        $this->ocorrenciaTipo = self::OCORRENCIA_ERRO;
        $this->error = $error;

        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $vars = array_keys(get_class_vars(self::class));
        $aRet = [];
        foreach ($vars as $var) {
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
        if (property_exists($this, $name)) {
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
        if (property_exists($this, $name)) {
            $method = 'get' . ucwords($name);
            return $this->{$method}();
        }
    }

    /**
     * Determine if an attribute exists on the detalhe.
     *
     * @param  string  $key
     * @return bool
     */
    public function __isset($key)
    {
        return isset($this->$key);
    }
}
