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

$boleto = new Eduardokum\LaravelBoleto\Boleto\Banco\Bancoob([
    'logo'                   => realpath(__DIR__ . '/../logos/') . DIRECTORY_SEPARATOR . '756.png',
    'dataVencimento'         => new Carbon\Carbon(),
    'valor'                  => 100,
    'multa'                  => false,
    'juros'                  => false,
    'numero'                 => 1,
    'numeroDocumento'        => 1,
    'pagador'                => $pagador,
    'beneficiario'           => $beneficiario,
    'carteira'               => 1,
    'agencia'                => 1111,
    'convenio'               => 123123,
    'conta'                  => 22222,
    'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
    'instrucoes'             => ['instrucao 1', 'instrucao 2', 'instrucao 3'],
    'aceite'                 => 'S',
    'especieDoc'             => 'DM',
]);

$configsCasoNaoTenhaUmMailerConfiguradoNoSeuLaravel = [
    'scheme'   => 'smtp',
    'port'     => 'porta',
    'host'     => 'smtp.host.com',
    'username' => 'usuario',
    'password' => 'senha',
    'from'     => ['address' => 'empresa@empresa.com', 'name' => 'Empresa'],
];

$mail = new Eduardokum\LaravelBoleto\Boleto\Mail($configsCasoNaoTenhaUmMailerConfiguradoNoSeuLaravel);

$data = [
    'empresa' => 'Nome da empresa',
    'logo'    => 'full/path/logo.png',
];

// Array do boleto será automaticamente passado para view no formato de array, com a variável chamada $boleto
$mail->send(
    ['arquivos/template.blade.php', $data],
    'assunto',
    $boleto,
    'email@cliente.com'
);

$mail->send(
    ["<!DOCTYPE html><html lang='en'><head><meta charset=''utf-8'></head><body><img src='{{ \$logo }}'/><h1>{{ \$empresa }}</h1><p>Olá cliente {{ \$boleto['pagador']['nome'] }}</p></body></html>", $data],
    'assunto',
    $boleto,
    'email@cliente.com'
);

$mail->send(
    'Email simples sem template',
    'assunto',
    $boleto,
    'email@cliente.com'
);

$mail->setTo('email@cliente.com')->setBoleto($boleto)->send('Email simples sem template', 'assunto');

$aLoteBoletos = [
    'cliente@cliente1.com.br' => $boleto,
    'cliente@cliente2.com.br' => $boleto,
    'cliente@cliente3.com.br' => $boleto,
];

$mail->sendLote(
    'Email simples sem template',
    'assunto',
    $aLoteBoletos
);
