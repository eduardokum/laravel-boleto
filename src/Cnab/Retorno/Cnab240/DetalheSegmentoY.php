<?php

namespace Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab240;

use Eduardokum\LaravelBoleto\Contracts\Cnab\Retorno\Cnab240\DetalheSegmentoY as SegmentoY;

class DetalheSegmentoY implements SegmentoY
{
    /**
     * @var string
     */
    private $codigoBancoCompensacao;

    /**
     * @var string
     */
    private $loteServico;

    /**
     * @var string
     */
    private $tipoRegistro;

    /**
     * @var string
     */
    private $numeroSequencialRegistroLote;

    /**
     * @var string
     */
    private $codigoSegmentoRegistroDetalhe;

    /**
     * @var string
     */
    private $codigoOcorrencia;

    /**
     * @var string
     */
    private $identificacaoRegistroOpcional;

    /**
     * @var string
     */
    private $identificacaoCheque;

    /**
     * @return mixed
     */
    public function getCodigoBancoCompensacao()
    {
        return $this->codigoBancoCompensacao;
    }

    /**
     * @param mixed $codigoBancoCompensacao
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
     */
    public function setCodigoSegmentoRegistroDetalhe($codigoSegmentoRegistroDetalhe)
    {
        $this->codigoSegmentoRegistroDetalhe = $codigoSegmentoRegistroDetalhe;

        return $this;
    }

    /**
     * @return array
     */
    public function getIdentificacaoCheque()
    {
        return $this->identificacaoCheque;
    }

    /**
     * @param array $identificacaoCheque
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
     */
    public function setTipoRegistro($tipoRegistro)
    {
        $this->tipoRegistro = $tipoRegistro;

        return $this;
    }

}