<?php
/**
 * Sanity check: garantir que o fluxo TED continua funcionando após a
 * introdução do suporte a liquidação de boletos (segmento J/J-52).
 */

require 'autoload.php';

use Carbon\Carbon;
use Eduardokum\LaravelBoleto\Pessoa;
use Eduardokum\LaravelBoleto\Pagamento\Banco\Banco as Pagamento;
use Eduardokum\LaravelBoleto\Cnab\Pagamento\Cnab240\Banco\Itau as RemessaPagamento;

$pagador = new Pessoa([
    'nome'      => 'MINHA EMPRESA',
    'endereco'  => 'Rua A, 123',
    'bairro'    => 'Centro',
    'cep'       => '00000-000',
    'uf'        => 'SP',
    'cidade'    => 'Sao Paulo',
    'documento' => '12.345.678/0001-90',
]);

$benef = new Pessoa([
    'nome'      => 'FORNECEDOR TED',
    'endereco'  => 'Rua B, 456',
    'bairro'    => 'Centro',
    'cep'       => '11111-111',
    'uf'        => 'RJ',
    'cidade'    => 'Rio',
    'documento' => '98.765.432/0001-10',
]);

$p = new Pagamento([
    'beneficiario'   => $benef,
    'codigoBanco'    => '237',
    'agencia'        => '1234',
    'conta'          => '567890',
    'contaDv'        => '1',
    'valor'          => 500.00,
    'dataVencimento' => Carbon::parse('2026-05-10'),
    'dataPagamento'  => Carbon::parse('2026-04-23'),
    'numeroControle' => 'TED001',
]);

$remessa = new RemessaPagamento([
    'agencia' => '1111',
    'conta'   => '99999',
    'contaDv' => '0',
    'pagador' => $pagador,
]);
$remessa->addPagamento($p);

echo $remessa->gerar();
