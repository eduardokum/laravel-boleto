<?php

namespace Eduardokum\LaravelBoleto\Tests;

use Eduardokum\LaravelBoleto\Pessoa;
use Eduardokum\LaravelBoleto\Util;

class UtilTest extends TestCase
{

    public function testIsHeaderRetorno(){
        $this->assertFalse(Util::isHeaderRetorno(''));
        $this->assertFalse(Util::isHeaderRetorno(str_pad('', 400, ' ')));
        $this->assertFalse(Util::isHeaderRetorno(str_pad('', 240, ' ')));
    }

    public function testFuncoesString(){
        $this->assertEquals('ASD', Util::upper('asd'));
        $this->assertEquals('asd', Util::lower('ASD'));
        $this->assertEquals('Asd', Util::upFirst('asd'));
        $this->assertEquals('asd', Util::lettersOnly('123asd'));
        $this->assertEquals('asd', Util::onlyLetters('123asd'));
        $this->assertEquals('asd', Util::numbersNot('123asd'));
        $this->assertEquals('asd', Util::notNumbers('123asd'));
        $this->assertEquals('123', Util::lettersNot('asd123'));
        $this->assertEquals('123', Util::notLetters('asd123'));
        $this->assertEquals('123', Util::numbersOnly('asd123'));
        $this->assertEquals('123', Util::onlyNumbers('asd123'));
        $this->assertEquals('asd123', Util::alphanumberOnly('_*(asd123)*_'));
        $this->assertEquals('asd123', Util::onlyAlphanumber('_*(asd123)*_'));
        $this->assertEquals('AaEeIiOoUu', Util::normalizeChars('ÁáÉéÍiÓóÚú'));
    }

    public function testModulos(){
        $this->assertEquals(7, Util::modulo11('123456789', 2, 9));
        $this->assertEquals(4, Util::modulo11('123456789', 2, 9, 1));
        $this->assertEquals(7, Util::modulo10('123456789'));
    }

    public function testAddRem() {
        $array = array_fill(0, 400, 0);
        Util::adiciona($array, 1, 10, '1234567890');
        $this->assertEquals(sprintf('%-0400s', '1234567890'), implode('', $array));
        $this->assertEquals('1234567890', Util::remove(1, 10, $array));
    }

    /**
     * @expectedException     \Exception
     */
    public function testAddMaiorTamanhoCnab() {
        $array = array_fill(0, 400, 0);
        Util::adiciona($array, 400, 410, '1234567890');
    }

    /**
     * @expectedException     \Exception
     */
    public function testAddFinalMenorInicial() {
        $array = array_fill(0, 400, 0);
        Util::adiciona($array, 300, 290, '1234567890');
    }

    /**
     * @expectedException     \Exception
     */
    public function testAddStringMaiorRange() {
        $array = array_fill(0, 400, 0);
        Util::adiciona($array, 300, 301, '1234567890');
    }

    /**
     * @expectedException     \Exception
     */
    public function testRemMaiorTamanhoCnab() {
        $array = array_fill(0, 400, 0);
        Util::remove(400, 410, $array);
    }

    /**
     * @expectedException     \Exception
     */
    public function testRemFinalMenorInicial() {
        $array = array_fill(0, 400, 0);
        Util::remove(310, 300, $array);
    }

    public function testFormatCnab() {
        $this->assertEquals('0000001234', Util::formatCnab('9', '1234', 10));
        $this->assertEquals('0000001234', Util::formatCnab('9L', '1.2.3.4', 10));
        $this->assertEquals('0000123400', Util::formatCnab('9', '1234', 10, 2));
        $this->assertEquals('ABC       ', Util::formatCnab('X', 'ABC', 10));

        $this->setExpectedException(\Exception::class);
        Util::formatCnab('J', '123', 10);
    }

    public function testPercentuais() {
        $this->assertEquals(0, Util::percent(1000, 0));
        $this->assertEquals(100, Util::percent(1000, 10));
        $this->assertEquals(10, Util::percentOf(1000, 100));
    }

    public function testDatas() {
        $this->assertEquals('7019', Util::fatorVencimento('2016-12-25'));
        $this->assertEquals('2016-12-25', Util::fatorVencimentoBack('7019'));
        $this->assertEquals('3606', Util::dataJuliano('2016-12-25'));
    }

    public function testNumeros() {

        $this->assertEquals(0, Util::nFloat('ABC'));
        $this->assertEquals(0, Util::nFloat(null));
        $this->assertEquals('1000.00', Util::nFloat(1000));
        $this->assertEquals('1000.000', Util::nFloat(1000, 3));
        $this->assertEquals('1,000.000', Util::nFloat(1000, 3, true));
        $this->assertEquals('1,000.123', Util::nFloat(1000.123000000, false, true));
        $this->assertEquals('1,000.123000009', Util::nFloat(1000.123000009, false, true));

        $this->assertEquals('', Util::nReal('ABC'));
        $this->assertEquals('', Util::nReal(null));
        $this->assertEquals('R$ 1.000,00', Util::nReal(1000));
        $this->assertEquals('R$ 1.000,000', Util::nReal(1000, 3));
        $this->assertEquals('1.000,000', Util::nReal(1000, 3, false));
        $this->assertEquals('1.000,123', Util::nReal(1000.123000000, false, false));
        $this->assertEquals('R$ 1.000,123000009', Util::nReal(1000.123000009, false, true));
        $this->assertEquals('R$ 1.000,12300', Util::nReal(1000.123000000, 5, true, true));
        $this->assertEquals('R$ 1.000', Util::nReal(1000, false, true, true));
        $this->assertEquals('R$ 1.000,0', Util::nReal(1000, false, true, false));
    }

    public function testControleArray() {

        $exemplo = [
            'P' => 123, // Pedido 123
            'NF' => 456, // Nota Fiscal 456
            'C' => 99, // Cliente 99
        ];

        $this->assertEquals('P123NF456C99', Util::array2Controle($exemplo));
        $this->assertEquals($exemplo, Util::controle2array('P123NF456C99'));
        $this->assertEquals([123], Util::controle2array(123));
    }

    /**
     * @expectedException     \Exception
     */
    public function testControleArrayMaior25() {
        Util::array2Controle(['ABCDEFG' => 1231231, 'EFGHIJKL' => 1231231]);
    }

    /**
     * @expectedException     \Exception
     */
    public function testControleArrayKeyNumerica() {
        Util::array2Controle([0 => 1]);
    }
}
