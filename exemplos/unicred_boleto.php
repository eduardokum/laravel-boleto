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
        'dataVencimento'         => new \Carbon\Carbon(),
        'valor'                  => 100,
        'multa'                  => false,
        'juros'                  => false,
        'numero'                 => 1,
        'numeroDocumento'        => 1,
        'pagador'                => $pagador,
        'beneficiario'           => $beneficiario,
        'carteira'               => 21,
        'agencia'                => 1111,
        "contaDv"              => "1",
        'convenio'               => 123123,
        'contaCorrente'          => \Eduardokum\LaravelBoleto\Util::numberFormatGeral(22222, 9),
        'conta'                  => \Eduardokum\LaravelBoleto\Util::numberFormatGeral(22222, 9),
        'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
        'instrucoes'             => ['instrucao 1', 'instrucao 2', 'instrucao 3'],
        'aceite'                 => 'N',
        'especieDoc'             => 'DM',
    ]
);

$fieldFreedom = $boleto->getCampoLivre();
$agency = substr($fieldFreedom, 0, 4);
$accountRecipient = substr($fieldFreedom, 5, 1);
$ourNumberWithDigitVerifier = substr($fieldFreedom, 14);
$digitVerifierOfOurNumber = substr($fieldFreedom, 24, 1);
//dd("Agency => $agency
//\n Account recipient => $accountRecipient
//\n Our number with digit verifier => $ourNumberWithDigitVerifier
//\n Digit verifier of our number = $digitVerifierOfOurNumber");

$pdf = new Eduardokum\LaravelBoleto\Boleto\Render\Pdf();
$pdf->addBoleto($boleto);
$pdf->gerarBoleto($pdf::OUTPUT_SAVE, __DIR__ . DIRECTORY_SEPARATOR . 'arquivos' . DIRECTORY_SEPARATOR . 'unicred.pdf');
