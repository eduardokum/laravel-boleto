<?php
/**
 *   Copyright (c) 2016 Eduardo Gusmão
 *
 *   Permission is hereby granted, free of charge, to any person obtaining a
 *   copy of this software and associated documentation files (the "Software"),
 *   to deal in the Software without restriction, including without limitation
 *   the rights to use, copy, modify, merge, publish, distribute, sublicense,
 *   and/or sell copies of the Software, and to permit persons to whom the
 *   Software is furnished to do so, subject to the following conditions:
 *
 *   The above copyright notice and this permission notice shall be included in all
 *   copies or substantial portions of the Software.
 *
 *   THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 *   INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR
 *   PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 *   COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 *   WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR
 *   IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace Eduardokum\LaravelBoleto\Contracts\Boleto;

Use Eduardokum\LaravelBoleto\Contracts\Pessoa as PessoaContract;

interface Boleto
{
    const COD_BANCO_BB = '001';
    const COD_BANCO_SANTANDER = '033';
    const COD_BANCO_CEF = '104';
    const COD_BANCO_BRADESCO = '237';
    const COD_BANCO_ITAU = '341';
    const COD_BANCO_HSBC = '399';

    const STATUS_REGISTRO = 1;
    const STATUS_ALTERACAO = 2;
    const STATUS_BAIXA = 3;

    /**
     * Render PDF.
     *
     * @param bool   $print
     *
     * @return mixed
     */
    public function renderPDF($print = false);

    /**
     * Render PDF.
     *
     * @return mixed
     */
    public function renderHTML();

    /**
     * Return boleto as a Array.
     *
     * @return array
     */
    public function toArray();

    /**
     * @return mixed
     */
    public function getLinhaDigitavel();

    /**
     * @return mixed
     */
    public function getCodigoBarras();

    /**
     * @return PessoaContract
     */
    public function getBeneficiario();

    /**
     * @return mixed
     */
    public function getLogoBase64();

    /**
     * @return mixed
     */
    public function getLogo();

    /**
     * @return mixed
     */
    public function getLogoBancoBase64();

    /**
     * @return mixed
     */
    public function getLogoBanco();

    /**
     * @return mixed
     */
    public function getCodigoBancoComDv();

    /**
     * @return int
     */
    public function getMoeda();

    /**
     * @return mixed
     */
    public function getQuantidade();

    /**
     * @return \Carbon\Carbon
     */
    public function getDataVencimento();

    /**
     * @return \Carbon\Carbon
     */
    public function getDataProcessamento();

    /**
     * @return \Carbon\Carbon
     */
    public function getDataDocumento();

    /**
     * @return mixed
     */
    public function getValor();

    /**
     * @return mixed
     */
    public function getDescontosAbatimentos();

    /**
     * @return mixed
     */
    public function getOutrasDeducoes();

    /**
     * @return mixed
     */
    public function getMulta();

    /**
     * @return mixed
     */
    public function getJuros();

    /**
     * @param bool $default
     *
     * @return mixed
     */
    public function getJurosApos($default = false);

    /**
     * @param bool $default
     *
     * @return mixed
     */
    public function getDiasProtesto($default = false);

    /**
     * @return mixed
     */
    public function getOutrosAcrescimos();

    /**
     * @return mixed
     */
    public function getValorCobrado();

    /**
     * @return mixed
     */
    public function getValorUnitario();

    /**
     * @return PessoaContract
     */
    public function getSacadorAvalista();

    /**
     * @return PessoaContract
     */
    public function getPagador();

    /**
     * @return mixed
     */
    public function getDescricaoDemonstrativo();

    /**
     * @return mixed
     */
    public function getInstrucoes();

    /**
     * @return mixed
     */
    public function getLocalPagamento();

    /**
     * @return mixed
     */
    public function getNumero();

    /**
     * @return mixed
     */
    public function getCip();

    /**
     * @return mixed
     */
    public function getNumeroDocumento();

    /**
     * @return mixed
     */
    public function getAgenciaCodigoBeneficiario();

    /**
     * @return mixed
     */
    public function getNossoNumero();

    /**
     * @return mixed
     */
    public function getNossoNumeroBoleto();

    /**
     * @return mixed
     */
    public function getEspecieDoc();

    /**
     * @return mixed
     */
    public function getEspecieDocCodigo();

    /**
     * @return mixed
     */
    public function getAceite();

    /**
     * @return mixed
     */
    public function getCarteira();

    /**
     * @return mixed
     */
    public function getCarteiraNome();

    /**
     * @return mixed
     */
    public function getUsoBanco();

    /**
     * @return mixed
     */
    public function getStatus();

    /**
     * @return mixed
     */
    public function alterarBoleto();

    /**
     * @return mixed
     */
    public function baixarBoleto();

}