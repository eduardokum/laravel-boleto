<?php

namespace Eduardokum\LaravelBoleto\Tests\Retorno;

use Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab400\Banco\Bradesco;
use Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab400\Detalhe;
use Eduardokum\LaravelBoleto\Tests\TestCase;
use Illuminate\Support\Collection;

class RetornoCnab400Test extends TestCase
{

    /**
     * @expectedException     \Exception
     */
    public function testRetornoInvalido(){
        new Bradesco([]);
    }

    /**
     * @expectedException     \Exception
     */
    public function testRetornoBancoInvalido(){
        new Bradesco(__DIR__ . '/files/cnab400/retorno_banco_fake.ret');
    }

    /**
     * @expectedException     \Exception
     */
    public function testRetornoServicoInvalido(){
        new Bradesco(__DIR__ . '/files/cnab400/retorno_banco_fake_2.ret');
    }

    public function testRetornoSeekableIterator(){
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

        $this->setExpectedException(\Exception::class);
        $retorno->seek(100);
    }

    public function testRetornoToArray(){
        $retorno = \Eduardokum\LaravelBoleto\Cnab\Retorno\Factory::make(__DIR__ . '/files/cnab400/bradesco.ret');
        $retorno->processar();

        $array = $retorno->toArray();

        $this->assertArrayHasKey('header', $array);
        $this->assertArrayHasKey('trailer', $array);
        $this->assertArrayHasKey('detalhes', $array);
    }

    public function testRetornoOcorrencia(){
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

}