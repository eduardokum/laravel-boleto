<?php

namespace Eduardokum\LaravelBoleto\Tests\Boleto;

use Exception;
use Eduardokum\LaravelBoleto\Pessoa;
use PHPUnit\Framework\Constraint\IsType;
use Eduardokum\LaravelBoleto\Tests\TestCase;
use Eduardokum\LaravelBoleto\Boleto\Render\Pdf;
use Eduardokum\LaravelBoleto\Boleto\Banco as Boleto;

class BoletoTest extends TestCase
{
    protected static $pagador;

    protected static $beneficiario;

    public static function setUpBeforeClass(): void
    {
        self::$beneficiario = new Pessoa([
            'nome' => 'ACME',
            'endereco' => 'Rua um, 123',
            'cep' => '99999-999',
            'uf' => 'UF',
            'cidade' => 'CIDADE',
            'documento' => '99.999.999/9999-99',
        ]);

        self::$pagador = new Pessoa([
            'nome' => 'Cliente',
            'endereco' => 'Rua um, 123',
            'bairro' => 'Bairro',
            'cep' => '99999-999',
            'uf' => 'UF',
            'cidade' => 'CIDADE',
            'documento' => '999.999.999-99',
        ]);
    }

    public static function tearDownAfterClass(): void
    {
        $aFiles = [
            __DIR__,
            'files',
        ];
        $files = glob(implode(DIRECTORY_SEPARATOR, $aFiles) . '/*'); // get all file names
        foreach ($files as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }
    }

    public function testAddBoletos()
    {
        $boleto = new Boleto\Banrisul([
            'logo' => realpath(__DIR__ . '/../../logos/') . DIRECTORY_SEPARATOR . '041.png',
            'dataVencimento' => $this->vencimento(),
            'valor' => $this->valor(),
            'multa' => $this->multa(),
            'juros' => $this->juros(),
            'numero' => 1,
            'diasBaixaAutomatica' => 20,
            'numeroDocumento' => 1,
            'pagador' => self::$pagador,
            'beneficiario' => self::$beneficiario,
            'carteira' => 1,
            'agencia' => 1111,
            'conta' => 22222,
            'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
            'instrucoes' => ['instrucao 1', 'instrucao 2', 'instrucao 3'],
            'aceite' => $this->aceite(),
            'especieDoc' => 'DM',
        ]);

        $pdf = new Pdf();
        $pdf->addBoletos([$boleto, $boleto]);

        $this->assertNotNull($pdf->gerarBoleto($pdf::OUTPUT_STRING));
    }

    public function testSave()
    {
        $boleto = new Boleto\Banrisul([
            'logo' => realpath(__DIR__ . '/../../logos/') . DIRECTORY_SEPARATOR . '041.png',
            'dataVencimento' => $this->vencimento(),
            'valor' => $this->valor(),
            'multa' => $this->multa(),
            'juros' => $this->juros(),
            'numero' => 1,
            'diasBaixaAutomatica' => 20,
            'numeroDocumento' => 1,
            'pagador' => self::$pagador,
            'beneficiario' => self::$beneficiario,
            'carteira' => 1,
            'agencia' => 1111,
            'conta' => 22222,
            'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
            'instrucoes' => ['instrucao 1', 'instrucao 2', 'instrucao 3'],
            'aceite' => $this->aceite(),
            'especieDoc' => 'DM',
        ]);

        $file = implode(DIRECTORY_SEPARATOR, [
            __DIR__,
            'files',
            'boletos1.pdf',
        ]);

        $pdf = new Pdf();
        $pdf->addBoletos([$boleto, $boleto]);
        $file2 = $pdf->gerarBoleto($pdf::OUTPUT_SAVE, $file);

        $this->assertEquals($file, $file2);
        $this->assertFileExists($file);
    }

    public function testSaveJS()
    {
        $boleto = new Boleto\Banrisul([
            'logo' => realpath(__DIR__ . '/../../logos/') . DIRECTORY_SEPARATOR . '041.png',
            'dataVencimento' => $this->vencimento(),
            'valor' => $this->valor(),
            'multa' => $this->multa(),
            'juros' => $this->juros(),
            'numero' => 1,
            'diasBaixaAutomatica' => 20,
            'numeroDocumento' => 1,
            'pagador' => self::$pagador,
            'beneficiario' => self::$beneficiario,
            'carteira' => 1,
            'agencia' => 1111,
            'conta' => 22222,
            'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
            'instrucoes' => ['instrucao 1', 'instrucao 2', 'instrucao 3'],
            'aceite' => $this->aceite(),
            'especieDoc' => 'DM',
        ]);

        $file = implode(DIRECTORY_SEPARATOR, [
            __DIR__,
            'files',
            'boletos2.pdf',
        ]);

        $pdf = new Pdf();
        $pdf->addBoletos([$boleto, $boleto])->showPrint();
        $file2 = $pdf->gerarBoleto($pdf::OUTPUT_SAVE, $file);

        $this->assertEquals($file, $file2);
        $this->assertFileExists($file);
    }

    public function testWithoutLogo()
    {
        $boleto = new Boleto\Banrisul([
            'dataVencimento' => $this->vencimento(),
            'valor' => $this->valor(),
            'multa' => $this->multa(),
            'juros' => $this->juros(),
            'numero' => 1,
            'diasBaixaAutomatica' => 20,
            'numeroDocumento' => 1,
            'pagador' => self::$pagador,
            'beneficiario' => self::$beneficiario,
            'carteira' => 1,
            'agencia' => 1111,
            'conta' => 22222,
            'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
            'instrucoes' => ['instrucao 1', 'instrucao 2', 'instrucao 3'],
            'aceite' => $this->aceite(),
            'especieDoc' => 'DM',
        ]);

        $file = implode(DIRECTORY_SEPARATOR, [
            __DIR__,
            'files',
            'boletos3.pdf',
        ]);

        $pdf = new Pdf();
        $pdf->addBoletos([$boleto, $boleto]);
        $file3 = $pdf->gerarBoleto($pdf::OUTPUT_SAVE, $file);

        $this->assertEquals($file, $file3);
        $this->assertFileExists($file);
    }

    public function testSemBoletoAdicionado()
    {
        $this->expectException(Exception::class);
        $pdf = new Pdf();
        $pdf->gerarBoleto($pdf::OUTPUT_STRING);
    }

    public function testBoletoBanrisul()
    {
        $boleto = new Boleto\Banrisul([
            'logo' => realpath(__DIR__ . '/../../logos/') . DIRECTORY_SEPARATOR . '041.png',
            'dataVencimento' => $this->vencimento(),
            'valor' => $this->valor(),
            'multa' => $this->multa(),
            'juros' => $this->juros(),
            'numero' => 1,
            'diasBaixaAutomatica' => 20,
            'numeroDocumento' => 1,
            'pagador' => self::$pagador,
            'beneficiario' => self::$beneficiario,
            'carteira' => 1,
            'agencia' => 1111,
            'conta' => 22222,
            'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
            'instrucoes' => ['instrucao 1', 'instrucao 2', 'instrucao 3'],
            'aceite' => $this->aceite(),
            'especieDoc' => 'DM',
        ]);
        $this->assertThat($boleto->toArray(), (new IsType(IsType::TYPE_ARRAY)));
        $this->assertNotNull($boleto->renderHTML());
        $this->assertNotNull($boleto->renderPDF());
    }

    public function testBoletoBB()
    {
        $boleto = new Boleto\Bb([
            'logo' => realpath(__DIR__ . '/../../logos/') . DIRECTORY_SEPARATOR . '001.png',
            'dataVencimento' => $this->vencimento(),
            'valor' => $this->valor(),
            'multa' => $this->multa(),
            'juros' => $this->juros(),
            'numero' => 1,
            'numeroDocumento' => 1,
            'pagador' => self::$pagador,
            'beneficiario' => self::$beneficiario,
            'carteira' => 11,
            'convenio' => 1234567,
            'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
            'instrucoes' => ['instrucao 1', 'instrucao 2', 'instrucao 3'],
            'aceite' => $this->aceite(),
            'especieDoc' => 'DM',
        ]);
        $this->assertThat($boleto->toArray(), (new IsType(IsType::TYPE_ARRAY)));
        $this->assertNotNull($boleto->renderHTML());
        $this->assertNotNull($boleto->renderPDF());
    }

    public function testBoletoBradesco()
    {
        $boleto = new Boleto\Bradesco([
            'logo' => realpath(__DIR__ . '/../../logos/') . DIRECTORY_SEPARATOR . '237.png',
            'dataVencimento' => $this->vencimento(),
            'valor' => $this->valor(),
            'multa' => $this->multa(),
            'juros' => $this->juros(),
            'numero' => 1,
            'diasBaixaAutomatica' => 2,
            'numeroDocumento' => 1,
            'pagador' => self::$pagador,
            'beneficiario' => self::$beneficiario,
            'carteira' => '09',
            'agencia' => 1111,
            'conta' => 9999999,
            'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
            'instrucoes' => ['instrucao 1', 'instrucao 2', 'instrucao 3'],
            'aceite' => $this->aceite(),
            'especieDoc' => 'DM',
        ]);
        $this->assertThat($boleto->toArray(), (new IsType(IsType::TYPE_ARRAY)));
        $this->assertNotNull($boleto->renderHTML());
        $this->assertNotNull($boleto->renderPDF());
    }

    public function testBoletoCaixa()
    {
        $boleto = new Boleto\Caixa([
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
            'instrucoes' => ['instrucao 1', 'instrucao 2', 'instrucao 3'],
            'aceite' => $this->aceite(),
            'especieDoc' => 'DM',
        ]);
        $this->assertThat($boleto->toArray(), (new IsType(IsType::TYPE_ARRAY)));
        $this->assertNotNull($boleto->renderHTML());
        $this->assertNotNull($boleto->renderPDF());
    }

    public function testBoletoHSBC()
    {
        $boleto = new Boleto\Hsbc([
            'logo' => realpath(__DIR__ . '/../../logos/') . DIRECTORY_SEPARATOR . '399.png',
            'dataVencimento' => $this->vencimento(),
            'valor' => $this->valor(),
            'multa' => $this->multa(),
            'juros' => $this->juros(),
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
            'instrucoes' => ['instrucao 1', 'instrucao 2', 'instrucao 3'],
            'aceite' => $this->aceite(),
            'especieDoc' => 'DM',
        ]);
        $this->assertThat($boleto->toArray(), (new IsType(IsType::TYPE_ARRAY)));
        $this->assertNotNull($boleto->renderHTML());
        $this->assertNotNull($boleto->renderPDF());
    }

    public function testBoletoItau()
    {
        $boleto = new Boleto\Itau([
            'logo' => realpath(__DIR__ . '/../../logos/') . DIRECTORY_SEPARATOR . '341.png',
            'dataVencimento' => $this->vencimento(),
            'valor' => $this->valor(),
            'multa' => $this->multa(),
            'juros' => $this->juros(),
            'numero' => 1,
            'numeroDocumento' => 1,
            'pagador' => self::$pagador,
            'beneficiario' => self::$beneficiario,
            'diasBaixaAutomatica' => 2,
            'carteira' => 112,
            'agencia' => 1111,
            'conta' => 99999,
            'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
            'instrucoes' => ['instrucao 1', 'instrucao 2', 'instrucao 3'],
            'aceite' => $this->aceite(),
            'especieDoc' => 'DM',
        ]);
        $this->assertThat($boleto->toArray(), (new IsType(IsType::TYPE_ARRAY)));
        $this->assertNotNull($boleto->renderHTML());
        $this->assertNotNull($boleto->renderPDF());
    }

    public function testBoletoSantander()
    {
        $boleto = new Boleto\Santander([
            'logo' => realpath(__DIR__ . '/../../logos/') . DIRECTORY_SEPARATOR . '033.png',
            'dataVencimento' => $this->vencimento(),
            'valor' => $this->valor(),
            'multa' => $this->multa(),
            'juros' => $this->juros(),
            'numero' => 1,
            'numeroDocumento' => 1,
            'pagador' => self::$pagador,
            'beneficiario' => self::$beneficiario,
            'diasBaixaAutomatica' => 15,
            'carteira' => 101,
            'agencia' => 1111,
            'codigoCliente' => 9999999,
            'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
            'instrucoes' => ['instrucao 1', 'instrucao 2', 'instrucao 3'],
            'aceite' => $this->aceite(),
            'especieDoc' => 'DM',
        ]);
        $this->assertThat($boleto->toArray(), (new IsType(IsType::TYPE_ARRAY)));
        $this->assertNotNull($boleto->renderHTML());
        $this->assertNotNull($boleto->renderPDF());
    }

    public function testBoletoSicredi()
    {
        $boleto = new Boleto\Sicredi([
            'logo' => realpath(__DIR__ . '/../../logos/') . DIRECTORY_SEPARATOR . '748.png',
            'dataVencimento' => new \Carbon\Carbon(),
            'valor' => 100,
            'multa' => false,
            'juros' => false,
            'numero' => 1,
            'numeroDocumento' => 1,
            'pagador' => self::$pagador,
            'beneficiario' => self::$beneficiario,
            'carteira' => '1',
            'byte' => 2,
            'agencia' => 1111,
            'posto' => 11,
            'conta' => 11111,
            'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
            'instrucoes' => ['instrucao 1', 'instrucao 2', 'instrucao 3'],
            'aceite' => 'S',
            'especieDoc' => 'DM',
        ]);
        $this->assertThat($boleto->toArray(), (new IsType(IsType::TYPE_ARRAY)));
        $this->assertNotNull($boleto->renderHTML());
        $this->assertNotNull($boleto->renderPDF());
    }

    public function testBoletoBancoob()
    {
        $boleto = new Boleto\Bancoob([
            'logo' => realpath(__DIR__ . '/../../logos/') . DIRECTORY_SEPARATOR . '756.png',
            'dataVencimento' => new \Carbon\Carbon(),
            'valor' => 100,
            'multa' => false,
            'juros' => false,
            'numero' => 1,
            'numeroDocumento' => 1,
            'pagador' => self::$pagador,
            'beneficiario' => self::$beneficiario,
            'carteira' => '1',
            'byte' => 2,
            'agencia' => 1111,
            'convenio' => 123123,
            'conta' => 11111,
            'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
            'instrucoes' => ['instrucao 1', 'instrucao 2', 'instrucao 3'],
            'aceite' => 'S',
            'especieDoc' => 'DM',
        ]);
        $this->assertThat($boleto->toArray(), (new IsType(IsType::TYPE_ARRAY)));
        $this->assertNotNull($boleto->renderHTML());
        $this->assertNotNull($boleto->renderPDF());
    }

    public function testBoletoBnb()
    {
        $boleto = new Boleto\Bnb([
            'logo' => realpath(__DIR__ . '/../../logos/') . DIRECTORY_SEPARATOR . '004.png',
            'dataVencimento' => new \Carbon\Carbon(),
            'valor' => 100,
            'multa' => 3.0,
            'juros' => 1.5,
            'numero' => 1,
            'numeroDocumento' => 1,
            'pagador' => self::$pagador,
            'beneficiario' => self::$beneficiario,
            'carteira' => '21',
            'agencia' => 1111,
            'conta' => 11111,
            'contaDv' => 1,
            'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
            'instrucoes' => ['instrucao 1', 'instrucao 2', 'instrucao 3'],
            'aceite' => 'S',
            'especieDoc' => 'DM',
        ]);
        $this->assertThat($boleto->toArray(), (new IsType(IsType::TYPE_ARRAY)));
        $this->assertNotNull($boleto->renderHTML());
        $this->assertNotNull($boleto->renderPDF());
    }

    public function testBoletoPine()
    {
        $boleto = new Boleto\Pine([
            'logo' => realpath(__DIR__ . '/../../logos/') . DIRECTORY_SEPARATOR . '643.png',
            'dataVencimento' => new \Carbon\Carbon('2023-03-09'),
            'valor' => 10,
            'multa' => false,
            'juros' => false,
            'numero' => 1,
            'numeroDocumento' => 1,
            'range' => 0,
            'pagador' => self::$pagador,
            'beneficiario' => self::$beneficiario,
            'carteira' => '112',
            'agencia' => '0001',
            'codigoCliente' => '12345',
            'conta' => '1234',
            'modalidadeCarteira' => 'D',
            'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
            'instrucoes' => ['instrucao 1', 'instrucao 2', 'instrucao 3'],
            'aceite' => 'N',
            'especieDoc' => 'DM',
        ]);
        $this->assertThat($boleto->toArray(), (new IsType(IsType::TYPE_ARRAY)));
        $this->assertNotNull($boleto->renderHTML());
        $this->assertNotNull($boleto->renderPDF());
    }

    public function testBoletoFibra()
    {
        $boleto = new Boleto\Fibra([
            'logo' => realpath(__DIR__ . '/../../logos/') . DIRECTORY_SEPARATOR . '224.png',
            'dataVencimento' => new \Carbon\Carbon('2023-01-04'),
            'valor' => 10,
            'multa' => false,
            'juros' => false,
            'numero' => 1,
            'numeroDocumento' => 1,
            'range' => 0,
            'pagador' => self::$pagador,
            'beneficiario' => self::$beneficiario,
            'modalidadeCarteira' => 'D',
            'carteira' => 112,
            'agencia' => '0001',
            'codigoCliente' => '12345',
            'conta' => '1234567',
            'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
            'instrucoes' => ['instrucao 1', 'instrucao 2', 'instrucao 3'],
            'aceite' => 'N',
            'especieDoc' => 'DM',
        ]);
        $this->assertThat($boleto->toArray(), (new IsType(IsType::TYPE_ARRAY)));
        $this->assertNotNull($boleto->renderHTML());
        $this->assertNotNull($boleto->renderPDF());
    }

    public function testBoletoOurinvest()
    {
        $boleto = new Boleto\Ourinvest([
            'logo' => realpath(__DIR__ . '/../../logos/') . DIRECTORY_SEPARATOR . '712.png',
            'dataVencimento' => $this->vencimento(),
            'valor' => $this->valor(),
            'multa' => $this->multa(),
            'juros' => $this->juros(),
            'numero' => 2,
            'numeroDocumento' => 2,
            'pagador' => self::$pagador,
            'beneficiario' => self::$beneficiario,
            'carteira' => '19',
            'agencia' => 0001,
            'conta' => 9999999,
            'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
            'instrucoes' => ['instrucao 1', 'instrucao 2', 'instrucao 3'],
            'aceite' => $this->aceite(),
            'especieDoc' => 'DM',
        ]);
        $this->assertThat($boleto->toArray(), (new IsType(IsType::TYPE_ARRAY)));
        $this->assertNotNull($boleto->renderHTML());
        $this->assertNotNull($boleto->renderPDF());
    }

    public function testBoletoRendimento()
    {
        $boleto = new Boleto\Rendimento([
            'logo' => realpath(__DIR__ . '/../../logos/') . DIRECTORY_SEPARATOR . '633.png',
            'dataVencimento' => $this->vencimento(),
            'valor' => $this->valor(),
            'multa' => $this->multa(),
            'juros' => $this->juros(),
            'numero' => 2,
            'numeroDocumento' => 2,
            'pagador' => self::$pagador,
            'beneficiario' => self::$beneficiario,
            'carteira' => '121',
            'agencia' => '0001',
            'codigoCliente' => '5447390',
            'conta' => '1234',
            'modalidadeCarteira' => '6',
            'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
            'instrucoes' => ['instrucao 1', 'instrucao 2', 'instrucao 3'],
            'aceite' => 'N',
            'especieDoc' => 'DM',
        ]);
        $this->assertThat($boleto->toArray(), (new IsType(IsType::TYPE_ARRAY)));
        $this->assertNotNull($boleto->renderHTML());
        $this->assertNotNull($boleto->renderPDF());
    }

    public function testBoletoBBWithQRCodePix()
    {
        $boleto = new Boleto\Bb([
            'logo' => realpath(__DIR__ . '/../../logos/') . DIRECTORY_SEPARATOR . '001.png',
            'dataVencimento' => $this->vencimento(),
            'valor' => $this->valor(),
            'multa' => $this->multa(),
            'juros' => $this->juros(),
            'numero' => 1,
            'numeroDocumento' => 1,
            'pagador' => self::$pagador,
            'beneficiario' => self::$beneficiario,
            'carteira' => 11,
            'convenio' => 1234567,
            'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
            'instrucoes' => ['instrucao 1', 'instrucao 2', 'instrucao 3'],
            'aceite' => $this->aceite(),
            'especieDoc' => 'DM',
            'pix_qrcode' => 'data:image/png;base64,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',
        ]);

        $boletoHtml = $boleto->renderHTML();

        $this->assertThat($boleto->toArray(), (new IsType(IsType::TYPE_ARRAY)));
        $this->assertNotNull($boletoHtml);
        $this->assertNotNull($boleto->renderPDF());
    }

    public function testBoletoBBWithQRCodePixCopiaECola()
    {
        $boleto = new Boleto\Bb([
            'logo' => realpath(__DIR__ . '/../../logos/') . DIRECTORY_SEPARATOR . '001.png',
            'dataVencimento' => $this->vencimento(),
            'valor' => $this->valor(),
            'multa' => $this->multa(),
            'juros' => $this->juros(),
            'numero' => 1,
            'numeroDocumento' => 1,
            'pagador' => self::$pagador,
            'beneficiario' => self::$beneficiario,
            'carteira' => 11,
            'convenio' => 1234567,
            'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
            'instrucoes' => ['instrucao 1', 'instrucao 2', 'instrucao 3'],
            'aceite' => $this->aceite(),
            'especieDoc' => 'DM',
            'pix_qrcode' => '00020104141234567890123426660014BR.GOV.BCB.PIX014466756C616E6F32303139406578616D706C652E636F6D27300012BR.COM.OUTRO011001234567895204000053039865406123.455802BR5915NOMEDORECEBEDOR6008BRASILIA61087007490062530515RP12345678-201950300017BR.GOV.BCB.BRCODE01051.0.080450014BR.GOV.BCB.PIX0123PADRAO.URL.PIX/0123ABCD81390012BR.COM.OUTRO01190123.ABCD.3456.WXYZ6304EB76',
        ]);

        $boletoHtml = $boleto->renderHTML();

        $this->assertThat($boleto->toArray(), (new IsType(IsType::TYPE_ARRAY)));
        $this->assertNotNull($boletoHtml);
        $this->assertNotNull($boleto->renderPDF());
    }

    public function testBoletoSantanderPix()
    {
        $boleto = new Boleto\Santander([
            'logo' => realpath(__DIR__ . '/../../logos/') . DIRECTORY_SEPARATOR . '033.png',
            'dataVencimento' => $this->vencimento(),
            'valor' => $this->valor(),
            'multa' => $this->multa(),
            'juros' => $this->juros(),
            'numero' => 1,
            'numeroDocumento' => 1,
            'pagador' => self::$pagador,
            'beneficiario' => self::$beneficiario,
            'diasBaixaAutomatica' => 15,
            'carteira' => 101,
            'agencia' => 1111,
            'codigoCliente' => 9999999,
            'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
            'instrucoes' => ['instrucao 1', 'instrucao 2', 'instrucao 3'],
            'aceite' => $this->aceite(),
            'especieDoc' => 'DM',
            'id' => '123456789012345678901234567890',
            'pix_chave' => '39a1178e-db6b-4407-bc7b-b674390acf5f',
            'pix_chave_tipo' => Boleto\Santander::TIPO_CHAVEPIX_ALEATORIA,
        ]);

        $boletoHtml = $boleto->renderHTML();

        $this->assertThat($boleto->toArray(), (new IsType(IsType::TYPE_ARRAY)));
        $this->assertNotNull($boletoHtml);
        $this->assertNotNull($boleto->renderPDF());
    }

    public function testBoletoSantanderPixGeraCopiaECola()
    {
        $boleto = new Boleto\Santander([
            'logo' => realpath(__DIR__ . '/../../logos/') . DIRECTORY_SEPARATOR . '033.png',
            'dataVencimento' => $this->vencimento(),
            'valor' => 100,
            'multa' => $this->multa(),
            'juros' => $this->juros(),
            'numero' => 1,
            'numeroDocumento' => 1,
            'pagador' => self::$pagador,
            'beneficiario' => self::$beneficiario,
            'diasBaixaAutomatica' => 15,
            'carteira' => 101,
            'agencia' => 1111,
            'codigoCliente' => 9999999,
            'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
            'instrucoes' => ['instrucao 1', 'instrucao 2', 'instrucao 3'],
            'aceite' => $this->aceite(),
            'especieDoc' => 'DM',
            'id' => '123456789012345678901234567890',
            'pix_chave' => '39a1178e-db6b-4407-bc7b-b674390acf5f',
            'pix_chave_tipo' => Boleto\Santander::TIPO_CHAVEPIX_ALEATORIA,
        ]);
        $boleto->gerarPixCopiaECola();

        $this->assertEquals('00020101021226580014br.gov.bcb.pix013639a1178e-db6b-4407-bc7b-b674390acf5f5204000053039865406100.005802BR5904ACME6006CIDADE623405301234567890123456789012345678906304F0C4', $boleto->toArray()['pix_qrcode']);
    }

    public function testBoletoSantanderPixSemTipo()
    {
        $this->expectException(Exception::class);
        $boleto = new Boleto\Santander([
            'logo' => realpath(__DIR__ . '/../../logos/') . DIRECTORY_SEPARATOR . '033.png',
            'dataVencimento' => $this->vencimento(),
            'valor' => $this->valor(),
            'multa' => $this->multa(),
            'juros' => $this->juros(),
            'numero' => 1,
            'numeroDocumento' => 1,
            'pagador' => self::$pagador,
            'beneficiario' => self::$beneficiario,
            'diasBaixaAutomatica' => 15,
            'carteira' => 101,
            'agencia' => 1111,
            'codigoCliente' => 9999999,
            'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
            'instrucoes' => ['instrucao 1', 'instrucao 2', 'instrucao 3'],
            'aceite' => $this->aceite(),
            'especieDoc' => 'DM',
            'id' => '123456789012345678901234567890',
            'pix_chave' => '39a1178e-db6b-4407-bc7b-b674390acf5f',
        ]);

        $boletoHtml = $boleto->renderHTML();

        $this->assertThat($boleto->toArray(), (new IsType(IsType::TYPE_ARRAY)));
        $this->assertNotNull($boletoHtml);
        $this->assertNotNull($boleto->renderPDF());
    }

    public function testBoletoSantanderPixTipoSemPix()
    {
        $this->expectException(Exception::class);
        $boleto = new Boleto\Santander([
            'logo' => realpath(__DIR__ . '/../../logos/') . DIRECTORY_SEPARATOR . '033.png',
            'dataVencimento' => $this->vencimento(),
            'valor' => $this->valor(),
            'multa' => $this->multa(),
            'juros' => $this->juros(),
            'numero' => 1,
            'numeroDocumento' => 1,
            'pagador' => self::$pagador,
            'beneficiario' => self::$beneficiario,
            'diasBaixaAutomatica' => 15,
            'carteira' => 101,
            'agencia' => 1111,
            'codigoCliente' => 9999999,
            'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
            'instrucoes' => ['instrucao 1', 'instrucao 2', 'instrucao 3'],
            'aceite' => $this->aceite(),
            'especieDoc' => 'DM',
            'id' => '123456789012345678901234567890',
            'pix_chave_tipo' => Boleto\Santander::TIPO_CHAVEPIX_ALEATORIA,
        ]);

        $boletoHtml = $boleto->renderHTML();

        $this->assertThat($boleto->toArray(), (new IsType(IsType::TYPE_ARRAY)));
        $this->assertNotNull($boletoHtml);
        $this->assertNotNull($boleto->renderPDF());
    }

    public function testBoletoSantanderInvalidoID()
    {
        $this->expectException(Exception::class);
        $boleto = new Boleto\Santander([
            'logo' => realpath(__DIR__ . '/../../logos/') . DIRECTORY_SEPARATOR . '033.png',
            'dataVencimento' => $this->vencimento(),
            'valor' => $this->valor(),
            'multa' => $this->multa(),
            'juros' => $this->juros(),
            'numero' => 1,
            'numeroDocumento' => 1,
            'pagador' => self::$pagador,
            'beneficiario' => self::$beneficiario,
            'diasBaixaAutomatica' => 15,
            'carteira' => 101,
            'agencia' => 1111,
            'codigoCliente' => 9999999,
            'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
            'instrucoes' => ['instrucao 1', 'instrucao 2', 'instrucao 3'],
            'aceite' => $this->aceite(),
            'especieDoc' => 'DM',
            'id' => '12345678901234567890',
            'pix_chave_tipo' => Boleto\Santander::TIPO_CHAVEPIX_ALEATORIA,
        ]);
    }

    public function testBoletoSantanderPixTipoEPixSemID()
    {
        $this->expectException(Exception::class);
        $boleto = new Boleto\Santander([
            'logo' => realpath(__DIR__ . '/../../logos/') . DIRECTORY_SEPARATOR . '033.png',
            'dataVencimento' => $this->vencimento(),
            'valor' => $this->valor(),
            'multa' => $this->multa(),
            'juros' => $this->juros(),
            'numero' => 1,
            'numeroDocumento' => 1,
            'pagador' => self::$pagador,
            'beneficiario' => self::$beneficiario,
            'diasBaixaAutomatica' => 15,
            'carteira' => 101,
            'agencia' => 1111,
            'codigoCliente' => 9999999,
            'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
            'instrucoes' => ['instrucao 1', 'instrucao 2', 'instrucao 3'],
            'aceite' => $this->aceite(),
            'especieDoc' => 'DM',
            'pix_chave' => '39a1178e-db6b-4407-bc7b-b674390acf5f',
            'pix_chave_tipo' => Boleto\Santander::TIPO_CHAVEPIX_ALEATORIA,
        ]);

        $boletoHtml = $boleto->renderHTML();

        $this->assertThat($boleto->toArray(), (new IsType(IsType::TYPE_ARRAY)));
        $this->assertNotNull($boletoHtml);
        $this->assertNotNull($boleto->renderPDF());
    }


    public function testBoletoAilos()
    {
        $boleto = new Boleto\Ailos([
            'logo' => realpath(__DIR__ . '/../../logos/') . DIRECTORY_SEPARATOR . '085.png',
            'dataVencimento' => new \Carbon\Carbon(),
            'valor' => 100,
            'multa' => false,
            'juros' => false,
            'numero' => 1,
            'numeroDocumento' => 1,
            'pagador' => self::$pagador,
            'beneficiario' => self::$beneficiario,
            'carteira' => '1',
            'convenio' => '000000',
            'agencia' => 1111,
            'agenciaDv' => 1,
            'conta' => 11111,
            'contaDv' => 1,
            'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
            'instrucoes' => ['instrucao 1', 'instrucao 2', 'instrucao 3'],
            'aceite' => 'S',
            'especieDoc' => 'DM',
        ]);
        $this->assertThat($boleto->toArray(), (new IsType(IsType::TYPE_ARRAY)));
        $this->assertNotNull($boleto->renderHTML());
        $this->assertNotNull($boleto->renderPDF());
    }
}
