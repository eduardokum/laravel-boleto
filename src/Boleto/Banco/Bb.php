<?php
namespace Eduardokum\LaravelBoleto\Boleto\Banco;

use Eduardokum\LaravelBoleto\Boleto\AbstractBoleto;
use Eduardokum\LaravelBoleto\Boleto\Contracts\Banco\Bb as BbContract;
use Eduardokum\LaravelBoleto\Util;

class Bb  extends AbstractBoleto implements BbContract
{

    public $nossoNumeroFormato;
    public $convenio;
    public $contrato;
    protected $convenioFormato;

    public function __construct()
    {
        parent::__construct(self::COD_BANCO_BB);
    }

    public function preProcessamento()
    {
        if(!in_array($this->getCarteira(), ['11','12','16','18','17','31','51']))
        {
            throw new \Exception('Carteira inválida, aceito somente {11,12,16,18,17,31,51}');
        }

        if( (is_numeric($this->convenio)) && (strlen($this->convenio) > 1 && strlen($this->convenio) <= 6) ) {
            $this->convenio = Util::numberFormatGeral($this->convenio, 6, 0);
        }

        if( !in_array(strlen($this->convenio), array('6','7','8',6,7,8)) ) {
            throw new \Exception('Formato de convenio inválido, deve possuir {6|7|8} digitos');
        }

        $this->convenioFormato = strlen($this->convenio);

        $this->agenciaConta = sprintf("%s-%s / %s-%s", $this->getAgencia(), Util::modulo11($this->getAgencia()), $this->getConta(), Util::modulo11($this->getConta()));
    }


    protected function gerarCodigoBarras()
    {
        $nossoNumero  = $this->gerarNossoNumero();

        $this->codigoBarras = $this->getBanco();
        $this->codigoBarras .= $this->numeroMoeda;
        $this->codigoBarras .= Util::fatorVencimento($this->getDataVencimento());
        $this->codigoBarras .= Util::numberFormatValue($this->getValor(), 10, 0);

        if($this->convenioFormato == '8') {

            $this->codigoBarras .= '000000';
            $this->codigoBarras .= $this->convenio;
            $this->codigoBarras .= $nossoNumero;
            $this->codigoBarras .= $this->getCarteira();

        } else if ($this->convenioFormato == "7") {

            $this->codigoBarras .= '000000';
            $this->codigoBarras .= $this->convenio;
            $this->codigoBarras .= $nossoNumero;
            $this->codigoBarras .= $this->getCarteira();

        } else if ($this->convenioFormato == "6") {

            $this->codigoBarras .= $this->convenio;
            $this->codigoBarras .= $nossoNumero;

            if($this->nossoNumeroFormato == 1) {

                $this->codigoBarras .= Util::numberFormatGeral($this->getAgencia(), 4, 0);
                $this->codigoBarras .= Util::numberFormatGeral($this->getConta(), 8, 0);
                $this->codigoBarras .= $this->getCarteira();

            } else if($this->nossoNumeroFormato == 2) {

                $this->codigoBarras .= '21';

            } else {
                throw new \Exception('Campo nossoNumeroFormato inválido, deve possuir {1|2}');
            }
        } else {
            throw new \Exception('Formato de convenio inválido, deve possuir {6|7|8} digitos');
        }

        $dv_modulo = 11 - Util::modulo11($this->codigoBarras, 9, 1);
        $dv = ($dv_modulo == 0 ||$dv_modulo == 10 ||$dv_modulo == 11)?1:$dv_modulo;
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

            $campo5 = substr($this->codigoBarras, 5, 14);

            $this->linha = "$campo1 $campo2 $campo3 $campo4 $campo5";

            return $this->linha;
        } else {
            throw new \Exception('Código de barras não gerado ou inválido');
        }
    }

    protected function gerarNossoNumero() {

        if($this->convenioFormato == '8') {

            $nossoNumero        = Util::numberFormatGeral($this->getNumero(), 9, 0);
            $this->nossoNumero  = $this->convenio . $nossoNumero . "-" . Util::modulo11($this-$this->convenio . $nossoNumero);

        } else if ($this->convenioFormato == "7") {

            $nossoNumero        = Util::numberFormatGeral($this->getNumero(), 10, 0);
            $this->nossoNumero  = $this->convenio . $nossoNumero . "-" . Util::modulo11($this->convenio . $nossoNumero);
            $this->nossoNumero  = $this->convenio . $nossoNumero;

        } else if ($this->convenioFormato == "6") {

            if($this->nossoNumeroFormato == 1) {

                $nossoNumero        = Util::numberFormatGeral($this->getNumero(), 5, 0);
                $this->nossoNumero  = $this->convenio . $nossoNumero . "-" . Util::modulo11($this->convenio . $nossoNumero);

            } else if($this->nossoNumeroFormato == 2) {

                $nossoNumero        = Util::numberFormatGeral($this->getNumero(), 17, 0);
                $this->nossoNumero  = $nossoNumero;

            } else {
                throw new \Exception('Campo formatacaoNN inválido, deve possuir {1|2}');
            }
        } else {
            throw new \Exception('Formato de convenio inválido, deve possuir {6|7|8} digitos');
        }

        return $nossoNumero;
    }

}