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

    /**
     * Banrisul CNAB 240 - cobrança. Valida posições conforme o manual oficial
     * "Cobrança Banrisul - Leiaute CNAB 240 Posições" (manuais/BANRISUL/240_posicoes.pdf).
     */
    public function testRemessaBanrisulCnab240()
    {
        $beneficiario = new Pessoa([
            'nome'      => 'EMPRESA BENEFICIARIA EXEMPLO LTDA',
            'endereco'  => 'AV DESEMBARGADOR, 123',
            'cep'       => '90230-090',
            'uf'        => 'RS',
            'cidade'    => 'PORTO ALEGRE',
            'documento' => '12.345.678/0001-90',
        ]);

        $boleto = new Boleto\Banrisul([
            'dataVencimento'  => new \Carbon\Carbon('2026-07-15'),
            'dataDocumento'   => new \Carbon\Carbon('2026-06-11'),
            'valor'           => 1234.56,
            'multa'           => 2.00,
            'juros'           => 1.00,
            'numero'          => 25,
            'numeroDocumento' => 'DOC12345',
            'numeroControle'  => 'CTRL-001',
            'pagador'         => self::$pagador,
            'beneficiario'    => $beneficiario,
            'agencia'         => 100,
            'conta'           => 6677,
            'carteira'        => '1',
            'codigoCliente'   => '0001234567890',
            'diasProtesto'    => 5,
            'aceite'          => 'N',
            'especieDoc'      => 'DM',
            'instrucoes'      => ['NAO RECEBER APOS 30 DIAS', 'JUROS DE 1 PORCENTO AO MES'],
        ]);

        $remessa = new Remessa\Banrisul([
            'idremessa'     => 1,
            'agencia'       => 100,
            'conta'         => 6677,
            'contaDv'       => 5,
            'carteira'      => '1',
            'codigoCliente' => '0001234567890',
            'beneficiario'  => $beneficiario,
        ]);
        $remessa->setDataRemessa(new \Carbon\Carbon('2026-06-11'));
        $remessa->addBoleto($boleto);

        $content = $remessa->gerar();
        $lines = preg_split('/\r\n/', rtrim($content, "\r\n"));

        $field = function ($line, $i, $f) {
            return substr($line, $i - 1, $f - $i + 1);
        };

        // Estrutura: header arquivo, header lote, P, Q, R, trailer lote, trailer arquivo.
        $this->assertCount(7, $lines);
        foreach ($lines as $n => $line) {
            $this->assertEquals(240, strlen($line), "Linha " . ($n + 1) . " deve ter 240 bytes");
        }

        [$header, $headerLote, $p, $q, $r, $trailerLote, $trailerArquivo] = $lines;

        // --- Header de Arquivo (3.1) ---
        $this->assertEquals('041', $field($header, 1, 3));
        $this->assertEquals('0000', $field($header, 4, 7));
        $this->assertEquals('0', $field($header, 8, 8));
        $this->assertEquals('2', $field($header, 18, 18)); // CNPJ
        $this->assertEquals('00012345678900000000', $field($header, 33, 52)); // convênio 13+7
        $this->assertEquals('00100', $field($header, 53, 57)); // agência (0AAAA) - considerada no header de arquivo
        $this->assertEquals(' ', $field($header, 58, 58)); // DV agência - branco
        $this->assertEquals('000000006677', $field($header, 59, 70)); // conta
        $this->assertEquals('5', $field($header, 71, 71)); // DV conta - numérico
        $this->assertEquals(' ', $field($header, 72, 72)); // DV ag/conta - branco
        $this->assertEquals('1', $field($header, 143, 143)); // remessa
        $this->assertEquals('11062026', $field($header, 144, 151));
        $this->assertEquals('040', $field($header, 164, 166)); // layout arquivo
        $this->assertEquals('00000', $field($header, 167, 171));
        $this->assertEquals('BE', $field($header, 180, 181));

        // --- Header de Lote (3.2) ---
        $this->assertEquals('1', $field($headerLote, 8, 8));
        $this->assertEquals('R', $field($headerLote, 9, 9));
        $this->assertEquals('01', $field($headerLote, 10, 11));
        $this->assertEquals('00', $field($headerLote, 12, 13));
        $this->assertEquals('020', $field($headerLote, 14, 16)); // layout lote
        $this->assertEquals('00012345678900000000', $field($headerLote, 34, 53));

        // --- Segmento P (3.3) ---
        $this->assertEquals('P', $field($p, 14, 14));
        $this->assertEquals('01', $field($p, 16, 17)); // movimento: entrada
        $this->assertEquals('0000002577', $field($p, 38, 47)); // nosso número (10 primeiras posições)
        $this->assertEquals('0000000000', $field($p, 48, 57)); // restante numérico = zeros (não brancos)
        $this->assertEquals('1', $field($p, 58, 58)); // carteira
        $this->assertEquals('1', $field($p, 59, 59)); // com cadastramento
        $this->assertEquals('2', $field($p, 61, 61)); // cliente emite
        $this->assertEquals('DOC12345       ', $field($p, 63, 77)); // nº documento (15, alfa)
        $this->assertEquals('15072026', $field($p, 78, 85)); // vencimento
        $this->assertEquals('000000000123456', $field($p, 86, 100)); // valor (15,2)
        $this->assertEquals('02', $field($p, 107, 108)); // espécie DM -> 02
        $this->assertEquals('N', $field($p, 109, 109)); // aceite
        $this->assertEquals('2', $field($p, 118, 118)); // juros: taxa mensal
        $this->assertEquals('15072026', $field($p, 119, 126)); // data juros = vencimento (literal ao manual)
        $this->assertEquals('000000000000100', $field($p, 127, 141)); // 1,00
        $this->assertEquals('0', $field($p, 142, 142)); // sem desconto
        $this->assertEquals('105', $field($p, 221, 223)); // protestar, 05 dias
        $this->assertEquals('0000', $field($p, 224, 227)); // sem baixa
        $this->assertEquals('09', $field($p, 228, 229)); // moeda Real
        $this->assertEquals(' ', $field($p, 240, 240));

        // --- Segmento Q (3.4) ---
        $this->assertEquals('Q', $field($q, 14, 14));
        $this->assertEquals('1', $field($q, 18, 18)); // CPF
        $this->assertEquals('99999', $field($q, 129, 133)); // CEP (pagador 99999-999)
        $this->assertEquals('999', $field($q, 134, 136)); // sufixo CEP
        $this->assertEquals('UF', $field($q, 152, 153));
        $this->assertEquals('0', $field($q, 154, 154)); // sem sacador

        // --- Segmento R (3.5) ---
        $this->assertEquals('R', $field($r, 14, 14));
        $this->assertEquals('3', $field($r, 66, 66)); // multa percentual
        $this->assertEquals('16072026', $field($r, 67, 74)); // venc + 1 dia
        $this->assertEquals('000000000000020', $field($r, 75, 89)); // 2,0% (1 casa decimal)
        $this->assertEquals('NAO RECEBER APOS 30 DIAS                ', $field($r, 100, 139));
        $this->assertEquals('JUROS DE 1 PORCENTO AO MES              ', $field($r, 140, 179));
        $this->assertEquals(str_repeat(' ', 33), $field($r, 208, 240)); // cauda BRANCOS

        // --- Trailler de Lote (3.10) ---
        $this->assertEquals('5', $field($trailerLote, 8, 8));
        $this->assertEquals('000005', $field($trailerLote, 18, 23)); // header lote + 3 detalhes + trailer
        $this->assertEquals('000001', $field($trailerLote, 24, 29)); // 1 título simples
        $this->assertEquals('00000000000123456', $field($trailerLote, 30, 46)); // valor simples (17,2)

        // --- Trailler de Arquivo (3.11) ---
        $this->assertEquals('9', $field($trailerArquivo, 8, 8));
        $this->assertEquals('9999', $field($trailerArquivo, 4, 7));
        $this->assertEquals('000001', $field($trailerArquivo, 18, 23)); // 1 lote
        $this->assertEquals('000007', $field($trailerArquivo, 24, 29)); // 0+1+3*3... = 7 registros
    }
}
