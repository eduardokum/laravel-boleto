<?php

namespace PhalconBoleto\Boleto\Banco;

use Eduardokum\LaravelBoleto\Boleto\AbstractBoleto;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;
use Eduardokum\LaravelBoleto\Util;

class Banrisul extends AbstractBoleto implements BoletoContract
{
    /**
     * Código do banco
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
    protected $carteiras = ['1','2','3','4','5','6','7','8','C','D','E','F','H','I','K','M','N','R','S','X'];

    /**
     * Espécie do documento, coódigo para remessa
     * CD => Cobranca Direta
     * CE => Cobranca Escritural
     * CCB => Cobranca Credenciada Banrisul
     * TT => Titulos de Terceiros
     * @var string
     */
    protected $especiesCodigo = [
        'DM' => '01',
        'NP' => '02',
        'NS' => '03',
        'CD' => '04',
        'CE' => '06',
        'CCB' => '08',
        'TT' => '09',
    ];

    /**
     * Método que valida se o banco tem todos os campos obrigadotorios preenchidos
     */
    public function isValid()
    {


        if(
            $this->numero == '' ||
            $this->agencia == '' ||
            $this->conta == '' ||
            $this->carteira == ''
        )
        {
            return false;
        }
        return true;
    }

    /**
     * Gerar nosso número
     * @return string
     */
    protected function gerarNossoNumero()
    {
        $nossoNumero = Util::numberFormatGeral($this->getNumero(),8).'-'.Util::duploDigitoBanrisul(Util::numberFormatGeral($this->getNumero(),8));
        return $nossoNumero;
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
        $this->campoLivre .= $this->getConta();

        // Nosso numero => 33 a 40 | Valor: dinâmico(00000000) ´8´
        $this->campoLivre .= Util::numberFormatGeral($this->getNumero(),8);

        // Constante    => 41 - 42 | Valor: 40(Constante)
        $this->campoLivre .= '40';

        // Duplo digito => 43 - 44 | Valor: calculado(00) ´2´
        $this->campoLivre .= Util::duploDigitoBanrisul(Util::onlyNumbers($this->campoLivre));
        return $this->campoLivre;
    }
}