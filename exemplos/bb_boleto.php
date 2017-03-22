<?php
require 'autoload.php';

$beneficiario = new \Eduardokum\LaravelBoleto\Pessoa(
    [
        'nome'      => 'INDUSTRIA E COMERCIO DE MALHAS RVB LTDA',
        'endereco'  => 'Rua ABRAO DE SOUZA E SILVA 750 BATEAS  ',
        'cep'       => '88355570',
        'uf'        => 'SC',
        'cidade'    => 'BRUSQUE',
        'documento' => '83.203.992/0001-81',
    ]
);
$pagador = new \Eduardokum\LaravelBoleto\Pessoa(
    [
        'nome'      => 'LAFORT MALHAS IND E COM LTDA',
        'endereco'  => 'Rua VINTE QUATRO MAIO, 1550',
        'bairro'    => '',
        'cep'       => '80220-060',
        'uf'        => 'PR',
        'cidade'    => 'CURITIBA',
        'documento' => '75.165.399/0006/74',
    ]
);
$boleto = new \Eduardokum\LaravelBoleto\Boleto\Banco\Bb(
    [
        'logo'                   => realpath(__DIR__ . '/../logos/') . DIRECTORY_SEPARATOR . '001.png',
        'dataVencimento'         => new \Carbon\Carbon('2016-03-20'),
        'valor'                  => 1574.88,
        'multa'                  => false,
        'juros'                  => 4.41,
        'numero'                 => 1,
        'numeroDocumento'        => 28002002,
        'pagador'                => $pagador,
        'beneficiario'           => $beneficiario,
        'agencia'                => 3420,
        'agenciaDv'              => 7,
        'conta'                  => 29390,
        'contaDv'                => 3,
        'carteira'               => 31,
        'convenio'               => "058096",/*O NUMERO DO CONVENIO É COM 5 DIGITOS POREM ELE ENTRA NO CONVÊNIO DE 6 DIGITOS PRECISANDO ACRESCENTAR 0 NA FRENTE*/
        'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
        'instrucoes'             => ['/////ATENÇÃO ///// -> SEGUNDA VIA', 'PROCEDA OS AJUSTES DE VALORES PERTINENTES', 'PROTESTO 28.03.2016. A PARTIR DESSA, CONSULTE BB P/ PGTO'],
        'aceite'                 => 'S',
        'especieDoc'             => 'DM',
    ]
);

dd($boleto->getLinhaDigitavel());
