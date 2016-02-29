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

class Santander  extends AbstractBoleto implements BoletoContract
{
    /**
     * Código do banco
     * @var string
     */
    protected $codigoBanco = self::COD_BANCO_SANTANDER;
    /**
     * Define as carteiras disponíveis para este banco
     * @var array
     */
    protected $carteiras = ['101', '201'];
    /**
     * Espécie do documento, coódigo para remessa
     * @var string
     */
    protected $especiesCodigo = [
        'DM' => '01',
        'NP' => '02',
        'NS' => '03',
        'REC' => '05',
        'NS' => '06',
        'LC' => '07',
    ];
    /**
     * Define os nomes das carteiras para exibição no boleto
     * @var array
     */
    protected $carteirasNomes = ['101' => 'Cobrança Simples ECR', '102' => 'Cobrança Simples CSR'];
    /**
     * Define o valor do IOS - Seguradoras (Se 7% informar 7. Limitado a 9%) - Demais clientes usar 0 (zero)
     * @var int
     */
    protected $ios = 0;
    /**
     * Variaveis adicionais.
     *
     * @var array
     */
    public $variaveis_adicionais = [
        'esconde_uso_banco' => true,
    ];
    /**
     * Define o código da carteira (Com ou sem registro)
     *
     * @param string $carteira
     * @return AbstractBoleto
     * @throws \Exception
     */
    public function setCarteira($carteira)
    {
        switch($carteira)
        {
            case '1':
            case '5':
                $carteira = '101';
                break;
            case '4':
                $carteira = '102';
                break;
        }
        return parent::setCarteira($carteira);
    }
    /**
     * Define o valor do IOS
     *
     * @param int $ios
     */
    public function setIos($ios)
    {
        $this->ios = $ios;
    }
    /**
     * Retorna o atual valor do IOS
     *
     * @return int
     */
    public function getIos()
    {
        return $this->ios;
    }
    /**
     * Método que valida se o banco tem todos os campos obrigadotorios preenchidos
     */
    public function isValid()
    {
        if(
            empty($this->numero) ||
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
     * @return string
     */
    protected function gerarNossoNumero()
    {
        return Util::numberFormatGeral($this->getNumero(), 13);
    }
    /**
     * Método para gerar o código da posição de 20 a 44
     *
     * @return string
     */
    protected function getCampoLivre()
    {
        if ($this->campoLivre) {
            return $this->campoLivre;
        }
        return $this->campoLivre = '9' . Util::numberFormatGeral($this->getConta(), 7) .
        $this->getNossoNumero() .
        Util::numberFormatGeral($this->getIos(), 1) .
        Util::numberFormatGeral($this->getCarteira(), 3);
    }
}