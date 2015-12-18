<?php
namespace Eduardokum\LaravelBoleto\Cnab\Retorno;

class Header extends AbstractDetalhe
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
    public $cedenteCodigo;
    public $data;
    public $convenio;
}