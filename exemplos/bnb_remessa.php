<?php

require 'autoload.php';
$beneficiario = new Eduardokum\LaravelBoleto\Pessoa([
    'nome'      => 'ACME',
    'endereco'  => 'Rua um, 123',
    'cep'       => '99999-999',
    'uf'        => 'UF',
    'cidade'    => 'CIDADE',
    'documento' => '99.999.999/9999-99',
]);

$pagador = new Eduardokum\LaravelBoleto\Pessoa([
    'nome'      => 'Cliente',
    'endereco'  => 'Rua um, 123',
    'bairro'    => 'Bairro',
    'cep'       => '99999-999',
    'uf'        => 'UF',
    'cidade'    => 'CIDADE',
    'documento' => '999.999.999-99',
]);

$boleto = new Eduardokum\LaravelBoleto\Boleto\Banco\Bnb([
    'logo'                   => realpath(__DIR__ . '/../logos/') . DIRECTORY_SEPARATOR . '004.png',
    'dataVencimento'         => Carbon\Carbon::createFromDate(2017, 04, 03),
    'valor'                  => 2338.28,
    'multa'                  => 2.0,
    'juros'                  => false,
    'numero'                 => '0990887',
    'numeroDocumento'        => '3456/1',
    'pagador'                => $pagador,
    'beneficiario'           => $beneficiario,
    'carteira'               => '21',
    'agencia'                => '0232',
    'conta'                  => '0000559',
    'contaDv'                => '1',
    'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
    'instrucoes'             => ['instrucao 1', 'instrucao 2', 'instrucao 3'],
    'aceite'                 => 'S',
    'especieDoc'             => 'DM',
]);

$remessa = new Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400\Banco\Bnb([
    'agencia'      => '0232',
    'conta'        => '0000559',
    'contaDv'      => '1',
    'carteira'     => '21',
    'beneficiario' => $beneficiario,
]);
$remessa->addBoleto($boleto);

echo $remessa->save(__DIR__ . DIRECTORY_SEPARATOR . 'arquivos' . DIRECTORY_SEPARATOR . 'bnb.txt');
