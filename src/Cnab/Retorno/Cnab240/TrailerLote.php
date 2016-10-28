<?php

namespace Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab240;


class TrailerLote
{
    private $loteServico;
    private $TipoRegistro;
    private $qtdRegistroLote;
    private $qtdTitulosCobrancaSimples;
    private $valorTotalTitulosCobranca;
    private $qtdTitulosCobrancaVinculada;
    private $valorTotalTitulosCobrancaVinculada;
    private $qtdTitulosCobrancaCaucionada;
    private $valorTotalTitulosCobrancaCaucionada;
    private $qtdTitulosCobrancaDescontada;
    private $valorTotalTitulosCobrancaDescontada;
    private $numeroAvisoLancamento;

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
    public function getNumeroAvisoLancamento()
    {
        return $this->numeroAvisoLancamento;
    }

    /**
     * @param mixed $numeroAvisoLancamento
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
     */
    public function setValorTotalTitulosCobrancaVinculada($valorTotalTitulosCobrancaVinculada)
    {
        $this->valorTotalTitulosCobrancaVinculada = $valorTotalTitulosCobrancaVinculada;

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
     * Determine if an attribute exists
     *
     * @param  string $key
     * @return bool
     */
    public function __isset($key)
    {
        return isset($this->$key);
    }
}