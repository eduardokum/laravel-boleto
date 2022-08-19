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

$api = new Eduardokum\LaravelBoleto\Api\Banco\Inter([
    'versao' => 2,
    'beneficiario' => $beneficiario,
    'client_id' => '1238490c-4293-48ff-be74-17d1ae33a411',
    'client_secret' => '18ae83cf-9fd4-47ef-a2bf-bdf0f1c9d88f',
    'certificado'      => realpath(__DIR__ . '/certs/') . DIRECTORY_SEPARATOR . 'Inter API_Certificado.crt',
    'certificadoChave' => realpath(__DIR__ . '/certs/') . DIRECTORY_SEPARATOR . 'Inter API_Chave.key',
]);
$retorno = $api->retrieveList();

dd($retorno);
//$pdf = new Eduardokum\LaravelBoleto\Boleto\Render\Pdf();
//$pdf->addBoletos($retorno);
//$pdf->gerarBoleto($pdf::OUTPUT_SAVE, __DIR__ . DIRECTORY_SEPARATOR . 'arquivos' . DIRECTORY_SEPARATOR . 'inter_lista_v2.pdf');
