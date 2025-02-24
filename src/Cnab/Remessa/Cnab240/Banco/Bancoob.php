<?php
/**
 * Created by Guilherme Couto.
 * User: guicouto
 * Email: ccoutoguilherme@gmail.com
 * Date: 23/08/2017
 * Time: 15:02
 */

namespace Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab240\Banco;

use Eduardokum\LaravelBoleto\Util;
use Eduardokum\LaravelBoleto\CalculoDV;
use Eduardokum\LaravelBoleto\Exception\ValidationException;
use Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab240\AbstractRemessa;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;
use Eduardokum\LaravelBoleto\Contracts\Cnab\Remessa as RemessaContract;

class Bancoob extends AbstractRemessa implements RemessaContract
{
    const OCORRENCIA_REMESSA = '01';
    const OCORRENCIA_PEDIDO_BAIXA = '02';
    const OCORRENCIA_CONCESSAO_ABATIMENTO = '04';
    const OCORRENCIA_CANC_ABATIMENTO = '05';
    const OCORRENCIA_ALT_VENCIMENTO = '06';
    const OCORRENCIA_PROTESTAR = '09';
    const OCORRENCIA_NAO_PROTESTAR = '10';
    const OCORRENCIA_SUSTAR_PROTESTO = '18';
    const OCORRENCIA_ALT_OUTROS_DADOS = '31';
    const OCORRENCIA_NAO_CONCORDA_SACADO = '38';
    const OCORRENCIA_DISPENSA_JUROS = '47';
    const OCORRENCIA_ALT_DADOS_EXTRAS = '49';
    const OCORRENCIA_ENT_NEGATIVACAO = '66';
    const OCORRENCIA_NAO_NEGATIVAR = '67';
    const OCORRENCIA_EXC_NEGATIVACAO = '68';
    const OCORRENCIA_CANC_NEGATIVACAO = '69';
    const OCORRENCIA_DESCONTAR_TITULOS_DIA = '93';
    const PROTESTO_DIAS_CORRIDOS = '1';
    const PROTESTO_DIAS_UTEIS = '2';
    const PROTESTO_NAO_PROTESTAR = '3';
    const PROTESTO_NEGATIVAR_DIAS_CORRIDOS = '7';
    const PROTESTO_NAO_NEGATIVAR = '8';

    public function __construct(array $params = [])
    {
        parent::__construct($params);
        $this->addCampoObrigatorio('idremessa');
    }

    /**
     * Código do banco
     *
     * @var string
     */
    protected $codigoBanco = BoletoContract::COD_BANCO_BANCOOB;

    /**
     * Define as carteiras disponíveis para cada banco
     *
     * @var array
     */
    protected $carteiras = [1];

    /**
     * Caracter de fim de linha
     *
     * @var string
     */
    protected $fimLinha = "\r\n";

    /**
     * Caracter de fim de arquivo
     *
     * @var null
     */
    protected $fimArquivo = '';

    /**
     * @param BoletoContract $boleto
     *
     * @return Bancoob
     */
    public function addBoleto(BoletoContract $boleto)
    {
        $this->boletos[] = $boleto;
        $this->segmentoP($boleto);
        $this->segmentoQ($boleto);
        $this->segmentoR($boleto);

        return $this;
    }

    /**
     * @param BoletoContract $boleto
     *
     * @return Bancoob
     * @throws ValidationException
     */
    protected function segmentoP(BoletoContract $boleto)
    {
        $this->iniciaDetalhe();
        $this->add(1, 3, Util::onlyNumbers($this->getCodigoBanco()));
        $this->add(4, 7, '0001');
        $this->add(8, 8, '3');
        $this->add(9, 13, Util::formatCnab('9', $this->iRegistrosLote, 5));
        $this->add(14, 14, 'P');
        $this->add(15, 15, '');
        $this->add(16, 17, self::OCORRENCIA_REMESSA);
        if ($boleto->getStatus() == $boleto::STATUS_BAIXA) {
            $this->add(16, 17, self::OCORRENCIA_PEDIDO_BAIXA);
        }
        if ($boleto->getStatus() == $boleto::STATUS_ALTERACAO) {
            $this->add(16, 17, self::OCORRENCIA_ALT_OUTROS_DADOS);
        }
        if ($boleto->getStatus() == $boleto::STATUS_ALTERACAO_DATA) {
            $this->add(16, 17, self::OCORRENCIA_ALT_VENCIMENTO);
        }
        if ($boleto->getStatus() == $boleto::STATUS_CUSTOM) {
            $this->add(16, 17, sprintf('%2.02s', $boleto->getComando()));
        }
        $this->add(18, 22, Util::formatCnab('9', $this->getAgencia(), 5));
        $this->add(23, 23, ! is_null($this->getAgenciaDv()) ? $this->getAgenciaDv() : CalculoDV::bancoobAgencia($this->getAgencia()));
        $this->add(24, 35, Util::formatCnab('9', $this->getConta(), 12));
        $this->add(36, 36, ! is_null($this->getContaDv()) ? $this->getContaDv() : CalculoDV::bancoobContaCorrente($this->getConta()));
        $this->add(37, 37, '');
        $this->add(38, 47, Util::formatCnab('9', $boleto->getNossoNumero(), 10));
        $this->add(48, 49, '01');   //Parcela Única
        $this->add(50, 51, '01');   //Simples Com Registro
        $this->add(52, 52, '4');    //A4 Sem Envelopamento
        $this->add(53, 57, '');
        $this->add(58, 58, $this->getCarteira());
        $this->add(59, 59, '0');
        $this->add(60, 60, '');
        $this->add(61, 61, '2');
        $this->add(62, 62, '2');
        $this->add(63, 77, Util::formatCnab('9', $boleto->getNumeroDocumento(), 15));
        $this->add(78, 85, $boleto->getDataVencimento()->format('dmY'));
        $this->add(86, 100, Util::formatCnab('9', $boleto->getValor(), 15, 2));
        $this->add(101, 105, '00000');
        $this->add(106, 106, '');
        $this->add(107, 108, Util::formatCnab('9', $boleto->getEspecieDocCodigo(), 2));
        $this->add(109, 109, Util::formatCnab('9', $boleto->getAceite() == 'N' ? 'N' : 'A', 1));    //N = Não Aceita     A = Aceite
        $this->add(110, 117, $boleto->getDataDocumento()->format('dmY'));
        $this->add(118, 118, ($boleto->getJuros() !== null && $boleto->getJuros() > 0) ? '2' : '0');    //0 = ISENTO | 1 = R$ ao dia | 2 = % ao mês
        $this->add(119, 126, ($boleto->getJuros() !== null && $boleto->getJuros() > 0) ? $boleto->getDataVencimento()->copy()->addDays($boleto->getJurosApos() ?: 1)->format('dmY') : '00000000');
        $this->add(127, 141, Util::formatCnab('9', $boleto->getJuros(), 15, 2)); //Taxa mensal
        $this->add(142, 142, $boleto->getDesconto() > 0 ? '1' : '0'); //0 = SEM DESCONTO | 1 = VALOR FIXO | 2 = PERCENTUAL
        $this->add(143, 150, $boleto->getDesconto() > 0 ? $boleto->getDataDesconto()->format('dmY') : '00000000');
        $this->add(151, 165, Util::formatCnab('9', $boleto->getDesconto(), 15, 2));
        $this->add(166, 180, Util::formatCnab('9', 0, 15, 2));
        $this->add(181, 195, Util::formatCnab('9', 0, 15, 2));
        $this->add(196, 220, Util::formatCnab('X', $boleto->getNumeroControle(), 25));
        $this->add(221, 221, self::PROTESTO_NAO_PROTESTAR);
        if ($boleto->getDiasProtesto() > 0) {
            $this->add(221, 221, self::PROTESTO_DIAS_UTEIS);
        }
        $this->add(222, 223, Util::formatCnab('9', $boleto->getDiasProtesto(), 2));
        $this->add(224, 224, '0');
        $this->add(225, 227, '');
        $this->add(228, 229, '09');
        $this->add(230, 239, '0000000000');
        $this->add(240, 240, '');

        return $this;
    }

    /**
     * @param BoletoContract $boleto
     *
     * @return Bancoob
     * @throws ValidationException
     */
    public function segmentoQ(BoletoContract $boleto)
    {
        $this->iniciaDetalhe();
        $this->add(1, 3, Util::onlyNumbers($this->getCodigoBanco()));
        $this->add(4, 7, '0001');
        $this->add(8, 8, '3');
        $this->add(9, 13, Util::formatCnab('9', $this->iRegistrosLote, 5));
        $this->add(14, 14, 'Q');
        $this->add(15, 15, '');
        $this->add(16, 17, self::OCORRENCIA_REMESSA);
        if ($boleto->getStatus() == $boleto::STATUS_BAIXA) {
            $this->add(16, 17, self::OCORRENCIA_PEDIDO_BAIXA);
        }
        if ($boleto->getStatus() == $boleto::STATUS_ALTERACAO) {
            $this->add(16, 17, self::OCORRENCIA_ALT_OUTROS_DADOS);
        }
        $this->add(18, 18, strlen(Util::onlyNumbers($boleto->getPagador()->getDocumento())) == 14 ? 2 : 1);
        $this->add(19, 33, Util::formatCnab('9', Util::onlyNumbers($boleto->getPagador()->getDocumento()), 15));
        $this->add(34, 73, Util::formatCnab('X', $boleto->getPagador()->getNome(), 40));
        $this->add(74, 113, Util::formatCnab('X', $boleto->getPagador()->getEndereco(), 40));
        $this->add(114, 128, Util::formatCnab('X', $boleto->getPagador()->getBairro(), 15));
        $this->add(129, 133, Util::formatCnab('9', Util::onlyNumbers($boleto->getPagador()->getCep()), 5));
        $this->add(134, 136, Util::formatCnab('9', Util::onlyNumbers(substr($boleto->getPagador()->getCep(), 6, 9)), 3));
        $this->add(137, 151, Util::formatCnab('X', $boleto->getPagador()->getCidade(), 15));
        $this->add(152, 153, Util::formatCnab('X', $boleto->getPagador()->getUf(), 2));
        $this->add(154, 154, '0');
        $this->add(155, 169, '000000000000000');
        $this->add(170, 209, Util::formatCnab('X', '', 40));
        $this->add(210, 212, '000');
        $this->add(213, 240, Util::formatCnab('X', '', 28));

        if ($boleto->getSacadorAvalista()) {
            $this->add(154, 154, strlen(Util::onlyNumbers($boleto->getSacadorAvalista()->getDocumento())) == 14 ? 2 : 1);
            $this->add(155, 169, Util::formatCnab('9', Util::onlyNumbers($boleto->getSacadorAvalista()->getDocumento()), 15));
            $this->add(170, 209, Util::formatCnab('X', $boleto->getSacadorAvalista()->getNome(), 40));
        }

        return $this;
    }

    /**
     * @param BoletoContract $boleto
     *
     * @return Bancoob
     * @throws ValidationException
     */
    public function segmentoR(BoletoContract $boleto)
    {
        $this->iniciaDetalhe();
        $this->add(1, 3, Util::onlyNumbers($this->getCodigoBanco()));
        $this->add(4, 7, '0001');
        $this->add(8, 8, '3');
        $this->add(9, 13, Util::formatCnab('9', $this->iRegistrosLote, 5));
        $this->add(14, 14, 'R');
        $this->add(15, 15, '');
        $this->add(16, 17, self::OCORRENCIA_REMESSA);
        if ($boleto->getStatus() == $boleto::STATUS_BAIXA) {
            $this->add(16, 17, self::OCORRENCIA_PEDIDO_BAIXA);
        }
        if ($boleto->getStatus() == $boleto::STATUS_ALTERACAO) {
            $this->add(16, 17, self::OCORRENCIA_ALT_OUTROS_DADOS);
        }
        $this->add(18, 18, '0');
        $this->add(19, 26, '00000000');
        $this->add(27, 41, '000000000000000');
        $this->add(42, 42, '0');
        $this->add(43, 50, '00000000');
        $this->add(51, 65, '000000000000000');
        $this->add(66, 66, $boleto->getMulta() > 0 ? '2' : '0'); //0 = ISENTO | 1 = VALOR FIXO | 2 = PERCENTUAL
        $this->add(67, 74, $boleto->getMulta() > 0 ? $boleto->getDataVencimento()->copy()->addDay()->format('dmY') : '00000000');
        $this->add(75, 89, Util::formatCnab('9', $boleto->getMulta(), 15, 2));  //2,20 = 0000000000220
        $this->add(90, 199, '');
        $this->add(200, 207, '00000000');
        $this->add(208, 210, '000');
        $this->add(211, 215, '00000');
        $this->add(216, 216, '');
        $this->add(217, 228, '000000000000');
        $this->add(229, 230, '');
        $this->add(231, 231, '0');
        $this->add(232, 240, '');

        return $this;
    }

    /**
     * @return Bancoob
     * @throws ValidationException
     */
    protected function header()
    {
        $this->iniciaHeader();

        /**
         * HEADER DE ARQUIVO
         */
        $this->add(1, 3, Util::onlyNumbers($this->getCodigoBanco()));
        $this->add(4, 7, '0000');
        $this->add(8, 8, '0');
        $this->add(9, 17, '');
        $this->add(18, 18, strlen(Util::onlyNumbers($this->getBeneficiario()->getDocumento())) == 14 ? 2 : 1);
        $this->add(19, 32, Util::formatCnab('9', Util::onlyNumbers($this->getBeneficiario()->getDocumento()), 14));
        $this->add(33, 52, '');
        $this->add(53, 57, Util::formatCnab('9', $this->getAgencia(), 5));
        $this->add(58, 58, ! is_null($this->getAgenciaDv()) ? $this->getAgenciaDv() : CalculoDV::bancoobAgencia($this->getAgencia()));
        $this->add(59, 70, Util::formatCnab('9', $this->getConta(), 12));
        $this->add(71, 71, ! is_null($this->getContaDv()) ? $this->getContaDv() : CalculoDV::bancoobContaCorrente($this->getConta()));
        $this->add(72, 72, '0');
        $this->add(73, 102, Util::formatCnab('X', $this->getBeneficiario()->getNome(), 30));
        $this->add(103, 132, Util::formatCnab('X', 'SICOOB', 30));
        $this->add(133, 142, '');
        $this->add(143, 143, 1);
        $this->add(144, 151, date('dmY'));
        $this->add(152, 157, date('His'));
        $this->add(158, 163, Util::formatCnab('9', $this->getIdremessa(), 6));
        $this->add(164, 166, '081');
        $this->add(167, 171, '00000');
        $this->add(172, 191, '');
        $this->add(192, 211, '');
        $this->add(212, 240, '');

        return $this;
    }

    /**
     * @return Bancoob
     * @throws ValidationException
     */
    protected function headerLote()
    {
        $this->iniciaHeaderLote();

        /**
         * HEADER DE LOTE
         */
        $this->add(1, 3, Util::onlyNumbers($this->getCodigoBanco()));
        $this->add(4, 7, '0001');
        $this->add(8, 8, '1');
        $this->add(9, 9, 'R');
        $this->add(10, 11, '01');
        $this->add(12, 13, '');
        $this->add(14, 16, '040');
        $this->add(17, 17, '');
        $this->add(18, 18, strlen(Util::onlyNumbers($this->getBeneficiario()->getDocumento())) == 14 ? 2 : 1);
        $this->add(19, 33, Util::formatCnab('9', Util::onlyNumbers($this->getBeneficiario()->getDocumento()), 15));
        $this->add(34, 53, '');
        $this->add(54, 58, Util::formatCnab('9', $this->getAgencia(), 5));
        $this->add(59, 59, ! is_null($this->getAgenciaDv()) ? $this->getAgenciaDv() : CalculoDV::bancoobAgencia($this->getAgencia()));
        $this->add(60, 71, Util::formatCnab('9', $this->getConta(), 12));
        $this->add(72, 72, ! is_null($this->getContaDv()) ? $this->getContaDv() : CalculoDV::bancoobContaCorrente($this->getConta()));
        $this->add(73, 73, '');
        $this->add(74, 103, Util::formatCnab('X', $this->getBeneficiario()->getNome(), 30));
        $this->add(104, 183, '');
        $this->add(184, 191, Util::formatCnab('9', $this->getIdremessa(), 8));
        $this->add(192, 199, $this->getDataRemessa('dmY'));
        $this->add(200, 207, '00000000');
        $this->add(208, 240, '');

        return $this;
    }

    /**
     * @return Bancoob
     * @throws ValidationException
     */
    protected function trailerLote()
    {
        $this->iniciaTrailerLote();

        $valor = array_reduce($this->boletos, function ($valor, $boleto) {
            return $valor + $boleto->getValor();
        }, 0);

        $this->add(1, 3, Util::onlyNumbers($this->getCodigoBanco()));
        $this->add(4, 7, '0001');
        $this->add(8, 8, '5');
        $this->add(9, 17, '');
        $this->add(18, 23, Util::formatCnab('9', $this->getCountDetalhes() + 2, 6));
        $this->add(24, 29, Util::formatCnab('9', count($this->boletos), 6));
        $this->add(30, 46, Util::formatCnab('9', $valor, 17, 2));
        $this->add(47, 52, Util::formatCnab('9', 0, 6));
        $this->add(53, 69, Util::formatCnab('9', 0, 17, 2));
        $this->add(70, 115, Util::formatCnab('9', 0, 46));
        $this->add(116, 240, '');

        return $this;
    }

    /**
     * @return Bancoob
     * @throws ValidationException
     */
    protected function trailer()
    {
        $this->iniciaTrailer();

        $this->add(1, 3, Util::onlyNumbers($this->getCodigoBanco()));
        $this->add(4, 7, '9999');
        $this->add(8, 8, '9');
        $this->add(9, 17, '');
        $this->add(18, 23, Util::formatCnab('9', 1, 6));
        $this->add(24, 29, Util::formatCnab('9', $this->getCount(), 6));
        $this->add(30, 35, '000000');
        $this->add(36, 240, '');

        return $this;
    }
}
