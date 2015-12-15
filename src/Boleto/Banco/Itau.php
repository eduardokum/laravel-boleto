<?php
namespace Eduardokum\LaravelBoleto\Boleto\Banco;

use Eduardokum\LaravelBoleto\Boleto\AbstractBoleto;
use Eduardokum\LaravelBoleto\Boleto\Contracts\Banco\Itau as ItauContract;
use Eduardokum\LaravelBoleto\Util;

class Itau extends AbstractBoleto implements ItauContract
{

    public function __construct()
    {
        parent::__construct(self::COD_BANCO_ITAU);
    }

    protected function preProcessamento()
    {
        $this->agenciaConta = sprintf('%s/%s-%s', $this->getAgencia(), $this->getConta(), Util::modulo10($this->getAgencia().$this->getConta()));
        $this->localPagamento = 'Pagável Preferencialmente em qualquer Agência itaú';
        $this->especieDocumento = 'DM';

        if(!in_array($this->getCarteira(), ['109','112','115','175']))
        {
            throw new \Exception('Carteira inválida, aceito somente {109,112,115,175}');
        }
    }

    protected function gerarCodigoBarras()
    {
        $this->codigoBarras = $this->getBanco();
        $this->codigoBarras .= $this->numeroMoeda;
        $this->codigoBarras .= Util::fatorVencimento($this->getDataVencimento());
        $this->codigoBarras .= Util::numberFormatValue($this->getValor(), 10, 0);
        $this->codigoBarras .= Util::numberFormatGeral($this->getCarteira(),3,0);
        $this->codigoBarras .= $this->geraNossoNumero();
        $this->codigoBarras .= Util::numberFormatGeral($this->getAgencia(),4,0);
        $this->codigoBarras .= Util::numberFormatGeral($this->getConta(),5,0);
        $this->codigoBarras .= Util::modulo10($this->getAgencia().$this->getConta());
        $this->codigoBarras .= '000';

        $d = 11 - (Util::modulo11($this->codigoBarras, 9, 1));
        $dv = ($d == 0 || $d == 1 || $d == 10 || $d == 11)?1:$d;
        $this->codigoBarras = substr($this->codigoBarras, 0, 4) . $dv . substr($this->codigoBarras, 4);

        return $this->codigoBarras;
    }

    protected function gerarLinha()
    {
        if(strlen($this->codigoBarras) == 44) {
            $campo1 = substr($this->codigoBarras, 0, 4) . substr($this->codigoBarras, 19, 3) . substr($this->codigoBarras, 22, 2);
            $campo1 = $campo1 . Util::modulo10($campo1);
            $campo1 = substr($campo1, 0, 5) . '.' . substr($campo1, 5, 5);

            $campo2 = substr($this->codigoBarras, 24, 6) . substr($this->codigoBarras, 30, 4);
            $campo2 = $campo2 . Util::modulo10($campo2);
            $campo2 = substr($campo2, 0, 5) . '.' . substr($campo2, 5, 6);

            $campo3 = substr($this->codigoBarras, 34, 10);
            $campo3 = $campo3 . Util::modulo10($campo3);
            $campo3 = substr($campo3, 0, 5) . '.' . substr($campo3, 5, 6);

            $campo4 = substr($this->codigoBarras, 4, 1);

            $campo5 = substr($this->codigoBarras, 5, 4) . substr($this->codigoBarras, 9, 10);

            $this->linha = "$campo1 $campo2 $campo3 $campo4 $campo5";

            return $this->linha;
        } else {
            throw new \Exception('Código de barras não gerado ou inválido'.strlen($this->codigoBarras));
        }
    }

    private function geraNossoNumero() {

        if( $this->getAgencia() && $this->getConta() && $this->getCarteira()  ) {

            $nossonumero    = Util::numberFormatGeral($this->getNumero(), 8, 0);
            $dv             = Util::modulo10($this->getAgencia() . $this->getConta() . $this->getCarteira() . $nossonumero);
            $this->nossoNumero = $this->getCarteira() . '/' . $nossonumero.'-'.$dv;
            return $nossonumero.$dv;

        } else {
            throw new Exception('Todos os parâmetros devem ser informados {numero,contaCorrente,agencia}');
        }
    }

}