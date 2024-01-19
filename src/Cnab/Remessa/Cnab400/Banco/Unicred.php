<?php

namespace Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400\Banco;

use Eduardokum\LaravelBoleto\Util;
use Eduardokum\LaravelBoleto\CalculoDV;
use Eduardokum\LaravelBoleto\Exception\ValidationException;
use Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400\AbstractRemessa;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;
use Eduardokum\LaravelBoleto\Contracts\Cnab\Remessa as RemessaContract;

class Unicred extends AbstractRemessa implements RemessaContract
{
    const ESPECIE_DUPLICATA = 'DM';
    const ESPECIE_NOTA_PROMISSORIA = 'NP';
    const ESPECIE_NOTA_SEGURO = 'NS';
    const ESPECIE_COBRANCA_SERIADA = 'CS';
    const ESPECIE_RECIBO = 'REC';
    const ESPECIE_LETRAS_CAMBIO = 'LC';
    const ESPECIE_NOTA_DEBITO = 'ND';
    const ESPECIE_DUPLICATA_SERVICO = 'DS';
    const ESPECIE_OUTROS = 'OUTROS';
    const OCORRENCIA_REMESSA = '01';
    const OCORRENCIA_PEDIDO_BAIXA = '02';
    const OCORRENCIA_CONCESSAO_ABATIMENTO = '04';
    const OCORRENCIA_CANC_ABATIMENTO_CONCEDIDO = '05';
    const OCORRENCIA_ALT_VENCIMENTO = '06';
    const OCORRENCIA_PEDIDO_PROTESTO = '09';
    const OCORRENCIA_SUSTAR_PROTESTO_MANTER_TITULO = '11';
    const OCORRENCIA_ALT_SEU_NUMERO = '22';
    const OCORRENCIA_ALT_DADOS_PAGADOR = '23';
    const OCORRENCIA_SUSTAR_PROTESTO_BAIXAR_TITULO = '25';
    const OCORRENCIA_PROTESTO_AUTOMATICO = '26';
    const OCORRENCIA_ALT_STATUS_DESCONTO = '40';
    const INSTRUCAO_PROTESTAR_DIAS_CORRIDOS = '1';
    const INSTRUCAO_PROTESTAR_DIAS_UTEIS = '2';
    const INSTRUCAO_NAO_PROTESTAR = '3';

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
    protected $codigoBanco = BoletoContract::COD_BANCO_UNICRED;

    /**
     * Define as carteiras disponíveis para cada banco
     *
     * @var array
     */
    protected $carteiras = ['21'];

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
     * @throws ValidationException
     */
    public function getCodigoCliente()
    {
        if (empty($this->codigoCliente)) {
            $this->codigoCliente = Util::formatCnab('9', $this->getCarteiraNumero(), 4) .
                Util::formatCnab('9', $this->getAgencia(), 5) .
                Util::formatCnab('9', $this->getConta(), 7) .
                ! is_null($this->getContaDv()) ? $this->getContaDv() : CalculoDV::unicredContaCorrente($this->getConta());
        }

        return $this->codigoCliente;
    }

    /**
     * Seta o codigo do cliente.
     *
     * @param mixed $codigoCliente
     *
     * @return Unicred
     */
    public function setCodigoCliente($codigoCliente)
    {
        $this->codigoCliente = $codigoCliente;

        return $this;
    }

    /**
     * @return Unicred
     * @throws ValidationException
     */
    protected function header()
    {
        $this->iniciaHeader();

        $this->add(1, 1, '0');
        $this->add(2, 2, '1');
        $this->add(3, 9, 'Remessa');
        $this->add(10, 11, '01');
        $this->add(12, 26, Util::formatCnab('X', 'COBRANCA', 15));
        $this->add(27, 46, Util::formatCnab('9', $this->getCodigoCliente(), 20));
        $this->add(47, 76, Util::formatCnab('X', $this->getBeneficiario()->getNome(), 30));
        $this->add(77, 79, $this->getCodigoBanco());
        $this->add(80, 94, Util::formatCnab('X', 'UNICRED', 15));
        $this->add(95, 100, $this->getDataRemessa('dmy')); //DDMMAA
        $this->add(101, 107, '');
        $this->add(108, 110, '000');
        $this->add(111, 117, Util::formatCnab('9', $this->getIdremessa(), 7));
        $this->add(118, 394, '');
        $this->add(395, 400, Util::formatCnab('9', 1, 6));

        return $this;
    }

    /**
     * @param \Eduardokum\LaravelBoleto\Boleto\Banco\Unicred $boleto
     *
     * @return Unicred
     * @throws ValidationException
     */
    public function addBoleto(BoletoContract $boleto)
    {
        $this->boletos[] = $boleto;
        $this->iniciaDetalhe();

        $this->add(1, 1, '1');
        $this->add(2, 6, Util::formatCnab('9', $this->getAgencia(), 5));
        $this->add(7, 7, ! is_null($this->getAgenciaDv()) ? $this->getAgenciaDv() : CalculoDV::unicredAgencia($this->getAgencia()));
        $this->add(8, 19, Util::formatCnab('9', $this->getConta(), 12));
        $this->add(20, 20, Util::formatCnab('9', $this->getContaDv(), 1));
        $this->add(21, 21, '0');
        $this->add(21, 21, '0');
        $this->add(22, 24, Util::formatCnab('9', $this->getCarteira(), 3));
        $this->add(25, 37, Util::formatCnab('9', 0, 13));
        $this->add(38, 62, Util::formatCnab('X', $boleto->getNumeroControle(), 25)); // numero de controle
        $this->add(63, 65, $this->getCodigoBanco());
        $this->add(66, 67, '00');
        $this->add(68, 92, '');
        $this->add(93, 93, '0');
        //Código adotado pela FEBRABAN para identificação do tipo de pagamento de multa.
        // Domínio:
        // ‘1’ = Valor Fixo (R$)
        // ‘2’ = Taxa (%)
        // ‘3’ = Isento
        // *OBSERVAÇÃO:
        // Para boletos com espécie 31 (Cartão de crédito): Deve ser '3' = Isento.
        $this->add(94, 94, $boleto->getMulta() > 0 ? '2' : '3'); //Código da multa 2 = TAXA (%)
        $this->add(95, 104, Util::formatCnab('9', $boleto->getMulta() > 0 ? $boleto->getMulta() : '0', 10, 2));
        /** Código adotado pela FEBRABAN para identificação do tipo de pagamento de mora de juros.
         * Domínio:
         * ‘1’ = Valor Diário (R$)
         * ‘2’ = Taxa Mensal (%)
         * ‘3’= Valor Mensal (R$) *
         * ‘4’ = Taxa diária (%)
         * ‘5’ = Isento
         **/
        $this->add(105, 105, (null !== $boleto->getMoraDia() && $boleto->getMoraDia()) > 0 ? '1' : '5');
        /** Indica se o Título pode ou não ser utilizado como garantia de operação de desconto futura.
         * Domínio:
         * ‘S’ = Título selecionado para ser utilizado como garantia em uma operação de desconto
         * futura.
         * ‘N’ = Título NÃO selecionado para ser utilizado como garantia em uma operação de
         * desconto futura.
         * Default: ‘N’
         **/
        $this->add(106, 106, 'N'); // Identificação de Título Descontável.
        $this->add(107, 108, '');
        $this->add(109, 110, self::OCORRENCIA_REMESSA); // REGISTRO
        if ($boleto->getStatus() == $boleto::STATUS_BAIXA) {
            $this->add(109, 110, self::OCORRENCIA_PEDIDO_BAIXA); // BAIXA
        }
        if ($boleto->getStatus() == $boleto::STATUS_ALTERACAO) {
            $this->add(109, 110, self::OCORRENCIA_ALT_VENCIMENTO); // ALTERAR VENCIMENTO
        }
        if ($boleto->getStatus() == $boleto::STATUS_ALTERACAO_DATA) {
            $this->add(109, 110, self::OCORRENCIA_ALT_VENCIMENTO);
        }
        if ($boleto->getStatus() == $boleto::STATUS_CUSTOM) {
            $this->add(109, 110, sprintf('%2.02s', $boleto->getComando()));
        }
        $this->add(111, 120, Util::formatCnab('X', $boleto->getNumeroDocumento(), 10));
        $this->add(121, 126, $boleto->getDataVencimento()->format('dmy')); //DDMMAA
        $this->add(127, 139, Util::formatCnab('9', $boleto->getValor(), 13, 2));
        $this->add(140, 149, '0000000000');
        // Código adotado pela FEBRABAN para identificação do desconto.
        // Domínio:
        // 0 = Isento
        // 1 = Valor Fixo
        $this->add(150, 150, $boleto->getDesconto() > 0 ? '1' : '0'); //Código do desconto
        $this->add(151, 156, $boleto->getDataDocumento()->format('dmy'));
        $this->add(157, 157, '0');
        // $this->add(158, 158, self::INSTRUCAO_SEM);
        if ($boleto->getDiasProtesto() > 0) {
            // Código adotado pela FEBRABAN para identificar o tipo de prazo a ser considerado para o protesto.
            // Domínio:
            // 1 = Protestar Dias Corridos
            // 2 = Protestar Dias Úteis
            // 3 = Não Protestar        => PADRAO
            $this->add(158, 158, self::INSTRUCAO_PROTESTAR_DIAS_UTEIS); //Código para Protesto
            $this->add(159, 160, Util::formatCnab('9', $boleto->getDiasProtesto(), 2));
        } else {
            $this->add(158, 158, self::INSTRUCAO_NAO_PROTESTAR); //Código para Protesto
            $this->add(159, 160, Util::formatCnab('9', 0, 2));
        }

        $this->add(161, 173, Util::formatCnab('9', $boleto->getMoraDia(), 13, 2));
        $this->add(174, 179, $boleto->getDesconto() > 0 ? $boleto->getDataDesconto()->format('dmy') : '000000');
        $this->add(180, 192, Util::formatCnab('9', $boleto->getDesconto() > 0 ? $boleto->getDesconto() : '0', 13, 2));
        $this->add(193, 203, Util::formatCnab('9', $boleto->getNossoNumero(), 11)); //Nosso Número na UNICRED
        $this->add(204, 205, '00'); //Valor do Abatimento a ser concedido
        $this->add(206, 218, Util::formatCnab('9', 0, 13)); //Valor do Abatimento a ser concedido
        // Tipo de inscrição do Pagador
        // 01 – CPF
        // 02 - CNPJ
        $this->add(219, 220, strlen(Util::onlyNumbers($boleto->getPagador()->getDocumento())) == 14 ? '02' : '01'); //Identificação do Tipo de Inscrição do Pagador
        /** Quando se tratar de CNPJ, adotar o critério de preenchimento da direita para a esquerda,utilizando:
         * - 2 posições para o controle;
         * - 4 posições para a filial;
         * - 8 posições para o CNPJ.
         * Quando se tratar de CPF, adotar o mesmo critério da direita para a esquerda, utilizando:
         * - 2 posições para o controle;
         * - 9 posições para o CPF;
         * - 3 posições a esquerda zeradas.
         **/
        $this->add(221, 234, Util::formatCnab('9', Util::onlyNumbers($boleto->getPagador()->getDocumento()), 14));
        $this->add(235, 274, Util::formatCnab('X', $boleto->getPagador()->getNome(), 40));
        $this->add(275, 314, Util::formatCnab('X', $boleto->getPagador()->getEndereco(), 40));
        $this->add(315, 326, Util::formatCnab('X', $boleto->getPagador()->getBairro(), 12));
        $this->add(327, 334, Util::formatCnab('9', Util::onlyNumbers($boleto->getPagador()->getCep()), 8));
        $this->add(335, 354, Util::formatCnab('X', $boleto->getPagador()->getCidade(), 20)); //Cidade Pagador
        $this->add(355, 356, Util::formatCnab('X', $boleto->getPagador()->getUf(), 2)); //UF pagador
        $this->add(357, 394, Util::formatCnab('X', $boleto->getSacadorAvalista() ? $boleto->getSacadorAvalista()->getNome() : '', 38));
        $this->add(395, 400, Util::formatCnab('9', $this->iRegistros + 1, 6)); //No Sequencial do Registro

        return $this;
    }

    /**
     * @return Unicred
     * @throws ValidationException
     */
    protected function trailer()
    {
        $this->iniciaTrailer();

        $this->add(1, 1, '9');
        $this->add(2, 394, '');
        $this->add(395, 400, Util::formatCnab('9', $this->getCount(), 6));

        return $this;
    }
}
