<?php

namespace Cnab400\Tests;

class RetornoCnab400Test extends \PHPUnit_Framework_TestCase
{
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

    }

}
