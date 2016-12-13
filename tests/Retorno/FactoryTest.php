<?php

namespace Eduardokum\LaravelBoleto\Tests\Retorno;

use Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab400\Detalhe;
use Eduardokum\LaravelBoleto\Tests\TestCase;
use Illuminate\Support\Collection;

class FactoryTest extends TestCase
{
    /**
     * @expectedException     \Exception
     */
    public function testCriarEmBranco(){
        $retorno = \Eduardokum\LaravelBoleto\Cnab\Retorno\Factory::make('');
        $retorno->processar();
    }

    /**
     * @expectedException     \Exception
     */
    public function testCriarComRemessa(){
        $retorno = \Eduardokum\LaravelBoleto\Cnab\Retorno\Factory::make(__DIR__ . '/files/cnab400/remessa.txt');
        $retorno->processar();
    }

    /**
     * @expectedException     \Exception
     */
    public function testCriarComPathQueNaoExiste(){
        $retorno = \Eduardokum\LaravelBoleto\Cnab\Retorno\Factory::make(__DIR__ . '/files/cnab400/naoexiste.txt');
        $retorno->processar();
    }

    /**
     * @expectedException     \Exception
     */
    public function testCriarComRetornoBancoNaoExiste(){
        $retorno = \Eduardokum\LaravelBoleto\Cnab\Retorno\Factory::make(__DIR__ . '/files/cnab400/retorno_banco_fake.ret');
        $retorno->processar();
    }

    public function testCriarComFile()
    {
        $retorno = \Eduardokum\LaravelBoleto\Cnab\Retorno\Factory::make(__DIR__ . '/files/cnab400/bradesco.ret');
        $retorno->processar();
    }

    public function testCriarComString()
    {
        $retorno = \Eduardokum\LaravelBoleto\Cnab\Retorno\Factory::make(file_get_contents(__DIR__ . '/files/cnab400/bradesco.ret'));
        $retorno->processar();
    }
}