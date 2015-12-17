<?php
namespace Eduardokum\LaravelBoleto\Cnab\Retorno\Trailer;

use Eduardokum\LaravelBoleto\Cnab\Retorno\AbstractDetalhe;

class Bb extends AbstractDetalhe
{
    public $simplesQuantidade;
    public $simplesValor;
    public $simplesAvisos;
    public $vinculadaQuantidade;
    public $vinculadaValor;
    public $vinculadaAvisos;
    public $caucionadaQuantidade;
    public $caucionadaValor;
    public $caucionadaAvisos;
    public $descontadaQuantidades;
    public $descontadaValor;
    public $descontadaAvisos;
    public $vendorQuantidade;
    public $vendorValor;
    public $vendorAvisos;
}