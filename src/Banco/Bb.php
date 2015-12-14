<?php
namespace Eduardokum\LaravelBoleto\Banco;

use Carbon\Carbon;
use Eduardokum\LaravelBoleto\AbstractBoleto;
use Eduardokum\LaravelBoleto\Contracts\Banco\Bb as BbContract;
use Eduardokum\LaravelBoleto\Util;

class Bb  extends AbstractBoleto implements BbContract
{

    public $formatOurNumber;
    public $agreement;
    public $contract;
    protected $formatAgreement;

    public function __construct()
    {
        parent::__construct(self::COD_BANCO_BB);
    }

    public function process()
    {
        if(!in_array($this->getBookCollection(), ['11','12','16','18','17','31','51']))
        {
            throw new \Exception('Carteira inválida, aceito somente {11,12,16,18,17,31,51}');
        }

        if( (is_numeric($this->agreement)) && (strlen($this->agreement) > 1 && strlen($this->agreement) <= 6) ) {
            $this->agreement = Util::numberFormatBilletGeral($this->agreement, 6, 0);
        }

        if( !in_array(strlen($this->agreement), array('6','7','8',6,7,8)) ) {
            throw new \Exception('Formato de convenio inválido, deve possuir {6|7|8} digitos');
        }

        $this->formatAgreement = strlen($this->agreement);

        $this->agencyAccount = sprintf("%s-%s / %s-%s", $this->agency, Util::module11($this->agency), $this->account, Util::module11($this->account));

        $this->generateBarCode();
        $this->generateLine();
    }


    private function generateBarCode()
    {
        $factor     = Util::dueFactor($this->getExpiryDate());
        $amount     = Util::numberFormatBilletValue($this->getAmount(), 10, 0);
        $agency     = Util::numberFormatBilletGeral($this->getAgency(), 4, 0);
        $account    = Util::numberFormatBilletGeral($this->getAccount(), 8, 0);
        $ourNumber  = $this->generateOurNumber();

        $this->barcode = $this->getBank();
        $this->barcode .= $this->numeroMoeda;
        $this->barcode .= $factor;
        $this->barcode .= $amount;

        if($this->formatAgreement == '8') {

            $this->barcode .= '000000';
            $this->barcode .= $this->agreement;
            $this->barcode .= $ourNumber;
            $this->barcode .= $this->getBookCollection();

        } else if ($this->formatAgreement == "7") {

            $this->barcode .= '000000';
            $this->barcode .= $this->agreement;
            $this->barcode .= $ourNumber;
            $this->barcode .= $this->getBookCollection();

        } else if ($this->formatAgreement == "6") {

            $this->barcode .= $this->agreement;
            $this->barcode .= $ourNumber;

            if($this->formatOurNumber == 1) {

                $this->barcode .= $agency;
                $this->barcode .= $account;
                $this->barcode .= $this->getBookCollection();

            } else if($this->formatOurNumber == 2) {

                $this->barcode .= '21';

            } else {
                throw new \Exception('Campo formatOurNumber inválido, deve possuir {1|2}');
            }
        } else {
            throw new \Exception('Formato de convenio inválido, deve possuir {6|7|8} digitos');
        }



        $dv_modulo = 11 - Util::module11($this->barcode, 9, 1);
        $dv = ($dv_modulo == 0 ||$dv_modulo == 10 ||$dv_modulo == 11)?1:$dv_modulo;
        $this->barcode = substr($this->barcode, 0, 4) . $dv . substr($this->barcode, 4);

        return $this->barcode;
    }

    private function generateOurNumber() {

        if($this->formatAgreement == '8') {

            $ourNumber        = Util::numberFormatBilletGeral($this->getNumber(), 9, 0);
            $this->ourNumber  = $this->agreement . $ourNumber . "-" . Util::module11($this-$this->agreement . $ourNumber);

        } else if ($this->formatAgreement == "7") {

            $ourNumber        = Util::numberFormatBilletGeral($this->getNumber(), 10, 0);
            $this->ourNumber  = $this->agreement . $ourNumber . "-" . Util::module11($this->agreement . $ourNumber);
            $this->ourNumber  = $this->agreement . $ourNumber;

        } else if ($this->formatAgreement == "6") {

            if($this->formatOurNumber == 1) {

                $ourNumber        = Util::numberFormatBilletGeral($this->getNumber(), 5, 0);
                $this->ourNumber  = $this->agreement . $ourNumber . "-" . Util::module11($this->agreement . $ourNumber);

            } else if($this->formatOurNumber == 2) {

                $ourNumber        = Util::numberFormatBilletGeral($this->getNumber(), 17, 0);
                $this->ourNumber  = $ourNumber;

            } else {
                throw new \Exception('Campo formatacaoNN inválido, deve possuir {1|2}');
            }
        } else {
            throw new \Exception('Formato de convenio inválido, deve possuir {6|7|8} digitos');
        }

        return $ourNumber;
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

            $campo5 = substr($this->barcode, 5, 14);

            $this->line = "$campo1 $campo2 $campo3 $campo4 $campo5";

            return $this->line;
        } else {
            throw new \Exception('Código de barras não gerado ou inválido');
        }
    }

}