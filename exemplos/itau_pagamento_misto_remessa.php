<?php
/**
 * Exemplo com múltiplos lotes: TED + liquidação de boletos no mesmo arquivo.
 */

require 'autoload.php';

use Carbon\Carbon;
use Eduardokum\LaravelBoleto\Pessoa;
use Eduardokum\LaravelBoleto\Pagamento\Banco\Banco as Pagamento;
use Eduardokum\LaravelBoleto\Cnab\Pagamento\Cnab240\Banco\Itau as RemessaPagamento;

$pagador = new Pessoa([
    'nome'      => 'MINHA EMPRESA',
    'endereco'  => 'Av. Paulista, 1000',
    'cep'       => '01310-100',
    'uf'        => 'SP',
    'cidade'    => 'Sao Paulo',
    'documento' => '12.345.678/0001-90',
]);

$benefTed = new Pessoa([
    'nome'      => 'FORNECEDOR TED',
    'endereco'  => 'Rua B, 456',
    'bairro'    => 'Centro',
    'cep'       => '11111-111',
    'uf'        => 'RJ',
    'cidade'    => 'Rio',
    'documento' => '98.765.432/0001-10',
]);

$cedente = new Pessoa([
    'nome'      => 'FORNECEDOR BOLETO',
    'documento' => '11.222.333/0001-44',
]);

// Pagamento TED
$ted = new Pagamento([
    'beneficiario'   => $benefTed,
    'codigoBanco'    => '237',
    'agencia'        => '1234',
    'conta'          => '567890',
    'contaDv'        => '1',
    'valor'          => 500.00,
    'dataVencimento' => Carbon::parse('2026-05-10'),
    'dataPagamento'  => Carbon::parse('2026-04-23'),
    'numeroControle' => 'TED001',
]);

// Pagamento de boleto Itaú
$boleto = new Pagamento([
    'codigoBarras'   => '34191790010123456789012345678901234567890000',
    'valor'          => 150.75,
    'dataVencimento' => Carbon::parse('2026-05-10'),
    'dataPagamento'  => Carbon::parse('2026-04-23'),
    'numeroControle' => 'PED001',
    'beneficiario'   => $cedente,
]);

$remessa = new RemessaPagamento([
    'agencia' => '1111',
    'conta'   => '99999',
    'contaDv' => '0',
    'pagador' => $pagador,
]);
$remessa->addPagamento($ted);
$remessa->addPagamento($boleto);

echo $remessa->gerar();
