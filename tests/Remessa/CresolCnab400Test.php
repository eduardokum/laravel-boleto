<?php

namespace Eduardokum\LaravelBoleto\Tests\Remessa;

use Carbon\Carbon;
use Eduardokum\LaravelBoleto\Pessoa;
use Eduardokum\LaravelBoleto\Tests\TestCase;
use Eduardokum\LaravelBoleto\Boleto\Banco\Cresol as BoletoCresol;
use Eduardokum\LaravelBoleto\Exception\ValidationException;
use Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400\Banco\Cresol;

class CresolCnab400Test extends TestCase
{
    protected static $pagador;

    protected static $beneficiario;

    public static function setUpBeforeClass(): void
    {
        self::$beneficiario = new Pessoa([
            'nome'      => 'BENEFICIARIO PF',
            'endereco'  => 'Rua um, 123',
            'cep'       => '85000-000',
            'uf'        => 'PR',
            'cidade'    => 'CIDADE',
            'documento' => '077.651.119-06',
        ]);

        self::$pagador = new Pessoa([
            'nome'      => 'PAGADOR TESTE',
            'endereco'  => 'Rua dois, 456',
            'bairro'    => 'CENTRO',
            'cep'       => '85010-000',
            'uf'        => 'PR',
            'cidade'    => 'CIDADE',
            'documento' => '123.456.789-09',
        ]);
    }

    private function boleto(array $params = [])
    {
        return new BoletoCresol(array_merge([
            'agencia'         => 1069,
            'conta'           => 28245,
            'contaDv'         => 6,
            'carteira'        => '09',
            'numero'          => 1,
            'numeroDocumento' => 1,
            'dataVencimento'  => new Carbon('2026-03-10'),
            'dataDocumento'   => new Carbon('2026-02-10'),
            'valor'           => 150.00,
            'multa'           => 2,
            'juros'           => 0.33,
            'especieDoc'      => 'DM',
            'beneficiario'    => self::$beneficiario,
            'pagador'         => self::$pagador,
        ], $params));
    }

    private function remessa(array $params = [])
    {
        return new Cresol(array_merge([
            'agencia'      => 1069,
            'conta'        => 28245,
            'contaDv'      => 6,
            'carteira'     => '09',
            'idremessa'    => 1,
            'beneficiario' => self::$beneficiario,
        ], $params));
    }

    /**
     * Estrutura minima: header, detalhe e trailer, todos com 400 posições
     */
    public function testEstruturaDoArquivo()
    {
        $remessa = $this->remessa();
        $remessa->addBoleto($this->boleto());

        $linhas = explode("\r\n", trim($remessa->gerar()));

        $this->assertCount(3, $linhas);
        foreach ($linhas as $linha) {
            $this->assertEquals(400, strlen($linha));
        }
        $this->assertEquals('0', substr($linhas[0], 0, 1));
        $this->assertEquals('1', substr($linhas[1], 0, 1));
        $this->assertEquals('9', substr($linhas[2], 0, 1));
    }

    /**
     * Header conforme layout do manual de remessa CNAB 400
     */
    public function testHeader()
    {
        $remessa = $this->remessa();
        $remessa->addBoleto($this->boleto());
        $header = explode("\r\n", trim($remessa->gerar()))[0];

        $this->assertEquals('0', substr($header, 0, 1));
        $this->assertEquals('1', substr($header, 1, 1));
        $this->assertEquals('REMESSA', substr($header, 2, 7));
        $this->assertEquals('01', substr($header, 9, 2));
        $this->assertEquals('COBRANCA       ', substr($header, 11, 15));
        $this->assertEquals('00000090106900282456', substr($header, 26, 20));
        $this->assertEquals('133', substr($header, 76, 3));
        $this->assertEquals('CRESOL         ', substr($header, 79, 15));
        $this->assertEquals('000001', substr($header, 394, 6));
    }

    /**
     * Identificação da empresa (21-37) conforme o manual: zero, carteira, cooperativa,
     * conta e dígito
     */
    public function testIdentificacaoDaEmpresaNoDetalhe()
    {
        $remessa = $this->remessa();
        $remessa->addBoleto($this->boleto());
        $detalhe = explode("\r\n", trim($remessa->gerar()))[1];

        $this->assertEquals('0', substr($detalhe, 20, 1));
        $this->assertEquals('009', substr($detalhe, 21, 3));
        $this->assertEquals('01069', substr($detalhe, 24, 5));
        $this->assertEquals('0028245', substr($detalhe, 29, 7));
        $this->assertEquals('6', substr($detalhe, 36, 1));
    }

    /**
     * Detalhe conforme layout, com atenção à espécie (148-149) que precisa sair como
     * duplicata mercantil = 02 e não como o default 99
     */
    public function testDetalhe()
    {
        $remessa = $this->remessa();
        $remessa->addBoleto($this->boleto());
        $detalhe = explode("\r\n", trim($remessa->gerar()))[1];

        $this->assertEquals('2', substr($detalhe, 65, 1));         // 66 multa percentual
        $this->assertEquals('0200', substr($detalhe, 66, 4));      // 67-70 percentual multa
        $this->assertEquals('00000000001', substr($detalhe, 70, 11)); // 71-81 nosso numero
        $this->assertEquals('1', substr($detalhe, 81, 1));         // 82 dv
        $this->assertEquals('2', substr($detalhe, 92, 1));         // 93 cliente emite
        $this->assertEquals('01', substr($detalhe, 108, 2));       // 109-110 ocorrencia remessa
        $this->assertEquals('100326', substr($detalhe, 120, 6));   // 121-126 vencimento
        $this->assertEquals('0000000015000', substr($detalhe, 126, 13)); // 127-139 valor
        $this->assertEquals('02', substr($detalhe, 147, 2));       // 148-149 especie
        $this->assertEquals('100226', substr($detalhe, 150, 6));   // 151-156 emissao
        $this->assertEquals('01', substr($detalhe, 218, 2));       // 219-220 tipo insc pagador
        $this->assertEquals('00012345678909', substr($detalhe, 220, 14)); // 221-234 cpf
        $this->assertEquals('85010000', substr($detalhe, 326, 8)); // 327-334 cep
        $this->assertEquals('000002', substr($detalhe, 394, 6));
    }

    /**
     * O dígito "P" precisa sobreviver, pois a posição 82 é alfanumérica no manual
     */
    public function testNossoNumeroComDigitoP()
    {
        $remessa = $this->remessa();
        $remessa->addBoleto($this->boleto(['numero' => 2]));
        $detalhe = explode("\r\n", trim($remessa->gerar()))[1];

        $this->assertEquals('00000000002', substr($detalhe, 70, 11));
        $this->assertEquals('P', substr($detalhe, 81, 1));
    }

    /**
     * Espécies diferentes usam a tabela da Cresol
     */
    public function testEspecieRecibo()
    {
        $remessa = $this->remessa();
        $remessa->addBoleto($this->boleto(['especieDoc' => 'RC']));
        $detalhe = explode("\r\n", trim($remessa->gerar()))[1];

        $this->assertEquals('17', substr($detalhe, 147, 2));
    }

    /**
     * A posição 161-173 é "Valor a ser cobrado por Dia de Atraso", ou seja, reais por dia
     * derivados da taxa mensal. 6% ao mês sobre R$ 150,00 = R$ 0,30 por dia.
     */
    public function testJurosSaiEmReaisPorDia()
    {
        $remessa = $this->remessa();
        $remessa->addBoleto($this->boleto(['juros' => 6]));
        $detalhe = explode("\r\n", trim($remessa->gerar()))[1];

        $this->assertEquals('0000000000030', substr($detalhe, 160, 13));
    }

    /**
     * O layout não tem campo de tipo de desconto, então o percentual é convertido em reais
     */
    public function testDescontoPercentualViraValorAbsoluto()
    {
        $remessa = $this->remessa();
        $remessa->addBoleto($this->boleto([
            'descontoPercentual' => 10,
            'descontoCodigo' => \Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto::TIPO_DESCONTO_PERCENTUAL,
            'dataDesconto'   => new Carbon('2026-03-05'),
        ]));
        $detalhe = explode("\r\n", trim($remessa->gerar()))[1];

        $this->assertEquals('050326', substr($detalhe, 173, 6));         // 174-179 data
        $this->assertEquals('0000000001500', substr($detalhe, 179, 13)); // 180-192: 10% de 150
    }

    /**
     * Sem desconto, data zerada e valor zerado
     */
    public function testSemDescontoZeraOBloco()
    {
        $remessa = $this->remessa();
        $remessa->addBoleto($this->boleto());
        $detalhe = explode("\r\n", trim($remessa->gerar()))[1];

        $this->assertEquals('000000', substr($detalhe, 173, 6));
        $this->assertEquals('0000000000000', substr($detalhe, 179, 13));
    }

    /**
     * O campo de multa tem 4 posições com 2 decimais: sem validação, 150% seria gravado
     * como 15,00% silenciosamente
     */
    public function testMultaAcimaDoLimiteLancaExcecao()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessageMatches('/excede o limite de 99,99/u');

        $remessa = $this->remessa();
        $remessa->addBoleto($this->boleto(['multa' => 150]));
    }

    /**
     * O limite de 99,99% em si continua aceito
     */
    public function testMultaNoLimiteEhAceita()
    {
        $remessa = $this->remessa();
        $remessa->addBoleto($this->boleto(['multa' => 99.99]));
        $detalhe = explode("\r\n", trim($remessa->gerar()))[1];

        $this->assertEquals('2', substr($detalhe, 65, 1));
        $this->assertEquals('9999', substr($detalhe, 66, 4));
    }

    /**
     * O CNAB 400 não suporta protesto: a instrução não pode ser silenciada
     */
    public function testProtestoNaoSuportadoLancaExcecao()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessageMatches('/n[ãa]o suporta instru[çc][ãa]o de protesto/iu');

        $remessa = $this->remessa();
        $remessa->addBoleto($this->boleto(['diasProtesto' => 5]));
    }

    /**
     * Um título fora da faixa liberada faz a Cresol descartar o arquivo inteiro
     */
    public function testNossoNumeroForaDaFaixaLancaExcecao()
    {
        $remessa = $this->remessa();
        $remessa->setFaixaNossoNumero(1, 100);

        $remessa->addBoleto($this->boleto(['numero' => 100]));

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessageMatches('/fora da faixa/i');
        $remessa->addBoleto($this->boleto(['numero' => 101]));
    }

    /**
     * Sem faixa configurada o comportamento anterior é preservado
     */
    public function testSemFaixaConfiguradaNaoValida()
    {
        $remessa = $this->remessa();
        $remessa->addBoleto($this->boleto(['numero' => 999999]));

        $this->assertCount(3, explode("\r\n", trim($remessa->gerar())));
    }

    /**
     * Trailer com o sequencial correto para varios boletos
     */
    public function testTrailerComVariosBoletos()
    {
        $remessa = $this->remessa();
        $remessa->addBoleto($this->boleto(['numero' => 10, 'numeroDocumento' => 10]));
        $remessa->addBoleto($this->boleto(['numero' => 11, 'numeroDocumento' => 11]));
        $remessa->addBoleto($this->boleto(['numero' => 12, 'numeroDocumento' => 12]));

        $linhas = explode("\r\n", trim($remessa->gerar()));

        $this->assertCount(5, $linhas);
        $this->assertEquals('000005', substr($linhas[4], 394, 6));
    }
}
