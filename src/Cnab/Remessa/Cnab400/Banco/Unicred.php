<?php
namespace Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400\Banco;

use Carbon\Carbon;
use Eduardokum\LaravelBoleto\CalculoDV;
use Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400\AbstractRemessa;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;
use Eduardokum\LaravelBoleto\Contracts\Cnab\Remessa as RemessaContract;
use Eduardokum\LaravelBoleto\Util;

class Unicred extends AbstractRemessa implements RemessaContract
{
    const OCORRENCIA_REMESSA = '01';
    const OCORRENCIA_BAIXA = '02';
    const OCORRENCIA_CONCESSAO_ABATIMENTO = '04';
    const OCORRENCIA_CANC_ABATIMENTO = '05';
    const OCORRENCIA_ALT_VENCIMENTO = '06';
    const OCORRENCIA_ALT_SEU_NUMERO = '08';
    const OCORRENCIA_PROTESTAR = '09';
    const OCORRENCIA_SUSTAR_PROTESTO = '18';
    const OCORRENCIA_SUSTAR_PROTESTO_MANTER_CARTEIRA = '11';
    const OCORRENCIA_SUSTAR_PROTESTO_BAIXAR_TITULO = '25';

    const INSTRUCAO_PROTESTO = '26';
    const INSTRUCAO_PROTESTO_DIAS_CORR = '1';
    const INSTRUCAO_PROTESTO_DIAS_UTEIS = '2';
    const INSTRUCAO_PROTESTO_NAO = '3';

    const INSTRUCAO_ALT_CARTEIRA = '40';

    const INSTRUCAO_SEM = '00';

    public function __construct(array $params)
    {
        parent::__construct($params);
        $this->carteira = 21;
        $this->addCampoObrigatorio('idremessa');
    }

    /**
     * Código do banco
     *
     * @var string
     */
    protected $codigoBanco = BoletoContract::COD_BANCO_SICREDI;

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
     * Define as carteiras disponíveis para cada banco
     *
     * @var array
     */
    protected $carteiras = [21];

    /**
     * @return $this
     * @throws \Exception
     */
    protected function header()
    {
        $this->iniciaHeader();

        $this->add(1, 1, '0');
        $this->add(2, 2, '1');
        $this->add(3, 9, 'Remessa');
        $this->add(10, 11, '01');
        $this->add(12, 26, Util::formatCnab('X', 'Cobrança', 15));
        $this->add(27, 46, Util::formatCnab('9', $this->getCodigoCliente(), 20));
        $this->add(47, 76, $this->getBeneficiario()->getNome());
        $this->add(77, 79, $this->getCodigoBanco());
        $this->add(80, 94, Util::formatCnab('X', 'UNICRED', 15));
        $this->add(95, 100, $this->getDataRemessa('dmy'));
        $this->add(101, 107, '');
        $this->add(108, 110, '000');
        $this->add(111, 117, Util::formatCnab('9', $this->getIdremessa(), 7));
        $this->add(118, 394, '');
        $this->add(395, 400, Util::formatCnab('9', 1, 6));

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
        if (!$boleto->isComRegistro()) {
            return $this;
        }

        $this->iniciaDetalhe();

        $this->add(1, 1, '1');
        $this->add(2, 6, Util::formatCnab('9', $this->getAgencia(),5));
        $this->add(7, 7, CalculoDV::unicredAgencia($this->getAgencia()));
        $this->add(8, 19, Util::formatCnab('9', $this->getConta(), 12));
        $this->add(20, 20, $this->getContaDv() ?: CalculoDV::unicredContaCorrente($this->getConta()));
        $this->add(21, 21, '0');
        $this->add(22, 24, Util::formatCnab('9', $this->getCarteiraNumero(),'3'));
        $this->add(25, 37, Util::formatCnab('9', '0', 13));
        $this->add(38, 62, '');
        $this->add(63, 65, $this->getCodigoBanco());
        $this->add(66, 67, '00');
        $this->add(68, 92, '');
        $this->add(93, 94, '0');

        if ($boleto->getMulta() > 0) {
            $this->add(94, 94, '2'); //percentual
            $this->add(95, 104, Util::formatCnab('9', $boleto->getMulta(), 10, 2));
            $this->add(105,105, '2'); // mora de juros: mensal
        } else {
            $this->add(94, 94, '3'); //isento
            $this->add(95, 104, Util::formatCnab('9', '0', 10, 2));
            $this->add(105,105, '5'); //isento
        }

        $this->add(106,106,'N');
        $this->add(107, 108, '');
        $this->add(109, 110, self::OCORRENCIA_REMESSA); // REGISTRO
        $this->add(111, 120, Util::formatCnab('X', $boleto->getNumeroDocumento(), 9));
        $this->add(121, 126, $boleto->getDataVencimento()->format('dmy'));
        $this->add(127, 139, Util::formatCnab('9', $boleto->getValor(), 13, 2));
        $this->add(140, 149, '0');
        $this->add(150, 150, $boleto->getDesconto() > 0 ? 1 : 0);
        $this->add(151, 156, $boleto->getDataDocumento()->format('dmy'));
        $this->add(157, 157, '0');
        $this->add(158, 158, '3');
        $this->add(159, 160, self::INSTRUCAO_SEM);
        if ($boleto->getDiasProtesto() > 0) {
            $this->add(158, 158, self::INSTRUCAO_PROTESTO_DIAS_UTEIS);
            $this->add(159, 160, Util::formatCnab('9', $boleto->getDiasProtesto(), 2));
        }
        $this->add(161, 173, Util::formatCnab('9', $boleto->getMoraDia(), 13, 2));
        $this->add(174, 179, $boleto->getDesconto() > 0 ? $boleto->getDataDesconto()->format('dmy') : '000000');
        $this->add(180, 192, Util::formatCnab('9', $boleto->getDesconto(), 13, 2));
        $this->add(193, 203, Util::formatCnab('9', $boleto->getNossoNumero(), 11));
        $this->add(204, 205, '0');
        $this->add(206, 218, Util::formatCnab('9','0','13'));
        $this->add(219, 220, strlen(Util::onlyNumbers($boleto->getPagador()->getDocumento())) == 14 ? '02' : '01');
        $this->add(221, 234, Util::formatCnab('9L', $boleto->getPagador()->getDocumento(), 14));
        $this->add(235, 274, Util::formatCnab('X', $boleto->getPagador()->getNome(), 40));
        $this->add(275, 314, Util::formatCnab('X', $boleto->getPagador()->getEndereco(), 40));
        $this->add(315, 326, Util::formatCnab('X', $boleto->getPagador()->getBairro(), 12));
        $this->add(327, 334, Util::formatCnab('9L', $boleto->getPagador()->getCep(), 8));
        $this->add(335, 354, Util::formatCnab('X', $boleto->getPagador()->getCidade(), 20));
        $this->add(355, 356, Util::formatCnab('X', $boleto->getPagador()->getUf(), 2));
        $this->add(357, 394, Util::formatCnab('9','0','38'));
        $this->add(395, 400, Util::formatCnab('9', $this->iRegistros + 1, 6));

        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
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
