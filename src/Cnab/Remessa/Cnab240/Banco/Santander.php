<?php

namespace Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab240\Banco;

use Eduardokum\LaravelBoleto\Util;
use Eduardokum\LaravelBoleto\CalculoDV;
use Eduardokum\LaravelBoleto\Exception\ValidationException;
use Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab240\AbstractRemessa;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;
use Eduardokum\LaravelBoleto\Contracts\Cnab\Remessa as RemessaContract;

class Santander extends AbstractRemessa implements RemessaContract
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
    const PROTESTO_SEM = '0';
    const PROTESTO_DIAS_CORRIDOS = '1';
    const PROTESTO_DIAS_UTEIS = '2';
    const PROTESTO_PERFIL_BENEFICIARIO = '3';
    const PROTESTO_AUTOMATICO = '9';

    public function __construct(array $params = [])
    {
        parent::__construct($params);
        $this->addCampoObrigatorio('codigoCliente');
        $this->addCampoObrigatorio('idremessa');
    }

    /**
     * Código do banco
     *
     * @var string
     */
    protected $codigoBanco = BoletoContract::COD_BANCO_SANTANDER;

    /**
     * Define as carteiras disponíveis para cada banco
     *
     * @var array
     */
    protected $carteiras = [101, 201];

    /**
     * Codigo do cliente junto ao banco.
     *
     * @var string
     */
    protected $codigoCliente;

    /**
     * Retorna o codigo do cliente.
     *
     * @return string
     */
    public function getCodigoCliente()
    {
        return $this->codigoCliente;
    }

    /**
     * Seta o codigo do cliente.
     *
     * @param mixed $codigoCliente
     * @return Santander
     */
    public function setCodigoCliente($codigoCliente)
    {
        $this->codigoCliente = $codigoCliente;

        return $this;
    }

    /**
     * @param BoletoContract $boleto
     *
     * @return Santander
     * @throws ValidationException
     */
    public function addBoleto(BoletoContract $boleto)
    {
        $this->boletos[] = $boleto;
        $this->segmentoP($boleto);

        if ($boleto->getStatus() == $boleto::STATUS_REGISTRO) {
            $this->segmentoQ($boleto);
            $this->segmentoR($boleto);
        }

        return $this;
    }

    /**
     * @param BoletoContract $boleto
     *
     * @return Santander
     * @throws ValidationException
     */
    protected function segmentoP(BoletoContract $boleto)
    {
        $this->iniciaDetalhe();
        $this->add(1, 3, Util::onlyNumbers($this->getCodigoBanco()));
        $this->add(4, 7, sprintf('%04d', $this->getIdremessa()));
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
        $this->add(18, 21, Util::formatCnab('9', $this->getAgencia(), 4));
        $this->add(22, 22, $this->getAgenciaDv() ?: Util::formatCnab('9', '', 1));
        $this->add(23, 31, Util::formatCnab('9', $this->getConta(), 9));
        $this->add(32, 32, ! is_null($this->getContaDv()) ? $this->getContaDv() : CalculoDV::santanderContaCorrente($this->getAgencia(), $this->getConta()));
        $this->add(33, 41, Util::formatCnab('9', $this->getConta(), 9));
        $this->add(42, 42, ! is_null($this->getContaDv()) ? $this->getContaDv() : CalculoDV::santanderContaCorrente($this->getAgencia(), $this->getConta()));
        $this->add(43, 44, '');
        $this->add(45, 57, Util::formatCnab('9', $boleto->getNossoNumero(), 13));
        $this->add(58, 58, Util::formatCnab('9', $this->getCarteiraNumero() > 200 ? '1' : '5', 1));
        $this->add(59, 59, Util::formatCnab('9', 1, 1));
        $this->add(60, 60, Util::formatCnab('9', 2, 1));
        $this->add(61, 61, '');
        $this->add(62, 62, '');
        $this->add(63, 77, Util::formatCnab('9', $boleto->getNumeroDocumento(), 15));
        $this->add(78, 85, $boleto->getDataVencimento()->format('dmY'));
        $this->add(86, 100, Util::formatCnab('9', $boleto->getValor(), 15, 2));
        $this->add(101, 104, Util::formatCnab('9', 0, 4));
        $this->add(105, 105, Util::formatCnab('9', 0, 1));
        $this->add(106, 106, '');
        $this->add(107, 108, Util::formatCnab('9', $boleto->getEspecieDocCodigo('02', 240), 2));
        $this->add(109, 109, Util::formatCnab('9', 'N', 1));
        $this->add(110, 117, $boleto->getDataDocumento()->format('dmY'));
        $this->add(118, 118, ($boleto->getJuros() !== null && $boleto->getJuros() > 0) ? '2' : '3');    //3 = ISENTO | 1 = R$ ao dia | 2 = % ao mês
        $this->add(119, 126, Util::formatCnab('9', ($boleto->getJuros() !== null && $boleto->getJuros() > 0) ? $boleto->getDataVencimento()->format('dmY') : 0, 8));
        $this->add(127, 141, Util::formatCnab('9', $boleto->getJuros(), 15, 5));
        $this->add(142, 142, $boleto->getDesconto() > 0 ? '1' : '0'); //0 = SEM DESCONTO | 1 = VALOR FIXO | 2 = PERCENTUAL
        $this->add(143, 150, $boleto->getDesconto() > 0 ? $boleto->getDataDesconto()->format('dmY') : '00000000');
        $this->add(151, 165, Util::formatCnab('9', $boleto->getDesconto(), 15, 2));
        $this->add(166, 180, Util::formatCnab('9', 0, 15, 2));
        $this->add(181, 195, Util::formatCnab('9', 0, 15, 2));
        $this->add(196, 220, Util::formatCnab('X', $boleto->getNumeroControle(), 25));
        $this->add(221, 221, self::PROTESTO_SEM);
        if ($boleto->getDiasProtesto() > 0) {
            $this->add(221, 221, self::PROTESTO_DIAS_UTEIS);
        }
        $this->add(222, 223, Util::formatCnab('9', $boleto->getDiasProtesto(), 2));
        $this->add(224, 224, $boleto->getDiasBaixaAutomatica() > 0 ? '1' : '3'); // 1 = Baixar/Devolver / 2 = Não Baixar / 3 = Perfil do Benefíciario (Configuração do Banco)
        $this->add(225, 225, Util::formatCnab('9', 0, 1));  //Zero Fixo
        $this->add(226, 227, Util::formatCnab('9', $boleto->getDiasBaixaAutomatica(), 2));  //Dias para Baixa
        $this->add(228, 229, Util::formatCnab('9', 0, 2));  // 00 = Real
        $this->add(230, 240, '');

        return $this;
    }

    /**
     * @param BoletoContract $boleto
     *
     * @return Santander
     * @throws ValidationException
     */
    public function segmentoQ(BoletoContract $boleto)
    {
        $this->iniciaDetalhe();

        $this->add(1, 3, Util::onlyNumbers($this->getCodigoBanco()));
        $this->add(4, 7, sprintf('%04d', $this->getIdremessa()));
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
        $this->add(170, 209, '');
        $this->add(210, 212, Util::formatCnab('9', '000', 3));
        $this->add(213, 215, Util::formatCnab('9', '000', 3));
        $this->add(216, 218, Util::formatCnab('9', '000', 3));
        $this->add(219, 221, Util::formatCnab('9', '000', 3));
        $this->add(222, 240, '');

        if ($boleto->getSacadorAvalista()) {
            $this->add(154, 154, strlen(Util::onlyNumbers($boleto->getSacadorAvalista()->getDocumento())) == 14 ? 2 : 1);
            $this->add(155, 169, Util::formatCnab('9', Util::onlyNumbers($boleto->getSacadorAvalista()->getDocumento()), 15));
            $this->add(170, 209, Util::formatCnab('X', $boleto->getSacadorAvalista()->getNome(), 30));
        }

        return $this;
    }

    /**
     * @param BoletoContract $boleto
     *
     * @return Santander
     * @throws ValidationException
     */
    public function segmentoR(BoletoContract $boleto)
    {
        $this->iniciaDetalhe();
        $this->add(1, 3, Util::onlyNumbers($this->getCodigoBanco()));
        $this->add(4, 7, sprintf('%04d', $this->getIdremessa()));
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
        $this->add(42, 65, '');
        $this->add(66, 66, '2'); //1 = VALOR FIXO | 2 = PERCENTUAL
        $this->add(67, 74, $boleto->getDataVencimento()->format('dmY'));
        $this->add(75, 89, Util::formatCnab('9', $boleto->getMulta(), 15, 2));  //2,20 = 0000000000220
        $this->add(90, 240, '');

        return $this;
    }

    /**
     * @return Santander
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
        $this->add(9, 16, '');
        $this->add(17, 17, strlen(Util::onlyNumbers($this->getBeneficiario()->getDocumento())) == 14 ? '2' : '1');
        $this->add(18, 32, Util::formatCnab('9', Util::onlyNumbers($this->getBeneficiario()->getDocumento()), 15));
        $this->add(33, 47, Util::formatCnab('9', $this->getCodigoTransmissao(), 15));
        $this->add(48, 72, '');
        $this->add(73, 102, Util::formatCnab('X', $this->getBeneficiario()->getNome(), 30));
        $this->add(103, 132, Util::formatCnab('X', 'Banco Santander', 30));
        $this->add(133, 142, '');
        $this->add(143, 143, '1');
        $this->add(144, 151, $this->getDataRemessa('dmY'));
        $this->add(152, 157, '');
        $this->add(158, 163, Util::formatCnab('9', $this->getIdremessa(), 6));
        $this->add(164, 166, Util::formatCnab('9', '040', 3));
        $this->add(164, 166, Util::formatCnab('9', '040', 3));
        $this->add(167, 240, '');

        return $this;
    }

    /**
     * Retorna o codigo de transmissão.
     *
     * @return string
     * @throws ValidationException
     */
    public function getCodigoTransmissao()
    {
        return Util::formatCnab('9', $this->getAgencia(), 4)
            . Util::formatCnab('9', '0000', 4)
            . Util::formatCnab('9', $this->getCodigoCliente(), 7);
    }

    /**
     * @return Santander
     * @throws ValidationException
     */
    protected function headerLote()
    {
        $this->iniciaHeaderLote();

        /**
         * HEADER DE LOTE
         */
        $this->add(1, 3, Util::onlyNumbers($this->getCodigoBanco()));
        $this->add(4, 7, sprintf('%04d', $this->getIdremessa()));
        $this->add(8, 8, '1');
        $this->add(9, 9, 'R');
        $this->add(10, 11, '01');
        $this->add(12, 13, '');
        $this->add(14, 16, Util::formatCnab('9', '030', 3));
        $this->add(17, 17, '');
        $this->add(18, 18, strlen(Util::onlyNumbers($this->getBeneficiario()->getDocumento())) == 14 ? '2' : '1');
        $this->add(19, 33, Util::formatCnab('9', Util::onlyNumbers($this->getBeneficiario()->getDocumento()), 15));
        $this->add(34, 53, '');
        $this->add(54, 68, Util::formatCnab('9', $this->getCodigoTransmissao(), 15));
        $this->add(69, 73, '');
        $this->add(74, 103, Util::formatCnab('X', $this->getBeneficiario()->getNome(), 30));
        $this->add(104, 143, '');
        $this->add(144, 183, '');
        $this->add(184, 191, sprintf('%08d', $this->getIdremessa()));
        $this->add(192, 199, date('dmY'));
        $this->add(200, 240, '');

        return $this;
    }

    /**
     * @return Santander
     * @throws ValidationException
     */
    protected function trailerLote()
    {
        $this->iniciaTrailerLote();

        $this->add(1, 3, Util::onlyNumbers($this->getCodigoBanco()));
        $this->add(4, 7, sprintf('%04d', $this->getIdremessa()));
        $this->add(8, 8, '5');
        $this->add(9, 17, '');
        $this->add(18, 23, Util::formatCnab('9', $this->getCountDetalhes() + 2, 6));
        $this->add(24, 240, '');

        return $this;
    }

    /**
     * @return Santander
     * @throws ValidationException
     */
    protected function trailer()
    {
        $this->iniciaTrailer();

        $this->add(1, 3, Util::onlyNumbers($this->getCodigoBanco()));
        $this->add(4, 7, '9999');
        $this->add(8, 8, '9');
        $this->add(9, 17, '');
        $this->add(18, 23, '000001');
        $this->add(24, 29, Util::formatCnab('9', $this->getCount(), 6));
        $this->add(30, 240, '');

        return $this;
    }
}
