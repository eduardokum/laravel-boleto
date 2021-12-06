<?php
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
        'logo'            => realpath(__DIR__ . '/../logos/') . DIRECTORY_SEPARATOR . '077.png',
        'dataVencimento'  => (new \Carbon\Carbon())->addDays(),
        'valor'           => 10,
        'multa'           => false,
        'juros'           => false,
        'numero'          => 1,
        'numeroDocumento' => 1,
        'pagador'         => $pagador,
        'beneficiario'    => $beneficiario,
        'conta'           => '173699880',
        'operacao'        => '0587891',
        'aceite'          => 'S',
        'especieDoc'      => 'DM'
    ]
);

$api = new Eduardokum\LaravelBoleto\Api\Banco\Inter(
    '173699880',
    realpath(__DIR__ . '/certs/') . DIRECTORY_SEPARATOR . 'cert.crt',
    realpath(__DIR__ . '/certs/') . DIRECTORY_SEPARATOR . 'key.key'
);

$boleto = $api->createBoleto($boleto);

$pdf = new Eduardokum\LaravelBoleto\Boleto\Render\Pdf();
$pdf->addBoleto($boleto);
$pdf->gerarBoleto($pdf::OUTPUT_SAVE, __DIR__ . DIRECTORY_SEPARATOR . 'arquivos' . DIRECTORY_SEPARATOR . 'inter.pdf');
