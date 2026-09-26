<?php

namespace Eduardokum\LaravelBoleto\Tests;

use Eduardokum\LaravelBoleto\CalculoDV;

class CalculoDVTest extends TestCase
{
    public function testMod10CheckDigitStaysOneDigitWhenTheSumDividesByTen()
    {
        $this->assertSame(0, CalculoDV::rendimentoNossoNumero('1101', '010', '1010101010'));
        $this->assertSame(0, CalculoDV::pineNossoNumero('1101', '010', '1010101010'));
        $this->assertSame(0, CalculoDV::fibraNossoNumero('1101', '010', '1010101010'));
        $this->assertSame(8, CalculoDV::rendimentoNossoNumero('1000', '000', '0'));
    }
}
