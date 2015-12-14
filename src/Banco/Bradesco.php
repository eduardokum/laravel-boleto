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


    public function process()
    {
        if(!in_array($this->getBookCollection(), ['06','09','16','19','21','22', '6','9']))
        {
            throw new \Exception('Carteira inválida, aceito somente {06,09,16,19,21,22}');
        }
        $this->bookCollection = sprintf('%02s',$this->getBookCollection());

        $this->paymentLocal = 'Pagável Preferencialmente em qualquer Agência Bradesco';
        $this->agencyAccount = sprintf('%s-%s %s-%s', $this->agency, Util::module11($this->agency), $this->account, Util::module11($this->account));

        $this->generateBarCode();
        $this->generateLine();
    }


    private function generateBarCode()
    {
        $this->barcode = $this->getBank();
        $this->barcode .= $this->numeroMoeda;
        $this->barcode .= Util::dueFactor($this->getExpiryDate());
        $this->barcode .= Util::numberFormatBilletValue($this->getAmount(), 10, 0);
        $this->barcode .= Util::numberFormatBilletGeral($this->getAgency(),4,0);
        $this->barcode .= Util::numberFormatBilletGeral($this->getBookCollection(),2,0);
        $this->barcode .= $this->generateOurNumber();
        $this->barcode .= Util::numberFormatBilletGeral($this->getAccount(),7,0);
        $this->barcode .= '0';

        $r = Util::module11($this->barcode, 9, 1);
        $dv = ($r == 0 || $r == 1 || $r == 10)?1:(11 - $r);
        $this->barcode = substr($this->barcode, 0, 4) . $dv . substr($this->barcode, 4);

        return $this->barcode;
    }

    private function generateOurNumber() {
        $ourNumber = Util::numberFormatBilletGeral($this->getNumber(), 11, 0);
        $dv = Util::module11($ourNumber, 7, 0, 'P');
        $this->ourNumber = $this->getBookCollection() . '/' . $ourNumber.'-'.$dv;

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

            $campo5 = substr($this->barcode, 5, 4) . substr($this->barcode, 9, 10);

            $this->line = "$campo1 $campo2 $campo3 $campo4 $campo5";
        } else {
            throw new \Exception('Código de barras não gerado ou inválido');
        }
    }

}