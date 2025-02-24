<?php

require 'autoload.php';
$beneficiario = new Eduardokum\LaravelBoleto\Pessoa([
    'nome'      => 'ACME',
    'endereco'  => 'Rua um, 123',
    'cep'       => '99999-999',
    'uf'        => 'UF',
    'cidade'    => 'CIDADE',
    'documento' => '99.999.999/9999-99',
]);

$pagador = new Eduardokum\LaravelBoleto\Pessoa([
    'nome'      => 'Cliente',
    'endereco'  => 'Rua um, 123',
    'bairro'    => 'Bairro',
    'cep'       => '99999-999',
    'uf'        => 'UF',
    'cidade'    => 'CIDADE',
    'documento' => '999.999.999-99',
]);

$boleto = new Eduardokum\LaravelBoleto\Boleto\Banco\Itau([
    'logo'                   => realpath(__DIR__ . '/../logos/') . DIRECTORY_SEPARATOR . '341.png',
    'dataVencimento'         => new Carbon\Carbon(),
    'valor'                  => 100,
    'multa'                  => false,
    'juros'                  => false,
    'numero'                 => 1,
    'numeroDocumento'        => 1,
    'pagador'                => $pagador,
    'beneficiario'           => $beneficiario,
    'carteira'               => 112,
    'agencia'                => 1111,
    'conta'                  => 99999,
    'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
    'instrucoes'             => ['instrucao 1', 'instrucao 2', 'instrucao 3'],
    'aceite'                 => 'S',
    'especieDoc'             => 'DM',
    'pix_qrcode'             => '00020104141234567890123426660014BR.GOV.BCB.PIX014466756C616E6F32303139406578616D706C652E636F6D27300012BR.COM.OUTRO011001234567895204000053039865406123.455802BR5915NOMEDORECEBEDOR6008BRASILIA61087007490062530515RP12345678-201950300017BR.GOV.BCB.BRCODE01051.0.080450014BR.GOV.BCB.PIX0123PADRAO.URL.PIX/0123ABCD81390012BR.COM.OUTRO01190123.ABCD.3456.WXYZ6304EB76',
]);

$pdf = new Eduardokum\LaravelBoleto\Boleto\Render\Pdf();
$pdf->addBoleto($boleto);
echo $pdf->gerarBoleto();
