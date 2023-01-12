<?php
require 'autoload.php';
$beneficiario = new \Eduardokum\LaravelBoleto\Pessoa(
    [
        'nome'      => 'KRONOS LTDA',
        'endereco'  => 'Rua , 79',
        'cep'       => '13988-000',
        'uf'        => 'SP',
        'cidade'    => 'Itapira',
        'documento' => '26.589.893/0001-46',
    ]
);

$pagador = new \Eduardokum\LaravelBoleto\Pessoa(
    [
        'nome'      => 'Intrasis',
        'endereco'  => 'Av. Engenheiro Caetano Alveres, 3536',
        'bairro'    => 'Limão',
        'cep'       => '02546-000',
        'uf'        => 'SP',
        'cidade'    => 'São Paulo',
        'documento' => '10.228.511/0001-19',
    ]
);

$boleto = new Eduardokum\LaravelBoleto\Boleto\Banco\Pine(
    [
        'logo'                   => realpath(__DIR__ . '/../logos/') . DIRECTORY_SEPARATOR . '643.png',
        'dataVencimento'         => (new \Carbon\Carbon())->addDays(15),
        'valor'                  => 10,
        'multa'                  => false,
        'juros'                  => false,
        'numero'                 => 1,
        'numeroDocumento'        => 1,
        'pagador'                => $pagador,
        'beneficiario'           => $beneficiario,
        'carteira'               => 112,
        'agencia'                => '0001',
        'conta'                  => '5249',
        'codigoCliente'          => '48780',
        'modalidadeCarteira'     => '1',
        'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
        'instrucoes'             => ['instrucao 1', 'instrucao 2', 'instrucao 3'],
        'aceite'                 => 'N',
        'especieDoc'             => 'DM',
        'chaveNfe'               => '35230126589893000146550010000124251745502439'
    ]
);

$remessa = new \Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400\Banco\Pine(
    [
        'agencia'       => '0001',
        'conta'         => '5249',
        'contaDv'       => 9,
        'carteira'      => 112,
        'beneficiario'  => $beneficiario,
        'codigoCliente' => '48780',
    ]
);
$remessa->addBoleto($boleto);

echo $remessa->save(__DIR__ . DIRECTORY_SEPARATOR . 'arquivos' . DIRECTORY_SEPARATOR . 'pine.txt');
