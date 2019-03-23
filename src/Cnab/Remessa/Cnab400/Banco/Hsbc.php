<?php
namespace Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400\Banco;

use Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400\AbstractRemessa;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;
use Eduardokum\LaravelBoleto\Contracts\Cnab\Remessa as RemessaContract;
use Eduardokum\LaravelBoleto\Util;

class Hsbc extends AbstractRemessa implements RemessaContract
{
    const ESPECIE_DUPLICATA = '01';
    const ESPECIE_NOTA_PROMISSORIA = '02';
    const ESPECIE_NOTA_SEGURO = '03';
    const ESPECIE_RECIBO = '05';
    const ESPECIE_DUPLICATA_SERVICO = '10';
    const ESPECIE_COMPL_BOLETO_CLIENTE = '08';
    const ESPECIE_EMISSAO_TOTAL_BANCO = '09';
    const ESPECIE_EMISSAO_TOTAL_CLIENTE = '98';

    const OCORRENCIA_REMESSA = '01';
    const OCORRENCIA_PEDIDO_BAIXA = '02';
    const OCORRENCIA_CONCESSAO_ABATIMENTO = '04';
    const OCORRENCIA_CANCELAMENTO_ABATIMENTO = '05';
    const OCORRENCIA_PRORROGACAO_VENC = '06';
    const OCORRENCIA_ALT_CONTROLE_PARTICIPANTE = '07';
    const OCORRENCIA_ALT_SEUNUMERO = '08';
    const OCORRENCIA_PROTESTAR = '09';
    const OCORRENCIA_SUSTAR_PROTESTO = '10';
    const OCORRENCIA_NAO_COBRAR_JUROS = '11';
    const OCORRENCIA_CONCEDER_DESC_PAGTO_ATE = '13';
    const OCORRENCIA_CANC_CONDICAO_DESC = '14';
    const OCORRENCIA_CANC_DESC_DIARIO = '15';
    const OCORRENCIA_ALT_VENCIMENTO = '48';
    const OCORRENCIA_ALT_DIAS_CARTORIO = '49';
    const OCORRENCIA_INC_SACADO_ELETRONICO = '50';
    const OCORRENCIA_EXC_SACADO_ELETRONICO = '51';
    const OCORRENCIA_PROTESTO_FALIMENTARES = '57';

    const INSTRUCAO_SEM = '00';
    const INSTRUCAO_MULTA_PERC_XX_APOS_XX = '15';
    const INSTRUCAO_MULTA_PERC_XX_APOS_MAXIMO = '16';
    const INSTRUCAO_MULTA_VALOR_APOS_VENC = '19';
    const INSTRUCAO_COBRAR_JUROS_VENC_7 = '20';
    const INSTRUCAO_MULTA_VALOR_XX_APOS_VENC = '22';
    const INSTRUCAO_NAO_RECEBER_APOS_VENC = '23';
    const INSTRUCAO_MULTA_VALOR_XX_VENC = '24';
    const INSTRUCAO_JUROS_SOMENTE_APOS_XX = '29';
    const INSTRUCAO_CONCEDER_ABATIMENTO = '34';
    const INSTRUCAO_APOS_VENC_MULTA_10 = '36';
    const INSTRUCAO_CONCEDER_DESC_APOS_VENC = '40';
    const INSTRUCAO_NAO_RECEBER_ANTES_VENC = '42';
    const INSTRUCAO_INSTRUCAO_APOS_VENC_MULTA_20_MORA_1 = '53';
    const INSTRUCAO_NAO_RECEBER_ANTES_VENC_10_DEPOIS = '56';
    const INSTRUCAO_ABATIMENTO_DESC = '65';
    const INSTRUCAO_PROTESTO_APOS_VENC = '67';
    const INSTRUCAO_APOS_VENC_MULTA_2 = '68';
    const INSTRUCAO_NAO_RECEBER_APOS_VENC_XX = '71';
    const INSTRUCAO_NAO_RECEBER_APOS_VENC_XX_UTEIS = '72';
    const INSTRUCAO_MULTA_PERC_XX_APOS_VENC = '73';
    const INSTRUCAO_MULTA_PERC_XX_APOS_VENC_UTEIS = '74';
    const INSTRUCAO_PROTESTAR_XX_VENC = '75';
    const INSTRUCAO_PROTESTAR_XX_VENC_UTEIS = '77';
    const INSTRUCAO_PROTESTAR_XX_VENC_UTEIS_NAO_PAGO = '76';
    const INSTRUCAO_PROTESTAR_XX_VENC_NAO_PAGO = '84';

    public function __construct(array $params = [])
    {
        parent::__construct($params);
        $this->addCampoObrigatorio('contaDv');
    }


    /**
     * Código do banco
     *
     * @var string
     */
    protected $codigoBanco = BoletoContract::COD_BANCO_HSBC;

    /**
     * Define as carteiras disponíveis para cada banco
     *
     * @var array
     */
    protected $carteiras = ['CSB'];

    /**
     * Caracter de fim de linha
     *
     * @var string
     */
    protected $fimLinha = "\r\n";

    /**
     * Retorna o numero da carteira.
     *
     * @return int
     */
    public function getCarteiraNumero()
    {
        /**
         * 1 - Cobrança Simples
         * 3 - Garantias de Operações Quando o cliente optar por trabalhar diretamente nesta carteira de cobrança, o boleto poderá não ser aceito para compor a carteira ‘03’, desta forma ele será registrado na carteira ‘00 – Cobrança Simples’, sendo que no arquivo retorno será informado a carteira em que o boleto foi acatado.
         * 4 - Desconto Suspenso (somente para arquivo Retorno )
         */
        return 1;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    protected function header()
    {
        $this->iniciaHeader();

        $this->add(1, 1, '0');
        $this->add(2, 2, '1');
        $this->add(3, 9, 'REMESSA');
        $this->add(10, 11, '01');
        $this->add(12, 26, Util::formatCnab('X', 'COBRANCA', 15));
        $this->add(27, 27, 0);
        $this->add(28, 31, Util::formatCnab('9', $this->getAgencia(), 4));
        $this->add(32, 33, 55);
        $this->add(34, 37, Util::formatCnab('9', $this->getConta(), 4));
        $this->add(38, 43, Util::formatCnab('9', $this->getConta(), 6));
        $this->add(44, 44, Util::formatCnab('9', $this->getContaDv(), 1));
        $this->add(45, 46, '');
        $this->add(47, 76, Util::formatCnab('X', $this->getBeneficiario()->getNome(), 30));
        $this->add(77, 79, $this->getCodigoBanco());
        $this->add(80, 94, Util::formatCnab('X', 'HSBC', 15));
        $this->add(95, 100, $this->getDataRemessa('dmy'));
        $this->add(101, 105, '01600');
        $this->add(106, 108, 'BPI');
        $this->add(109, 110, '');
        $this->add(111, 117, 'LANCV08');
        $this->add(118, 394, '');
        $this->add(395, 400, Util::formatCnab('9', 1, 6));

        return $this;
    }

    /**
     * @param BoletoContract $boleto
     *
     * @return mixed|void
     * @throws \Exception
     */
    public function addBoleto(BoletoContract $boleto)
    {
        $this->boletos[] = $boleto;
        $this->iniciaDetalhe();

        $this->add(1, 1, '1');
        $this->add(2, 3, strlen(Util::onlyNumbers($this->getBeneficiario()->getDocumento())) == 14 ? '02' : '01');
        $this->add(4, 17, Util::formatCnab('9L', $this->getBeneficiario()->getDocumento(), 14));
        $this->add(18, 18, 0);
        $this->add(19, 22, Util::formatCnab('9', $this->getAgencia(), 4));
        $this->add(23, 24, '55');
        $this->add(25, 28, Util::formatCnab('9L', $this->getAgencia(), 4));
        $this->add(29, 34, Util::formatCnab('9L', $this->getConta(), 6));
        $this->add(35, 35, Util::formatCnab('9L', $this->getContaDv(), 1));
        $this->add(36, 37, '');
        $this->add(38, 62, Util::formatCnab('X', $boleto->getNumeroControle(), 25)); // numero de controle
        $this->add(63, 73, Util::formatCnab('9', $boleto->getNossoNumero(), 11));
        $this->add(74, 79, '000000');
        $this->add(80, 90, Util::formatCnab('9', 0, 11, 2));
        $this->add(91, 96, '000000');
        $this->add(97, 107, Util::formatCnab('9', 0, 11, 2));
        $this->add(108, 108, $this->getCarteiraNumero());
        $this->add(109, 110, self::OCORRENCIA_REMESSA); // REGISTRO
        if ($boleto->getStatus() == $boleto::STATUS_BAIXA) {
            $this->add(109, 110, self::OCORRENCIA_PEDIDO_BAIXA); // BAIXA
        }
        if ($boleto->getStatus() == $boleto::STATUS_ALTERACAO) {
            throw new \Exception('HSBC não suporta alteração geral, use o comando `comandarInstrucao` no boleto para enviar uma solicitação especifica');
        }
        if ($boleto->getStatus() == $boleto::STATUS_ALTERACAO_DATA) {
            $this->add(109, 110, self::OCORRENCIA_ALT_VENCIMENTO);
        }
        if ($boleto->getStatus() == $boleto::STATUS_CUSTOM) {
            $this->add(109, 110, sprintf('%2.02s', $boleto->getComando()));
        }
        $this->add(111, 120, Util::formatCnab('X', $boleto->getNumeroDocumento(), 10));
        $this->add(121, 126, $boleto->getDataVencimento()->format('dmy'));
        $this->add(127, 139, Util::formatCnab('9', $boleto->getValor(), 13, 2));
        $this->add(140, 142, $this->getCodigoBanco());
        $this->add(143, 147, '00000');
        $this->add(148, 149, $boleto->getEspecieDocCodigo());
        $this->add(150, 150, $boleto->getAceite());
        $this->add(151, 156, $boleto->getDataDocumento()->format('dmy'));
        $this->add(157, 158, self::INSTRUCAO_SEM);
        $this->add(159, 160, self::INSTRUCAO_SEM);
        if ($boleto->getDiasProtesto() > 0) {
            $this->add(157, 158, self::INSTRUCAO_PROTESTAR_XX_VENC_UTEIS);
        }
        if ($boleto->getMulta() > 0) {
            $this->add(159, 160, self::INSTRUCAO_MULTA_PERC_XX_APOS_VENC_UTEIS);
            $this->add(206, 211, '');
            $this->add(206, 215, Util::formatCnab('9', $boleto->getMulta(), 2, 2));
            $this->add(206, 218, Util::formatCnab('9', $boleto->getJurosApos(), 3));
        } else {
            $this->add(206, 218, Util::formatCnab('9', 0, 13, 2));
        }
        $this->add(161, 173, Util::formatCnab('9', $boleto->getMoraDia(), 13, 2));
        $this->add(174, 179, $boleto->getDesconto() > 0 ? $boleto->getDataDesconto()->format('dmy') : '000000');
        $this->add(180, 192, Util::formatCnab('9', $boleto->getDesconto(), 13, 2));
        $this->add(193, 205, Util::formatCnab('9', 0, 13, 2));
        $this->add(219, 220, strlen(Util::onlyNumbers($boleto->getPagador()->getDocumento())) == 14 ? '02' : '01');
        $this->add(221, 234, Util::formatCnab('9L', $boleto->getPagador()->getDocumento(), 14));
        $this->add(235, 274, Util::formatCnab('X', $boleto->getPagador()->getNome(), 40));
        $this->add(275, 312, Util::formatCnab('X', $boleto->getPagador()->getEndereco(), 38));
        $this->add(313, 314, '00');
        $this->add(315, 326, Util::formatCnab('X', $boleto->getPagador()->getBairro(), 12));
        $this->add(327, 334, Util::formatCnab('9L', $boleto->getPagador()->getCep(), 8));
        $this->add(335, 349, Util::formatCnab('X', $boleto->getPagador()->getCidade(), 15));
        $this->add(350, 351, Util::formatCnab('X', $boleto->getPagador()->getUf(), 2));
        $this->add(352, 390, Util::formatCnab('X', $boleto->getSacadorAvalista() ? $boleto->getSacadorAvalista()->getNome() : '', 39));
        $this->add(391, 391, '');
        $this->add(392, 393, Util::formatCnab('9', $boleto->getDiasProtesto('  '), 2));
        $this->add(394, 394, $boleto->getMoeda());
        $this->add(395, 400, Util::formatCnab('9', $this->iRegistros + 1, 6));
    }

    /**
     * @return $this
     * @throws \Exception
     */
    protected function trailer()
    {
        $this->iniciaTrailer();

        $this->add(1, 1, '9');
        $this->add(2, 394, '');
        $this->add(395, 400, Util::formatCnab('9', $this->getCount(), 6));

        return $this;
    }
}
