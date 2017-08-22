<?php

namespace Eduardokum\LaravelBoleto\Tests\Remessa;

use Eduardokum\LaravelBoleto\Boleto\Banco as Boleto;
use Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab240\Banco as Remessa;
use Eduardokum\LaravelBoleto\Pessoa;
use Eduardokum\LaravelBoleto\Tests\TestCase;

class RemessaCnab240Test extends TestCase
{

    protected static $pagador;
    protected static $beneficiario;

    public static function setUpBeforeClass(){
        self::$beneficiario = new Pessoa(
            [
                'nome' => 'ACME',
                'endereco' => 'Rua um, 123',
                'cep' => '99999-999',
                'uf' => 'UF',
                'cidade' => 'CIDADE',
                'documento' => '99.999.999/9999-99',
            ]
        );

        self::$pagador = new Pessoa(
            [
                'nome' => 'Cliente',
                'endereco' => 'Rua um, 123',
                'bairro' => 'Bairro',
                'cep' => '99999-999',
                'uf' => 'UF',
                'cidade' => 'CIDADE',
                'documento' => '999.999.999-99',
            ]
        );
    }

    public static function tearDownAfterClass()
    {
        $aFiles = [
            __DIR__,
            'files',
            'cnab240',
        ];
        $files = glob(implode(DIRECTORY_SEPARATOR, $aFiles) . '/*'); // get all file names
        foreach($files as $file){
            if(is_file($file))
                @unlink($file);
        }
    }
//
//    public function testRemessaSantanderCnab240(){
//        $boleto = new Boleto\Santander(
//            [
//                'logo' => realpath(__DIR__ . '/../logos/') . DIRECTORY_SEPARATOR . '033.png',
//                'dataVencimento' => new \Carbon\Carbon(),
//                'valor' => 100,
//                'multa' => false,
//                'juros' => false,
//                'numero' => 1,
//                'numeroDocumento' => 1,
//                'pagador' => self::$pagador,
//                'beneficiario' => self::$beneficiario,
//                'diasBaixaAutomatica' => 15,
//                'carteira' => 101,
//                'agencia' => 1111,
//                'conta' => 99999999,
//                'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
//                'instrucoes' =>  ['instrucao 1', 'instrucao 2', 'instrucao 3'],
//                'aceite' => 'S',
//                'especieDoc' => 'DM',
//            ]
//        );
//
//        $remessa = new Remessa\Santander(
//            [
//                'agencia' => 1111,
//                'carteira' => 101,
//                'conta' => 99999999,
//                'codigoCliente' => 12345678,
//                'beneficiario' => self::$beneficiario,
//            ]
//        );
//        $remessa->addBoleto($boleto);
//
//        $file = implode(DIRECTORY_SEPARATOR, [
//            __DIR__,
//            'files',
//            'cnab400',
//            'santander.txt'
//        ]);
//
//        $file2 = $remessa->save($file);
//
//        $this->assertFileExists($file);
//        $this->assertEquals($file, $file2);
//    }

    public function testRemessaItauCnab240(){
        $boleto = new Boleto\Itau([
            'logo' => realpath(__DIR__ . '/../logos/') . DIRECTORY_SEPARATOR . '033.png',
            'dataVencimento' => new \Carbon\Carbon(),
            'valor' => 100,
            'multa' => false,
            'juros' => false,
            'numero' => 1,
            'numeroDocumento' => 1,
            'pagador' => self::$pagador,
            'beneficiario' => self::$beneficiario,
            'diasBaixaAutomatica' => 0,
            'carteira' => '109',
            'agencia' => '9999',
            'conta' => '99999',
            'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
            'instrucoes' =>  ['instrucao 1', 'instrucao 2', 'instrucao 3'],
            'aceite' => 'S',
            'especieDoc' => 'DM',
        ]);

        $remessa = new Remessa\Itau([
            'agencia' => '9999',
            'carteira' => '109',
            'conta' => '99999',
            'beneficiario' => self::$beneficiario,
        ]);
        $remessa->addBoleto($boleto);

        $file = implode(DIRECTORY_SEPARATOR, [
            __DIR__,
            'files',
            'cnab240',
            'itau.txt'
        ]);

        $file2 = $remessa->save($file);

        $this->assertFileExists($file);
        $this->assertEquals($file, $file2);
    }
}