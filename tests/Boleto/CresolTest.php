<?php

namespace Eduardokum\LaravelBoleto\Tests\Boleto;

use Carbon\Carbon;
use Eduardokum\LaravelBoleto\Pessoa;
use Eduardokum\LaravelBoleto\CalculoDV;
use Eduardokum\LaravelBoleto\Tests\TestCase;
use Eduardokum\LaravelBoleto\Boleto\Banco\Cresol;

class CresolTest extends TestCase
{
    protected static $pagador;

    protected static $beneficiario;

    public static function setUpBeforeClass(): void
    {
        self::$beneficiario = new Pessoa([
            'nome'      => 'BENEFICIARIO PESSOA FISICA',
            'endereco'  => 'Rua um, 123',
            'cep'       => '99999-999',
            'uf'        => 'PR',
            'cidade'    => 'CIDADE',
            'documento' => '077.651.119-06',
        ]);

        self::$pagador = new Pessoa([
            'nome'      => 'Cliente',
            'endereco'  => 'Rua um, 123',
            'bairro'    => 'Bairro',
            'cep'       => '99999-999',
            'uf'        => 'PR',
            'cidade'    => 'CIDADE',
            'documento' => '999.999.999-99',
        ]);
    }

    /**
     * Monta um boleto Cresol com o mínimo necessário para o código de barras
     */
    private function boleto(array $params = [])
    {
        return new Cresol(array_merge([
            'carteira'      => '09',
            'beneficiario'  => self::$beneficiario,
            'pagador'       => self::$pagador,
            'especieDoc'    => 'DM',
        ], $params));
    }

    /**
     * Exemplos 1 a 3 do item 3 das especificações técnicas, que usam a carteira 19
     */
    public function testDvNossoNumeroExemplosDoManual()
    {
        $this->assertEquals('8', CalculoDV::cresolNossoNumero('19', 2));
        $this->assertEquals('P', CalculoDV::cresolNossoNumero('19', 1));
        $this->assertEquals('0', CalculoDV::cresolNossoNumero('19', 6));
    }

    /**
     * Resto 1 produz a letra "P" em vez de dígito numérico. Na carteira 09 o menor
     * nosso número nessa condição é o 2
     */
    public function testDvNossoNumeroRestoUmRetornaP()
    {
        $this->assertEquals('P', CalculoDV::cresolNossoNumero('09', 2));

        $boleto = $this->boleto([
            'agencia'        => 1069,
            'conta'          => 28245,
            'numero'         => 2,
            'dataVencimento' => new Carbon('2026-01-15'),
            'valor'          => 10.00,
        ]);

        $this->assertEquals('P', $boleto->getNossoNumeroDv());
        $this->assertTrue($boleto->nossoNumeroDvEhLetra());
        $this->assertEquals('00000000002P', $boleto->getNossoNumero());
    }

    /**
     * Resto 0 produz dígito 0, sem a subtração de 11. Na carteira 09 o menor
     * nosso número nessa condição é o 10
     */
    public function testDvNossoNumeroRestoZeroRetornaZero()
    {
        $this->assertEquals('0', CalculoDV::cresolNossoNumero('09', 10));

        $boleto = $this->boleto([
            'agencia'        => 1069,
            'conta'          => 28245,
            'numero'         => 10,
            'dataVencimento' => new Carbon('2026-01-15'),
            'valor'          => 10.00,
        ]);

        $this->assertEquals('0', $boleto->getNossoNumeroDv());
        $this->assertFalse($boleto->nossoNumeroDvEhLetra());
    }

    /**
     * Vetor golden A - boleto real das especificações técnicas (item 8)
     */
    public function testVetorGoldenEspecificacoesTecnicas()
    {
        $boleto = $this->boleto([
            'agencia'        => 2035,
            'conta'          => 4497,
            'numero'         => 6362,
            'dataVencimento' => new Carbon('2021-08-30'),
            'valor'          => 175.00,
        ]);

        $this->assertEquals('5', $boleto->getNossoNumeroDv());
        $this->assertEquals('000000063625', $boleto->getNossoNumero());
        $this->assertEquals('2035090000000636200044970', substr($boleto->getCodigoBarras(), 19, 25));
        $this->assertEquals(
            '13392.03505 90000.000639 62000.449702 4 87280000017500',
            $boleto->getLinhaDigitavel()
        );
    }

    /**
     * Vetor golden B - boleto real de exemplo. A conta impressa no boleto e 14.257-3,
     * mas o campo livre carrega a conta do cedente no sistema Cresol (3892)
     */
    public function testVetorGoldenBoletoExemplo()
    {
        $boleto = $this->boleto([
            'agencia'        => 1026,
            'conta'          => 14257,
            'contaDv'        => 3,
            'codigoCedente'  => 3892,
            'numero'         => 3,
            'dataVencimento' => new Carbon('2021-06-14'),
            'valor'          => 5.00,
        ]);

        $this->assertEquals('8', $boleto->getNossoNumeroDv());
        $this->assertEquals('1026090000000000300038920', substr($boleto->getCodigoBarras(), 19, 25));
        $this->assertEquals(
            '13391.02608 90000.000001 03000.389209 7 86510000000500',
            $boleto->getLinhaDigitavel()
        );
    }

    /**
     * Sem codigoCedente informado o campo livre usa a conta corrente
     */
    public function testCodigoCedentePadraoEAConta()
    {
        $boleto = $this->boleto([
            'agencia'        => 1069,
            'conta'          => 28245,
            'numero'         => 1,
            'dataVencimento' => new Carbon('2026-01-15'),
            'valor'          => 10.00,
        ]);

        $this->assertEquals('28245', $boleto->getCodigoCedente());
        $this->assertEquals('0028245', substr($boleto->getCodigoBarras(), 36, 7));
    }

    /**
     * O campo livre e reversivel pelo parse da propria classe
     */
    public function testParseCampoLivre()
    {
        $parsed = Cresol::parseCampoLivre('2035090000000636200044970');

        $this->assertEquals('2035', $parsed['agencia']);
        $this->assertEquals('09', $parsed['carteira']);
        $this->assertEquals('00000006362', $parsed['nossoNumero']);
        $this->assertEquals('0004497', $parsed['codigoCedente']);
    }

    /**
     * A tabela de especies segue o manual Cresol, onde duplicata mercantil e 02
     */
    public function testEspecieDocumentoSegueTabelaCresol()
    {
        $boleto = $this->boleto([
            'agencia'        => 1069,
            'conta'          => 28245,
            'numero'         => 1,
            'dataVencimento' => new Carbon('2026-01-15'),
            'valor'          => 10.00,
        ]);

        $this->assertEquals('02', $boleto->getEspecieDocCodigo('99', 400));
        $this->assertEquals('02', $boleto->getEspecieDocCodigo('99', 240));

        $boleto->setEspecieDoc('RC');
        $this->assertEquals('17', $boleto->getEspecieDocCodigo('99', 400));
    }

    /**
     * Beneficiario pessoa fisica e suportado
     */
    public function testBeneficiarioPessoaFisica()
    {
        $boleto = $this->boleto([
            'agencia'        => 1069,
            'conta'          => 28245,
            'numero'         => 1,
            'dataVencimento' => new Carbon('2026-01-15'),
            'valor'          => 10.00,
        ]);

        $this->assertEquals('077.651.119-06', $boleto->getBeneficiario()->getDocumento());
        $this->assertEquals(11, strlen(\Eduardokum\LaravelBoleto\Util::onlyNumbers($boleto->getBeneficiario()->getDocumento())));
    }
}
