<?php
namespace Eduardokum\LaravelBoleto\Contracts\Boleto;

use Eduardokum\LaravelBoleto\Contracts\Pessoa as PessoaContract;

interface Boleto
{
    const COD_BANCO_BB = '001';
    const COD_BANCO_SANTANDER = '033';
    const COD_BANCO_CEF = '104';
    const COD_BANCO_BRADESCO = '237';
    const COD_BANCO_ITAU = '341';
    const COD_BANCO_HSBC = '399';
    const COD_BANCO_SICREDI = '748';
    const COD_BANCO_BANRISUL = '041';
    const COD_BANCO_BANCOOB = '756';
    const COD_BANCO_BNB = '004';

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
     * @return \Carbon\Carbon
     */
    public function getDataVencimento();

    /**
     * @return \Carbon\Carbon
     */
    public function getDataDesconto();

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
     *
     * @return mixed
     */
    public function getEspecieDocCodigo($default = 99);

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
     * Método onde qualquer boleto deve extender para gerar o código da posição de 20 a 44
     *
     * @param $campoLivre
     *
     * @return array
     */
    static public function parseCampoLivre($campoLivre);


    /**
     * @return mixed
     */
    public function getMostrarEnderecoFichaCompensacao();
}
