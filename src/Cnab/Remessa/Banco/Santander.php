<?php
namespace Eduardokum\LaravelBoleto\Cnab\Remessa\Banco;

use Eduardokum\LaravelBoleto\Cnab\Remessa\AbstractCnab;
use Eduardokum\LaravelBoleto\Cnab\Contracts\Remessa;
use Eduardokum\LaravelBoleto\Cnab\Remessa\Detalhe;
use Eduardokum\LaravelBoleto\Util;

class Santander extends AbstractCnab implements Remessa
{


    const ESPECIE_DUPLICATA = '01';
    const ESPECIE_NOTA_PROMISSORIA = '02';
    const ESPECIE_NOTA_SEGURO = '03';
    const ESPECIE_RECIBO = '05';
    const ESPECIE_DUPLICATA_SERVICO = '06';
    const ESPECIE_LETRA_CAMBIO = '07';

    const OCORRENCIA_REMESSA = '01';
    const OCORRENCIA_BAIXA_TITULO = '02';
    const OCORRENCIA_CONCESSAO_ABATIMENTO = '04';
    const OCORRENCIA_CANC_ABATIMENTO = '05';
    const OCORRENCIA_ALT_VENCIMENTO = '06';
    const OCORRENCIA_ALT_CONTROLE_PARTICIPANTE = '07';
    const OCORRENCIA_ALT_SEUNUMERO = '08';
    const OCORRENCIA_PROTESTAR = '09';
    const OCORRENCIA_SUSTAR_PROTESTO = '18';

    const INSTRUCAO_SEM = '00';
    const INSTRUCAO_BAIXAR_APOS_VENC_15 = '02';
    const INSTRUCAO_BAIXAR_APOS_VENC_30 = '03';
    const INSTRUCAO_NAP_AIXAR = '04';
    const INSTRUCAO_PROTESTAR = '06';
    const INSTRUCAO_NAO_PROTESTAR = '07';
    const INSTRUCAO_NAO_COBRAR_MORA = '08';

    public $agencia;
    public $conta;
    public $codigoTransmissao;
    public $cedenteDocumento;
    public $cedenteNome;

    protected $total = 0;

    public $variaveisRequeridas = [
        'conta',
        'agencia',
        'codigoTransmissao',
        'cedenteDocumento',
        'cedenteNome',
    ];

    public function __construct() {
        $this->fimLinha = chr(13).chr(10);
        $this->fimArquivo = chr(13).chr(10);
    }

    protected function header()
    {
        $this->iniciaHeader();

        $this->add(1, 1, '0' );
        $this->add(2, 2, '1');
        $this->add(3, 9, 'REMESSA');
        $this->add(10, 11, '01');
        $this->add(12, 26, Util::formatCnab('X', 'COBRANCA', 15));
        $this->add(27, 46, Util::formatCnab('9', $this->getCodigoTransmissao(), 20));
        $this->add(47, 76, Util::formatCnab('X', $this->getCedenteNome(), 30));
        $this->add(77, 79, self::COD_BANCO_SANTANDER);
        $this->add(80, 94, Util::formatCnab('X', 'SANTANDER', 15));
        $this->add(95, 100, date('dmy'));
        $this->add(101, 116, Util::formatCnab('9','0',16));
        $this->add(117, 391, '');
        $this->add(392, 394, '000');
        $this->add(395, 400, Util::formatCnab('9', 1, 6));

        return $this;
    }

    public function addDetalhe(Detalhe $detalhe)
    {
        $this->iniciaDetalhe();

        $this->total += $detalhe->getValor();

        if(in_array('06', $detalhe->getInstrucoes()))
        {
            $detalhe->diasProtesto = '00';
        }

        $this->add(1, 1, '1');
        $this->add(2, 3, '02');
        $this->add(4, 17, Util::formatCnab('9L', $this->getCedenteDocumento(), 14));
        $this->add(18, 37, Util::formatCnab('9', $this->getCodigoTransmissao(), 20));
        $this->add(38, 62, Util::formatCnab('X', $detalhe->getNumeroControleString(), 25));
        $this->add(63, 69, Util::formatCnab('9', $detalhe->getNumero(), 7));
        $this->add(70, 70, Util::modulo11($detalhe->getNumero()));
        $this->add(71, 76, Util::formatCnab('D', $detalhe->getDataLimiteDesconto(), 6));
        $this->add(77, 77, '');
        $this->add(78, 78, ($detalhe->getTaxaMulta() ? '4' : '0'));
        $this->add(79, 82, Util::formatCnab('9', $detalhe->getTaxaMulta(), 4, 2));
        $this->add(83, 84, '00');
        $this->add(85, 97, Util::formatCnab('9', 0, 13, 2));
        $this->add(98, 101, '');
        $this->add(102, 107, Util::formatCnab('9', $detalhe->getDataMulta(), 6));
        $this->add(108, 108, $this->getCarteira('1'));
        $this->add(109, 110, '01');
        $this->add(111, 120, Util::formatCnab('X', $detalhe->getNumeroDocumento(), 10));
        $this->add(121, 126, Util::formatCnab('D', $detalhe->getDataVencimento(), 6));
        $this->add(127, 139, Util::formatCnab('9', $detalhe->getValor(), 13, 2));
        $this->add(140, 142, self::COD_BANCO_SANTANDER);
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
        $this->add(221, 234, Util::formatCnab('9L', $detalhe->getSacadoDocumento(), 14));
        $this->add(235, 274, Util::formatCnab('X', $detalhe->getSacadoNome(), 40));
        $this->add(275, 314, Util::formatCnab('X', $detalhe->getSacadoEndereco(), 40));
        $this->add(315, 326, Util::formatCnab('X', $detalhe->getSacadoBairro(), 12));
        $this->add(327, 334, Util::formatCnab('9L', $detalhe->getSacadoCEP(), 8));
        $this->add(335, 349, Util::formatCnab('X', $detalhe->getSacadoCidade(), 15));
        $this->add(350, 351, Util::formatCnab('X', $detalhe->getSacadoEstado(), 2));
        $this->add(352, 381, Util::formatCnab('X', $detalhe->getSacadorAvalista(), 30));
        $this->add(382, 382, '');
        $this->add(383, 383, 'I');
        $this->add(384, 385, substr($this->getConta(),-2));
        $this->add(386, 391, '');
        $this->add(392, 393, Util::formatCnab('9', $detalhe->getDiasProtesto(), 2));
        $this->add(394, 394, '');
        $this->add(395, 400, Util::formatCnab('N', $this->iRegistros+1, 6));

        return $this;
    }

    protected function trailer()
    {
        $this->iniciaTrailer();

        $this->add(1, 1, '9');
        $this->add(2, 7, Util::formatCnab('9', $this->getCount(), 6));
        $this->add(8, 20, Util::formatCnab('9', $this->getTotal(), 13, 2));
        $this->add(21, 394, Util::formatCnab('9', 0, 374));
        $this->add(395, 400, Util::formatCnab('9', $this->getCount(), 6));

        return $this;
    }

}