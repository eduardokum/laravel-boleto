<?php

namespace Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400\Banco;

use Eduardokum\LaravelBoleto\Util;
use Eduardokum\LaravelBoleto\Exception\ValidationException;
use Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400\AbstractRemessa;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;
use Eduardokum\LaravelBoleto\Contracts\Cnab\Remessa as RemessaContract;

class Grafeno extends AbstractRemessa implements RemessaContract
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

    // OCORRENCIAS
    const OCORRENCIA_REMESSA = '01';
    const OCORRENCIA_PEDIDO_BAIXA = '02';
    const OCORRENCIA_CONCESSAO_ABATIMENTO = '04';
    const OCORRENCIA_ALT_VENCIMENTO = '06';
    const OCORRENCIA_ALT_NUMERO_CONTROLE = '07';
    const OCORRENCIA_ALT_NUMERO_DOCUMENTO = '08';
    const OCORRENCIA_PEDIDO_PROTESTO = '09';
    const OCORRENCIA_ALT_NFE = '10';
    const OCORRENCIA_SUSTAR_PROTESTO_BAIXAR_TITULO = '19';
    const OCORRENCIA_GRAFENO_TITULARIDADE = '23';

    // INSTRUCAO
    const INSTRUCAO_SEM = '00';

    public function __construct(array $params = [])
    {
        parent::__construct($params);
        $this->addCampoObrigatorio('convenio', 'idremessa');
    }

    protected $tamanho_linha = 444;

    /**
     * Código do banco
     *
     * @var string
     */
    protected $codigoBanco = BoletoContract::COD_BANCO_GRAFENO;

    /**
     * Define as carteiras disponíveis para cada banco
     *
     * @var array
     */
    protected $carteiras = [1, 2, 3];

    /**
     * Convenio cliente junto ao banco.
     *
     * @var string
     */
    protected $convenio;

    /**
     * Codigo do cliente junto ao banco.
     *
     * @var string
     */
    protected $codigoCliente;

    /**
     * Retorna o convenio do cliente.
     *
     * @return string
     */
    public function getConvenio()
    {
        return $this->convenio;
    }

    /**
     * Seta o convenio do cliente.
     *
     * @param mixed $convenio
     *
     * @return Grafeno
     */
    public function setConvenio($convenio)
    {
        $this->convenio = $convenio;

        return $this;
    }

    /**
     * @return Grafeno
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
        $this->add(27, 46, Util::formatCnab('X', Util::numberFormatGeral($this->getConta(), 8), 20));
        $this->add(47, 76, Util::formatCnab('X', $this->getBeneficiario()->getNome(), 30));
        $this->add(77, 79, $this->getCodigoBanco());
        $this->add(80, 94, Util::formatCnab('X', 'BMPMONEYPLUS', 15));
        $this->add(95, 100, $this->getDataRemessa('dmy'));
        $this->add(101, 108, '');
        $this->add(109, 110, Util::formatCnab('X', 'MX', 2));
        $this->add(111, 117, $this->getIdremessa());
        $this->add(118, 438, $this->getIdremessa());
        $this->add(439, 444, Util::formatCnab('9', 1, 6));

        return $this;
    }

    /**
     * @param \Eduardokum\LaravelBoleto\Boleto\Banco\Fibra $boleto
     *
     * @return Grafeno
     * @throws ValidationException
     */
    public function addBoleto(BoletoContract $boleto)
    {
        $this->boletos[] = $boleto;
        $this->iniciaDetalhe();

        if (empty($this->codigoCliente)) {
            $this->codigoCliente = '0' . Util::numberFormatGeral($this->getCarteira(), 3)
                . Util::numberFormatGeral($this->getAgencia(), 5)
                . Util::numberFormatGeral($this->getConta(), 7)
                . $this->getContaDv();
        }

        $this->add(1, 1, '1');
        $this->add(2, 6, '');
        $this->add(7, 7, '');
        $this->add(8, 12, '');
        $this->add(13, 19, '');
        $this->add(20, 20, '');
        $this->add(21, 37, Util::formatCnab('X', $this->codigoCliente, 17));
        $this->add(38, 62, Util::formatCnab('X', $boleto->getNumeroControle(), 25)); // numero de controle
        $this->add(63, 65, $this->getCodigoBanco());
        $this->add(66, 66, $boleto->getMulta() > 0 ? '2' : '0');
        $this->add(67, 70, Util::formatCnab('9', Util::nFloat(($boleto->getMulta() / $boleto->getValor()) * 100), 4, 2));
        $this->add(71, 82, Util::formatCnab('9', $boleto->getNossoNumero(), 12));
        $this->add(83, 92, Util::formatCnab('9', $boleto->getDesconto(), 10, 2));
        $this->add(93, 93, '');
        $this->add(94, 94, '');
        $this->add(95, 104, '');
        $this->add(105, 105, '');
        $this->add(106, 106, '');
        $this->add(107, 108, '01');
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
        $this->add(140, 142, '000');
        $this->add(143, 147, '00000');
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
        $this->add(235, 274, Util::formatCnab('X', $boleto->getPagador()->getNome(), 40));
        $this->add(275, 314, Util::formatCnab('X', $boleto->getPagador()->getEndereco(), 40));
        $this->add(315, 326, Util::formatCnab('X', '', 12));
        $this->add(327, 334, Util::formatCnab('9', Util::onlyNumbers($boleto->getPagador()->getCep()), 8));
        $this->add(335, 394, Util::formatCnab('X', $boleto->getSacadorAvalista() ? $boleto->getSacadorAvalista()->getNome() : '', 60));
        $this->add(395, 438, Util::formatCnab('9', $boleto->getChaveNfe(), 44));
        $this->add(439, 444, Util::formatCnab('9', $this->iRegistros + 1, 6));

        if ($email = $boleto->getPagador()->getEmail()) {
            $this->iniciaDetalhe();
            $this->add(1, 1, '2');
            $this->add(2, 438, Util::formatCnab('X', $email, 437));
            $this->add(439, 444, Util::formatCnab('9', $this->iRegistros + 1, 6));
        }

        return $this;
    }

    /**
     * @return Grafeno
     * @throws ValidationException
     */
    protected function trailer()
    {
        $this->iniciaTrailer();

        $this->add(1, 1, '9');
        $this->add(2, 438, '');
        $this->add(439, 444, Util::formatCnab('9', $this->getCount(), 6));

        return $this;
    }

    public function nomeSugerido()
    {
        //          CGDDMMYYYY??????????.rem
        //          CG - Serviço utilizado pelo cliente, atualmente fixado em Cobrança Grafeno
        //          DDMMYYYY - Dia Mês Ano Geração do arquivo
        //          ?????????? - Primeiros 10 dígitos da razão social da conta do cliente
        //          .rem - Extensão do arquivo remessa
        return sprintf('CG%08s_%010s.rem', $this->getDataRemessa('DDMMYYYYY'), str_replace(' ', '', $this->getBeneficiario()->getNome()));
    }
}
