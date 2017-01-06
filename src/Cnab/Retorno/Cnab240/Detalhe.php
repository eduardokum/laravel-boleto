<?php

namespace Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab240;

use Eduardokum\LaravelBoleto\Contracts\Cnab\Retorno\Cnab240\Detalhe as DetalheContract;
use Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab240\DetalheSegmentoT as DetalheSegmentoTContract;
use Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab240\DetalheSegmentoU as DetalheSegmentoUContract;
use Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab240\DetalheSegmentoY as DetalheSegmentoYContract;

class Detalhe implements DetalheContract
{
    use MagicTrait;
    /**
     * @var DetalheSegmentoTContract
     */
    protected $segmentoT;

    /**
     * @var DetalheSegmentoUContract
     */
    protected $segmentoU;

    /**
     * @var DetalheSegmentoYContract
     */
    protected $segmentoY;

    /**
     * @var string
     */
    protected $valorPagoSacado;

    /**
     * @var string
     */
    protected $ocorrenciaTipo;

    /**
     * @var string
     */
    protected $ocorrenciaDescricao;

    /**
     * @var string
     */
    protected $ocorrencia;

    /**
     * @var string
     */
    protected $codigoOcorrencia;

    /**
     * @var string
     */
    protected $error;

    /**
     * @var string
     */
    protected $errorCode;

    /**
     * @return DetalheSegmentoTContract
     */
    public function getSegmentoT()
    {
        return $this->segmentoT;
    }

    /**
     * @param DetalheSegmentoTContract $segmentoT
     *
     * @return $this
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
     *
     * @return $this
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
     *
     * @return $this
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
     *
     * @return $this
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
     * @return $this
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
     * @return $this
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
     *
     * @return $this
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
     * @return $this
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
     * @return $this
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
}
