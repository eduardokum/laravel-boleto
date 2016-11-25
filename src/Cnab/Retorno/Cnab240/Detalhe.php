<?php

namespace Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab240;

use Eduardokum\LaravelBoleto\Contracts\Cnab\Retorno\Cnab240\Detalhe as DetalheContract;
use Eduardokum\LaravelBoleto\Contracts\Cnab\Retorno\Cnab240\DetalheSegmentoT as DetalheSegmentoTContract;
use Eduardokum\LaravelBoleto\Contracts\Cnab\Retorno\Cnab240\DetalheSegmentoU as DetalheSegmentoUContract;
use Eduardokum\LaravelBoleto\Contracts\Cnab\Retorno\Cnab240\DetalheSegmentoY as DetalheSegmentoYContract;

class Detalhe implements DetalheContract
{
    /**
     * @var DetalheSegmentoTContract
     */
    private $segmentoT;

    /**
     * @var DetalheSegmentoUContract
     */
    private $segmentoU;

    /**
     * @var DetalheSegmentoYContract
     */
    private $segmentoY;

    /**
     * @var string
     */
    private $valorPagoSacado;

    /**
     * @var string
     */
    private $ocorrenciaTipo;

    /**
     * @var string
     */
    private $ocorrenciaDescricao;

    /**
     * @var string
     */
    private $ocorrencia;

    /**
     * @var string
     */
    private $codigoOcorrencia;

    /**
     * @var string
     */
    private $error;

    /**
     * @var string
     */
    private $errorCode;

    /**
     * @return DetalheSegmentoTContract
     */
    public function getSegmentoT()
    {
        return $this->segmentoT;
    }

    /**
     * @param DetalheSegmentoTContract $segmentoT
     */
    public function setSegmentoT(DetalheSegmentoTContract $segmentoT)
    {
        $this->segmentoT = $segmentoT;

        return $this;
    }

    /**
     * @return DetalheSegmentoUContract
     */
    public function getSegmentoU()
    {
        return $this->segmentoU;
    }

    /**
     * @param DetalheSegmentoUContract $segmentoU
     */
    public function setSegmentoU(DetalheSegmentoUContract $segmentoU)
    {
        $this->segmentoU = $segmentoU;

        return $this;
    }

    /**
     * @return DetalheSegmentoYContract
     */
    public function getSegmentoY()
    {
        return $this->segmentoY;
    }

    /**
     * @param DetalheSegmentoYContract $segmentoY
     */
    public function setSegmentoY(DetalheSegmentoYContract $segmentoY)
    {
        $this->segmentoY = $segmentoY;

        return $this;
    }

    /**
     * @return string
     */
    public function getValorPagoSacado()
    {
        return $this->valorPagoSacado;
    }

    /**
     * @param mixed $valorPagoSacado
     */
    public function setValorPagoSacado($valorPagoSacado)
    {
        $this->valorPagoSacado = $valorPagoSacado;

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
     * @return string
     */
    public function getCodigoOcorrencia()
    {
        return $this->codigoOcorrencia;
    }

    /**
     * @param mixed $codigoOcorrencia
     */
    public function setCodigoOcorrencia($codigoOcorrencia)
    {
        $this->codigoOcorrencia = $codigoOcorrencia;

        return $this;
    }


    /**
     * @return string
     */
    public function getOcorrencia()
    {
        return $this->ocorrencia;
    }

    /**
     * @param string $ocorrencia
     *
     * @return Detalhe
     */
    public function setOcorrencia($ocorrencia)
    {
        $this->ocorrencia = sprintf('%02s', $ocorrencia);

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
     * @return string
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * @param string $errorCode
     */
    public function setErrorCode($errorCode)
    {
        $this->errorCode = $errorCode;
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
     * @param  string $key
     * @return bool
     */
    public function __isset($key)
    {
        return isset($this->$key);
    }
}
