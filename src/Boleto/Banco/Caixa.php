<?php
/**
 *   Copyright (c) 2016 Eduardo Gusmão
 *
 *   Permission is hereby granted, free of charge, to any person obtaining a
 *   copy of this software and associated documentation files (the "Software"),
 *   to deal in the Software without restriction, including without limitation
 *   the rights to use, copy, modify, merge, publish, distribute, sublicense,
 *   and/or sell copies of the Software, and to permit persons to whom the
 *   Software is furnished to do so, subject to the following conditions:
 *
 *   The above copyright notice and this permission notice shall be included in all
 *   copies or substantial portions of the Software.
 *
 *   THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 *   INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR
 *   PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 *   COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 *   WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR
 *   IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace Eduardokum\LaravelBoleto\Boleto\Banco;

use Eduardokum\LaravelBoleto\Boleto\AbstractBoleto;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;
use Eduardokum\LaravelBoleto\Util;

class Caixa  extends AbstractBoleto implements BoletoContract
{
    /**
     * Código do banco
     * @var string
     */
    protected $codigoBanco = self::COD_BANCO_CEF;
    /**
     * Define as carteiras disponíveis para este banco
     * @var array
     */
    protected $carteiras = ['RG'];
    /**
     * Espécie do documento, coódigo para remessa
     * @var string
     */
    protected $especiesCodigo = [
        'DM' => '01',
        'NP' => '02',
        'DS' => '03',
        'NS' => '05',
        'LC' => '06',
    ];
    /**
     * Método que valida se o banco tem todos os campos obrigadotorios preenchidos
     */
    public function isValid()
    {
        if(
            empty($this->numero) ||
            empty($this->agencia) ||
            empty($this->conta) ||
            empty($this->carteira)
        )
        {
            return false;
        }
        return true;
    }
    /**
     * Gera o Nosso Número.
     *
     * @throws Exception
     * @return string
     */
    protected function gerarNossoNumero()
    {
        $numero = $this->getNumero();
        $composicao = '1';
        if ($this->getCarteira() == 'SR'){
            $composicao = '2';
        }

        $carteira = $composicao. '4';
        // As 15 próximas posições no nosso número são a critério do beneficiário, utilizando o sequencial
        // Depois, calcula-se o código verificador por módulo 11
        $numero = $carteira.Util::numberFormatGeral($numero, 15);
        return $numero;
    }
    /**
     * Método que retorna o nosso numero usado no boleto. alguns bancos possuem algumas diferenças.
     *
     * @return string
     */
    public function getNossoNumeroBoleto()
    {
        return $this->getNossoNumero() . '-' . Util::modulo11($this->getNossoNumero());
    }
    /**
     * Método para gerar o código da posição de 20 a 44
     *
     * @return string
     * @throws \Exception
     */
    protected function getCampoLivre()
    {
        if ($this->campoLivre) {
            return $this->campoLivre;
        }
        $nossoNumero = Util::numberFormatGeral($this->gerarNossoNumero(), 17);
        $beneficiario = Util::numberFormatGeral($this->getConta(), 6);
        // Código do beneficiário + DV]
        $campoLivre = $beneficiario . Util::modulo11($beneficiario);
        // Sequencia 1 (posições 3-5 NN) + Constante 1 (1 => registrada, 2 => sem registro)
        $carteira = $this->getCarteira();
        if ($carteira == 'SR'){
            $constante = '2';
        } else {
            $constante = '1';
        }
        $campoLivre .= substr($nossoNumero, 2, 3) . $constante;
        // Sequencia 2 (posições 6-8 NN) + Constante 2 (4-Beneficiário)
        $campoLivre .= substr($nossoNumero, 5, 3) . '4';
        // Sequencia 3 (posições 9-17 NN)
        $campoLivre .= substr($nossoNumero, 8, 9);
        // DV do Campo Livre
        $campoLivre .= Util::modulo11($campoLivre);
        return $this->campoLivre = $campoLivre;
    }
}