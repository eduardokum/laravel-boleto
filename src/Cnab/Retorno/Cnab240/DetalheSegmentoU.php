<?php

namespace Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab240;

use Eduardokum\LaravelBoleto\Contracts\Cnab\Retorno\Cnab240\DetalheSegmentoU as DetalheSegmentoUContract;
use Carbon\Carbon;

class DetalheSegmentoU implements DetalheSegmentoUContract
{
    use MagicTrait;
    /**
     * @var string
     */
    protected $codigoBancoCompensacao;

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
    protected $loteServico;

    /**
     * @var string
     */
    protected $jurosMultaEncargos;

    /**
     * @var string
     */
    protected $valorDescontoConcedido;

    /**
     * @var string
     */
    protected $valorAbatimentoConcedidoCancelado;

    /**
     * @var string
     */
    protected $valorIOF;

    /**
     * @var string
     */
    protected $valorPagoSacado;

    /**
     * @var string
     */
    protected $valorLiquidoCreditado;

    /**
     * @var string
     */
    protected $valorOutrasDespesas;

    /**
     * @var string
     */
    protected $valorOutrosCreditos;

    /**
     * @var Carbon
     */
    protected $dataOcorrencia;

    /**
     * @var Carbon
     */
    protected $dataCredito;

    /**
     * @var string
     */
    protected $codigoOcorrenciaSacado;

    /**
     * @var Carbon
     */
    protected $dataOcorrenciaSacado;

    /**
     * @var string
     */
    protected $valorOcorrenciaSacado;

    /**
     * @var string
     */
    protected $complementoOcorrenciaSacado;

    /**
     * @var string
     */
    protected $codigoBancoCorrespondenteCompensacao;

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
    public function getJurosMultaEncargos()
    {
        return $this->jurosMultaEncargos;
    }

    /**
     * @param mixed $jurosMultaEncargos
     *
     * @return $this
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
     *
     * @return $this
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
     *
     * @return $this
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
     *
     * @return $this
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
     *
     * @return $this
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
     *
     * @return $this
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
     *
     * @return $this
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
     *
     * @return $this
     */
    public function setValorOutrosCreditos($valorOutrosCreditos)
    {
        $this->valorOutrosCreditos = $valorOutrosCreditos;

        return $this;
    }

    /**
     * @param string $format
     *
     * @return mixed
     */
    public function getDataOcorrencia($format = 'd/m/Y')
    {
        return $this->dataOcorrencia instanceof Carbon
            ? $format === false ? $this->dataOcorrencia : $this->dataOcorrencia->format($format)
            : null;
    }

    /**
     * @param mixed  $dataOcorrencia
     *
     * @param string $format
     *
     * @return $this
     */
    public function setDataOcorrencia($dataOcorrencia, $format = 'dmY')
    {
        $this->dataOcorrencia = trim($dataOcorrencia, '0 ') ? Carbon::createFromFormat($format, $dataOcorrencia) : null;

        return $this;
    }

    /**
     * @param string $format
     *
     * @return mixed
     */
    public function getDataCredito($format = 'd/m/Y')
    {
        return $this->dataCredito instanceof Carbon
            ? $format === false ? $this->dataOcorrencia : $this->dataOcorrencia->format($format)
            : null;
    }

    /**
     * @param mixed  $dataCredito
     *
     * @param string $format
     *
     * @return $this
     */
    public function setDataCredito($dataCredito, $format = 'dmY')
    {
        $this->dataCredito = trim($dataCredito, '0 ') ? Carbon::createFromFormat($format, $dataCredito) : null;

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
     *
     * @return $this
     */
    public function setCodigoOcorrenciaSacado($codigoOcorrenciaSacado)
    {
        $this->codigoOcorrenciaSacado = $codigoOcorrenciaSacado;

        return $this;
    }

    /**
     * @param string $format
     *
     * @return mixed
     */
    public function getDataOcorrenciaSacado($format = 'd/m/Y')
    {
        return $this->dataOcorrenciaSacado instanceof Carbon
            ? $format === false ? $this->dataOcorrenciaSacado : $this->dataOcorrenciaSacado->format($format)
            : null;
    }

    /**
     * @param mixed  $dataOcorrenciaSacado
     *
     * @param string $format
     *
     * @return $this
     */
    public function setDataOcorrenciaSacado($dataOcorrenciaSacado, $format = 'dmY')
    {
        $this->dataOcorrenciaSacado = trim($dataOcorrenciaSacado, '0 ') ? Carbon::createFromFormat($format, $dataOcorrenciaSacado) : null;

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
     *
     * @return $this
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
     *
     * @return $this
     */
    public function setComplementoOcorrenciaSacado($complementoOcorrenciaSacado)
    {
        $this->complementoOcorrenciaSacado = $complementoOcorrenciaSacado;

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
     *
     * @return $this
     */
    public function setCodigoBancoCorrespondenteCompensacao($codigoBancoCorrespondenteCompensacao)
    {
        $this->codigoBancoCorrespondenteCompensacao = $codigoBancoCorrespondenteCompensacao;

        return $this;
    }
}