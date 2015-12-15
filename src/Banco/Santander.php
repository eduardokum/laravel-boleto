<?php
namespace Eduardokum\LaravelBoleto\Banco;

use Eduardokum\LaravelBoleto\AbstractBoleto;
use Eduardokum\LaravelBoleto\Contracts\Banco\Santander as SantanderContract;
use Eduardokum\LaravelBoleto\Util;

class Santander  extends AbstractBoleto implements SantanderContract
{

    public $cedenteCodigo;

    private $carteiraDesc = array(
        1   => 'ELETRÔNICA COM REGISTRO',
        3   => 'CAUCIONADA ELETRÔNICA',
        4   => 'COBRANÇA SEM REGISTRO',
        5   => 'RÁPIDA COM REGISTRO',
        6   => 'CAUCIONADA RAPIDA',
        7   => 'DESCONTADA ELETRÔNICA',
        101 => 'COBRANÇA SIMPLES RÁPIDA COM REGISTRO',
        102 => 'COBRANÇA SIMPLES SEM REGISTRO',
    );

    public function __construct()
    {
        parent::__construct(self::COD_BANCO_SANTANDER);
    }

    protected function preProcessamento()
    {
        if(!in_array($this->getCarteira(), ['1','3','4','5','6','7','101','102']))
        {
            throw new \Exception('Carteira inválida, aceito somente {1,3,4,5,6,7,101,102}');
        }

        $this->carteiraDescricao = $this->carteiraDesc[(int)$this->getCarteira()];
        if(in_array($this->getCarteira(), ['1','3','5','6','7','101']) ) {
            $this->carteira = '101';
        } else {
            $this->carteira = '102';
        }

        $this->agenciaConta = sprintf('%s-%s / %s', $this->getAgencia(), Util::modulo11($this->agencia), $this->cedenteCodigo);
        $this->localPagamento = 'Pagar preferencialmente no Grupo Santander Banespa - GC';
    }

    protected function gerarCodigoBarras()
    {
        $this->codigoBarras = $this->banco;
        $this->codigoBarras .= $this->numeroMoeda;
        $this->codigoBarras .= Util::fatorVencimento($this->getDataVencimento());
        $this->codigoBarras .= Util::numberFormatValue($this->getValor(), 10, 0);
        $this->codigoBarras .= $this->numeroMoeda;
        $this->codigoBarras .= Util::numberFormatGeral($this->cedenteCodigo,7,0);
        $this->codigoBarras .= $this->geraNossoNumero();
        $this->codigoBarras .= 0; // Valor IOS
        $this->codigoBarras .= Util::numberFormatGeral($this->getCarteira(),3,0);

        $r = Util::modulo11($this->codigoBarras, 9, 1);
        $dv = ($r == 0 || $r == 1 || $r == 10)?1:(11 - $r);
        $this->codigoBarras = substr($this->codigoBarras, 0, 4) . $dv . substr($this->codigoBarras, 4);

        return $this->codigoBarras;
    }

    protected function gerarLinha()
    {
        if(strlen($this->codigoBarras) == 44) {
            $campo1 = substr($this->codigoBarras, 0, 3) . substr($this->codigoBarras, 3, 1) . substr($this->codigoBarras, 19, 1) . substr($this->codigoBarras, 20, 4);
            $campo1 = $campo1 . Util::modulo10($campo1);
            $campo1 = substr($campo1, 0, 5) . '.' . substr($campo1, 5);

            $campo2 = substr($this->codigoBarras, 24, 10);
            $campo2 = $campo2 . Util::modulo10($campo2);
            $campo2 = substr($campo2, 0, 5) . '.' . substr($campo2, 5);

            $campo3 = substr($this->codigoBarras, 34, 10);
            $campo3 = $campo3 . Util::modulo10($campo3);
            $campo3 = substr($campo3, 0, 5) . '.' . substr($campo3, 5);

            $campo4 = substr($this->codigoBarras, 4, 1);

            $campo5 = substr($this->codigoBarras, 5, 4) . substr($this->codigoBarras, 9, 10);

            $this->linha = "$campo1 $campo2 $campo3 $campo4 $campo5";

            return $this->linha;
        } else {
            throw new Exception('Código de barras não gerado ou inválido');
        }
    }

    private function geraNossoNumero() {
        $nossonumero = '00000';
        $nossonumero .= Util::numberFormatGeral($this->getNumero(),7,0) . Util::modulo11($this->getNumero());
        $this->nossoNumero = Util::numberFormatGeral($this->getNumero(),7,0) . '-' . Util::modulo11($this->getNumero());
        return $nossonumero;
    }


}