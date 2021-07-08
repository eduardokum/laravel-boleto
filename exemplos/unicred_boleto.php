<?php
require __DIR__ . '/../vendor/autoload.php';
ob_start();
$beneficiario = new \Eduardokum\LaravelBoleto\Pessoa(
    [
        'nome'      => 'ACME',
        'endereco'  => 'Rua um, 123',
        'cep'       => '99999-999',
        'uf'        => 'UF',
        'cidade'    => 'CIDADE',
        'documento' => '99.999.999/9999-99',
    ]
);

$pagador = new \Eduardokum\LaravelBoleto\Pessoa(
    [
        'nome'      => 'Cliente',
        'endereco'  => 'Rua um, 123',
        'bairro'    => 'Bairro',
        'cep'       => '99999-999',
        'uf'        => 'UF',
        'cidade'    => 'CIDADE',
        'documento' => '999.999.999-99',
    ]
);


$boleto = new Eduardokum\LaravelBoleto\Boleto\Banco\Unicred(
    [
        'logo'                   => null,
        'dataVencimento'         => \Carbon\Carbon::create(2019, 02, 15),
        'valor'                  => 300,
        'multa'                  => false,
        'juros'                  => false,
        'numero'                 => 1,
        'numeroDocumento'        => 2002,
        'pagador'                => $pagador,
        'beneficiario'           => $beneficiario,
        'carteira'               => 21,
        "agenciaDv"              => 4,
        'agencia'                => 5811,
//        "contaDv"              => "1",
        'convenio'               => 80004288,
        'contaCorrente'          => \Eduardokum\LaravelBoleto\Util::numberFormatGeral(818321, 9),
        'conta'                  => \Eduardokum\LaravelBoleto\Util::numberFormatGeral(818321, 9),
        'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
        'instrucoes'             => ['instrucao 1', 'instrucao 2', 'instrucao 3'],
        'aceite'                 => 'N',
        'especieDoc'             => 'DM',
    ]
);

$pdf = new Eduardokum\LaravelBoleto\Boleto\Render\Pdf();
$pdf->addBoleto($boleto);
$pdf->gerarBoleto($pdf::OUTPUT_SAVE, __DIR__ . DIRECTORY_SEPARATOR . 'arquivos' . DIRECTORY_SEPARATOR . 'unicred.pdf');
