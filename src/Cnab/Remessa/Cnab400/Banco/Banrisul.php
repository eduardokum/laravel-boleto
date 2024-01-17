<?php

namespace Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400\Banco;

use Eduardokum\LaravelBoleto\Util;
use Eduardokum\LaravelBoleto\Exception\ValidationException;
use Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400\AbstractRemessa;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;
use Eduardokum\LaravelBoleto\Contracts\Cnab\Remessa as RemessaContract;

class Banrisul extends AbstractRemessa implements RemessaContract
{
    const TIPO_COBRANCA_DIRETA = '04';
    const TIPO_COBRANCA_ESCRITURAL = '06';
    const TIPO_COBRANCA_CREDENCIADA = '08';
    const TIPO_TITULO_TERCEIROS = '09';
    const INSTRUCAO_SEM = '00';
    const INSTRUCAO_PROTESTAR_XX = '09';
    const INSTRUCAO_DEVOLVER_XX = '15';
    const INSTRUCAO_MULTA_XX = '18';
    const INSTRUCAO_MULTA_FRACAO_XX = '20';
    const INSTRUCAO_NAO_PROTESTAR = '23';
    const OCORRENCIA_REMESSA = '01';
    const OCORRENCIA_PEDIDO_BAIXA = '02';
    const OCORRENCIA_CONCESSAO_ABATIMENTO = '04';
    const OCORRENCIA_CANC_ABATIMENTO_CONCEDIDO = '05';
    const OCORRENCIA_ALT_VENCIMENTO = '06';
    const OCORRENCIA_ALT_CONTROLE_PARTICIPANTE = '07';
    const OCORRENCIA_ALT_SEU_NUMERO = '08';
    const OCORRENCIA_PEDIDO_PROTESTO = '09';
    const OCORRENCIA_SUSTAR_PROTESTO = '10';
    const OCORRENCIA_DISPENSAR_JUROS = '11';
    const OCORRENCIA_REEMBOLSO_TRANS = '12';
    const OCORRENCIA_REEMBOLSO_DEV = '13';
    const OCORRENCIA_ALT_NOME_END_SACADO = '14';
    const OCORRENCIA_ALT_PRAZO_PROTESTO = '16';
    const OCORRENCIA_PROTESTO_FALENCIA = '17';
    const OCORRENCIA_ALT_PAGADOR_NOME = '18';
    const OCORRENCIA_ALT_PAGADOR_END = '19';
    const OCORRENCIA_ALT_PAGADOR_CIDADE = '20';
    const OCORRENCIA_ALT_PAGADOR_CEP = '21';
    const OCORRENCIA_ACERTO_RATEIO_CREDITO = '68';
    const OCORRENCIA_CANC_RATEIO_CREDITO = '69';

    public function __construct(array $params = [])
    {
        parent::__construct($params);
        $this->addCampoObrigatorio('codigoCliente');
    }

    /**
     * Valor total dos titulos
     *
     * @var int
     */
    private $valorTotal = 0;

    /**
     * Código do banco
     *
     * @var string
     */
    protected $codigoBanco = BoletoContract::COD_BANCO_BANRISUL;

    /**
     * Define as carteiras disponíveis para este banco
     * 1 -> Cobrança Simples
     * 3 -> Cobrança Caucionada
     * 4 -> Cobrança em IGPM
     * 5 -> Cobrança Caucionada CGB Especial
     * 6 -> Cobrança Simples Seguradora
     * 7 -> Cobrança em UFIR
     * 8 -> Cobrança em IDTR
     * C -> Cobrança Vinculada
     * D -> Cobrança CSB
     * E -> Cobrança Caucionada Câmbio
     * F -> Cobrança Vendor
     * H -> Cobrança Caucionada Dólar
     * I -> Cobrança Caucionada Compror
     * K -> Cobrança Simples INCC-M
     * M -> Cobrança Partilhada
     * N -> Capital de Giro CGB ICM
     * R -> Desconto de Duplicata
     * S -> Vendor Eletrônico
     * X -> Vendor BDL
     *
     *
     * @var array
     */
    protected $carteiras = ['1', '3', '4', '5', '6', '7', '8', 'C', 'D', 'E', 'F', 'H', 'I', 'K', 'M', 'N', 'R', 'S', 'X'];

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
     * Codigo do cliente office banking junto ao banco.
     *
     * @var string
     */
    protected $codigoClienteOfficeBanking;

    /**
     * Remessa em teste
     *
     * @var bool
     */
    protected $teste = false;

    /**
     * Define se é teste
     *
     * @param bool $teste
     * @return Banrisul
     */
    public function setTeste($teste)
    {
        $this->teste = (bool) $teste;

        return $this;
    }

    /**
     * Retorna se é com registro.
     *
     * @return bool
     */
    public function isTeste()
    {
        return $this->teste;
    }

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
     * @return Banrisul
     */
    public function setCodigoCliente($codigoCliente)
    {
        $this->codigoCliente = $codigoCliente;

        return $this;
    }

    /**
     * Retorna o codigo do cliente office banking.
     *
     * @return mixed
     */
    public function getCodigoClienteOfficeBanking()
    {
        return $this->codigoClienteOfficeBanking;
    }

    /**
     * Seta o codigo do cliente office banking.
     *
     * @param mixed $officeBanking
     *
     * @return Banrisul
     */
    public function setCodigoClienteOfficeBanking($officeBanking)
    {
        $this->codigoClienteOfficeBanking = $officeBanking;

        return $this;
    }

    /**
     * @return Banrisul
     * @throws ValidationException
     */
    protected function header()
    {
        $this->iniciaHeader();

        $this->add(1, 1, '0');
        $this->add(2, 2, '1');
        $this->add(3, 9, 'REMESSA');
        $this->add(10, 26, '');
        $this->add(27, 39, Util::formatCnab('9L', $this->getCodigoCliente(), 13));
        $this->add(40, 46, '');
        $this->add(47, 76, Util::formatCnab('X', $this->getBeneficiario()->getNome(), 30));
        $this->add(77, 79, $this->getCodigoBanco());
        $this->add(80, 87, Util::formatCnab('X', 'BANRISUL', 8));
        $this->add(88, 94, '');
        $this->add(95, 100, $this->getDataRemessa('dmy'));
        $this->add(101, 109, '');

        if ($this->isCarteiraRSX()) {
            $cod_servico = $this->isTeste() ? '8808' : '0808';
            $tipo_processamento = $this->isTeste() ? 'X' : 'P';
            $cod_cliente = $this->getCodigoClienteOfficeBanking();

            $this->add(110, 113, Util::formatCnab('9', $cod_servico, 4));
            $this->add(114, 114, '');
            $this->add(115, 115, $tipo_processamento);
            $this->add(116, 116, '');
            $this->add(117, 126, Util::formatCnab('9', $cod_cliente, 10));
        } else {
            $this->add(110, 126, '');
        }

        $this->add(127, 394, '');
        $this->add(395, 400, Util::formatCnab('9', 1, 6));

        return $this;
    }

    /**
     * @param \Eduardokum\LaravelBoleto\Boleto\Banco\Banrisul $boleto
     *
     * @return Banrisul
     * @throws ValidationException
     */
    public function addBoleto(BoletoContract $boleto)
    {
        $this->boletos[] = $boleto;
        if ($chaveNfe = $boleto->getChaveNfe()) {
            $this->iniciaDetalheExtendido();
        } else {
            $this->iniciaDetalhe();
        }

        $this->add(1, 1, 1);
        $this->add(2, 17, '');
        $this->add(18, 30, Util::formatCnab('9L', $this->getCodigoCliente(), 13, '0'));
        $this->add(31, 37, '');
        $this->add(38, 62, Util::formatCnab('X', $boleto->getNumeroControle(), 25));
        $this->add(63, 72, Util::formatCnab('9L', $boleto->getNossoNumero(), 10));
        $this->add(73, 104, '');
        $this->add(105, 107, '');
        $this->add(108, 108, Util::formatCnab('X', $this->getCarteiraNumero(), 1));
        $this->add(109, 110, self::OCORRENCIA_REMESSA); // REGISTRO
        if ($boleto->getStatus() == $boleto::STATUS_BAIXA) {
            $this->add(109, 110, self::OCORRENCIA_PEDIDO_BAIXA); // BAIXA
        }
        if ($boleto->getStatus() == $boleto::STATUS_ALTERACAO) {
            throw new ValidationException('Banrisul não suporta alteração geral, use o comando `comandarInstrucao` no boleto para enviar uma solicitação especifica');
        }
        if ($boleto->getStatus() == $boleto::STATUS_ALTERACAO_DATA) {
            $this->add(109, 110, self::OCORRENCIA_ALT_VENCIMENTO);
        }
        if ($boleto->getStatus() == $boleto::STATUS_CUSTOM) {
            $this->add(109, 110, sprintf('%2.02s', $boleto->getComando()));
        }
        $this->add(111, 120, Util::formatCnab('X', $boleto->getNumeroDocumento(), 10));
        $this->add(121, 126, $boleto->getDataVencimento()->format('dmy'));
        $this->add(127, 139, Util::formatCnab('9', $boleto->getValor(), 13, 2));
        $this->add(140, 142, $this->getCodigoBanco());
        $this->add(143, 147, '');
        $this->add(148, 149, $this->isCarteiraRSX() ? '' : self::TIPO_COBRANCA_CREDENCIADA);
        $this->add(150, 150, $boleto->getAceite());
        $this->add(151, 156, $boleto->getDataDocumento()->format('dmy'));
        $this->add(157, 158, self::INSTRUCAO_SEM);
        $this->add(159, 160, self::INSTRUCAO_SEM);
        if ($boleto->getDiasProtesto() > 0) {
            $this->add(157, 158, self::INSTRUCAO_PROTESTAR_XX);
        } elseif ($boleto->getDiasBaixaAutomatica() > 0) {
            $this->add(157, 158, self::INSTRUCAO_DEVOLVER_XX);
        }
        if ($boleto->getMulta() > 0) {
            $this->add(159, 160, self::INSTRUCAO_MULTA_XX);
        }
        $this->add(161, 161, 0);
        $this->add(162, 173, Util::formatCnab('9', $boleto->getMoraDia(), 12, 2));
        $this->add(174, 192, '');

        if ($boleto->getDesconto() > 0) {
            $this->add(174, 179, $boleto->getDataDesconto()->format('dmy'));
            $this->add(180, 192, Util::formatCnab('9', $boleto->getDesconto(), 13, 2));
        }

        $this->add(193, 205, Util::formatCnab('9', 0, 13, 2));
        $this->add(206, 218, Util::formatCnab('9', 0, 13, 2));
        $this->add(219, 220, strlen(Util::onlyNumbers($boleto->getPagador()->getDocumento())) == 14 ? '02' : '01');
        $this->add(221, 234, Util::formatCnab('9L', $boleto->getPagador()->getDocumento(), 14));
        $this->add(235, 269, Util::formatCnab('X', $boleto->getPagador()->getNome(), 35));
        $this->add(270, 274, '');
        $this->add(275, 314, Util::formatCnab('X', $boleto->getPagador()->getEndereco(), 40));
        $this->add(315, 321, '');
        $this->add(322, 324, Util::formatCnab('9', $boleto->getMulta(), 3, 1));
        $this->add(325, 326, '00');
        $this->add(327, 334, Util::formatCnab('9L', $boleto->getPagador()->getCep(), 8));
        $this->add(335, 349, Util::formatCnab('X', $boleto->getPagador()->getCidade(), 15));
        $this->add(350, 351, Util::formatCnab('X', $boleto->getPagador()->getUf(), 2));

        if ($this->isCarteiraRSX()) {
            $this->add(352, 371, '');
        } else {
            $this->add(352, 355, '');
            $this->add(356, 357, '');
            $this->add(358, 369, '');
            $this->add(370, 371, Util::formatCnab('9', $boleto->getDiasProtesto($boleto->getDiasBaixaAutomatica()), 2));
        }

        $this->add(372, 394, '');
        $this->add(395, 400, Util::formatCnab('9', $this->iRegistros + 1, 6));
        if ($chaveNfe) {
            $this->add(401, 444, Util::formatCnab('9', $chaveNfe, 44));
        }

        $this->valorTotal += $boleto->getValor();

        return $this;
    }

    /**
     * @return Banrisul
     * @throws ValidationException
     */
    protected function trailer()
    {
        $this->iniciaTrailer();

        $this->add(1, 1, '9');
        $this->add(2, 27, '');
        $this->add(28, 40, Util::formatCnab('9', $this->valorTotal, 13, 2));
        $this->add(41, 394, '');
        $this->add(395, 400, Util::formatCnab('9', $this->getCount(), 6));

        return $this;
    }

    /**
     * Verifica se a carteira é uma das seguintes : R, S, X ou alguma a mais passada por parametro
     *
     * @param array $adicional
     *
     * @return bool
     */
    private function isCarteiraRSX(array $adicional = [])
    {
        return in_array(Util::upper($this->getCarteira()), array_merge(['R', 'S', 'X'], $adicional));
    }
}
