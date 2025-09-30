<?php

namespace Eduardokum\LaravelBoleto\Cnab\Pagamento\Retorno\Cnab240;

use Eduardokum\LaravelBoleto\MagicTrait;

class Header
{
    use MagicTrait;

    /**
     * @var string
     */
    protected $codBanco;

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
    protected $tipoInscricao;

    /**
     * @var string
     */
    protected $numeroInscricao;

    /**
     * @var string
     */
    protected $convenio;

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
     * @var string
     */
    protected $nomeEmpresa;

    /**
     * @var string
     */
    protected $nomeBanco;

    /**
     * @var string
     */
    protected $codigoRemessaRetorno;

    /**
     * @var string
     */
    protected $data;

    /**
     * @var string
     */
    protected $hora;

    /**
     * @var string
     */
    protected $numeroSequencialArquivo;

    /**
     * @var string
     */
    protected $versaoLayoutArquivo;

    /**
     * Setters que retornam $this para method chaining
     */
    public function setCodBanco($value)
    {
        $this->codBanco = $value;
        return $this;
    }

    public function setLoteServico($value)
    {
        $this->loteServico = $value;
        return $this;
    }

    public function setTipoRegistro($value)
    {
        $this->tipoRegistro = $value;
        return $this;
    }

    public function setTipoInscricao($value)
    {
        $this->tipoInscricao = $value;
        return $this;
    }

    public function setNumeroInscricao($value)
    {
        $this->numeroInscricao = $value;
        return $this;
    }

    public function setConvenio($value)
    {
        $this->convenio = $value;
        return $this;
    }

    public function setAgencia($value)
    {
        $this->agencia = $value;
        return $this;
    }

    public function setAgenciaDv($value)
    {
        $this->agenciaDv = $value;
        return $this;
    }

    public function setConta($value)
    {
        $this->conta = $value;
        return $this;
    }

    public function setContaDv($value)
    {
        $this->contaDv = $value;
        return $this;
    }

    public function setNomeEmpresa($value)
    {
        $this->nomeEmpresa = $value;
        return $this;
    }

    public function setNomeBanco($value)
    {
        $this->nomeBanco = $value;
        return $this;
    }

    public function setCodigoRemessaRetorno($value)
    {
        $this->codigoRemessaRetorno = $value;
        return $this;
    }

    public function setData($value)
    {
        $this->data = $value;
        return $this;
    }

    public function setHora($value)
    {
        $this->hora = $value;
        return $this;
    }

    public function setNumeroSequencialArquivo($value)
    {
        $this->numeroSequencialArquivo = $value;
        return $this;
    }

    public function setVersaoLayoutArquivo($value)
    {
        $this->versaoLayoutArquivo = $value;
        return $this;
    }
}
