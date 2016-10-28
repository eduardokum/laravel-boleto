<?php

namespace Eduardokum\LaravelBoleto\Contracts\Cnab\Retorno\Cnab240;


interface DetalheSegmentoT
{

    /**
     * @return mixed
     */
    public function getCodigoBancoCompensacao();

    /**
     * @return mixed
     */
    public function getDigitoAgenciaCedente();

    /**
     * @return mixed
     */
    public function getNumeroLoteRetorno();

    /**
     * @return mixed
     */
    public function getTipoRegistro();

    /**
     * @return mixed
     */
    public function getNumeroSequencialRegistroLote();

    /**
     * @return mixed
     */
    public function getCodigoSegmentoRegistroDetalhe();

    /**
     * @return mixed
     */
    public function getAgenciaCedente();

    /**
     * @return mixed
     */
    public function getAgenciaCedenteDigito();

    /**
     * @return mixed
     */
    public function getContaCorrente();
    /**
     * @return mixed
     */
    public function getContaDigito();

    /**
     * @return mixed
     */
    public function getNossoNumero();

    /**
     * @return mixed
     */
    public function getCodigoCarteira();

    /**
     * @return mixed
     */
    public function getIdentificador();

    /**
     * @return mixed
     */
    public function getSeuNumero();

    /**
     * @return mixed
     */
    public function getDataVencimento($format = 'd/m/Y');

    /**
     * @return mixed
     */
    public function getValorTitulo();

    /**
     * @return mixed
     */
    public function getNumeroBancoCobradorRecebedor();

    /**
     * @return mixed
     */
    public function getAgenciaCobradoraRecebedora();

    /**
     * @return mixed
     */
    public function getCodigoMoeda();

    /**
     * @return mixed
     */
    public function getTipoInscriçãoSacado();

    /**
     * @return mixed
     */
    public function getNumeroInscricaoSacado();

    /**
     * @return mixed
     */
    public function getNomeSacado();

    /**
     * @return mixed
     */
    public function getContaCobranca();

    /**
     * @return mixed
     */
    public function getValorTarifa();

    /**
     * @return mixed
     */
    public function getIdentificacaoRejeicao();

}