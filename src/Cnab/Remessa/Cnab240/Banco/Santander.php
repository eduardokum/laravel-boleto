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

class Santander extends AbstractRemessa implements RemessaContract
{
    public function __construct(array $params = [])
    {
        parent::__construct($params);
        $this->addCampoObrigatorio('codigoCliente');
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
     * @param  mixed $codigoCliente
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
     * @return $this
     * @throws \Exception
     */
    public function addBoleto(BoletoContract $boleto)
    {
        $this->boletos[] = $boleto;
        $this->segmentoP($boleto);
        $this->segmentoQ($boleto);

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
        $this->add(16, 17, Util::formatCnab('9', 01, 2));
        $this->add(18, 21, Util::formatCnab('9', $this->getAgencia(), 4));
        $this->add(22, 22, Util::formatCnab('9', '', 1));
        $this->add(23, 31, Util::formatCnab('9', $this->getConta(), 9));
        $this->add(32, 32, $this->getContaDv() ?: CalculoDV::santanderContaCorrente($this->getAgencia(), $this->getConta()));
        $this->add(33, 41, Util::formatCnab('9', $this->getConta(), 9));
        $this->add(42, 42, $this->getContaDv() ?: CalculoDV::santanderContaCorrente($this->getAgencia(), $this->getConta()));
        $this->add(43, 44, '');
        $this->add(45, 57, Util::formatCnab('9', $boleto->getNossoNumero(), 13));
        $this->add(58, 58, Util::formatCnab('9', $this->getCarteira(), 1));
        $this->add(59, 59, Util::formatCnab('9', 1, 1));
        $this->add(60, 60, Util::formatCnab('9', 2, 1));
        $this->add(61, 61, '');
        $this->add(62, 62, '');
        $this->add(63, 77, Util::formatCnab('9', $boleto->getNumeroControle(), 15));
        $this->add(78, 85, $boleto->getDataVencimento()->format('dmY'));
        $this->add(86, 100, Util::formatCnab('9', $boleto->getValor(), 15, 2));
        $this->add(101, 104, Util::formatCnab('9', 0, 4));
        $this->add(105, 105, Util::formatCnab('9', 0, 1));
        $this->add(106, 106, '');
        $this->add(107, 108, Util::formatCnab('9', $boleto->getEspecieDocCodigo(), 2));
        $this->add(109, 109, Util::formatCnab('9', 'N', 1));
        $this->add(110, 117, $boleto->getDataDocumento()->format('dmY'));
        $this->add(118, 118, 1);
        $this->add(119, 126, Util::formatCnab('9', $boleto->getDataVencimento()->format('dmY'), 8));
        $this->add(127, 141, Util::formatCnab('9', $boleto->getMoraDia(), 15, 2));
        $this->add(142, 142, Util::formatCnab('9', '', 1));
        $this->add(143, 150, Util::formatCnab('9', $boleto->getDataDesconto()->format('dmY'), 8));
        $this->add(151, 165, Util::formatCnab('9', $boleto->getDesconto(), 15, 2));
        $this->add(166, 180, Util::formatCnab('9', 0, 15, 2));
        $this->add(181, 195, Util::formatCnab('9', 0, 15, 2));
        $this->add(196, 220, '');
        $this->add(221, 221, Util::formatCnab('9', 0, 1));
        $this->add(222, 223, Util::formatCnab('9', 0, 2));
        $this->add(224, 224, Util::formatCnab('9', 2, 1));
        $this->add(225, 225, Util::formatCnab('9', 0, 1));
        $this->add(226, 227, Util::formatCnab('9', 0, 2));
        $this->add(228, 229, Util::formatCnab('9', 0, 2));
        $this->add(230, 240, '');

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
        $this->add(16, 17, Util::formatCnab('9', 01, 2));
        $this->add(18, 18, Util::formatCnab('9', 1, 1));
        $this->add(19, 33, Util::formatCnab('9', Util::onlyNumbers($boleto->getPagador()->getDocumento()), 15));
        $this->add(34, 73, Util::formatCnab('X', $boleto->getPagador()->getNome(), 40));
        $this->add(74, 113, Util::formatCnab('X', $boleto->getPagador()->getEndereco(), 40));
        $this->add(114, 128, Util::formatCnab('X', $boleto->getPagador()->getBairro(), 15));
        $this->add(129, 133, Util::formatCnab('9', Util::onlyNumbers($boleto->getPagador()->getCep()), 5));
        $this->add(134, 136, Util::formatCnab('9', Util::onlyNumbers(substr($boleto->getPagador()->getCep(), 6, 9)), 3));
        $this->add(137, 151, Util::formatCnab('X', $boleto->getPagador()->getCidade(), 15));
        $this->add(152, 153, Util::formatCnab('X', $boleto->getPagador()->getUf(), 2));
        $this->add(154, 154, Util::formatCnab('9', 1, 1));
        $this->add(155, 169, Util::formatCnab('9', Util::onlyNumbers($boleto->getPagador()->getDocumento()), 15));
        $this->add(170, 209, Util::formatCnab('X', '', 40));
        $this->add(210, 212, '000');
        $this->add(213, 215, '000');
        $this->add(216, 218, '000');
        $this->add(218, 221, '000');
        $this->add(218, 240, '');

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
        $this->add(158, 163, Util::formatCnab('9', 0, 6));
        $this->add(164, 166, Util::formatCnab('9', '040', 3));
        $this->add(164, 166, Util::formatCnab('9', '040', 3));
        $this->add(167, 240, '');

        return $this;
    }

    /**
     * Retorna o codigo de transmissão.
     *
     * @return string
     * @throws \Exception
     */
    public function getCodigoTransmissao()
    {
        return Util::formatCnab('9', $this->getAgencia(), 4)
        . Util::formatCnab('9', $this->getCodigoCliente(), 8)
        . Util::formatCnab('9', $this->getConta(), 8);
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
        $this->add(184, 191, Util::formatCnab('9', 0, 8));
        $this->add(192, 199, date('dmY'));
        $this->add(200, 240, '');

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
        $this->add(18, 23, Util::formatCnab('9', ($this->iRegistrosLote + 2), 6));
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
        $this->add(18, 23, '000001');
        $this->add(24, 29, Util::formatCnab('9', count($this->aRegistros) + 1, 6));
        $this->add(30, 240, '');

        return $this;
    }
}
