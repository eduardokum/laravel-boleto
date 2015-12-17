<?php
namespace Eduardokum\LaravelBoleto\Cnab\Retorno\Header;

use Eduardokum\LaravelBoleto\Cnab\Retorno\AbstractDetalhe;

class Bb extends AbstractDetalhe
{
    public $operacaoCodigo;
    public $operacao;
    public $servicoCodigo;
    public $servico;
    public $agencia;
    public $agenciaDigito;
    public $conta;
    public $contaDigito;
    public $cedenteNome;
    public $data;
    public $convenio;
}