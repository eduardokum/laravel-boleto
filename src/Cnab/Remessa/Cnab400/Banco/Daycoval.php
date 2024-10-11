<?php

namespace Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400\Banco;

use Illuminate\Support\Arr;
use Eduardokum\LaravelBoleto\Util;
use Eduardokum\LaravelBoleto\Pessoa;
use Eduardokum\LaravelBoleto\Exception\ValidationException;
use Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400\AbstractRemessa;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;
use Eduardokum\LaravelBoleto\Contracts\Cnab\Remessa as RemessaContract;

class Daycoval extends AbstractRemessa implements RemessaContract
{
    const ESPECIE_DUPLICATA = '01';
    const ESPECIE_DUPLICATA_SERVICO = '12';

    // Ocorrências
    const OCORRENCIA_REMESSA = '01';
    const OCORRENCIA_PEDIDO_BAIXA = '02';
    const OCORRENCIA_CONCESSAO_ABATIMENTO = '04';
    const OCORRENCIA_ALT_VENCIMENTO = '06';
    const OCORRENCIA_PEDIDO_PROTESTO = '09';
    const OCORRENCIA_PEDIDO_NAO_PROTESTO = '10';
    const OCORRENCIA_SUSTAR_PROTESTO_BAIXAR_TITULO = '18';

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
    protected $codigoBanco = BoletoContract::COD_BANCO_DAYCOVAL;

    protected $numero_arquivo_dia = 1;

    /**
     * Define as carteiras disponíveis para este banco
     * 3 Cobrança Caucionada
     * @var array
     */
    protected $carteiras = [6];

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
     * @return Daycoval
     */
    public function setCodigoCliente($codigoCliente)
    {
        $this->codigoCliente = $codigoCliente;

        return $this;
    }

    /**
     * A nomenclatura do Arquivo Remessa é:
     * 0XEDDMMS.TXT
     * 0XE é Inicial do Arquivo (fixo)
     * DD è Dia em que o Arquivo foi gerado, ex.: 18
     * MM è Mês em que o Arquivo foi gerado, ex.: 09
     * S è Sequencial do Arquivo gerado, EX: 1 porque é o Primeiro do dia
     * .TXT è Extensão (Texto)
     * Ex.: 0XE18091.TXT
     * @return string
     */
    public function nomeSugerido()
    {
        return sprintf('OXE%02s%02s%s.TXT', date('d'), date('m'), $this->numero_arquivo_dia);
    }

    /**
     * @return Daycoval
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
        $this->add(80, 94, Util::formatCnab('X', 'BANCO DAYCOVAL', 15));
        $this->add(95, 100, $this->getDataRemessa('dmy'));
        $this->add(101, 394, '');
        $this->add(395, 400, Util::formatCnab('9', 1, 6));

        return $this;
    }

    /**
     * @param \Eduardokum\LaravelBoleto\Boleto\Banco\Pine $boleto
     *
     * @return Daycoval
     * @throws ValidationException
     */
    public function addBoleto(BoletoContract $boleto)
    {
        $this->boletos[] = $boleto;
        $this->iniciaDetalhe();

        $this->add(1, 1, '1');
        if ($this->getBeneficiario()->getTipo() == Pessoa::TIPO_BENEFICIARIO) {
            $this->add(2, 3, strlen(Util::onlyNumbers($this->getBeneficiario()->getDocumento())) == 14 ? '02' : '01');
        } elseif ($this->getBeneficiario()->getTipo() == Pessoa::TIPO_SACADOR) {
            $this->add(2, 3, strlen(Util::onlyNumbers($this->getBeneficiario()->getDocumento())) == 14 ? '04' : '03');
        } else {
            throw new ValidationException('Tipo de beneficiário inválido');
        }
        $this->add(4, 17, Util::formatCnab('9', Util::onlyNumbers($this->getBeneficiario()->getDocumento()), 14));
        $this->add(18, 37, Util::formatCnab('X', $this->getCodigoCliente(), 20));
        $this->add(38, 62, Util::formatCnab('X', $boleto->getNumeroControle(), 25)); // numero de controle
        $this->add(63, 70, Util::formatCnab('9', substr(substr($boleto->getNossoNumero(), -9), 0, -1), 8));
        $this->add(71, 83, Util::formatCnab('X', '', 11));
        $this->add(84, 107, Util::formatCnab('X', '', 3));
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
        $this->add(157, 158, self::INSTRUCAO_SEM);
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
        $this->add(352, 381, Util::formatCnab('X', $boleto->getBeneficiario()->getNome(), 30));
        $this->add(382, 385, Util::formatCnab('X', '', 4));
        $this->add(386, 391, Util::formatCnab('X', '', 6));
        $this->add(392, 393, Util::formatCnab('9', $boleto->getDiasProtesto('0'), 2));
        $this->add(394, 394, $boleto->getMoeda() == 9 ? '0' : $boleto->getMoeda());
        $this->add(395, 400, Util::formatCnab('9', $this->iRegistros + 1, 6));

        $instrucoes = array_filter($boleto->getInstrucoes());
        if (count($instrucoes) > 0) {
            $this->iniciaDetalhe();
            $this->add(1, 1, '2');
            $this->add(2, 2, '0');
            $this->add(3, 71, Util::formatCnab('X', Arr::get($instrucoes, 0), 69));
            $this->add(72, 140, Util::formatCnab('X', Arr::get($instrucoes, 1), 69));
            $this->add(141, 209, Util::formatCnab('X', Arr::get($instrucoes, 2), 69));
            $this->add(210, 278, Util::formatCnab('X', Arr::get($instrucoes, 3), 69));
            $this->add(279, 394, '');
            $this->add(395, 400, Util::formatCnab('9', $this->iRegistros + 1, 6));
        }

        if (count($boleto->getNotasFiscais()) > 0) {
            $this->iniciaDetalhe();
            $this->add(1, 1, '4');

            $nota1 = $boleto->getNotaFiscal(0);
            $this->add(2, 16, Util::formatCnab('X', $nota1->getNumero(), 15)); // Numero da nota 1
            $this->add(17, 29, Util::formatCnab('9', $nota1->getValor(), 13, 2)); // valor da nota 1
            $this->add(30, 37, Util::formatCnab('9', $nota1->getData('dmY'), 8)); // data nota 1
            $this->add(38, 81, Util::formatCnab('9', $nota1->getChave(), 44)); // Chave da nota 1
            $this->add(82, 394, '');
            $this->add(395, 400, Util::formatCnab('9', $this->iRegistros + 1, 6));
        }

        return $this;
    }

    /**
     * @return Daycoval
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
