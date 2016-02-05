<?php
namespace Eduardokum\LaravelBoleto\Cnab\Remessa\Banco;

use Eduardokum\LaravelBoleto\Cnab\Remessa\AbstractCnab;
use Eduardokum\LaravelBoleto\Cnab\Contracts\Remessa;
use Eduardokum\LaravelBoleto\Cnab\Remessa\Detalhe;
use Eduardokum\LaravelBoleto\Util;

class Caixa  extends AbstractCnab implements Remessa
{

    const ESPECIE_DUPLICATA = '01';
    const ESPECIE_NOTA_PROMISSORIA = '02';
    const ESPECIE_DUPLICATA_SERVICO = '03';
    const SPECIE_NOTA_SEGURO = '05';
    const ESPECIE_LETRAS_CAMBIO = '06';
    const ESPECIE_OUTROS = '09';

    const OCORRENCIA_ENTRADA_TITULO = '01';
    const OCORRENCIA_PPEDIDO_BAIXA = '02';
    const OCORRENCIA_CONCESSAO_ABATIMENTO = '03';
    const OCORRENCIA_CANC_ABATIMENTO = '04';
    const OCORRENCIA_ALT_VENC = '05';
    const OCORRENCIA_ALT_USO_EMPRESA = '06';
    const OCORRENCIA_ALT_PRAZO_PROTESTO = '07';
    const OCORRENCIA_ALT_PRAZO_DEVOLUCAO = '08';
    const OCORRENCIA_ALT_OUTROS_DADOS = '09';
    const OCORRENCIA_ALT_OUTROS_DADOS_EMISSAO_BOLETO = '10';
    const OCORRENCIA_ALT_PROTESTO_DEVOLUCAO = '11';
    const OCORRENCIA_ALT_DEVOLUCAO_PROTESTO = '12';

    const INSTRUCAO_PROTESTAR_VENC_XX = '01';
    const INSTRUCAO_DEVOLVER_VENC_XX = '02';

    public $agencia;
    public $cedenteDocumento;
    public $cedenteCodigo;
    public $cedenteNome;

    protected $variaveisRequeridas = [
        'agencia',
        'cedenteDocumento',
        'cedenteCodigo',
        'cedenteNome',
    ];

    public function __construct()
    {
        $this->fimLinha = chr(13).chr(10);
        $this->fimArquivo = chr(13).chr(10);
    }

    protected function header()
    {
        $this->iniciaHeader();

        $this->arrumaCodCedente();

        $this->add(1, 1, '0');
        $this->add(2, 2, '1');
        $this->add(3, 9, 'REMESSA');
        $this->add(10, 11, '01');
        $this->add(12, 26, Util::formatCnab('X', 'COBRANCA', 15));
        $this->add(27, 42, Util::formatCnab('9', $this->getCedenteCodigo(), 16));
        $this->add(43, 46, Util::formatCnab('X', '', 4));
        $this->add(47, 76, Util::formatCnab('X', $this->getCedenteNome(), 30));
        $this->add(77, 79, self::COD_BANCO_CEF);
        $this->add(80, 94, Util::formatCnab('X', 'C ECON FEDERAL', 15));
        $this->add(95, 100, date('dmy'));
        $this->add(101, 389, '');
        $this->add(390, 394, Util::formatCnab('9', $this->getID(), 5));
        $this->add(395, 400, Util::formatCnab('9', 1, 6));

        return $this;
    }

    public function addDetalhe(Detalhe $detalhe)
    {
        $this->iniciaDetalhe();

        if( $this->getCarteira('11') == '11' ) {
            $nossoNumero = Util::formatCnab('9', $detalhe->getNumero(), 10);
            $nossoNumero .= Util::modulo11($detalhe->getNumero());
        } else if($this->getCarteira('11') == '12') {
            $nossoNumero = '9'.Util::formatCnab('9', $detalhe->getNumero(), 9);
            $nossoNumero = $nossoNumero.Util::modulo11($nossoNumero);
        } else {
            throw new \Exception("cateira '{$this->getCarteira('11')}' inválida, somente 11 e 12 são válidas");
        }

        $this->add(1, 1, '1');
        $this->add(2, 3, '02');
        $this->add(4, 17, Util::formatCnab('9L', $this->getCedenteDocumento(), 14));
        $this->add(18, 33, Util::formatCnab('9', $this->getCedenteCodigo(), 16));
        $this->add(34, 35, Util::formatCnab('X', '', 2));
        $this->add(36, 37, '00');
        $this->add(38, 62, Util::formatCnab('X', $detalhe->getNumeroControleString(), 25));
        $this->add(63, 73, Util::formatCnab('9', $nossoNumero, 11));
        $this->add(74, 76, Util::formatCnab('X', '', 3));
        $this->add(77, 106, Util::formatCnab('X', '', 30));
        $this->add(107, 108, Util::formatCnab('9', $this->getCarteira('11'), 2));
        $this->add(109, 110, '01');
        $this->add(111, 120, Util::formatCnab('X', $detalhe->getNumeroDocumento(), 10));
        $this->add(121, 126, Util::formatCnab('D', $detalhe->getDataVencimento(), 6));
        $this->add(127, 139, Util::formatCnab('9', $detalhe->getValor(), 13, 2));
        $this->add(140, 142, self::COD_BANCO_CEF);
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
        $this->add(219, 220, Util::formatCnab('9', $detalhe->getSacadoTipoDocumento(), 2));
        $this->add(221, 234, Util::formatCnab('9L',$detalhe->getSacadoDocumento(), 14));
        $this->add(235, 274, Util::formatCnab('X', $detalhe->getSacadoNome(), 30));
        $this->add(275, 314, Util::formatCnab('X', $detalhe->getSacadoEndereco(), 40));
        $this->add(315, 326, Util::formatCnab('X', $detalhe->getSacadoBairro(), 12));
        $this->add(327, 334, Util::formatCnab('L', $detalhe->getSacadoCEP(), 8));
        $this->add(335, 349, Util::formatCnab('A', $detalhe->getSacadoCidade(), 15));
        $this->add(350, 351, Util::formatCnab('A', $detalhe->getSacadoEstado(), 2));
        $this->add(352, 357, Util::formatCnab('D', $detalhe->getDataVencimento(), 6));
        $this->add(358, 367, Util::formatCnab('9', $detalhe->getValorMulta(Util::percent($detalhe->getValor(), $detalhe->getTaxaMulta())), 10, 2));
        $this->add(368, 389, Util::formatCnab('X', $detalhe->getSacadorAvalista(), 22));
        $this->add(390, 391, '00');
        $this->add(392, 393, Util::formatCnab('9', $detalhe->getDiasProtesto(), 2));
        $this->add(394, 394, Util::formatCnab('9', $detalhe->getTipoMoeda('9'), 1));
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

    private function arrumaCodCedente() {
        $_4first = substr($this->getCedenteCodigo(), 0, 4);
        $agencia = Util::formatCnab('9', $this->getAgencia(), 4);
        if( $_4first != $agencia ) {
            if( strlen($this->getCedenteCodigo()) == 12 ) {
                $this->cedenteCodigo = $agencia.$this->getCedenteCodigo();
            } else if( strlen($this->getCedenteCodigo()) == 11 ) {
                $this->cedenteCodigo = $agencia.$this->getCedenteCodigo();
                $this->cedenteCodigo .= Util::modulo11($this->getCedenteCodigo());
            } else {
                throw new \Exception('Codigo de cedente inválido, formato: PPPXXXXXXXXD');
            }
        }
    }
}