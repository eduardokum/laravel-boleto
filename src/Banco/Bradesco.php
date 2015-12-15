<?php
namespace Eduardokum\LaravelBoleto\Banco;

use Eduardokum\LaravelBoleto\AbstractBoleto;
use Eduardokum\LaravelBoleto\Contracts\Banco\Bradesco as BradescoContract;
use Eduardokum\LaravelBoleto\Util;

class Bradesco  extends AbstractBoleto implements BradescoContract
{

    public function __construct()
    {
        parent::__construct(self::COD_BANCO_BRADESCO);
    }


    public function preProcessamento()
    {
        if(!in_array($this->getCarteira(), ['06','09','16','19','21','22', '6','9']))
        {
            throw new \Exception('Carteira inválida, aceito somente {06,09,16,19,21,22}');
        }
        $this->carteira = sprintf('%02s',$this->getCarteira());

        $this->localPagamento = 'Pagável Preferencialmente em qualquer Agência Bradesco';
        $this->agenciaConta = sprintf('%s-%s %s-%s', $this->getAgencia(), Util::modulo11($this->getAgencia()), $this->getConta(), Util::modulo11($this->getConta()));
    }


    protected function gerarCodigoBarras()
    {
        $this->codigoBarras = $this->getBanco();
        $this->codigoBarras .= $this->numeroMoeda;
        $this->codigoBarras .= Util::fatorVencimento($this->getDataVencimento());
        $this->codigoBarras .= Util::numberFormatValue($this->getValor(), 10, 0);
        $this->codigoBarras .= Util::numberFormatGeral($this->getAgencia(),4,0);
        $this->codigoBarras .= Util::numberFormatGeral($this->getCarteira(),2,0);
        $this->codigoBarras .= $this->gerarNossoNumero();
        $this->codigoBarras .= Util::numberFormatGeral($this->getConta(),7,0);
        $this->codigoBarras .= '0';

        $r = Util::modulo11($this->codigoBarras, 9, 1);
        $dv = ($r == 0 || $r == 1 || $r == 10)?1:(11 - $r);
        $this->codigoBarras = substr($this->codigoBarras, 0, 4) . $dv . substr($this->codigoBarras, 4);

        return $this->codigoBarras;
    }

    protected function gerarLinha()
    {
        if(strlen($this->codigoBarras) == 44) {
            $campo1 = substr($this->codigoBarras, 0, 4) . substr($this->codigoBarras, 19, 5);
            $campo1 = $campo1 . Util::modulo10($campo1);
            $campo1 = substr($campo1, 0, 5) . '.' . substr($campo1, 5, 5);

            $campo2 = substr($this->codigoBarras, 24, 10);
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
            throw new \Exception('Código de barras não gerado ou inválido');
        }
    }

    private function gerarNossoNumero() {
        $nossoNumero = Util::numberFormatGeral($this->getNumero(), 11, 0);
        $dv = Util::modulo11($nossoNumero, 7, 0, 'P');
        $this->nossoNumero = $this->getCarteira() . '/' . $nossoNumero.'-'.$dv;

        return $nossoNumero;
    }

}