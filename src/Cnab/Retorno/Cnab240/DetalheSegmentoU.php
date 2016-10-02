<?php

namespace Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab240;

use Eduardokum\LaravelBoleto\Contracts\Cnab\Retorno\Cnab240\DetalheSegmentoU as SegmentoU;

class DetalheSegmentoU implements SegmentoU
{
    private $codigoBancoCompensacao;
    private $numeroLoteRetorno;
    private $tipoRegistro;
    private $numeroSequencialRegistroLote;
    private $codigoSegmentoRegistroDetalhe;
    private $loteServico;
    private $numeroAgenciaCobradoraRecebedora;
    private $identificador;
    private $jurosMultaEncargos;
    private $valorDescontoConcedido;
    private $valorAbatimentoConcedidoCancelado;
    private $valorIOF;
    private $valorPagoSacado;
    private $valorLiquidoCreditado;
    private $valorOutrasDespesas;
    private $valorOutrosCreditos;
    private $dataOcorrencia;
    private $dataCredito;
    private $descontoConcedido;
    private $codigoOcorrenciaSacado;
    private $dataOcorrenciaSacado;
    private $valorOcorrenciaSacado;
    private $complementoOcorrenciaSacado;
    private $codigoBancoCorrespondenteCompensacao;
    private $valorDesconto;
    private $valorAbatimento;

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
    public function getNumeroLoteRetorno()
    {
        return $this->numeroLoteRetorno;
    }

    /**
     * @param mixed $numeroLoteRetorno
     */
    public function setNumeroLoteRetorno($numeroLoteRetorno)
    {
        $this->numeroLoteRetorno = $numeroLoteRetorno;

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
    public function getNumeroAgenciaCobradoraRecebedora()
    {
        return $this->numeroAgenciaCobradoraRecebedora;
    }

    /**
     * @param mixed $numeroAgenciaCobradoraRecebedora
     */
    public function setNumeroAgenciaCobradoraRecebedora($numeroAgenciaCobradoraRecebedora)
    {
        $this->numeroAgenciaCobradoraRecebedora = $numeroAgenciaCobradoraRecebedora;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getIdentificador()
    {
        return $this->identificador;
    }

    /**
     * @param mixed $identificador
     */
    public function setIdentificador($identificador)
    {
        $this->identificador = $identificador;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getJurosMultaEncargos()
    {
        return $this->jurosMultaEncargos;
    }

    /**
     * @param mixed $jurosMultaEncargos
     */
    public function setJurosMultaEncargos($jurosMultaEncargos)
    {
        $this->jurosMultaEncargos = $jurosMultaEncargos;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getValorDescontoConcedido()
    {
        return $this->valorDescontoConcedido;
    }

    /**
     * @param mixed $valorDescontoConcedido
     */
    public function setValorDescontoConcedido($valorDescontoConcedido)
    {
        $this->valorDescontoConcedido = $valorDescontoConcedido;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getValorAbatimentoConcedidoCancelado()
    {
        return $this->valorAbatimentoConcedidoCancelado;
    }

    /**
     * @param mixed $valorAbatimentoConcedidoCancelado
     */
    public function setValorAbatimentoConcedidoCancelado($valorAbatimentoConcedidoCancelado)
    {
        $this->valorAbatimentoConcedidoCancelado = $valorAbatimentoConcedidoCancelado;

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
     */
    public function setValorIOF($valorIOF)
    {
        $this->valorIOF = $valorIOF;

        return $this;
    }

    /**
     * @return mixed
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
     * @return mixed
     */
    public function getValorLiquidoCreditado()
    {
        return $this->valorLiquidoCreditado;
    }

    /**
     * @param mixed $valorLiquidoCreditado
     */
    public function setValorLiquidoCreditado($valorLiquidoCreditado)
    {
        $this->valorLiquidoCreditado = $valorLiquidoCreditado;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getValorOutrasDespesas()
    {
        return $this->valorOutrasDespesas;
    }

    /**
     * @param mixed $valorOutrasDespesas
     */
    public function setValorOutrasDespesas($valorOutrasDespesas)
    {
        $this->valorOutrasDespesas = $valorOutrasDespesas;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getValorOutrosCreditos()
    {
        return $this->valorOutrosCreditos;
    }

    /**
     * @param mixed $valorOutrosCreditos
     */
    public function setValorOutrosCreditos($valorOutrosCreditos)
    {
        $this->valorOutrosCreditos = $valorOutrosCreditos;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDataOcorrencia()
    {
        return $this->dataOcorrencia;
    }

    /**
     * @param mixed $dataOcorrencia
     */
    public function setDataOcorrencia($dataOcorrencia)
    {
        $this->dataOcorrencia = $dataOcorrencia;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDataCredito()
    {
        return $this->dataCredito;
    }

    /**
     * @param mixed $dataCredito
     */
    public function setDataCredito($dataCredito)
    {
        $this->dataCredito = $dataCredito;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCodigoOcorrenciaSacado()
    {
        return $this->codigoOcorrenciaSacado;
    }

    /**
     * @param mixed $codigoOcorrenciaSacado
     */
    public function setCodigoOcorrenciaSacado($codigoOcorrenciaSacado)
    {
        $this->codigoOcorrenciaSacado = $codigoOcorrenciaSacado;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDataOcorrenciaSacado()
    {
        return $this->dataOcorrenciaSacado;
    }

    /**
     * @param mixed $dataOcorrenciaSacado
     */
    public function setDataOcorrenciaSacado($dataOcorrenciaSacado)
    {
        $this->dataOcorrenciaSacado = $dataOcorrenciaSacado;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getValorOcorrenciaSacado()
    {
        return $this->valorOcorrenciaSacado;
    }

    /**
     * @param mixed $valorOcorrenciaSacado
     */
    public function setValorOcorrenciaSacado($valorOcorrenciaSacado)
    {
        $this->valorOcorrenciaSacado = $valorOcorrenciaSacado;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getComplementoOcorrenciaSacado()
    {
        return $this->complementoOcorrenciaSacado;
    }

    /**
     * @param mixed $complementoOcorrenciaSacado
     */
    public function setComplementoOcorrenciaSacado($complementoOcorrenciaSacado)
    {
        $this->complementoOcorrenciaSacado = $complementoOcorrenciaSacado;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDescontoConcedido()
    {
        return $this->descontoConcedido;
    }

    /**
     * @param mixed $descontoConcedido
     */
    public function setDescontoConcedido($descontoConcedido)
    {
        $this->descontoConcedido = $descontoConcedido;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCodigoBancoCorrespondenteCompensacao()
    {
        return $this->codigoBancoCorrespondenteCompensacao;
    }

    /**
     * @param mixed $codigoBancoCorrespondenteCompensacao
     */
    public function setCodigoBancoCorrespondenteCompensacao($codigoBancoCorrespondenteCompensacao)
    {
        $this->codigoBancoCorrespondenteCompensacao = $codigoBancoCorrespondenteCompensacao;

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
    public function getValorAbatimento()
    {
        return $this->valorAbatimento;
    }

    /**
     * @param mixed $valorAbatimento
     */
    public function setValorAbatimento($valorAbatimento)
    {
        $this->valorAbatimento = $valorAbatimento;

        return $this;
    }

}