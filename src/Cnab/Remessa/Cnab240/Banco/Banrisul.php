<?php

namespace Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab240\Banco;

use Eduardokum\LaravelBoleto\Util;
use Eduardokum\LaravelBoleto\Exception\ValidationException;
use Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab240\AbstractRemessa;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;
use Eduardokum\LaravelBoleto\Contracts\Cnab\Remessa as RemessaContract;

/**
 * Remessa CNAB 240 - Banrisul (Cobrança)
 *
 * Implementação fiel ao manual oficial de cobrança:
 *   "Cobrança Banrisul - Leiaute CNAB 240 Posições Padrão Febraban"
 *   (arquivo manuais/BANRISUL/240_posicoes.pdf)
 *   Layout de arquivo 040, layout de lote 020.
 *
 * IMPORTANTE: este manual é diferente do "v10.3 / 08-04-2025" (que possui PIX,
 * segmento Y-53, espécie de cobrança de 10 dígitos, negativação, pagamento
 * parcial). Nada disso existe neste leiaute e portanto não é tratado aqui.
 *
 * Referências de campo (coluna "Nº/REG" do manual):
 *   07.3P Código de movimento; 14.3P Carteira; 24.3P Espécie do título;
 *   27/28/29.3P Juros; 30/31/32.3P Desconto 1; 37/38.3P Protesto;
 *   39.3P Baixa/Devolução; 40.3P Moeda; 14/15/16.3R Multa.
 */
class Banrisul extends AbstractRemessa implements RemessaContract
{
    /**
     * Códigos de movimento (campo 07.3P do manual).
     */
    const OCORRENCIA_REMESSA = '01';                 // Entrada de títulos
    const OCORRENCIA_PEDIDO_BAIXA = '02';            // Pedido de baixa
    const OCORRENCIA_CONCESSAO_ABATIMENTO = '04';    // Concessão de abatimento
    const OCORRENCIA_CANC_ABATIMENTO = '05';         // Cancelamento de abatimento
    const OCORRENCIA_ALT_VENCIMENTO = '06';          // Alteração de vencimento
    const OCORRENCIA_PROTESTAR = '09';               // Protestar imediatamente
    const OCORRENCIA_SUSTAR_PROTESTO = '10';         // Sustação da instrução de protesto
    const OCORRENCIA_REEMBOLSO_TRANSFERENCIA = '12'; // Reembolso/transf. p/ cobrança simples (Desconto/Vendor)
    const OCORRENCIA_REEMBOLSO_DEVOLUCAO = '13';     // Reembolso/devolução (Desconto/Vendor)
    const OCORRENCIA_PROTESTO_FALENCIA = '15';       // Protesto imediato por motivo de falência
    const OCORRENCIA_ALT_OUTROS_DADOS = '31';        // Alteração de outros dados

    /**
     * Códigos de juros de mora (campo 27.3P do manual).
     * Obs.: o código 3 é "Reservado" neste leiaute; para "sem juros" usamos 0.
     */
    const JUROS_VALOR_DIA = '1';
    const JUROS_TAXA_MENSAL = '2';

    /**
     * Códigos de desconto 1 (campo 30.3P do manual). Neste leiaute SOMENTE os
     * códigos 1 e 3 são válidos: os percentuais (2 e 5) e as variantes "dia
     * útil" (4 e 6) foram excluídos do campo 30.3P (atualização Jan/2019).
     * Descontos percentuais são convertidos para valor fixo em R$.
     */
    const DESCONTO_VALOR_FIXO = '1';                    // Valor fixo até a data informada
    const DESCONTO_VALOR_ANTECIPACAO_DIA_CORRIDO = '3'; // Valor por antecipação dia corrido

    /**
     * Códigos de protesto (campo 37.3P do manual).
     */
    const PROTESTO_DIAS_CORRIDOS = '1';
    const PROTESTO_NAO_PROTESTAR = '3';

    /**
     * Código de baixa/devolução (campo 39.3P do manual).
     */
    const BAIXA_DEVOLVER = '1';

    /**
     * Códigos de multa (campo 14.3R do manual).
     */
    const MULTA_VALOR_FIXO = '1';
    const MULTA_PERCENTUAL_MES = '2';
    const MULTA_PERCENTUAL = '3';

    /**
     * Código do banco.
     *
     * @var string
     */
    protected $codigoBanco = BoletoContract::COD_BANCO_BANRISUL;

    /**
     * Carteiras suportadas (campo 14.3P do manual). Não existe carteira "4".
     *
     * @var array
     */
    protected $carteiras = ['1', '2', '3', 'B', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'N', 'P', 'R', 'S', 'T', 'U'];

    /**
     * Tipos de desconto canônicos suportados por este leiaute (campo 30.3P).
     *
     * O campo 30.3P só aceita 1 (valor fixo até a data) e 3 (valor por
     * antecipação dia corrido). Por isso:
     *  - percentual "até a data" (2) -> 1, convertido para valor fixo em R$;
     *  - percentual/valor "dia corrido/dia útil" (3,4,5,6) -> 3 (antecipação
     *    dia corrido), percentuais convertidos para valor em R$.
     *
     * @var array<string, string>
     */
    protected $tiposDescontoSuportados = [
        BoletoContract::TIPO_DESCONTO_VALOR_FIXO                    => self::DESCONTO_VALOR_FIXO,
        BoletoContract::TIPO_DESCONTO_PERCENTUAL                    => self::DESCONTO_VALOR_FIXO,
        BoletoContract::TIPO_DESCONTO_VALOR_ANTECIPACAO_DIA_CORRIDO => self::DESCONTO_VALOR_ANTECIPACAO_DIA_CORRIDO,
        BoletoContract::TIPO_DESCONTO_VALOR_ANTECIPACAO_DIA_UTIL    => self::DESCONTO_VALOR_ANTECIPACAO_DIA_CORRIDO,
        BoletoContract::TIPO_DESCONTO_PERCENTUAL_DIA_CORRIDO        => self::DESCONTO_VALOR_ANTECIPACAO_DIA_CORRIDO,
        BoletoContract::TIPO_DESCONTO_PERCENTUAL_DIA_UTIL           => self::DESCONTO_VALOR_ANTECIPACAO_DIA_CORRIDO,
    ];

    /**
     * Código do beneficiário no Banrisul (convênio - 13 dígitos, campo 07.0/11.1).
     *
     * @var string
     */
    protected $codigoCliente;

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
     * Convênio (campos 07.0 e 11.1 do manual): campo numérico de 20 posições do
     * qual o Banrisul considera apenas as 13 PRIMEIRAS posições. Para manter o
     * campo numérico válido (regra 2.3-3: numérico não utilizado = zeros),
     * gravamos os 13 dígitos do código do beneficiário seguidos de 7 zeros.
     *
     * @return string
     */
    protected function getConvenioCnab20()
    {
        $c = Util::onlyNumbers($this->getCodigoCliente());
        $c13 = str_pad(substr($c, 0, 13), 13, '0', STR_PAD_LEFT);

        return $c13 . '0000000';
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

        // Títulos de terceiros (espécie "AD"): manual (3.4/3.7) exige o nome do
        // Sacador/Avalista no segmento Q (170-209) e a inclusão do segmento Y.
        $especie = (string) $boleto->getEspecieDocCodigo(99, 240);
        if ($especie === 'AD' && ! $boleto->getSacadorAvalista()) {
            throw new ValidationException('Banrisul CNAB240: Espécie "AD" (título de terceiros) exige Sacador/Avalista e inclusão do Segmento Y, conforme manual.');
        }

        if ($boleto->getSacadorAvalista() || $especie === 'AD') {
            $this->segmentoY01($boleto);
        }

        return $this;
    }

    /**
     * Segmento R é opcional: necessário para enviar multa ou mensagens (3/4).
     *
     * @param BoletoContract $boleto
     * @return bool
     */
    protected function precisaSegmentoR(BoletoContract $boleto)
    {
        if ($boleto->getMulta() > 0) {
            return true;
        }

        $instrucoes = array_filter((array) $boleto->getInstrucoes(), function ($i) {
            return trim((string) $i) !== '';
        });

        return count($instrucoes) > 0;
    }

    /**
     * Carteiras de desconto/vendor eletrônico (R e S). Nestes casos o manual
     * determina deixar em branco os campos de juros, desconto, IOF e protesto
     * do Segmento P (a taxa da operação é informada no Segmento R, 75-89).
     *
     * @return bool
     */
    protected function isCarteiraEletronica()
    {
        return in_array((string) $this->getCarteira(), ['R', 'S'], true);
    }

    /**
     * Resolve o código de movimento (campo 07.3P) a partir do status do boleto.
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
     * Tipo de inscrição: 1 = CPF, 2 = CNPJ (campo G005 / 05.0 do manual).
     *
     * @param string|null $documento
     * @return int
     */
    protected function getTipoInscricao($documento)
    {
        return strlen(Util::onlyNumbers($documento)) == 14 ? 2 : 1;
    }

    /**
     * Normaliza o código da moeda (campo 40.3P): códigos numéricos ocupam 2
     * posições (ex.: 09 = Real). Default = 09 quando não informado.
     *
     * @param BoletoContract $boleto
     * @return string
     */
    protected function getCodigoMoeda(BoletoContract $boleto)
    {
        $moeda = strtoupper(trim((string) $boleto->getMoeda()));

        if ($moeda === '') {
            return '09';
        }

        if (ctype_digit($moeda) && strlen($moeda) === 1) {
            return '0' . $moeda;
        }

        return $moeda;
    }

    /**
     * Manual seção 2: campos alfanuméricos não devem conter acentuação,
     * pontuação ou caracteres especiais — podem rejeitar a remessa. Substitui
     * por espaço para preservar a separação entre palavras.
     *
     * @param string|null $value
     * @return string
     */
    protected function sanitizeAlfa($value)
    {
        // Translitera acentos para ASCII (José -> JOSE) ANTES de remover os demais caracteres; senão a letra acentuada viraria espaço e corromperia o nome. Util::upper resolve acentos minúsculos; normalizeChars converte para ASCII.
        $value = Util::normalizeChars(Util::upper((string) $value));

        return preg_replace('/[^A-Za-z0-9 \-]/', ' ', $value);
    }

    /**
     * @return $this
     * @throws ValidationException
     */
    protected function header()
    {
        $this->iniciaHeader();

        // Controle (1-8)
        $this->add(1, 3, Util::onlyNumbers($this->getCodigoBanco())); // 041
        $this->add(4, 7, '0000');                                     // Lote de serviço
        $this->add(8, 8, '0');                                        // Registro Header de Arquivo
        // CNAB (9-17) - BRANCOS
        $this->add(9, 17, '');

        // Empresa (18-72)
        $this->add(18, 18, $this->getTipoInscricao($this->getBeneficiario()->getDocumento()));
        $this->add(19, 32, Util::formatCnab('9', Util::onlyNumbers($this->getBeneficiario()->getDocumento()), 14));
        // Convênio (33-52): numérico, 13 primeiras posições.
        $this->add(33, 52, $this->getConvenioCnab20());
        // Conta (53-72): no HEADER DE ARQUIVO estes campos SÃO considerados pelo
        // banco (diferente do Header de Lote). Agência "0AAAA", conta numérica.
        $this->add(53, 57, Util::formatCnab('9', $this->getAgencia(), 5));
        $this->add(58, 58, '');                                       // DV agência - BRANCO
        $this->add(59, 70, Util::formatCnab('9', $this->getConta(), 12));
        $this->add(71, 71, Util::formatCnab('9', $this->getContaDv(), 1)); // DV conta - Numérico
        $this->add(72, 72, '');                                       // DV ag/conta - BRANCO

        // Nome do beneficiário (73-102) e nome do banco (103-132)
        $this->add(73, 102, Util::formatCnab('X', $this->sanitizeAlfa($this->getBeneficiario()->getNome()), 30));
        $this->add(103, 132, Util::formatCnab('X', 'BANRISUL', 30));

        // CNAB (133-142) - BRANCOS
        $this->add(133, 142, '');

        // Arquivo (143-171)
        $this->add(143, 143, '1');                       // 1 = Remessa
        $this->add(144, 151, $this->getDataRemessa('dmY'));
        $this->add(152, 157, date('His'));
        $this->add(158, 163, Util::formatCnab('9', $this->getIdremessa(), 6));
        $this->add(164, 166, '040');                     // Layout do arquivo
        $this->add(167, 171, '00000');                   // Densidade

        // Reservado Banco (172-191)
        $this->add(172, 179, '');
        $this->add(180, 181, 'BE');                      // Uso reservado do Banco - remessa
        $this->add(182, 191, '');

        // Reservado empresa (192-211) - BRANCOS
        $this->add(192, 211, '');
        // VANS / serviço (212-240) - não considerado, BRANCOS
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
        $this->add(8, 8, '1');                                        // Registro Header de Lote

        // Serviço (9-17)
        $this->add(9, 9, 'R');                                        // R = Remessa
        $this->add(10, 11, '01');                                     // Tipo de serviço
        $this->add(12, 13, '00');                                     // Forma de lançamento
        $this->add(14, 16, '020');                                    // Layout do lote
        $this->add(17, 17, '');

        // Empresa (18-73) - tipo/número de inscrição não considerados, mas
        // numéricos: preenchemos com os dados reais (válido) por consistência.
        $this->add(18, 18, $this->getTipoInscricao($this->getBeneficiario()->getDocumento()));
        $this->add(19, 33, Util::formatCnab('9', Util::onlyNumbers($this->getBeneficiario()->getDocumento()), 15));
        $this->add(34, 53, $this->getConvenioCnab20());
        // Conta (54-73): no Header de Lote são "não considerados". Numérico não
        // utilizado = zeros; DVs alfanuméricos = brancos.
        $this->add(54, 58, Util::formatCnab('9', 0, 5));
        $this->add(59, 59, '');
        $this->add(60, 71, Util::formatCnab('9', 0, 12));
        $this->add(72, 72, '');
        $this->add(73, 73, '');

        $this->add(74, 103, Util::formatCnab('X', $this->sanitizeAlfa($this->getBeneficiario()->getNome()), 30));

        // Mensagens 1 e 2 (104-183) - opcionais
        $this->add(104, 143, '');
        $this->add(144, 183, '');

        // Controle cobrança (184-207)
        $this->add(184, 191, Util::formatCnab('9', $this->getIdremessa(), 8));   // Nº remessa
        $this->add(192, 199, $this->getDataRemessa('dmY'));                       // Data de gravação
        // Data do crédito (200-207) - não considerado na remessa. Numérico = zeros.
        $this->add(200, 207, Util::formatCnab('9', 0, 8));

        // CNAB final (208-240) - BRANCOS
        $this->add(208, 240, '');

        return $this;
    }

    /**
     * Segmento P (registro 3, obrigatório) - manual 3.3.
     *
     * @param BoletoContract $boleto
     *
     * @return $this
     * @throws ValidationException
     */
    protected function segmentoP(BoletoContract $boleto)
    {
        $this->iniciaDetalhe();
        $movimento = $this->getCodigoMovimento($boleto);
        $moeda = $this->getCodigoMoeda($boleto);
        // Moeda 02 (dólar): valor com 4 casas decimais; demais com 2 (manual 21.3P).
        $decValor = $moeda === '02' ? 4 : 2;

        // Controle (1-17)
        $this->add(1, 3, Util::onlyNumbers($this->getCodigoBanco()));
        $this->add(4, 7, '0001');
        $this->add(8, 8, '3');
        $this->add(9, 13, Util::formatCnab('9', $this->iRegistrosLote, 5));
        $this->add(14, 14, 'P');
        $this->add(15, 15, '');
        $this->add(16, 17, $movimento);

        // Conta corrente (18-37) - não considerado. Numérico = zeros; DVs = brancos.
        $this->add(18, 22, Util::formatCnab('9', 0, 5));
        $this->add(23, 23, '');
        $this->add(24, 35, Util::formatCnab('9', 0, 12));
        $this->add(36, 36, '');
        $this->add(37, 37, '');

        // Nosso Número / Identificação do título no Banco (38-57).
        // Manual 13.3P: campo numérico de 20 posições, "serão consideradas as 10
        // PRIMEIRAS posições". Logo o nosso número vai em 38-47 e o restante
        // (48-57) é zero (numérico não utilizado).
        $this->add(38, 47, Util::formatCnab('9', $boleto->getNossoNumero(), 10));
        $this->add(48, 57, Util::formatCnab('9', 0, 10));

        // Características da cobrança (58-62)
        $this->add(58, 58, Util::formatCnab('X', $this->getCarteira(), 1)); // Carteira
        $this->add(59, 59, '1');                                            // Com cadastramento
        $this->add(60, 60, '');                                             // Tipo de documento - não considerado
        $this->add(61, 61, '2');                                            // Emissão: cliente emite o bloqueto
        $this->add(62, 62, '');                                             // Distribuição - não considerado

        // Seu Número (63-75, 13 posições) - alfanumérico obrigatório. Manual 19.3P: "serão consideradas as 13 primeiras posições"; deixar as posições 76-77 em branco.
        $this->add(63, 75, Util::formatCnab('X', $boleto->getNumeroDocumento(), 13));
        $this->add(76, 77, '');

        // Vencimento (78-85) e valor do título (86-100)
        $this->add(78, 85, $boleto->getDataVencimento()->format('dmY'));
        $this->add(86, 100, Util::formatCnab('9', $boleto->getValor(), 15, $decValor));

        // Agência cobradora (101-106) - não considerado
        $this->add(101, 105, Util::formatCnab('9', 0, 5));
        $this->add(106, 106, '');

        // Espécie do título (107-108) - alfanumérico (ex.: 02, 04, 07, 12, AA, AD)
        $this->add(107, 108, Util::formatCnab('X', $boleto->getEspecieDocCodigo(99, 240), 2));
        // Aceite (109)
        $this->add(109, 109, Util::formatCnab('A', $boleto->getAceite(), 1));
        // Data de emissão do título (110-117)
        $this->add(110, 117, $boleto->getDataDocumento()->format('dmY'));

        // Juros de mora (118-141)
        $this->preencheJuros($boleto);

        // Desconto 1 (142-165)
        $this->preencheDescontoSegmentoP($boleto);

        // Valor do IOF (166-180) - não calculado pelo sistema. Campo numérico não utilizado = zeros (manual 2.1-3), nunca espaços.
        $this->add(166, 180, Util::formatCnab('9', 0, 15, 2));
        // Valor do abatimento (181-195)
        $this->add(181, 195, Util::formatCnab('9', 0, 15, 2));

        // Identificação do título na empresa (196-220)
        $this->add(196, 220, Util::formatCnab('X', $boleto->getNumeroControle(), 25));

        // Protesto (221-223)
        $this->preencheProtesto($boleto);
        // Baixa/Devolução (224-227)
        $this->preencheBaixa($boleto);

        // Código da moeda (228-229)
        $this->add(228, 229, Util::formatCnab('X', $moeda, 2));

        // Número do contrato da operação de crédito (230-239)
        $this->add(230, 239, Util::formatCnab('9', 0, 10));

        // CNAB final (240) - branco
        $this->add(240, 240, '');

        return $this;
    }

    /**
     * Juros de mora (campos 27/28/29.3P do manual).
     *
     * @param BoletoContract $boleto
     * @throws ValidationException
     */
    protected function preencheJuros(BoletoContract $boleto)
    {
        // Carteiras R e S: sem instrução de juros no Segmento P (a taxa vai no Segmento R). Campos numéricos = zeros (manual 2.1-3), nunca espaços.
        if ($this->isCarteiraEletronica()) {
            $this->add(118, 118, '0');
            $this->add(119, 126, '00000000');
            $this->add(127, 141, Util::formatCnab('9', 0, 15, 2));

            return;
        }

        // Sem juros: código 0 (o código 3 é "Reservado" neste leiaute).
        if ($boleto->getJuros() <= 0) {
            $this->add(118, 118, '0');
            $this->add(119, 126, '00000000');
            $this->add(127, 141, Util::formatCnab('9', 0, 15, 2));

            return;
        }

        // Manual 28.3P (Data Juros Mora): não enviamos data — o banco aplica os juros automaticamente após o vencimento quando o código 27.3P é 1 ou 2. Campo numérico de data sem valor = zeros (00000000), nunca espaços (manual 2.1-3). Enviar data explícita causava rejeição motivo 79.
        $moeda = $this->getCodigoMoeda($boleto);
        if ($moeda === '02') {
            // Manual 27.3P: para moeda 02 o código só pode ser 1 (valor ao dia). getJuros() devolve percentual; convertemos para valor/dia (base 30).
            $valorDia = ((float) $boleto->getValor()) * (min((float) $boleto->getJuros(), 99.99) / 100) / 30;
            $this->add(118, 118, self::JUROS_VALOR_DIA);
            $this->add(119, 126, '00000000');
            $this->add(127, 141, Util::formatCnab('9', $valorDia, 15, 2));

            return;
        }

        $this->add(118, 118, self::JUROS_TAXA_MENSAL);
        $this->add(119, 126, '00000000');
        $this->add(127, 141, Util::formatCnab('9', min((float) $boleto->getJuros(), 99.99), 15, 2));
    }

    /**
     * Desconto 1 (campos 30/31/32.3P do manual).
     *
     * O campo 30.3P deste leiaute aceita SOMENTE os códigos 1 (valor fixo até a
     * data) e 3 (valor por antecipação dia corrido) — ambos monetários. Os
     * códigos percentuais (2/5) e "dia útil" (4/6) foram excluídos (atualização
     * Jan/2019) e causam rejeição motivo 30 ("Desconto a Conceder Não Confere").
     * Por isso o tipo é mapeado para 1/3 e o percentual é convertido para R$.
     * Valor sempre com 2 casas decimais (manual 32.3P).
     *
     * @param BoletoContract $boleto
     * @throws ValidationException
     */
    protected function preencheDescontoSegmentoP(BoletoContract $boleto)
    {
        // Carteiras R e S: sem desconto no Segmento P. Campos numéricos = zeros (manual 2.1-3), nunca espaços.
        if ($this->isCarteiraEletronica()) {
            $this->add(142, 142, '0');
            $this->add(143, 150, '00000000');
            $this->add(151, 165, Util::formatCnab('9', 0, 15, 2));

            return;
        }

        // resolveCodigoDesconto devolve sempre 1 ou 3 (ver $tiposDescontoSuportados).
        $codigo = $this->resolveCodigoDesconto($boleto);
        $valor = $this->resolveValorDescontoMonetario($boleto);

        if ($codigo === BoletoContract::TIPO_DESCONTO_NENHUM || $valor <= 0) {
            $this->add(142, 142, '0');
            $this->add(143, 150, '00000000');
            $this->add(151, 165, Util::formatCnab('9', 0, 15, 2));

            return;
        }

        // Manual 31.3P: para o código 1, se a data não for informada, assume a
        // data do vencimento.
        $dataDesconto = $boleto->getDataDesconto()
            ? $boleto->getDataDesconto()->format('dmY')
            : $boleto->getDataVencimento()->format('dmY');

        // Manual 32.3P: valor monetário com 2 casas decimais (códigos 1 e 3 não
        // são percentuais neste leiaute).
        $this->add(142, 142, $codigo);
        $this->add(143, 150, $dataDesconto);
        $this->add(151, 165, Util::formatCnab('9', $valor, 15, 2));
    }

    /**
     * Valor do desconto SEMPRE em R$ (campo 32.3P). O leiaute Banrisul não tem
     * código percentual no campo 30.3P, então qualquer tipo percentual canônico
     * é convertido para valor monetário: valorTitulo * percentual / 100.
     *
     * @param BoletoContract $boleto
     * @return float
     */
    protected function resolveValorDescontoMonetario(BoletoContract $boleto)
    {
        $codigo = (string) $boleto->getDescontoCodigo();

        // Back-compat: setDesconto() sem setDescontoCodigo() = valor fixo.
        if ($codigo === BoletoContract::TIPO_DESCONTO_NENHUM && $boleto->getDesconto() > 0) {
            $codigo = BoletoContract::TIPO_DESCONTO_VALOR_FIXO;
        }

        $percentuais = [
            BoletoContract::TIPO_DESCONTO_PERCENTUAL,
            BoletoContract::TIPO_DESCONTO_PERCENTUAL_DIA_CORRIDO,
            BoletoContract::TIPO_DESCONTO_PERCENTUAL_DIA_UTIL,
        ];

        if (in_array($codigo, $percentuais, true)) {
            return round((float) $boleto->getValor() * (float) $boleto->getDescontoPercentual() / 100, 2);
        }

        return (float) $boleto->getDesconto();
    }

    /**
     * Protesto (campos 37/38.3P do manual).
     *
     * @param BoletoContract $boleto
     * @throws ValidationException
     */
    protected function preencheProtesto(BoletoContract $boleto)
    {
        // Carteiras R e S: sem instrução de protesto. Código 0 (= aceito pelo banco, retorno 37) e prazo zerado; campos numéricos = zeros (manual 2.1-3).
        if ($this->isCarteiraEletronica()) {
            $this->add(221, 221, '0');
            $this->add(222, 223, '00');

            return;
        }

        $dias = (int) $boleto->getDiasProtesto();
        if ($dias > 0) {
            // Manual 38.3P: se o código for 1, o número de dias deve ser >= 03.
            $dias = max(3, min(99, $dias));
            $this->add(221, 221, self::PROTESTO_DIAS_CORRIDOS);
            $this->add(222, 223, Util::formatCnab('9', $dias, 2));

            return;
        }

        $this->add(221, 221, self::PROTESTO_NAO_PROTESTAR);
        $this->add(222, 223, '00');
    }

    /**
     * Baixa/Devolução (campos 39.3P do manual).
     *
     * @param BoletoContract $boleto
     * @throws ValidationException
     */
    protected function preencheBaixa(BoletoContract $boleto)
    {
        $dias = (int) $boleto->getDiasBaixaAutomatica();
        if ($dias > 0) {
            $this->add(224, 224, self::BAIXA_DEVOLVER);
            // Manual: "serão consideradas as duas últimas casas".
            $this->add(225, 227, Util::formatCnab('9', min(99, $dias), 3));

            return;
        }

        $this->add(224, 224, '0');
        $this->add(225, 227, '000');
    }

    /**
     * Segmento Q (registro 3, obrigatório) - manual 3.4.
     *
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
        $especie = (string) $boleto->getEspecieDocCodigo(99, 240);

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

        $cep = Util::onlyNumbers($pagador->getCep());
        $this->add(129, 133, Util::formatCnab('9', substr($cep, 0, 5), 5));   // CEP
        $this->add(134, 136, Util::formatCnab('9', substr($cep, 5, 3), 3));   // Sufixo do CEP
        $this->add(137, 151, Util::formatCnab('X', $this->sanitizeAlfa($pagador->getCidade()), 15));
        $this->add(152, 153, Util::formatCnab('X', $pagador->getUf(), 2));

        // Sacador / Avalista (154-209). Para títulos de terceiros (AD) o nome é
        // obrigatório, editado com um espaço em branco à esquerda (manual 3.4).
        if ($especie === 'AD' && $boleto->getSacadorAvalista()) {
            $sacador = $boleto->getSacadorAvalista();
            $this->add(154, 154, $this->getTipoInscricao($sacador->getDocumento()));
            $this->add(155, 169, Util::formatCnab('9', Util::onlyNumbers($sacador->getDocumento()), 15));
            $nomeEditado = ' ' . ltrim($this->sanitizeAlfa($sacador->getNome()));
            $this->add(170, 209, Util::formatCnab('X', $nomeEditado, 40));
        } else {
            $this->add(154, 154, '0');
            $this->add(155, 169, Util::formatCnab('9', 0, 15));
            $this->add(170, 209, '');
        }

        // Banco correspondente (210-232) - não considerado
        $this->add(210, 212, Util::formatCnab('9', 0, 3));
        $this->add(213, 232, '');

        // CNAB final (233-240) - BRANCOS
        $this->add(233, 240, '');

        return $this;
    }

    /**
     * Segmento R (registro 3, opcional) - manual 3.5. Multa e mensagens 3/4.
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

        // Desconto 2 (18-41) - não utilizado
        $this->add(18, 18, '0');
        $this->add(19, 26, '00000000');
        $this->add(27, 41, Util::formatCnab('9', 0, 15, 2));

        // Desconto 3 (42-65) - não utilizado
        $this->add(42, 42, '0');
        $this->add(43, 50, '00000000');
        $this->add(51, 65, Util::formatCnab('9', 0, 15, 2));

        // Multa (66-89) - manual 14/15/16.3R
        if ($boleto->getMulta() > 0) {
            $dataMulta = $boleto->getDataVencimento()
                ->copy()
                ->addDays(max(1, (int) $boleto->getMultaApos()));
            // Manual 16.3R: percentual = 1 casa decimal; carteiras P/Q/R/S = 3 casas.
            $dec = in_array((string) $this->getCarteira(), ['P', 'Q', 'R', 'S'], true) ? 3 : 1;
            $this->add(66, 66, self::MULTA_PERCENTUAL);
            // Manual 15.3R: data da multa obrigatória, exceto carteira F (em branco).
            $this->add(67, 74, (string) $this->getCarteira() === 'F' ? '' : $dataMulta->format('dmY'));
            $this->add(75, 89, Util::formatCnab('9', $boleto->getMulta(), 15, $dec));
        } else {
            $this->add(66, 66, '0');
            $this->add(67, 74, '00000000');
            $this->add(75, 89, Util::formatCnab('9', 0, 15, 2));
        }

        // Informação ao Pagador (90-99) - não considerado
        $this->add(90, 99, '');

        // Mensagens 3 e 4 (100-179) - prevalecem sobre as mensagens 1 e 2
        $instrucoes = array_values(array_filter((array) $boleto->getInstrucoes(), function ($i) {
            return trim((string) $i) !== '';
        }));
        $this->add(100, 139, Util::formatCnab('X', $this->sanitizeAlfa($instrucoes[0] ?? ''), 40));
        $this->add(140, 179, Util::formatCnab('X', $this->sanitizeAlfa($instrucoes[1] ?? ''), 40));

        // Dados para débito em conta corrente (180-199) - não considerado (numérico)
        $this->add(180, 182, Util::formatCnab('9', 0, 3));   // Banco
        $this->add(183, 186, Util::formatCnab('9', 0, 4));   // Agência
        $this->add(187, 199, Util::formatCnab('9', 0, 13));  // Conta/DV

        // Código de ocorrência do Pagador (200-207) - não considerado (numérico)
        $this->add(200, 207, Util::formatCnab('9', 0, 8));

        // CNAB final (208-240) - BRANCOS
        $this->add(208, 240, '');

        return $this;
    }

    /**
     * Segmento Y-01 - Sacador/Avalista (manual 3.7). Obrigatório apenas para
     * títulos de terceiros.
     *
     * @param BoletoContract $boleto
     *
     * @return $this
     * @throws ValidationException
     */
    public function segmentoY01(BoletoContract $boleto)
    {
        $this->iniciaDetalhe();
        $sacador = $boleto->getSacadorAvalista();

        $this->add(1, 3, Util::onlyNumbers($this->getCodigoBanco()));
        $this->add(4, 7, '0001');
        $this->add(8, 8, '3');
        $this->add(9, 13, Util::formatCnab('9', $this->iRegistrosLote, 5));
        $this->add(14, 14, 'Y');
        $this->add(15, 15, '');
        // Manual 07.3Y: código de movimento remessa = 01.
        $this->add(16, 17, self::OCORRENCIA_REMESSA);
        // Manual 08.3Y: identificação de registro opcional = 03.
        $this->add(18, 19, '03');

        $this->add(20, 20, $this->getTipoInscricao($sacador->getDocumento()));
        $this->add(21, 35, Util::formatCnab('9', Util::onlyNumbers($sacador->getDocumento()), 15));
        $this->add(36, 75, Util::formatCnab('X', $this->sanitizeAlfa($sacador->getNome()), 40));
        $this->add(76, 115, Util::formatCnab('X', $this->sanitizeAlfa($sacador->getEndereco()), 40));
        $this->add(116, 130, Util::formatCnab('X', $this->sanitizeAlfa($sacador->getBairro()), 15));

        $cep = Util::onlyNumbers($sacador->getCep());
        $this->add(131, 135, Util::formatCnab('9', substr($cep, 0, 5), 5));
        $this->add(136, 138, Util::formatCnab('9', substr($cep, 5, 3), 3));
        $this->add(139, 153, Util::formatCnab('X', $this->sanitizeAlfa($sacador->getCidade()), 15));
        $this->add(154, 155, Util::formatCnab('X', $sacador->getUf(), 2));

        // CNAB final (156-240) - BRANCOS
        $this->add(156, 240, '');

        return $this;
    }

    /**
     * Trailler de Lote (registro 5) - manual 3.10.
     *
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

        // Quantidade de registros do lote (header de lote + detalhes + trailler).
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
        // CNAB final (124-240) - BRANCOS
        $this->add(124, 240, '');

        return $this;
    }

    /**
     * Totaliza títulos por modalidade de cobrança para o trailler do lote.
     *
     * Carteiras 1 = simples; 2 = vinculada; 3 = caucionada; R/S = descontada
     * (desconto/vendor eletrônico). Demais carteiras são contabilizadas como
     * simples por padrão.
     *
     * @return array
     */
    protected function totalizaPorCarteira()
    {
        $mapa = [
            '1' => 'simples',
            '2' => 'vinculada',
            '3' => 'caucionada',
            'R' => 'descontada',
            'S' => 'descontada',
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
     * Trailler de Arquivo (registro 9) - manual 3.11.
     *
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
        // Quantidade de lotes do arquivo (registros tipo 1)
        $this->add(18, 23, Util::formatCnab('9', 1, 6));
        // Quantidade de registros do arquivo (tipos 0+1+3+5+9)
        $this->add(24, 29, Util::formatCnab('9', $this->getCount(), 6));
        // Quantidade de contas para conciliação - não considerado
        $this->add(30, 35, Util::formatCnab('9', 0, 6));
        // CNAB final (36-240) - BRANCOS
        $this->add(36, 240, '');

        return $this;
    }
}
