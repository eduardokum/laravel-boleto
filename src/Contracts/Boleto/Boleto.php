<?php

namespace Eduardokum\LaravelBoleto\Contracts\Boleto;

use Carbon\Carbon;
use Eduardokum\LaravelBoleto\Contracts\Pessoa as PessoaContract;

interface Boleto
{
    const COD_BANCO_BB = '001';
    const COD_BANCO_SANTANDER = '033';
    const COD_BANCO_INTER = '077';
    const COD_BANCO_AILOS = '085';
    const COD_BANCO_CEF = '104';
    const COD_BANCO_CRESOL = '133';
    const COD_BANCO_BTG = '208';
    const COD_BANCO_BRADESCO = '237';
    const COD_BANCO_C6 = '336';
    const COD_BANCO_ITAU = '341';
    const COD_BANCO_HSBC = '399';
    const COD_BANCO_DELCRED = '435';
    const COD_BANCO_SICREDI = '748';
    const COD_BANCO_BANRISUL = '041';
    const COD_BANCO_BANCOOB = '756';
    const COD_BANCO_BNB = '004';
    const COD_BANCO_UNICRED = '136';
    const COD_BANCO_FIBRA = '224';
    const COD_BANCO_RENDIMENTO = '633';
    const COD_BANCO_PINE = '643';
    const COD_BANCO_OURINVEST = '712';
    const STATUS_REGISTRO = 1;
    const STATUS_ALTERACAO = 2;
    const STATUS_BAIXA = 3;
    const STATUS_ALTERACAO_DATA = 4;
    const STATUS_CUSTOM = 99;

    /**
     * Render PDF.
     *
     * @param bool $print
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
    public function getID();

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
    public function getCodigoBanco();

    /**
     * @return mixed
     */
    public function getCodigoBancoComDv();

    /**
     * @return int
     */
    public function getMoeda();

    /**
     * @return Carbon
     */
    public function getDataVencimento();

    /**
     * @return Carbon
     */
    public function getDataVencimentoApos();

    /**
     * @return Carbon
     */
    public function getDataDesconto();

    /**
     * @return Carbon
     */
    public function getDataProcessamento();

    /**
     * @return Carbon
     */
    public function getDataDocumento();

    /**
     * @return mixed
     */
    public function getValor();

    /**
     * @return mixed
     */
    public function getDesconto();

    /**
     * @return mixed
     */
    public function getMulta();

    /**
     * @return mixed
     */
    public function getJuros();

    /**
     * @return mixed
     */
    public function getMoraDia();

    /**
     * @return mixed
     */
    public function getJurosApos();

    /**
     * @param int $default
     *
     * @return mixed
     */
    public function getDiasProtesto($default = 0);

    /**
     * @param int $default
     *
     * @return mixed
     */
    public function getDiasBaixaAutomatica($default = 0);

    /**
     * @return PessoaContract
     */
    public function getSacadorAvalista();

    /**
     * @return PessoaContract
     */
    public function getPagador();

    /**
     * @return array
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
    public function getNumeroDocumento();

    /**
     * @return mixed
     */
    public function getNumeroControle();

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
     * @param int $default
     * @param int $tipo
     *
     * @return mixed
     */
    public function getEspecieDocCodigo($default = 99, $tipo = 240);

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
    public function getChaveNfe();

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

    /**
     * @return mixed
     */
    public function alterarDataDeVencimento();

    /**
     * @param $instrucao
     *
     * @return mixed
     */
    public function comandarInstrucao($instrucao);

    /**
     * @return mixed
     */
    public function getComando();

    /**
     * @return mixed
     */
    public function getPixQrCode();

    /**
     * @return mixed
     */
    public function getPixChave();

    /**
     * @return mixed
     */
    public function getPixChaveTipo();

    /**
     * Método onde qualquer boleto deve extender para gerar o código da posição de 20 a 44
     *
     * @param $campoLivre
     *
     * @return array
     */
    public static function parseCampoLivre($campoLivre);

    /**
     * @return mixed
     */
    public function getMostrarEnderecoFichaCompensacao();

    /**
     * @return bool
     */
    public function imprimeBoleto();
}
