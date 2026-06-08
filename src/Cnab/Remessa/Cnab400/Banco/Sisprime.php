<?php

namespace Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400\Banco;

use Eduardokum\LaravelBoleto\Util;
use Eduardokum\LaravelBoleto\CalculoDV;
use Eduardokum\LaravelBoleto\Exception\ValidationException;
use Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400\AbstractRemessa;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;
use Eduardokum\LaravelBoleto\Contracts\Cnab\Remessa as RemessaContract;
use Illuminate\Support\Facades\Log;

class Sisprime extends AbstractRemessa implements RemessaContract
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
    protected $codigoBanco = BoletoContract::COD_BANCO_SISPRIME;

    /**
     * Define as carteiras disponíveis para cada banco
     *
     * @var array
     */
    protected $carteiras = ['9'];

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
                ! is_null($this->getContaDv()) ? $this->getContaDv() : CalculoDV::sisprimeContaCorrente($this->getConta());
        }

        return $this->codigoCliente;
    }

    /**
     * Seta o codigo do cliente.
     *
     * @param mixed $codigoCliente
     *
     * @return Sisprime
     */
    public function setCodigoCliente($codigoCliente)
    {
        $this->codigoCliente = $codigoCliente;

        return $this;
    }

    /**
     * @return Sisprime
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
        $this->add(27, 46, Util::formatCnab('9', $this->getCodigoCliente(), 20));
        $this->add(47, 76, Util::formatCnab('X', $this->getBeneficiario()->getNome(), 30));
        $this->add(77, 79, $this->getCodigoBanco());
        $this->add(80, 94, Util::formatCnab('X', 'SISPRIME', 15));
        $this->add(95, 100, $this->getDataRemessa('dmy')); //DDMMAA
        $this->add(101, 108, '');
        $this->add(109, 110, 'MX');
        $this->add(111, 117, Util::formatCnab('9', $this->getIdremessa(), 7));
        $this->add(118, 394, '');
        $this->add(395, 400, Util::formatCnab('9', 1, 6));

        return $this;
    }

    /**
     * @param \Eduardokum\LaravelBoleto\Boleto\Banco\Sisprime $boleto
     *
     * @return Sisprime
     * @throws ValidationException
     */
    public function addBoleto(BoletoContract $boleto)
    {
        $this->boletos[] = $boleto;
        $this->iniciaDetalhe();

        $this->add(1, 1, '1');
        $this->add(2, 20, Util::formatCnab('9','0', 19));

        $this->add(21, 21, '0');
        $this->add(22, 24, Util::formatCnab('9', $boleto->getCarteira(), 3));
        $this->add(25, 29, Util::formatCnab('9', $boleto->getAgencia(), 5));
        $this->add(30, 37, Util::formatCnab('9', $boleto->getConta().$boleto->getContaDv(), 8));

        $this->add(38, 62, Util::formatCnab('A', $boleto->getNumeroControle(), 25));
        $this->add(63, 65, $this->getCodigoBanco());
        $this->add(66,66, $boleto->getMulta() > 0 ? '2' : '0'); // se 2 considerar perc multa, se zero desconsidera multa 
        $this->add(67,70, Util::formatCnab('9', $boleto->getMulta(), 3));
        $this->add(71, 81, Util::formatCnab('9', $boleto->getNossoNumero(), 11));
        $this->add(82, 82, CalculoDV::sisprimeNossoNumero($boleto->getCarteira().$boleto->getNumero()));
        
        $this->add(83, 92, Util::formatCnab('9', 0, 10));
        $this->add(93, 93, 2);
        $this->add(94, 108, Util::formatCnab('A','0', 15));
        
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
        $this->add(140, 147, '00000000');
        $this->add(148, 149, $boleto->getEspecieDocCodigo());
        $this->add(150, 150, 'N');
        $this->add(151, 156, $boleto->getDataDocumento()->format('dmy'));
        $this->add(157, 160, '0000');
        $this->add(161, 173, Util::formatCnab('9', Util::percent($boleto->getValor(), $boleto->getJuros()), 13, 2));
        $this->add(174, 179, $boleto->getDesconto() > 0 ? $boleto->getDataDesconto()->format('dmy') : '000000');
        $this->add(180, 192, Util::formatCnab('9', $boleto->getDesconto(), 13, 2));
        $this->add(193, 205, '0000000000000');
        $this->add(206, 218, Util::formatCnab('9', 0, 13, 2));
        $this->add(219, 220, Util::isCnpj($boleto->getPagador()->getDocumento()) ? '02' : '01');
        $this->add(221, 234, Util::formatCnabDocumento($boleto->getPagador()->getDocumento(), 14));
        $this->add(235, 274, Util::formatCnab('X', $boleto->getPagador()->getNome(), 40));
        $this->add(275, 314, Util::formatCnab('X', $boleto->getPagador()->getEndereco(), 40));
        $this->add(315, 326, '000000000000');
        $this->add(327, 334, Util::formatCnab('9', Util::onlyNumbers($boleto->getPagador()->getCep()), 8));
        $this->add(335, 354, Util::formatCnab('X', $boleto->getPagador()->getBairro(), 20));
        $this->add(355, 392, Util::formatCnab('X', $boleto->getPagador()->getCidade(), 38));
        $this->add(393, 394, Util::formatCnab('X', $boleto->getPagador()->getUf(), 2));
        $this->add(395, 400, Util::formatCnab('9', $this->iRegistros + 1, 6));

        return $this;
    }

    /**
     * @return Sisprime
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
