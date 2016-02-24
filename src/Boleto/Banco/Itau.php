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

class Itau extends AbstractBoleto implements BoletoContract
{
    /**
     * Código do banco
     * @var string
     */
    protected $codigoBanco = '341';
    /**
     * Variáveis adicionais.
     * @var array
     */
    public $variaveis_adicionais = [
        'carteira' => '',
    ];
    /**
     * Define as carteiras disponíveis para este banco
     * @var array
     */
    protected $carteiras = [
        '102','103','104','105','106','107','108','109','110','111','112','113',
        '114','115','120','121','122','126','129','131','138','139','140','141',
        '142','143','146','147','150','166','168','169','172','173','174','175',
        '177','178','179','180','195','196','198','210','212','221','280','305',
        '352','354','358','359','364','365','458','459','880','986',
    ];
    /**
     * Espécie do documento, coódigo para remessa
     * @var string
     */
    protected $especiesCodigo = [
        'DM' => '01',
        'NP' => '02',
        'NS' => '03',
        'REC' => '05',
        'CT' => '06',
        'NS' => '07',
        'DS' => '08',
        'LC' => '09',
        'ND' => '13',
        'CDA' => '15',
        'EC' => '16',
        'DS' => '17',
    ];
    /**
     * Campo obrigatório para emissão de boletos com carteira 198 fornecido pelo Banco com 5 dígitos
     * @var int
     */
    protected $codigoCliente;
    /**
     * Dígito verificador da carteira/nosso número para impressão no boleto
     * @var int
     */
    protected $carteiraDv;
    /**
     * Define o código do cliente
     *
     * @param int $codigoCliente
     * @return $this
     */
    public function setCodigoCliente($codigoCliente)
    {
        $this->codigoCliente = $codigoCliente;
        return $this;
    }
    /**
     * Retorna o código do cliente
     *
     * @return int
     */
    public function getCodigoCliente()
    {
        return $this->codigoCliente;
    }
    /**
     * Método que valida se o banco tem todos os campos obrigadotorios preenchidos
     */
    public function isValid()
    {
        if(
            empty($this->numero) ||
            empty($this->agencia) ||
            empty($this->conta) ||
            empty($this->carteira) ||
            (in_array($this->getCarteira(), ['107', '122', '142', '143', '196', '198']) && empty($this->codigoCliente))
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
        $this->getCampoLivre(); // Força o calculo do DV.
        return Util::numberFormatGeral($this->getNumero(), 8) . $this->carteiraDv;
    }
    /**
     * Método que retorna o nosso numero usado no boleto. alguns bancos possuem algumas diferenças.
     *
     * @return string
     */
    public function getNossoNumeroBoleto()
    {
        return $this->getCarteira() . '/' . substr_replace($this->getNossoNumero(), '-', -1, 0);
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

        $numero = Util::numberFormatGeral($this->getNumero(), 8);
        $carteira = Util::numberFormatGeral($this->getCarteira(), 3);
        $agencia = Util::numberFormatGeral($this->getAgencia(), 4);
        $conta = Util::numberFormatGeral($this->getConta(), 5);
        // Carteira 198 - (Nosso Número com 15 posições) - Anexo 5 do manual
        if (in_array($this->getCarteira(), ['107', '122', '142', '143', '196', '198'])) {
            $codigo = $carteira . $numero .
                Util::numberFormatGeral($this->getNumeroDocumento(), 7) .
                Util::numberFormatGeral($this->getCodigoCliente(), 5);
            // Define o DV da carteira para a view
            $this->carteiraDv = $modulo = Util::modulo10($codigo);
            return $this->campoLivre = $codigo . $modulo . '0';
        }
        // Geração do DAC - Anexo 4 do manual
        if (!in_array($this->getCarteira(), ['126', '131', '146', '150', '168'])) {
            // Define o DV da carteira para a view
            $this->carteiraDv = $dvAgContaCarteira = Util::modulo10($agencia . $conta . $carteira . $numero);
        } else {
            // Define o DV da carteira para a view
            $this->carteiraDv = $dvAgContaCarteira = Util::modulo10($carteira . $numero);
        }
        // Módulo 10 Agência/Conta
        $dvAgConta = Util::modulo10($agencia . $conta);
        return $this->campoLivre = $carteira . $numero . $dvAgContaCarteira . $agencia . $conta . $dvAgConta . '000';
    }
}