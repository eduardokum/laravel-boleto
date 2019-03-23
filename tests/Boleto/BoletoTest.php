<?php

namespace Eduardokum\LaravelBoleto\Tests\Boleto;

use Eduardokum\LaravelBoleto\Boleto\Banco as Boleto;
use Eduardokum\LaravelBoleto\Boleto\Render\Pdf;
use Eduardokum\LaravelBoleto\Pessoa;
use Eduardokum\LaravelBoleto\Tests\TestCase;

class BoletoTest extends TestCase
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
        ];
        $files = glob(implode(DIRECTORY_SEPARATOR, $aFiles) . '/*'); // get all file names
        foreach($files as $file){
            if(is_file($file))
                @unlink($file);
        }
    }

    public function testAddBoletos()
    {
        $boleto = new Boleto\Banrisul(
            [
                'logo' => realpath(__DIR__ . '/../../logos/') . DIRECTORY_SEPARATOR . '041.png',
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

        $pdf = new Pdf();
        $pdf->addBoletos([$boleto, $boleto]);

        $this->assertNotNull($pdf->gerarBoleto($pdf::OUTPUT_STRING));
    }

    public function testSave()
    {
        $boleto = new Boleto\Banrisul(
            [
                'logo' => realpath(__DIR__ . '/../../logos/') . DIRECTORY_SEPARATOR . '041.png',
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

        $file = implode(DIRECTORY_SEPARATOR, [
            __DIR__,
            'files',
            'boletos1.pdf'
        ]);

        $pdf = new Pdf();
        $pdf->addBoletos([$boleto, $boleto]);
        $file2 = $pdf->gerarBoleto($pdf::OUTPUT_SAVE, $file);

        $this->assertEquals($file, $file2);
        $this->assertFileExists($file);
    }

    public function testSaveJS()
    {
        $boleto = new Boleto\Banrisul(
            [
                'logo' => realpath(__DIR__ . '/../../logos/') . DIRECTORY_SEPARATOR . '041.png',
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

        $file = implode(DIRECTORY_SEPARATOR, [
            __DIR__,
            'files',
            'boletos2.pdf'
        ]);

        $pdf = new Pdf();
        $pdf->addBoletos([$boleto, $boleto]);
        $file2 = $pdf->gerarBoleto($pdf::OUTPUT_SAVE, $file, true);

        $this->assertEquals($file, $file2);
        $this->assertFileExists($file);
    }

    public function testWithoutLogo()
    {
        $boleto = new Boleto\Banrisul(
            [
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

        $file = implode(DIRECTORY_SEPARATOR, [
            __DIR__,
            'files',
            'boletos3.pdf'
        ]);

        $pdf = new Pdf();
        $pdf->addBoletos([$boleto, $boleto]);
        $file3 = $pdf->gerarBoleto($pdf::OUTPUT_SAVE, $file);

        $this->assertEquals($file, $file3);
        $this->assertFileExists($file);
    }

    /**
     * @expectedException     \Exception
     */
    public function testSemBoletoAdicionado()
    {
        $pdf = new Pdf();
        $pdf->gerarBoleto($pdf::OUTPUT_STRING);
    }

    public function testBoletoBanrisul()
    {
        $boleto = new Boleto\Banrisul(
            [
                'logo' => realpath(__DIR__ . '/../../logos/') . DIRECTORY_SEPARATOR . '041.png',
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
        $this->assertInternalType('array', $boleto->toArray());
        $this->assertNotNull($boleto->renderHTML());
        $this->assertNotNull($boleto->renderPDF());
    }

    public function testBoletoBB()
    {
        $boleto = new Boleto\Bb(
            [
                'logo' => realpath(__DIR__ . '/../../logos/') . DIRECTORY_SEPARATOR. '001.png',
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
        $this->assertInternalType('array', $boleto->toArray());
        $this->assertNotNull($boleto->renderHTML());
        $this->assertNotNull($boleto->renderPDF());
    }

    public function testBoletoBradesco()
    {
        $boleto = new Boleto\Bradesco(
            [
                'logo' => realpath(__DIR__ . '/../../logos/') . DIRECTORY_SEPARATOR . '237.png',
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
        $this->assertInternalType('array', $boleto->toArray());
        $this->assertNotNull($boleto->renderHTML());
        $this->assertNotNull($boleto->renderPDF());
    }

    public function testBoletoCaixa()
    {
        $boleto = new Boleto\Caixa(
            [
                'logo' => realpath(__DIR__ . '/../../logos/') . DIRECTORY_SEPARATOR . '104.png',
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
        $this->assertInternalType('array', $boleto->toArray());
        $this->assertNotNull($boleto->renderHTML());
        $this->assertNotNull($boleto->renderPDF());
    }

    public function testBoletoHSBC()
    {
        $boleto = new Boleto\Hsbc(
            [
                'logo' => realpath(__DIR__ . '/../../logos/') . DIRECTORY_SEPARATOR . '399.png',
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
        $this->assertInternalType('array', $boleto->toArray());
        $this->assertNotNull($boleto->renderHTML());
        $this->assertNotNull($boleto->renderPDF());
    }

    public function testBoletoItau()
    {
        $boleto = new Boleto\Itau(
            [
                'logo' => realpath(__DIR__ . '/../../logos/') . DIRECTORY_SEPARATOR . '341.png',
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
        $this->assertInternalType('array', $boleto->toArray());
        $this->assertNotNull($boleto->renderHTML());
        $this->assertNotNull($boleto->renderPDF());
    }

    public function testBoletoSantander()
    {
        $boleto = new Boleto\Santander(
            [
                'logo' => realpath(__DIR__ . '/../../logos/') . DIRECTORY_SEPARATOR . '033.png',
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
                'codigoCliente' => 9999999,
                'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
                'instrucoes' =>  ['instrucao 1', 'instrucao 2', 'instrucao 3'],
                'aceite' => 'S',
                'especieDoc' => 'DM',
            ]
        );
        $this->assertInternalType('array', $boleto->toArray());
        $this->assertNotNull($boleto->renderHTML());
        $this->assertNotNull($boleto->renderPDF());
    }

    public function testBoletoSicredi()
    {
        $boleto = new Boleto\Sicredi(
            [
                'logo'                   => realpath(__DIR__ . '/../../logos/') . DIRECTORY_SEPARATOR . '748.png',
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
        $this->assertInternalType('array', $boleto->toArray());
        $this->assertNotNull($boleto->renderHTML());
        $this->assertNotNull($boleto->renderPDF());
    }

    public function testBoletoBancoob()
    {
        $boleto = new Boleto\Bancoob(
            [
                'logo'                   => realpath(__DIR__ . '/../../logos/') . DIRECTORY_SEPARATOR . '756.png',
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
        $this->assertInternalType('array', $boleto->toArray());
        $this->assertNotNull($boleto->renderHTML());
        $this->assertNotNull($boleto->renderPDF());
    }
    
    public function testBoletoBnb()
    {
        $boleto = new Boleto\Bnb(
            [
                'logo'                   => realpath(__DIR__ . '/../../logos/') . DIRECTORY_SEPARATOR . '004.png',
                'dataVencimento'         => new \Carbon\Carbon(),
                'valor'                  => 100,
                'multa'                  => 3.0,
                'juros'                  => 1.5,
                'numero'                 => 1,
                'numeroDocumento'        => 1,
                'pagador'                => self::$pagador,
                'beneficiario'           => self::$beneficiario,
                'carteira'               => '21',
                'agencia'                => 1111,
                'conta'                  => 11111,
                'contaDv'                => 1,
                'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
                'instrucoes'             => ['instrucao 1', 'instrucao 2', 'instrucao 3'],
                'aceite'                 => 'S',
                'especieDoc'             => 'DM',
            ]
        );
        $this->assertInternalType('array', $boleto->toArray());
        $this->assertNotNull($boleto->renderHTML());
        $this->assertNotNull($boleto->renderPDF());
    }
}