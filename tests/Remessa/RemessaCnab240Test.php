<?php

namespace Eduardokum\LaravelBoleto\Tests\Remessa;

use Eduardokum\LaravelBoleto\Pessoa;
use Eduardokum\LaravelBoleto\Tests\TestCase;
use Eduardokum\LaravelBoleto\Boleto\Banco as Boleto;
use Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab240\Banco as Remessa;

class RemessaCnab240Test extends TestCase
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
            'cnab240',
        ];
        $files = glob(implode(DIRECTORY_SEPARATOR, $aFiles) . '/*'); // get all file names
        foreach ($files as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }
    }

    public function testRemessaSantanderCnab240()
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
            'idremessa' => 1,
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
            'santander.txt'
        ]);

        $file2 = $remessa->save($file);

        $this->assertFileExists($file);
        $this->assertEquals($file, $file2);

        $conteudo = file_get_contents($file);
        $temY03 = (bool) preg_match('/^.{13}Y.{3}03/m', $conteudo);
        $this->assertFalse($temY03);
    }

    public function testRemessaSantanderCnab240Pix()
    {
        $pixChave = '39a1178e-db6b-4407-bc7b-b674390acf5f';
        $txid = 'TXID123456789012345678901234';

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
            'id'                     => $txid,
            'pix_chave'              => $pixChave,
            'pix_chave_tipo'         => Boleto\Santander::TIPO_CHAVEPIX_ALEATORIA,
        ]);

        $remessa = new Remessa\Santander([
            'idremessa'     => 1,
            'agencia'       => 1111,
            'carteira'      => 101,
            'conta'         => 99999999,
            'codigoCliente' => 12345678,
            'beneficiario'  => self::$beneficiario,
        ]);
        $remessa->addBoleto($boleto);

        $conteudo = $remessa->gerar();
        $linhas = preg_split("/\r\n|\n|\r/", trim($conteudo));
        $linhaY03 = null;
        foreach ($linhas as $linha) {
            if (strlen($linha) >= 19 && substr($linha, 13, 1) === 'Y' && substr($linha, 17, 2) === '03') {
                $linhaY03 = $linha;
                break;
            }
        }

        $this->assertNotNull($linhaY03);
        $this->assertEquals(240, strlen($linhaY03));
        $this->assertEquals('5', substr($linhaY03, 80, 1));
        $this->assertEquals(strtolower($pixChave), trim(substr($linhaY03, 81, 77)));
        $this->assertEquals($txid, trim(substr($linhaY03, 158, 35)));
    }

    public function testRemessaItauCnab240()
    {
        $boleto = new Boleto\Itau([
            'logo' => realpath(__DIR__ . '/../logos/') . DIRECTORY_SEPARATOR . '033.png',
            'dataVencimento' => $this->vencimento(),
            'valor' => $this->valor(),
            'multa' => $this->multa(),
            'juros' => $this->juros(),
            'numero' => 1,
            'numeroDocumento' => 1,
            'pagador' => self::$pagador,
            'beneficiario' => self::$beneficiario,
            'diasBaixaAutomatica' => 0,
            'carteira' => '109',
            'agencia' => '9999',
            'conta' => '99999',
            'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
            'instrucoes' => ['instrucao 1', 'instrucao 2', 'instrucao 3'],
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
            'itau.txt',
        ]);

        $file2 = $remessa->save($file);

        $this->assertFileExists($file);
        $this->assertEquals($file, $file2);
    }
}
