<?php

namespace Eduardokum\LaravelBoleto\Cnab\Pagamento\Retorno\Cnab240;

use Eduardokum\LaravelBoleto\MagicTrait;

class TrailerLote
{
    use MagicTrait;

    protected $codBanco;
    protected $loteServico;
    protected $tipoRegistro;
    protected $qtdRegistroLote;
    protected $valorTotalPagamentos;
    protected $valorTotalTarifas;
    protected $qtdPagamentos;

    // Setters que retornam $this para method chaining
    public function setCodBanco($v)
    {
        $this->codBanco = $v;
        return $this;
    }
    public function setLoteServico($v)
    {
        $this->loteServico = $v;
        return $this;
    }
    public function setTipoRegistro($v)
    {
        $this->tipoRegistro = $v;
        return $this;
    }
    public function setQtdRegistroLote($v)
    {
        $this->qtdRegistroLote = $v;
        return $this;
    }
    public function setValorTotalPagamentos($v)
    {
        $this->valorTotalPagamentos = $v;
        return $this;
    }
    public function setValorTotalTarifas($v)
    {
        $this->valorTotalTarifas = $v;
        return $this;
    }
    public function setQtdPagamentos($v)
    {
        $this->qtdPagamentos = $v;
        return $this;
    }
}
