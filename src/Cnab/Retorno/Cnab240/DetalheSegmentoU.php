<?php

namespace Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab240;

use Eduardokum\LaravelBoleto\Contracts\Cnab\Retorno\Cnab240\DetalheSegmentoU as SegmentoU;
use Eduardokum\LaravelBoleto\Util;

class DetalheSegmentoU implements SegmentoU
{


    /**
     * @var string
     */
    private $codigoBancoCompensacao;

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
    private $loteServico;

    /**
     * @var string
     */
    private $jurosMultaEncargos;

    /**
     * @var string
     */
    private $valorDescontoConcedido;

    /**
     * @var string
     */
    private $valorAbatimentoConcedidoCancelado;

    /**
     * @var string
     */
    private $valorIOF;

    /**
     * @var string
     */
    private $valorPagoSacado;

    /**
     * @var string
     */
    private $valorLiquidoCreditado;

    /**
     * @var string
     */
    private $valorOutrasDespesas;

    /**
     * @var string
     */
    private $valorOutrosCreditos;

    /**
     * @var string
     */
    private $dataOcorrencia;

    /**
     * @var string
     */
    private $dataCredito;

    /**
     * @var string
     */
    private $codigoOcorrenciaSacado;

    /**
     * @var string
     */
    private $dataOcorrenciaSacado;

    /**
     * @var string
     */
    private $valorOcorrenciaSacado;

    /**
     * @var string
     */
    private $complementoOcorrenciaSacado;

    /**
     * @var string
     */
    private $codigoBancoCorrespondenteCompensacao;

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
    public function getDataOcorrencia($format = 'd/m/Y')
    {
        return $this->dataOcorrencia instanceof Carbon
            ? $format === false ? $this->dataOcorrencia : $this->dataOcorrencia->format($format)
            : null;
    }

    /**
     * @param mixed $dataOcorrencia
     */
    public function setDataOcorrencia($dataOcorrencia, $format = 'dmy')
    {
        $this->dataOcorrencia = trim($dataOcorrencia, '0 ') ? Carbon::createFromFormat($format, $dataOcorrencia) : null;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDataCredito()
    {
        return $this->dataCredito instanceof Carbon
            ? $format === false ? $this->dataOcorrencia : $this->dataOcorrencia->format($format)
            : null;
    }

    /**
     * @param mixed $dataCredito
     */
    public function setDataCredito($dataCredito, $format = 'dmy')
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
        return $this->dataOcorrenciaSacado instanceof Carbon
            ? $format === false ? $this->dataOcorrenciaSacado : $this->dataOcorrenciaSacado->format($format)
            : null;
    }

    /**
     * @param mixed $dataOcorrenciaSacado
     */
    public function setDataOcorrenciaSacado($dataOcorrenciaSacado, $format = 'dmy')
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

    /**
     * @return array
     */
    public function toArray()
    {
        $vars = array_keys(get_class_vars(self::class));
        $aRet = [];
        foreach ($vars as $var) {
            $methodName = 'get' . ucfirst($var);
            $aRet[$var] = method_exists($this, $methodName)
                ? $this->$methodName()
                : $this->$var;
        }
        return $aRet;
    }

    /**
     * Fast set method.
     *
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        if (property_exists($this, $name)) {
            $this->$name = $value;
        }
    }

    /**
     * Fast get method.
     *
     * @param $name
     */
    public function __get($name)
    {
        if (property_exists($this, $name)) {
            $method = 'get' . ucwords($name);
            return $this->{$method}();
        }
    }

    /**
     * Determine if an attribute exists.
     *
     * @param  string $key
     * @return bool
     */
    public function __isset($key)
    {
        return isset($this->$key);
    }

}