<?php

namespace Eduardokum\LaravelBoleto\Tests\Remessa;

use Carbon\Carbon;
use Eduardokum\LaravelBoleto\Pessoa;
use Eduardokum\LaravelBoleto\Tests\TestCase;
use Eduardokum\LaravelBoleto\Exception\ValidationException;
use Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab240\Banco\Cresol;
use Eduardokum\LaravelBoleto\Boleto\Banco\Cresol as BoletoCresol;

class CresolCnab240Test extends TestCase
{
    protected static $pagador;

    protected static $beneficiario;

    public static function setUpBeforeClass(): void
    {
        self::$beneficiario = new Pessoa([
            'nome'      => 'BENEFICIARIO LTDA',
            'endereco'  => 'Rua um, 123',
            'cep'       => '85000-000',
            'uf'        => 'PR',
            'cidade'    => 'CIDADE',
            'documento' => '11.643.817/0001-02',
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
            'aceite'          => 'N',
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
     * O trailer de arquivo termina em brancos, então só a quebra de linha final pode ser
     * removida — um trim() comum comeria o padding e mascararia o tamanho do registro.
     */
    private function linhas(Cresol $remessa)
    {
        return explode("\r\n", rtrim($remessa->gerar(), "\r\n"));
    }

    /**
     * Arquivo mínimo válido: header de arquivo, header de lote, P, Q, R, trailer de lote
     * e trailer de arquivo — as "ao menos 6 linhas" citadas no manual de integrações
     */
    public function testEstruturaDoArquivo()
    {
        $remessa = $this->remessa();
        $remessa->addBoleto($this->boleto());

        $linhas = $this->linhas($remessa);

        $this->assertCount(7, $linhas);
        foreach ($linhas as $linha) {
            $this->assertEquals(240, strlen($linha));
            $this->assertEquals('133', substr($linha, 0, 3));
        }

        $this->assertEquals('0', substr($linhas[0], 7, 1));
        $this->assertEquals('1', substr($linhas[1], 7, 1));
        $this->assertEquals('3', substr($linhas[2], 7, 1));
        $this->assertEquals('P', substr($linhas[2], 13, 1));
        $this->assertEquals('Q', substr($linhas[3], 13, 1));
        $this->assertEquals('R', substr($linhas[4], 13, 1));
        $this->assertEquals('5', substr($linhas[5], 7, 1));
        $this->assertEquals('9', substr($linhas[6], 7, 1));
    }

    /**
     * Header de arquivo conforme seção 4.1.2
     */
    public function testHeaderArquivo()
    {
        $remessa = $this->remessa();
        $remessa->addBoleto($this->boleto());
        $header = $this->linhas($remessa)[0];

        $this->assertEquals('0000', substr($header, 3, 4));
        $this->assertEquals('2', substr($header, 17, 1));                 // 18 tipo inscricao
        $this->assertEquals('11643817000102', substr($header, 18, 14));   // 19-32 cnpj
        $this->assertEquals('00000000000000028245', substr($header, 32, 20)); // 33-52 convenio
        $this->assertEquals('01069', substr($header, 52, 5));             // 53-57 cooperativa
        $this->assertEquals('0', substr($header, 57, 1));                 // 58 dv agencia
        $this->assertEquals('000000028245', substr($header, 58, 12));     // 59-70 conta
        $this->assertEquals('6', substr($header, 70, 1));                 // 71 dv conta
        $this->assertEquals('CRESOL' . str_repeat(' ', 24), substr($header, 102, 30));
        $this->assertEquals('1', substr($header, 142, 1));                // 143 remessa
        $this->assertEquals('000000', substr($header, 157, 6));           // 158-163 zeros
        $this->assertEquals('084', substr($header, 163, 3));              // 164-166 versao
        $this->assertEquals('00000', substr($header, 166, 5));            // 167-171 densidade
    }

    /**
     * Header de lote, com destaque para o convênio 34-53 (seção 4.1.3):
     * zeros + carteira + cooperativa + conta + dígito
     */
    public function testHeaderLote()
    {
        $remessa = $this->remessa();
        $remessa->addBoleto($this->boleto());
        $headerLote = $this->linhas($remessa)[1];

        $this->assertEquals('0001', substr($headerLote, 3, 4));
        $this->assertEquals('R', substr($headerLote, 8, 1));
        $this->assertEquals('01', substr($headerLote, 9, 2));
        $this->assertEquals('042', substr($headerLote, 13, 3));
        $this->assertEquals('011643817000102', substr($headerLote, 18, 15)); // 19-33 cnpj
        $this->assertEquals('00000090106900282456', substr($headerLote, 33, 20)); // 34-53 convenio
        $this->assertEquals('01069', substr($headerLote, 53, 5));
        $this->assertEquals('000000028245', substr($headerLote, 59, 12));
        $this->assertEquals('6', substr($headerLote, 71, 1));
    }

    /**
     * Segmento P conforme seção 4.1.4
     */
    public function testSegmentoP()
    {
        $remessa = $this->remessa();
        $remessa->addBoleto($this->boleto());
        $p = $this->linhas($remessa)[2];

        $this->assertEquals('00001', substr($p, 8, 5));                  // 9-13 sequencial
        $this->assertEquals('01', substr($p, 15, 2));                    // 16-17 remessa
        $this->assertEquals('01069', substr($p, 17, 5));                 // 18-22 cooperativa
        $this->assertEquals('000000028245', substr($p, 23, 12));         // 24-35 conta
        $this->assertEquals('6', substr($p, 35, 1));                     // 36 dv
        $this->assertEquals('00000000001', substr($p, 45, 11));          // 46-56 nosso numero
        $this->assertEquals('1', substr($p, 56, 1));                     // 57 dv nosso numero
        $this->assertEquals('2', substr($p, 60, 1));                     // 61 cliente emite
        $this->assertEquals('10032026', substr($p, 77, 8));              // 78-85 vencimento
        $this->assertEquals('000000000015000', substr($p, 85, 15));      // 86-100 valor
        $this->assertEquals('02', substr($p, 106, 2));                   // 107-108 especie DM
        $this->assertEquals('N', substr($p, 108, 1));                    // 109 aceite
        $this->assertEquals('10022026', substr($p, 109, 8));             // 110-117 emissao
        $this->assertEquals('1', substr($p, 117, 1));                    // 118 juros real ao dia
        $this->assertEquals('000000000000002', substr($p, 126, 15));     // 127-141 R$/dia
        $this->assertEquals('3', substr($p, 220, 1));                    // 221 nao protestar
        $this->assertEquals('00', substr($p, 221, 2));                   // 222-223 dias
        $this->assertEquals('09', substr($p, 227, 2));                   // 228-229 moeda
        $this->assertEquals('00000000000', substr($p, 229, 11));         // 230-240 zeros
    }

    /**
     * Diferente do CNAB 400, o 240 carrega a instrução de protesto no segmento P
     */
    public function testSegmentoPComProtesto()
    {
        $remessa = $this->remessa();
        $remessa->addBoleto($this->boleto(['diasProtesto' => 5]));
        $p = $this->linhas($remessa)[2];

        $this->assertEquals('1', substr($p, 220, 1));
        $this->assertEquals('05', substr($p, 221, 2));
    }

    /**
     * A posição 118 declara "1 = real ao dia", então 127-141 leva o valor em reais por
     * dia derivado da taxa mensal, e não a taxa mensal em si.
     * 6% ao mês sobre R$ 150,00 = R$ 0,30 por dia.
     */
    public function testJurosSaiEmReaisPorDia()
    {
        $remessa = $this->remessa();
        $remessa->addBoleto($this->boleto(['juros' => 6]));
        $p = $this->linhas($remessa)[2];

        $this->assertEquals('1', substr($p, 117, 1));
        $this->assertEquals('10032026', substr($p, 118, 8));             // 119-126 data do juros
        $this->assertEquals('000000000000030', substr($p, 126, 15));
    }

    /**
     * Sem juros, a data e o valor ficam zerados
     */
    public function testSemJurosZeraDataEValor()
    {
        $remessa = $this->remessa();
        $remessa->addBoleto($this->boleto(['juros' => 0]));
        $p = $this->linhas($remessa)[2];

        $this->assertEquals('00000000', substr($p, 118, 8));
        $this->assertEquals('000000000000000', substr($p, 126, 15));
    }

    /**
     * Desconto percentual é convertido em reais, porque a posição 142 do manual Cresol
     * só admite "0 = sem desconto" e "1 = com desconto"
     */
    public function testDescontoPercentualViraValorAbsoluto()
    {
        $remessa = $this->remessa();
        $remessa->addBoleto($this->boleto([
            'descontoPercentual' => 10,
            'descontoCodigo' => \Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto::TIPO_DESCONTO_PERCENTUAL,
            'dataDesconto'   => new Carbon('2026-03-05'),
        ]));
        $p = $this->linhas($remessa)[2];

        $this->assertEquals('1', substr($p, 141, 1));                    // 142 com desconto
        $this->assertEquals('05032026', substr($p, 142, 8));             // 143-150 data
        $this->assertEquals('000000000001500', substr($p, 150, 15));     // 151-165: 10% de 150
    }

    /**
     * Sem desconto, o bloco 142-165 fica zerado
     */
    public function testSemDescontoZeraOBloco()
    {
        $remessa = $this->remessa();
        $remessa->addBoleto($this->boleto());
        $p = $this->linhas($remessa)[2];

        $this->assertEquals('0', substr($p, 141, 1));
        $this->assertEquals('00000000', substr($p, 142, 8));
        $this->assertEquals('000000000000000', substr($p, 150, 15));
    }

    /**
     * Segmento Q conforme seção 4.1.5, incluindo a quebra do CEP em 5 + 3
     */
    public function testSegmentoQ()
    {
        $remessa = $this->remessa();
        $remessa->addBoleto($this->boleto());
        $q = $this->linhas($remessa)[3];

        $this->assertEquals('00002', substr($q, 8, 5));                  // sequencial continua o P
        $this->assertEquals('1', substr($q, 17, 1));                     // 18 CPF
        $this->assertEquals('000012345678909', substr($q, 18, 15));      // 19-33 cpf
        $this->assertEquals('PAGADOR TESTE', trim(substr($q, 33, 40)));  // 34-73 nome
        $this->assertEquals('CENTRO', trim(substr($q, 113, 15)));        // 114-128 bairro
        $this->assertEquals('85010', substr($q, 128, 5));                // 129-133 cep
        $this->assertEquals('000', substr($q, 133, 3));                  // 134-136 sufixo
        $this->assertEquals('PR', substr($q, 151, 2));                   // 152-153 uf
        $this->assertEquals('0', substr($q, 153, 1));                    // 154 avalista zerado
        $this->assertEquals('000000000000000', substr($q, 154, 15));     // 155-169 zeros
        $this->assertEquals('000', substr($q, 209, 3));                  // 210-212 zeros
    }

    /**
     * Segmento R traz a multa percentual conforme seção 4.1.6
     */
    public function testSegmentoRComMulta()
    {
        $remessa = $this->remessa();
        $remessa->addBoleto($this->boleto());
        $r = $this->linhas($remessa)[4];

        $this->assertEquals('00003', substr($r, 8, 5));
        $this->assertEquals('0', substr($r, 17, 1));                     // 18 desconto 2 zerado
        $this->assertEquals('2', substr($r, 65, 1));                     // 66 multa percentual
        $this->assertEquals('10032026', substr($r, 66, 8));              // 67-74 data da multa
        $this->assertEquals('000000000000200', substr($r, 74, 15));      // 75-89 valor da multa
    }

    /**
     * O segmento R é opcional: sem multa ele não é gravado
     */
    public function testSegmentoROmitidoQuandoNaoNecessario()
    {
        $remessa = $this->remessa();
        $remessa->addBoleto($this->boleto(['multa' => 0]));

        $linhas = $this->linhas($remessa);

        $this->assertCount(6, $linhas);
        $this->assertEquals('Q', substr($linhas[3], 13, 1));
        $this->assertEquals('5', substr($linhas[4], 7, 1));
    }

    /**
     * Trailers conforme seções 4.1.7 e 4.1.8, contando as linhas do lote e do arquivo
     */
    public function testTrailers()
    {
        $remessa = $this->remessa();
        $remessa->addBoleto($this->boleto(['numero' => 10, 'numeroDocumento' => 10]));
        $remessa->addBoleto($this->boleto(['numero' => 13, 'numeroDocumento' => 13]));

        $linhas = $this->linhas($remessa);
        $trailerLote = $linhas[count($linhas) - 2];
        $trailer = $linhas[count($linhas) - 1];

        $this->assertCount(10, $linhas);
        $this->assertEquals('000008', substr($trailerLote, 17, 6));      // 18-23: 6 detalhes + 2
        $this->assertEquals(str_repeat('0', 92), substr($trailerLote, 23, 92));
        $this->assertEquals('9999', substr($trailer, 3, 4));
        $this->assertEquals('000001', substr($trailer, 17, 6));          // 18-23 lotes
        $this->assertEquals('000010', substr($trailer, 23, 6));          // 24-29 registros
        $this->assertEquals('000000', substr($trailer, 29, 6));
    }

    /**
     * O dígito "P" não cabe na posição 57, declarada numérica no manual do 240
     */
    public function testNossoNumeroComDigitoLetraLancaExcecao()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessageMatches('/d[ií]gito verificador "P"/iu');

        $remessa = $this->remessa();
        $remessa->addBoleto($this->boleto(['numero' => 2]));
    }

    /**
     * A faixa liberada vale para os dois layouts
     */
    public function testNossoNumeroForaDaFaixaLancaExcecao()
    {
        $remessa = $this->remessa();
        $remessa->setFaixaNossoNumero(1, 100);
        $remessa->addBoleto($this->boleto(['numero' => 1]));

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessageMatches('/fora da faixa/i');
        $remessa->addBoleto($this->boleto(['numero' => 101]));
    }

    /**
     * O manual limita a multa do segmento R a 99,99%
     */
    public function testMultaAcimaDoLimiteLancaExcecao()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessageMatches('/excede o limite de 99,99/u');

        $remessa = $this->remessa();
        $remessa->addBoleto($this->boleto(['multa' => 150]));
    }

    /**
     * O manual do 240 não tem código para "alteração de outros dados"
     */
    public function testStatusAlteracaoLancaExcecao()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessageMatches('/altera[çc][ãa]o de outros dados/iu');

        $remessa = $this->remessa();
        $remessa->addBoleto($this->boleto()->alterarBoleto());
    }

    /**
     * Baixa usa o código de movimento 02 em todos os segmentos do título
     */
    public function testStatusBaixaUsaCodigoDeMovimento02()
    {
        $remessa = $this->remessa();
        $remessa->addBoleto($this->boleto()->baixarBoleto());

        $linhas = $this->linhas($remessa);

        $this->assertEquals('02', substr($linhas[2], 15, 2));
        $this->assertEquals('02', substr($linhas[3], 15, 2));
        $this->assertEquals('02', substr($linhas[4], 15, 2));
    }
}
