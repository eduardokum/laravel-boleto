<?php

namespace Eduardokum\LaravelBoleto\Cnab\Pagamento\Retorno\Cnab240;

use Eduardokum\LaravelBoleto\MagicTrait;

class Trailer
{
    use MagicTrait;

    protected $codBanco;
    protected $numeroLote;
    protected $tipoRegistro;
    protected $qtdLotesArquivo;
    protected $qtdRegistroArquivo;

    // Setters que retornam $this para method chaining
    public function setCodBanco($v)
    {
        $this->codBanco = $v;
        return $this;
    }
    public function setNumeroLote($v)
    {
        $this->numeroLote = $v;
        return $this;
    }
    public function setTipoRegistro($v)
    {
        $this->tipoRegistro = $v;
        return $this;
    }
    public function setQtdLotesArquivo($v)
    {
        $this->qtdLotesArquivo = $v;
        return $this;
    }
    public function setQtdRegistroArquivo($v)
    {
        $this->qtdRegistroArquivo = $v;
        return $this;
    }
}
