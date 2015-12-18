<?php
namespace Eduardokum\LaravelBoleto\Cnab\Retorno;

class Trailer extends AbstractDetalhe
{

    public $valorTitulos;
    public $avisos = 0;
    public $quantidadeTitulos;
    public $quantidadeLiquidados = 0;
    public $quantidadeBaixados = 0;
    public $quantidadeEntradas = 0;
    public $quantidadeAlterados = 0;
    public $quantidadeErros = 0;

}