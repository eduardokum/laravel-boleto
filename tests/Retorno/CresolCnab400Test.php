<?php

namespace Eduardokum\LaravelBoleto\Tests\Retorno;

use Eduardokum\LaravelBoleto\Tests\TestCase;
use Eduardokum\LaravelBoleto\Cnab\Retorno\Factory;
use Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab400\Detalhe;
use Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab400\Banco\Cresol;

class CresolCnab400Test extends TestCase
{
    private function retorno()
    {
        $retorno = Factory::make(__DIR__ . '/files/cnab400/cresol.ret');
        $retorno->processar();

        return $retorno;
    }

    /**
     * A factory precisa resolver o banco 133 pelo conteúdo do arquivo
     */
    public function testFactoryResolveCresol()
    {
        $this->assertInstanceOf(Cresol::class, $this->retorno());
    }

    public function testHeader()
    {
        $header = $this->retorno()->getHeader();

        $this->assertEquals('2', $header->getOperacaoCodigo());
        $this->assertEquals('RETORNO', $header->getOperacao());
        $this->assertEquals('01', $header->getServicoCodigo());
        $this->assertEquals('90106900282456', $header->getCodigoCliente());
    }

    /**
     * Agência, conta e dígito vêm do primeiro detalhe
     */
    public function testHeaderRecebeAgenciaEContaDoDetalhe()
    {
        $header = $this->retorno()->getHeader();

        $this->assertEquals('1069', $header->getAgencia());
        $this->assertEquals('28245', $header->getConta());
        $this->assertEquals('6', $header->getContaDv());
    }

    public function testQuantidadeDeDetalhes()
    {
        $retorno = $this->retorno();

        $this->assertCount(3, $retorno->getDetalhes());
        $this->assertInstanceOf(Detalhe::class, $retorno->getDetalhes()->first());
    }

    /**
     * Entrada confirmada: ocorrência 02
     */
    public function testDetalheEntradaConfirmada()
    {
        $d = $this->retorno()->getDetalhes()->get(1);

        $this->assertEquals('02', $d->getOcorrencia());
        $this->assertEquals('Entrada Confirmada', $d->getOcorrenciaDescricao());
        $this->assertEquals($d::OCORRENCIA_ENTRADA, $d->getOcorrenciaTipo());
        $this->assertEquals('000000000011', $d->getNossoNumero());
        $this->assertEquals('150.00', $d->getValor());
        $this->assertEquals('10/03/2026', $d->getDataVencimento());
        $this->assertEquals('12/02/2026', $d->getDataOcorrencia());
    }

    /**
     * Liquidação: valores financeiros e nosso número com dígito "P"
     */
    public function testDetalheLiquidacao()
    {
        $d = $this->retorno()->getDetalhes()->get(2);

        $this->assertEquals('06', $d->getOcorrencia());
        $this->assertEquals('Liquidação', $d->getOcorrenciaDescricao());
        $this->assertEquals($d::OCORRENCIA_LIQUIDADA, $d->getOcorrenciaTipo());
        $this->assertEquals('00000000002P', $d->getNossoNumero());
        $this->assertEquals('150.00', $d->getValor());
        $this->assertEquals('153.08', $d->getValorRecebido());
        $this->assertEquals('3.50', $d->getValorTarifa());
        $this->assertEquals('0.08', $d->getValorMora());
        $this->assertEquals('16/03/2026', $d->getDataCredito());
    }

    /**
     * Entrada rejeitada: ocorrência 03 com o motivo lido das posições 319-328
     */
    public function testDetalheEntradaRejeitada()
    {
        $d = $this->retorno()->getDetalhes()->get(3);

        $this->assertEquals('03', $d->getOcorrencia());
        $this->assertEquals($d::OCORRENCIA_ERRO, $d->getOcorrenciaTipo());
        $this->assertEquals('Espécie do Título Inválida', $d->getRejeicao());
        $this->assertTrue($d->hasError());
    }

    /**
     * O trailer da Cresol não carrega totais, então eles são apurados no parse
     */
    public function testTrailerApuraTotais()
    {
        $trailer = $this->retorno()->getTrailer();

        $this->assertEquals(3, $trailer->getQuantidadeTitulos());
        $this->assertEquals('375.00', $trailer->getValorTitulos());
        $this->assertEquals(1, $trailer->getQuantidadeEntradas());
        $this->assertEquals(1, $trailer->getQuantidadeLiquidados());
        $this->assertEquals(1, $trailer->getQuantidadeErros());
    }

    public function testToArray()
    {
        $array = $this->retorno()->toArray();

        $this->assertArrayHasKey('header', $array);
        $this->assertArrayHasKey('trailer', $array);
        $this->assertArrayHasKey('detalhes', $array);
        $this->assertCount(3, $array['detalhes']);
    }
}
