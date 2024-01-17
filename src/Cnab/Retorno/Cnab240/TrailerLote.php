<?php

namespace Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab240;

use Eduardokum\LaravelBoleto\MagicTrait;
use Eduardokum\LaravelBoleto\Contracts\Cnab\Retorno\Cnab240\TrailerLote as TrailerLoteContract;

class TrailerLote implements TrailerLoteContract
{
    use MagicTrait;

    /**
     * @var int
     */
    protected $loteServico;

    /**
     * @var int
     */
    protected $TipoRegistro;

    /**
     * @var int
     */
    protected $qtdRegistroLote;

    /**
     * @var int
     */
    protected $qtdTitulosCobrancaSimples;

    /**
     * @var float
     */
    protected $valorTotalTitulosCobranca;

    /**
     * @var int
     */
    protected $qtdTitulosCobrancaVinculada;

    /**
     * @var float
     */
    protected $valorTotalTitulosCobrancaVinculada;

    /**
     * @var int
     */
    protected $qtdTitulosCobrancaCaucionada;

    /**
     * @var float
     */
    protected $valorTotalTitulosCobrancaCaucionada;

    /**
     * @var int
     */
    protected $qtdTitulosCobrancaDescontada;

    /**
     * @var float
     */
    protected $valorTotalTitulosCobrancaDescontada;

    /**
     * @var int
     */
    protected $numeroAvisoLancamento;

    /**
     * @return mixed
     */
    public function getLoteServico()
    {
        return $this->loteServico;
    }

    /**
     * @param mixed $loteServico
     *
     * @return TrailerLote
     */
    public function setLoteServico($loteServico)
    {
        $this->loteServico = $loteServico;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getNumeroAvisoLancamento()
    {
        return $this->numeroAvisoLancamento;
    }

    /**
     * @param mixed $numeroAvisoLancamento
     *
     * @return TrailerLote
     */
    public function setNumeroAvisoLancamento($numeroAvisoLancamento)
    {
        $this->numeroAvisoLancamento = $numeroAvisoLancamento;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getQtdRegistroLote()
    {
        return $this->qtdRegistroLote;
    }

    /**
     * @param mixed $qtdRegistroLote
     *
     * @return TrailerLote
     */
    public function setQtdRegistroLote($qtdRegistroLote)
    {
        $this->qtdRegistroLote = $qtdRegistroLote;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getQtdTitulosCobrancaCaucionada()
    {
        return $this->qtdTitulosCobrancaCaucionada;
    }

    /**
     * @param mixed $qtdTitulosCobrancaCaucionada
     *
     * @return TrailerLote
     */
    public function setQtdTitulosCobrancaCaucionada($qtdTitulosCobrancaCaucionada)
    {
        $this->qtdTitulosCobrancaCaucionada = $qtdTitulosCobrancaCaucionada;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getQtdTitulosCobrancaDescontada()
    {
        return $this->qtdTitulosCobrancaDescontada;
    }

    /**
     * @param mixed $qtdTitulosCobrancaDescontada
     *
     * @return TrailerLote
     */
    public function setQtdTitulosCobrancaDescontada($qtdTitulosCobrancaDescontada)
    {
        $this->qtdTitulosCobrancaDescontada = $qtdTitulosCobrancaDescontada;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getQtdTitulosCobrancaSimples()
    {
        return $this->qtdTitulosCobrancaSimples;
    }

    /**
     * @param mixed $qtdTitulosCobrancaSimples
     *
     * @return TrailerLote
     */
    public function setQtdTitulosCobrancaSimples($qtdTitulosCobrancaSimples)
    {
        $this->qtdTitulosCobrancaSimples = $qtdTitulosCobrancaSimples;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getQtdTitulosCobrancaVinculada()
    {
        return $this->qtdTitulosCobrancaVinculada;
    }

    /**
     * @param mixed $qtdTitulosCobrancaVinculada
     *
     * @return TrailerLote
     */
    public function setQtdTitulosCobrancaVinculada($qtdTitulosCobrancaVinculada)
    {
        $this->qtdTitulosCobrancaVinculada = $qtdTitulosCobrancaVinculada;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTipoRegistro()
    {
        return $this->TipoRegistro;
    }

    /**
     * @param mixed $TipoRegistro
     *
     * @return TrailerLote
     */
    public function setTipoRegistro($TipoRegistro)
    {
        $this->TipoRegistro = $TipoRegistro;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getValorTotalTitulosCobrancaSimples()
    {
        return $this->valorTotalTitulosCobranca;
    }

    /**
     * @param mixed $valorTotalTitulosCobranca
     *
     * @return TrailerLote
     */
    public function setValorTotalTitulosCobrancaSimples($valorTotalTitulosCobranca)
    {
        $this->valorTotalTitulosCobranca = $valorTotalTitulosCobranca;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getValorTotalTitulosCobrancaCaucionada()
    {
        return $this->valorTotalTitulosCobrancaCaucionada;
    }

    /**
     * @param mixed $valorTotalTitulosCobrancaCaucionada
     *
     * @return TrailerLote
     */
    public function setValorTotalTitulosCobrancaCaucionada($valorTotalTitulosCobrancaCaucionada)
    {
        $this->valorTotalTitulosCobrancaCaucionada = $valorTotalTitulosCobrancaCaucionada;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getValorTotalTitulosCobrancaDescontada()
    {
        return $this->valorTotalTitulosCobrancaDescontada;
    }

    /**
     * @param mixed $valorTotalTitulosCobrancaDescontada
     *
     * @return TrailerLote
     */
    public function setValorTotalTitulosCobrancaDescontada($valorTotalTitulosCobrancaDescontada)
    {
        $this->valorTotalTitulosCobrancaDescontada = $valorTotalTitulosCobrancaDescontada;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getValorTotalTitulosCobrancaVinculada()
    {
        return $this->valorTotalTitulosCobrancaVinculada;
    }

    /**
     * @param mixed $valorTotalTitulosCobrancaVinculada
     *
     * @return TrailerLote
     */
    public function setValorTotalTitulosCobrancaVinculada($valorTotalTitulosCobrancaVinculada)
    {
        $this->valorTotalTitulosCobrancaVinculada = $valorTotalTitulosCobrancaVinculada;

        return $this;
    }
}
