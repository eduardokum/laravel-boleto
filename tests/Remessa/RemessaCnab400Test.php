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
            'beneficiario' => self::$beneficiario,
        ]);
        $remessa->gerar();
    }

    public function testRemessaCarteiraIndisponivel()
    {
        $this->expectException(Exception::class);
        $remessa = new Remessa\Banrisul([
            'agencia' => 1111,
            'conta' => 22222,
            'carteira' => '123',
            'codigoCliente' => 11112222222,
            'beneficiario' => self::$beneficiario,
        ]);
        $remessa->gerar();
    }

    public function testRemessaAddboletosCnab400()
    {
        $boleto = new Boleto\Banrisul([
            'logo' => realpath(__DIR__ . '/../logos/') . DIRECTORY_SEPARATOR . '041.png',
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
            'aceite' => 'S',
            'especieDoc' => 'DM',
        ]);

        $boleto2 = $boleto;
        $boleto2->setNumeroDocumento(2);

        $remessa = new Remessa\Banrisul([
            'agencia' => 1111,
            'conta' => 22222,
            'carteira' => 1,
            'codigoCliente' => 11112222222,
            'beneficiario' => self::$beneficiario,
        ]);
        $remessa->addBoletos([$boleto, $boleto2]);
        $this->assertEquals(4, count(Util::file2array($remessa->gerar())));
    }

    public function testRemessaBanrisulCnab400()
    {
        $boleto = new Boleto\Banrisul([
            'logo' => realpath(__DIR__ . '/../logos/') . DIRECTORY_SEPARATOR . '041.png',
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
            'aceite' => 'S',
            'especieDoc' => 'DM',
        ]);
        $remessa = new Remessa\Banrisul([
            'agencia' => 1111,
            'conta' => 22222,
            'carteira' => 1,
            'codigoCliente' => 11112222222,
            'beneficiario' => self::$beneficiario,
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
            'logo' => realpath(__DIR__ . '/../logos/') . DIRECTORY_SEPARATOR . '001.png',
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
            'aceite' => 'S',
            'especieDoc' => 'DM',
        ]);

        $remessa = new Remessa\Bb([
            'agencia' => 1111,
            'carteira' => 11,
            'conta' => 999999999,
            'convenio' => 1234567,
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
            'logo' => realpath(__DIR__ . '/../logos/') . DIRECTORY_SEPARATOR . '001.png',
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
            'aceite' => 'S',
            'especieDoc' => 'DM',
            'chaveNfe' => '12345678901234567890123456789012345678901234',
        ]);

        $remessa = new Remessa\Bb([
            'agencia' => 1111,
            'carteira' => 11,
            'conta' => 999999999,
            'convenio' => 1234567,
            'beneficiario' => self::$beneficiario,
        ]);
        $remessa->addBoleto($boleto);

        $cnab = explode($remessa->getFimLinha(), $remessa->gerar());
        $this->assertEquals(444, strlen($cnab[1]));
    }

    public function testRemessaBradescoCnab400()
    {
        $boleto = new Boleto\Bradesco([
            'logo' => realpath(__DIR__ . '/../logos/') . DIRECTORY_SEPARATOR . '237.png',
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
            'aceite' => 'S',
            'especieDoc' => 'DM',
        ]);

        $remessa = new Remessa\Bradesco([
            'idRemessa' => 1,
            'agencia' => 1111,
            'carteira' => '09',
            'conta' => 99999999,
            'contaDv' => 9,
            'codigoCliente' => 12345678901234567890,
            'beneficiario' => self::$beneficiario,
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
            'logo' => realpath(__DIR__ . '/../logos/') . DIRECTORY_SEPARATOR . '237.png',
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
            'aceite' => 'S',
            'especieDoc' => 'DM',
            'chaveNfe' => '12345678901234567890123456789012345678901234',
        ]);

        $remessa = new Remessa\Bradesco([
            'idRemessa' => 1,
            'agencia' => 1111,
            'carteira' => '09',
            'conta' => 99999999,
            'contaDv' => 9,
            'codigoCliente' => 12345678901234567890,
            'beneficiario' => self::$beneficiario,
        ]);
        $remessa->addBoleto($boleto);

        $cnab = explode($remessa->getFimLinha(), $remessa->gerar());
        $this->assertEquals(444, strlen($cnab[1]));
    }

    public function testRemessaCaixaCnab400()
    {
        $boleto = new Boleto\Caixa([
            'logo' => realpath(__DIR__ . '/../logos/') . DIRECTORY_SEPARATOR . '104.png',
            'dataVencimento' => $this->vencimento(),
            'valor' => $this->valor(),
            'multa' => $this->multa(),
            'juros' => $this->juros(),
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
            'aceite' => 'S',
            'especieDoc' => 'DM',
        ]);

        $remessa = new Remessa\Caixa([
            'agencia' => 1111,
            'conta' => 123456,
            'idremessa' => 1,
            'carteira' => 'RG',
            'codigoCliente' => 999999,
            'beneficiario' => self::$beneficiario,
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
            'logo' => realpath(__DIR__ . '/../logos/') . DIRECTORY_SEPARATOR . '399.png',
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
            'aceite' => 'S',
            'especieDoc' => 'DM',
        ]);

        $remessa = new Remessa\Hsbc([
            'agencia' => 1111,
            'carteira' => 'CSB',
            'conta' => 999999,
            'contaDv' => 9,
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
            'logo' => realpath(__DIR__ . '/../logos/') . DIRECTORY_SEPARATOR . '341.png',
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
            'aceite' => 'S',
            'especieDoc' => 'DM',
        ]);

        $remessa = new Remessa\Itau([
            'agencia' => 1111,
            'conta' => 99999,
            'contaDv' => 9,
            'carteira' => 112,
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

    public function testRemessaSantanderCnab400()
    {
        $boleto = new Boleto\Santander([
            'logo' => realpath(__DIR__ . '/../logos/') . DIRECTORY_SEPARATOR . '033.png',
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
            'conta' => 99999999,
            'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
            'instrucoes' => ['instrucao 1', 'instrucao 2', 'instrucao 3'],
            'aceite' => 'S',
            'especieDoc' => 'DM',
        ]);

        $remessa = new Remessa\Santander([
            'agencia' => 1111,
            'carteira' => 101,
            'conta' => 99999999,
            'codigoCliente' => 12345678,
            'beneficiario' => self::$beneficiario,
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
            'logo' => realpath(__DIR__ . '/../logos/') . DIRECTORY_SEPARATOR . '748.png',
            'dataVencimento' => $this->vencimento(),
            'valor' => $this->valor(),
            'multa' => $this->multa(),
            'juros' => $this->juros(),
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

        $remessa = new Remessa\Sicredi([
            'agencia' => 2606,
            'carteira' => '1',
            'conta' => 12510,
            'idremessa' => 1,
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
            'logo' => realpath(__DIR__ . '/../logos/') . DIRECTORY_SEPARATOR . '748.png',
            'dataVencimento' => $this->vencimento(),
            'valor' => $this->valor(),
            'multa' => $this->multa(),
            'juros' => $this->juros(),
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

        $remessa = new Remessa\Bancoob([
            'agencia' => 2606,
            'carteira' => '1',
            'conta' => 12510,
            'convenio' => 123123,
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
            'logo' => realpath(__DIR__ . '/../logos/') . DIRECTORY_SEPARATOR . '643.png',
            'dataVencimento' => $this->vencimento(),
            'valor' => $this->valor(),
            'multa' => $this->multa(),
            'juros' => $this->juros(),
            'numero' => 1,
            'numeroDocumento' => 1,
            'range' => 0,
            'pagador' => self::$pagador,
            'beneficiario' => self::$beneficiario,
            'carteira' => '112',
            'agencia' => '0001',
            'codigoCliente' => '12345',
            'conta' => '1234',
            'modalidadeCarteira' => '1',
            'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
            'instrucoes' => ['instrucao 1', 'instrucao 2', 'instrucao 3'],
            'aceite' => 'N',
            'especieDoc' => 'DM',
        ]);

        $remessa = new Remessa\Pine([
            'agencia' => '0001',
            'conta' => '1234',
            'contaDv' => 9,
            'carteira' => 112,
            'beneficiario' => self::$beneficiario,
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
            'logo' => realpath(__DIR__ . '/../logos/') . DIRECTORY_SEPARATOR . '224.png',
            'dataVencimento' => $this->vencimento(),
            'valor' => $this->valor(),
            'multa' => $this->multa(),
            'juros' => $this->juros(),
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

        $remessa = new Remessa\Fibra([
            'agencia' => '0001',
            'conta' => '1234567',
            'contaDv' => 9,
            'carteira' => 112,
            'beneficiario' => self::$beneficiario,
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
            'logo' => realpath(__DIR__ . '/../logos/') . DIRECTORY_SEPARATOR . '712.png',
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
            'aceite' => 'S',
            'especieDoc' => 'DM',
            'chaveNfe' => '12345678901234567890123456789012345678901234',
        ]);

        $remessa = new Remessa\Ourinvest([
            'idRemessa' => 1,
            'agencia' => 1111,
            'carteira' => '19',
            'conta' => 1234567,
            'contaDv' => 9,
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

        $remessa = new Remessa\Rendimento([
            'agencia' => '0001',
            'conta' => '1234',
            'contaDv' => 9,
            'carteira' => 121,
            'codigoCliente' => '5447390',
            'beneficiario' => self::$beneficiario,
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
            'logo' => realpath(__DIR__ . '/../logos/') . DIRECTORY_SEPARATOR . '077.png',
            'dataVencimento' => $this->vencimento(),
            'valor' => $this->valor(),
            'multa' => $this->multa(),
            'juros' => $this->juros(),
            'numero' => 1,
            'numeroDocumento' => 1,
            'pagador' => self::$pagador,
            'beneficiario' => self::$beneficiario,
            'conta' => '123456789',
            'operacao' => '1234567',
            'aceite' => 'S',
            'especieDoc' => 'DM',
        ]);

        $remessa = new Remessa\Inter([
            'idRemessa' => 1,
            'agencia' => '0001',
            'conta' => '123456789',
            'carteira' => 112,
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
}
