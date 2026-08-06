<?php

namespace Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab240\Banco;

use Eduardokum\LaravelBoleto\Util;
use Eduardokum\LaravelBoleto\CalculoDV;
use Eduardokum\LaravelBoleto\Exception\ValidationException;
use Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab240\AbstractRemessa;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;
use Eduardokum\LaravelBoleto\Contracts\Cnab\Remessa as RemessaContract;
use Eduardokum\LaravelBoleto\Cnab\Remessa\Traits\ValidacoesCresol;

/**
 * Remessa CNAB 240 da Cobrança Integrada Cresol (banco 133).
 *
 * Referência: "Manual Cobrança Integrada Cresol CNAB 240 – Cooperado", versão 1.0.2,
 * seções 4.1.2 a 4.1.8.
 */
class Cresol extends AbstractRemessa implements RemessaContract
{
    use ValidacoesCresol;

    /**
     * Códigos de movimento aceitos no segmento P, pos. 016-017 (seção 4.1.4).
     * O manual do 240 não prevê "31 = alteração de outros dados", presente no 400.
     */
    const OCORRENCIA_REMESSA = '01';

    const OCORRENCIA_PEDIDO_BAIXA = '02';
    const OCORRENCIA_CONCESSAO_ABATIMENTO = '04';
    const OCORRENCIA_CANC_ABATIMENTO = '05';
    const OCORRENCIA_ALT_VENCIMENTO = '06';
    const OCORRENCIA_PROTESTAR = '09';
    const OCORRENCIA_CANC_PROTESTO = '10';
    const OCORRENCIA_SUSTAR_PROTESTO_MANTER_CARTEIRA = '11';
    const PROTESTO_DIAS_CORRIDOS = '1';
    const PROTESTO_NAO_PROTESTAR = '3';
    const MOEDA_REAL = '09';

    public function __construct(array $params = [])
    {
        parent::__construct($params);
        $this->addCampoObrigatorio('agencia', 'conta');
    }

    /**
     * Código do banco
     *
     * @var string
     */
    protected $codigoBanco = BoletoContract::COD_BANCO_CRESOL;

    /**
     * Define as carteiras disponíveis para cada banco. A Cresol opera apenas a
     * carteira 09 (seção 3.1 do manual).
     *
     * @var array
     */
    protected $carteiras = ['09'];

    /**
     * Nome do banco impresso no header de arquivo, pos. 103-132
     *
     * @var string
     */
    protected $nomeBanco = 'CRESOL';

    /**
     * Retorna o dígito da conta, calculando-o quando não informado
     *
     * @return string
     */
    public function getContaDv()
    {
        return parent::getContaDv() !== null && parent::getContaDv() !== ''
            ? parent::getContaDv()
            : CalculoDV::cresolContaCorrente($this->getConta());
    }

    /**
     * Código do convênio no banco, header de lote pos. 034-053 (seção 4.1.3):
     * 4 zeros + carteira (3) + cooperativa (5) + conta corrente (7) + dígito (1).
     *
     * @return string
     * @throws ValidationException
     */
    public function getConvenio()
    {
        return Util::formatCnab('9', 0, 4)
            . Util::formatCnab('9', $this->getCarteira(), 3)
            . Util::formatCnab('9', $this->getAgencia(), 5)
            . Util::formatCnab('9', $this->getConta(), 7)
            . Util::formatCnab('9', $this->getContaDv(), 1);
    }

    /**
     * @param BoletoContract $boleto
     *
     * @return Cresol
     * @throws ValidationException
     */
    public function addBoleto(BoletoContract $boleto)
    {
        $this->validaFaixaNossoNumero($boleto);
        $this->validaNossoNumeroNumerico($boleto);
        $this->validaMultaPercentual($boleto);

        $this->boletos[] = $boleto;
        $this->segmentoP($boleto);
        $this->segmentoQ($boleto);
        $this->segmentoR($boleto);

        return $this;
    }

    /**
     * O dígito do nosso número pode ser a letra "P" (item 3 das Especificações Técnicas
     * Cresol), mas o segmento P declara a posição 057 como numérica. Um título assim
     * derruba a validação de layout e, com ela, o arquivo inteiro — então é recusado aqui.
     *
     * @param BoletoContract $boleto
     *
     * @return void
     * @throws ValidationException
     */
    protected function validaNossoNumeroNumerico(BoletoContract $boleto)
    {
        if (method_exists($boleto, 'nossoNumeroDvEhLetra') && $boleto->nossoNumeroDvEhLetra()) {
            throw new ValidationException(sprintf(
                'Nosso número %s possui dígito verificador "%s". O segmento P do CNAB 240 da Cresol declara a posição 57 como numérica, use outro nosso número ou gere a remessa em CNAB 400.',
                $boleto->getNumero(),
                $boleto->getNossoNumeroDv()
            ));
        }
    }

    /**
     * Grava o código de movimento (pos. 016-017), comum aos três segmentos
     *
     * @param BoletoContract $boleto
     *
     * @return void
     * @throws ValidationException
     */
    protected function addCodigoMovimento(BoletoContract $boleto)
    {
        $this->add(16, 17, self::OCORRENCIA_REMESSA);

        if ($boleto->getStatus() == $boleto::STATUS_BAIXA) {
            $this->add(16, 17, self::OCORRENCIA_PEDIDO_BAIXA);
        }
        if ($boleto->getStatus() == $boleto::STATUS_ALTERACAO_DATA) {
            $this->add(16, 17, self::OCORRENCIA_ALT_VENCIMENTO);
        }
        if ($boleto->getStatus() == $boleto::STATUS_CUSTOM) {
            $this->add(16, 17, sprintf('%2.02s', $boleto->getComando()));
        }
        if ($boleto->getStatus() == $boleto::STATUS_ALTERACAO) {
            throw new ValidationException('O CNAB 240 da Cresol não possui código de movimento para alteração de outros dados. Informe o código desejado via STATUS_CUSTOM ou use o CNAB 400.');
        }
    }

    /**
     * Cabeçalho comum dos registros de detalhe (pos. 001-017)
     *
     * @param BoletoContract $boleto
     * @param string         $segmento
     *
     * @return void
     * @throws ValidationException
     */
    protected function iniciaSegmento(BoletoContract $boleto, $segmento)
    {
        $this->iniciaDetalhe();
        $this->add(1, 3, Util::onlyNumbers($this->getCodigoBanco()));
        $this->add(4, 7, '0001');
        $this->add(8, 8, '3');
        $this->add(9, 13, Util::formatCnab('9', $this->iRegistrosLote, 5));
        $this->add(14, 14, $segmento);
        $this->add(15, 15, '');
        $this->addCodigoMovimento($boleto);
    }

    /**
     * Segmento P — obrigatório (seção 4.1.4)
     *
     * @param BoletoContract $boleto
     *
     * @return Cresol
     * @throws ValidationException
     */
    protected function segmentoP(BoletoContract $boleto)
    {
        $this->iniciaSegmento($boleto, 'P');

        $this->add(18, 22, Util::formatCnab('9', $this->getAgencia(), 5));
        $this->add(23, 23, '');
        $this->add(24, 35, Util::formatCnab('9', $this->getConta(), 12));
        $this->add(36, 36, Util::formatCnab('9', $this->getContaDv(), 1));
        $this->add(37, 37, '');
        $this->add(38, 40, '000');
        $this->add(41, 45, '00000');
        // 046-056 nosso número e 057 dígito, ambos numéricos no manual
        $this->add(46, 56, Util::formatCnab('9', substr($boleto->getNossoNumero(), 0, 11), 11));
        $this->add(57, 57, Util::formatCnab('9', substr($boleto->getNossoNumero(), -1), 1));
        $this->add(58, 58, '0');
        $this->add(59, 59, '0');
        $this->add(60, 60, '0');
        $this->add(61, 61, '2'); // '2' = Cliente emite
        $this->add(62, 62, '2'); // '2' = Cliente distribui
        $this->add(63, 77, Util::formatCnab('X', $boleto->getNumeroDocumento(), 15));
        $this->add(78, 85, $boleto->getDataVencimento()->format('dmY'));
        $this->add(86, 100, Util::formatCnab('9', $boleto->getValor(), 15, 2));
        $this->add(101, 105, '00000');
        $this->add(106, 106, '');
        $this->add(107, 108, Util::formatCnab('9', $boleto->getEspecieDocCodigo('99', 240), 2));
        $this->add(109, 109, Util::formatCnab('X', $boleto->getAceite(), 1));
        $this->add(110, 117, $boleto->getDataDocumento()->format('dmY'));
        // 118 aceita apenas "1 = real ao dia" no manual Cresol, então 127-141 tem que
        // levar o valor em reais por dia (getMoraDia), e não a taxa mensal (getJuros)
        $this->add(118, 118, '1');
        $this->add(119, 126, $boleto->getJuros() > 0
            ? $boleto->getDataVencimentoApos()->copy()->addDays((int) $boleto->getJurosApos())->format('dmY')
            : '00000000');
        $this->add(127, 141, Util::formatCnab('9', $boleto->getMoraDia(), 15, 2));
        $this->preencheDescontoSegmentoP($boleto);
        $this->add(166, 180, Util::formatCnab('9', 0, 15, 2));
        // 181-195 abatimento: o manual só o considera com código de movimento 04, e o
        // boleto não possui esse campo, então vai zerado
        $this->add(181, 195, Util::formatCnab('9', 0, 15, 2));
        $this->add(196, 220, Util::formatCnab('X', $boleto->getNumeroControle(), 25));
        $this->add(221, 221, self::PROTESTO_NAO_PROTESTAR);
        $this->add(222, 223, '00');
        if ($boleto->getDiasProtesto() > 0) {
            $this->add(221, 221, self::PROTESTO_DIAS_CORRIDOS);
            $this->add(222, 223, Util::formatCnab('9', $boleto->getDiasProtesto(), 2));
        }
        $this->add(224, 224, $boleto->getDiasBaixaAutomatica() > 0 ? '1' : '2');
        $this->add(225, 227, Util::formatCnab('9', $boleto->getDiasBaixaAutomatica(), 3));
        $this->add(228, 229, self::MOEDA_REAL);
        $this->add(230, 240, Util::formatCnab('9', 0, 11));

        return $this;
    }

    /**
     * Segmento Q — obrigatório (seção 4.1.5).
     *
     * O manual fixa em zeros/brancos os campos de sacador/avalista (pos. 154-209), então
     * o avalista do boleto não é transmitido neste layout.
     *
     * @param BoletoContract $boleto
     *
     * @return Cresol
     * @throws ValidationException
     */
    protected function segmentoQ(BoletoContract $boleto)
    {
        $this->iniciaSegmento($boleto, 'Q');

        $pagador = $boleto->getPagador();
        $documento = Util::onlyNumbers($pagador->getDocumento());

        $this->add(18, 18, strlen($documento) == 14 ? '2' : '1');
        $this->add(19, 33, Util::formatCnab('9', $documento, 15));
        $this->add(34, 73, Util::formatCnab('X', str_replace('&', 'E', Util::normalizeChars($pagador->getNome())), 40));
        $this->add(74, 113, Util::formatCnab('X', str_replace('&', 'E', Util::normalizeChars($pagador->getEndereco())), 40));
        $this->add(114, 128, Util::formatCnab('X', str_replace('&', 'E', Util::normalizeChars($pagador->getBairro())), 15));
        $this->add(129, 133, Util::formatCnab('9', substr(Util::onlyNumbers($pagador->getCep()), 0, 5), 5));
        $this->add(134, 136, Util::formatCnab('9', substr(Util::onlyNumbers($pagador->getCep()), 5, 3), 3));
        $this->add(137, 151, Util::formatCnab('X', str_replace('&', 'E', Util::normalizeChars($pagador->getCidade())), 15));
        $this->add(152, 153, Util::formatCnab('X', $pagador->getUf(), 2));
        $this->add(154, 154, '0');
        $this->add(155, 169, Util::formatCnab('9', 0, 15));
        $this->add(170, 209, '');
        $this->add(210, 212, '000');
        $this->add(213, 232, '');
        $this->add(233, 240, '');

        return $this;
    }

    /**
     * Segmento R — opcional (seção 4.1.6). A única informação que a Cresol lê aqui e que
     * a lib produz é a multa, então o registro só é gravado quando há multa. Descontos 2
     * e 3 não são utilizados pela Cresol e vão zerados; as mensagens 3 e 4 são impressas
     * pelo banco e ficam em branco, já que o boleto é emitido pelo próprio cooperado.
     *
     * @param BoletoContract $boleto
     *
     * @return Cresol
     * @throws ValidationException
     */
    protected function segmentoR(BoletoContract $boleto)
    {
        if (! ($boleto->getMulta() > 0)) {
            return $this;
        }

        $this->iniciaSegmento($boleto, 'R');

        $this->add(18, 18, '0');
        $this->add(19, 26, '00000000');
        $this->add(27, 41, Util::formatCnab('9', 0, 15, 2));
        $this->add(42, 42, '0');
        $this->add(43, 50, '00000000');
        $this->add(51, 65, Util::formatCnab('9', 0, 15, 2));
        $this->add(66, 66, '0');
        $this->add(67, 74, '00000000');
        $this->add(75, 89, Util::formatCnab('9', 0, 15, 2));
        if ($boleto->getMulta() > 0) {
            // 066 aceita apenas "2 = percentual" no manual Cresol, de 0,00% a 99,99%
            $this->add(66, 66, '2');
            $this->add(67, 74, $boleto->getDataVencimento()->format('dmY'));
            $this->add(75, 89, Util::formatCnab('9', $boleto->getMulta(), 15, 2));
        }
        $this->add(90, 99, '');
        $this->add(100, 139, '');
        $this->add(140, 179, '');
        $this->add(180, 199, '');
        $this->add(200, 207, Util::formatCnab('9', 0, 8));
        $this->add(208, 210, '000');
        $this->add(211, 215, '00000');
        $this->add(216, 216, '');
        $this->add(217, 228, Util::formatCnab('9', 0, 12));
        $this->add(229, 229, '');
        $this->add(230, 230, '');
        $this->add(231, 231, '0');
        $this->add(232, 240, '');

        return $this;
    }

    /**
     * Header de arquivo (seção 4.1.2)
     *
     * @return Cresol
     * @throws ValidationException
     */
    protected function header()
    {
        $this->iniciaHeader();

        $documento = Util::onlyNumbers($this->getBeneficiario()->getDocumento());

        $this->add(1, 3, Util::onlyNumbers($this->getCodigoBanco()));
        $this->add(4, 7, '0000');
        $this->add(8, 8, '0');
        $this->add(9, 17, '');
        $this->add(18, 18, strlen($documento) == 14 ? '2' : '1');
        $this->add(19, 32, Util::formatCnab('9', $documento, 14));
        // 033-052 "Número do convênio de cobrança Cresol" = número da conta do cooperado
        $this->add(33, 52, Util::formatCnab('9', $this->getConta(), 20));
        $this->add(53, 57, Util::formatCnab('9', $this->getAgencia(), 5));
        $this->add(58, 58, '0');
        $this->add(59, 70, Util::formatCnab('9', $this->getConta(), 12));
        $this->add(71, 71, Util::formatCnab('9', $this->getContaDv(), 1));
        $this->add(72, 72, '');
        $this->add(73, 102, Util::formatCnab('X', str_replace('&', 'E', Util::normalizeChars($this->getBeneficiario()->getNome())), 30));
        $this->add(103, 132, Util::formatCnab('X', $this->nomeBanco, 30));
        $this->add(133, 142, '');
        $this->add(143, 143, '1');
        $this->add(144, 151, $this->getDataRemessa('dmY'));
        $this->add(152, 157, date('His'));
        // O manual fixa zeros no número sequencial do arquivo (pos. 158-163)
        $this->add(158, 163, Util::formatCnab('9', 0, 6));
        $this->add(164, 166, '084');
        $this->add(167, 171, '00000');
        $this->add(172, 191, '');
        $this->add(192, 211, '');
        $this->add(212, 240, '');

        return $this;
    }

    /**
     * Header de lote (seção 4.1.3)
     *
     * @return Cresol
     * @throws ValidationException
     */
    protected function headerLote()
    {
        $this->iniciaHeaderLote();

        $documento = Util::onlyNumbers($this->getBeneficiario()->getDocumento());

        $this->add(1, 3, Util::onlyNumbers($this->getCodigoBanco()));
        $this->add(4, 7, '0001');
        $this->add(8, 8, '1');
        $this->add(9, 9, 'R');
        $this->add(10, 11, '01');
        $this->add(12, 13, '');
        $this->add(14, 16, '042');
        $this->add(17, 17, '');
        $this->add(18, 18, strlen($documento) == 14 ? '2' : '1');
        $this->add(19, 33, Util::formatCnab('9', $documento, 15));
        $this->add(34, 53, $this->getConvenio());
        $this->add(54, 58, Util::formatCnab('9', $this->getAgencia(), 5));
        $this->add(59, 59, '');
        $this->add(60, 71, Util::formatCnab('9', $this->getConta(), 12));
        $this->add(72, 72, Util::formatCnab('9', $this->getContaDv(), 1));
        $this->add(73, 73, '');
        $this->add(74, 103, Util::formatCnab('X', str_replace('&', 'E', Util::normalizeChars($this->getBeneficiario()->getNome())), 30));
        $this->add(104, 143, '');
        $this->add(144, 183, '');
        $this->add(184, 191, Util::formatCnab('9', 0, 8));
        $this->add(192, 199, Util::formatCnab('9', 0, 8));
        $this->add(200, 207, Util::formatCnab('9', 0, 8));
        $this->add(208, 240, '');

        return $this;
    }

    /**
     * Trailer de lote (seção 4.1.7). A quantidade inclui header de lote, detalhes e o
     * próprio trailer.
     *
     * @return Cresol
     * @throws ValidationException
     */
    protected function trailerLote()
    {
        $this->iniciaTrailerLote();

        $this->add(1, 3, Util::onlyNumbers($this->getCodigoBanco()));
        $this->add(4, 7, '0001');
        $this->add(8, 8, '5');
        $this->add(9, 17, '');
        $this->add(18, 23, Util::formatCnab('9', $this->getCountDetalhes() + 2, 6));
        $this->add(24, 115, Util::formatCnab('9', 0, 92));
        $this->add(116, 240, '');

        return $this;
    }

    /**
     * Trailer de arquivo (seção 4.1.8)
     *
     * @return Cresol
     * @throws ValidationException
     */
    protected function trailer()
    {
        $this->iniciaTrailer();

        $this->add(1, 3, Util::onlyNumbers($this->getCodigoBanco()));
        $this->add(4, 7, '9999');
        $this->add(8, 8, '9');
        $this->add(9, 17, '');
        $this->add(18, 23, Util::formatCnab('9', 1, 6));
        $this->add(24, 29, Util::formatCnab('9', $this->getCount(), 6));
        $this->add(30, 35, Util::formatCnab('9', 0, 6));
        $this->add(36, 240, '');

        return $this;
    }

    /**
     * O segmento P da Cresol aceita apenas "0 = sem desconto" e "1 = com desconto"
     * (pos. 142), então o percentual é convertido em valor absoluto antes de gravar.
     *
     * @param BoletoContract $boleto
     *
     * @return void
     * @throws ValidationException
     */
    protected function preencheDescontoSegmentoP(BoletoContract $boleto)
    {
        $valor = $this->resolveValorDescontoAbsoluto($boleto);

        if ($valor <= 0) {
            $this->add(142, 142, '0');
            $this->add(143, 150, '00000000');
            $this->add(151, 165, Util::formatCnab('9', 0, 15, 2));

            return;
        }

        $this->add(142, 142, '1');
        $this->add(143, 150, $boleto->getDataDesconto()
            ? $boleto->getDataDesconto()->format('dmY')
            : '00000000');
        $this->add(151, 165, Util::formatCnab('9', $valor, 15, 2));
    }
}
