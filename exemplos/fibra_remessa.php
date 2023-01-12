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
        'endereco'  => 'Av. Engenheiro Caetano Alvares, 3536',
        'bairro'    => 'Limão',
        'cep'       => '02546-000',
        'uf'        => 'SP',
        'cidade'    => 'São Paulo',
        'documento' => '10.228.511/0001-19',
    ]
);

$boleto = new Eduardokum\LaravelBoleto\Boleto\Banco\Fibra(
    [
        'logo'                   => realpath(__DIR__ . '/../logos/') . DIRECTORY_SEPARATOR . '224.png',
        'dataVencimento'         => (new \Carbon\Carbon())->addDays(15),
        'valor'                  => 10,
        'multa'                  => false,
        'juros'                  => false,
        'numero'                 => 1,
        'numeroDocumento'        => 1,
        'range'                  => 0,
        'pagador'                => $pagador,
        'beneficiario'           => $beneficiario,
        'modalidadeCarteira'     => '5',
        'carteira'               => 112,
        'agencia'                => '0001',
        'codigoCliente'          => '89191',
        'conta'                  => '6713030',
        'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
        'instrucoes'             => ['instrucao 1', 'instrucao 2', 'instrucao 3'],
        'aceite'                 => 'N',
        'especieDoc'             => 'DM',
        'chaveNfe'               => '35230126589893000146550010000124251745502439'
    ]
);

$remessa = new \Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400\Banco\Fibra(
    [
        'agencia'      => '0001',
        'conta'        => '6713030',
        'contaDv'      => 9,
        'carteira'     => 112,
        'beneficiario' => $beneficiario,
        'codigoCliente' => '89191',
    ]
);
$remessa->addBoleto($boleto);

echo $remessa->save(__DIR__ . DIRECTORY_SEPARATOR . 'arquivos' . DIRECTORY_SEPARATOR . 'fibra.txt');
