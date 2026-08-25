<?php

namespace Retorno;

use Illuminate\Support\Collection;
use Eduardokum\LaravelBoleto\Tests\TestCase;
use Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab240\Detalhe;
use Eduardokum\LaravelBoleto\Exception\ValidationException;

class RetornoCnab240Test extends TestCase
{
    public function testRetornoSantanderCnab240()
    {
        $retorno = \Eduardokum\LaravelBoleto\Cnab\Retorno\Factory::make(__DIR__ . '/files/cnab240/santander.ret');
        $retorno->processar();

        $this->assertNotNull($retorno->getHeader());
        $this->assertNotNull($retorno->getHeaderLote());
        $this->assertNotNull($retorno->getDetalhes());
        $this->assertNotNull($retorno->getTrailerLote());
        $this->assertNotNull($retorno->getTrailer());

        $this->assertEquals('Banco Santander (Brasil) S.A.', $retorno->getBancoNome());
        $this->assertEquals('033', $retorno->getCodigoBanco());

        $this->assertInstanceOf(Collection::class, $retorno->getDetalhes());

        $this->assertInstanceOf(Detalhe::class, $retorno->getDetalhe(1));

        foreach ($retorno->getDetalhes() as $detalhe) {
            $this->assertInstanceOf(Detalhe::class, $detalhe);
            $this->assertArrayHasKey('numeroDocumento', $detalhe->toArray());
        }
    }

    public function testRetornoSemDetalheCnab240()
    {
        $this->expectException(ValidationException::class);
        $retorno = \Eduardokum\LaravelBoleto\Cnab\Retorno\Factory::make(__DIR__ . '/files/cnab240/retorno_sem_detalhe.ret');
        $retorno->processar();
    }

    public function testRetornoPixCnab240Santander()
    {
        $retorno = \Eduardokum\LaravelBoleto\Cnab\Retorno\Factory::make(__DIR__ . '/files/cnab240/santander_pix.ret');
        $retorno->processar();

        $this->assertNotNull($retorno->getHeader());
        $this->assertNotNull($retorno->getDetalhes());
        $this->assertNotNull($retorno->getTrailer());

        $this->assertCount(1, $retorno->getDetalhes());
        $this->assertEquals('033', $retorno->getCodigoBanco());

        $detalhe = $retorno->getDetalhe(1);
        $this->assertInstanceOf(Detalhe::class, $detalhe);
        $this->assertEquals('TXID123456789012345678901234', $detalhe->getId());
        $this->assertEquals(
            'qr.santander.com.br/pix/v2/9d36b84f-c70b-478f-b95c-12729b90ca25',
            $detalhe->getPixLocation()
        );
    }

    public function testRetornoPixCnab240SantanderLiquidacaoComChaveNaoDefineLocation()
    {
        $retorno = \Eduardokum\LaravelBoleto\Cnab\Retorno\Factory::make(
            __DIR__ . '/files/cnab240/santander_pix_liquidacao_chave.ret'
        );
        $retorno->processar();

        $this->assertCount(1, $retorno->getDetalhes());

        $detalhe = $retorno->getDetalhe(1);
        $this->assertEquals('YKP000550547000000000003509062026', $detalhe->getId());
        $this->assertNull($detalhe->getPixLocation());
    }

    public function testRetornoSemPixCnab240Santander()
    {
        $retorno = \Eduardokum\LaravelBoleto\Cnab\Retorno\Factory::make(__DIR__ . '/files/cnab240/santander.ret');
        $retorno->processar();

        $this->assertCount(1, $retorno->getDetalhes());
        $this->assertNull($retorno->getDetalhe(1)->getId());
        $this->assertNull($retorno->getDetalhe(1)->getPixLocation());
    }
}
