<?php

require 'autoload.php';

use Eduardokum\LaravelBoleto\PessoaLookup\PessoaResolver;
use Eduardokum\LaravelBoleto\PessoaLookup\CpfCnpjComBrLookup;

$token = getenv('CPFCNPJ_TOKEN') ?: '5ae973d7a997af13f0aaf2bf60e65803';

$lookup = new CpfCnpjComBrLookup($token);
$resolver = new PessoaResolver($lookup);

$pagador = $resolver->porDocumento('27.865.757/0001-02');

$beneficiario = new Eduardokum\LaravelBoleto\Pessoa([
    'nome'      => 'ACME',
    'endereco'  => 'Rua um, 123',
    'bairro'    => 'Bairro',
    'cep'       => '99999-999',
    'uf'        => 'UF',
    'cidade'    => 'CIDADE',
    'documento' => '99.999.999/9999-99',
]);

$boleto = new Eduardokum\LaravelBoleto\Boleto\Banco\Bancoob([
    'dataVencimento'  => new Carbon\Carbon(),
    'valor'           => 100,
    'numero'          => '0004309540',
    'numeroDocumento' => 1,
    'pagador'         => $pagador,
    'beneficiario'    => $beneficiario,
    'carteira'        => 1,
    'agencia'         => '0001',
    'conta'           => '12345',
    'convenio'        => '123456',
]);

echo $boleto->getPagador()->getNomeDocumento() . PHP_EOL;
echo $boleto->getPagador()->getEnderecoCompleto() . PHP_EOL;
