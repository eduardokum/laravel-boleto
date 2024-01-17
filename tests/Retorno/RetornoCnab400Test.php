<?php

namespace Retorno;

use Exception;
use Illuminate\Support\Collection;
use Eduardokum\LaravelBoleto\Tests\TestCase;
use Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab400\Detalhe;
use Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab400\Banco\Bradesco;

class RetornoCnab400Test extends TestCase
{
    public function testRetornoInvalido()
    {
        $this->expectException(Exception::class);
        new Bradesco([]);
    }

    public function testRetornoBancoInvalido()
    {
        $this->expectException(Exception::class);
        new Bradesco(__DIR__ . '/files/cnab400/retorno_banco_fake.ret');
    }

    public function testRetornoServicoInvalido()
    {
        $this->expectException(Exception::class);
        new Bradesco(__DIR__ . '/files/cnab400/retorno_banco_fake_2.ret');
    }

    public function testRetornoSeekableIterator()
    {
        $retorno = \Eduardokum\LaravelBoleto\Cnab\Retorno\Factory::make(__DIR__ . '/files/cnab400/bradesco.ret');
        $retorno->processar();
        $retorno->rewind();
        $this->assertEquals(1, $retorno->key());
        $this->assertInstanceOf(Detalhe::class, $retorno->current());
        $retorno->seek(2);
        $this->assertEquals(2, $retorno->key());
        $this->assertInstanceOf(Detalhe::class, $retorno->current());
        $retorno->next();
        $this->assertEquals(3, $retorno->key());
        $this->assertInstanceOf(Detalhe::class, $retorno->current());

        $this->expectException(Exception::class);
        $retorno->seek(100);
    }

    public function testRetornoToArray()
    {
        $retorno = \Eduardokum\LaravelBoleto\Cnab\Retorno\Factory::make(__DIR__ . '/files/cnab400/bradesco.ret');
        $retorno->processar();

        $array = $retorno->toArray();

        $this->assertArrayHasKey('header', $array);
        $this->assertArrayHasKey('trailer', $array);
        $this->assertArrayHasKey('detalhes', $array);
    }

    public function testRetornoOcorrencia()
    {
        $retorno = \Eduardokum\LaravelBoleto\Cnab\Retorno\Factory::make(__DIR__ . '/files/cnab400/bradesco.ret');
        $retorno->processar();

        $detalhe = $retorno->current();

        $this->assertTrue($detalhe->hasOcorrencia());
        $this->assertTrue($detalhe->hasOcorrencia('02'));
        $this->assertTrue($detalhe->hasOcorrencia(['02']));
    }

    public function testRetornoBradescoCnab400()
    {
        $retorno = \Eduardokum\LaravelBoleto\Cnab\Retorno\Factory::make(__DIR__ . '/files/cnab400/bradesco.ret');
        $retorno->processar();

        $this->assertNotNull($retorno->getHeader());
        $this->assertNotNull($retorno->getDetalhes());
        $this->assertNotNull($retorno->getTrailer());

        $this->assertEquals('Banco Bradesco S.A.', $retorno->getBancoNome());
        $this->assertEquals('237', $retorno->getCodigoBanco());

        $this->assertEquals('0', $retorno->getTrailer()->avisos);
        $this->assertEquals('6', $retorno->getTrailer()->quantidadeLiquidados);

        $this->assertInstanceOf(Collection::class, $retorno->getDetalhes());
        $this->assertInstanceOf(Detalhe::class, $retorno->getDetalhe(1));

        foreach ($retorno->getDetalhes() as $detalhe) {
            $this->assertInstanceOf(Detalhe::class, $detalhe);
            $this->assertArrayHasKey('numeroDocumento', $detalhe->toArray());
        }
    }

    public function testRetornoBancoBrasilCnab400()
    {
        $retorno = \Eduardokum\LaravelBoleto\Cnab\Retorno\Factory::make(__DIR__ . '/files/cnab400/banco_brasil.ret');
        $retorno->processar();

        $this->assertNotNull($retorno->getHeader());
        $this->assertNotNull($retorno->getDetalhes());
        $this->assertNotNull($retorno->getTrailer());

        $this->assertEquals('Banco do Brasil S.A.', $retorno->getBancoNome());
        $this->assertEquals('001', $retorno->getCodigoBanco());

        $this->assertEquals('0', $retorno->getTrailer()->avisos);
        $this->assertEquals('1', $retorno->getTrailer()->quantidadeLiquidados);

        $this->assertInstanceOf(Collection::class, $retorno->getDetalhes());
        $this->assertInstanceOf(Detalhe::class, $retorno->getDetalhe(1));

        foreach ($retorno->getDetalhes() as $detalhe) {
            $this->assertInstanceOf(Detalhe::class, $detalhe);
            $this->assertArrayHasKey('numeroDocumento', $detalhe->toArray());
        }
    }

    public function testRetornoItauCnab400()
    {
        $retorno = \Eduardokum\LaravelBoleto\Cnab\Retorno\Factory::make(__DIR__ . '/files/cnab400/itau.ret');
        $retorno->processar();

        $this->assertNotNull($retorno->getHeader());
        $this->assertNotNull($retorno->getDetalhes());
        $this->assertNotNull($retorno->getTrailer());

        $this->assertEquals('Itaú Unibanco S.A.', $retorno->getBancoNome());
        $this->assertEquals('341', $retorno->getCodigoBanco());

        $this->assertEquals('0', $retorno->getTrailer()->avisos);
        $this->assertEquals('3', $retorno->getTrailer()->quantidadeLiquidados);

        $this->assertInstanceOf(Collection::class, $retorno->getDetalhes());
        $this->assertInstanceOf(Detalhe::class, $retorno->getDetalhe(1));

        foreach ($retorno->getDetalhes() as $detalhe) {
            $this->assertInstanceOf(Detalhe::class, $detalhe);
            $this->assertArrayHasKey('numeroDocumento', $detalhe->toArray());
        }

    }

    public function testRetornoCefCnab400()
    {
        $retorno = \Eduardokum\LaravelBoleto\Cnab\Retorno\Factory::make(__DIR__ . '/files/cnab400/cef.ret');
        $retorno->processar();

        $this->assertNotNull($retorno->getHeader());
        $this->assertNotNull($retorno->getDetalhes());
        $this->assertNotNull($retorno->getTrailer());

        $this->assertEquals('Caixa Econômica Federal', $retorno->getBancoNome());
        $this->assertEquals('104', $retorno->getCodigoBanco());

        $this->assertInstanceOf(Collection::class, $retorno->getDetalhes());
        $this->assertInstanceOf(Detalhe::class, $retorno->getDetalhe(1));

        foreach ($retorno->getDetalhes() as $detalhe) {
            $this->assertInstanceOf(Detalhe::class, $detalhe);
            $this->assertArrayHasKey('numeroDocumento', $detalhe->toArray());
        }
    }

    public function testRetornoHsbcCnab400()
    {
        $retorno = \Eduardokum\LaravelBoleto\Cnab\Retorno\Factory::make(__DIR__ . '/files/cnab400/hsbc.ret');
        $retorno->processar();

        $this->assertNotNull($retorno->getHeader());
        $this->assertNotNull($retorno->getDetalhes());
        $this->assertNotNull($retorno->getTrailer());

        $this->assertEquals('HSBC Bank Brasil S.A.', $retorno->getBancoNome());
        $this->assertEquals('399', $retorno->getCodigoBanco());

        $this->assertInstanceOf(Collection::class, $retorno->getDetalhes());
        $this->assertInstanceOf(Detalhe::class, $retorno->getDetalhe(1));

        foreach ($retorno->getDetalhes() as $detalhe) {
            $this->assertInstanceOf(Detalhe::class, $detalhe);
            $this->assertArrayHasKey('numeroDocumento', $detalhe->toArray());
        }
    }

    public function testRetornoSantanderCnab400()
    {
        $retorno = \Eduardokum\LaravelBoleto\Cnab\Retorno\Factory::make(__DIR__ . '/files/cnab400/santander.ret');
        $retorno->processar();

        $this->assertNotNull($retorno->getHeader());
        $this->assertNotNull($retorno->getDetalhes());
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

    public function testRetornoSicrediCnab400()
    {
        $retorno = \Eduardokum\LaravelBoleto\Cnab\Retorno\Factory::make(__DIR__ . '/files/cnab400/sicredi.ret');
        $retorno->processar();

        $this->assertNotNull($retorno->getHeader());
        $this->assertNotNull($retorno->getDetalhes());
        $this->assertNotNull($retorno->getTrailer());

        $this->assertEquals('Banco Cooperativo Sicredi S.A.', $retorno->getBancoNome());
        $this->assertEquals('748', $retorno->getCodigoBanco());

        $this->assertInstanceOf(Collection::class, $retorno->getDetalhes());
        $this->assertInstanceOf(Detalhe::class, $retorno->getDetalhe(1));

        foreach ($retorno->getDetalhes() as $detalhe) {
            $this->assertInstanceOf(Detalhe::class, $detalhe);
            $this->assertArrayHasKey('numeroDocumento', $detalhe->toArray());
        }
    }

    public function testRetornoBanrisulCnab400()
    {
        $retorno = \Eduardokum\LaravelBoleto\Cnab\Retorno\Factory::make(__DIR__ . '/files/cnab400/banrisul.ret');
        $retorno->processar();

        $this->assertNotNull($retorno->getHeader());
        $this->assertNotNull($retorno->getDetalhes());
        $this->assertNotNull($retorno->getTrailer());

        $this->assertEquals('Banco do Estado do Rio Grande do Sul S.A.', $retorno->getBancoNome());
        $this->assertEquals('041', $retorno->getCodigoBanco());

        $this->assertInstanceOf(Collection::class, $retorno->getDetalhes());
        $this->assertInstanceOf(Detalhe::class, $retorno->getDetalhe(1));

        foreach ($retorno->getDetalhes() as $detalhe) {
            $this->assertInstanceOf(Detalhe::class, $detalhe);
            $this->assertArrayHasKey('numeroDocumento', $detalhe->toArray());
        }
    }

    public function testRetornoBbCnab400()
    {
        $retorno = \Eduardokum\LaravelBoleto\Cnab\Retorno\Factory::make(__DIR__ . '/files/cnab400/bb.ret');
        $retorno->processar();

        $this->assertNotNull($retorno->getHeader());
        $this->assertNotNull($retorno->getDetalhes());
        $this->assertNotNull($retorno->getTrailer());

        $this->assertEquals('Banco do Brasil S.A.', $retorno->getBancoNome());
        $this->assertEquals('001', $retorno->getCodigoBanco());

        $this->assertInstanceOf(Collection::class, $retorno->getDetalhes());
        $this->assertInstanceOf(Detalhe::class, $retorno->getDetalhe(1));

        foreach ($retorno->getDetalhes() as $detalhe) {
            $this->assertInstanceOf(Detalhe::class, $detalhe);
            $this->assertArrayHasKey('numeroDocumento', $detalhe->toArray());
        }
    }

    public function testRetornoBnbCnab400()
    {
        $retorno = \Eduardokum\LaravelBoleto\Cnab\Retorno\Factory::make(__DIR__ . '/files/cnab400/bnb.ret');
        $retorno->processar();

        $this->assertNotNull($retorno->getHeader());
        $this->assertNotNull($retorno->getDetalhes());
        $this->assertNotNull($retorno->getTrailer());

        $this->assertEquals('Banco do Nordeste do Brasil S.A.', $retorno->getBancoNome());
        $this->assertEquals('004', $retorno->getCodigoBanco());

        $this->assertInstanceOf(Collection::class, $retorno->getDetalhes());
        $this->assertInstanceOf(Detalhe::class, $retorno->getDetalhe(1));

        foreach ($retorno->getDetalhes() as $detalhe) {
            $this->assertInstanceOf(Detalhe::class, $detalhe);
            $this->assertArrayHasKey('numeroDocumento', $detalhe->toArray());
        }
    }

    public function testRetornoDellbankCnab400()
    {
        $retorno = \Eduardokum\LaravelBoleto\Cnab\Retorno\Factory::make(__DIR__ . '/files/cnab400/delbank.ret');
        $retorno->processar();

        $this->assertNotNull($retorno->getHeader());
        $this->assertNotNull($retorno->getDetalhes());
        $this->assertNotNull($retorno->getTrailer());

        $this->assertEquals('Delcred SCD S.A', $retorno->getBancoNome());
        $this->assertEquals('435', $retorno->getCodigoBanco());

        $this->assertInstanceOf(Collection::class, $retorno->getDetalhes());
        $this->assertInstanceOf(Detalhe::class, $retorno->getDetalhe(1));

        foreach ($retorno->getDetalhes() as $detalhe) {
            $this->assertInstanceOf(Detalhe::class, $detalhe);
            $this->assertArrayHasKey('numeroDocumento', $detalhe->toArray());
        }
    }

    public function testRetornoFibraCnab400()
    {
        $retorno = \Eduardokum\LaravelBoleto\Cnab\Retorno\Factory::make(__DIR__ . '/files/cnab400/fibra.ret');
        $retorno->processar();

        $this->assertNotNull($retorno->getHeader());
        $this->assertNotNull($retorno->getDetalhes());
        $this->assertNotNull($retorno->getTrailer());

        $this->assertEquals('Banco Fibra S.A.', $retorno->getBancoNome());
        $this->assertEquals('224', $retorno->getCodigoBanco());

        $this->assertInstanceOf(Collection::class, $retorno->getDetalhes());
        $this->assertInstanceOf(Detalhe::class, $retorno->getDetalhe(1));

        foreach ($retorno->getDetalhes() as $detalhe) {
            $this->assertInstanceOf(Detalhe::class, $detalhe);
            $this->assertArrayHasKey('numeroDocumento', $detalhe->toArray());
        }
    }

    public function testRetornoInterCnab400()
    {
        $retorno = \Eduardokum\LaravelBoleto\Cnab\Retorno\Factory::make(__DIR__ . '/files/cnab400/inter.ret');
        $retorno->processar();

        $this->assertNotNull($retorno->getHeader());
        $this->assertNotNull($retorno->getDetalhes());
        $this->assertNotNull($retorno->getTrailer());

        $this->assertEquals('Banco Inter S.A.', $retorno->getBancoNome());
        $this->assertEquals('077', $retorno->getCodigoBanco());

        $this->assertInstanceOf(Collection::class, $retorno->getDetalhes());
        $this->assertInstanceOf(Detalhe::class, $retorno->getDetalhe(1));

        foreach ($retorno->getDetalhes() as $detalhe) {
            $this->assertInstanceOf(Detalhe::class, $detalhe);
            $this->assertArrayHasKey('numeroDocumento', $detalhe->toArray());
        }
    }

    public function testRetornoPineCnab400()
    {
        $retorno = \Eduardokum\LaravelBoleto\Cnab\Retorno\Factory::make(__DIR__ . '/files/cnab400/pine.ret');
        $retorno->processar();

        $this->assertNotNull($retorno->getHeader());
        $this->assertNotNull($retorno->getDetalhes());
        $this->assertNotNull($retorno->getTrailer());

        $this->assertEquals('Banco Pine S.A.', $retorno->getBancoNome());
        $this->assertEquals('643', $retorno->getCodigoBanco());

        $this->assertInstanceOf(Collection::class, $retorno->getDetalhes());
        $this->assertInstanceOf(Detalhe::class, $retorno->getDetalhe(1));

        foreach ($retorno->getDetalhes() as $detalhe) {
            $this->assertInstanceOf(Detalhe::class, $detalhe);
            $this->assertArrayHasKey('numeroDocumento', $detalhe->toArray());
        }
    }

    public function testRetornoOurinvestCnab400()
    {
        $retorno = \Eduardokum\LaravelBoleto\Cnab\Retorno\Factory::make(__DIR__ . '/files/cnab400/ourinvest.ret');
        $retorno->processar();

        $this->assertNotNull($retorno->getHeader());
        $this->assertNotNull($retorno->getDetalhes());
        $this->assertNotNull($retorno->getTrailer());

        $this->assertEquals('Banco Ourinvest', $retorno->getBancoNome());
        $this->assertEquals('712', $retorno->getCodigoBanco());

        $this->assertInstanceOf(Collection::class, $retorno->getDetalhes());
        $this->assertInstanceOf(Detalhe::class, $retorno->getDetalhe(1));

        foreach ($retorno->getDetalhes() as $detalhe) {
            $this->assertInstanceOf(Detalhe::class, $detalhe);
            $this->assertArrayHasKey('numeroDocumento', $detalhe->toArray());
        }
    }

    public function testRetornoPixCnab400()
    {
        $retorno = \Eduardokum\LaravelBoleto\Cnab\Retorno\Factory::make(__DIR__ . '/files/cnab400/santander_pix.ret');
        $retorno->processar();

        $this->assertNotNull($retorno->getHeader());
        $this->assertNotNull($retorno->getDetalhes());
        $this->assertNotNull($retorno->getTrailer());

        $this->assertCount(1, $retorno->getDetalhes());

        $this->assertNotNull($retorno->current()->getId());
        $this->assertNotNull($retorno->current()->getPixLocation());
    }

    public function testRetornoSemPixCnab400()
    {
        $retorno = \Eduardokum\LaravelBoleto\Cnab\Retorno\Factory::make(__DIR__ . '/files/cnab400/santander.ret');
        $retorno->processar();

        $this->assertNotNull($retorno->getHeader());
        $this->assertNotNull($retorno->getDetalhes());
        $this->assertNotNull($retorno->getTrailer());

        $this->assertCount(1, $retorno->getDetalhes());

        $this->assertNull($retorno->current()->getId());
        $this->assertNull($retorno->current()->getPixChave());
        $this->assertNull($retorno->current()->getPixChaveTipo());
    }
}
