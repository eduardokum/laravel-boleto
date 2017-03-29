<?php
require 'autoload.php';
$beneficiario = new \Eduardokum\LaravelBoleto\Pessoa(
    [
        'nome'      => 'S L COMERCIO DE CARNES LTDA',
        'endereco'  => 'Rua um, 123',
        'cep'       => '99999-999',
        'uf'        => 'UF',
        'cidade'    => 'CIDADE',
        'documento' => '08.080.317/0001-97',
    ]
);

$pagador = new \Eduardokum\LaravelBoleto\Pessoa(
    [
        'nome'      => 'EDIMARIO GOMES DOS SANTOS ME',
        'endereco'  => 'RUA DA ASSEMBLÉIA, 67, SL43',
        'bairro'    => 'RECIFE',
        'cep'       => '51030-040',
        'uf'        => 'PE',
        'cidade'    => 'RECIFE',
        'documento' => '23.519.460/0001-26',
    ]
);

$boleto = new Eduardokum\LaravelBoleto\Boleto\Banco\Bnb(
    [
        'logo'                   => realpath(__DIR__ . '/../logos/') . DIRECTORY_SEPARATOR . '004.png',
        'dataVencimento'         => \Carbon\Carbon::createFromDate(2017, 04, 14),
        'valor'                  => 1.28,
        'multa'                  => false,
        'juros'                  => false,
        'numero'                 => '9990887',
        'numeroControle'         => '9990887',
        'numeroDocumento'        => '94561',
        'pagador'                => $pagador,
        'beneficiario'           => $beneficiario,
        'carteira'               => '21',
        'agencia'                => '0232',
        'conta'                  => '000559',
        'contaDv'                => '1',
        'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
        'instrucoes'             => ['instrucao 1', 'instrucao 2', 'instrucao 3'],
        'aceite'                 => 'S',
        'especieDoc'             => 'DM',
    ]
);

$pdf = new Eduardokum\LaravelBoleto\Boleto\Render\Pdf();
$pdf->addBoleto($boleto);

$pdf->gerarBoleto($pdf::OUTPUT_SAVE, __DIR__ . DIRECTORY_SEPARATOR . 'arquivos' . DIRECTORY_SEPARATOR . 'bnb.pdf');
