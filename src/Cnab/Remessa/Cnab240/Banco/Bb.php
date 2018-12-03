<?php
/**
 * Created by PhpStorm.
 * User: simetriatecnologia
 * Date: 15/09/16
 * Time: 14:02
 */

namespace Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab240\Banco;

use Eduardokum\LaravelBoleto\CalculoDV;
use Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab240\AbstractRemessa;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;
use Eduardokum\LaravelBoleto\Contracts\Cnab\Remessa as RemessaContract;
use Eduardokum\LaravelBoleto\Util;

class Bb extends AbstractRemessa implements RemessaContract
{

    const OCORRENCIA_REMESSA = '01';
    const OCORRENCIA_PEDIDO_BAIXA = '02';
    const OCORRENCIA_CONCESSAO_ABATIMENTO = '04';
    const OCORRENCIA_CANC_ABATIMENTO = '05';
    const OCORRENCIA_ALT_VENCIMENTO = '06';
    const OCORRENCIA_CONCESSAO_DESCONTO = '07';
    const OCORRENCIA_CANC_DESCONTO = '08';
    const OCORRENCIA_PROTESTAR = '09';
    const OCORRENCIA_CANC_PROTESTO = '10';
    const OCORRENCIA_RECUSA_SACADO = '30';
    const OCORRENCIA_ALT_OUTROS_DADOS = '31';
    const OCORRENCIA_ALT_MODALIDADE = '40';

    const PROTESTO_SEM = '0';
    const PROTESTO_DIAS_CORRIDOS = '1';
    const PROTESTO_DIAS_UTEIS = '2';
    const PROTESTO_NAO_PROTESTAR = '3';

    public function __construct(array $params = [])
    {
        parent::__construct($params);
        $this->addCampoObrigatorio('convenio', 'convenioLider', 'variacaoCarteira');
    }

    /**
     * Código do banco
     *
     * @var string
     */
    protected $codigoBanco = BoletoContract::COD_BANCO_BB;

    /**
     * Define as carteiras disponíveis para cada banco
     *
     * @var array
     */
    protected $carteiras = ['11', '12', '17', '31', '51'];

    /**
     * Convenio com o banco
     *
     * @var string
     */
    protected $convenio;

    /**
     * Convenio lider com o banco
     *
     * @var string
     */
    protected $convenioLider;

    /**
     * Variação da carteira
     *
     * @var string
     */
    protected $variacaoCarteira;

    /**
     * @return mixed
     */
    public function getConvenio()
    {
        return $this->convenio;
    }

    /**
     * @param mixed $convenio
     *
     * @return Bb
     */
    public function setConvenio($convenio)
    {
        $this->convenio = ltrim($convenio, 0);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getConvenioLider()
    {
        return $this->convenioLider ? $this->convenioLider : $this->getConvenio();
    }

    /**
     * @param mixed $convenioLider
     *
     * @return Bb
     */
    public function setConvenioLider($convenioLider)
    {
        $this->convenioLider = $convenioLider;

        return $this;
    }

    /**
     * Retorna variação da carteira
     *
     * @return string
     */
    public function getVariacaoCarteira()
    {
        return $this->variacaoCarteira;
    }

    /**
     * Seta a variação da carteira
     *
     * @param string $variacaoCarteira
     *
     * @return Bb
     */
    public function setVariacaoCarteira($variacaoCarteira)
    {
        $this->variacaoCarteira = $variacaoCarteira;

        return $this;
    }

    /**
     * @param BoletoContract $boleto
     *
     * @return $this
     * @throws \Exception
     */
    public function addBoleto(BoletoContract $boleto)
    {
        $this->boletos[] = $boleto;
        $this->segmentoP($boleto);
        $this->segmentoQ($boleto);
		if($boleto->getStatus() == $boleto::STATUS_REGISTRO) {
			$this->segmentoR($boleto);
		}
        return $this;
    }

    /**
     * @param BoletoContract $boleto
     *
     * @return $this
     * @throws \Exception
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
        $this->add(18, 22, Util::formatCnab('9', $this->getAgencia(), 5));
        $this->add(23, 23, CalculoDV::bbAgencia($this->getAgencia()));
        $this->add(24, 35, Util::formatCnab('9', $this->getConta(), 12));
        $this->add(36, 36, CalculoDV::bbContaCorrente($this->getConta()));
        $this->add(37, 37, '');
        $this->add(38, 57, Util::formatCnab('X', $this->nossoNumero($boleto), 20));
        $this->add(58, 58, '1'); //'1' = Cobrança Simples
        $this->add(59, 59, '');
        $this->add(60, 60, '');
        $this->add(61, 61, '');
        $this->add(62, 62, '');
        $this->add(63, 77, Util::formatCnab('9', $boleto->getNumeroDocumento(), 15)); //valor do número do documento
        $this->add(78, 85, $boleto->getDataVencimento()->format('dmY'));
        $this->add(86, 100, Util::formatCnab('9', $boleto->getValor(), 15, 2));
        $this->add(101, 105, '00000');
        $this->add(106, 106, '0');
        $this->add(107, 108, Util::formatCnab('9', $boleto->getEspecieDocCodigo(), 2));
        $this->add(109, 109, Util::formatCnab('9', $boleto->getAceite(), 1));
        $this->add(110, 117, $boleto->getDataDocumento()->format('dmY'));
        $this->add(118, 118, $boleto->getJuros() ? '2' : '3'); //'1' = Valor por Dia, '2' = Taxa Mensal, '3' = Isento
        $this->add(119, 126, $boleto->getDataVencimento()->format('dmY'));
        $this->add(127, 141, Util::formatCnab('9', $boleto->getJuros(), 15, 2)); //Valor da mora/dia ou Taxa mensal
        $this->add(142, 142, $boleto->getDesconto() > 0 ? '1' : '0'); // Se houver desconto '1' = Valor Fixo Até a Data Informada, Se não houver envia o 0
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
        $this->add(225, 227, '000');
        $this->add(228, 229, Util::formatCnab('9', $boleto->getMoeda(), 2));
        $this->add(230, 239, '0000000000');
        $this->add(240, 240, '');

        return $this;
    }

    /**
     * @param BoletoContract $boleto
     *
     * @return $this
     * @throws \Exception
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
        if ($boleto->getStatus() == $boleto::STATUS_ALTERACAO_DATA) {
            $this->add(16, 17, self::OCORRENCIA_ALT_VENCIMENTO);
        }
        if ($boleto->getStatus() == $boleto::STATUS_CUSTOM) {
            $this->add(16, 17, sprintf('%2.02s', $boleto->getComando()));
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
        $this->add(170, 209, '');
        $this->add(210, 212, '000');
        $this->add(213, 240, '');

        if($boleto->getSacadorAvalista()) {
            $this->add(154, 154, strlen(Util::onlyNumbers($boleto->getSacadorAvalista()->getDocumento())) == 14 ? 2 : 1);
            $this->add(155, 169, Util::formatCnab('9', Util::onlyNumbers($boleto->getSacadorAvalista()->getDocumento()), 15));
            $this->add(170, 209, Util::formatCnab('X', $boleto->getSacadorAvalista()->getNome(), 30));
        }

        return $this;
    }

    /**
     * @param BoletoContract $boleto
     *
     * @return $this
     * @throws \Exception
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
        if ($boleto->getStatus() == $boleto::STATUS_ALTERACAO_DATA) {
            $this->add(16, 17, self::OCORRENCIA_ALT_VENCIMENTO);
        }
        if ($boleto->getStatus() == $boleto::STATUS_CUSTOM) {
            $this->add(16, 17, sprintf('%2.02s', $boleto->getComando()));
        }
        $this->add(18, 18, '0');
        $this->add(19, 26, '00000000');
        $this->add(27, 41, '000000000000000');
        $this->add(42, 42, '0');
        $this->add(43, 50, '00000000');
        $this->add(51, 65, '000000000000000');
        $this->add(66, 66, $boleto->getMulta() > 0 ? '2' : '0'); //0 = ISENTO | 1 = VALOR FIXO | 2 = PERCENTUAL
        $this->add(67, 74, $boleto->getDataVencimento()->format('dmY'));
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
     * @return $this
     * @throws \Exception
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
        $this->add(33, 41, Util::formatCnab('9', Util::onlyNumbers($this->getConvenio()), 9));
        $this->add(42, 45, '0014');
        $this->add(46, 47, Util::formatCnab('9', $this->getCarteira(), 2));
        $this->add(48, 50, Util::formatCnab('9', $this->getVariacaoCarteira(), 3));
        $this->add(51, 52, '');
        $this->add(53, 57, Util::formatCnab('9', $this->getAgencia(), 5));
        $this->add(58, 58, CalculoDV::bbAgencia($this->getAgencia()));
        $this->add(59, 70, Util::formatCnab('9', $this->getConta(), 12));
        $this->add(71, 71, CalculoDV::bbContaCorrente($this->getConta()));
        $this->add(72, 72, '');
        $this->add(73, 102, Util::formatCnab('X', $this->getBeneficiario()->getNome(), 30));
        $this->add(103, 132, Util::formatCnab('X', 'BANCO DO BRASIL S.A.', 30));
        $this->add(133, 142, '');
        $this->add(143, 143, 1);
        $this->add(144, 151, $this->getDataRemessa('dmY'));
        $this->add(152, 157, date('His'));
        $this->add(158, 163, '000000');
        $this->add(164, 166, '084');
        $this->add(167, 171, '01600');
        $this->add(172, 211, '');
        $this->add(212, 240, '');

        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
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
        $this->add(14, 16, '042');
        $this->add(17, 17, '');
        $this->add(18, 18, strlen(Util::onlyNumbers($this->getBeneficiario()->getDocumento())) == 14 ? 2 : 1);
        $this->add(19, 33, Util::formatCnab('9', Util::onlyNumbers($this->getBeneficiario()->getDocumento()), 15));
        $this->add(34, 42, Util::formatCnab('9', Util::onlyNumbers($this->getConvenio()), 9));
        $this->add(43, 46, '0014');
        $this->add(47, 48, Util::formatCnab('9', $this->getCarteira(), 2));
        $this->add(49, 51, Util::formatCnab('9', $this->getVariacaoCarteira(), 3));
        $this->add(52, 53, '');
        $this->add(54, 58, Util::formatCnab('9', $this->getAgencia(), 5));
        $this->add(59, 59, CalculoDV::bbAgencia($this->getAgencia()));
        $this->add(60, 71, Util::formatCnab('9', $this->getConta(), 12));
        $this->add(72, 72, CalculoDV::bbContaCorrente($this->getConta()));
        $this->add(73, 73, '');
        $this->add(74, 103, Util::formatCnab('X', $this->getBeneficiario()->getNome(), 30));
        $this->add(104, 183, '');
        $this->add(184, 191, '00000000');
        $this->add(192, 199, date('dmY'));
        $this->add(200, 207, '00000000');
        $this->add(208, 240, '');

        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    protected function trailerLote()
    {
        $this->iniciaTrailerLote();

        $this->add(1, 3, Util::onlyNumbers($this->getCodigoBanco()));
        $this->add(4, 7, '0001');
        $this->add(8, 8, '5');
        $this->add(9, 17, '');
        $this->add(18, 23, Util::formatCnab('9', $this->getCountDetalhes() + 2, 6));
        $this->add(24, 240, '');

        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
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
        $this->add(30, 35, '000001');
        $this->add(36, 240, '');

        return $this;
    }

    /**
     * @param BoletoContract $boleto
     *
     * @return mixed|string
     */
    private function nossoNumero(BoletoContract $boleto) {
        $convenio = (int) Util::onlyNumbers($this->getConvenio());
        if ($convenio > 1000000) {
            return $boleto->getNossoNumero();
        }
        return $boleto->getNossoNumero() . CalculoDV::bbNossoNumero($boleto->getNossoNumero());
    }
}
