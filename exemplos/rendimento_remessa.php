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

$boleto = new Eduardokum\LaravelBoleto\Boleto\Banco\Rendimento([
    'logo'                   => realpath(__DIR__ . '/../logos/') . DIRECTORY_SEPARATOR . '633.png',
    'dataVencimento'         => new Carbon\Carbon('2023-01-20'),
    'valor'                  => 12750,
    'multa'                  => false,
    'juros'                  => false,
    'numero'                 => 96928179,
    'numeroDocumento'        => 96928179,
    'range'                  => 0,
    'pagador'                => $pagador,
    'beneficiario'           => $beneficiario,
    'carteira'               => '121',
    'agencia'                => '0001',
    'codigoCliente'          => '5447390',
    'conta'                  => '1234',
    'modalidadeCarteira'     => '6',
    'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
    'instrucoes'             => ['instrucao 1', 'instrucao 2', 'instrucao 3'],
    'aceite'                 => 'N',
    'especieDoc'             => 'DM',
]);

$remessa = new Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400\Banco\Rendimento([
    'agencia'       => '0001',
    'conta'         => '1234',
    'carteira'      => 121,
    'beneficiario'  => $beneficiario,
    'codigoCliente' => '5447390',
]);
$remessa->addBoleto($boleto);

echo $remessa->save(__DIR__ . DIRECTORY_SEPARATOR . 'arquivos' . DIRECTORY_SEPARATOR . 'rendimento.txt');
