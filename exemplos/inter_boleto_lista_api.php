<?php

use Xpendi\CnabBoleto\Boleto\Banco\Inter;

require 'autoload.php';

$beneficiario = new \Xpendi\CnabBoleto\Pessoa(
    [
        'nome' => 'ACME',
        'endereco' => 'Rua um, 123',
        'cep' => '99999-999',
        'uf' => 'UF',
        'cidade' => 'CIDADE',
        'documento' => '99.999.999/9999-99',
    ]
);

$api = new Xpendi\CnabBoleto\Api\Banco\Inter([
    'conta'            => '123456789',
    'certificado'      => realpath(__DIR__ . '/certs/') . DIRECTORY_SEPARATOR . 'cert.crt',
    'certificadoChave' => realpath(__DIR__ . '/certs/') . DIRECTORY_SEPARATOR . 'key.key',
]);

$retorno = $api->retrieveList();

dd($retorno);
//$pdf = new Xpendi\CnabBoleto\Boleto\Render\Pdf();
//$pdf->addBoletos($retorno);
//$pdf->gerarBoleto($pdf::OUTPUT_SAVE, __DIR__ . DIRECTORY_SEPARATOR . 'arquivos' . DIRECTORY_SEPARATOR . 'inter_lista_v2.pdf');

