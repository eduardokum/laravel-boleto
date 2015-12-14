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

    public $billetType;
    public $assignorCode;

    public function __construct()
    {
        parent::__construct(self::COD_BANCO_CEF);
    }

    public function process()
    {
        if (! in_array($this->getBookCollection(), ['12', '14'])) {
            throw new \Exception('Carteira inválida, aceito somente {12,14}');
        }

        $this->fixAssignorCode();

        if ($this->getBookCollection() == 12) {
            $this->bookCollection = 9;
        } else {
            if ($this->getBookCollection() == 14) {
                $this->bookCollection = 82;
            }
        }

        if (strlen($this->assignorCode) != 11) {
            throw new Exception("Codigo do cedente inválido... PPPXXXXXXXX");
        }

        $AAAA = $this->getAgency();
        $PPP = substr($this->assignorCode, 0, 3);
        $XXXXXXXX = substr($this->assignorCode, 3);
        $DV = Util::module11("$AAAA$PPP$XXXXXXXX");


        $this->billetType = (empty($this->billetType)) ? self::CEF_TIPO_POS11 : $this->billetType;

        if ($this->billetType == self::CEF_TIPO_POS16) {

            if ($PPP != '870') {
                throw new Exception("16 posições somente válido para código de operação 870");
            }
            if ($this->getBookCollection() != '82') {
                throw new Exception("Válido para esse tipo de boleto somente carteira sem registro");
            }
        }

        $this->agencyAccount = "$AAAA.$PPP.$XXXXXXXX-$DV";
        $this->paymentLocal = 'Preferencialmente nas casas lotéricas até o valor limite';

        $this->generateBarCode();
        $this->generateLine();

        $this->bookCollection = $this->carteiraDesc[$this->getBookCollection()];
    }


    private function generateBarCode()
    {
        $factor          = Util::dueFactor($this->getExpiryDate());
        $amount          = Util::numberFormatBilletValue($this->getAmount(), 10, 0);

        if( $this->billetType == self::CEF_TIPO_POS11 ) {

            $this->barcode = $this->getBank();
            $this->barcode .= $this->numeroMoeda;
            $this->barcode .= $factor;
            $this->barcode .= $amount;
            $this->barcode .= $this->generateOurNumber();
            $this->barcode .= Util::numberFormatBilletGeral($this->getAgency(),4,0);
            $this->barcode .= Util::numberFormatBilletGeral($this->assignorCode,11,0);

        } else if($this->billetType == self::CEF_TIPO_POS16 ) {

            $this->barcode = $this->getBank();
            $this->barcode .= $this->numeroMoeda;
            $this->barcode .= $factor;
            $this->barcode .= $amount;
            $this->barcode .= substr($this->assignorCode, -5);
            $this->barcode .= $this->getAgency();
            $this->barcode .= 87;
            $this->barcode .= $this->generateOurNumber();

        } else {
            throw new Exception("Tipo do bloqueto inválido");
        }

        $r = Util::module11($this->barcode, 9, 1);
        $dv = ($r == 0 || $r == 1 || $r == 10)?1:(11 - $r);
        $this->barcode = substr($this->barcode, 0, 4) . $dv . substr($this->barcode, 4);

        return $this->barcode;
    }

    private function generateOurNumber() {
        if( $this->billetType == self::CEF_TIPO_POS11 ) {

            $tNN  = 10-strlen($this->getBookCollection());
            $ourNumber = substr($this->getNumber(), ($tNN*-1) );
            $ourNumber = $this->getBookCollection().Util::numberFormatBilletGeral($ourNumber, $tNN, '0');
            $d = 11 - (Util::module11($ourNumber, 9, 1));
            $dv = ($d==10||$d==11)?'0':$d;
            $ourNumber = $ourNumber;
            $this->ourNumber = $ourNumber.'-'.$dv;
            return $ourNumber;

        } else if($this->billetType == self::CEF_TIPO_POS16 ) {

            $ourNumber = "8".Util::numberFormatBilletGeral(substr($this->numero,-14), 14, 0);
            $d = 11 - (Util::module11($ourNumber, 9, 1));
            $dv = ($d>9)?'0':$d;
            $ourNumber = $ourNumber;
            $this->ourNumber = $ourNumber.'-'.$dv;
            return $ourNumber;
        }
    }

    private function generateLine()
    {
        if(strlen($this->barcode) == 44) {
            $campo1 = substr($this->barcode, 0, 4) . substr($this->barcode, 19, 5);
            $campo1 = $campo1 . Util::module10($campo1);
            $campo1 = substr($campo1, 0, 5) . '.' . substr($campo1, 5, 5);

            $campo2 = substr($this->barcode, 24, 10);
            $campo2 = $campo2 . Util::module10($campo2);
            $campo2 = substr($campo2, 0, 5) . '.' . substr($campo2, 5, 6);

            $campo3 = substr($this->barcode, 34, 10);
            $campo3 = $campo3 . Util::module10($campo3);
            $campo3 = substr($campo3, 0, 5) . '.' . substr($campo3, 5, 6);

            $campo4 = substr($this->barcode, 4, 1);

            $campo5 = substr($this->barcode, 5, 4) . substr($this->barcode, 9, 10);

            $this->line = "$campo1 $campo2 $campo3 $campo4 $campo5";
        } else {
            throw new \Exception('Código de barras não gerado ou inválido');
        }
    }

    private function fixAssignorCode()
    {
        $_4first = substr($this->assignorCode, 0, 4);
        $agency = Util::numberFormatBilletGeral($this->getAgency(), 4, '0');
        if ($_4first != $agency) {
            if (strlen($this->assignorCode) == 12) {
                $this->assignorCode = substr($this->assignorCode, 0, 11);
            }
        } else {
            $this->assignorCode = substr($this->assignorCode, 4, 11);
        }
    }

}