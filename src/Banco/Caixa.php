<?php
namespace Eduardokum\LaravelBoleto\Banco;

use Eduardokum\LaravelBoleto\AbstractBoleto;
use Eduardokum\LaravelBoleto\Contracts\Banco\Caixa as CaixaContract;
use Eduardokum\LaravelBoleto\Util;

class Caixa  extends AbstractBoleto implements CaixaContract
{

    const CEF_TIPO_POS16 = 'pos16';
    const CEF_TIPO_POS11 = 'pos11';

    private $carteiraDesc = array(
        11 => 'CS',
        12 => 'CR',
        9  => 'CR',
        14 => 'SR',
        82 => 'SR'
    );

    public $tipo;
    public $cedenteCodigo;

    public function __construct()
    {
        parent::__construct(self::COD_BANCO_CEF);
    }

    public function preProcessamento()
    {
        if (! in_array($this->getCarteira(), ['12', '14'])) {
            throw new \Exception('Carteira inválida, aceito somente {12,14}');
        }

        $this->corrigirCedenteCodigo();

        if ($this->getCarteira() == 12) {
            $this->carteira = 9;
        } else {
            if ($this->getCarteira() == 14) {
                $this->carteira = 82;
            }
        }

        if (strlen($this->cedenteCodigo) != 11) {
            throw new Exception("Codigo do cedente inválido... PPPXXXXXXXX");
        }

        $AAAA = $this->getAgencia();
        $PPP = substr($this->cedenteCodigo, 0, 3);
        $XXXXXXXX = substr($this->cedenteCodigo, 3);
        $DV = Util::modulo11("$AAAA$PPP$XXXXXXXX");

        $this->tipo = (empty($this->tipo)) ? self::CEF_TIPO_POS11 : $this->tipo;

        if ($this->tipo == self::CEF_TIPO_POS16) {

            if ($PPP != '870') {
                throw new Exception("16 posições somente válido para código de operação 870");
            }
            if ($this->getCarteira() != '82') {
                throw new Exception("Válido para esse tipo de boleto somente carteira sem registro");
            }
        }

        $this->agenciaConta = "$AAAA.$PPP.$XXXXXXXX-$DV";
        $this->localPagamento = 'Preferencialmente nas casas lotéricas até o valor limite';
    }


    protected function gerarCodigoBarras()
    {
        if( $this->tipo == self::CEF_TIPO_POS11 ) {

            $this->codigoBarras = $this->getBanco();
            $this->codigoBarras .= $this->numeroMoeda;
            $this->codigoBarras .= Util::fatorVencimento($this->getDataVencimento());
            $this->codigoBarras .= Util::numberFormatValue($this->getValor(), 10, 0);
            $this->codigoBarras .= $this->gerarNossoNumero();
            $this->codigoBarras .= Util::numberFormatGeral($this->getAgencia(),4,0);
            $this->codigoBarras .= Util::numberFormatGeral($this->cedenteCodigo,11,0);

        } else if($this->tipo == self::CEF_TIPO_POS16 ) {

            $this->codigoBarras = $this->getBanco();
            $this->codigoBarras .= $this->numeroMoeda;
            $this->codigoBarras .= Util::fatorVencimento($this->getDataVencimento());
            $this->codigoBarras .= Util::numberFormatValue($this->getValor(), 10, 0);
            $this->codigoBarras .= substr($this->cedenteCodigo, -5);
            $this->codigoBarras .= $this->getAgencia();
            $this->codigoBarras .= 87;
            $this->codigoBarras .= $this->gerarNossoNumero();

        } else {
            throw new Exception("Tipo do bloqueto inválido");
        }

        $r = Util::modulo11($this->codigoBarras, 9, 1);
        $dv = ($r == 0 || $r == 1 || $r == 10)?1:(11 - $r);
        $this->codigoBarras = substr($this->codigoBarras, 0, 4) . $dv . substr($this->codigoBarras, 4);

        $this->carteira = $this->carteiraDes[$this->getCarteira()];

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
        if( $this->tipo == self::CEF_TIPO_POS11 ) {

            $tNN  = 10-strlen($this->getCarteira());
            $nossoNumero = substr($this->getNumero(), ($tNN*-1) );
            $nossoNumero = $this->getCarteira().Util::numberFormatGeral($nossoNumero, $tNN, '0');
            $d = 11 - (Util::modulo11($nossoNumero, 9, 1));
            $dv = ($d==10||$d==11)?'0':$d;
            $nossoNumero = $nossoNumero;
            $this->nossoNumero = $nossoNumero.'-'.$dv;
            return $nossoNumero;

        } else if($this->tipo == self::CEF_TIPO_POS16 ) {

            $nossoNumero = "8".Util::numberFormatGeral(substr($this->numero,-14), 14, 0);
            $d = 11 - (Util::modulo11($nossoNumero, 9, 1));
            $dv = ($d>9)?'0':$d;
            $nossoNumero = $nossoNumero;
            $this->nossoNumero = $nossoNumero.'-'.$dv;
            return $nossoNumero;
        }
    }

    private function corrigirCedenteCodigo()
    {
        $_4first = substr($this->cedenteCodigo, 0, 4);
        $agencia = Util::numberFormatGeral($this->getAgencia(), 4, '0');
        if ($_4first != $agencia) {
            if (strlen($this->cedenteCodigo) == 12) {
                $this->cedenteCodigo = substr($this->cedenteCodigo, 0, 11);
            }
        } else {
            $this->cedenteCodigo = substr($this->cedenteCodigo, 4, 11);
        }
    }

}