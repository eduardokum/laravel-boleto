<?php

namespace Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab240;

use Eduardokum\LaravelBoleto\Contracts\Cnab\Retorno\Cnab240\DetalheSegmentoU as SegmentoU;

class DetalheSegmentoU implements SegmentoU
{
    private $codigoBancoCompensacao;
    private $tipoRegistro;
    private $numeroSequencialRegistroLote;
    private $codigoSegmentoRegistroDetalhe;
    private $loteServico;
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
    private $codigoOcorrenciaSacado;
    private $dataOcorrenciaSacado;
    private $valorOcorrenciaSacado;
    private $complementoOcorrenciaSacado;
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