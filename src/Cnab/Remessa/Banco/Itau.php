<?php
namespace Eduardokum\LaravelBoleto\Cnab\Remessa\Banco;

use Eduardokum\LaravelBoleto\Cnab\Remessa\AbstractCnab;
use Eduardokum\LaravelBoleto\Cnab\Contracts\Remessa;
use Eduardokum\LaravelBoleto\Cnab\Remessa\Detalhe;
use Eduardokum\LaravelBoleto\Util;

class Itau extends AbstractCnab implements Remessa
{

    const ESPECIE_DUPLICATA = '01';
    const ESPECIE_NOTA_PROMISSORIA = '02';
    const ESPECIE_NOTA_SEGURO = '03';
    const ESPECIE_MENSALIDADE_ESCOLAR = '04';
    const ESPECIE_RECIBO = '05';
    const ESPECIE_CONTRATO = '06';
    const ESPECIE_COSSEGUROS = '07';
    const ESPECIE_DUPLICATA_SERVIÇO = '08';
    const ESPECIE_LETRA_CAMBIO = '09';
    const ESPECIE_NOTA_DEBITOS = '13';
    const ESPECIE_DOCUMENTO_DIVIDA = '15';
    const ESPECIE_ENCARGOS_CONDOMINIAIS = '16';
    const ESPECIE_NOTA_SERVICOS = '17';
    const ESPECIE_DIVERSOS = '99';

    const OCORRENCIA_REMESSA = '01';
    const OCORRENCIA_BAIXA = '02';
    const OCORRENCIA_CONCESSAO_ABATIMENTO = '04';
    const OCORRENCIA_CANC_ABATIMENTO = '05';
    const OCORRENCIA_ALT_VENCIMENTO = '06';
    const OCORRENCIA_ALT_CONTROLE_PARTICIPANTE = '07';
    const OCORRENCIA_ALT_SEUNUMERO = '08';
    const OCORRENCIA_PROTESTAR = '09';
    const OCORRENCIA_NAO_PROTESTAR = '10';
    const OCORRENCIA_PROTESTO_FALIMENTARES = '11';
    const OCORRENCIA_SUSTAR_PROTESTO = '18';
    const OCORRENCIA_EXCL_AVALISTA = '30';
    const OCORRENCIA_ALT_OUTROS_DADOS = '31';
    const OCORRENCIA_BAIXA_PAGO_DIRETAMENTE = '34';
    const OCORRENCIA_CANC_INSTRUCAO = '35';
    const OCORRENCIA_ALT_VENC_SUSTAR_PROTESTO = '37';
    const OCORRENCIA_NAO_CONCORDA_SACADO = '38';
    const OCORRENCIA_DISPENSA_JUROS = '47';

    const INSTRUCAO_DEVOL_VENC_5 = '02';
    const INSTRUCAO_DEVOL_VENC_30 = '03';
    const INSTRUCAO_RECEBER_CONFORME_TITULO = '05';
    const INSTRUCAO_DEVOL_VENC_10 = '06';
    const INSTRUCAO_DEVOL_VENC_15 = '07';
    const INSTRUCAO_DEVOL_VENC_20 = '08';
    const INSTRUCAO_PROTESTAR_VENC_XX_CARTORIO_5 = '09';
    const INSTRUCAO_NAO_PROTESTAR = '10';
    const INSTRUCAO_INSTRUCAO_DEVOL_VENC_25 = '11';
    const INSTRUCAO_DEVOL_VENC_35 = '12';
    const INSTRUCAO_DEVOL_VENC_40 = '13';
    const INSTRUCAO_DEVOL_VENC_45 = '14';
    const INSTRUCAO_DEVOL_VENC_50 = '15';
    const INSTRUCAO_DEVOL_VENC_55 = '16';
    const INSTRUCAO_DEVOL_VENC_60 = '17';
    const INSTRUCAO_DEVOL_VENC_90 = '18';
    const INSTRUCAO_NAO_RECEBER_VENC_05 = '19';
    const INSTRUCAO_NAO_RECEBER_VENC_10 = '20';
    const INSTRUCAO_NAO_RECEBER_VENC_15 = '21';
    const INSTRUCAO_NAO_RECEBER_VENC_20 = '22';
    const INSTRUCAO_NAO_RECEBER_VENC_25 = '23';
    const INSTRUCAO_NAO_RECEBER_VENC_30 = '24';
    const INSTRUCAO_NAO_RECEBER_VENC_35 = '25';
    const INSTRUCAO_NAO_RECEBER_VENC_40 = '26';
    const INSTRUCAO_NAO_RECEBER_VENC_45 = '27';
    const INSTRUCAO_NAO_RECEBER_VENC_50 = '28';
    const INSTRUCAO_NAO_RECEBER_VENC_55 = '29';
    const INSTRUCAO_DESCONTO_DIA = '30';
    const INSTRUCAO_NAO_RECEBER_VENC_60 = '31';
    const INSTRUCAO_NAO_RECEBER_VENC_90 = '32';
    const INSTRUCAO_CONCEDER_ABATIMENTO_VENCIDO = '33';
    const INSTRUCAO_PROTESTAR_VENC_XX = '34';
    const INSTRUCAO_PROTESTAR_VENC_XX_UTEIS = '35';
    const INSTRUCAO_RECEBER_ULT_DIA_MES_VENC = '37';
    const INSTRUCAO_CONCEDER_DESC_VENC = '38';
    const INSTRUCAO_NAO_RECEBER_VENC = '39';
    const INSTRUCAO_CONCEDER_DESC_NOTA_CRED = '40';
    const INSTRUCAO_PROTESTO_FALIMENTARES = '42';
    const INSTRUCAO_SUJEITO_PROTESTO_NAO_VENC = '43';
    const INSTRUCAO_PAGTO_ATRASO_APOS_DDMMAA = '44';
    const INSTRUCAO_DIA_GRACAO = '45';
    const INSTRUCAO_DISPENSAR_JUROS = '47';
    const INSTRUCAO_RECEBER_ANT_QUITADA = '51';
    const INSTRUCAO_PAGTO_SOMENTE_BOLETO_BANCO = '52';
    const INSTRUCAO_VENC_PAGTO_EMPRESA = '54';
    const INSTRUCAO_VALOR_SOMA_MORA = '57';
    const INSTRUCAO_DEVOL_VENC_365 = '58';
    const INSTRUCAO_PAGTO_BANCO = '59';
    const INSTRUCAO_ENTREGUE_PENHOR = '61';
    const INSTRUCAO_TRANSFERIDO = '62';
    const INSTRUCAO_VALOR_PRORATA_10 = '78';
    const INSTRUCAO_JUROS_VENC_15 = '79';
    const INSTRUCAO_PAGTO_CHEQUE = '80';
    const INSTRUCAO_PROTESTAR_VENC_XX_2 = '81';
    const INSTRUCAO_PROTESTAR_VENC_XX_UTEIS_2 = '82';
    const INSTRUCAO_OPERACAO_VENDOR = '83';
    const INSTRUCAO_AG_CEDENTE_APOS_VENC = '84';
    const INSTRUCAO_ANTES_VENC_APOS_15_SEDE = '86';
    const INSTRUCAO_NAO_RECEBER_ANTES_VENC = '88';
    const INSTRUCAO_VENC_QLQ_AG = '90';
    const INSTRUCAO_INSTRUCAO_NAO_RECEBER_VENC_XX = '91';
    const INSTRUCAO_INSTRUCAO_DEVOL_VENC_XX = '92';
    const INSTRUCAO_MSG_30_POS = '93';
    const INSTRUCAO_MSG_40_POS = '94';

    public $agencia;
    public $conta;
    public $cedenteDocumento;

    protected $variaveisRequeridas = [
        'conta',
        'agencia',
        'cedenteDocumento',
    ];

    public function __construct() {
        $this->fimLinha = chr(13).chr(10);
        $this->fimArquivo = chr(13).chr(10);
    }

    protected function header()
    {
        $this->iniciaHeader();

        $this->add(1, 1, '0');
        $this->add(2, 2, '1');
        $this->add(3, 9, 'REMESSA');
        $this->add(10, 11, '01');
        $this->add(12, 26, Util::formatCnab('X', 'COBRANCA', 15));
        $this->add(27, 30, Util::formatCnab('9', $this->getAgencia(), 4));
        $this->add(31, 32, '00');
        $this->add(33, 37, Util::formatCnab('9', $this->getConta(),5));
        $this->add(38, 38, Util::modulo10($this->getAgencia().$this->getConta()));
        $this->add(39, 46, '');
        $this->add(47, 76, Util::formatCnab('X', $this->getCedenteNome(), 30));
        $this->add(77, 79, self::COD_BANCO_ITAU);
        $this->add(80, 94, Util::formatCnab('X', 'BANCO ITAU SA', 15));
        $this->add(95, 100, date('dmy'));
        $this->add(101, 394, '');
        $this->add(395, 400, Util::formatCnab('9', 1, 6));

        return $this;
    }

    public function addDetalhe(Detalhe $detalhe)
    {
        $this->iniciaDetalhe();

        $this->add(1, 1, '1');
        $this->add(2, 3, '02');
        $this->add(4, 17, Util::formatCnab('L', $this->getCedenteDocumento(), 14));
        $this->add(18, 21, Util::formatCnab('9', $this->getAgencia(), 4));
        $this->add(22, 23, '00');
        $this->add(24, 28, Util::formatCnab('9', $this->getConta(), 5));
        $this->add(29, 29, Util::modulo10($this->getAgencia().$this->getConta()));
        $this->add(30, 33, '');
        $this->add(34, 37, '0000');
        $this->add(38, 62, Util::formatCnab('X', $detalhe->getNumeroControleString(), 25));
        $this->add(63, 70, Util::formatCnab('9', $detalhe->getNumero(), 8));
        $this->add(71, 83, Util::formatCnab('9', '0', 13, 2));
        $this->add(84, 86, Util::formatCnab('9', $this->getCarteira('109'), 3));
        $this->add(87, 107, '');
        $this->add(108, 108, 'I');
        $this->add(109, 110, '01');
        $this->add(111, 120, Util::formatCnab('X', $detalhe->getNumeroDocumento(), 10));
        $this->add(121, 126, Util::formatCnab('d', $detalhe->getDataVencimento(), 6));
        $this->add(127, 139, Util::formatCnab('9', $detalhe->getValor(), 13, 2));
        $this->add(140, 142, 341);
        $this->add(143, 147, '00000');
        $this->add(148, 149, $detalhe->getEspecie('01'));
        $this->add(150, 150, $detalhe->getAceite('N'));
        $this->add(151, 156, Util::formatCnab('D', $detalhe->getDataDocumento(), 6));
        $this->add(157, 158, $detalhe->getInstrucao1('00'));
        $this->add(159, 160, $detalhe->getInstrucao2('00'));
        $this->add(161, 173, Util::formatCnab('9', $detalhe->getValorMora(), 13, 2));
        $this->add(174, 179, Util::formatCnab('D', $detalhe->getDataLimiteDesconto(), 6));
        $this->add(180, 192, Util::formatCnab('9', $detalhe->getValorDesconto(), 13, 2));
        $this->add(193, 205, Util::formatCnab('9', $detalhe->getvalorIOF(), 13, 2));
        $this->add(206, 218, Util::formatCnab('9', $detalhe->getValorAbatimento(), 13, 2));
        $this->add(219, 220, Util::formatCnab('9L', $detalhe->getSacadoTipoDocumento(), 2));
        $this->add(221, 234, Util::formatCnab('L', $detalhe->getSacadoDocumento(), 14));
        $this->add(235, 264, Util::formatCnab('X', $detalhe->getSacadoNome(), 30));
        $this->add(265, 274, '');
        $this->add(275, 314, Util::formatCnab('X', $detalhe->getSacadoEndereco(), 40));
        $this->add(315, 326, Util::formatCnab('X', $detalhe->getSacadoBairro(), 12));
        $this->add(327, 334, Util::formatCnab('L', $detalhe->getSacadoCEP(), 8));
        $this->add(335, 349, Util::formatCnab('A', $detalhe->getSacadoCidade(), 15));
        $this->add(350, 351, Util::formatCnab('A', $detalhe->getSacadoEstado(), 2));
        $this->add(352, 381, Util::formatCnab('A', $detalhe->getSacadorAvalista(), 30));
        $this->add(382, 385, '');
        $this->add(386, 391, Util::formatCnab('D', $detalhe->getDataVencimento(), 6));
        $this->add(392, 393, Util::formatCnab('9', $detalhe->getDiasProtesto(), 2));
        $this->add(394, 394, '');
        $this->add(395, 400, Util::formatCnab('9', $this->iRegistros+1, 6));

        return $this;
    }

    protected function trailer()
    {
        $this->iniciaTrailer();

        $this->add(1, 1, '9');
        $this->add(2, 394, '');
        $this->add(395, 400, Util::formatCnab('9', $this->getCount(), 6));

        return $this;
    }


}