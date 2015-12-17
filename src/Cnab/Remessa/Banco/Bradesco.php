<?php
namespace Eduardokum\LaravelBoleto\Cnab\Remessa\Banco;

use Eduardokum\LaravelBoleto\Cnab\Remessa\AbstractCnab;
use Eduardokum\LaravelBoleto\Cnab\Contracts\Remessa;
use Eduardokum\LaravelBoleto\Cnab\Remessa\Detalhe;
use Eduardokum\LaravelBoleto\Util;

class Bradesco extends AbstractCnab implements Remessa
{
    public $agencia;
    public $conta;
    public $contaRazao;
    public $cedenteCodigo;
    public $cedenteNome;
    public $debitoAutomatico = false;

    const ESPECIE_DUPLICATA = '01';
    const ESPECIE_NOTA_PROMISSORIA = '02';
    const ESPECIE_NOTA_SEGURO = '03';
    const ESPECIE_COBRANCA_SERIADA = '04';
    const ESPECIE_RECIBO = '05';
    const ESPECIE_LETRAS_CAMBIO = '10';
    const ESPECIE_NOTA_DEBITO = '11';
    const ESPECIE_DUPLICATA_SERVICO = '12';
    const ESPECIE_OUTROS = '99';

    const OCORRENCIA_REMESSA = '01';
    const OCORRENCIA_PEDIDO_BAIXA = '02';
    const OCORRENCIA_CONCESSAO_ABATIMENTO = '04';
    const OCORRENCIA_CANC_ABATIMENTO_CONCEDIDO = '05';
    const OCORRENCIA_ALT_VENCIMENTO = '06';
    const OCORRENCIA_ALT_CONTROLE_PARTICIPANTE = '07';
    const OCORRENCIA_ALT_SEU_NUMERO = '08';
    const OCORRENCIA_PEDIDO_PROTESTO = '09';
    const OCORRENCIA_SUSTAR_PROTESTO_BAIXAR_TITULO = '18';
    const OCORRENCIA_SUSTAR_PROTESTO_MANTER_TITULO = '19';
    const OCORRENCIA_TRANS_CESSAO_CREDITO_ID10 = '22';
    const OCORRENCIA_TRANS_CARTEIRAS = '23';
    const OCORRENCIA_DEVOLUCAO_TRANS_CARTEIRAS = '24';
    const OCORRENCIA_ALT_OUTROS_DADOS = '31';
    const OCORRENCIA_DESAGENDAMENTO_DEBITO_AUT = '35';
    const OCORRENCIA_ACERTO_RATEIO_CREDITO = '68';
    const OCORRENCIA_CANC_RATEIO_CREDITO = '69';

    const INSTRUCAO_SEM = '00';
    const INSTRUCAO_NAO_COBRAR_JUROS = '08';
    const INSTRUCAO_NAO_RECEBER_APOS_VENC = '09';
    const INSTRUCAO_MULTA_10_APOS_VENC_4 = '10';
    const INSTRUCAO_NAO_RECEBER_APOS_VENC_8 = '11';
    const INSTRUCAO_COBRAR_ENCAR_APOS_5 = '12';
    const INSTRUCAO_COBRAR_ENCAR_APOS_10 = '13';
    const INSTRUCAO_COBRAR_ENCAR_APOS_15 = '14';
    const INSTRUCAO_CENCEDER_DESC_APOS_VENC = '15';

    public $variaveisRequeridas = [
        'agencia',
        'conta',
        'contaRazao',
        'cedenteCodigo',
        'cedenteNome'
    ];

    public function __construct() {
        $this->fimLinha = chr(13).chr(10);
        $this->fimArquivo = chr(13).chr(10);
    }

    protected function header()
    {
        $this->iniciaHeader();

        $this->add(1,         1,      '0');
        $this->add(2,         2,      '1');
        $this->add(3,         9,      'REMESSA');
        $this->add(10,        11,     '01');
        $this->add(12,        26,     Util::formatCnab('A', 'COBRANCA', 15));
        $this->add(27,        46,     Util::formatCnab('N', $this->getCedenteCodigo(),20));
        $this->add(47,        76,     Util::formatCnab('A', $this->getCedenteNome(), 30));
        $this->add(77,        79,     self::COD_BANCO_BRADESCO);
        $this->add(80,        94,     Util::formatCnab('A', 'Bradesco', 15));
        $this->add(95,        100,    date('dmy'));
        $this->add(101,       108,    '');
        $this->add(109,       110,    'MX');
        $this->add(111,       117,    Util::formatCnab('N', $this->getID(), 7));
        $this->add(118,       394,    '');
        $this->add(395,       400,    Util::formatCnab('N', 1, 6));

        return $this;
    }

    public function addDetalhe(Detalhe $detalhe)
    {
        $this->iniciaDetalhe();

        $idempresa  = '0';
        $idempresa  .= Util::formatCnab('N', $this->getCarteira('21'), 3);
        $idempresa  .= Util::formatCnab('N', $this->getAcencia(), 5);
        $idempresa  .= Util::formatCnab('N', $this->getConta(), 7);
        $idempresa  .= Util::modulo11($this->getConta());

        $dvNossoNumero = Util::modulo11(Util::formatCnab('N', $this->getCarteira('21'), 2) . Util::formatCnab('N', $detalhe->getNumero(), 11), 7, 0, 'P');
        $dvNossoNumero = $detalhe->getNumero() > 0 ? $dvNossoNumero : 0;

        $this->add(1,         1,      '1');


        if($this->debitoAutomatico) {
            /**
             *
             * 002 a 020 - Identificação do Débito Automático em C/C
             * Somente deverão ser preenchidos, caso o cliente Cedente esteja previamente cadastrado
             * para operar com a modalidade de débito automático em Conta do cliente pagador (Sacado),
             * cujos campos correspondentes a essas posições são:
             * - posição 002 a 006 = no da Agência a ser debitada, ou seja, do Sacado
             * - posição 007 a 007 = dígito da Agência a ser debitada
             * - posição 008 a 012 = razão da Conta  - Ex. 07050
             * - posição 013 a 019 = no da Conta Corrente do Sacado
             * - posição 020 a 020 = dígito da Conta Corrente do Sacado
             *
             */
            $this->add(2,         6,      Util::formatCnab('N', $this->getAgencia(), 5));
            $this->add(7,         7,      Util::modulo11($this->getAgencia()));
            $this->add(8,         12,     Util::formatCnab('N', $this->getContaRazao(), 5));
            $this->add(13,        19,     Util::formatCnab('N', $this->getConta(), 7));
            $this->add(20,        20,     Util::modulo11($this->getConta()));
        } else {
            $this->add(2,         6,      Util::formatCnab('A', '', 5));
            $this->add(7,         7,      '');
            $this->add(8,         12,     Util::formatCnab('A', '', 5));
            $this->add(13,        19,     Util::formatCnab('A', '', 7));
            $this->add(20,        20,     '');
        }

        if($detalhe->getTaxaMulta())
        {
            $tipoMulta = 2;
            $multa = $detalhe->getTaxaMulta();
        }
        elseif($detalhe->getValorMulta())
        {
            $tipoMulta = 0;
            $multa = $detalhe->getValorMulta();
        }
        else
        {
            $tipoMulta = ' ';
            $multa = 0;
        }

        $this->add(21,        37,     Util::formatCnab('A', $idempresa, 17));
        $this->add(38,        62,     Util::formatCnab('A', $detalhe->getNumeroControleString(), 25));
        $this->add(63,        65,     self::COD_BANCO_BRADESCO);
        $this->add(66,        66,     $tipoMulta);
        $this->add(67,        70,     Util::formatCnab('N', $multa, 4, 2));
        $this->add(71,        81,     Util::formatCnab('N', $detalhe->getNumero(), 11));
        $this->add(82,        82,     $dvNossoNumero);
        $this->add(83,        92,     Util::formatCnab('N', $detalhe->getValorDesconto(), 10, 2));
        $this->add(93,        93,     ($detalhe->getNumero()>0 ? '2' : '1'));
        $this->add(94,        94,     ($detalhe->getNumero()>0 ? 'N' : ' '));
        $this->add(95,        104,    '');
        $this->add(105,       105,    'R');
        $this->add(106,       106,    '2');
        $this->add(107,       108,    '');
        $this->add(109,       110,    Util::formatCnab('N', $detalhe->getOcorrencia(), 2));
        $this->add(111,       120,    Util::formatCnab('A', $detalhe->getNumeroDocumento(), 10));
        $this->add(121,       126,    Util::formatCnab('D', $detalhe->getDataVencimento(), 6));
        $this->add(127,       139,    Util::formatCnab('N', $detalhe->getValor(), 13, 2));
        $this->add(140,       142,    Util::formatCnab('A','', 3));
        $this->add(143,       147,    Util::formatCnab('A','', 5));
        $this->add(148,       149,    $detalhe->getEspecie('01'));
        $this->add(150,       150,    $detalhe->getAceite('N'));
        $this->add(151,       156,    Util::formatCnab('D', $detalhe->getDataDocumento(), 6));
        $this->add(157,       158,    $detalhe->getInstrucao1('00'));
        $this->add(159,       160,    $detalhe->getInstrucao2('00'));
        $this->add(161,       173,    Util::formatCnab('N', $detalhe->getValorMora(), 13, 2));
        $this->add(174,       179,    Util::formatCnab('D', $detalhe->getDataLimiteDesconto(), 6));
        $this->add(180,       192,    Util::formatCnab('N', $detalhe->getValorDesconto(), 13, 2));
        $this->add(193,       205,    Util::formatCnab('N', $detalhe->getvalorIOF(), 13, 2));
        $this->add(206,       218,    Util::formatCnab('N', $detalhe->getValorAbatimento(), 13, 2));
        $this->add(219,       220,    Util::formatCnab('NL',$detalhe->getSacadoTipoDocumento(), 2));
        $this->add(221,       234,    Util::formatCnab('L', $detalhe->getSacadoDocumento(), 14));
        $this->add(235,       274,    Util::formatCnab('A', $detalhe->getSacadoNome(), 40));
        $this->add(275,       314,    Util::formatCnab('A', $detalhe->getSacadoEndereco(), 40));
        $this->add(315,       326,    '');
        $this->add(327,       331,    substr($detalhe->getSacadoCEP(), 0, 5));
        $this->add(332,       334,    substr($detalhe->getSacadoCEP(), -3));
        $this->add(335,       394,    Util::formatCnab('A', $detalhe->getSacadorAvalista(), 60));
        $this->add(395,       400,    Util::formatCnab('N', $this->iRegistros+1, 6));

        return $this;
    }

    protected function trailer()
    {
        $this->iniciaTrailer();

        $this->add(1,         1,      '9');
        $this->add(2,         394,    '');
        $this->add(395,       400,    Util::formatCnab('N', $this->getCount(), 6));

        return $this;
    }


}