<?php

namespace Eduardokum\LaravelBoleto\Cnab\Pagamento\Retorno\Cnab240;

use Eduardokum\LaravelBoleto\MagicTrait;

class HeaderLote
{
    use MagicTrait;

    protected $codBanco;
    protected $numeroLoteRetorno;
    protected $tipoRegistro;
    protected $tipoOperacao;
    protected $tipoServico;
    protected $formaLancamento;
    protected $versaoLayoutLote;
    protected $tipoInscricao;
    protected $numeroInscricao;
    protected $convenio;
    protected $agencia;
    protected $agenciaDv;
    protected $conta;
    protected $contaDv;
    protected $nomeEmpresa;
    protected $dataGravacao;
    protected $numeroRetorno;

    // Setters que retornam $this para method chaining
    public function setCodBanco($v)
    {
        $this->codBanco = $v;
        return $this;
    }
    public function setNumeroLoteRetorno($v)
    {
        $this->numeroLoteRetorno = $v;
        return $this;
    }
    public function setTipoRegistro($v)
    {
        $this->tipoRegistro = $v;
        return $this;
    }
    public function setTipoOperacao($v)
    {
        $this->tipoOperacao = $v;
        return $this;
    }
    public function setTipoServico($v)
    {
        $this->tipoServico = $v;
        return $this;
    }
    public function setFormaLancamento($v)
    {
        $this->formaLancamento = $v;
        return $this;
    }
    public function setVersaoLayoutLote($v)
    {
        $this->versaoLayoutLote = $v;
        return $this;
    }
    public function setTipoInscricao($v)
    {
        $this->tipoInscricao = $v;
        return $this;
    }
    public function setNumeroInscricao($v)
    {
        $this->numeroInscricao = $v;
        return $this;
    }
    public function setConvenio($v)
    {
        $this->convenio = $v;
        return $this;
    }
    public function setAgencia($v)
    {
        $this->agencia = $v;
        return $this;
    }
    public function setAgenciaDv($v)
    {
        $this->agenciaDv = $v;
        return $this;
    }
    public function setConta($v)
    {
        $this->conta = $v;
        return $this;
    }
    public function setContaDv($v)
    {
        $this->contaDv = $v;
        return $this;
    }
    public function setNomeEmpresa($v)
    {
        $this->nomeEmpresa = $v;
        return $this;
    }
    public function setDataGravacao($v)
    {
        $this->dataGravacao = $v;
        return $this;
    }
    public function setNumeroRetorno($v)
    {
        $this->numeroRetorno = $v;
        return $this;
    }
}
