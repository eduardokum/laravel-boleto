<?php
namespace Eduardokum\LaravelBoleto\Cnab\Remessa\Banco;

use Eduardokum\LaravelBoleto\Cnab\Remessa\AbstractCnab;
use Eduardokum\LaravelBoleto\Cnab\Contracts\Remessa;
use Eduardokum\LaravelBoleto\Cnab\Remessa\Detalhe;
use Eduardokum\LaravelBoleto\Util;

class Bb extends AbstractCnab implements Remessa
{
    const ESPECIE_DUPLICATA = '01';
    const ESPECIE_NOTA_PROMISSORIA = '02';
    const ESPECIE_NOTA_SEGURO = '03';
    const ESPECIE_RECIBO = '05';
    const ESPECIE_LETRAS_CAMBIO = '08';
    const ESPECIE_WARRANT = '09';
    const ESPECIE_CHEQUE = '10';
    const ESPECIE_NOTA_DEBITO = '13';
    const ESPECIE_DUPLICATA_SERVICO = '12';
    const ESPECIE_APOLICE_SEGURO = '15';
    const ESPECIE_DIV_ATV_UNIAO = '25';
    const ESPECIE_DIV_ATV_ESTADO = '26';
    const ESPECIE_DIV_ATV_MUNICIPIO = '27';

    const OCORRENCIA_REMESSA = '01';
    const OCORRENCIA_PEDIDO_BAIXA = '02';
    const OCORRENCIA_PEDIDO_DEBITO = '03';
    const OCORRENCIA_CONCESSAO_ABATIMENTO = '04';
    const OCORRENCIA_CANC_ABATIMENTO_CONCEDIDO = '05';
    const OCORRENCIA_ALT_VENCIMENTO = '06';
    const OCORRENCIA_ALT_CONTROLE_PARTICIPANTE = '07';
    const OCORRENCIA_ALT_SEU_NUMERO = '08';
    const OCORRENCIA_PEDIDO_PROTESTO = '09';
    const OCORRENCIA_SUSTAR_PROTESTO = '10';
    const OCORRENCIA_DISPENSAR_JUROS = '11';
    const OCORRENCIA_ALT_NOME_END_SACADO = '12';
    const OCORRENCIA_CONCEDER_DESC = '31';
    const OCORRENCIA_NAO_CONCEDER_DESC = '32';
    const OCORRENCIA_RETIFICAR_DESC = '33';
    const OCORRENCIA_ALT_DATA_DESC = '34';
    const OCORRENCIA_COBRAR_MULTA = '35';
    const OCORRENCIA_DISPENSAR_MULTA = '36';
    const OCORRENCIA_DISPOENSAR_INDEXADOR = '37';
    const OCORRENCIA_DISPENSAR_LIMITE_REC = '38';
    const OCORRENCIA_ALT_LIMITE_REC = '39';
    const OCORRENCIA_ALT_MODALIDADE = '40';

    const INSTRUCAO_SEM = '00';
    const INSTRUCAO_COBRAR_JUROS = '01';
    const INSTRUCAO_NAO_PROTESTAR = '07';
    const INSTRUCAO_PROTESTAR = '09';
    const INSTRUCAO_PROTESTAR_VENC_03 = '03';
    const INSTRUCAO_PROTESTAR_VENC_04 = '04';
    const INSTRUCAO_PROTESTAR_VENC_05 = '05';
    const INSTRUCAO_PROTESTAR_VENC_XX = '06';
    const INSTRUCAO_PROTESTAR_VENC_15 = '15';
    const INSTRUCAO_PROTESTAR_VENC_20 = '20';
    const INSTRUCAO_PROTESTAR_VENC_25 = '25';
    const INSTRUCAO_PROTESTAR_VENC_30 = '30';
    const INSTRUCAO_PROTESTAR_VENC_45 = '45';
    const INSTRUCAO_CONCEDER_DESC_ATE = '22';
    const INSTRUCAO_DEVOLVER = '42';
    const INSTRUCAO_BAIXAR = '44';
    const INSTRUCAO_ENTREGAR_SACADO_PAGAMENTO = '46';

    public $agencia;
    public $conta;
    public $cedenteDocumento;
    public $carteiraVariacao;
    public $cedenteNome;
    public $convenio;
    public $convenioLider;

    public $variaveisRequeridas = [
        'conta',
        'agencia',
        'convenio',
        'carteiraVariacao',
        'cedenteDocumento',
        'cedenteNome'
    ];

    public function __construct()
    {
        $this->fimLinha = chr(13).chr(10);
        $this->fimArquivo = chr(13).chr(10);
    }

    protected function header()
    {
        $this->iniciaHeader();

        $this->convenio = preg_replace('/^0+/', '', $this->getConvenio());
        if(empty($this->convenio))
        {
            throw new \Exception('Necessita informar o convenio');
        }

        if(empty($this->getConvenioLider()))
        {
            $this->convenioLider = $this->getConvenio();
        }

        $this->add(1,         1,      '0');
        $this->add(2,         2,      '1');
        $this->add(3,         9,      'REMESSA');
        $this->add(10,        11,     '01');
        $this->add(12,        19,     Util::formatCnab('X', 'COBRANCA', 8));
        $this->add(20,        26,     '');
        $this->add(27,        30,     Util::formatCnab('9', $this->getAgencia(), 4));
        $this->add(31,        31,     Util::modulo11($this->getAgencia()));
        $this->add(32,        39,     Util::formatCnab('9', $this->getConta(),8));
        $this->add(40,        40,     Util::modulo11($this->getConta()));
        $this->add(41,        46,     '000000');
        $this->add(47,        76,     Util::formatCnab('X', $this->getCedenteNome(), 30));
        $this->add(77,        79,     Util::formatCnab('X', self::COD_BANCO_BB, 3));
        $this->add(79,        94,     Util::formatCnab('X', 'BANCODOBRASIL', 15));
        $this->add(80,        100,    date('dmy'));
        $this->add(101,       107,    Util::formatCnab('9', $this->getID(), 7));

        if(strlen($this->getConvenio()) < 7)
        {
            $this->add(108,       394,    '');
        }
        else
        {
            $this->add(108,       129,    '');
            $this->add(130,       136, Util::formatCnab('9', $this->getConvenioLider(), 7));
            $this->add(137,       394,    '');
        }

        $this->add(395,       400,    Util::formatCnab('N', 1, 6));

        return $this;
    }

    public function addDetalhe(Detalhe $detalhe)
    {
        $this->iniciaDetalhe();

        $convenio6 = strlen($this->getConvenio()) < 7 ? true : false;

        if($convenio6)
        {
            $prefixoTitulo = 'AI';
            if(in_array($this->getCarteira('17'), ['11','31','51']) )
            {
                $nossoNumero = Util::formatCnab('9', 0, 11);
                if($this->getCarteira('17') != '11' )
                    $prefixoTitulo = 'SD';
            }
            else
            {
                $nossoNumero = Util::formatCnab('9', $this->getConvenio(), 6)
                    . Util::formatCnab('9', $detalhe->getNumero(), 5);
            }
            if($this->getCarteira('17') == '12')
                $prefixoTitulo .= 'U';
        }
        else
        {
            if(in_array($this->getCarteira('17'), ['11','31','51']) )
            {
                $nossoNumero = Util::formatCnab('9', 0, 17);
            }
            else
            {
                $nossoNumero = Util::formatCnab('9', $this->getConvenio(), 7)
                    . Util::formatCnab('9', $detalhe->getNumero(), 10);
            }

        }

        if(!in_array('06', [$detalhe->getInstrucao1(), $detalhe->getInstrucao2()]) ) {
            $detalhe->diasProtesto = '00';
        }

        $idArquivo = $convenio6 ? 1 : 7;

        $this->add(1,         1,      $idArquivo );
        $this->add(2,         3,      '02');
        $this->add(4,         17,     Util::formatCnab('L', $this->cedenteDocumento, 14));
        $this->add(18,        21,     Util::formatCnab('9', $this->getAgencia(), 4));
        $this->add(22,        22,     Util::modulo11($this->getAgencia()));
        $this->add(23,        30,     Util::formatCnab('9', $this->getConta(), 8));
        $this->add(31,        31,     Util::modulo11($this->getConta()));

        if($convenio6)
        {
            $this->add(32,        37,     Util::formatCnab('9', $this->getConvenio(), 6));
            $this->add(38,        62,     Util::formatCnab('X', $detalhe->getNumeroControleString(), 25));
            $this->add(63,        73,     $nossoNumero);
            $this->add(64,        74,     Util::modulo11($nossoNumero));
            $this->add(75,        76,     '00');
            $this->add(77,        78,     '00');
            $this->add(79,        81,     '');
            $this->add(82,        82,     ($detalhe->getSacadorAvalista()?'A':''));
            $this->add(83,        85,     Util::formatCnab('X', $prefixoTitulo, 3));
            $this->add(86,        88,     Util::formatCnab('9', $this->carteiraVariacao, 3));
            $this->add(89,        89,     '0');
            $this->add(90,        94,     '00000');
            $this->add(95,        95,     '0');
        }
        else
        {
            $this->add(32,        38,     Util::formatCnab('9', $this->getConvenio(), 7));
            $this->add(39,        63,     Util::formatCnab('X', $detalhe->getNumeroControleString(), 25));
            $this->add(64,        80,     $nossoNumero);
            $this->add(81,        82,     '00');
            $this->add(83,        84,     '00');
            $this->add(85,        87,     '');
            $this->add(88,        88,     ($detalhe->getSacadorAvalista()?'A':''));
            $this->add(89,        91,     '');
            $this->add(92,        94,     Util::formatCnab('9', $this->carteiraVariacao, 3));
            $this->add(95,        95,     '0');
        }

        $this->add(96,        101,    '000000');
        $this->add(102,       106,    Util::formatCnab('X', $detalhe->getTipoCobranca(), 5));
        $this->add(107,       108,    $this->getCarteira('17'));
        $this->add(109,       110,    Util::formatCnab('N', $detalhe->getOcorrencia(), 2));
        $this->add(111,       120,    Util::formatCnab('X', $detalhe->getNumeroDocumento(), 10));
        $this->add(121,       126,    Util::formatCnab('D', $detalhe->getDataVencimento(), 6));
        $this->add(127,       139,    Util::formatCnab('9', $detalhe->getValor(),13,2));
        $this->add(140,       142,    self::COD_BANCO_BB);
        $this->add(143,       146,    '0000');
        $this->add(147,       147,    '');
        $this->add(148,       149,    $detalhe->getEspecie('01'));
        $this->add(150,       150,    $detalhe->getAceite('N'));
        $this->add(151,       156,    Util::formatCnab('D', $detalhe->getDataDocumento(), 6));
        $this->add(157,       158,    Util::formatCnab('9', $detalhe->getInstrucao1('00'), 2));
        $this->add(159,       160,    Util::formatCnab('9', $detalhe->getInstrucao2('00'), 2));
        $this->add(161,       173,    Util::formatCnab('9', $detalhe->getValorMora(), 13, 2));
        $this->add(174,       179,    Util::formatCnab('9', $detalhe->getDataLimiteDesconto('0'), 6));
        $this->add(180,       192,    Util::formatCnab('9', $detalhe->getValorDesconto(), 13, 2));
        $this->add(193,       205,    Util::formatCnab('9', $detalhe->getvalorIOF(), 13, 2));
        $this->add(206,       218,    Util::formatCnab('9', $detalhe->getValorAbatimento(), 13, 2));
        $this->add(219,       220,    Util::formatCnab('9', $detalhe->getSacadoTipoDocumento(), 2));
        $this->add(221,       234,    Util::formatCnab('9', $detalhe->getSacadoDocumento(), 14));
        $this->add(235,       271,    Util::formatCnab('X', $detalhe->getSacadoNome(), 37));
        $this->add(272,       274,    '');

        if($convenio6)
        {
            $this->add(275,       326,    Util::formatCnab('X', $detalhe->getSacadoEndereco(), 52));
        }
        else
        {
            $this->add(275,       311,    Util::formatCnab('X', $detalhe->getSacadoEndereco(), 37));
            $this->add(312,       326,    '');
        }
        $this->add(327,       334,    Util::formatCnab('9', $detalhe->getSacadoCEP(), 8));
        $this->add(335,       349,    Util::formatCnab('X', $detalhe->getSacadoCidade(), 15));
        $this->add(350,       351,    Util::formatCnab('X', $detalhe->getSacadoEstado(), 2));
        $this->add(352,       391,    Util::formatCnab('X', $detalhe->getSacadorAvalista(), 40));
        $this->add(392,       393,    Util::formatCnab('9', $detalhe->getDiasProtesto(), 2));
        $this->add(394,       394,    '');
        $this->add(395,       400,    Util::formatCnab('N', $this->iRegistros+1, 6));
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