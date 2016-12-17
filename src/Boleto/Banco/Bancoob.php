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

class Bancoob extends AbstractBoleto implements BoletoContract
{
    const BANCOBB_CONST_NOSSO_NUMERO = "3197";
    /**
     * Código do banco
     * @var string
     */
    protected $codigoBanco = self::COD_BANCO_BANCOOB;
    /**
     * Define as carteiras disponíveis para este banco
     * @var array
     */
    protected $carteiras = array('1','3');
    /**
     * Espécie do documento, coódigo para remessa
     * @var string
     */
    protected $especiesCodigo = [
        'DM' => '01',
        'NP' => '02',
        'DS' => '12',
    ];
    /**
     * Defgine o numero da variação da carteira.
     * @var string
     */
    protected $variacao_carteira;
    /**
     * Define o número da variação da carteira, para saber quando utilizar o nosso numero de 17 posições.
     *
     * @param string $variacao_carteira
     * @return BancoDoBrasil
     */
    public function setVariacaoCarteira($variacao_carteira)
    {
        $this->variacao_carteira = (int) $variacao_carteira;
        return $this;
    }
    /**
     * Retorna o número da variacao de carteira
     *
     * @return string
     */
    public function getVariacaoCarteira()
    {
        return $this->variacao_carteira;
    }
    /**
     * Método que valida se o banco tem todos os campos obrigadotorios preenchidos
     */
    public function isValid()
    {
        if(
            empty($this->numero) ||
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
     * @throws \Exception
     * @return string
     */
    protected function gerarNossoNumero()
    {
        $agencia = $this->getAgencia();
        $conta = $this->getConta().$this->getContaDv();
        $numero_boleto = $this->getNumero();

        $numero = Util::numberFormatGeral($agencia, 4).Util::numberFormatGeral($conta, 10).Util::numberFormatGeral($numero_boleto, 7);

        return $numero;
    }
    /**
     * Método que retorna o nosso numero usado no boleto. alguns bancos possuem algumas diferenças.
     *
     * @return string
     */
    public function getNossoNumeroBoleto()
    {
        $numero = $this->getNossoNumero();
        $constante = str_repeat(self::BANCOBB_CONST_NOSSO_NUMERO, 6);
        $soma = 0;

        for ($i=0; $i < strlen($numero); $i++) {
            if ((int)$numero[$i] > 0) {
                $soma += ((int)$numero[$i] * (int)$constante[$i]);
            }
        }

        $resto = $soma % 11;
        $digito_verificador = 0;

        if (($resto != 0) && ($resto != 1)){
            $digito_verificador = 11 - $resto;
        }

        $nosso_numero = $this->getNumero().'-'.$digito_verificador;

        return $nosso_numero;
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

        return $this->gerarNossoNumero();
    }
}