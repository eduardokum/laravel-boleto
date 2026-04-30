<?php

namespace Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab240\Banco;

use Eduardokum\LaravelBoleto\Util;
use Eduardokum\LaravelBoleto\Exception\ValidationException;
use Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab240\AbstractRemessa;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;
use Eduardokum\LaravelBoleto\Contracts\Cnab\Remessa as RemessaContract;

/**
 * Remessa CNAB 240 - Banrisul
 *
 * Implementação baseada no manual oficial:
 *   "Cobrança Banrisul - Leiaute Padrão Febraban CNAB 240 Posições - Versão 10.3"
 *   Atualizado em 08/04/2025.
 *
 * Códigos das constantes referenciam os campos descritos no manual:
 *   C004 - Movimento; C006 - Carteira; C015 - Espécie; C018 - Juros;
 *   C021 - Desconto; C026 - Protesto/Negativação; C028 - Baixa/Devolução;
 *   C030 - Espécie de Cobrança; C077 - Pagamento Parcial; G019 - Layout Arquivo;
 *   G030 - Layout Lote; G065 - Moeda; G103 - Tipo de Chave PIX.
 */
class Banrisul extends AbstractRemessa implements RemessaContract
{
    /**
     * Códigos de movimento (campo C004 do manual)
     */
    const OCORRENCIA_REMESSA = '01';
    const OCORRENCIA_PEDIDO_BAIXA = '02';
    const OCORRENCIA_CONCESSAO_ABATIMENTO = '04';
    const OCORRENCIA_CANC_ABATIMENTO = '05';
    const OCORRENCIA_ALT_VENCIMENTO = '06';
    const OCORRENCIA_CONCESSAO_DESCONTO = '07';
    const OCORRENCIA_CANC_DESCONTO = '08';
    const OCORRENCIA_PROTESTAR = '09';
    const OCORRENCIA_SUSTAR_PROTESTO_BAIXAR = '10';
    const OCORRENCIA_SUSTAR_PROTESTO_MANTER = '11';
    const OCORRENCIA_ALT_JUROS = '12';
    const OCORRENCIA_DISPENSA_JUROS = '13';
    const OCORRENCIA_ALT_MULTA = '14';
    const OCORRENCIA_DISPENSA_MULTA = '15';
    const OCORRENCIA_ALT_DESCONTO = '16';
    const OCORRENCIA_NAO_CONCEDER_DESCONTO = '17';
    const OCORRENCIA_ALT_ABATIMENTO = '18';
    const OCORRENCIA_ALT_NUMERO_CONTROLE = '22';
    const OCORRENCIA_ALT_PAGADOR = '23';
    const OCORRENCIA_ALT_SACADOR = '24';
    const OCORRENCIA_ALT_OUTROS_DADOS = '31';
    const OCORRENCIA_TRANS_CARTEIRA = '43';
    const OCORRENCIA_NEGATIVAR = '45';
    const OCORRENCIA_RETIRAR_NEGATIVACAO = '46';
    const OCORRENCIA_ALT_VLR_MIN_PERC = '48';
    const OCORRENCIA_ALT_VLR_MAX_PERC = '49';

    /**
     * Códigos de protesto / negativação (campo C026 do manual)
     */
    const PROTESTO_DIAS_CORRIDOS = '1';
    const PROTESTO_NAO_PROTESTAR = '3';
    const PROTESTO_NAO_NEGATIVAR = '7';
    const PROTESTO_NEGATIVAR_DIAS_CORRIDOS = '8';

    /**
     * Códigos de baixa / devolução (campo C028 do manual)
     */
    const BAIXA_DEVOLVER = '1';
    const BAIXA_CANCELAR_PRAZO = '3';

    /**
     * Códigos de juros de mora (campo C018 do manual)
     */
    const JUROS_VALOR_DIA = '1';
    const JUROS_TAXA_MENSAL = '2';
    const JUROS_ISENTO = '3';

    /**
     * Códigos de desconto (campo C021 do manual)
     */
    const DESCONTO_VALOR_FIXO = '1';
    const DESCONTO_PERCENTUAL = '2';
    const DESCONTO_VALOR_ANTECIPACAO_DIA_CORRIDO = '3';
    const DESCONTO_PERCENTUAL_DIA_CORRIDO = '5';
    const DESCONTO_CANCELAMENTO = '7';

    /**
     * Códigos de multa (campo G073 do manual)
     */
    const MULTA_VALOR_FIXO = '1';
    const MULTA_PERCENTUAL = '2';

    /**
     * Códigos de espécie de cobrança (campo C030 do manual)
     */
    const ESPECIE_COBRANCA_SIMPLES = '0000805076';
    const ESPECIE_COBRANCA_SEGURADORAS = '0000805157';
    const ESPECIE_COBRANCA_FINANCEIRAS = '0000805238';
    const ESPECIE_COBRANCA_PARTILHADA = '0000815470';
    const ESPECIE_COBRANCA_SIMPLES_DOLAR = '0000825468';
    const ESPECIE_COBRANCA_DESCONTO = '0000603015';

    /**
     * Identificações de Tipo de Pagamento (campo C078 do manual - segmento Y-53)
     */
    const TP_PAGTO_QUALQUER_VALOR = '01';
    const TP_PAGTO_ENTRE_MIN_MAX = '02';
    const TP_PAGTO_NAO_DIVERGENTE = '03';

    /**
     * Pagamento parcial (campo C077 do manual)
     */
    const PAGAMENTO_PARCIAL_NAO = '1';
    const PAGAMENTO_PARCIAL_SIM = '2';

    /**
     * Código do banco
     *
     * @var string
     */
    protected $codigoBanco = BoletoContract::COD_BANCO_BANRISUL;

    /**
     * Carteiras suportadas pelo CNAB 240 do Banrisul (campo C006 do manual).
     *
     * 1 -> Cobrança Simples
     * 2 -> Cobrança Vinculada (apenas clientes previamente autorizados)
     * 3 -> Cobrança Caucionada (apenas clientes previamente autorizados)
     * 4 -> Cobrança Descontada
     *
     * Carteira 5 (Vendor) não é tratada pelo Banrisul.
     *
     * @var array
     */
    protected $carteiras = ['1', '2', '3', '4'];

    /**
     * Tipos de desconto suportados pelo Banrisul CNAB 240 (campo C021).
     *
     * O manual não diferencia "dia útil" de "dia corrido" para os tipos
     * fixo/antecipação e percentual sobre valor: as variantes canônicas
     * "dia útil" (4 e 6) são mapeadas para os equivalentes "dia corrido"
     * (3 e 5) já tratados pelo banco.
     *
     * @var array<string, string>
     */
    protected $tiposDescontoSuportados = [
        BoletoContract::TIPO_DESCONTO_VALOR_FIXO                      => self::DESCONTO_VALOR_FIXO,
        BoletoContract::TIPO_DESCONTO_PERCENTUAL                      => self::DESCONTO_PERCENTUAL,
        BoletoContract::TIPO_DESCONTO_VALOR_ANTECIPACAO_DIA_CORRIDO   => self::DESCONTO_VALOR_ANTECIPACAO_DIA_CORRIDO,
        BoletoContract::TIPO_DESCONTO_VALOR_ANTECIPACAO_DIA_UTIL      => self::DESCONTO_VALOR_ANTECIPACAO_DIA_CORRIDO,
        BoletoContract::TIPO_DESCONTO_PERCENTUAL_DIA_CORRIDO          => self::DESCONTO_PERCENTUAL_DIA_CORRIDO,
        BoletoContract::TIPO_DESCONTO_PERCENTUAL_DIA_UTIL             => self::DESCONTO_PERCENTUAL_DIA_CORRIDO,
        BoletoContract::TIPO_DESCONTO_CANCELAMENTO                    => self::DESCONTO_CANCELAMENTO,
    ];

    /**
     * Código do beneficiário no Banrisul (13 dígitos, fornecido pelo banco).
     *
     * @var string
     */
    protected $codigoCliente;

    /**
     * Espécie de cobrança (campo C030 do manual). Default = Cobrança Simples.
     *
     * @var string
     */
    protected $codigoEspecieCobranca = self::ESPECIE_COBRANCA_SIMPLES;

    public function __construct(array $params = [])
    {
        parent::__construct($params);
        $this->addCampoObrigatorio('codigoCliente');
    }

    /**
     * @return string|null
     */
    public function getCodigoCliente()
    {
        return $this->codigoCliente;
    }

    /**
     * @param string $codigoCliente
     *
     * @return $this
     */
    public function setCodigoCliente($codigoCliente)
    {
        $this->codigoCliente = $codigoCliente;

        return $this;
    }

    /**
     * @return string
     */
    public function getCodigoEspecieCobranca()
    {
        // Quando a carteira for desconto, força código próprio (manual C030).
        if ((string) $this->getCarteira() === '4') {
            return self::ESPECIE_COBRANCA_DESCONTO;
        }

        return $this->codigoEspecieCobranca;
    }

    /**
     * @param string $codigoEspecieCobranca
     *
     * @return $this
     */
    public function setCodigoEspecieCobranca($codigoEspecieCobranca)
    {
        $this->codigoEspecieCobranca = Util::formatCnab('9', Util::onlyNumbers($codigoEspecieCobranca), 10);

        return $this;
    }

    /**
     * @param BoletoContract $boleto
     *
     * @return $this
     * @throws ValidationException
     */
    public function addBoleto(BoletoContract $boleto)
    {
        $this->boletos[] = $boleto;

        $this->segmentoP($boleto);
        $this->segmentoQ($boleto);

        if ($this->precisaSegmentoR($boleto)) {
            $this->segmentoR($boleto);
        }

        if ($boleto->getSacadorAvalista()) {
            $this->segmentoY01($boleto);
        }

        return $this;
    }

    /**
     * O segmento R é necessário quando há multa ou mensagens 3/4 a serem
     * impressas no boleto. Para a carteira de desconto (4), o manual proíbe.
     *
     * @param BoletoContract $boleto
     * @return bool
     */
    protected function precisaSegmentoR(BoletoContract $boleto)
    {
        if ((string) $this->getCarteira() === '4') {
            return false;
        }

        return $boleto->getMulta() > 0;
    }

    /**
     * Resolve o código de movimento (16-17) baseado no status do boleto.
     *
     * @param BoletoContract $boleto
     * @return string
     */
    protected function getCodigoMovimento(BoletoContract $boleto)
    {
        switch ($boleto->getStatus()) {
            case BoletoContract::STATUS_BAIXA:
                return self::OCORRENCIA_PEDIDO_BAIXA;
            case BoletoContract::STATUS_ALTERACAO:
                return self::OCORRENCIA_ALT_OUTROS_DADOS;
            case BoletoContract::STATUS_ALTERACAO_DATA:
                return self::OCORRENCIA_ALT_VENCIMENTO;
            case BoletoContract::STATUS_CUSTOM:
                return sprintf('%02s', $boleto->getComando());
            default:
                return self::OCORRENCIA_REMESSA;
        }
    }

    /**
     * Tipo de inscrição: 1 = CPF, 2 = CNPJ (campo G005 do manual).
     *
     * @param string|null $documento
     * @return int
     */
    protected function getTipoInscricao($documento)
    {
        return strlen(Util::onlyNumbers($documento)) == 14 ? 2 : 1;
    }

    /**
     * @param BoletoContract $boleto
     *
     * @return $this
     * @throws ValidationException
     */
    protected function segmentoP(BoletoContract $boleto)
    {
        $this->iniciaDetalhe();
        $movimento = $this->getCodigoMovimento($boleto);

        // Controle (1-15)
        $this->add(1, 3, Util::onlyNumbers($this->getCodigoBanco()));
        $this->add(4, 7, '0001');
        $this->add(8, 8, '3');
        $this->add(9, 13, Util::formatCnab('9', $this->iRegistrosLote, 5));
        $this->add(14, 14, 'P');
        $this->add(15, 15, '');
        // Código de movimento (16-17)
        $this->add(16, 17, $movimento);

        // Conta corrente (18-37) - Manual: campo não considerado (Brancos*)
        $this->add(18, 22, '');
        $this->add(23, 23, '');
        $this->add(24, 35, '');
        $this->add(36, 36, '');
        $this->add(37, 37, '');

        // Nosso Número (38-57) - Manual: usar 38-47 e deixar 48-57 em branco
        $this->add(38, 47, Util::formatCnab('9', $boleto->getNossoNumero(), 10));
        $this->add(48, 57, '');

        // Características da cobrança (58-62)
        $this->add(58, 58, $this->getCarteira());
        $this->add(59, 59, '1'); // C007 = Com Cadastramento
        $this->add(60, 60, '1'); // C008 = Tradicional
        $this->add(61, 61, $this->getEmissaoBoleto()); // C009 = Quem emite
        $this->add(62, 62, $this->getDistribuicaoBoleto()); // C010 = Quem distribui

        // Número do documento (63-77) - Banrisul usa 13 chars + 2 brancos
        $this->add(63, 75, Util::formatCnab('X', $boleto->getNumeroDocumento(), 13));
        $this->add(76, 77, '');

        $this->add(78, 85, $boleto->getDataVencimento()->format('dmY'));
        $this->add(86, 100, Util::formatCnab('9', $boleto->getValor(), 15, 2));

        // Agência cobradora (101-106) - Brancos*/zeros
        $this->add(101, 105, '00000');
        $this->add(106, 106, '');

        $this->add(107, 108, Util::formatCnab('9', $boleto->getEspecieDocCodigo(), 2));
        $this->add(109, 109, Util::formatCnab('A', $boleto->getAceite(), 1));
        $this->add(110, 117, $boleto->getDataDocumento()->format('dmY'));

        // Juros de mora (118-141) - C018, C019, C020
        $this->preencheJuros($boleto);

        // Desconto 1 (142-165) - C021, C022, C023
        $this->preencheDesconto($boleto);

        // IOF e abatimento (166-195)
        $this->add(166, 180, Util::formatCnab('9', 0, 15, 2));
        $this->add(181, 195, Util::formatCnab('9', 0, 15, 2));

        // Identificação do título na empresa (196-220)
        $this->add(196, 220, Util::formatCnab('X', $boleto->getNumeroControle(), 25));

        // Protesto / Negativação (221-223) - C026/C027
        $this->preencheProtesto($boleto);

        // Baixa / Devolução (224-227) - C028/C029
        $this->preencheBaixa($boleto);

        // Código da moeda (228-229) - G065. Default 09 = Real.
        $this->add(228, 229, Util::formatCnab('9', $boleto->getMoeda(), 2));

        // Código de espécie de cobrança (230-239) - C030
        $this->add(230, 239, $this->getCodigoEspecieCobranca());

        // Autorização de pagamento parcial (240) - C077
        $this->add(240, 240, self::PAGAMENTO_PARCIAL_NAO);

        return $this;
    }

    /**
     * Identificação da emissão do boleto (campo C009 do manual).
     * Carteira de desconto: obrigatório '1' (Banco emite).
     *
     * @return string
     */
    protected function getEmissaoBoleto()
    {
        return (string) $this->getCarteira() === '4' ? '1' : '2';
    }

    /**
     * Identificação da distribuição do boleto (campo C010 do manual).
     * Carteira de desconto: obrigatório '1' (Banco distribui).
     *
     * @return string
     */
    protected function getDistribuicaoBoleto()
    {
        return (string) $this->getCarteira() === '4' ? '1' : '2';
    }

    /**
     * Preenche juros conforme regras do manual (campos C018, C019, C020).
     *
     * @param BoletoContract $boleto
     * @throws ValidationException
     */
    protected function preencheJuros(BoletoContract $boleto)
    {
        // Carteira de desconto: obrigatoriamente isento (manual C018)
        if ((string) $this->getCarteira() === '4' || $boleto->getJuros() <= 0) {
            $this->add(118, 118, self::JUROS_ISENTO);
            $this->add(119, 126, '00000000');
            $this->add(127, 141, Util::formatCnab('9', 0, 15, 2));

            return;
        }

        // Data de juros: deve ser maior que a data de vencimento (manual C019).
        // Se ausente, banco assume dia imediatamente após o vencimento.
        $diasApos = max(1, (int) $boleto->getJurosApos());
        $dataJuros = $boleto->getDataVencimento()->copy()->addDays($diasApos);

        $this->add(118, 118, self::JUROS_TAXA_MENSAL);
        $this->add(119, 126, $dataJuros->format('dmY'));
        // Percentual mensal não pode ser maior que 99,99% (manual C020).
        $juros = min((float) $boleto->getJuros(), 99.99);
        $this->add(127, 141, Util::formatCnab('9', $juros, 15, 2));
    }

    /**
     * Preenche desconto 1 conforme regras do manual (campos C021, C022, C023).
     *
     * Para a carteira 4 (Cobrança Descontada) o manual obriga zerar todos os
     * campos de desconto, independentemente do que foi configurado no boleto.
     * Nas demais carteiras delega à implementação padrão CNAB 240, que
     * aplica o mapa $tiposDescontoSuportados.
     *
     * @param BoletoContract $boleto
     * @throws ValidationException
     */
    protected function preencheDesconto(BoletoContract $boleto)
    {
        if ((string) $this->getCarteira() === '4') {
            $this->add(142, 142, '0');
            $this->add(143, 150, '00000000');
            $this->add(151, 165, Util::formatCnab('9', 0, 15, 2));

            return;
        }

        $this->preencheDescontoSegmentoP($boleto);
    }

    /**
     * Preenche protesto/negativação (campos C026/C027 do manual).
     *
     * @param BoletoContract $boleto
     * @throws ValidationException
     */
    protected function preencheProtesto(BoletoContract $boleto)
    {
        // Carteira de desconto: campos zerados (manual C026/C027).
        if ((string) $this->getCarteira() === '4') {
            $this->add(221, 221, '0');
            $this->add(222, 223, '00');

            return;
        }

        $dias = (int) $boleto->getDiasProtesto();
        // Manual: prazo válido entre 3 e 99 dias.
        if ($dias > 0) {
            $dias = max(3, min(99, $dias));
            $this->add(221, 221, self::PROTESTO_DIAS_CORRIDOS);
            $this->add(222, 223, Util::formatCnab('9', $dias, 2));

            return;
        }

        $this->add(221, 221, self::PROTESTO_NAO_PROTESTAR);
        $this->add(222, 223, '00');
    }

    /**
     * Preenche baixa/devolução (campos C028/C029 do manual).
     *
     * @param BoletoContract $boleto
     * @throws ValidationException
     */
    protected function preencheBaixa(BoletoContract $boleto)
    {
        // Carteira de desconto: campos zerados.
        if ((string) $this->getCarteira() === '4') {
            $this->add(224, 224, '0');
            $this->add(225, 227, '000');

            return;
        }

        $dias = (int) $boleto->getDiasBaixaAutomatica();
        if ($dias > 0) {
            $this->add(224, 224, self::BAIXA_DEVOLVER);
            $this->add(225, 227, Util::formatCnab('9', min(99, $dias), 3));

            return;
        }

        $this->add(224, 224, '0');
        $this->add(225, 227, '000');
    }

    /**
     * Manual seção 2.3, item 4: em campos alfanuméricos não utilizar
     * acentuação gráfica, pontuação, parênteses, tabulações ou caracteres
     * especiais — podem rejeitar a remessa. Substitui por espaço para
     * preservar separação entre palavras (ex.: "AV.DESEMBARGADOR" → "AV DESEMBARGADOR").
     *
     * @param string|null $value
     *
     * @return string
     */
    protected function sanitizeAlfa($value)
    {
        return preg_replace('/[^A-Za-z0-9 \-]/', ' ', (string) $value);
    }

    /**
     * @param BoletoContract $boleto
     *
     * @return $this
     * @throws ValidationException
     */
    public function segmentoQ(BoletoContract $boleto)
    {
        $this->iniciaDetalhe();
        $movimento = $this->getCodigoMovimento($boleto);
        $pagador = $boleto->getPagador();

        $this->add(1, 3, Util::onlyNumbers($this->getCodigoBanco()));
        $this->add(4, 7, '0001');
        $this->add(8, 8, '3');
        $this->add(9, 13, Util::formatCnab('9', $this->iRegistrosLote, 5));
        $this->add(14, 14, 'Q');
        $this->add(15, 15, '');
        $this->add(16, 17, $movimento);

        // Dados do pagador (18-153)
        $this->add(18, 18, $this->getTipoInscricao($pagador->getDocumento()));
        $this->add(19, 33, Util::formatCnab('9', Util::onlyNumbers($pagador->getDocumento()), 15));
        $this->add(34, 73, Util::formatCnab('X', $this->sanitizeAlfa($pagador->getNome()), 40));
        $this->add(74, 113, Util::formatCnab('X', $this->sanitizeAlfa($pagador->getEndereco()), 40));
        $this->add(114, 128, Util::formatCnab('X', $this->sanitizeAlfa($pagador->getBairro()), 15));
        $this->add(129, 133, Util::formatCnab('9', Util::onlyNumbers($pagador->getCep()), 5));
        $this->add(134, 136, Util::formatCnab('9', Util::onlyNumbers(substr($pagador->getCep(), 6, 9)), 3));
        $this->add(137, 151, Util::formatCnab('X', $this->sanitizeAlfa($pagador->getCidade()), 15));
        $this->add(152, 153, Util::formatCnab('X', $pagador->getUf(), 2));

        // Sacador / Avalista (154-209) - Banrisul: NÃO informar aqui.
        // Manual (3.4): "Quando o beneficiário não for o original do título,
        // informar dados do sacador no segmento Y-01.
        // Se informados no segmento Q, título será rejeitado."
        $this->add(154, 154, '0');
        $this->add(155, 169, '000000000000000');
        $this->add(170, 209, '');

        // Banco correspondente (210-232) - Brancos*
        $this->add(210, 212, '000');
        $this->add(213, 232, '');

        // CNAB final (233-240) - Brancos*
        $this->add(233, 240, '');

        return $this;
    }

    /**
     * Segmento R - Multa e mensagens 3/4. Não permitido para carteira de
     * desconto (manual seção 3.6).
     *
     * @param BoletoContract $boleto
     *
     * @return $this
     * @throws ValidationException
     */
    public function segmentoR(BoletoContract $boleto)
    {
        $this->iniciaDetalhe();
        $movimento = $this->getCodigoMovimento($boleto);

        $this->add(1, 3, Util::onlyNumbers($this->getCodigoBanco()));
        $this->add(4, 7, '0001');
        $this->add(8, 8, '3');
        $this->add(9, 13, Util::formatCnab('9', $this->iRegistrosLote, 5));
        $this->add(14, 14, 'R');
        $this->add(15, 15, '');
        $this->add(16, 17, $movimento);

        // Desconto 2 (18-41) - Não utilizado
        $this->add(18, 18, '0');
        $this->add(19, 26, '00000000');
        $this->add(27, 41, Util::formatCnab('9', 0, 15, 2));

        // Desconto 3 (42-65) - Manual: campos não validados pelo Banrisul
        $this->add(42, 42, '0');
        $this->add(43, 50, '00000000');
        $this->add(51, 65, Util::formatCnab('9', 0, 15, 2));

        // Multa (66-89) - G073/G074/G075
        if ($boleto->getMulta() > 0) {
            $dataMulta = $boleto->getDataVencimento()
                ->copy()
                ->addDays(max(1, (int) $boleto->getMultaApos()));
            $this->add(66, 66, self::MULTA_PERCENTUAL);
            $this->add(67, 74, $dataMulta->format('dmY'));
            $this->add(75, 89, Util::formatCnab('9', $boleto->getMulta(), 15, 2));
        } else {
            $this->add(66, 66, '0');
            $this->add(67, 74, '00000000');
            $this->add(75, 89, Util::formatCnab('9', 0, 15, 2));
        }

        // Informação ao pagador (90-99) - Brancos*
        $this->add(90, 99, '');

        // Mensagens 3 e 4 (100-179) - até 2 instruções livres
        $instrucoes = $boleto->getInstrucoes() ?: [];
        $this->add(100, 139, Util::formatCnab('X', $instrucoes[0] ?? '', 40));
        $this->add(140, 179, Util::formatCnab('X', $instrucoes[1] ?? '', 40));

        // CNAB (180-199) - Brancos*
        $this->add(180, 199, '');

        // Cód ocorrência do pagador (200-207) - não considerado em remessa
        $this->add(200, 207, '00000000');

        // Dados de débito automático (208-230) - Não considerado
        $this->add(208, 210, '000');
        $this->add(211, 215, '00000');
        $this->add(216, 216, '');
        $this->add(217, 228, Util::formatCnab('9', 0, 12));
        $this->add(229, 229, '');
        $this->add(230, 230, '');

        // Aviso para débito automático (231) - não considerado
        $this->add(231, 231, '0');

        // CNAB final (232-240)
        $this->add(232, 240, '');

        return $this;
    }

    /**
     * Segmento Y-01 - Sacador/Avalista (manual seção 3.7).
     *
     * @param BoletoContract $boleto
     *
     * @return $this
     * @throws ValidationException
     */
    public function segmentoY01(BoletoContract $boleto)
    {
        $this->iniciaDetalhe();
        $movimento = $this->getCodigoMovimento($boleto);
        $sacador = $boleto->getSacadorAvalista();

        $this->add(1, 3, Util::onlyNumbers($this->getCodigoBanco()));
        $this->add(4, 7, '0001');
        $this->add(8, 8, '3');
        $this->add(9, 13, Util::formatCnab('9', $this->iRegistrosLote, 5));
        $this->add(14, 14, 'Y');
        $this->add(15, 15, '');
        $this->add(16, 17, $movimento);

        // Identificação do registro opcional (18-19) - 01 = Sacador/Avalista
        $this->add(18, 19, '01');

        $this->add(20, 20, $this->getTipoInscricao($sacador->getDocumento()));
        $this->add(21, 35, Util::formatCnab('9', Util::onlyNumbers($sacador->getDocumento()), 15));
        $this->add(36, 75, Util::formatCnab('X', $this->sanitizeAlfa($sacador->getNome()), 40));
        $this->add(76, 115, Util::formatCnab('X', $this->sanitizeAlfa($sacador->getEndereco()), 40));
        $this->add(116, 130, Util::formatCnab('X', $this->sanitizeAlfa($sacador->getBairro()), 15));

        // Manual (3.7): CEP (5 num) + Sufixo CEP (3 num) - separados.
        $cep = Util::onlyNumbers($sacador->getCep());
        $this->add(131, 135, Util::formatCnab('9', substr($cep, 0, 5), 5));
        $this->add(136, 138, Util::formatCnab('9', substr($cep, 5, 3), 3));

        $this->add(139, 153, Util::formatCnab('X', $this->sanitizeAlfa($sacador->getCidade()), 15));
        $this->add(154, 155, Util::formatCnab('X', $sacador->getUf(), 2));

        // CNAB final (156-240) - Brancos
        $this->add(156, 240, '');

        return $this;
    }

    /**
     * @return $this
     * @throws ValidationException
     */
    protected function header()
    {
        $this->iniciaHeader();

        $this->add(1, 3, Util::onlyNumbers($this->getCodigoBanco()));
        $this->add(4, 7, '0000');
        $this->add(8, 8, '0');
        // CNAB (9-17) - Brancos*
        $this->add(9, 17, '');

        // Empresa (18-72)
        $this->add(18, 18, $this->getTipoInscricao($this->getBeneficiario()->getDocumento()));
        $this->add(19, 32, Util::formatCnab('9', Util::onlyNumbers($this->getBeneficiario()->getDocumento()), 14));
        // Manual G007: informar à esquerda e deixar espaços em branco à direita.
        $this->add(33, 52, Util::formatCnab('X', $this->getCodigoCliente(), 20));
        // Conta corrente (53-72) - Banrisul: campo "Brancos*" (não considerado),
        // mas a regra geral 2.3 item 1 do manual exige zeros para Num não utilizado;
        // validadores estritos rejeitam Num preenchido com espaços.
        $this->add(53, 57, Util::formatCnab('9', 0, 5));
        $this->add(58, 58, ''); // DV agência (Alfa)
        $this->add(59, 70, Util::formatCnab('9', 0, 12));
        $this->add(71, 71, ''); // DV conta (Alfa)
        $this->add(72, 72, ''); // DV ag/conta (Alfa)

        // Nome do beneficiário (73-102) e nome do banco (103-132)
        $this->add(73, 102, Util::formatCnab('X', $this->getBeneficiario()->getNome(), 30));
        $this->add(103, 132, Util::formatCnab('X', 'BANRISUL', 30));

        // CNAB (133-142) - Brancos*
        $this->add(133, 142, '');

        // Arquivo (143-171)
        $this->add(143, 143, '1'); // 1 = Remessa (G015)
        $this->add(144, 151, $this->getDataRemessa('dmY'));
        $this->add(152, 157, date('His'));
        $this->add(158, 163, Util::formatCnab('9', $this->getIdremessa(), 6));
        $this->add(164, 166, '103'); // Layout do arquivo (G019) - v10.3
        $this->add(167, 171, '00000'); // Densidade (G020) - zeros/brancos

        // Reservados e CNAB final (172-240) - Brancos*
        $this->add(172, 191, '');
        $this->add(192, 211, '');
        $this->add(212, 240, '');

        return $this;
    }

    /**
     * @return $this
     * @throws ValidationException
     */
    protected function headerLote()
    {
        $this->iniciaHeaderLote();

        $this->add(1, 3, Util::onlyNumbers($this->getCodigoBanco()));
        $this->add(4, 7, '0001');
        $this->add(8, 8, '1');

        // Serviço (9-16)
        $this->add(9, 9, 'R'); // G028 = Remessa
        $this->add(10, 11, '01'); // G025 = Cobrança
        $this->add(12, 13, ''); // CNAB
        $this->add(14, 16, '060'); // Layout do lote (G030) - v10.3
        $this->add(17, 17, '');

        // Empresa (18-73)
        $this->add(18, 18, $this->getTipoInscricao($this->getBeneficiario()->getDocumento()));
        $this->add(19, 33, Util::formatCnab('9', Util::onlyNumbers($this->getBeneficiario()->getDocumento()), 15));
        // Manual G007: informar à esquerda e deixar espaços em branco à direita.
        $this->add(34, 53, Util::formatCnab('X', $this->getCodigoCliente(), 20));
        // Conta corrente (54-73) - Banrisul: campo "Brancos*" (não considerado),
        // mas Num não utilizado exige zeros pela regra geral 2.3 item 1 do manual.
        $this->add(54, 58, Util::formatCnab('9', 0, 5));
        $this->add(59, 59, ''); // DV agência (Alfa)
        $this->add(60, 71, Util::formatCnab('9', 0, 12));
        $this->add(72, 72, ''); // DV conta (Alfa)
        $this->add(73, 73, ''); // DV ag/conta (Alfa)

        $this->add(74, 103, Util::formatCnab('X', $this->getBeneficiario()->getNome(), 30));

        // Mensagens 1 e 2 (104-183) - opcionais
        $this->add(104, 143, '');
        $this->add(144, 183, '');

        // Controle cobrança (184-207)
        $this->add(184, 191, Util::formatCnab('9', $this->getIdremessa(), 8));
        $this->add(192, 199, $this->getDataRemessa('dmY'));
        // Data do crédito (200-207) - Manual: Brancos* na remessa.
        $this->add(200, 207, '');

        // CNAB final (208-240) - Brancos*
        $this->add(208, 240, '');

        return $this;
    }

    /**
     * @return $this
     * @throws ValidationException
     */
    protected function trailerLote()
    {
        $this->iniciaTrailerLote();

        $totais = $this->totalizaPorCarteira();

        $this->add(1, 3, Util::onlyNumbers($this->getCodigoBanco()));
        $this->add(4, 7, '0001');
        $this->add(8, 8, '5');
        $this->add(9, 17, '');

        // Quantidade total de registros do lote (header + detalhes + trailer)
        $this->add(18, 23, Util::formatCnab('9', $this->getCountDetalhes() + 2, 6));

        // Cobrança Simples (24-46)
        $this->add(24, 29, Util::formatCnab('9', $totais['simples']['qtd'], 6));
        $this->add(30, 46, Util::formatCnab('9', $totais['simples']['valor'], 17, 2));

        // Cobrança Vinculada (47-69)
        $this->add(47, 52, Util::formatCnab('9', $totais['vinculada']['qtd'], 6));
        $this->add(53, 69, Util::formatCnab('9', $totais['vinculada']['valor'], 17, 2));

        // Cobrança Caucionada (70-92)
        $this->add(70, 75, Util::formatCnab('9', $totais['caucionada']['qtd'], 6));
        $this->add(76, 92, Util::formatCnab('9', $totais['caucionada']['valor'], 17, 2));

        // Cobrança Descontada (93-115)
        $this->add(93, 98, Util::formatCnab('9', $totais['descontada']['qtd'], 6));
        $this->add(99, 115, Util::formatCnab('9', $totais['descontada']['valor'], 17, 2));

        // Número do aviso de lançamento (116-123) - não considerado
        $this->add(116, 123, '');

        // CNAB final (124-240) - Brancos*
        $this->add(124, 240, '');

        return $this;
    }

    /**
     * Totaliza títulos por carteira (cobrança simples / vinculada / caucionada
     * / descontada) para preencher o trailer do lote conforme manual (3.14).
     *
     * @return array
     */
    protected function totalizaPorCarteira()
    {
        $mapa = [
            '1' => 'simples',
            '2' => 'vinculada',
            '3' => 'caucionada',
            '4' => 'descontada',
        ];

        $tipo = $mapa[(string) $this->getCarteira()] ?? 'simples';

        $totais = [
            'simples'    => ['qtd' => 0, 'valor' => 0],
            'vinculada'  => ['qtd' => 0, 'valor' => 0],
            'caucionada' => ['qtd' => 0, 'valor' => 0],
            'descontada' => ['qtd' => 0, 'valor' => 0],
        ];

        foreach ($this->boletos as $boleto) {
            $totais[$tipo]['qtd']++;
            $totais[$tipo]['valor'] += $boleto->getValor();
        }

        return $totais;
    }

    /**
     * @return $this
     * @throws ValidationException
     */
    protected function trailer()
    {
        $this->iniciaTrailer();

        $this->add(1, 3, Util::onlyNumbers($this->getCodigoBanco()));
        $this->add(4, 7, '9999');
        $this->add(8, 8, '9');
        $this->add(9, 17, '');
        // Quantidade de lotes do arquivo (G049)
        $this->add(18, 23, Util::formatCnab('9', 1, 6));
        // Quantidade de registros do arquivo (G056)
        $this->add(24, 29, Util::formatCnab('9', $this->getCount(), 6));
        // Quantidade de contas para conciliação (G037) - exclusivo conciliação
        $this->add(30, 35, Util::formatCnab('9', 0, 6));
        // CNAB final (36-240) - Brancos*
        $this->add(36, 240, '');

        return $this;
    }
}
