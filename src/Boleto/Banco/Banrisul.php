<?php

namespace Eduardokum\LaravelBoleto\Boleto\Banco;

use Eduardokum\LaravelBoleto\Boleto\AbstractBoleto;
use Eduardokum\LaravelBoleto\CalculoDV;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;
use Eduardokum\LaravelBoleto\Util;

class Banrisul extends AbstractBoleto implements BoletoContract
{
    /**
     * Código do banco
     *
     * @var string
     */
    protected $codigoBanco = self::COD_BANCO_BANRISUL;

    /**
     * Define as carteiras disponíveis para este banco
     * 1 -> Cobrança Simples
     * 3 -> Cobrança Caucionada
     * 4 -> Cobrança em IGPM
     * 5 -> Cobrança Caucionada CGB Especial
     * 6 -> Cobrança Simples Seguradora
     * 7 -> Cobrança em UFIR
     * 8 -> Cobrança em IDTR
     * C -> Cobrança Vinculada
     * D -> Cobrança CSB
     * E -> Cobrança Caucionada Câmbio
     * F -> Cobrança Vendor
     * H -> Cobrança Caucionada Dólar
     * I -> Cobrança Caucionada Compror
     * K -> Cobrança Simples INCC-M
     * M -> Cobrança Partilhada
     * N -> Capital de Giro CGB ICM
     * R -> Desconto de Duplicata
     * S -> Vendor Eletrônico – Valor Final (Corrigido)
     * X -> Vendor BDL – Valor Inicial (Valor da NF)
     *
     * @var array
     */
    protected $carteiras = ['1', '2', '3', '4', '5', '6', '7', '8', 'C', 'D', 'E', 'F', 'H', 'I', 'K', 'M', 'N', 'R', 'S', 'X'];

    /**
     * Seta dias para baixa automática
     *
     * @param int $baixaAutomatica
     *
     * @return $this
     * @throws \Exception
     */
    public function setDiasBaixaAutomatica($baixaAutomatica)
    {
        if ($this->getDiasProtesto() > 0) {
            throw new \Exception('Você deve usar dias de protesto ou dias de baixa, nunca os 2');
        }
        $baixaAutomatica = (int) $baixaAutomatica;
        $this->diasBaixaAutomatica = $baixaAutomatica > 0 ? $baixaAutomatica : 0;
        return $this;
    }

    /**
     * Gerar nosso número
     *
     * @return string
     */
    protected function gerarNossoNumero()
    {
        $numero_boleto = $this->getNumero();
        $nossoNumero = Util::numberFormatGeral($numero_boleto, 8)
            . CalculoDV::banrisulNossoNumero(Util::numberFormatGeral($numero_boleto, 8));
        return $nossoNumero;
    }
    /**
     * Método que retorna o nosso numero usado no boleto. alguns bancos possuem algumas diferenças.
     *
     * @return string
     */
    public function getNossoNumeroBoleto()
    {
        return substr_replace($this->getNossoNumero(), '-', -2, 0);
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

        // Carteira     => 20 - 20 | Valor: 1(Com registro) ou 2(Sem registro)
        $this->campoLivre  = '2';

        // Constante    => 21 - 21 | Valor: 1(Constante)
        $this->campoLivre .= '1';

        // Agencia      => 22 a 25 | Valor: dinâmico(0000) ´4´
        $this->campoLivre .= Util::numberFormatGeral($this->getAgencia(), 4);

        // Cod. Cedente => 26 a 32 | Valor: dinâmico(0000000) ´7´
        $this->campoLivre .= Util::numberFormatGeral($this->getConta(), 7);

        // Nosso numero => 33 a 40 | Valor: dinâmico(00000000) ´8´
        $this->campoLivre .= Util::numberFormatGeral($this->getNumero(), 8);

        // Constante    => 41 - 42 | Valor: 40(Constante)
        $this->campoLivre .= '40';

        // Duplo digito => 43 - 44 | Valor: calculado(00) ´2´
        $this->campoLivre .= CalculoDV::banrisulDuploDigito(Util::onlyNumbers($this->campoLivre));
        return $this->campoLivre;
    }
}
