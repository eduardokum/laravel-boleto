<?php
/**
 *   Copyright (c) 2016 Eduardo Gusmão
 *
 *   Permission is hereby granted, free of charge, to any person obtaining a
 *   copy of this software and associated documentation files (the "Software"),
 *   to deal in the Software without restriction, including without limitation
 *   the rights to use, copy, modify, merge, publish, distribute, sublicense,
 *   and/or sell copies of the Software, and to permit persons to whom the
 *   Software is furnished to do so, subject to the following conditions:
 *
 *   The above copyright notice and this permission notice shall be included in all
 *   copies or substantial portions of the Software.
 *
 *   THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 *   INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR
 *   PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 *   COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 *   WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR
 *   IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400\Banco;

use Eduardokum\LaravelBoleto\Cnab\Remessa\AbstractRemessa;
use Eduardokum\LaravelBoleto\Contracts\Cnab\Remessa as RemessaContract;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;
use Eduardokum\LaravelBoleto\Util;

class Bradesco extends AbstractRemessa implements RemessaContract
{

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

    /**
     * Código do banco
     * @var string
     */
    protected $codigoBanco = self::COD_BANCO_BRADESCO;

    /**
     * Define as carteiras disponíveis para cada banco
     * @var array
     */
    protected $carteiras = ['09', '28'];

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
    protected $fimArquivo = "\r\n";

    /**
     * Codigo do cliente junto ao banco.
     *
     * @var string
     */
    protected $codigoCliente;

    /**
     * Retorna o codigo do cliente.
     *
     * @return mixed
     */
    public function getCodigoCliente()
    {
        return $this->codigoCliente;
    }

    /**
     * Seta o codigo do cliente.
     *
     * @param mixed $codigoCliente
     *
     * @return Bradesco
     */
    public function setCodigoCliente($codigoCliente)
    {
        $this->codigoCliente = $codigoCliente;

        return $this;
    }

    protected function header()
    {
        $this->iniciaHeader();

        $this->add(1, 1, '0');
        $this->add(2, 2, '1');
        $this->add(3, 9, 'REMESSA');
        $this->add(10, 11, '01');
        $this->add(12, 26, Util::formatCnab('A', 'COBRANCA', 15));
        $this->add(27, 46, Util::formatCnab('N', $this->getCodigoCliente(), 20));
        $this->add(47, 76, Util::formatCnab('A', $this->getBeneficiario()->getNome(), 30));
        $this->add(77, 79, $this->getCodigoBanco());
        $this->add(80, 94, Util::formatCnab('A', 'Bradesco', 15));
        $this->add(95, 100, date('dmy'));
        $this->add(101, 108, '');
        $this->add(109, 110, 'MX');
        $this->add(111, 117, Util::formatCnab('N', $this->getIdremessa(), 7));
        $this->add(118, 394, '');
        $this->add(395, 400, Util::formatCnab('N', 1, 6));

        return $this;
    }



    public function addBoleto(BoletoContract $boleto)
    {
        $this->iniciaDetalhe();

        $beneficiario_id = '0' .
            Util::formatCnab('9', $this->getCarteiraNumero(), 3) .
            Util::formatCnab('9', $this->getAgencia(), 5) .
            Util::formatCnab('9', $this->getConta(), 7) .
            Util::formatCnab('9', $this->getContaDv(), 1);

        $this->add(1, 1, '1');
        $this->add(2, 6, Util::formatCnab('A', '', 5));
        $this->add(7, 7, '');
        $this->add(8, 12, Util::formatCnab('A', '', 5));
        $this->add(13, 19, Util::formatCnab('A', '', 7));
        $this->add(20, 20, '');
        $this->add(21, 37, Util::formatCnab('A', $beneficiario_id, 17));
        $this->add(38, 62, Util::formatCnab('A', '', 25)); // numero de controle
        $this->add(63, 65, $this->getCodigoBanco());
        $this->add(66, 66, $boleto->getMulta() > 0 ? '2' : '0');
        $this->add(67, 70, Util::formatCnab('N', $boleto->getMulta() === NULL ? '0' : $boleto->getMulta(), 4, 2));
        $this->add(71, 81, Util::formatCnab('N', $boleto->getNossoNumero(), 11));
        $this->add(82, 82, Util::modulo11($boleto->getCarteira().$boleto->getNossoNumero(), 2, 7, 0, 'P'));
        $this->add(83, 92, Util::formatCnab('N', 0, 10, 2));
        $this->add(93, 93, '2'); // 1 = Banco emite e Processa o registro. 2 = Cliente emite e o Banco somente processa o registro
        $this->add(94, 94, ''); // N= Não registra na cobrança. Diferente de N registra e emite Boleto.
        $this->add(95, 104, Util::formatCnab('A', '', 10));
        $this->add(105, 105, '');
        $this->add(106, 106, '2'); // 1 = emite aviso, e assume o endereço do Pagador constante do Arquivo-Remessa; 2 = não emite aviso;
        $this->add(107, 108, Util::formatCnab('A', '', 2));
        $this->add(109, 110, '01'); // REGISTRO
        if($boleto->getStatus() == $boleto::STATUS_BAIXA)
        {
            $this->add(109, 110, '02'); // BAIXA
        }
        if($boleto->getStatus() == $boleto::STATUS_ALTERACAO)
        {
            $this->add(109, 110, '06'); // ALTERAR VENCIMENTO
        }
        $this->add(111, 120, Util::formatCnab('A', $boleto->getNumeroDocumento(), 10));
        $this->add(121, 126, $boleto->getDataVencimento()->format('dmy'));
        $this->add(127, 139, Util::formatCnab('N', $boleto->getValor(), 13, 2));
        $this->add(140, 142, Util::formatCnab('A','', 3));
        $this->add(143, 147, '00000');
        $this->add(148, 149, $boleto->getEspecieDocCodigo());
        $this->add(150, 150, 'N');
        $this->add(151, 156, $boleto->getDataDocumento()->format('dmy'));

        $this->add(157, 158, '00');
        $this->add(159, 160, '00');

        if($boleto->getDiasProtesto() > 0)
        {
            $this->add(157, 158, '06');
            $this->add(159, 160, Util::formatCnab('N', $boleto->getDiasProtesto(), 2));
        }

        $juros = 0;
        if($boleto->getJuros() > 0)
        {
            $juros = Util::percent($boleto->getValor(), $boleto->getJuros())/30;
        }
        $this->add(161, 173, Util::formatCnab('9', $juros, 13, 2));
        $this->add(174, 179, '000000');
        $this->add(180, 192, Util::formatCnab('N', 0, 13, 2));
        $this->add(193, 205, Util::formatCnab('N', 0, 13, 2));
        $this->add(206, 218, Util::formatCnab('N', $boleto->getDescontosAbatimentos(), 13, 2));

        $this->add(219, 220, strlen(Util::onlyNumbers($boleto->getPagador()->getDocumento())) == 14 ? '02' : '01');
        $this->add(221, 234, Util::formatCnab('9L', $boleto->getPagador()->getDocumento(), 14));
        $this->add(235, 274, Util::formatCnab('X', $boleto->getPagador()->getNome(), 40));
        $this->add(275, 314, Util::formatCnab('X', $boleto->getPagador()->getEndereco(), 40));
        $this->add(315, 326, Util::formatCnab('X', $boleto->getPagador()->getBairro(), 12));
        $this->add(327, 334, Util::formatCnab('9L', $boleto->getPagador()->getCep(), 8));
        $this->add(335, 394, Util::formatCnab('X', $boleto->getSacadorAvalista() ? $boleto->getSacadorAvalista()->getNome() : '', 60));
        $this->add(395, 400, Util::formatCnab('N', $this->iRegistros+1, 6));

        return $this;
    }

    protected function trailer()
    {
        $this->iniciaTrailer();

        $this->add(1, 1, '9');
        $this->add(2, 394, '');
        $this->add(395, 400, Util::formatCnab('N', $this->getCount(), 6));

        return $this;
    }

    public function isValid()
    {
        if(empty($this->getCodigoCliente()) ||empty($this->getContaDv()) || !parent::isValid())
        {
            return false;
        }

        return true;
    }


}