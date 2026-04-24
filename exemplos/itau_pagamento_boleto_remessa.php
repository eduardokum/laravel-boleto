<?php

require 'autoload.php';

use Carbon\Carbon;
use Eduardokum\LaravelBoleto\Pessoa;
use Eduardokum\LaravelBoleto\Pagamento\Banco\Banco as Pagamento;
use Eduardokum\LaravelBoleto\Cnab\Pagamento\Cnab240\Banco\Itau as RemessaPagamento;

// Pagador (nossa empresa - será debitada)
$pagador = new Pessoa([
    'nome'      => 'MINHA EMPRESA LTDA',
    'endereco'  => 'Av. Paulista, 1000',
    'bairro'    => 'Bela Vista',
    'cep'       => '01310-100',
    'uf'        => 'SP',
    'cidade'    => 'Sao Paulo',
    'documento' => '12.345.678/0001-90',
]);

// Cedente do boleto 1 (Itaú)
$cedenteItau = new Pessoa([
    'nome'      => 'FORNECEDOR A SA',
    'documento' => '11.222.333/0001-44',
]);

// Cedente do boleto 2 (outro banco)
$cedenteOutro = new Pessoa([
    'nome'      => 'FORNECEDOR B LTDA',
    'documento' => '55.666.777/0001-88',
]);

// Boleto Itaú - código de barras fictício iniciado em 341
$pagamento1 = new Pagamento([
    'codigoBarras'   => '34191790010123456789012345678901234567890000',
    'valor'          => 150.75,
    'valorTitulo'    => 150.75,
    'dataVencimento' => Carbon::parse('2026-05-10'),
    'dataPagamento'  => Carbon::parse('2026-04-23'),
    'numeroControle' => 'PED00001',
    'beneficiario'   => $cedenteItau,
]);

// Boleto de outro banco - aceita também linha digitável (47 dígitos) e converte
$pagamento2 = new Pagamento([
    'linhaDigitavel' => '23793.38128 60007.827136 95000.063305 1 84410000002000',
    'valor'          => 200.00,
    'valorTitulo'    => 210.00,
    'desconto'       => 10.00,
    'acrescimo'      => 0,
    'dataVencimento' => Carbon::parse('2026-05-15'),
    'dataPagamento'  => Carbon::parse('2026-04-23'),
    'numeroControle' => 'PED00002',
    'beneficiario'   => $cedenteOutro,
]);

$remessa = new RemessaPagamento([
    'agencia'   => '1234',
    'conta'     => '567890',
    'contaDv'   => '1',
    'pagador'   => $pagador,
    'idremessa' => 1,
]);

$remessa->addPagamento($pagamento1);
$remessa->addPagamento($pagamento2);

echo $remessa->save(__DIR__ . DIRECTORY_SEPARATOR . 'arquivos' . DIRECTORY_SEPARATOR . 'itau_pagamento_boleto.txt');
