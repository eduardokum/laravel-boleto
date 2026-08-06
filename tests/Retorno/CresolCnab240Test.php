<?php

namespace Eduardokum\LaravelBoleto\Tests\Retorno;

use Illuminate\Support\Collection;
use Eduardokum\LaravelBoleto\Tests\TestCase;
use Eduardokum\LaravelBoleto\Cnab\Retorno\Factory;
use Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab240\Detalhe;
use Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab240\Banco\Cresol;

class CresolCnab240Test extends TestCase
{
    /**
     * @return Cresol
     * @throws \Exception
     */
    private function retorno()
    {
        $retorno = Factory::make(__DIR__ . '/files/cnab240/cresol.ret');
        $retorno->processar();

        return $retorno;
    }

    public function testFactoryReconheceOLayoutCresol()
    {
        $retorno = $this->retorno();

        $this->assertInstanceOf(Cresol::class, $retorno);
        $this->assertEquals('133', $retorno->getCodigoBanco());
        $this->assertEquals('Cresol', $retorno->getBancoNome());
        $this->assertInstanceOf(Collection::class, $retorno->getDetalhes());
        $this->assertCount(2, $retorno->getDetalhes());
    }

    /**
     * Header de arquivo conforme seção 5.1.1
     */
    public function testHeaderArquivo()
    {
        $header = $this->retorno()->getHeader();

        $this->assertEquals('133', $header->getCodBanco());
        $this->assertEquals('0000', $header->getLoteServico());
        $this->assertEquals('0', $header->getTipoRegistro());
        $this->assertEquals('2', $header->getTipoInscricao());
        $this->assertEquals('11643817000102', $header->getNumeroInscricao());
        $this->assertEquals('1069', $header->getAgencia());
        $this->assertEquals('28245', $header->getConta());
        $this->assertEquals('6', $header->getContaDv());
        $this->assertEquals('BENEFICIARIO LTDA', $header->getNomeEmpresa());
        $this->assertEquals('CRESOL', $header->getNomeBanco());
        $this->assertEquals('2', $header->getCodigoRemessaRetorno());
        $this->assertEquals('084', $header->getVersaoLayoutArquivo());
    }

    /**
     * Header de lote conforme seção 5.1.2, com operação "T" e layout de lote 042.
     * Diferente do header de arquivo, o header de lote preserva os zeros à esquerda de
     * agência e conta — comportamento da própria lib, não do layout.
     */
    public function testHeaderLote()
    {
        $headerLote = $this->retorno()->getHeaderLote();

        $this->assertEquals('133', $headerLote->getCodBanco());
        $this->assertEquals('0001', $headerLote->getNumeroLoteRetorno());
        $this->assertEquals('T', $headerLote->getTipoOperacao());
        $this->assertEquals('01', $headerLote->getTipoServico());
        $this->assertEquals('042', $headerLote->getVersaoLayoutLote());
        $this->assertEquals('01069', $headerLote->getAgencia());
        $this->assertEquals('000000028245', $headerLote->getConta());
        $this->assertEquals('6', $headerLote->getContaDv());
    }

    /**
     * Segmentos T e U de um título liquidado (seções 5.1.3 e 5.1.4)
     */
    public function testDetalheLiquidado()
    {
        $detalhe = $this->retorno()->getDetalhe(1);

        $this->assertInstanceOf(Detalhe::class, $detalhe);
        $this->assertEquals('06', $detalhe->getOcorrencia());
        $this->assertEquals(Detalhe::OCORRENCIA_LIQUIDADA, $detalhe->getOcorrenciaTipo());
        $this->assertEquals('1', ltrim($detalhe->getNossoNumero(), '0'));
        $this->assertEquals('1', ltrim($detalhe->getNumeroDocumento(), '0'));
        $this->assertEquals('CONTROLE-1', $detalhe->getNumeroControle());
        $this->assertEquals('10/03/2026', $detalhe->getDataVencimento());
        $this->assertEquals('150.00', $detalhe->getValor());
        $this->assertEquals('150.00', $detalhe->getValorRecebido());
        $this->assertEquals('PAGADOR TESTE', $detalhe->getPagador()->getNome());
        $this->assertEquals('11/03/2026', $detalhe->getDataOcorrencia());
        $this->assertEquals('12/03/2026', $detalhe->getDataCredito());
    }

    /**
     * A tarifa do liquidado sai da diferença entre o valor pago e o líquido creditado
     * (segmento U, pos. 078-092 e 093-107)
     */
    public function testTarifaDoTituloLiquidado()
    {
        $this->assertEquals(2.5, $this->retorno()->getDetalhe(1)->getValorTarifa());
    }

    /**
     * O código de baixa/liquidação vem em 214-223 e complementa a descrição da ocorrência
     */
    public function testDescricaoDaLiquidacaoRecebeOComplemento()
    {
        $this->assertEquals(
            'Liquidação Compensação Eletrônica',
            $this->retorno()->getDetalhe(1)->getOcorrenciaDescricao()
        );
    }

    /**
     * As posições 214-223 são cinco motivos de duas posições. O campo tem 10 posições:
     * tratá-lo com 8 deslocava os pares e nenhuma rejeição era encontrada.
     */
    public function testDetalheRejeitadoResolveOsDoisMotivos()
    {
        $detalhe = $this->retorno()->getDetalhe(2);

        $this->assertEquals('03', $detalhe->getOcorrencia());
        $this->assertEquals('Entrada Rejeitada', $detalhe->getOcorrenciaDescricao());
        $this->assertStringContainsString('Nosso Número Inválido', $detalhe->getError());
        $this->assertStringContainsString('Código do Segmento Inválido', $detalhe->getError());
    }

    /**
     * Trailer de lote (seção 5.1.5): as posições 024-115 são zeros no layout Cresol, então
     * a quantidade e o valor da cobrança simples são apurados a partir dos detalhes
     */
    public function testTrailerLote()
    {
        $trailerLote = $this->retorno()->getTrailerLote();

        $this->assertEquals(6, $trailerLote->getQtdRegistroLote());
        $this->assertEquals(2, $trailerLote->getQtdTitulosCobrancaSimples());
        $this->assertEquals('450.00', $trailerLote->getValorTotalTitulosCobrancaSimples());
        $this->assertEquals(0, $trailerLote->getQtdTitulosCobrancaVinculada());
        $this->assertEquals(0, $trailerLote->getQtdTitulosCobrancaCaucionada());
        $this->assertEquals(0, $trailerLote->getQtdTitulosCobrancaDescontada());
    }

    /**
     * Trailer de arquivo conforme seção 5.1.6
     */
    public function testTrailerArquivo()
    {
        $trailer = $this->retorno()->getTrailer();

        $this->assertEquals('9999', $trailer->getNumeroLote());
        $this->assertEquals('9', $trailer->getTipoRegistro());
        $this->assertEquals(1, $trailer->getQtdLotesArquivo());
        $this->assertEquals(8, $trailer->getQtdRegistroArquivo());
    }
}
