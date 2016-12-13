<?php
/**
 * Created by PhpStorm.
 * User: simetriatecnologia
 * Date: 15/09/16
 * Time: 14:02
 */

namespace Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab240\Banco;

use Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab240\AbstractRemessa;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;
use Eduardokum\LaravelBoleto\Contracts\Cnab\Remessa as RemessaContract;
use Eduardokum\LaravelBoleto\Util;

class Santander extends AbstractRemessa implements RemessaContract
{

    const DM_DUPLICATA_MERCANTIL = 02;
    const DS_DUPLICATA_DE_SERVICO = 04;
    const LC_LETRA_DE_CÂMBIO_SOMENTE_PARA_BANCO_353 = 07;
    const LC_LETRA_DE_CÂMBIO_SOMENTE_PARA_BANCO_008 = 30;
    const NP_NOTA_PROMISSORIA = 12;
    const NR_NOTA_PROMISSORIA_RURAL = 13;
    const RC_RECIBO = 17;
    const AP_APOLICE_DE_SEGURO = 20;
    const CH_CHEQUE = 97;
    const ND_NOTA_PROMISSORIA_DIRETA = 98;

    /**
     * Código do banco
     *
     * @var string
     */
    protected $codigoBanco = BoletoContract::COD_BANCO_SANTANDER;

    /**
     * Tipo de inscrição da empresa
     *
     * @var string
     */
    protected $tipoInscricaoEmpresa;

    /**
     * Numero de inscrição da empresa
     *
     * @var string
     */
    protected $numeroInscricaoEmpresa;


    /**
     * Define as carteiras disponíveis para cada banco
     *
     * @var array
     */
    protected $carteiras = [101, 201];

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
     * Quantidade de registros do lote.
     */
    private $qtyRegistrosLote;

    public function addBoleto(BoletoContract $boleto, $nSequencialLote = null)
    {
        $this->iniciaDetalhe();
        $this->segmentoP($nSequencialLote + $nSequencialLote + 1, $boleto);
        $this->segmentoQ($nSequencialLote + $nSequencialLote + 2, $boleto);

        return $this;
    }

    /**
     * @param integer        $nSequencialLote
     * @param BoletoContract $boleto
     *
     * @return $this
     * @throws \Exception
     */
    protected function segmentoP($nSequencialLote, BoletoContract $boleto)
    {
        $this->iniciaDetalhe();
        $this->add(1, 3, Util::onlyNumbers($this->getCodigoBanco())); //Código do Banco
        $this->add(4, 7, Util::formatCnab(9, 0001, 4)); // Numero do lote remessa
        $this->add(8, 8, Util::formatCnab(9, 3, 1)); // Numero do lote remessa
        $this->add(9, 13, Util::formatCnab(9, $nSequencialLote, 5)); // Nº sequencial do registro de lote
        $this->add(14, 14, Util::formatCnab('9', 'P', 1)); // Nº sequencial do registro de lote
        $this->add(15, 15, ''); // Reservado (Uso Banco)
        $this->add(16, 17, Util::formatCnab(9, 01, 2)); // Código de movimento remessa
        $this->add(18, 21, Util::formatCnab(9, $this->getAgencia(), 4)); // Agência do cedente
        $this->add(22, 22, Util::formatCnab(9, '', 1)); // Digito verificador da Agência do cedente
        $this->add(23, 31, Util::formatCnab(9, $this->getConta(), 9)); // Numero da conta corrente
        $this->add(32, 32, Util::formatCnab(9, $this->getContaDv(), 1)); // Digito verificador da conta corrente
        $this->add(33, 41, Util::formatCnab(9, $this->getConta(), 9)); // Conta Cobrança
        $this->add(42, 42, Util::formatCnab(9, $this->getContaDv(), 1)); // Digito  da Conta Cobrança
        $this->add(43, 44, ''); // Reservado (Uso Banco)

        $this->add(45, 57, Util::formatCnab(9, $boleto->getNossoNumero(), 13)); // Nosso Número

        $this->add(58, 58, Util::formatCnab(9, $this->getCarteira(), 1)); // Tipo de Cobrança

        $this->add(59, 59, Util::formatCnab(9, 1, 1)); // Forma de Cadastramento
        $this->add(60, 60, Util::formatCnab(9, 2, 1)); // Tipo de documento
        $this->add(61, 61, ''); // Reservado (Uso Banco)
        $this->add(62, 62, ''); // Reservado (Uso Banco)
        //
        $this->add(63, 77, Util::formatCnab(9, $boleto->getNumero(), 15)); // Seu Número
        $this->add(78, 85, $boleto->getDataVencimento()->format('dmY')); // Data de vencimento do título
        $this->add(86, 100, Util::formatCnab(9, $boleto->getValor(), 15, 2)); // Valor nominal do título
        $this->add(101, 104, Util::formatCnab(9, 0, 4)); //Agência encarregada da cobrança
        $this->add(105, 105, Util::formatCnab(9, 0, 1)); //Dígito da Agência do Cedente
        $this->add(106, 106, ''); //Reservado (uso Banco)
        $this->add(107, 108, Util::formatCnab(9, self::DS_DUPLICATA_DE_SERVICO, 2)); //Espécie do título
        $this->add(109, 109, Util::formatCnab('9', 'N', 1)); //Identif. de título Aceito/Não Aceito
        $this->add(110, 117, date('dmY')); //Data da emissão do título

        $juros = 0;
        if ($boleto->getJuros() > 0) {
            $juros = Util::percent($boleto->getValor(), $boleto->getJuros())/30;
        }
        $this->add(118, 118, Util::formatCnab(9, '', 1)); //Código do juros de mora
        $this->add(119, 126, Util::formatCnab(9, $boleto->getDataVencimento()->format('dmY'), 8)); //Data do juros de mora / data de vencimento do titulo
        $this->add(127, 141, Util::formatCnab(9, $juros, 15, 2)); //Valor da mora/dia ou Taxa mensal
        $this->add(142, 142, Util::formatCnab(9, '', 1)); //Código do desconto 1
        $this->add(143, 150, Util::formatCnab(9, $boleto->getDataVencimento()->format('dmY'), 8)); //Data de desconto 1
        $this->add(151, 165, Util::formatCnab(9, 0, 15, 2)); //Valor ou Percentual do desconto concedido //TODO
        $this->add(166, 180, Util::formatCnab(9, 0, 15, 2)); //Valor do IOF a ser recolhido
        $this->add(181, 195, Util::formatCnab(9, $boleto->getDescontosAbatimentos(), 15, 2)); //Valor do abatimento
        $this->add(196, 220, ''); //Identificação do título na empresa
        $this->add(221, 221, Util::formatCnab(9, 0, 1)); //Código para protesto
        $this->add(222, 223, Util::formatCnab(9, 0, 2)); //Número de dias para protesto
        $this->add(224, 224, Util::formatCnab(9, 2, 1)); //Código para Baixa/Devolução
        $this->add(225, 225, Util::formatCnab(9, 0, 1)); // Reservado (uso Banco)
        $this->add(226, 227, Util::formatCnab(9, 0, 2)); // Número de dias para Baixa/Devolução
        $this->add(228, 229, Util::formatCnab(9, 0, 2)); // Código da moeda
        $this->add(230, 240, ''); // Reservado (Uso Banco)

        return $this;
    }

    /**
     * @param integer        $nSequencialLote
     * @param BoletoContract $boleto
     *
     * @throws \Exception
     */
    public function segmentoQ($nSequencialLote, BoletoContract $boleto)
    {
        $this->qtyRegistrosLote = $nSequencialLote;
        $this->iniciaDetalhe();

        $this->add(1, 3, Util::onlyNumbers($this->getCodigoBanco())); //Código do Banco
        $this->add(4, 7, Util::formatCnab(9, 0001, 4)); // Numero do lote remessa
        $this->add(8, 8, Util::formatCnab(9, 3, 1)); // Numero do lote remessa
        $this->add(9, 13, Util::formatCnab(9, $nSequencialLote, 5)); // Nº sequencial do registro de lote
        $this->add(14, 14, Util::formatCnab('9', 'Q', 1)); // Nº sequencial do registro de lote
        $this->add(15, 15, ''); // Reservado (Uso Banco)
        $this->add(16, 17, Util::formatCnab(9, 01, 2)); // Código de movimento remessa
        $this->add(18, 18, Util::formatCnab(9, 1, 1)); // Tipo de inscrição do sacado
        $this->add(19, 33, Util::formatCnab(9, Util::onlyNumbers($boleto->getPagador()->getDocumento()), 15)); // Número de inscrição do sacado
        $this->add(34, 73, Util::formatCnab('X', $boleto->getPagador()->getNome(), 40)); // Nome do pagador/Sacado
        $this->add(74, 113, Util::formatCnab('X', $boleto->getPagador()->getEndereco(), 40)); // Endereço do pagador/Sacado
        $this->add(114, 128, Util::formatCnab('X', $boleto->getPagador()->getBairro(), 15)); // Bairro do pagador/Sacado
        $this->add(129, 133, Util::formatCnab(9, Util::onlyNumbers($boleto->getPagador()->getCep()), 5)); // CEP do pagador/Sacado
        $this->add(134, 136, Util::formatCnab(9, Util::onlyNumbers(substr($boleto->getPagador()->getCep(), 6, 9)), 3)); //SUFIXO do cep do pagador/Sacado
        $this->add(137, 151, Util::formatCnab('X', $boleto->getPagador()->getCidade(), 15)); // cidade do sacado
        $this->add(152, 153, Util::formatCnab('X', $boleto->getPagador()->getUf(), 2)); // Uf do sacado
        $this->add(154, 154, Util::formatCnab(9, 1, 1)); // Tipo de inscrição do sacado
        $this->add(155, 169, Util::formatCnab(9, Util::onlyNumbers($boleto->getPagador()->getDocumento()), 15)); // Tipo de inscrição do sacado
        $this->add(170, 209, Util::formatCnab('X', '', 40)); // Nome do Sacador
        $this->add(210, 212, Util::formatCnab(9, 0, 3)); // Identificador de carne 000 - Não possui, 001 - Possui Carné
        $this->add(213, 215, Util::formatCnab(9, 0, 3)); // Sequencial da parcela
        $this->add(216, 218, Util::formatCnab(9, 0, 3)); // Quantidade total de parcelas
        $this->add(218, 221, Util::formatCnab(9, 0, 3)); // Número do plano
        $this->add(218, 240, ''); // Reservado (Uso Banco)

    }

    public function isValid()
    {
        if (empty($this->getCodigoCliente()) || !parent::isValid()) {
            return false;
        }

        return true;
    }

    protected function header()
    {
        $this->iniciaHeader();

        /**
         * HEADER DE ARQUIVO
         */
        $this->add(1, 3, Util::onlyNumbers($this->getCodigoBanco())); //Codigo do banco
        $this->add(4, 7, '0000'); // Lote de Serviço
        $this->add(8, 8, '0'); // Tipo de Registro
        $this->add(9, 16, ''); // Reservados (Uso Banco)
        $this->add(17, 17, strlen(Util::onlyNumbers($this->getBeneficiario()->getDocumento())) == 14 ? '2' : '1'); // Tipo de inscrição da empresa
        $this->add(18, 32, Util::formatCnab('9L', $this->getBeneficiario()->getDocumento(), 14)); // Numero de inscrição da empresa
        $this->add(33, 47, Util::formatCnab(9, $this->getCodigoTransmissao(), 15)); // Codigo de Transmissão
        $this->add(48, 72, ''); // Reservados (Uso Banco)
        $this->add(73, 102, Util::formatCnab('X', $this->getBeneficiario()->getNome(), 30)); // Nome da empresa
        $this->add(103, 132, Util::formatCnab('X', 'Banco Santander', 30)); // Nome do Banco
        $this->add(133, 142, ''); // Reservados (Uso Banco)
        $this->add(143, 143, '1'); // Codigo remessa
        $this->add(144, 151, date('dmY')); // Data de Geracao do arquivo
        $this->add(152, 157, ''); // Reservado (Uso Banco)
        $this->add(158, 163, Util::formatCnab(9, 0, 6)); // Numero Sequencial do arquivo
        $this->add(164, 166, Util::formatCnab('9', '040', 3)); // Versão do layout
        $this->add(164, 166, Util::formatCnab('9', '040', 3)); // Versão do layout
        $this->add(167, 240, ''); // Reservado (Uso Banco)

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

    protected function headerLote()
    {
        $this->iniciaHeaderLote();

        /**
         * HEADER DE LOTE
         */
        $this->add(1, 3, Util::onlyNumbers($this->getCodigoBanco())); //Codigo do banco
        $this->add(4, 7, '0001'); // Lote de Serviço
        $this->add(8, 8, '1'); // Tipo de Registro
        $this->add(9, 9, 'R'); // Tipo de operação
        $this->add(10, 11, Util::formatCnab(9, 01, 2)); // Tipo de serviço
        $this->add(12, 13, ''); // Reservados (Uso Banco)
        $this->add(14, 16, Util::formatCnab('9', '030', 3)); // Versão do layout
        $this->add(17, 17, ''); // Reservados (Uso Banco)
        $this->add(18, 18, strlen(Util::onlyNumbers($this->getBeneficiario()->getDocumento())) == 14 ? '2' : '1'); // Tipo de inscrição da empresa
        $this->add(19, 33, Util::formatCnab('9L', $this->getBeneficiario()->getDocumento(), 14)); // Numero de inscrição da empresa
        $this->add(34, 53, ''); // Reservados (Uso Banco)
        $this->add(54, 68, Util::formatCnab(9, $this->getCodigoTransmissao(), 15)); // Codigo de Transmissão
        $this->add(69, 73, ''); // Reservados (Uso Banco)
        $this->add(74, 103, Util::formatCnab('X', $this->getBeneficiario()->getNome(), 30)); // Nome do cedente
        $this->add(104, 143, ''); // Mensagem 1
        $this->add(144, 183, ''); // Mensagem 2
        $this->add(184, 191, Util::formatCnab(9, 0, 8)); // Número Remessa/retorno
        $this->add(192, 199, date('dmY')); // Data de Gravação do arquivo
        $this->add(200, 240, ''); // Reservado (Uso Banco)

        return $this;
    }

    /**
     * Define o trailer de lote
     *
     * @return $this
     * @throws \Exception
     */
    protected function trailerLote()
    {
        $this->iniciaTrailerLote();

        $this->add(1, 3, Util::onlyNumbers($this->getCodigoBanco())); //Codigo do banco
        $this->add(4, 7, '0001'); // Numero do lote remessa
        $this->add(8, 8, Util::formatCnab(9, 5, 1)); //Tipo de registro
        $this->add(9, 17, ''); // Reservado (Uso Banco)
        $this->add(18, 23, Util::formatCnab(9, ($this->qtyRegistrosLote + 2), 6)); // Quantidade de registros do lote
        $this->add(24, 240, ''); // Reservado (Uso Banco)

        return $this;
    }

    protected function trailer()
    {
        $this->iniciaTrailer();

        $this->add(1, 3, Util::onlyNumbers($this->getCodigoBanco())); //Codigo do banco
        $this->add(4, 7, '9999'); // Numero do lote remessa
        $this->add(8, 8, Util::formatCnab(9, 9, 1)); //Tipo de registro
        $this->add(9, 17, ''); // Reservado (Uso Banco)
        $this->add(18, 23, Util::formatCnab(9, 1, 6)); // Qtd de lotes do arquivo
        $this->add(24, 29, Util::formatCnab(9, ($this->qtyRegistrosLote + 4), 6)); // Qtd de lotes do arquivo
        $this->add(30, 240, ''); // Reservado (Uso Banco)

        return $this;
    }

}