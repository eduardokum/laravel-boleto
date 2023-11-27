<?php

namespace Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400\Banco;

use Carbon\Carbon;
use Eduardokum\LaravelBoleto\Util;
use Eduardokum\LaravelBoleto\Exception\ValidationException;
use Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400\AbstractRemessa;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;
use Eduardokum\LaravelBoleto\Contracts\Cnab\Remessa as RemessaContract;

class Sicredi extends AbstractRemessa implements RemessaContract
{
    const ESPECIE_DUPLICATA = 'A';
    const ESPECIE_DUPLICATA_RURAL = 'B';
    const ESPECIE_NOTA_PROMISSORIA = 'C';
    const ESPECIE_NOTA_PROMISSORIA_RURAL = 'D';
    const ESPECIE_NOTA_SEGURO = 'E';
    const ESPECIE_RECIBO = 'G';
    const ESPECIE_LETRA_CAMBIO = 'H';
    const ESPECIE_NOTA_DEBITOS = 'I';
    const ESPECIE_NOTA_SERVICOS = 'J';
    const ESPECIE_OUTROS = 'K';
    const OCORRENCIA_REMESSA = '01';
    const OCORRENCIA_BAIXA = '02';
    const OCORRENCIA_CONCESSAO_ABATIMENTO = '04';
    const OCORRENCIA_CANC_ABATIMENTO = '05';
    const OCORRENCIA_ALT_VENCIMENTO = '06';
    const OCORRENCIA_PROTESTAR = '09';
    const OCORRENCIA_SUSTAR_PROTESTO = '18';
    const OCORRENCIA_SUSTAR_PROTESTO_MANTER_CARTEIRA = '19';
    const OCORRENCIA_ALT_OUTROS_DADOS = '31';
    const INSTRUCAO_SEM = '00';
    const INSTRUCAO_PROTESTO = '06';

    public function __construct(array $params)
    {
        parent::__construct($params);
        $this->setCarteira('A'); //Carteira Simples 'A'
        $this->addCampoObrigatorio('idremessa');
    }

    /**
     * Define o código da carteira (Com ou sem registro)
     *
     * @param string $carteira
     *
     * @return AbstractRemessa
     */
    public function setCarteira($carteira)
    {
        $this->carteira = 'A';

        return $this;
    }

    /**
     * Código do banco
     *
     * @var string
     */
    protected $codigoBanco = BoletoContract::COD_BANCO_SICREDI;

    /**
     * Define as carteiras disponíveis para cada banco
     *
     * @var array
     */
    protected $carteiras = ['A'];

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
     * @return Sicredi
     */
    public function setCodigoCliente($codigoCliente)
    {
        $this->codigoCliente = $codigoCliente;

        return $this;
    }

    /**
     * @return Sicredi
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
        $this->add(27, 31, Util::formatCnab('9', $this->getCodigoCliente(), 5));
        $this->add(32, 45, Util::formatCnab('9L', $this->getBeneficiario()->getDocumento(), 14, 0, 0));
        $this->add(46, 76, '');
        $this->add(77, 79, $this->getCodigoBanco());
        $this->add(80, 94, Util::formatCnab('X', 'Sicredi', 15));
        $this->add(95, 102, $this->getDataRemessa('Ymd'));
        $this->add(103, 110, '');
        $this->add(111, 117, Util::formatCnab('9', $this->getIdremessa(), 7));
        $this->add(118, 390, '');
        $this->add(391, 394, '2.00');
        $this->add(395, 400, Util::formatCnab('9', 1, 6));

        return $this;
    }

    /**
     * @param \Eduardokum\LaravelBoleto\Boleto\Banco\Sicredi $boleto
     *
     * @return Sicredi
     * @throws ValidationException
     */
    public function addBoleto(BoletoContract $boleto)
    {
        $this->boletos[] = $boleto;
        if (! $boleto->isComRegistro()) {
            return $this;
        }

        if ($chaveNfe = $boleto->getChaveNfe()) {
            $this->iniciaDetalheExtendido();
        } else {
            $this->iniciaDetalhe();
        }

        $this->add(1, 1, '1');
        $this->add(2, 2, 'A');
        $this->add(3, 3, $this->getCarteiraNumero());
        $this->add(4, 4, 'A');
        $this->add(5, 16, '');
        $this->add(17, 17, 'A');
        $this->add(18, 18, 'A');
        $this->add(19, 19, 'A');
        $this->add(20, 47, '');
        $this->add(48, 56, Util::formatCnab('9', $boleto->getNossoNumero(), 9));
        $this->add(57, 62, '');
        $this->add(63, 70, Carbon::now()->format('Ymd'));
        $this->add(71, 71, '');
        $this->add(72, 72, $boleto->getByte() == 1 ? 'S' : 'N');
        $this->add(73, 73, '');
        $this->add(74, 74, $boleto->getByte() == 1 ? 'A' : 'B');
        $this->add(75, 76, '');
        $this->add(77, 78, '');
        $this->add(79, 82, '');
        $this->add(83, 92, Util::formatCnab('9', 0, 10, 2));
        $this->add(93, 96, Util::formatCnab('9', $boleto->getMulta(), 4, 2));
        $this->add(97, 108, '');
        $this->add(109, 110, self::OCORRENCIA_REMESSA); // REGISTRO
        if ($boleto->getStatus() == $boleto::STATUS_BAIXA) {
            $this->add(109, 110, self::OCORRENCIA_BAIXA); // BAIXA
        }
        if ($boleto->getStatus() == $boleto::STATUS_ALTERACAO) {
            $this->add(109, 110, self::OCORRENCIA_ALT_OUTROS_DADOS); // ALTERAR OUTROS DADOS (Endereço, etc.)
        }
        if ($boleto->getStatus() == $boleto::STATUS_ALTERACAO_DATA) {
            $this->add(109, 110, self::OCORRENCIA_ALT_VENCIMENTO); // ALTERAR VENCIMENTO
        }
        if ($boleto->getStatus() == $boleto::STATUS_CUSTOM) {
            $this->add(109, 110, sprintf('%2.02s', $boleto->getComando()));
        }
        $this->add(111, 120, Util::formatCnab('X', $boleto->getNumeroDocumento(), 10));
        $this->add(121, 126, $boleto->getDataVencimento()->format('dmy'));
        $this->add(127, 139, Util::formatCnab('9', $boleto->getValor(), 13, 2));
        $this->add(140, 148, '');
        $this->add(149, 149, $boleto->getEspecieDocCodigo('A', 400));
        $this->add(150, 150, $boleto->getAceite());
        $this->add(151, 156, $boleto->getDataDocumento()->format('dmy'));
        $this->add(157, 158, self::INSTRUCAO_SEM);
        $this->add(159, 160, self::INSTRUCAO_SEM);
        if ($boleto->getDiasProtesto() > 0) {
            $this->add(157, 158, self::INSTRUCAO_PROTESTO);
            $this->add(159, 160, Util::formatCnab('9', $boleto->getDiasProtesto(), 2));
        }
        $this->add(161, 173, Util::formatCnab('9', $boleto->getMoraDia(), 13, 2));
        $this->add(174, 179, $boleto->getDesconto() > 0 ? $boleto->getDataDesconto()->format('dmy') : '000000');
        $this->add(180, 192, Util::formatCnab('9', $boleto->getDesconto(), 13, 2));
        $this->add(193, 205, Util::formatCnab('9', 0, 13, 2));
        $this->add(206, 218, Util::formatCnab('9', 0, 13, 2));
        $this->add(219, 220, strlen(Util::onlyNumbers($boleto->getPagador()->getDocumento())) == 14 ? '20' : '10');
        $this->add(221, 234, Util::formatCnab('9L', $boleto->getPagador()->getDocumento(), 14));
        $this->add(235, 274, Util::formatCnab('X', $boleto->getPagador()->getNome(), 40));
        $this->add(275, 314, Util::formatCnab('X', $boleto->getPagador()->getEndereco(), 40));
        $this->add(315, 319, '00000');
        $this->add(320, 325, '000000');
        $this->add(326, 326, ' ');
        $this->add(327, 334, Util::formatCnab('9L', $boleto->getPagador()->getCep(), 8));
        $this->add(335, 339, '00000');
        $this->add(340, 353, $boleto->getSacadorAvalista() ? Util::formatCnab('9L', $boleto->getSacadorAvalista()->getDocumento(), 14) : Util::formatCnab('X', '', 14));
        $this->add(354, 394, Util::formatCnab('X', $boleto->getSacadorAvalista() ? $boleto->getSacadorAvalista()->getNome() : '', 41));
        $this->add(395, 400, Util::formatCnab('9', $this->iRegistros + 1, 6));
        if ($chaveNfe) {
            $this->add(401, 444, Util::formatCnab('9', $chaveNfe, 44));
        }

        if ($boleto->getByte() == 1) {
            $this->iniciaDetalhe();

            $this->add(1, 1, '2');
            $this->add(2, 12, '');
            $this->add(13, 21, Util::formatCnab('9', $boleto->getNossoNumero(), 9));
            $this->add(22, 101, Util::formatCnab('X', $boleto->getInstrucoes()[0], 80));
            $this->add(102, 181, Util::formatCnab('X', $boleto->getInstrucoes()[1], 80));
            $this->add(182, 261, Util::formatCnab('X', $boleto->getInstrucoes()[2], 80));
            $this->add(262, 341, Util::formatCnab('X', $boleto->getInstrucoes()[3], 80));
            $this->add(342, 351, Util::formatCnab('9', $boleto->getNumeroDocumento(), 10));
            $this->add(352, 394, '');
            $this->add(395, 400, Util::formatCnab('9', $this->iRegistros + 1, 6));
        }

        return $this;
    }

    /**
     * @return Sicredi
     * @throws ValidationException
     */
    protected function trailer()
    {
        $this->iniciaTrailer();

        $this->add(1, 1, '9');
        $this->add(2, 2, '1');
        $this->add(3, 5, $this->getCodigoBanco());
        $this->add(6, 10, $this->getCodigoCliente());
        $this->add(11, 394, '');
        $this->add(395, 400, Util::formatCnab('9', $this->getCount(), 6));

        return $this;
    }
}
