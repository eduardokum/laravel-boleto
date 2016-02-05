<?php
namespace Eduardokum\LaravelBoleto\Boleto\Banco;

use Eduardokum\LaravelBoleto\Boleto\AbstractBoleto;
use Eduardokum\LaravelBoleto\Boleto\Contracts\Banco\Hsbc as HsbcContract;
use Eduardokum\LaravelBoleto\Util;

class Hsbc  extends AbstractBoleto implements HsbcContract
{

    public $cedenteCodigo;
    public $range;

    public function __construct()
    {
        parent::__construct(self::COD_BANCO_HSBC);
    }

    protected function preProcessamento()
    {
        $this->localPagamento = 'Pagar preferencialmente em agência do Hsbc';

        $this->carteira = Util::lower($this->getCarteira());

        if(!in_array($this->getCarteira(), ['1', '3', 'cnr','csb']))
        {
            throw new \Exception('Carteira inválida, aceito somente {cnr,csb}');
        }

        if($this->getCarteira() == '1')
        {
            $this->carteira = 'csb';
        }
        if($this->getCarteira() == '3')
        {
            $this->carteira = 'cnr';
        }

        if($this->getCarteira() == 'csb') {
            $this->especieDocumento = (!$this->especieDocumento)?'PD':$this->especieDocumento;
        }
    }

    protected function gerarCodigoBarras()
    {
        if($this->getCarteira() == 'cnr')
        {
            $this->agenciaConta = sprintf('%s %s', $this->getAgencia(), $this->cedenteCodigo);

            $this->codigoBarras = $this->getBanco();
            $this->codigoBarras .= $this->numeroMoeda;
            $this->codigoBarras .= Util::fatorVencimento($this->getDataVencimento());
            $this->codigoBarras .= Util::numberFormatValue($this->getValor(), 10, 0);
            $this->codigoBarras .= Util::numberFormatGeral($this->cedenteCodigo, 7, 0);
            $this->codigoBarras .= $this->geraNossoNumero();
            $this->codigoBarras .= Util::dataJuliano($this->getDataVencimento());
            $this->codigoBarras .= 2;
        }

        if($this->carteira == 'csb' )
        {
            $this->agenciaConta = sprintf('%s-%s', $this->getAgencia(), $this->getAgencia().$this->getConta());

            $this->codigoBarras = $this->getBanco();
            $this->codigoBarras .= $this->numeroMoeda;
            $this->codigoBarras .= Util::fatorVencimento($this->getDataVencimento());
            $this->codigoBarras .= Util::numberFormatValue($this->getValor(), 10, 0);
            $this->codigoBarras .= $this->geraNossoNumero();
            $this->codigoBarras .= Util::numberFormatGeral($this->getAgencia(), 4, 0) . Util::numberFormatGeral($this->getConta(), 7, 0);
            $this->codigoBarras .= '00';
            $this->codigoBarras .= '1';
        }

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

            $campo2 = substr($this->codigoBarras, 24, 2) . substr($this->codigoBarras, 26, 8);
            $campo2 = $campo2 . Util::modulo10($campo2);
            $campo2 = substr($campo2, 0, 5) . '.' . substr($campo2, 5, 6);

            $campo3 = substr($this->codigoBarras, 34, 5) . substr($this->codigoBarras, 39, 4) . substr($this->codigoBarras, 43, 1);
            $campo3 = $campo3 . Util::modulo10($campo3);
            $campo3 = substr($campo3, 0, 5) . '.' . substr($campo3, 5, 6);

            $campo4 = substr($this->codigoBarras, 4, 1);

            $campo5 = substr($this->codigoBarras, 5, 4) . substr($this->codigoBarras, 9, 10);

            $this->linha = "$campo1 $campo2 $campo3 $campo4 $campo5";

            return $this->linha;
        } else {
            throw new Exception('Código de barras não gerado ou inválido');
        }
    }


    private function geraNossoNumero() {

        if($this->carteira == 'cnr') {
            if( $this->getNumero() && $this->cedenteCodigo && $this->getDataVencimento() ) {

                $nossonumero = Util::numberFormatGeral($this->getNumero(), 13, 0);
                $nossonumero .= Util::modulo11Reverso( $nossonumero ) . '4';
                $nossonumero .= Util::modulo11Reverso( ( $nossonumero + $this->cedenteCodigo + $this->getDataVencimento()->format('dmy') ) );
                $this->nossoNumero = $nossonumero;
                return Util::numberFormatGeral($this->numero, 13, 0);

            } else {
                throw new Exception('Todos os parâmetros devem ser informados {numero,contaCorrente,vencimento}');
            }
        }
        if($this->carteira == 'csb') {
            if( $this->range && $this->getNumero() ) {

                $nossonumero = Util::numberFormatGeral($this->range, 5, 0) . Util::numberFormatGeral($this->numero, 5, 0);
                $nossonumero .= Util::modulo11($nossonumero,7);
                $this->nossoNumero = $nossonumero;
                return $nossonumero;

            } else {
                throw new Exception('Todos os parâmetros devem ser informados {numero,range}');
            }
        }
    }

}