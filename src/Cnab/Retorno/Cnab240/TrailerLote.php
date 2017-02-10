<?php

namespace Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab240;

use \Eduardokum\LaravelBoleto\Contracts\Cnab\Retorno\Cnab240\TrailerLote as TrailerLoteContract;
use Eduardokum\LaravelBoleto\MagicTrait;

class TrailerLote implements TrailerLoteContract
{
    use MagicTrait;
    /**
     * @var integer
     */
    protected $loteServico;

    /**
     * @var integer
     */
    protected $TipoRegistro;

    /**
     * @var integer
     */
    protected $qtdRegistroLote;

    /**
     * @var integer
     */
    protected $qtdTitulosCobrancaSimples;

    /**
     * @var float
     */
    protected $valorTotalTitulosCobranca;

    /**
     * @var integer
     */
    protected $qtdTitulosCobrancaVinculada;

    /**
     * @var float
     */
    protected $valorTotalTitulosCobrancaVinculada;

    /**
     * @var integer
     */
    protected $qtdTitulosCobrancaCaucionada;

    /**
     * @var float
     */
    protected $valorTotalTitulosCobrancaCaucionada;

    /**
     * @var integer
     */
    protected $qtdTitulosCobrancaDescontada;

    /**
     * @var float
     */
    protected $valorTotalTitulosCobrancaDescontada;

    /**
     * @var integer
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
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * @return $this
     */
    public function setValorTotalTitulosCobrancaVinculada($valorTotalTitulosCobrancaVinculada)
    {
        $this->valorTotalTitulosCobrancaVinculada = $valorTotalTitulosCobrancaVinculada;

        return $this;
    }
}
