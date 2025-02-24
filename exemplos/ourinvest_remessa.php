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
    'email'     => 'email@dominio.com',
]);

$boleto = new Eduardokum\LaravelBoleto\Boleto\Banco\Ourinvest([
    'logo'                   => realpath(__DIR__ . '/../logos/') . DIRECTORY_SEPARATOR . '712.png',
    'dataVencimento'         => new Carbon\Carbon(),
    'valor'                  => 100,
    'multa'                  => false,
    'juros'                  => false,
    'numero'                 => 2,
    'numeroDocumento'        => 2,
    'pagador'                => $pagador,
    'beneficiario'           => $beneficiario,
    'carteira'               => '19',
    'agencia'                => 0001,
    'conta'                  => 9999999,
    'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
    'instrucoes'             => ['instrucao 1', 'instrucao 2', 'instrucao 3'],
    'aceite'                 => 'S',
    'especieDoc'             => 'DM',
    'chaveNfe'               => '12345678901234567890123456789012345678901234',
]);

$remessa = new Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400\Banco\Ourinvest([
    'idRemessa'    => 1,
    'agencia'      => 1111,
    'carteira'     => '19',
    'conta'        => 1234567,
    'contaDv'      => 9,
    'beneficiario' => $beneficiario,
]);
$remessa->addBoleto($boleto);

$file = $remessa->save(__DIR__ . DIRECTORY_SEPARATOR . 'arquivos' . DIRECTORY_SEPARATOR . 'ourinvest.txt');
