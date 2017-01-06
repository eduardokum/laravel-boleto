<?php

namespace Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab240;

use Eduardokum\LaravelBoleto\Contracts\Cnab\Retorno\Cnab240\DetalheSegmentoY as DetalheSegmentoYContract;

class DetalheSegmentoY implements DetalheSegmentoYContract
{
    use MagicTrait;
    /**
     * @var string
     */
    protected $codigoBancoCompensacao;

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
    protected $numeroSequencialRegistroLote;

    /**
     * @var string
     */
    protected $codigoSegmentoRegistroDetalhe;

    /**
     * @var string
     */
    protected $codigoOcorrencia;

    /**
     * @var string
     */
    protected $identificacaoRegistroOpcional;

    /**
     * @var string
     */
    protected $identificacaoCheque;

    /**
     * @return mixed
     */
    public function getCodigoBancoCompensacao()
    {
        return $this->codigoBancoCompensacao;
    }

    /**
     * @param mixed $codigoBancoCompensacao
     *
     * @return $this
     */
    public function setCodigoBancoCompensacao($codigoBancoCompensacao)
    {
        $this->codigoBancoCompensacao = $codigoBancoCompensacao;

        return $this;
    }

    /**
     * @return mixed
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
     * @return mixed
     */
    public function getCodigoSegmentoRegistroDetalhe()
    {
        return $this->codigoSegmentoRegistroDetalhe;
    }

    /**
     * @param mixed $codigoSegmentoRegistroDetalhe
     *
     * @return $this
     */
    public function setCodigoSegmentoRegistroDetalhe($codigoSegmentoRegistroDetalhe)
    {
        $this->codigoSegmentoRegistroDetalhe = $codigoSegmentoRegistroDetalhe;

        return $this;
    }

    /**
     * @return string
     */
    public function getIdentificacaoCheque()
    {
        return $this->identificacaoCheque;
    }

    /**
     * @param string $identificacaoCheque
     *
     * @return $this
     */
    public function setIdentificacaoCheque($identificacaoCheque)
    {
        $this->identificacaoCheque = $identificacaoCheque;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getIdentificacaoRegistroOpcional()
    {
        return $this->identificacaoRegistroOpcional;
    }

    /**
     * @param mixed $identificacaoRegistroOpcional
     *
     * @return $this
     */
    public function setIdentificacaoRegistroOpcional($identificacaoRegistroOpcional)
    {
        $this->identificacaoRegistroOpcional = $identificacaoRegistroOpcional;

        return $this;
    }

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
    public function getNumeroSequencialRegistroLote()
    {
        return $this->numeroSequencialRegistroLote;
    }

    /**
     * @param mixed $numeroSequencialRegistroLote
     *
     * @return $this
     */
    public function setNumeroSequencialRegistroLote($numeroSequencialRegistroLote)
    {
        $this->numeroSequencialRegistroLote = $numeroSequencialRegistroLote;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTipoRegistro()
    {
        return $this->tipoRegistro;
    }

    /**
     * @param mixed $tipoRegistro
     *
     * @return $this
     */
    public function setTipoRegistro($tipoRegistro)
    {
        $this->tipoRegistro = $tipoRegistro;

        return $this;
    }
}