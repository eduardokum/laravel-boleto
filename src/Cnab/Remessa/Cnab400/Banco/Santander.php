<?php

namespace Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400\Banco;

use Eduardokum\LaravelBoleto\Util;
use Eduardokum\LaravelBoleto\CalculoDV;
use Eduardokum\LaravelBoleto\Exception\ValidationException;
use Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400\AbstractRemessa;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;
use Eduardokum\LaravelBoleto\Contracts\Cnab\Remessa as RemessaContract;

class Santander extends AbstractRemessa implements RemessaContract
{
    const ESPECIE_DUPLICATA = '01';
    const ESPECIE_NOTA_PROMISSORIA = '02';
    const ESPECIE_NOTA_SEGURO = '03';
    const ESPECIE_RECIBO = '05';
    const ESPECIE_DUPLICATA_SERVICO = '06';
    const ESPECIE_LETRA_CAMBIO = '07';
    const OCORRENCIA_REMESSA = '01';
    const OCORRENCIA_PEDIDO_BAIXA = '02';
    const OCORRENCIA_CONCESSAO_ABATIMENTO = '04';
    const OCORRENCIA_CANC_ABATIMENTO = '05';
    const OCORRENCIA_ALT_VENCIMENTO = '06';
    const OCORRENCIA_ALT_CONTROLE_PARTICIPANTE = '07';
    const OCORRENCIA_ALT_SEUNUMERO = '08';
    const OCORRENCIA_PROTESTAR = '09';
    const OCORRENCIA_SUSTAR_PROTESTO = '18';
    const INSTRUCAO_SEM = '00';
    const INSTRUCAO_BAIXAR_APOS_VENC_15 = '02';
    const INSTRUCAO_BAIXAR_APOS_VENC_30 = '03';
    const INSTRUCAO_NAO_BAIXAR = '04';
    const INSTRUCAO_PROTESTAR = '06';
    const INSTRUCAO_NAO_PROTESTAR = '07';
    const INSTRUCAO_NAO_COBRAR_MORA = '08';

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
     * @param mixed $codigoCliente
     *
     * @return Santander
     */
    public function setCodigoCliente($codigoCliente)
    {
        $this->codigoCliente = $codigoCliente;

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
        $conta = $this->getConta();
        if (strlen($conta) >= 8) {
            $conta = substr($conta, 0, 7);
        }

        return Util::formatCnab('9', $this->getAgencia(), 4)
            . Util::formatCnab('9', substr($this->getCodigoCliente(), 0, 7), 8)
            . Util::formatCnab('9', $conta, 8);
    }

    /**
     * Valor total dos titulos.
     *
     * @var float
     */
    private $total = 0;

    /**
     * @return Santander
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
        $this->add(27, 46, Util::formatCnab('9', $this->getCodigoTransmissao(), 20));
        $this->add(47, 76, Util::formatCnab('X', $this->getBeneficiario()->getNome(), 30));
        $this->add(77, 79, $this->getCodigoBanco());
        $this->add(80, 94, Util::formatCnab('X', 'SANTANDER', 15));
        $this->add(95, 100, $this->getDataRemessa('dmy'));
        $this->add(101, 116, Util::formatCnab('9', '0', 16));
        $this->add(117, 391, '');
        $this->add(392, 394, '000');
        $this->add(395, 400, Util::formatCnab('9', 1, 6));

        return $this;
    }

    /**
     * @param \Eduardokum\LaravelBoleto\Boleto\Banco\Santander $boleto
     *
     * @return Santander
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

        $this->total += $boleto->getValor();

        $this->add(1, 1, '1');
        $this->add(2, 3, strlen(Util::onlyNumbers($this->getBeneficiario()->getDocumento())) == 14 ? '02' : '01');
        $this->add(4, 17, Util::formatCnab('9L', $this->getBeneficiario()->getDocumento(), 14));
        $this->add(18, 37, Util::formatCnab('9', $this->getCodigoTransmissao(), 20));
        $this->add(38, 62, Util::formatCnab('X', $boleto->getNumeroControle(), 25)); // numero de controle
        $this->add(63, 70, substr(Util::onlyNumbers($boleto->getNossoNumero()), -8));
        $this->add(71, 76, '000000');
        $this->add(77, 77, '');
        $this->add(78, 78, ($boleto->getMulta() > 0 ? '4' : '0'));
        $this->add(79, 82, Util::formatCnab('9', $boleto->getMulta(), 4, 2));
        $this->add(83, 84, '00');
        $this->add(85, 97, Util::formatCnab('9', 0, 13, 2));
        $this->add(98, 101, '');
        $this->add(102, 107, $boleto->getJurosApos() === false ? '000000' : $boleto->getDataVencimento()->copy()->addDays($boleto->getJurosApos())->format('dmy'));
        $this->add(108, 108, $this->getCarteiraNumero() > 200 ? '1' : '5');
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
        $this->add(121, 126, $boleto->getDataVencimento()->format('dmy'));
        $this->add(127, 139, Util::formatCnab('9', $boleto->getValor(), 13, 2));
        $this->add(140, 142, $this->getCodigoBanco());
        $this->add(143, 147, '00000');
        $this->add(148, 149, $boleto->getEspecieDocCodigo('01', '400'));
        $this->add(150, 150, $boleto->getAceite());
        $this->add(151, 156, $boleto->getDataDocumento()->format('dmy'));
        $this->add(157, 158, self::INSTRUCAO_SEM);
        $this->add(159, 160, self::INSTRUCAO_SEM);
        if ($boleto->getDiasProtesto() > 0) {
            $this->add(157, 158, self::INSTRUCAO_PROTESTAR);
        } elseif ($boleto->getDiasBaixaAutomatica() == 15) {
            $this->add(157, 158, self::INSTRUCAO_BAIXAR_APOS_VENC_15);
        } elseif ($boleto->getDiasBaixaAutomatica() == 30) {
            $this->add(157, 158, self::INSTRUCAO_BAIXAR_APOS_VENC_30);
        }
        $this->add(161, 173, Util::formatCnab('9', $boleto->getMoraDia(), 13, 2));
        $this->add(174, 179, $boleto->getDesconto() > 0 ? $boleto->getDataDesconto()->format('dmy') : '000000');
        $this->add(180, 192, Util::formatCnab('9', $boleto->getDesconto(), 13, 2));
        $this->add(193, 205, Util::formatCnab('9', 0, 13, 2));
        $this->add(206, 218, Util::formatCnab('9', 0, 13, 2));
        $this->add(219, 220, strlen(Util::onlyNumbers($boleto->getPagador()->getDocumento())) == 14 ? '02' : '01');
        $this->add(221, 234, Util::formatCnab('9L', $boleto->getPagador()->getDocumento(), 14));
        $this->add(235, 274, Util::formatCnab('X', $boleto->getPagador()->getNome(), 40));
        $this->add(275, 314, Util::formatCnab('X', $boleto->getPagador()->getEndereco(), 40));
        $this->add(315, 326, Util::formatCnab('X', $boleto->getPagador()->getBairro(), 12));
        $this->add(327, 334, Util::formatCnab('9L', $boleto->getPagador()->getCep(), 8));
        $this->add(335, 349, Util::formatCnab('X', $boleto->getPagador()->getCidade(), 15));
        $this->add(350, 351, Util::formatCnab('X', $boleto->getPagador()->getUf(), 2));
        $this->add(352, 381, Util::formatCnab('X', $boleto->getSacadorAvalista() ? $boleto->getSacadorAvalista()->getNome() : '', 30));
        $this->add(382, 382, '');
        $this->add(383, 383, 'I');
        $this->add(384, 385, substr($this->getConta(), -1) . (! is_null($this->getContaDv()) ? $this->getContaDv() : CalculoDV::santanderContaCorrente($this->getAgencia(), $this->getConta())));
        if (strlen($this->getConta()) == 9) {
            $this->add(384, 385, substr($this->getConta(), -2));
        }
        $this->add(386, 391, '');
        $this->add(392, 393, Util::formatCnab('9', $boleto->getDiasProtesto('0'), 2));
        $this->add(394, 394, '');
        $this->add(395, 400, Util::formatCnab('9', $this->iRegistros + 1, 6));
        if ($chaveNfe) {
            $this->add(401, 444, Util::formatCnab('9', $chaveNfe, 44));
        }

        if ($boleto->validarPix()) {
            $tipoChave = [
                $boleto::TIPO_CHAVEPIX_CPF       => 1,
                $boleto::TIPO_CHAVEPIX_CNPJ      => 2,
                $boleto::TIPO_CHAVEPIX_CELULAR   => 3,
                $boleto::TIPO_CHAVEPIX_EMAIL     => 4,
                $boleto::TIPO_CHAVEPIX_ALEATORIA => 5,
            ];
            $this->iniciaDetalhe();
            $this->add(1, 1, '8');
            $this->add(2, 3, '00'); // '00' = Conforme Perfil do Beneficiário; '01' = Aceita qualquer valor; ‘02’ = Entre o mínimo e o máximo; ‘03’ = Não aceita pagamento com o valor divergente
            $this->add(4, 5, '01'); // Quantidade de pagamentos
            $this->add(6, 6, '2'); // '1' = % (percentual); '2' = valor
            $this->add(7, 19, Util::formatCnab('9', $boleto->getValor(), 13, 2));
            $this->add(20, 24, Util::formatCnab('9', 200, 5, 2));
            $this->add(25, 37, Util::formatCnab('9', $boleto->getValor(), 13, 2));
            $this->add(38, 42, Util::formatCnab('9', 100, 5, 2));
            $this->add(43, 43, Util::formatCnab('9', $tipoChave[$boleto->getPixChaveTipo()], 1));
            $this->add(44, 120, Util::formatCnab('Z', $boleto->getPixChave(), 77));
            $this->add(121, 155, Util::formatCnab('X', $boleto->getID(), 35));
            $this->add(156, 394, '');
            $this->add(395, 400, Util::formatCnab('9', $this->iRegistros + 1, 6));
        }

        return $this;
    }

    /**
     * @return Santander
     * @throws ValidationException
     */
    protected function trailer()
    {
        $this->iniciaTrailer();

        $this->add(1, 1, '9');
        $this->add(2, 7, Util::formatCnab('9', $this->getCount(), 6));
        $this->add(8, 20, Util::formatCnab('9', $this->total, 13, 2));
        $this->add(21, 394, Util::formatCnab('9', 0, 374));
        $this->add(395, 400, Util::formatCnab('9', $this->getCount(), 6));

        return $this;
    }
}
