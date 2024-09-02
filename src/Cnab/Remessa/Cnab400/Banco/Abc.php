<?php

namespace Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400\Banco;

use Eduardokum\LaravelBoleto\Util;
use Eduardokum\LaravelBoleto\Exception\ValidationException;
use Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400\AbstractRemessa;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;
use Eduardokum\LaravelBoleto\Contracts\Cnab\Remessa as RemessaContract;

class Abc extends AbstractRemessa implements RemessaContract
{
    const ESPECIE_DUPLICATA = '01';
    const ESPECIE_NOTA_PROMISSORIA = '02';
    const ESPECIE_CHEQUE = '03';
    const ESPECIE_LETRA_CAMBIO = '04';
    const ESPECIE_RECIBO = '05';
    const ESPECIE_APOLICE_SEGURO = '08';
    const ESPECIE_DUPLICATA_SERVICO = '12';
    const ESPECIE_CARTAO_CREDITO = '31';
    const ESPECIE_OUTROS = '99';

    // Ocorrências
    const OCORRENCIA_REMESSA = '01';
    const OCORRENCIA_PEDIDO_BAIXA = '02';
    const OCORRENCIA_CONCESSAO_ABATIMENTO = '04';
    const OCORRENCIA_CANC_ABATIMENTO_CONCEDIDO = '05';
    const OCORRENCIA_ALT_VENCIMENTO = '06';
    const OCORRENCIA_PEDIDO_PROTESTO = '09';
    const OCORRENCIA_PEDIDO_NAO_PROTESTO = '10';
    const OCORRENCIA_SUSTAR_PROTESTO_BAIXAR_TITULO = '18';
    const OCORRENCIA_ALT_VALOR_VENCIMENTO = '47';

    // Instrucao
    const INSTRUCAO_SEM = '00';
    const INSTRUCAO_SEM_PROTESTO = '10';
    const INSTRUCAO_MENSAGEM = '94';

    public function __construct(array $params = [])
    {
        parent::__construct($params);
        $this->setCamposObrigatorios('agencia', 'carteira', 'codigoCliente');
    }

    /**
     * Código do banco
     *
     * @var string
     */
    protected $codigoBanco = BoletoContract::COD_BANCO_ABC;

    /**
     * Define as carteiras disponíveis para este banco
     * 1 - Cobrança Normal com emissão de bloquetes, pelo banco. Ordem para Classificação: Banco preferencial do Cedente, nosso banco e nossos correspondentes, segundo ordem de escolha, ditada pelo nosso banco.
     * 2 - Ordem para Classificação: Banco preferencial do Cedente, nossos correspondentes, nosso banco.
     * 3 - Cobrança com determinação do Cobrador nas posições 140 – 142, (que não é o Nosso Banco).
     * 4 - O código do Banco cobrador (que não é o Nosso Banco) deve vir nas posições 140-142;. Nosso Número do Banco cobrador deve vir nas posições 74 a 86. Nesta carteira, o cliente envia o título para o Nosso Banco, já com o Nosso Número do correspondente e seu DV calculado (Exceto Banco Itaú, cujo layout exige Nosso Número sem DV na remessa). O campo deve ocupar as 13 posições, com zeros à esquerda.
     * 5 - Cobrança exclusivamente para o próprio banco. As posições 140 a 142 devem estar preenchidas com o código do nosso banco.
     * 6 - Cobrança Expressa (sem emissão de bloquetes)
     * 7 - O código do Banco cobrador (que não é o Nosso Banco) deve vir nas posições 140-142. Após a aceitação do título pelo Nosso Banco, o Nosso Número do correspondente será gerado pelo sistema.
     * @var array
     */
    protected $carteiras = [1, 2, 3, 4, 5, 6, 7];

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
     * @return Abc
     */
    public function setCodigoCliente($codigoCliente)
    {
        $this->codigoCliente = $codigoCliente;

        return $this;
    }

    /**
     * @return Abc
     * @throws ValidationException
     */
    protected function header()
    {
        $this->iniciaHeader();

        $this->add(1, 1, '0');
        $this->add(2, 2, '1');
        $this->add(3, 9, 'REMESSA');
        $this->add(10, 11, '01');
        $this->add(12, 26, Util::formatCnab('X', 'COBRANCA', 15));
        $this->add(27, 46, Util::formatCnab('X', $this->getCodigoCliente(), 20));
        $this->add(47, 76, Util::formatCnab('X', $this->getBeneficiario()->getNome(), 30));
        $this->add(77, 79, $this->getCodigoBanco());
        $this->add(80, 94, Util::formatCnab('X', 'BANCO ABC BRAS', 15));
        $this->add(95, 100, $this->getDataRemessa('dmy'));
        $this->add(101, 394, '');
        $this->add(395, 400, Util::formatCnab('9', 1, 6));

        return $this;
    }

    /**
     * @param \Eduardokum\LaravelBoleto\Boleto\Banco\Pine $boleto
     *
     * @return Abc
     * @throws ValidationException
     */
    public function addBoleto(BoletoContract $boleto)
    {
        $this->boletos[] = $boleto;
        $this->iniciaDetalhe();

        $this->add(1, 1, '1');
        $this->add(2, 3, strlen(Util::onlyNumbers($this->getBeneficiario()->getDocumento())) == 14 ? '02' : '01');
        $this->add(4, 17, Util::formatCnab('9', Util::onlyNumbers($this->getBeneficiario()->getDocumento()), 14));
        $this->add(18, 37, Util::formatCnab('X', $this->getCodigoCliente(), 20));
        $this->add(38, 62, Util::formatCnab('X', $boleto->getNumeroControle(), 25)); // numero de controle
        $this->add(63, 73, Util::formatCnab('9', $boleto->getNossoNumero(), 11));
        $this->add(74, 86, Util::formatCnab('X', '', 11));
        $this->add(87, 89, Util::formatCnab('X', '', 3));
        $this->add(90, 90, $boleto->getMulta() > 0 ? '2' : '0');
        $this->add(91, 103, Util::formatCnab('9', $boleto->getMulta(), 13, 4));
        $this->add(104, 105, $boleto->getMulta() > 0 ? Util::numberFormatGeral($boleto->getMultaApos(), 2) : '00');
        $this->add(106, 107, '');
        $this->add(108, 108, Util::formatCnab('9', $this->getCarteira(), 1));
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
        $this->add(111, 120, Util::formatCnab('9', $boleto->getNumero(), 10));
        $this->add(121, 126, $boleto->getDataVencimento()->format('dmy'));
        $this->add(127, 139, Util::formatCnab('9', $boleto->getValor(), 13, 2));
        $this->add(140, 142, $this->getCodigoBanco());
        $this->add(143, 146, Util::formatCnab('9', 0, 4));
        $this->add(147, 147, Util::formatCnab('9', 0, 1));
        $this->add(148, 149, $boleto->getEspecieDocCodigo());
        $this->add(150, 150, $boleto->getAceite());
        $this->add(151, 156, $boleto->getDataDocumento()->format('dmy'));
        $this->add(157, 158, $boleto->getDiasProtesto() > 0 ? self::INSTRUCAO_SEM_PROTESTO : self::INSTRUCAO_SEM);
        $this->add(159, 160, self::INSTRUCAO_SEM);
        $this->add(161, 173, Util::formatCnab('9', $boleto->getMoraDia(), 13, 2));
        $this->add(174, 179, $boleto->getDesconto() > 0 ? $boleto->getDataDesconto()->format('dmy') : '000000');
        $this->add(180, 192, Util::formatCnab('9', $boleto->getDesconto(), 13, 2));
        $this->add(193, 205, Util::formatCnab('9', 0, 13, 2));
        $this->add(206, 218, Util::formatCnab('9', 0, 13, 2));
        $this->add(219, 220, strlen(Util::onlyNumbers($boleto->getPagador()->getDocumento())) == 14 ? '02' : '01');
        $this->add(221, 234, Util::formatCnab('9', Util::onlyNumbers($boleto->getPagador()->getDocumento()), 14));
        $this->add(235, 264, Util::formatCnab('X', $boleto->getPagador()->getNome(), 30));
        $this->add(265, 274, Util::formatCnab('X', '', 10));
        $this->add(275, 314, Util::formatCnab('X', $boleto->getPagador()->getEndereco(), 40));
        $this->add(315, 326, Util::formatCnab('X', $boleto->getPagador()->getBairro(), 12));
        $this->add(327, 334, Util::formatCnab('9', Util::onlyNumbers($boleto->getPagador()->getCep()), 8));
        $this->add(335, 349, Util::formatCnab('X', $boleto->getPagador()->getCidade(), 15));
        $this->add(350, 351, Util::formatCnab('X', $boleto->getPagador()->getUf(), 2));
        $this->add(352, 381, Util::formatCnab('X', $boleto->getSacadorAvalista() ? $boleto->getSacadorAvalista()->getNome() : '', 30));
        $this->add(382, 385, Util::formatCnab('X', '', 4));
        $this->add(386, 391, Util::formatCnab('X', '', 6));
        $this->add(392, 393, Util::formatCnab('9', $boleto->getDiasProtesto('0'), 2));
        $this->add(394, 394, $boleto->getMoeda() == 9 ? '1' : $boleto->getMoeda());
        $this->add(395, 400, Util::formatCnab('9', $this->iRegistros + 1, 6));

        if (count($boleto->getNotasFiscais()) > 0) {
            $this->iniciaDetalhe();
            $this->add(1, 1, '4');

            $nota1 = $boleto->getNotaFiscal(0);
            $this->add(2, 16, Util::formatCnab('X', $nota1->getNumero(), 15)); // Numero da nota 1
            $this->add(17, 29, Util::formatCnab('9', $nota1->getValor(), 13, 2)); // valor da nota 1
            $this->add(30, 37, Util::formatCnab('9', $nota1->getData('dmY'), 8)); // data nota 1
            $this->add(38, 81, Util::formatCnab('9', $nota1->getChave(), 44)); // Chave da nota 1

            $nota2 = $boleto->getNotaFiscal(1);
            $this->add(82, 96, Util::formatCnab('X', $nota2->getNumero(), 15)); // Numero da nota 2
            $this->add(97, 109, Util::formatCnab('9', $nota2->getValor(), 13, 2)); // valor da nota 2
            $this->add(110, 117, Util::formatCnab('9', $nota2->getData('dmY'), 8)); // data nota 2
            $this->add(118, 161, Util::formatCnab('9', $nota2->getChave(), 44));  // Chave da nota 2

            $nota3 = $boleto->getNotaFiscal(2);
            $this->add(162, 176, Util::formatCnab('X', $nota3->getNumero(), 15)); // Numero da nota 3
            $this->add(177, 189, Util::formatCnab('9', $nota3->getValor(), 13, 2)); // valor da nota 3
            $this->add(190, 197, Util::formatCnab('9', $nota3->getData('dmY'), 8)); // data nota 3
            $this->add(198, 241, Util::formatCnab('9', $nota3->getChave(), 44));  // Chave da nota 3

            $this->add(395, 400, Util::formatCnab('9', $this->iRegistros + 1, 6));
        }

        return $this;
    }

    /**
     * @return Abc
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
