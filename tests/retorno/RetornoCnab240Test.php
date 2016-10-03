<?php

namespace Cnab400\Tests;

class RetornoCnab240Test extends \PHPUnit_Framework_TestCase
{
    public function testRetornoSantanderCnab240()
    {
        $retorno = \Eduardokum\LaravelBoleto\Cnab\Retorno\Factory::make('files/cnab240/santander.ret');
        $retorno->processar();

        $this->assertNotNull($retorno->getHeader());
        $this->assertNotNull($retorno->getHeaderLote());
        $this->assertNotNull($retorno->getDetalhes());
        $this->assertNotNull($retorno->getTrailerLote());
        $this->assertNotNull($retorno->getTrailerArquivo());

        $this->assertEquals('Banco Santander (Brasil) S.A.', $retorno->getBancoNome());
        $this->assertEquals('033', $retorno->getCodigoBanco());

    }

}