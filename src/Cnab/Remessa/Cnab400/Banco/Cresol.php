<?php
namespace Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400\Banco;

use Eduardokum\LaravelBoleto\CalculoDV;
use Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400\AbstractRemessa;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;
use Eduardokum\LaravelBoleto\Contracts\Cnab\Remessa as RemessaContract;
use Eduardokum\LaravelBoleto\Util;
use Illuminate\Support\Facades\Log;

class Cresol extends AbstractRemessa implements RemessaContract
{
    const ESPECIE_DUPLICATA = '01';
    const ESPECIE_NOTA_PROMISSORIA = '02';
    const ESPECIE_DUPLICATA_SERVICO = '06';
    const ESPECIE_LETRA_CAMBIO = '07';

    const OCORRENCIA_REMESSA = '01';
    const OCORRENCIA_PEDIDO_BAIXA = '02';
    const OCORRENCIA_CONCESSAO_ABATIMENTO = '04';
    const OCORRENCIA_CANC_ABATIMENTO = '05';
    const OCORRENCIA_ALT_VENCIMENTO = '06';
    const OCORRENCIA_PROTESTAR = '09';
    const OCORRENCIA_SUSTAR_PROTESTO = '11';

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
        $this->addCampoObrigatorio('carteira');
    }


    /**
     * Código do banco
     *
     * @var string
     */
    protected $codigoBanco = BoletoContract::COD_BANCO_CRESOL;

    /**
     * Define as carteiras disponíveis para cada banco
     *
     * @var array
     */
    protected $carteiras = [9];

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
     * @return Cresol
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
     * @throws \Exception
     */
    public function getCodigoTransmissao()
    {
        $conta = $this->getConta();
        if (strlen($conta) >= 8) {
            $conta = substr($conta, 0, 7);
        }

        return Util::formatCnab('9', $conta, 20);
    }

    /**
     * Valor total dos titulos.
     *
     * @var float
     */
    private $total = 0;

    /**
     * @return $this
     * @throws \Exception
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
        $this->add(80, 94, Util::formatCnab('X', 'CRESOL', 15));
        $this->add(95, 100, $this->getDataRemessa('dmy'));
        $this->add(101, 116, Util::formatCnab('9', '0', 16));
        $this->add(117, 391, '');
        $this->add(392, 394, '000');
        $this->add(395, 400, Util::formatCnab('9', 1, 6));

        return $this;
    }

    /**
     * https://drive.google.com/file/d/1cNBoQyae6wylBVvVFOjNgNxSh6ZfuHG2/view 
     * padrao remessa CNAB400 cresol 133 pdf
     * @param BoletoContract $boleto
     *
     * @return $this
     * @throws \Exception
     */
    public function addBoleto(BoletoContract $boleto)
    {
        $this->boletos[] = $boleto;
        $this->iniciaDetalhe();

        $this->total += $boleto->getValor();

        $this->add(1, 1, '1');
        $this->add(2, 6, '');
        $this->add(7, 7, '');
        $this->add(8, 12, '');
        $this->add(13, 19, '');
        $this->add(20, 20, '');
        $this->add(21, 21, '0');
        $this->add(22, 24, Util::formatCnab('9',$this->getCarteiraNumero(),3));
        $this->add(25, 29, '01026');
        $this->add(30, 36, Util::formatCnab('9',$this->getConta(),7));
        $this->add(37, 37, $this->getContaDv());
        $this->add(38, 62, Util::formatCnab('X', $boleto->getNumeroControle(), 25)); // numero de controle
        $this->add(63, 65, '');
        $this->add(66, 66, ($boleto->getMulta() > 0 ? '2' : '0'));
        $this->add(67, 70, Util::formatCnab('9', $boleto->getMulta(), 4, 2));

        $nosso_numero_aux = Util::formatCnab('9',$this->getCarteiraNumero(),2). substr(Util::onlyNumbers($boleto->getNossoNumero()), -10);
        log::info("aqui comeca :".$nosso_numero_aux);
        $nosso_numero_aux = substr($nosso_numero_aux,0,11);
        log::info("aqui tira o digito :".$nosso_numero_aux);
        $dv_nosso_numero = CalculoDV::cresolNossoNumero(Util::formatCnab('9',$this->getCarteiraNumero(),2), $nosso_numero_aux);
        log::info("aqui clacula de novo digito :".$dv_nosso_numero);
        Log::info("aqui novo");
        Log::info($nosso_numero_aux .$dv_nosso_numero); 
        $this->add(71, 82, $nosso_numero_aux. $dv_nosso_numero ); // 12 digitos

        $this->add(83, 92, '');
        $this->add(93, 93, '2'); //cliente emite o boleto
        $this->add(94, 94, '');
        $this->add(95, 104, ''); 
        $this->add(105, 105, '');
        $this->add(106, 106, '');
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
        $this->add(121, 126, $boleto->getDataVencimento()->format('dmy'));
        $this->add(127, 139, Util::formatCnab('9', $boleto->getValor(), 13, 2));
        $this->add(140, 142, $this->getCodigoBanco());
        $this->add(143, 147, '');
        $this->add(148, 149, $boleto->getEspecieDocCodigo('02', '400'));
        $this->add(150, 150, $boleto->getAceite());
        $this->add(151, 156, $boleto->getDataDocumento()->format('dmy'));
        $this->add(157, 158, self::INSTRUCAO_SEM);
        $this->add(159, 160, self::INSTRUCAO_SEM);
        if ($boleto->getDiasProtesto() > 0) {
            $this->add(157, 158, self::INSTRUCAO_PROTESTAR);
        }
        $this->add(161, 173, Util::formatCnab('9', $boleto->getMoraDia(), 13, 2));
        $this->add(174, 179, $boleto->getDesconto() > 0 ? $boleto->getDataDesconto()->format('dmy') : '000000');
        $this->add(180, 192, Util::formatCnab('9', $boleto->getDesconto(), 13, 2));
        $this->add(193, 205, Util::formatCnab('9', 0, 13, 2));
        $this->add(206, 218, Util::formatCnab('9', 0, 13, 2));
        $this->add(219, 220, strlen(Util::onlyNumbers($boleto->getPagador()->getDocumento())) == 14 ? '02' : '01');
        $this->add(221, 234, Util::formatCnab('9L', $boleto->getPagador()->getDocumento(), 14));
        $this->add(235, 274, Util::formatCnab('X', $boleto->getPagador()->getNome(), 40));
        $this->add(275, 314, Util::formatCnab('X', $boleto->getPagador()->getEnderecoCompleto(), 40));
        $this->add(315, 326, Util::formatCnab('X', $boleto->getPagador()->getBairro(), 12));
        $this->add(327, 334, Util::formatCnab('9L', $boleto->getPagador()->getCep(), 8));
        $this->add(335, 394, Util::formatCnab('X', $boleto->getSacadorAvalista() ? $boleto->getSacadorAvalista()->getNome() : '', 30));
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
        $this->add(2, 394, Util::formatCnab('9', 0, 393));
        $this->add(395, 400, Util::formatCnab('9', $this->getCount(), 6));
        return $this;
    }
}
