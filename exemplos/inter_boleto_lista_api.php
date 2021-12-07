<?php

use Eduardokum\LaravelBoleto\Boleto\Banco\Inter;

require 'autoload.php';

$beneficiario = new \Eduardokum\LaravelBoleto\Pessoa(
    [
        'nome' => 'ACME',
        'endereco' => 'Rua um, 123',
        'cep' => '99999-999',
        'uf' => 'UF',
        'cidade' => 'CIDADE',
        'documento' => '99.999.999/9999-99',
    ]
);

$api = new Eduardokum\LaravelBoleto\Api\Banco\Inter(
    '123456789',
    realpath(__DIR__ . '/certs/') . DIRECTORY_SEPARATOR . 'cert.crt',
    realpath(__DIR__ . '/certs/') . DIRECTORY_SEPARATOR . 'key.key'
);

$retorno = $api->retrieveList();
$boletos = [];
if ($list = $retorno->body->content) {
    foreach ($list as $boleto) {
        $boletos[] = Inter::createFromAPI($boleto, [
            'conta'           => '123456789',
            'beneficiario' => $beneficiario,
        ]);
    }
}

$pdf = new Eduardokum\LaravelBoleto\Boleto\Render\Pdf();
$pdf->addBoletos($boletos);
$pdf->gerarBoleto($pdf::OUTPUT_SAVE, __DIR__ . DIRECTORY_SEPARATOR . 'arquivos' . DIRECTORY_SEPARATOR . 'inter_lista.pdf');
