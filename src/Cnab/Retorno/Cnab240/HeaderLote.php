<?php

namespace Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab240;

use Carbon\Carbon;
use Eduardokum\LaravelBoleto\Contracts\Cnab\Retorno\Cnab240\HeaderLote as HeaderLoteContract;

class HeaderLote implements HeaderLoteContract
{
    /**
     * @var string
     */
    protected $codBanco;

    /**
     * @var string
     */
    protected $numeroLoteRetorno;


    /**
     * @var string
     */
    protected $loteServico;

    /**
     * @var string
     */
    protected $tipoRegistro;

    /**
     * @var string
     */
    protected $tipoOperacao;

    /**
     * @var string
     */
    protected $tipoServico;

    /**
     * @var string
     */
    protected $versaoLayoutLote;

    /**
     * @var string
     */
    protected $codigoInscricao;

    /**
     * @var string
     */
    protected $numeroInscricao;

    /**
     * @var string
     */
    protected $agenciaDigito;

    /**
     * @var string
     */
    protected $codigoCedente;

    /**
     * @var string
     */
    protected $nomeEmpresa;

    /**
     * @var string
     */
    protected $numeroRetorno;

    /**
     * @var string
     */
    protected $dataGravacao;

    /**
     * @var string
     */
    protected $agencia;

    /**
     * @var string
     */
    protected $conta;

    /**
     * @var string
     */
    protected $contaDigito;

    /**
     * @return string
     */
    public function getCodigoBanco()
    {
        return $this->codigoBanco;
    }

    /**
     * @param string $codigoBanco
     *
     * @return Header
     */
    public function setCodigoBanco($codigoBanco)
    {
        $this->codigoBanco = $codigoBanco;

        return $this;
    }

    /**
     * @return string
     */
    public function getLoteServico()
    {
        return $this->loteServico;
    }

    /**
     * @param string $loteServico
     *
     * @return Header
     */
    public function setLoteServico($loteServico)
    {
        $this->loteServico = $loteServico;

        return $this;
    }


    /**
     * @return string
     */
    public function getTipoRegistro()
    {
        return $this->tipoRegistro;
    }

    /**
     * @param string $tipoRegistro
     *
     * @return Header
     */
    public function setTipoRegistro($tipoRegistro)
    {
        $this->tipoRegistro = $tipoRegistro;

        return $this;
    }

    /**
     * @return string
     */
    public function getCodBanco()
    {
        return $this->codBanco;
    }

    /**
     * @param string $codBanco
     */
    public function setCodBanco($codBanco)
    {
        $this->codBanco = $codBanco;

        return $this;
    }

    /**
     * @return string
     */
    public function getNumeroLoteRetorno()
    {
        return $this->numeroLoteRetorno;
    }

    /**
     * @param string $numeroLoteRetorno
     */
    public function setNumeroLoteRetorno($numeroLoteRetorno)
    {
        $this->numeroLoteRetorno = $numeroLoteRetorno;

        return $this;
    }

    /**
     * @return string
     */
    public function getTipoOperacao()
    {
        return $this->tipoOperacao;
    }

    /**
     * @param string $tipoRegistro
     *
     * @return Header
     */
    public function setTipoOperacao($tipoOperacao)
    {
        $this->tipoOperacao = $tipoOperacao;

        return $this;
    }


    /**
     * @return string
     */
    public function getTipoServico()
    {
        return $this->tipoServico;
    }

    /**
     * @param string $tipoServico
     *
     * @return Header
     */
    public function setTipoServico($tipoServico)
    {
        $this->tipoServico = $tipoServico;

        return $this;
    }


    /**
     * @return string
     */
    public function getVersaoLayoutLote()
    {
        return $this->versaoLayoutLote;
    }

    /**
     * @param string $versaoLayoutLote
     *
     * @return Header
     */
    public function setVersaoLayoutLote($versaoLayoutLote)
    {
        $this->versaoLayoutLote = $versaoLayoutLote;

        return $this;
    }


    /**
     * @return string
     */
    public function getCodigoInscricao()
    {
        return $this->codigoInscricao;
    }

    /**
     * @param string $versaoLayoutLote
     *
     * @return Header
     */
    public function setCodigoInscricao($codigoInscricao)
    {
        $this->codigoInscricao = $codigoInscricao;

        return $this;
    }


    /**
     * @return string
     */
    public function getNumeroInscricao()
    {
        return $this->numeroInscricao;
    }

    /**
     * @param string $numeroInscricao
     *
     * @return Header
     */
    public function setNumeroInscricao($numeroInscricao)
    {
        $this->numeroInscricao = $numeroInscricao;

        return $this;
    }

    /**
     * @return string
     */
    public function getCodigoCedente()
    {
        return $this->codigoCedente;
    }

    /**
     * @param string $codigoCedente
     *
     * @return Header
     */
    public function setCodigoCedente($codigoCedente)
    {
        $this->codigoCedente = $codigoCedente;

        return $this;
    }

    /**
     * @return string
     */
    public function getNomeEmpresa()
    {
        return $this->nomeEmpresa;
    }

    /**
     * @param string $nomeEmpresa
     *
     * @return Header
     */
    public function setNomeEmpresa($nomeEmpresa)
    {
        $this->nomeEmpresa = $nomeEmpresa;

        return $this;
    }


    /**
     * @return string
     */
    public function getMensagem1()
    {
        return $this->mensagem_1;
    }

    /**
     * @param string $format
     *
     * @return string
     */
    public function getDataGravacao($format = 'd/m/Y')
    {
        return $this->dataGravacao instanceof Carbon
            ? $format === false ? $this->dataGravacao : $this->dataGravacao->format($format)
            : null;
    }

    /**
     * @param string $dataGravacao
     *
     * @return Header
     */
    public function setDataGravacao($dataGravacao)
    {
        $this->dataGravacao = trim($dataGravacao, '0 ') ? Carbon::createFromFormat('dmy', $dataGravacao) : null;

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
        $this->agencia = $agencia;

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
        $this->conta = $conta;

        return $this;
    }

    /**
     * @return string
     */
    public function getNumeroRetorno()
    {
        return $this->numeroRetorno;
    }

    /**
     * @param string $numeroRetorno
     */
    public function setNumeroRetorno($numeroRetorno)
    {
        $this->numeroRetorno = $numeroRetorno;

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


}