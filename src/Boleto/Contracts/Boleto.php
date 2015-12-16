<?php
namespace Eduardokum\LaravelBoleto\Boleto\Contracts;

use Carbon\Carbon;

interface Boleto
{

    const COD_BANCO_BB = '001';
    const COD_BANCO_SANTANDER = '033';
    const COD_BANCO_CEF = '104';
    const COD_BANCO_BRADESCO = '237';
    const COD_BANCO_ITAU = '341';
    const COD_BANCO_HSBC = '399';

    ###########################################################################
    ################################## BILLET #################################
    ###########################################################################

    /**
     * Return line for generate bar code.
     *
     * @return mixed
     */
    public function getLinha();

    /**
     * Get bill value.
     *
     * @return mixed
     */
    public function getValor();


    /**
     * Return Bar code.
     *
     * @return mixed
     */
    public function getCodigoBarras();

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
    public function processar();

    /**
     * Return bank code, with and without verification.
     *
     * @param bool $verificacao
     *
     * @return mixed
     */
    public function getBanco($verificacao = false);

    /**
     * Return billet string identification.
     *
     * @return mixed
     */
    public function getIdentificacao();

    /**
     * Return baixa code.
     *
     * @return mixed
     */
    public function getCodigoBaixa();

    /**
     * Return formatted Agency and Account
     *
     * @return mixed
     */
    public function getAgenciaConta();

    /**
     * Return billet identification on our system.
     *
     * @return mixed
     */
    public function getNossoNumero();

    /**
     * Return billet identification on bank.
     *
     * @return mixed
     */
    public function getNumero();

    /**
     * Return billet expiration.
     *
     * @return Carbon
     */
    public function getDataVencimento();

    /**
     * Return billet processing.
     *
     * @return Carbon
     */
    public function getDataProcessamento();

    /**
     * Return billet date.
     *
     * @return Carbon
     */
    public function getDataDocumento();

    /**
     * Return list of demonstratives.
     *
     * @return array
     */
    public function getDemonstrativos();

    /**
     * Return list of instructions.
     *
     * @return array
     */
    public function getInstrucoes();

    /**
     * Return payment local
     *
     * @return mixed
     */
    public function getLocalPagamento();

    /**
     * Return document specie.
     *
     * @return mixed
     */
    public function getEspecieDocumento();


    /**
     * Return document acceptance, S or N
     *
     * @return mixed
     */
    public function getAceite();


    /**
     * Returb book collection.
     *
     * @param bool $descricao
     *
     * @return mixed
     */
    public function getCarteira($descricao = false);

    ###########################################################################
    ################################## ASSIGNOR ###############################
    ###########################################################################

    /**
     * Return assignor identification.
     *
     * @return mixed
     */
    public function getCedenteDocumento();

    /**
     * Return assignor name.
     *
     * @return mixed
     */
    public function getCedenteNome();

    /**
     * Return assignor address.
     *
     * @return mixed
     */
    public function getCedenteEndereco();

    /**
     * Return assignor state and province.
     *
     * @return mixed
     */
    public function getCedenteCidadeUF();

    ###########################################################################
    ################################## DRAWEE #################################
    ###########################################################################

    /**
     * Return drawee identification.
     *
     * @return mixed
     */
    public function getSacadoDocumento();

    /**
     * Return drawee name.
     *
     * @return mixed
     */
    public function getSacadoNome();

    /**
     * Return drawee address.
     *
     * @return mixed
     */
    public function getSacadoEndereco();

    /**
     * Return drawee state and province.
     *
     * @return mixed
     */
    public function getSacadoCidadeUF();

    /**
     * Render billet.
     *
     * @return mixed
     */
    public function render();
}