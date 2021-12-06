<?php

use Eduardokum\LaravelBoleto\Util;

require 'autoload.php';

$beneficiario = new \Eduardokum\LaravelBoleto\Pessoa(
    [
        'nome'      => 'INTRA-SIS DESENVOLVIMENTO DE SISTEMAS',
        'endereco'  => 'Av. Engenheiro Caetano Alvares, 3536 - Sala 3',
        'cep'       => '02546-000',
        'uf'        => 'SP',
        'cidade'    => 'São Paulo',
        'documento' => '10.228.511/0001-19',
    ]
);

$pagador = new \Eduardokum\LaravelBoleto\Pessoa(
    [
        'nome'      => 'Eduardo Gusmão',
        'endereco'  => 'Rua Sete de Abril, 1294 - AP 503',
        'bairro'    => 'Juveve',
        'cep'       => '80040-120',
        'uf'        => 'PR',
        'cidade'    => 'Curitiba',
        'documento' => '33532256843',
    ]
);

$boleto = new Eduardokum\LaravelBoleto\Boleto\Banco\Inter(
    [
        'logo'                   => realpath(__DIR__ . '/../logos/') . DIRECTORY_SEPARATOR . '077.png',
        'dataVencimento'         => (new \Carbon\Carbon())->addDays(),
        'valor'                  => 10,
        'multa'                  => false,
        'juros'                  => false,
        'numero'                 => 1,
        'numeroDocumento'        => 1,
        'pagador'                => $pagador,
        'beneficiario'           => $beneficiario,
        'conta'                  => '173699880',
        'operacao'               => '0587891',
        'aceite'                 => 'S',
        'especieDoc'             => 'DM'
    ]
);

$remessa = new \Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400\Banco\Inter(
    [
        'idRemessa'    => 1,
        'agencia'      => '0001',
        'conta'        => '173699880',
        'carteira'     => 112,
        'beneficiario' => $beneficiario,
    ]
);
$remessa->addBoleto($boleto);

echo $remessa->save(__DIR__ . DIRECTORY_SEPARATOR . 'arquivos' . DIRECTORY_SEPARATOR . 'inter.txt', true);
