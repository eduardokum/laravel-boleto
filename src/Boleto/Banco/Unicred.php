<?php

namespace Eduardokum\LaravelBoleto\Boleto\Banco;


use Eduardokum\LaravelBoleto\Boleto\AbstractBoleto;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;
use Eduardokum\LaravelBoleto\Util;


class Unicred extends AbstractBoleto implements BoletoContract
{

    protected $codigoBanco = self::COD_BANCO_UNICRED;

    /**
     * Método onde o Boleto deverá gerar o Nosso Número.
     *
     * @return string
     */
    protected function gerarNossoNumero()
    {
        $quantidadeCaracteresNossoNumero = 11;
        $variavelParaOCalculo = Util::numberFormatGeral($this->numeroDocumento, 10);
        $constanteParaCalculo = '3298765432';
        $soma = 0;

        for ($contador = 0; $contador < 10; $contador++) {
            $soma += $variavelParaOCalculo[$contador] * $constanteParaCalculo[$contador];
        }

        $restoDivisao = $soma % 11;

        if ($restoDivisao == 1 || $restoDivisao == 0)
            $digitoVerificador = 0;
        else
            $digitoVerificador = 11 - $restoDivisao;

        return Util::numberFormatGeral($this->numeroDocumento . $digitoVerificador, $quantidadeCaracteresNossoNumero);
    }


    /**
     * Método onde qualquer boleto deve extender para gerar o código da posição de 20 a 44
     *
     * @return string
     */
    protected function getCampoLivre()
    {

        if ($this->campoLivre) {
            return $this->campoLivre;
        }

        $campoLivre = Util::numberFormatGeral($this->getCodigoBanco(), 3);
        $campoLivre .= Util::numberFormatGeral($this->getMoeda(), 1);
        $valorPosicao5A9 = $this->getAgencia() .
        $this->getConta()
//        $campoLivre .= Util::numberFormatGeral($this->getAgencia(), 5);
//        $campoLivre .= Util::numberFormatGeral($this->getConta(), 5);
//        $campoLivre .= CalculoDV::itauContaCorrente($this->getAgencia(), $this->getConta());
//        $campoLivre .= '000';

        return $this->campoLivre = $campoLivre;
    }

    /**
     * Método onde qualquer boleto deve extender para gerar o código da posição de 20 a 44
     *
     * @param $campoLivre
     *
     * @return array
     */
    static public function parseCampoLivre($campoLivre)
    {
        // TODO: Implement parseCampoLivre() method.
    }
}