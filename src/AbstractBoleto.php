<?php
namespace Eduardokum\LaravelBoleto;

use Carbon\Carbon;
use Eduardokum\LaravelBoleto\Render\Pdf;

class AbstractBoleto
{

    const COD_BANCO_BB = '001';
    const COD_BANCO_SANTANDER = '033';
    const COD_BANCO_CEF = '104';
    const COD_BANCO_BRADESCO = '237';
    const COD_BANCO_ITAU = '341';
    const COD_BANCO_HSBC = '399';

    protected $bank;

    public $logo;
    public $agency;
    public $account;
    public $bookCollection;
    public $bookCollectionVariation;
    public $number;

    public $identification;
    public $documentSpecie;
    public $acceptance = 'N';
    public $date;
    public $processingDate;
    public $amount;
    public $expiryDate;
    public $numeroMoeda = 9;
    public $ourNumber;
    public $assignorIdentification;
    public $assignorName;
    public $assignorAddress;
    public $assignorStateProvince;
    public $draweeIdentification;
    public $draweeName;
    public $draweeAddress;
    public $draweeStateProvince;

    protected $demonstratives = [];
    protected $instructions = [];
    protected $agencyAccount;
    protected $line;
    protected $barcode;
    protected $paymentLocal = 'Pagável em qualquer Banco até o vencimento';

    /**
     * AbstractBoleto constructor.
     *
     * @param $bank
     */
    public function __construct($bank)
    {
        $this->bank = $bank;
        $this->processingDate = Carbon::now();
    }

    public function getAmount()
    {
        return $this->amount;
    }

    public function getLogo()
    {
        return $this->logo ? $this->logo : "http://dummyimage.com/300x70/f5/0.png&text=Sem+Logo";
    }

    public function getBank($withVerification = false)
    {
        if($withVerification)
        {
            return sprintf('%s-%s', $this->bank, Util::module11($this->bank));
        }
        return $this->bank;
    }

    public function getIdentification()
    {
        $this->identification;
    }

    public function getAgencyAccount()
    {
        return $this->agencyAccount;
    }

    public function getAgency()
    {
        return $this->agency;
    }

    public function getAccount()
    {
        return $this->account;
    }

    public function getOurNumber()
    {
        return $this->ourNumber;
    }

    public function getNumber()
    {
        return $this->number;
    }

    public function getLine()
    {
        return $this->line;
    }

    public function getBarCode()
    {
        return $this->barcode;
    }

    public function getExpiryDate()
    {
        return is_string($this->expiryDate) ? Carbon::createFromFormat('Y-m-d', $this->expiryDate) : $this->expiryDate;
    }

    public function getProcessingDate()
    {
        return is_string($this->processingDate) ? Carbon::createFromFormat('Y-m-d', $this->processingDate) : $this->processingDate;
    }

    public function getDate()
    {
        return is_string($this->date) ? Carbon::createFromFormat('Y-m-d', $this->date) : $this->date;
    }

    public function getDemonstratives()
    {
        return $this->demonstratives;
    }

    public function getInstructions()
    {
        return $this->instructions;
    }

    public function getPaymentLocal()
    {
        return $this->paymentLocal;
    }

    public function getDocumentSpecie()
    {
        return $this->documentSpecie;
    }

    public function getAcceptance()
    {
        return is_bool($this->acceptance) || is_numeric($this->acceptance) ? ($this->acceptance ? 'S' : 'N') : $this->acceptance;
    }

    public function getBookCollection($withDescription = false)
    {
        return $this->bookCollection;
    }

    public function getAssignorIdentification()
    {
        return $this->assignorIdentification;
    }

    public function getAssignorName()
    {
        return $this->assignorName;
    }

    public function getAssignorAddress()
    {
        return $this->assignorAddress;
    }

    public function getAssignorStateProvince()
    {
        return $this->assignorStateProvince;
    }

    public function getDraweeName()
    {
        return $this->draweeName;
    }

    public function getDraweeIdentification()
    {
        return $this->draweeIdentification;
    }

    public function getDraweeAddress()
    {
        return $this->draweeAddress;
    }

    public function getDraweeStateProvince()
    {
        return $this->draweeStateProvince;
    }

    /**
     * Add a demonstrative string to a billet.
     *
     * @param $demonstrative
     *
     * @throws \Exception
     */
    public function addDemonstrative($demonstrative)
    {
        if(count($demonstrative) >= 5 )
        {
            throw new \Exception('Too many demonstratives. Max of 5');
        }

        $this->demonstrative[] = $demonstrative;
    }

    /**
     * Add a instruction string to a billet.
     *
     * @param $instruction
     *
     * @throws \Exception
     */
    public function addInstruction($instruction)
    {
        if(count($instruction) >= 5 )
        {
            throw new \Exception('Too many instructions. Max of 5');
        }

        $this->instructions[] = $instruction;
    }

    public function render()
    {
        $pdf = new Pdf();
        $pdf->addBoleto($this);
        return $pdf->gerarBoleto($pdf::OUTPUT_STANDARD, true);
    }

}