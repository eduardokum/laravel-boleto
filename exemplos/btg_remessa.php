<?php

require 'autoload.php';
$beneficiario = new \Eduardokum\LaravelBoleto\Pessoa([
    'nome'      => 'ACME',
    'endereco'  => 'Rua um, 123',
    'cep'       => '99999-999',
    'uf'        => 'UF',
    'cidade'    => 'CIDADE',
    'documento' => '99.999.999/9999-99',
]);

$pagador = new \Eduardokum\LaravelBoleto\Pessoa([
    'nome'      => 'Cliente',
    'endereco'  => 'Rua um, 123',
    'bairro'    => 'Bairro',
    'cep'       => '99999-999',
    'uf'        => 'UF',
    'cidade'    => 'CIDADE',
    'documento' => '999.999.999-99',
]);

$boleto = new Eduardokum\LaravelBoleto\Boleto\Banco\Btg([
    'logo'                   => realpath(__DIR__.'/../logos/').DIRECTORY_SEPARATOR.'208.png',
    'dataVencimento'         => new \Carbon\Carbon('2021-12-11'),
    'valor'                  => 200,
    'multa'                  => 10,
    'juros'                  => 10,
    'desconto'               => 10,
    'numero'                 => 6380,
    'numeroDocumento'        => 6380,
    'pagador'                => $pagador,
    'beneficiario'           => $beneficiario,
    'carteira'               => '1',
    'agencia'                => '0050',
    'conta'                  => '000000000',
    'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
    'instrucoes'             => ['instrucao 1', 'instrucao 2', 'instrucao 3'],
    'aceite'                 => 'S',
    'especieDoc'             => 'DM',
    'pix_qrcode'             => '00020126330014br.gov.bcb.pix01111335366962052040000530398654040.805802BR5919NOME6014CIDADE62580520LKH2021102118215467250300017br.gov.bcb.brcode01051.0.063044D24',
]);

$remessa = new \Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab240\Banco\Btg([
    'agencia'       => '0050',
    'carteira'      => '1',
    'conta'         => '000000000',
    'codigoCliente' => '001100983001401000',
    'idremessa'     => 1,
    'beneficiario'  => $beneficiario,
]);
$remessa->addBoleto($boleto);

echo $remessa->save(__DIR__.DIRECTORY_SEPARATOR.'arquivos'.DIRECTORY_SEPARATOR.'btg.txt');
