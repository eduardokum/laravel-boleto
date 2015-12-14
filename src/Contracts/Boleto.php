<?php
namespace Eduardokum\LaravelBoleto\Contracts;

use Carbon\Carbon;

interface Boleto
{

    ###########################################################################
    ################################## BILLET #################################
    ###########################################################################

    /**
     * Return line for generate bar code.
     *
     * @return mixed
     */
    public function getLine();

    /**
     * Get bill value.
     *
     * @return mixed
     */
    public function getAmount();


    /**
     * Return Bar code.
     *
     * @return mixed
     */
    public function getBarCode();

    /**
     * Return full path logo.
     *
     * @return mixed
     */
    public function getLogo();

    /**
     * Process billet.
     *
     * @return mixed
     */
    public function process();

    /**
     * Return bank code, with and without verification.
     *
     * @param bool $withVerification
     *
     * @return mixed
     */
    public function getBank($withVerification = false);

    /**
     * Return billet string identification.
     *
     * @return mixed
     */
    public function getIdentification();

    /**
     * Return formatted Agency and Account
     *
     * @return mixed
     */
    public function getAgencyAccount();

    /**
     * Return billet identification on our system.
     *
     * @return mixed
     */
    public function getOurNumber();

    /**
     * Return billet identification on bank.
     *
     * @return mixed
     */
    public function getNumber();

    /**
     * Return billet expiration.
     *
     * @return Carbon
     */
    public function getExpiryDate();

    /**
     * Return billet processing.
     *
     * @return Carbon
     */
    public function getProcessingDate();

    /**
     * Return billet date.
     *
     * @return Carbon
     */
    public function getDate();

    /**
     * Return list of demonstratives.
     *
     * @return array
     */
    public function getDemonstratives();

    /**
     * Return list of instructions.
     *
     * @return array
     */
    public function getInstructions();

    /**
     * Return payment local
     *
     * @return mixed
     */
    public function getPaymentLocal();

    /**
     * Return document specie.
     *
     * @return mixed
     */
    public function getDocumentSpecie();


    /**
     * Return document acceptance, S or N
     *
     * @return mixed
     */
    public function getAcceptance();


    /**
     * Returb book collection.
     *
     * @param bool $withDescription
     *
     * @return mixed
     */
    public function getBookCollection($withDescription = false);

    ###########################################################################
    ################################## ASSIGNOR ###############################
    ###########################################################################

    /**
     * Return assignor identification.
     *
     * @return mixed
     */
    public function getAssignorIdentification();

    /**
     * Return assignor name.
     *
     * @return mixed
     */
    public function getAssignorName();

    /**
     * Return assignor address.
     *
     * @return mixed
     */
    public function getAssignorAddress();

    /**
     * Return assignor state and province.
     *
     * @return mixed
     */
    public function getAssignorStateProvince();

    ###########################################################################
    ################################## DRAWEE #################################
    ###########################################################################

    /**
     * Return drawee identification.
     *
     * @return mixed
     */
    public function getDraweeIdentification();

    /**
     * Return drawee name.
     *
     * @return mixed
     */
    public function getDraweeName();

    /**
     * Return drawee address.
     *
     * @return mixed
     */
    public function getDraweeAddress();

    /**
     * Return drawee state and province.
     *
     * @return mixed
     */
    public function getDraweeStateProvince();

    /**
     * Render billet.
     *
     * @return mixed
     */
    public function render();

}