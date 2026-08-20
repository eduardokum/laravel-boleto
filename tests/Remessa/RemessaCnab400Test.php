<?php

namespace Eduardokum\LaravelBoleto\Tests\Remessa;

use Exception;
use Eduardokum\LaravelBoleto\Util;
use Eduardokum\LaravelBoleto\Pessoa;
use Eduardokum\LaravelBoleto\Tests\TestCase;
use Eduardokum\LaravelBoleto\Boleto\Banco as Boleto;
use Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400\Banco as Remessa;

class RemessaCnab400Test extends TestCase
{
    protected static $pagador;

    protected static $beneficiario;

    public static function setUpBeforeClass(): void
    {
        self::$beneficiario = new Pessoa([
            'nome'      => 'ACME',
            'endereco'  => 'Rua um, 123',
            'cep'       => '99999-999',
            'uf'        => 'UF',
            'cidade'    => 'CIDADE',
            'documento' => '99.999.999/9999-99',
        ]);

        self::$pagador = new Pessoa([
            'nome'      => 'Cliente',
            'endereco'  => 'Rua um, 123',
            'bairro'    => 'Bairro',
            'cep'       => '99999-999',
            'uf'        => 'UF',
            'cidade'    => 'CIDADE',
            'documento' => '999.999.999-99',
        ]);
    }

    public static function tearDownAfterClass(): void
    {
        $aFiles = [
            __DIR__,
            'files',
            'cnab400',
        ];
        $files = glob(implode(DIRECTORY_SEPARATOR, $aFiles) . '/*'); // get all file names
        foreach ($files as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }
    }

    public function testRemessaCamposInvalidos()
    {
        $this->expectException(Exception::class);
        $remessa = new Remessa\Banrisul([
            'codigoCliente' => 11112222222,
            'beneficiario'  => self::$beneficiario,
        ]);
        $remessa->gerar();
    }

    public function testRemessaCarteiraIndisponivel()
    {
        $this->expectException(Exception::class);
        $remessa = new Remessa\Banrisul([
            'agencia'       => 1111,
            'conta'         => 22222,
            'carteira'      => '123',
            'codigoCliente' => 11112222222,
            'beneficiario'  => self::$beneficiario,
        ]);
        $remessa->gerar();
    }

    public function testRemessaAddboletosCnab400()
    {
        $boleto = new Boleto\Banrisul([
            'logo'                   => realpath(__DIR__ . '/../logos/') . DIRECTORY_SEPARATOR . '041.png',
            'dataVencimento'         => $this->vencimento(),
            'valor'                  => $this->valor(),
            'multa'                  => $this->multa(),
            'juros'                  => $this->juros(),
            'numero'                 => 1,
            'diasBaixaAutomatica'    => 20,
            'numeroDocumento'        => 1,
            'pagador'                => self::$pagador,
            'beneficiario'           => self::$beneficiario,
            'carteira'               => 1,
            'agencia'                => 1111,
            'conta'                  => 22222,
            'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
            'instrucoes'             => ['instrucao 1', 'instrucao 2', 'instrucao 3'],
            'aceite'                 => 'S',
            'especieDoc'             => 'DM',
        ]);

        $boleto2 = $boleto;
        $boleto2->setNumeroDocumento(2);

        $remessa = new Remessa\Banrisul([
            'agencia'       => 1111,
            'conta'         => 22222,
            'carteira'      => 1,
            'codigoCliente' => 11112222222,
            'beneficiario'  => self::$beneficiario,
        ]);
        $remessa->addBoletos([$boleto, $boleto2]);
        $this->assertEquals(4, count(Util::file2array($remessa->gerar())));
    }

    public function testRemessaBanrisulCnab400()
    {
        $boleto = new Boleto\Banrisul([
            'logo'                   => realpath(__DIR__ . '/../logos/') . DIRECTORY_SEPARATOR . '041.png',
            'dataVencimento'         => $this->vencimento(),
            'valor'                  => $this->valor(),
            'multa'                  => $this->multa(),
            'juros'                  => $this->juros(),
            'numero'                 => 1,
            'diasBaixaAutomatica'    => 20,
            'numeroDocumento'        => 1,
            'pagador'                => self::$pagador,
            'beneficiario'           => self::$beneficiario,
            'carteira'               => 1,
            'agencia'                => 1111,
            'conta'                  => 22222,
            'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
            'instrucoes'             => ['instrucao 1', 'instrucao 2', 'instrucao 3'],
            'aceite'                 => 'S',
            'especieDoc'             => 'DM',
        ]);
        $remessa = new Remessa\Banrisul([
            'agencia'       => 1111,
            'conta'         => 22222,
            'carteira'      => 1,
            'codigoCliente' => 11112222222,
            'beneficiario'  => self::$beneficiario,
        ]);
        $remessa->addBoleto($boleto);

        $file = implode(DIRECTORY_SEPARATOR, [
            __DIR__,
            'files',
            'cnab400',
            'banrisul.txt',
        ]);
        $file2 = $remessa->save($file);

        $this->assertFileExists($file);
        $this->assertEquals($file, $file2);
    }

    public function testRemessaBBCnab400()
    {
        $boleto = new Boleto\Bb([
            'logo'                   => realpath(__DIR__ . '/../logos/') . DIRECTORY_SEPARATOR . '001.png',
            'dataVencimento'         => $this->vencimento(),
            'valor'                  => $this->valor(),
            'multa'                  => $this->multa(),
            'juros'                  => $this->juros(),
            'numero'                 => 1,
            'numeroDocumento'        => 1,
            'pagador'                => self::$pagador,
            'beneficiario'           => self::$beneficiario,
            'carteira'               => 11,
            'convenio'               => 1234567,
            'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
            'instrucoes'             => ['instrucao 1', 'instrucao 2', 'instrucao 3'],
            'aceite'                 => 'S',
            'especieDoc'             => 'DM',
        ]);

        $remessa = new Remessa\Bb([
            'agencia'      => 1111,
            'carteira'     => 11,
            'conta'        => 999999999,
            'convenio'     => 1234567,
            'beneficiario' => self::$beneficiario,
        ]);
        $remessa->addBoleto($boleto);

        $file = implode(DIRECTORY_SEPARATOR, [
            __DIR__,
            'files',
            'cnab400',
            'bb.txt',
        ]);

        $file2 = $remessa->save($file);

        $this->assertFileExists($file);
        $this->assertEquals($file, $file2);
    }

    public function testRemessaBBCnab400Extendida()
    {
        $boleto = new Boleto\Bb([
            'logo'                   => realpath(__DIR__ . '/../logos/') . DIRECTORY_SEPARATOR . '001.png',
            'dataVencimento'         => $this->vencimento(),
            'valor'                  => $this->valor(),
            'multa'                  => $this->multa(),
            'juros'                  => $this->juros(),
            'numero'                 => 1,
            'numeroDocumento'        => 1,
            'pagador'                => self::$pagador,
            'beneficiario'           => self::$beneficiario,
            'carteira'               => 11,
            'convenio'               => 1234567,
            'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
            'instrucoes'             => ['instrucao 1', 'instrucao 2', 'instrucao 3'],
            'aceite'                 => 'S',
            'especieDoc'             => 'DM',
            'chaveNfe'               => '12345678901234567890123456789012345678901234',
        ]);

        $remessa = new Remessa\Bb([
            'agencia'      => 1111,
            'carteira'     => 11,
            'conta'        => 999999999,
            'convenio'     => 1234567,
            'beneficiario' => self::$beneficiario,
        ]);
        $remessa->addBoleto($boleto);

        $cnab = explode($remessa->getFimLinha(), $remessa->gerar());
        $this->assertEquals(444, strlen($cnab[1]));
    }

    public function testRemessaBradescoCnab400()
    {
        $boleto = new Boleto\Bradesco([
            'logo'                   => realpath(__DIR__ . '/../logos/') . DIRECTORY_SEPARATOR . '237.png',
            'dataVencimento'         => $this->vencimento(),
            'valor'                  => $this->valor(),
            'multa'                  => $this->multa(),
            'juros'                  => $this->juros(),
            'numero'                 => 1,
            'diasBaixaAutomatica'    => 2,
            'numeroDocumento'        => 1,
            'pagador'                => self::$pagador,
            'beneficiario'           => self::$beneficiario,
            'carteira'               => '09',
            'agencia'                => 1111,
            'conta'                  => 9999999,
            'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
            'instrucoes'             => ['instrucao 1', 'instrucao 2', 'instrucao 3'],
            'aceite'                 => 'S',
            'especieDoc'             => 'DM',
        ]);

        $remessa = new Remessa\Bradesco([
            'idRemessa'     => 1,
            'agencia'       => 1111,
            'carteira'      => '09',
            'conta'         => 99999999,
            'contaDv'       => 9,
            'codigoCliente' => 12345678901234567890,
            'beneficiario'  => self::$beneficiario,
        ]);
        $remessa->addBoleto($boleto);

        $file = implode(DIRECTORY_SEPARATOR, [
            __DIR__,
            'files',
            'cnab400',
            'bradesco.txt',
        ]);

        $file2 = $remessa->save($file);

        $this->assertFileExists($file);
        $this->assertEquals($file, $file2);
    }

    public function testRemessaBradescoCnab400Extendida()
    {
        $boleto = new Boleto\Bradesco([
            'logo'                   => realpath(__DIR__ . '/../logos/') . DIRECTORY_SEPARATOR . '237.png',
            'dataVencimento'         => $this->vencimento(),
            'valor'                  => $this->valor(),
            'multa'                  => $this->multa(),
            'juros'                  => $this->juros(),
            'numero'                 => 1,
            'diasBaixaAutomatica'    => 2,
            'numeroDocumento'        => 1,
            'pagador'                => self::$pagador,
            'beneficiario'           => self::$beneficiario,
            'carteira'               => '09',
            'agencia'                => 1111,
            'conta'                  => 9999999,
            'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
            'instrucoes'             => ['instrucao 1', 'instrucao 2', 'instrucao 3'],
            'aceite'                 => 'S',
            'especieDoc'             => 'DM',
            'chaveNfe'               => '12345678901234567890123456789012345678901234',
        ]);

        $remessa = new Remessa\Bradesco([
            'idRemessa'     => 1,
            'agencia'       => 1111,
            'carteira'      => '09',
            'conta'         => 99999999,
            'contaDv'       => 9,
            'codigoCliente' => 12345678901234567890,
            'beneficiario'  => self::$beneficiario,
        ]);
        $remessa->addBoleto($boleto);

        $cnab = explode($remessa->getFimLinha(), $remessa->gerar());
        $this->assertEquals(444, strlen($cnab[1]));
    }

    public function testRemessaAbcCnab400()
    {
        $boleto = new Boleto\Abc([
            'logo'                   => realpath(__DIR__ . '/../logos/') . DIRECTORY_SEPARATOR . '246.png',
            'dataVencimento'         => $this->vencimento(),
            'valor'                  => $this->valor(),
            'multa'                  => $this->multa(),
            'juros'                  => $this->juros(),
            'numero'                 => 1,
            'numeroDocumento'        => 1,
            'pagador'                => self::$pagador,
            'beneficiario'           => self::$beneficiario,
            'carteira'               => 6,
            'operacao'               => 1234567,
            'agencia'                => '0001',
            'conta'                  => '7654321',
            'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
            'instrucoes'             => ['instrucao 1', 'instrucao 2', 'instrucao 3'],
            'aceite'                 => 'S',
            'especieDoc'             => 'DM',
        ]);

        $remessa = new Remessa\Abc([
            'agencia'       => '0001',
            'conta'         => '7654321',
            'carteira'      => 6,
            'codigoCliente' => '00011234567',
            'beneficiario'  => self::$beneficiario,
        ]);
        $remessa->addBoleto($boleto);

        $file = implode(DIRECTORY_SEPARATOR, [
            __DIR__,
            'files',
            'cnab400',
            'abc.txt',
        ]);

        $file2 = $remessa->save($file);

        $this->assertFileExists($file);
        $this->assertEquals($file, $file2);
    }

    public function testRemessaGrafenoCnab400()
    {
        $boleto = new Boleto\Grafeno([
            'logo'                   => realpath(__DIR__ . '/../../logos/') . DIRECTORY_SEPARATOR . '274.png',
            'dataVencimento'         => $this->vencimento(),
            'valor'                  => $this->valor(),
            'multa'                  => $this->multa(),
            'juros'                  => $this->juros(),
            'numero'                 => 1,
            'diasBaixaAutomatica'    => 2,
            'numeroDocumento'        => 1,
            'pagador'                => self::$pagador,
            'beneficiario'           => self::$beneficiario,
            'carteira'               => '1',
            'agencia'                => '0001',
            'conta'                  => '12345678',
            'range'                  => '25000000000',
            'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
            'instrucoes'             => ['instrucao 1', 'instrucao 2', 'instrucao 3'],
            'aceite'                 => $this->aceite(),
            'especieDoc'             => 'DM',
        ]);

        $remessa = new Remessa\Grafeno([
            'idRemessa'    => 1,
            'agencia'      => '0001',
            'carteira'     => '1',
            'conta'        => '12345678',
            'contaDv'      => '9',
            'convenio'     => '12345678',
            'beneficiario' => self::$beneficiario,
        ]);
        $remessa->addBoleto($boleto);

        $file = implode(DIRECTORY_SEPARATOR, [
            __DIR__,
            'files',
            'cnab400',
            'grafeno.txt',
        ]);

        $file2 = $remessa->save($file);

        $this->assertFileExists($file);
        $this->assertEquals($file, $file2);
    }

    public function testRemessaCaixaCnab400()
    {
        $boleto = new Boleto\Caixa([
            'logo'                   => realpath(__DIR__ . '/../logos/') . DIRECTORY_SEPARATOR . '104.png',
            'dataVencimento'         => $this->vencimento(),
            'valor'                  => $this->valor(),
            'multa'                  => $this->multa(),
            'juros'                  => $this->juros(),
            'numero'                 => 1,
            'numeroDocumento'        => 1,
            'pagador'                => self::$pagador,
            'diasBaixaAutomatica'    => 2,
            'beneficiario'           => self::$beneficiario,
            'agencia'                => 1111,
            'conta'                  => 123456,
            'carteira'               => 'RG',
            'codigoCliente'          => 999999,
            'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
            'instrucoes'             => ['instrucao 1', 'instrucao 2', 'instrucao 3'],
            'aceite'                 => 'S',
            'especieDoc'             => 'DM',
        ]);

        $remessa = new Remessa\Caixa([
            'agencia'       => 1111,
            'conta'         => 123456,
            'idremessa'     => 1,
            'carteira'      => 'RG',
            'codigoCliente' => 999999,
            'beneficiario'  => self::$beneficiario,
        ]);
        $remessa->addBoleto($boleto);

        $file = implode(DIRECTORY_SEPARATOR, [
            __DIR__,
            'files',
            'cnab400',
            'caixa.txt',
        ]);

        $file2 = $remessa->save($file);

        $this->assertFileExists($file);
        $this->assertEquals($file, $file2);
    }

    public function testRemessaHSBCCnab400()
    {
        $boleto = new Boleto\Hsbc([
            'logo'                   => realpath(__DIR__ . '/../logos/') . DIRECTORY_SEPARATOR . '399.png',
            'dataVencimento'         => $this->vencimento(),
            'valor'                  => $this->valor(),
            'multa'                  => $this->multa(),
            'juros'                  => $this->juros(),
            'numero'                 => 1,
            'numeroDocumento'        => 1,
            'pagador'                => self::$pagador,
            'beneficiario'           => self::$beneficiario,
            'carteira'               => 'CSB',
            'range'                  => 12345,
            'agencia'                => 1111,
            'conta'                  => 999999,
            'contaDv'                => 9,
            'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
            'instrucoes'             => ['instrucao 1', 'instrucao 2', 'instrucao 3'],
            'aceite'                 => 'S',
            'especieDoc'             => 'DM',
        ]);

        $remessa = new Remessa\Hsbc([
            'agencia'      => 1111,
            'carteira'     => 'CSB',
            'conta'        => 999999,
            'contaDv'      => 9,
            'beneficiario' => self::$beneficiario,
        ]);
        $remessa->addBoleto($boleto);

        $file = implode(DIRECTORY_SEPARATOR, [
            __DIR__,
            'files',
            'cnab400',
            'hsbc.txt',
        ]);

        $file2 = $remessa->save($file);

        $this->assertFileExists($file);
        $this->assertEquals($file, $file2);
    }

    public function testRemessaItauCnab400()
    {
        $boleto = new Boleto\Itau([
            'logo'                   => realpath(__DIR__ . '/../logos/') . DIRECTORY_SEPARATOR . '341.png',
            'dataVencimento'         => $this->vencimento(),
            'valor'                  => $this->valor(),
            'multa'                  => $this->multa(),
            'juros'                  => $this->juros(),
            'numero'                 => 1,
            'numeroDocumento'        => 1,
            'pagador'                => self::$pagador,
            'beneficiario'           => self::$beneficiario,
            'diasBaixaAutomatica'    => 2,
            'carteira'               => 112,
            'agencia'                => 1111,
            'conta'                  => 99999,
            'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
            'instrucoes'             => ['instrucao 1', 'instrucao 2', 'instrucao 3'],
            'aceite'                 => 'S',
            'especieDoc'             => 'DM',
        ]);

        $remessa = new Remessa\Itau([
            'agencia'      => 1111,
            'conta'        => 99999,
            'contaDv'      => 9,
            'carteira'     => 112,
            'beneficiario' => self::$beneficiario,
        ]);
        $remessa->addBoleto($boleto);

        $file = implode(DIRECTORY_SEPARATOR, [
            __DIR__,
            'files',
            'cnab400',
            'itau.txt',
        ]);

        $file2 = $remessa->save($file);

        $this->assertFileExists($file);
        $this->assertEquals($file, $file2);
    }

    public function testRemessaItauCnab400AlterarValor()
    {
        $boleto = new Boleto\Itau([
            'logo'                => realpath(__DIR__ . '/../logos/') . DIRECTORY_SEPARATOR . '341.png',
            'dataVencimento'      => $this->vencimento(),
            'valor'               => 250.00,
            'numero'              => 1,
            'numeroDocumento'     => 1,
            'pagador'             => self::$pagador,
            'beneficiario'        => self::$beneficiario,
            'carteira'            => 112,
            'agencia'             => 1111,
            'conta'               => 99999,
            'especieDoc'          => 'DM',
        ]);
        $boleto->alterarValor();

        $remessa = new Remessa\Itau([
            'agencia'      => 1111,
            'conta'        => 99999,
            'contaDv'      => 9,
            'carteira'     => 112,
            'beneficiario' => self::$beneficiario,
        ]);
        $remessa->addBoleto($boleto);

        $content = $remessa->gerar();
        $lines = explode("\r\n", trim($content));
        // linha de detalhe é a segunda linha (índice 1)
        $detalhe = $lines[1];
        // posições 109-110 (1-based) = substr offset 108, length 2
        $this->assertEquals('31', substr($detalhe, 108, 2));
    }

    public function testRemessaSantanderCnab400()
    {
        $boleto = new Boleto\Santander([
            'logo'                   => realpath(__DIR__ . '/../logos/') . DIRECTORY_SEPARATOR . '033.png',
            'dataVencimento'         => $this->vencimento(),
            'valor'                  => $this->valor(),
            'multa'                  => $this->multa(),
            'juros'                  => $this->juros(),
            'numero'                 => 1,
            'numeroDocumento'        => 1,
            'pagador'                => self::$pagador,
            'beneficiario'           => self::$beneficiario,
            'diasBaixaAutomatica'    => 15,
            'carteira'               => 101,
            'agencia'                => 1111,
            'conta'                  => 99999999,
            'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
            'instrucoes'             => ['instrucao 1', 'instrucao 2', 'instrucao 3'],
            'aceite'                 => 'S',
            'especieDoc'             => 'DM',
        ]);

        $remessa = new Remessa\Santander([
            'agencia'       => 1111,
            'carteira'      => 101,
            'conta'         => 99999999,
            'codigoCliente' => 12345678,
            'beneficiario'  => self::$beneficiario,
        ]);
        $remessa->addBoleto($boleto);

        $file = implode(DIRECTORY_SEPARATOR, [
            __DIR__,
            'files',
            'cnab400',
            'santander.txt',
        ]);

        $file2 = $remessa->save($file);

        $this->assertFileExists($file);
        $this->assertEquals($file, $file2);
    }

    public function testRemessaSicrediCnab400()
    {
        $boleto = new Boleto\Sicredi([
            'logo'                   => realpath(__DIR__ . '/../logos/') . DIRECTORY_SEPARATOR . '748.png',
            'dataVencimento'         => $this->vencimento(),
            'valor'                  => $this->valor(),
            'multa'                  => $this->multa(),
            'juros'                  => $this->juros(),
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
        ]);

        $remessa = new Remessa\Sicredi([
            'agencia'      => 2606,
            'carteira'     => '1',
            'conta'        => 12510,
            'idremessa'    => 1,
            'beneficiario' => self::$beneficiario,
        ]);
        $remessa->addBoleto($boleto);

        $file = implode(DIRECTORY_SEPARATOR, [
            __DIR__,
            'files',
            'cnab400',
            'sicredi.txt',
        ]);

        $file2 = $remessa->save($file);

        $this->assertFileExists($file);
        $this->assertEquals($file, $file2);
    }

    public function testRemessaBancoobCnab400()
    {
        $boleto = new Boleto\Bancoob([
            'logo'                   => realpath(__DIR__ . '/../logos/') . DIRECTORY_SEPARATOR . '748.png',
            'dataVencimento'         => $this->vencimento(),
            'valor'                  => $this->valor(),
            'multa'                  => $this->multa(),
            'juros'                  => $this->juros(),
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
        ]);

        $remessa = new Remessa\Bancoob([
            'agencia'      => 2606,
            'carteira'     => '1',
            'conta'        => 12510,
            'convenio'     => 123123,
            'beneficiario' => self::$beneficiario,
        ]);
        $remessa->addBoleto($boleto);

        $file = implode(DIRECTORY_SEPARATOR, [
            __DIR__,
            'files',
            'cnab400',
            'bancoob.txt',
        ]);

        $file2 = $remessa->save($file);

        $this->assertFileExists($file);
        $this->assertEquals($file, $file2);
    }

    public function testRemessaPineCnab400()
    {
        $boleto = new Boleto\Pine([
            'logo'                   => realpath(__DIR__ . '/../logos/') . DIRECTORY_SEPARATOR . '643.png',
            'dataVencimento'         => $this->vencimento(),
            'valor'                  => $this->valor(),
            'multa'                  => $this->multa(),
            'juros'                  => $this->juros(),
            'numero'                 => 1,
            'numeroDocumento'        => 1,
            'range'                  => 0,
            'pagador'                => self::$pagador,
            'beneficiario'           => self::$beneficiario,
            'carteira'               => '1',
            'agencia'                => '0001',
            'codigoCliente'          => '12345',
            'conta'                  => '1234',
            'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
            'instrucoes'             => ['instrucao 1', 'instrucao 2', 'instrucao 3'],
            'aceite'                 => 'N',
            'especieDoc'             => 'DM',
        ]);

        $remessa = new Remessa\Pine([
            'agencia'       => '0001',
            'conta'         => '1234',
            'contaDv'       => 9,
            'carteira'      => 112,
            'beneficiario'  => self::$beneficiario,
            'codigoCliente' => '1234',
        ]);
        $remessa->addBoleto($boleto);

        $file = implode(DIRECTORY_SEPARATOR, [
            __DIR__,
            'files',
            'cnab400',
            'pine.txt',
        ]);

        $file2 = $remessa->save($file);

        $this->assertFileExists($file);
        $this->assertEquals($file, $file2);
    }

    public function testRemessaFibraCnab400()
    {
        $boleto = new Boleto\Fibra([
            'logo'                   => realpath(__DIR__ . '/../logos/') . DIRECTORY_SEPARATOR . '224.png',
            'dataVencimento'         => $this->vencimento(),
            'valor'                  => $this->valor(),
            'multa'                  => $this->multa(),
            'juros'                  => $this->juros(),
            'numero'                 => 1,
            'numeroDocumento'        => 1,
            'range'                  => 0,
            'pagador'                => self::$pagador,
            'beneficiario'           => self::$beneficiario,
            'carteira'               => 'D',
            'agencia'                => '0001',
            'codigoCliente'          => '12345',
            'conta'                  => '1234567',
            'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
            'instrucoes'             => ['instrucao 1', 'instrucao 2', 'instrucao 3'],
            'aceite'                 => 'N',
            'especieDoc'             => 'DM',
        ]);

        $remessa = new Remessa\Fibra([
            'agencia'       => '0001',
            'conta'         => '1234567',
            'contaDv'       => 9,
            'carteira'      => 112,
            'beneficiario'  => self::$beneficiario,
            'codigoCliente' => '12345',
        ]);
        $remessa->addBoleto($boleto);

        $file = implode(DIRECTORY_SEPARATOR, [
            __DIR__,
            'files',
            'cnab400',
            'fibra.txt',
        ]);

        $file2 = $remessa->save($file);

        $this->assertFileExists($file);
        $this->assertEquals($file, $file2);
    }

    public function testRemessaOurinvestCnab400()
    {
        $boleto = new Boleto\Ourinvest([
            'logo'                   => realpath(__DIR__ . '/../logos/') . DIRECTORY_SEPARATOR . '712.png',
            'dataVencimento'         => $this->vencimento(),
            'valor'                  => $this->valor(),
            'multa'                  => $this->multa(),
            'juros'                  => $this->juros(),
            'numero'                 => 2,
            'numeroDocumento'        => 2,
            'pagador'                => self::$pagador,
            'beneficiario'           => self::$beneficiario,
            'carteira'               => '19',
            'agencia'                => 0001,
            'conta'                  => 9999999,
            'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
            'instrucoes'             => ['instrucao 1', 'instrucao 2', 'instrucao 3'],
            'aceite'                 => 'S',
            'especieDoc'             => 'DM',
            'chaveNfe'               => '12345678901234567890123456789012345678901234',
        ]);

        $remessa = new Remessa\Ourinvest([
            'idRemessa'    => 1,
            'agencia'      => 1111,
            'carteira'     => '19',
            'conta'        => 1234567,
            'contaDv'      => 9,
            'beneficiario' => self::$beneficiario,
        ]);
        $remessa->addBoleto($boleto);

        $file = implode(DIRECTORY_SEPARATOR, [
            __DIR__,
            'files',
            'cnab400',
            'ourinvest.txt',
        ]);

        $file2 = $remessa->save($file);

        $this->assertFileExists($file2);
    }

    public function testRemessaRendimentoCnab400()
    {
        $boleto = new Boleto\Rendimento([
            'logo'                   => realpath(__DIR__ . '/../../logos/') . DIRECTORY_SEPARATOR . '633.png',
            'dataVencimento'         => $this->vencimento(),
            'valor'                  => $this->valor(),
            'multa'                  => $this->multa(),
            'juros'                  => $this->juros(),
            'numero'                 => 2,
            'numeroDocumento'        => 2,
            'pagador'                => self::$pagador,
            'beneficiario'           => self::$beneficiario,
            'carteira'               => '6',
            'agencia'                => '0001',
            'codigoCliente'          => '5447390',
            'conta'                  => '1234',
            'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
            'instrucoes'             => ['instrucao 1', 'instrucao 2', 'instrucao 3'],
            'aceite'                 => 'N',
            'especieDoc'             => 'DM',
        ]);

        $remessa = new Remessa\Rendimento([
            'agencia'       => '0001',
            'conta'         => '1234',
            'contaDv'       => 9,
            'carteira'      => 121,
            'codigoCliente' => '5447390',
            'beneficiario'  => self::$beneficiario,
        ]);
        $remessa->addBoleto($boleto);

        $file = implode(DIRECTORY_SEPARATOR, [
            __DIR__,
            'files',
            'cnab400',
            'rendimento.txt',
        ]);

        $file2 = $remessa->save($file);

        $this->assertFileExists($file2);
    }

    public function testRemessaInterCnab400()
    {
        $boleto = new Boleto\Inter([
            'logo'            => realpath(__DIR__ . '/../logos/') . DIRECTORY_SEPARATOR . '077.png',
            'dataVencimento'  => $this->vencimento(),
            'valor'           => $this->valor(),
            'multa'           => $this->multa(),
            'juros'           => $this->juros(),
            'numero'          => 1,
            'numeroDocumento' => 1,
            'pagador'         => self::$pagador,
            'beneficiario'    => self::$beneficiario,
            'conta'           => '123456789',
            'operacao'        => '1234567',
            'aceite'          => 'S',
            'especieDoc'      => 'DM',
        ]);

        $remessa = new Remessa\Inter([
            'idRemessa'    => 1,
            'agencia'      => '0001',
            'conta'        => '123456789',
            'carteira'     => 112,
            'beneficiario' => self::$beneficiario,
        ]);
        $remessa->addBoleto($boleto);

        $file = implode(DIRECTORY_SEPARATOR, [
            __DIR__,
            'files',
            'cnab400',
            'inter.txt',
        ]);

        $file2 = $remessa->save($file);

        $this->assertFileExists($file2);
    }

    /**
     * C6 Bank, Carteira 20 (Cobrança Simples Emissão Cliente) — o cliente atribui o nosso número.
     * Posições conferidas contra o manual (LAYOUT CNAB 400 - C6 BANK v2.7, jul/2025, manuais/C6/).
     */
    public function testRemessaC6Cnab400CarteiraCliente()
    {
        $boleto = new Boleto\C6([
            'logo'                   => realpath(__DIR__ . '/../logos/') . DIRECTORY_SEPARATOR . '336.png',
            'dataVencimento'         => $this->vencimento(),
            'valor'                  => $this->valor(),
            'multa'                  => $this->multa(),
            'juros'                  => $this->juros(),
            'numero'                 => 1,
            'numeroDocumento'        => 1,
            'numeroControle'         => 'CTRL0000000001',
            'pagador'                => self::$pagador,
            'beneficiario'           => self::$beneficiario,
            'carteira'               => '20',
            'codigoCliente'          => 123456,
            'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
            'aceite'                 => 'S',
            'especieDoc'             => 'DM',
        ]);

        $remessa = new Remessa\C6([
            'idremessa'     => 1,
            'agencia'       => '0001',
            'conta'         => 100000002,
            'carteira'      => '20',
            'codigoCliente' => 123456,
            'beneficiario'  => self::$beneficiario,
        ]);
        $remessa->addBoleto($boleto);

        $content = $remessa->gerar();
        $lines = explode("\r\n", trim($content));

        // Header (manual, seção 5.1)
        $header = $lines[0];
        $this->assertEquals('0', substr($header, 0, 1));           // campo 1
        $this->assertEquals('1REMESSA', substr($header, 1, 8));    // campos 2-3
        $this->assertEquals('01COBRANCA', substr($header, 9, 10)); // campos 4-5
        $this->assertEquals('336', substr($header, 76, 3));        // campo 10 - Código do Banco

        // Conta Cobrança (campo 14, posições 109-120)
        $this->assertEquals('000100000002', substr($header, 108, 12));

        // Detalhe (manual, seção 5.2)
        $detalhe = $lines[1];
        $this->assertEquals('1', substr($detalhe, 0, 1));               // campo 18
        $this->assertEquals('336', substr($detalhe, 82, 3));            // campo 27 - Código do Banco
        $this->assertEquals('20', substr($detalhe, 106, 2));            // campo 29 - Carteira
        $this->assertEquals('01', substr($detalhe, 108, 2));            // campo 30 - Ocorrência (Remessa)
        // Nosso número (campo 24, 63-73) no formato 0NNNNNNNNNN + dígito (campo 25, posição 74)
        $nossoNumero = substr($detalhe, 62, 12);
        $this->assertEquals('0', substr($nossoNumero, 0, 1));
        $this->assertNotEquals('000000000000', $nossoNumero, 'Carteira 20 deve gerar o nosso número, não deixar em branco/zerado');

        $file = implode(DIRECTORY_SEPARATOR, [__DIR__, 'files', 'cnab400', 'c6.txt']);
        $file2 = $remessa->save($file);
        $this->assertFileExists($file2);
    }

    /**
     * C6 Bank, Carteira 10 (Cobrança Simples Emissão Banco) — o banco atribui o nosso número;
     * o manual (campo 24) diz para deixar o campo em branco na remessa de registro.
     */
    public function testRemessaC6Cnab400CarteiraBanco()
    {
        $boleto = new Boleto\C6([
            'logo'                   => realpath(__DIR__ . '/../logos/') . DIRECTORY_SEPARATOR . '336.png',
            'dataVencimento'         => $this->vencimento(),
            'valor'                  => $this->valor(),
            'multa'                  => $this->multa(),
            'juros'                  => $this->juros(),
            'numero'                 => 1,
            'numeroDocumento'        => 1,
            'numeroControle'         => 'CTRL0000000002',
            'pagador'                => self::$pagador,
            'beneficiario'           => self::$beneficiario,
            'carteira'               => '10',
            'codigoCliente'          => 123456,
            'aceite'                 => 'S',
            'especieDoc'             => 'DM',
        ]);

        $this->assertNull($boleto->getNossoNumeroMaxLength(), 'Carteira 10: banco atribui, sem limite de dígitos a validar aqui');

        $remessa = new Remessa\C6([
            'idremessa'     => 1,
            'agencia'       => '0001',
            'conta'         => 100000002,
            'carteira'      => '10',
            'codigoCliente' => 123456,
            'beneficiario'  => self::$beneficiario,
        ]);
        $remessa->addBoleto($boleto);

        $content = $remessa->gerar();
        $lines = explode("\r\n", trim($content));
        $detalhe = $lines[1];

        // Nosso número (campo 24+25, posições 63-74) deve ficar em branco/zerado — o C6 atribui.
        $this->assertEquals('000000000000', substr($detalhe, 62, 12));
        $this->assertEquals('10', substr($detalhe, 106, 2)); // campo 29 - Carteira
    }

    /**
     * C6 Bank — valores fixos e determinísticos pra travar byte a byte os campos críticos do
     * detalhe de remessa: nosso número, vencimento, número de controle, número documento,
     * juros, multa e desconto. Posições conferidas contra o manual (v2.7, jul/2025).
     */
    public function testRemessaC6Cnab400CamposCriticos()
    {
        $vencimento = \Carbon\Carbon::create(2026, 12, 25);
        $dataDesconto = \Carbon\Carbon::create(2026, 12, 20);

        $boleto = new Boleto\C6([
            'logo'            => realpath(__DIR__ . '/../logos/') . DIRECTORY_SEPARATOR . '336.png',
            'dataVencimento'  => $vencimento,
            'valor'           => 1500.00,
            'multa'           => 2.5,
            'juros'           => 1.0,
            'jurosApos'       => 1,
            'desconto'        => 30.00,
            'dataDesconto'    => $dataDesconto,
            'numero'          => 5,
            'numeroDocumento' => 'DOC000005',
            'numeroControle'  => 'CTRLTESTE00005',
            'pagador'         => self::$pagador,
            'beneficiario'    => self::$beneficiario,
            'carteira'        => '20',
            'codigoCliente'   => 123456,
            'aceite'          => 'S',
            'especieDoc'      => 'DM',
        ]);

        $remessa = new Remessa\C6([
            'idremessa'     => 5,
            'agencia'       => '0001',
            'conta'         => 100000002,
            'carteira'      => '20',
            'codigoCliente' => 123456,
            'beneficiario'  => self::$beneficiario,
        ]);
        $remessa->addBoleto($boleto);

        $content = $remessa->gerar();
        $lines = explode("\r\n", trim($content));
        $detalhe = $lines[1];

        // Número de controle (campo 23, "Uso Exclusivo", posições 38-62, 25 X)
        $this->assertEquals(Util::formatCnab('X', 'CTRLTESTE00005', 25), substr($detalhe, 37, 25));

        // Nosso número (campo 24+25, posições 63-74) — carteira 20, cliente atribui, não pode ficar zerado
        $this->assertNotEquals('000000000000', substr($detalhe, 62, 12));

        // Seu número / número documento (campo 31, posições 111-120, 10 X)
        $this->assertEquals(Util::formatCnab('X', 'DOC000005', 10), substr($detalhe, 110, 10));

        // Data de vencimento (campo 32, posições 121-126, DDMMAA)
        $this->assertEquals('251226', substr($detalhe, 120, 6));

        // Valor do título (campo 33, posições 127-139, 13 9(11)v99)
        $this->assertEquals(Util::formatCnab('9', 1500.00, 13, 2), substr($detalhe, 126, 13));

        // Juros ao dia (campo 40, posições 161-173) — mora diária calculada a partir de valor+juros%
        $this->assertEquals(Util::formatCnab('9', $boleto->getMoraDia(), 13, 2), substr($detalhe, 160, 13));

        // Data para desconto (campo 41, posições 174-179, DDMMAA)
        $this->assertEquals('201226', substr($detalhe, 173, 6));

        // Valor do desconto (campo 42, posições 180-192)
        $this->assertEquals(Util::formatCnab('9', 30.00, 13, 2), substr($detalhe, 179, 13));

        // Data da multa (campo 43, posições 193-198) — igual ao vencimento quando há multa
        $this->assertEquals('251226', substr($detalhe, 192, 6));

        // Indicador de multa (campo 55, posição 382) — "2" = percentual, já que há multa > 0
        $this->assertEquals('2', substr($detalhe, 381, 1));

        // Percentual de multa (campo 56, posições 383-384)
        $this->assertEquals('02', substr($detalhe, 382, 2));

        // Data dos juros (campo 58, posições 386-391) — vencimento + jurosApos (1 dia)
        $this->assertEquals('261226', substr($detalhe, 385, 6));

        // Campos 59-60 (392-394) são "Uso do Banco" — devem ficar em branco, nunca dias de protesto
        $this->assertEquals('   ', substr($detalhe, 391, 3));
    }

    public function testRemessaBvCnab400()
    {
        $boleto = new Boleto\Bv([
            'logo'                   => realpath(__DIR__ . '/../../logos/') . DIRECTORY_SEPARATOR . '655.png',
            'dataVencimento'         => $this->vencimento(),
            'valor'                  => $this->valor(),
            'multa'                  => $this->multa(),
            'juros'                  => $this->juros(),
            'numero'                 => 1,
            'numeroDocumento'        => 1,
            'pagador'                => self::$pagador,
            'beneficiario'           => self::$beneficiario,
            'carteira'               => 500,
            'convenio'               => 1234567890,
            'conta'                  => 12345678,
            'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
            'instrucoes'             => ['instrucao 1', 'instrucao 2', 'instrucao 3'],
            'aceite'                 => $this->aceite(),
            'especieDoc'             => 'DM',
        ]);

        $remessa = new Remessa\Bv([
            'agencia'      => '0001',
            'carteira'     => 500,
            'conta'        => 12345678,
            'convenio'     => 1234567890,
            'beneficiario' => self::$beneficiario,
        ]);
        $remessa->addBoleto($boleto);

        $file = implode(DIRECTORY_SEPARATOR, [
            __DIR__,
            'files',
            'cnab400',
            'bv.txt',
        ]);

        $file2 = $remessa->save($file);

        $this->assertFileExists($file2);
    }
}
