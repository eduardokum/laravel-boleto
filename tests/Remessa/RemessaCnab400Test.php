<?php

namespace Eduardokum\LaravelBoleto\Tests\Remessa;

use Eduardokum\LaravelBoleto\Boleto\Banco as Boleto;
use Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400\Banco as Remessa;
use Eduardokum\LaravelBoleto\Pessoa;
use Eduardokum\LaravelBoleto\Tests\TestCase;
use Eduardokum\LaravelBoleto\Util;

class RemessaCnab400Test extends TestCase
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
            'cnab400',
        ];
        $files = glob(implode(DIRECTORY_SEPARATOR, $aFiles) . '/*'); // get all file names
        foreach($files as $file){
            if(is_file($file))
                @unlink($file);
        }
    }

    /**
     * @expectedException     \Exception
     */
    public function testRemessaCamposInvalidos(){
        $remessa = new Remessa\Banrisul([
            'codigoCliente' => 11112222222,
            'beneficiario' => self::$beneficiario,
        ]);
        $remessa->gerar();
    }

    /**
     * @expectedException     \Exception
     */
    public function testRemessaCarteiraIndisponivel(){
        $remessa = new Remessa\Banrisul([
            'agencia' => 1111,
            'conta' => 22222,
            'carteira' => '123',
            'codigoCliente' => 11112222222,
            'beneficiario' => self::$beneficiario,
        ]);
        $remessa->gerar();
    }

    public function testRemessaAddboletosCnab400(){
        $boleto = new Boleto\Banrisul(
            [
                'logo' => realpath(__DIR__ . '/../logos/') . DIRECTORY_SEPARATOR . '041.png',
                'dataVencimento' => new \Carbon\Carbon(),
                'valor' => 100,
                'multa' => false,
                'juros' => false,
                'numero' => 1,
                'diasBaixaAutomatica' => 20,
                'numeroDocumento' => 1,
                'pagador' => self::$pagador,
                'beneficiario' => self::$beneficiario,
                'carteira' => 1,
                'agencia' => 1111,
                'conta' => 22222,
                'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
                'instrucoes' =>  ['instrucao 1', 'instrucao 2', 'instrucao 3'],
                'aceite' => 'S',
                'especieDoc' => 'DM',
            ]
        );

        $boleto2 = $boleto;
        $boleto2->setNumeroDocumento(2);

        $remessa = new Remessa\Banrisul(
            [
                'agencia' => 1111,
                'conta' => 22222,
                'carteira' => 1,
                'codigoCliente' => 11112222222,
                'beneficiario' => self::$beneficiario,
            ]
        );
        $remessa->addBoletos([$boleto, $boleto2]);
        $this->assertEquals(4, count(Util::file2array($remessa->gerar())));
    }

    public function testRemessaBanrisulCnab400()
    {
        $boleto = new Boleto\Banrisul(
            [
                'logo' => realpath(__DIR__ . '/../logos/') . DIRECTORY_SEPARATOR . '041.png',
                'dataVencimento' => new \Carbon\Carbon(),
                'valor' => 100,
                'multa' => false,
                'juros' => false,
                'numero' => 1,
                'diasBaixaAutomatica' => 20,
                'numeroDocumento' => 1,
                'pagador' => self::$pagador,
                'beneficiario' => self::$beneficiario,
                'carteira' => 1,
                'agencia' => 1111,
                'conta' => 22222,
                'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
                'instrucoes' =>  ['instrucao 1', 'instrucao 2', 'instrucao 3'],
                'aceite' => 'S',
                'especieDoc' => 'DM',
            ]
        );
        $remessa = new Remessa\Banrisul(
            [
                'agencia' => 1111,
                'conta' => 22222,
                'carteira' => 1,
                'codigoCliente' => 11112222222,
                'beneficiario' => self::$beneficiario,
            ]
        );
        $remessa->addBoleto($boleto);

        $file = implode(DIRECTORY_SEPARATOR, [
            __DIR__,
            'files',
            'cnab400',
            'banrisul.txt'
        ]);
        $file2 = $remessa->save($file);

        $this->assertFileExists($file);
        $this->assertEquals($file, $file2);
    }

    public function testRemessaBBCnab400(){
        $boleto = new Boleto\Bb(
            [
                'logo' => realpath(__DIR__ . '/../logos/') . DIRECTORY_SEPARATOR . '001.png',
                'dataVencimento' => new \Carbon\Carbon(),
                'valor' => 100,
                'multa' => false,
                'juros' => false,
                'numero' => 1,
                'numeroDocumento' => 1,
                'pagador' => self::$pagador,
                'beneficiario' => self::$beneficiario,
                'carteira' => 11,
                'convenio' => 1234567,
                'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
                'instrucoes' =>  ['instrucao 1', 'instrucao 2', 'instrucao 3'],
                'aceite' => 'S',
                'especieDoc' => 'DM',
            ]
        );

        $remessa = new Remessa\Bb(
            [
                'agencia' => 1111,
                'carteira' => 11,
                'conta' => 999999999,
                'convenio' => 1234567,
                'beneficiario' => self::$beneficiario,
            ]
        );
        $remessa->addBoleto($boleto);

        $file = implode(DIRECTORY_SEPARATOR, [
            __DIR__,
            'files',
            'cnab400',
            'bb.txt'
        ]);

        $file2 = $remessa->save($file);

        $this->assertFileExists($file);
        $this->assertEquals($file, $file2);
    }

    public function testRemessaBradescoCnab400(){
        $boleto = new Boleto\Bradesco(
            [
                'logo' => realpath(__DIR__ . '/../logos/') . DIRECTORY_SEPARATOR . '237.png',
                'dataVencimento' => new \Carbon\Carbon(),
                'valor' => 100,
                'multa' => false,
                'juros' => false,
                'numero' => 1,
                'diasBaixaAutomatica' => 2,
                'numeroDocumento' => 1,
                'pagador' => self::$pagador,
                'beneficiario' => self::$beneficiario,
                'carteira' => '09',
                'agencia' => 1111,
                'conta' => 9999999,
                'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
                'instrucoes' =>  ['instrucao 1', 'instrucao 2', 'instrucao 3'],
                'aceite' => 'S',
                'especieDoc' => 'DM',
            ]
        );

        $remessa = new Remessa\Bradesco(
            [
                'idRemessa' => 1,
                'agencia' => 1111,
                'carteira' => '09',
                'conta' => 99999999,
                'contaDv' => 9,
                'codigoCliente' => 12345678901234567890,
                'beneficiario' => self::$beneficiario,
            ]
        );
        $remessa->addBoleto($boleto);

        $file = implode(DIRECTORY_SEPARATOR, [
            __DIR__,
            'files',
            'cnab400',
            'bradesco.txt'
        ]);

        $file2 = $remessa->save($file);

        $this->assertFileExists($file);
        $this->assertEquals($file, $file2);
    }

    public function testRemessaCaixaCnab400(){
        $boleto = new Boleto\Caixa(
            [
                'logo' => realpath(__DIR__ . '/../logos/') . DIRECTORY_SEPARATOR . '104.png',
                'dataVencimento' => new \Carbon\Carbon(),
                'valor' => 100.41,
                'multa' => false,
                'juros' => false,
                'numero' => 1,
                'numeroDocumento' => 1,
                'pagador' => self::$pagador,
                'diasBaixaAutomatica' => 2,
                'beneficiario' => self::$beneficiario,
                'agencia' => 1111,
                'conta' => 123456,
                'carteira' => 'RG',
                'codigoCliente' => 999999,
                'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
                'instrucoes' =>  ['instrucao 1', 'instrucao 2', 'instrucao 3'],
                'aceite' => 'S',
                'especieDoc' => 'DM',
            ]
        );

        $remessa = new Remessa\Caixa(
            [
                'agencia' => 1111,
                'conta' => 123456,
                'idremessa' => 1,
                'carteira' => 'RG',
                'codigoCliente' => 999999,
                'beneficiario' => self::$beneficiario,
            ]
        );
        $remessa->addBoleto($boleto);

        $file = implode(DIRECTORY_SEPARATOR, [
            __DIR__,
            'files',
            'cnab400',
            'caixa.txt'
        ]);

        $file2 = $remessa->save($file);

        $this->assertFileExists($file);
        $this->assertEquals($file, $file2);
    }

    public function testRemessaHSBCCnab400(){
        $boleto = new Boleto\Hsbc(
            [
                'logo' => realpath(__DIR__ . '/../logos/') . DIRECTORY_SEPARATOR . '399.png',
                'dataVencimento' => new \Carbon\Carbon(),
                'valor' => 100,
                'multa' => false,
                'juros' => false,
                'numero' => 1,
                'numeroDocumento' => 1,
                'pagador' => self::$pagador,
                'beneficiario' => self::$beneficiario,
                'carteira' => 'CSB',
                'range' => 12345,
                'agencia' => 1111,
                'conta' => 999999,
                'contaDv' => 9,
                'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
                'instrucoes' =>  ['instrucao 1', 'instrucao 2', 'instrucao 3'],
                'aceite' => 'S',
                'especieDoc' => 'DM',
            ]
        );

        $remessa = new Remessa\Hsbc(
            [
                'agencia' => 1111,
                'carteira' => 'CSB',
                'conta' => 999999,
                'contaDv' => 9,
                'beneficiario' => self::$beneficiario,
            ]
        );
        $remessa->addBoleto($boleto);

        $file = implode(DIRECTORY_SEPARATOR, [
            __DIR__,
            'files',
            'cnab400',
            'hsbc.txt'
        ]);

        $file2 = $remessa->save($file);

        $this->assertFileExists($file);
        $this->assertEquals($file, $file2);
    }

    public function testRemessaItauCnab400(){
        $boleto = new Boleto\Itau(
            [
                'logo' => realpath(__DIR__ . '/../logos/') . DIRECTORY_SEPARATOR . '341.png',
                'dataVencimento' => new \Carbon\Carbon(),
                'valor' => 100,
                'multa' => false,
                'juros' => false,
                'numero' => 1,
                'numeroDocumento' => 1,
                'pagador' => self::$pagador,
                'beneficiario' => self::$beneficiario,
                'diasBaixaAutomatica' => 2,
                'carteira' => 112,
                'agencia' => 1111,
                'conta' => 99999,
                'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
                'instrucoes' =>  ['instrucao 1', 'instrucao 2', 'instrucao 3'],
                'aceite' => 'S',
                'especieDoc' => 'DM',
            ]
        );

        $remessa = new Remessa\Itau(
            [
                'agencia' => 1111,
                'conta' => 99999,
                'contaDv' => 9,
                'carteira' => 112,
                'beneficiario' => self::$beneficiario,
            ]
        );
        $remessa->addBoleto($boleto);

        $file = implode(DIRECTORY_SEPARATOR, [
            __DIR__,
            'files',
            'cnab400',
            'itau.txt'
        ]);

        $file2 = $remessa->save($file);

        $this->assertFileExists($file);
        $this->assertEquals($file, $file2);
    }

    public function testRemessaSantanderCnab400(){
        $boleto = new Boleto\Santander(
            [
                'logo' => realpath(__DIR__ . '/../logos/') . DIRECTORY_SEPARATOR . '033.png',
                'dataVencimento' => new \Carbon\Carbon(),
                'valor' => 100,
                'multa' => false,
                'juros' => false,
                'numero' => 1,
                'numeroDocumento' => 1,
                'pagador' => self::$pagador,
                'beneficiario' => self::$beneficiario,
                'diasBaixaAutomatica' => 15,
                'carteira' => 101,
                'agencia' => 1111,
                'conta' => 99999999,
                'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
                'instrucoes' =>  ['instrucao 1', 'instrucao 2', 'instrucao 3'],
                'aceite' => 'S',
                'especieDoc' => 'DM',
            ]
        );

        $remessa = new Remessa\Santander(
            [
                'agencia' => 1111,
                'carteira' => 101,
                'conta' => 99999999,
                'codigoCliente' => 12345678,
                'beneficiario' => self::$beneficiario,
            ]
        );
        $remessa->addBoleto($boleto);

        $file = implode(DIRECTORY_SEPARATOR, [
            __DIR__,
            'files',
            'cnab400',
            'santander.txt'
        ]);

        $file2 = $remessa->save($file);

        $this->assertFileExists($file);
        $this->assertEquals($file, $file2);
    }

    public function testRemessaSicrediCnab400()
    {
        $boleto = new Boleto\Sicredi(
            [
                'logo'                   => realpath(__DIR__ . '/../logos/') . DIRECTORY_SEPARATOR . '748.png',
                'dataVencimento'         => new \Carbon\Carbon(),
                'valor'                  => 100,
                'multa'                  => false,
                'juros'                  => false,
                'numero'                 => 1,
                'numeroDocumento'        => 1,
                'pagador'                => self::$pagador,
                'beneficiario'           => self::$beneficiario,
                'carteira'               => '1',
                'byte'                   => 2,
                'agencia'                => 1111,
                'posto'                  => 11,
                'conta'                  => 11111,
                'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
                'instrucoes'             => ['instrucao 1', 'instrucao 2', 'instrucao 3'],
                'aceite'                 => 'S',
                'especieDoc'             => 'DM',
            ]
        );

        $remessa = new Remessa\Sicredi(
            [
                'agencia'      => 2606,
                'carteira'     => '1',
                'conta'        => 12510,
                'idremessa'    => 1,
                'beneficiario' => self::$beneficiario,
            ]
        );
        $remessa->addBoleto($boleto);

        $file = implode(DIRECTORY_SEPARATOR, [
            __DIR__,
            'files',
            'cnab400',
            'sicredi.txt'
        ]);

        $file2 = $remessa->save($file);

        $this->assertFileExists($file);
        $this->assertEquals($file, $file2);
    }

    public function testRemessaBancoobCnab400()
    {
        $boleto = new Boleto\Bancoob(
            [
                'logo'                   => realpath(__DIR__ . '/../logos/') . DIRECTORY_SEPARATOR . '748.png',
                'dataVencimento'         => new \Carbon\Carbon(),
                'valor'                  => 100,
                'multa'                  => false,
                'juros'                  => false,
                'numero'                 => 1,
                'numeroDocumento'        => 1,
                'pagador'                => self::$pagador,
                'beneficiario'           => self::$beneficiario,
                'carteira'               => '1',
                'byte'                   => 2,
                'agencia'                => 1111,
                'convenio'               => 123123,
                'conta'                  => 11111,
                'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
                'instrucoes'             => ['instrucao 1', 'instrucao 2', 'instrucao 3'],
                'aceite'                 => 'S',
                'especieDoc'             => 'DM',
            ]
        );

        $remessa = new Remessa\Bancoob(
            [
                'agencia'      => 2606,
                'carteira'     => '1',
                'conta'        => 12510,
                'convenio'     => 123123,
                'beneficiario' => self::$beneficiario,
            ]
        );
        $remessa->addBoleto($boleto);

        $file = implode(DIRECTORY_SEPARATOR, [
            __DIR__,
            'files',
            'cnab400',
            'bancoob.txt'
        ]);

        $file2 = $remessa->save($file);

        $this->assertFileExists($file);
        $this->assertEquals($file, $file2);
    }
}