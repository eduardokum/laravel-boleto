<?php
namespace Eduardokum\LaravelBoleto\Cnab\Remessa\Banco;

use Eduardokum\LaravelBoleto\Cnab\Remessa\AbstractCnab;
use Eduardokum\LaravelBoleto\Cnab\Contracts\Remessa;
use Eduardokum\LaravelBoleto\Cnab\Remessa\Detalhe;
use Eduardokum\LaravelBoleto\Util;

class Hsbc extends AbstractCnab implements Remessa
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
    const OCORRENCIA_VENC_ALT_PARA = '48';
    const OCORRENCIA_ALT_DIAS_CARTORIO = '49';
    const OCORRENCIA_INC_SACADO_ELETRONICO = '50';
    const OCORRENCIA_EXC_SACADO_ELETRONICO = '51';
    const OCORRENCIA_PROTESTO_FALIMENTARES = '57';

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

    public $agencia;
    public $conta;
    public $range;
    public $cedenteDocumento;
    public $cedenteNome;

    public $variaveisRequeridas = [
        'agencia',
        'conta',
        'range',
        'cedenteDocumento',
        'cedenteNome',
    ];

    public function __construct() {
        $this->fimLinha = chr(10);
    }

    protected function header()
    {
        $this->iniciaHeader();

        $this->add(1, 1, '0');
        $this->add(2, 2, '1');
        $this->add(3, 9, 'REMESSA');
        $this->add(10, 11, '01');
        $this->add(12, 26, Util::formatCnab('A', 'COBRANCA', 15));
        $this->add(27, 27, 0);
        $this->add(28, 31, Util::formatCnab('N', $this->getAgencia(), 4));
        $this->add(32, 33, 55);
        $this->add(34, 37, Util::formatCnab('NL', $this->getAgencia(), 4));
        $this->add(38, 44, Util::formatCnab('NL', $this->getConta(), 7));
        $this->add(45, 46, '');
        $this->add(47, 76, Util::formatCnab('A', $this->getCedenteNome(), 30));
        $this->add(77, 79, self::COD_BANCO_HSBC);
        $this->add(80, 94, Util::formatCnab('A', 'HSBC', 15));
        $this->add(95, 100, date('dmy'));
        $this->add(101, 105, '01600');
        $this->add(106, 108, 'BPI');
        $this->add(109, 110, '');
        $this->add(111, 117, 'LANCV08');
        $this->add(118, 394, '');
        $this->add(395, 400, Util::formatCnab('N', 1, 6));

        return $this;
    }

    public function addDetalhe(Detalhe $detalhe)
    {
        $this->iniciaDetalhe();

        $nossoNumero    = Util::formatCnab('N',$this->getRange(), 5)
            . Util::formatCnab('N', $detalhe->getNumero(), 5);
        $nossoNumero    = $nossoNumero . Util::modulo11($nossoNumero, 7);

        if($detalhe->getEspecie('01') == '09') {
            $nossoNumero = 0;
        }

        $this->add(1, 1, '1');
        $this->add(2, 3, '02');
        $this->add(4, 17, Util::formatCnab('NL', $this->getCedenteDocumento(), 14));
        $this->add(18, 18, 0);
        $this->add(19, 22, Util::formatCnab('N', $this->getAgencia(), 4));
        $this->add(23, 24, '55');
        $this->add(25, 28, Util::formatCnab('NL', $this->getAgencia(), 4));
        $this->add(29, 35, Util::formatCnab('NL', $this->getConta(), 7));
        $this->add(36, 37, '');
        $this->add(38, 62, Util::formatCnab('X', $detalhe->getNumeroControleString(), 25));
        $this->add(63, 73, Util::formatCnab('N', $nossoNumero, 11));
        $this->add(74, 79, '000000');
        $this->add(80, 90, Util::formatCnab('N', 0, 11, 2));
        $this->add(91, 96, '000000');
        $this->add(97, 107, Util::formatCnab('N', 0, 11, 2));
        $this->add(108, 108, $this->getCarteira('1'));
        $this->add(109, 110, '01');
        $this->add(111, 120, Util::formatCnab('X', $detalhe->getNumeroDocumento(), 10));
        $this->add(121, 126, Util::formatCnab('D', $detalhe->getDataVencimento(), 6));
        $this->add(127, 139, Util::formatCnab('N', $detalhe->getValor(), 13, 2));
        $this->add(140, 142, self::COD_BANCO_HSBC);
        $this->add(143, 147, Util::formatCnab('N', "0", 5));
        $this->add(148, 149, $detalhe->getEspecie('01'));
        $this->add(150, 150, $detalhe->getAceite('N'));
        $this->add(151, 156, Util::formatCnab('D', $detalhe->getDataDocumento(), 6));
        $this->add(157, 158, $detalhe->getInstrucao1('00'));
        $this->add(159, 160, $detalhe->getInstrucao2('00'));
        $this->add(161, 173, Util::formatCnab('N', $detalhe->getValorMora(), 13, 2));
        $this->add(174, 179, Util::formatCnab('D', $detalhe->getDataLimiteDesconto(), 6));
        $this->add(180, 192, Util::formatCnab('N', $detalhe->getValorDesconto(), 13, 2));
        $this->add(193, 205, Util::formatCnab('N', $detalhe->getvalorIOF(), 13, 2));
        $this->add(206, 218, Util::formatCnab('N', $detalhe->getValorAbatimento(), 13, 2));
        $this->add(219, 220, Util::formatCnab('NL',$detalhe->getSacadoTipoDocumento(), 2));
        $this->add(221, 234, Util::formatCnab('L', $detalhe->getSacadoDocumento(), 14));
        $this->add(235, 274, Util::formatCnab('A', $detalhe->getSacadoNome(), 40));
        $this->add(275, 312, Util::formatCnab('A', $detalhe->getSacadoEndereco(), 38));
        $this->add(313, 314, Util::formatCnab('N', $detalhe->getNaoReceberDias(), 2));
        $this->add(315, 326, Util::formatCnab('A', $detalhe->getSacadoBairro(), 12));
        $this->add(327, 331, substr($detalhe->getSacadoCEP(), 0, 5));
        $this->add(332, 334, substr($detalhe->getSacadoCEP(), -3));
        $this->add(335, 349, Util::formatCnab('A', $detalhe->getSacadoCidade(), 15));
        $this->add(350, 351, Util::formatCnab('A', $detalhe->getSacadoEstado(), 2));
        $this->add(352, 390, Util::formatCnab('A', $detalhe->getSacadorAvalista(), 39));
        $this->add(391, 391, '');
        $this->add(392, 393, Util::formatCnab('N', $detalhe->getDiasProtesto(), 2));
        $this->add(394, 394, $detalhe->getTipoMoeda('9'));
        $this->add(395, 400, Util::formatCnab('N', $this->iRegistros+1, 6));

        if(in_array('15', $detalhe->getInstrucoes()) || in_array('16', $detalhe->getInstrucoes()) || in_array( '29', $detalhe->getInstrucoes())) {

            if(in_array('29', $detalhe->getInstrucoes()))
            {
                $detalhe->taxaMulta = 0;
            }

            if(empty($detalhe->getDataMulta()) || empty($detalhe->getTaxaMulta()) )
            {
                throw new \Exception('Campos ausentes {dataMulta|taxaMulta}');
            }

            $string = Util::formatCnab('D', $detalhe->getDataMulta(), 6)
                . Util::formatCnab('N', $detalhe->getTaxaMulta(), 4, 2).'000';
            $this->add(206, 218, Util::formatCnab('A', $string, 13, 0, 0));

        }
        if(in_array('19', $detalhe->getInstrucoes()) || in_array('22', $detalhe->getInstrucoes()) || in_array('24', $detalhe->getInstrucoes())) {

            if(in_array('24', $detalhe->getInstrucoes()))
            {
                $detalhe->xDiasMulta = 0;
            }

            if(empty($detalhe->getXDiasMulta()) || empty($detalhe->getValorMulta()) )
            {
                throw new \Exception('Campos ausentes {xDiasMulta|valorMulta}');
            }

            $string = Util::formatCnab('N', $detalhe->getValorMulta(), 10, 2)
                . Util::formatCnab('N', $detalhe->getXDiasMulta(), 3);
            $this->add(206, 218, Util::formatCnab('A', $string, 13, 0, 0));
        }

        if( in_array('73', $detalhe->getInstrucoes()) || in_array('74', $detalhe->getInstrucoes()) ) {

            if(empty($detalhe->getTaxaMulta()) || empty($detalhe->getXDiasMulta()) )
            {
                throw new \Exception('Campos ausentes {taxaMulta|xDiasMulta}');
            }

            $string = Util::formatCnab('A', '', 6)
                . Util::formatCnab('N', $detalhe->getTaxaMulta(), 4,2)
                . Util::formatCnab('N', $detalhe->getXDiasMulta(), 3);
            $this->add(206, 218, Util::formatCnab('A', $string, 13, 0, 0));
        }
    }

    protected function trailer()
    {
        $this->iniciaTrailer();

        $this->add(1, 1, '9' );
        $this->add(2, 394, '');
        $this->add(395, 400, Util::formatCnab('N', $this->getCount(), 6));

        return $this;
    }

}