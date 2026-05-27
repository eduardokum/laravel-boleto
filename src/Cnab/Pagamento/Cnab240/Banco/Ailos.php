<?php

namespace Eduardokum\LaravelBoleto\Cnab\Pagamento\Cnab240\Banco;

use Eduardokum\LaravelBoleto\Cnab\Pagamento\Cnab240\AbstractPagamento;
use Eduardokum\LaravelBoleto\Contracts\Cnab\Pagamento as PagamentoRemessaContract;
use Eduardokum\LaravelBoleto\Contracts\Pagamento\Pagamento as PagamentoContract;
use Eduardokum\LaravelBoleto\Util;

/**
 * Class Ailos
 *
 * Remessa CNAB 240 do Sistema Ailos (banco 085) para PAGAMENTO DE TÍTULOS DE
 * COBRANÇA (boletos da própria cooperativa ou de outras instituições). Manual
 * "Pagamento por Arquivo - Layout 240 Posições" (versão 10, mai/2025).
 *
 * Usa segmentos J (obrigatório) + J-52 (obrigatório) — manual seção 5.1.3 e 5.1.4.
 * Segmento J-53 (opcional, agregador eletrônico) não está implementado.
 *
 * Valores não suportados pela Ailos neste manual: PIX, TED/DOC, transferência
 * entre contas. Se essas modalidades forem necessárias, exigem manual adicional
 * fornecido pela cooperativa.
 *
 * @package Eduardokum\LaravelBoleto\Cnab\Pagamento\Cnab240\Banco
 */
class Ailos extends AbstractPagamento implements PagamentoRemessaContract
{
    const BANCO = '085';
    const NOME_BANCO = 'AILOS';
    const VERSAO_LAYOUT = '088';      // Header de arquivo pos 164-166 (manual seção 5.1.1)
    const VERSAO_LAYOUT_LOTE = '045'; // Header de lote pos 14-16 (manual seção 5.1.2)

    const LOTE_SERVICO_HEADER_ARQUIVO = '0000';
    const LOTE_SERVICO_HEADER_LOTE = '0001'; // legado — multilote usa $this->loteAtualNumero
    const LOTE_SERVICO_TRAILER = '9999';

    // Tipos canônicos usados pelo agrupador (AbstractPagamento::agruparPagamentosPorTipo)
    // para separar boletos da própria Ailos (forma 30) dos demais (forma 31).
    const TIPO_PAGAMENTO_BOLETO_AILOS  = 'BOLETO_AILOS';
    const TIPO_PAGAMENTO_BOLETO_OUTROS = 'BOLETO_OUTROS';

    const TIPO_REGISTRO_HEADER_ARQUIVO = '0';
    const TIPO_REGISTRO_HEADER_LOTE = '1';
    const TIPO_REGISTRO_DETALHE = '3';
    const TIPO_REGISTRO_TRAILER_LOTE = '5';
    const TIPO_REGISTRO_TRAILER_ARQUIVO = '9';

    const TIPO_OPERACAO = 'C'; // Crédito
    const CODIGO_REMESSA = '1'; // 1=Remessa, 2=Retorno

    const TIPO_DOCUMENTO_CPF = '1';
    const TIPO_DOCUMENTO_CNPJ = '2';

    const CODIGO_SEGMENTO_J = 'J';
    const CODIGO_REGISTRO_OPCIONAL_J52 = '52';

    // Tipo de serviço (G025) — pagamento a fornecedores
    const TIPO_SERVICO_FORNECEDOR = '20';

    // Forma de lançamento (G029) — manual seção 5.1.2
    const FORMA_LANCAMENTO_BOLETO_AILOS  = '30'; // Pagamento de título de cobrança Ailos
    const FORMA_LANCAMENTO_BOLETO_OUTROS = '31'; // Pagamento de título de outros bancos

    const CAMPO_BRANCO = '';
    const DENSIDADE_GRAVACAO = '01600';

    /**
     * Sequencial do pagamento dentro do lote (Febraban G038). Conta pagamentos
     * (não segmentos): J e J-52 do mesmo título compartilham o mesmo número.
     */
    protected $iSequencialPagamento = 0;

    /**
     * Número do lote atualmente sendo gravado (pos 4-7 dos registros do lote).
     * Atualizado por headerLoteMulti() e reusado por segmentoJ/J-52/trailer
     * para evitar hardcode de "0001" em arquivos multilote.
     */
    protected $loteAtualNumero = 1;

    /**
     * Lista de pagamentos do lote atual (subset de $this->pagamentos).
     * Definida por headerLoteMulti/trailerLoteMulti e consultada por
     * getFormaLancamento() e somatórios de trailer para isolar o lote.
     */
    protected $loteAtualPagamentos = null;

    protected $codigoBanco = self::BANCO;
    protected $carteiras = [];

    protected $fimLinha = "\r\n";
    protected $fimArquivo = "\r\n";

    public function __construct(array $params = [])
    {
        parent::__construct($params);
        $this->codigoBanco = self::BANCO;
    }

    public function getCodigoBanco()
    {
        return self::BANCO;
    }

    protected function getNomeBanco()
    {
        return self::NOME_BANCO;
    }

    protected function getVersaoLayout()
    {
        return self::VERSAO_LAYOUT;
    }

    public function getTipoServico()
    {
        return $this->tipoServico ?? self::TIPO_SERVICO_FORNECEDOR;
    }

    /**
     * Resolve a forma de lançamento conforme o boleto a pagar:
     * - 30 quando é boleto da própria Ailos (banco 085 no código de barras)
     * - 31 quando é boleto de outra instituição financeira
     *
     * Em multilote, $loteAtualPagamentos restringe a inspeção ao lote em
     * gravação — evita retornar a forma de outro lote.
     */
    public function getFormaLancamento()
    {
        $pagamentos = $this->loteAtualPagamentos ?? $this->pagamentos;
        $primeiroPagamento = $pagamentos[0] ?? null;

        if ($primeiroPagamento && method_exists($primeiroPagamento, 'getCodigoBarras')) {
            return $this->ehBoletoAilos($primeiroPagamento)
                ? self::FORMA_LANCAMENTO_BOLETO_AILOS
                : self::FORMA_LANCAMENTO_BOLETO_OUTROS;
        }

        return self::FORMA_LANCAMENTO_BOLETO_OUTROS;
    }

    /**
     * Classifica um pagamento como boleto Ailos (banco 085 nas 3 primeiras
     * posições do código de barras) ou de outro banco. Usado pelo agrupador
     * de lotes e pela escolha de forma de lançamento.
     */
    protected function ehBoletoAilos($pagamento): bool
    {
        if (!method_exists($pagamento, 'getCodigoBarras')) {
            return false;
        }

        $bancoFavorecido = substr(Util::onlyNumbers($pagamento->getCodigoBarras() ?? ''), 0, 3);

        return $bancoFavorecido === self::BANCO;
    }

    /**
     * Sobrescreve o agrupador padrão para que boletos Ailos (forma 30) e
     * boletos de outros bancos (forma 31) caiam em lotes separados — manual
     * 5.1 exige um único tipo de serviço/forma por lote.
     */
    protected function getTipoPagamentoDoPagamento(\Eduardokum\LaravelBoleto\Pagamento\Banco\Banco $pagamento)
    {
        return $this->ehBoletoAilos($pagamento)
            ? self::TIPO_PAGAMENTO_BOLETO_AILOS
            : self::TIPO_PAGAMENTO_BOLETO_OUTROS;
    }

    /**
     * Header do arquivo (manual seção 5.1.1).
     */
    protected function header()
    {
        $this->iniciaHeader();

        $tipoInsc = $this->getPagador()->getTipoDocumento() == 'CPF'
            ? self::TIPO_DOCUMENTO_CPF
            : self::TIPO_DOCUMENTO_CNPJ;

        $this->add(1, 3, self::BANCO);                                                                      // 001-003: Código do Banco (085)
        $this->add(4, 7, self::LOTE_SERVICO_HEADER_ARQUIVO);                                                // 004-007: Lote de Serviço (0000)
        $this->add(8, 8, self::TIPO_REGISTRO_HEADER_ARQUIVO);                                               // 008-008: Tipo de Registro (0)
        $this->add(9, 17, self::CAMPO_BRANCO);                                                              // 009-017: Brancos (Uso FEBRABAN)
        $this->add(18, 18, $tipoInsc);                                                                      // 018-018: Tipo de Inscrição da Empresa
        $this->add(19, 32, Util::formatCnab('9L', $this->getPagador()->getDocumento(), 14));                // 019-032: Nº Inscrição da Empresa
        $this->add(33, 52, Util::formatCnab('X', $this->getConvenio() ?? '', 20));                          // 033-052: Código do Convênio
        $this->add(53, 57, Util::formatCnab('9L', $this->getAgencia(), 5));                                 // 053-057: Agência
        $this->add(58, 58, Util::formatCnab('X', $this->getAgenciaDv() ?? '', 1));                          // 058-058: DV Agência
        $this->add(59, 70, Util::formatCnab('9L', $this->getConta(), 12));                                  // 059-070: Conta
        $this->add(71, 71, Util::formatCnab('X', $this->getContaDv() ?? '', 1));                            // 071-071: DV Conta
        $this->add(72, 72, self::CAMPO_BRANCO);                                                             // 072-072: DV Ag/Conta (branco)
        $this->add(73, 102, Util::formatCnab('X', Util::normalizeChars($this->getPagador()->getNome()), 30)); // 073-102: Nome da Empresa
        $this->add(103, 132, Util::formatCnab('X', self::NOME_BANCO, 30));                                  // 103-132: Nome do Banco
        $this->add(133, 142, self::CAMPO_BRANCO);                                                           // 133-142: Brancos
        $this->add(143, 143, self::CODIGO_REMESSA);                                                         // 143-143: Código Remessa/Retorno
        $this->add(144, 151, $this->getDataRemessa('dmY'));                                                 // 144-151: Data Geração
        $this->add(152, 157, $this->getDataRemessa('His'));                                                 // 152-157: Hora Geração
        $this->add(158, 163, Util::formatCnab('9L', $this->getIdremessa(), 6));                             // 158-163: Nº Sequencial Arquivo
        $this->add(164, 166, self::VERSAO_LAYOUT);                                                          // 164-166: Versão Layout Arquivo (088)
        $this->add(167, 171, self::DENSIDADE_GRAVACAO);                                                     // 167-171: Densidade Gravação
        $this->add(172, 191, self::CAMPO_BRANCO);                                                           // 172-191: Reservado Banco
        $this->add(192, 211, self::CAMPO_BRANCO);                                                           // 192-211: Reservado Empresa
        $this->add(212, 240, self::CAMPO_BRANCO);                                                           // 212-240: Brancos

        return $this;
    }

    /**
     * Header do lote (manual seção 5.1.2).
     */
    protected function headerLote()
    {
        $this->iniciaHeaderLote();
        $this->iSequencialPagamento = 0;

        $tipoInsc = $this->getPagador()->getTipoDocumento() == 'CPF'
            ? self::TIPO_DOCUMENTO_CPF
            : self::TIPO_DOCUMENTO_CNPJ;

        $this->add(1, 3, self::BANCO);                                                                      // 001-003: Código do Banco (085)
        $this->add(4, 7, Util::formatCnab('9L', $this->loteAtualNumero, 4));                                // 004-007: Código do Lote (NOTA 3)
        $this->add(8, 8, self::TIPO_REGISTRO_HEADER_LOTE);                                                  // 008-008: Tipo de Registro (1)
        $this->add(9, 9, self::TIPO_OPERACAO);                                                              // 009-009: Tipo de Operação (C)
        $this->add(10, 11, $this->getTipoServico());                                                        // 010-011: Tipo de Serviço
        $this->add(12, 13, $this->getFormaLancamento());                                                    // 012-013: Forma de Lançamento (30 ou 31)
        $this->add(14, 16, self::VERSAO_LAYOUT_LOTE);                                                       // 014-016: Versão Layout Lote (045)
        $this->add(17, 17, self::CAMPO_BRANCO);                                                             // 017-017: Brancos
        $this->add(18, 18, $tipoInsc);                                                                      // 018-018: Tipo Inscrição Empresa
        $this->add(19, 32, Util::formatCnab('9L', $this->getPagador()->getDocumento(), 14));                // 019-032: Nº Inscrição Empresa
        $this->add(33, 52, Util::formatCnab('X', $this->getConvenio() ?? '', 20));                          // 033-052: Convênio
        $this->add(53, 57, Util::formatCnab('9L', $this->getAgencia(), 5));                                 // 053-057: Agência
        $this->add(58, 58, Util::formatCnab('X', $this->getAgenciaDv() ?? '', 1));                          // 058-058: DV Agência
        $this->add(59, 70, Util::formatCnab('9L', $this->getConta(), 12));                                  // 059-070: Conta
        $this->add(71, 71, Util::formatCnab('X', $this->getContaDv() ?? '', 1));                            // 071-071: DV Conta
        $this->add(72, 72, self::CAMPO_BRANCO);                                                             // 072-072: DV Ag/Conta
        $this->add(73, 102, Util::formatCnab('X', Util::normalizeChars($this->getPagador()->getNome()), 30)); // 073-102: Nome da Empresa
        $this->add(103, 142, self::CAMPO_BRANCO);                                                           // 103-142: Mensagem
        $this->add(143, 172, Util::formatCnab('X', Util::normalizeChars($this->getPagador()->getEndereco() ?? ''), 30)); // 143-172: Endereço
        $this->add(173, 177, self::CAMPO_BRANCO);                                                           // 173-177: Número do Local
        $this->add(178, 192, self::CAMPO_BRANCO);                                                           // 178-192: Casa, Apto
        $this->add(193, 212, Util::formatCnab('X', Util::normalizeChars($this->getPagador()->getCidade() ?? ''), 20)); // 193-212: Cidade
        $cep = Util::formatCnab('9L', $this->getPagador()->getCep() ?? '', 8);
        $this->add(213, 217, substr($cep, 0, 5));                                                           // 213-217: CEP
        $this->add(218, 220, substr($cep, 5, 3));                                                           // 218-220: Complemento CEP
        $this->add(221, 222, Util::formatCnab('X', $this->getPagador()->getUf() ?? '', 2));                 // 221-222: UF
        $this->add(223, 230, self::CAMPO_BRANCO);                                                           // 223-230: Brancos
        $this->add(231, 240, self::CAMPO_BRANCO);                                                           // 231-240: Ocorrências (uso retorno)

        return $this;
    }

    public function addPagamento(PagamentoContract $pagamento)
    {
        $this->pagamentos[] = $pagamento;
        return $this;
    }

    /**
     * Para cada pagamento, gera o par de segmentos J + J-52 obrigatórios.
     */
    protected function gerarSegmentos(\Eduardokum\LaravelBoleto\Pagamento\Banco\Banco $pagamento)
    {
        $this->segmentoJ($pagamento);
        $this->segmentoJ52($pagamento);
    }

    /**
     * Segmento J — Pagamento de título de cobrança (manual seção 5.1.3).
     * Posições 018-061 contêm a decomposição do código de barras do boleto.
     */
    public function segmentoJ($pagamento)
    {
        $this->iniciaDetalhe();
        $this->iSequencialPagamento++;

        $codigoBarras = Util::onlyNumbers($pagamento->getCodigoBarras() ?? '');

        $this->add(1, 3, self::BANCO);                                                                      // 001-003: Banco
        $this->add(4, 7, Util::formatCnab('9L', $this->loteAtualNumero, 4));                                // 004-007: Lote
        $this->add(8, 8, self::TIPO_REGISTRO_DETALHE);                                                      // 008-008: Tipo Registro (3)
        $this->add(9, 13, Util::formatCnab('9L', $this->iSequencialPagamento, 5));                          // 009-013: Nº Sequencial Registro Lote
        $this->add(14, 14, self::CODIGO_SEGMENTO_J);                                                        // 014-014: Segmento (J)
        $this->add(15, 17, Util::formatCnab('9L', '0', 3));                                                 // 015-017: Tipo de Movimento

        // 018-061: Código de barras decomposto
        $this->add(18, 20, substr($codigoBarras, 0, 3));   // Banco favorecido
        $this->add(21, 21, substr($codigoBarras, 3, 1));   // Moeda
        $this->add(22, 22, substr($codigoBarras, 4, 1));   // DV código de barras
        $this->add(23, 26, substr($codigoBarras, 5, 4));   // Fator de vencimento
        $this->add(27, 36, substr($codigoBarras, 9, 10));  // Valor
        $this->add(37, 61, substr($codigoBarras, 19, 25)); // Campo livre

        // Nome do beneficiário (cedente do boleto)
        $this->add(62, 91, Util::formatCnab('X', Util::normalizeChars($pagamento->getBeneficiario()->getNome()), 30));

        // Data de vencimento nominal
        $dataVencimento = $pagamento->getDataVencimento()
            ? $pagamento->getDataVencimento()->format('dmY')
            : date('dmY');
        $this->add(92, 99, Util::formatCnab('9L', $dataVencimento, 8));

        // Valor nominal (do título)
        $valorTitulo = (int) round(($pagamento->getValorTitulo() ?? $pagamento->getValor()) * 100);
        $this->add(100, 114, Util::formatCnab('9L', $valorTitulo, 15));

        // Descontos / Abatimentos
        $valorDesconto = (int) round(($pagamento->getDesconto() ?? 0) * 100);
        $this->add(115, 129, Util::formatCnab('9L', $valorDesconto, 15));

        // Acréscimos (mora + multa)
        $valorAcrescimo = (int) round(($pagamento->getAcrescimo() ?? 0) * 100);
        $this->add(130, 144, Util::formatCnab('9L', $valorAcrescimo, 15));

        // Data efetiva do pagamento
        $dataPagamento = $pagamento->getDataPagamento()
            ? $pagamento->getDataPagamento()->format('dmY')
            : date('dmY');
        $this->add(145, 152, Util::formatCnab('9L', $dataPagamento, 8));

        // Valor efetivo do pagamento
        $valorPagamento = (int) round($pagamento->getValor() * 100);
        $this->add(153, 167, Util::formatCnab('9L', $valorPagamento, 15));

        $this->add(168, 182, Util::formatCnab('9', '0', 15));                                               // 168-182: Zeros
        $this->add(183, 202, Util::formatCnab('X', $pagamento->getNumeroControle() ?? '', 20));             // 183-202: Seu Número (nº doc atribuído pela empresa)
        $this->add(203, 222, self::CAMPO_BRANCO);                                                           // 203-222: Nosso Número (preenchido no retorno - 20 alfa)
        $this->add(223, 224, Util::formatCnab('9', '09', 2));                                               // 223-224: Código da Moeda (09 = Real, G065)
        $this->add(225, 230, self::CAMPO_BRANCO);                                                           // 225-230: CNAB
        $this->add(231, 240, self::CAMPO_BRANCO);                                                           // 231-240: Ocorrências (preenchido no retorno)

        return $this;
    }

    /**
     * Segmento J-52 — Identificação do sacado/cedente/sacador avalista.
     * Obrigatório para forma de lançamento 30 ou 31 (manual seção 5.1.4).
     */
    public function segmentoJ52($pagamento)
    {
        $this->iniciaDetalhe();

        $sacado = $this->getPagador();
        $tipoInscSacado = $sacado->getTipoDocumento() == 'CPF'
            ? self::TIPO_DOCUMENTO_CPF
            : self::TIPO_DOCUMENTO_CNPJ;

        $cedente = $pagamento->getBeneficiario();
        $tipoInscCedente = $cedente->getTipoDocumento() == 'CPF'
            ? self::TIPO_DOCUMENTO_CPF
            : self::TIPO_DOCUMENTO_CNPJ;

        $sacadorAvalista = method_exists($pagamento, 'getSacadorAvalista')
            ? $pagamento->getSacadorAvalista()
            : null;

        $this->add(1, 3, self::BANCO);                                                                      // 001-003: Banco
        $this->add(4, 7, Util::formatCnab('9L', $this->loteAtualNumero, 4));                                // 004-007: Lote
        $this->add(8, 8, self::TIPO_REGISTRO_DETALHE);                                                      // 008-008: Tipo Registro (3)
        $this->add(9, 13, Util::formatCnab('9L', $this->iSequencialPagamento, 5));                          // 009-013: Mesmo seq do J
        $this->add(14, 14, self::CODIGO_SEGMENTO_J);                                                        // 014-014: Segmento (J)
        $this->add(15, 17, Util::formatCnab('9L', '0', 3));                                                 // 015-017: Tipo Movimento
        $this->add(18, 19, self::CODIGO_REGISTRO_OPCIONAL_J52);                                             // 018-019: Identificação Reg Opcional (52)

        $this->add(20, 20, $tipoInscSacado);                                                                // 020-020: Tipo Insc Sacado
        $this->add(21, 35, Util::formatCnab('9L', $sacado->getDocumento(), 15));                            // 021-035: Nº Inscrição Sacado
        $this->add(36, 75, Util::formatCnab('X', Util::normalizeChars($sacado->getNome()), 40));            // 036-075: Nome Sacado

        $this->add(76, 76, $tipoInscCedente);                                                               // 076-076: Tipo Insc Cedente
        $this->add(77, 91, Util::formatCnab('9L', $cedente->getDocumento(), 15));                           // 077-091: Nº Inscrição Cedente
        $this->add(92, 131, Util::formatCnab('X', Util::normalizeChars($cedente->getNome()), 40));          // 092-131: Nome Cedente

        if ($sacadorAvalista !== null) {
            $tipoInscSacador = $sacadorAvalista->getTipoDocumento() == 'CPF'
                ? self::TIPO_DOCUMENTO_CPF
                : self::TIPO_DOCUMENTO_CNPJ;
            $this->add(132, 132, $tipoInscSacador);
            $this->add(133, 147, Util::formatCnab('9L', $sacadorAvalista->getDocumento(), 15));
            $this->add(148, 187, Util::formatCnab('X', Util::normalizeChars($sacadorAvalista->getNome()), 40));
        } else {
            $this->add(132, 132, '0');
            $this->add(133, 147, Util::formatCnab('9', '0', 15));
            $this->add(148, 187, self::CAMPO_BRANCO);
        }

        $this->add(188, 240, self::CAMPO_BRANCO);

        return $this;
    }

    /**
     * Trailer do lote.
     */
    protected function trailerLote()
    {
        $this->iniciaTrailerLote();

        $this->add(1, 3, self::BANCO);
        $this->add(4, 7, Util::formatCnab('9L', $this->loteAtualNumero, 4));
        $this->add(8, 8, self::TIPO_REGISTRO_TRAILER_LOTE);
        $this->add(9, 17, self::CAMPO_BRANCO);
        $this->add(18, 23, Util::formatCnab('9L', $this->getCountRegistrosLote(), 6));
        $this->add(24, 41, Util::formatCnab('9L', $this->getValorTotalLote(), 18));
        $this->add(42, 59, Util::formatCnab('9', '0', 18));
        $this->add(60, 230, self::CAMPO_BRANCO);
        $this->add(231, 240, self::CAMPO_BRANCO);

        return $this;
    }

    /**
     * Trailer do arquivo. Soma lotes e registros corretamente em multilote.
     */
    protected function trailer()
    {
        $this->iniciaTrailer();

        $totalLotes = empty($this->lotes) ? 1 : $this->getCountLotes();
        $totalRegistros = empty($this->lotes) ? $this->getCount() : $this->getCountMulti();

        $this->add(1, 3, self::BANCO);
        $this->add(4, 7, self::LOTE_SERVICO_TRAILER);
        $this->add(8, 8, self::TIPO_REGISTRO_TRAILER_ARQUIVO);
        $this->add(9, 17, self::CAMPO_BRANCO);
        $this->add(18, 23, Util::formatCnab('9L', $totalLotes, 6));                                         // Quantidade de lotes do arquivo
        $this->add(24, 29, Util::formatCnab('9L', $totalRegistros, 6));                                     // Total de registros do arquivo
        $this->add(30, 35, Util::formatCnab('9L', 0, 6));
        $this->add(36, 240, self::CAMPO_BRANCO);

        return $this;
    }

    /**
     * Header do lote para multilote: memoriza o número e a fatia de pagamentos
     * deste lote e delega para headerLote(), que já usa essas referências.
     */
    protected function headerLoteMulti(array $lote)
    {
        $this->loteAtualNumero = (int) $lote['numero'];
        $this->loteAtualPagamentos = $lote['pagamentos'];

        return $this->headerLote();
    }

    /**
     * Trailer do lote para multilote: idem headerLoteMulti.
     */
    protected function trailerLoteMulti(array $lote)
    {
        $this->loteAtualNumero = (int) $lote['numero'];
        $this->loteAtualPagamentos = $lote['pagamentos'];

        return $this->trailerLote();
    }

    /**
     * Trailer do arquivo para multilote (chamado por AbstractPagamento::gerar).
     */
    protected function trailerMulti()
    {
        return $this->trailer();
    }

    protected function getCountDetalhes()
    {
        // Cada pagamento gera 2 segmentos (J + J-52). Em multilote, usa só os
        // pagamentos do lote atual; fora dele, todos os pagamentos.
        $alvo = $this->loteAtualPagamentos ?? $this->pagamentos;

        return count($alvo) * 2;
    }

    protected function getCountRegistrosLote()
    {
        return $this->getCountDetalhes() + 2; // header + detalhes + trailer
    }

    protected function getValorTotalLote()
    {
        $alvo = $this->loteAtualPagamentos ?? $this->pagamentos;
        $valorTotal = 0;

        foreach ($alvo as $pagamento) {
            if (method_exists($pagamento, 'getValor') && $pagamento->getValor() > 0) {
                $valorTotal += $pagamento->getValor();
            }
        }

        return Util::formatCnab('9L', $valorTotal * 100, 18);
    }

    /**
     * Total de registros do arquivo considerando todos os lotes.
     */
    protected function getCountMulti()
    {
        $total = 0;

        foreach ($this->lotes as $lote) {
            $total += count($lote['pagamentos']) * 2; // J + J-52
            $total += 2;                              // header e trailer do lote
        }

        $total += 2; // header e trailer do arquivo

        return $total;
    }

    /**
     * Total monetário de um lote específico (usado pelos trailers multilote).
     */
    protected function getValorTotalLoteMulti(array $lote)
    {
        $valorTotal = 0;

        foreach ($lote['pagamentos'] as $pagamento) {
            if (method_exists($pagamento, 'getValor') && $pagamento->getValor() > 0) {
                $valorTotal += $pagamento->getValor();
            }
        }

        return Util::formatCnab('9L', $valorTotal * 100, 18);
    }

    protected function getConvenio()
    {
        return $this->convenio ?? null;
    }

    public function setConvenio($convenio)
    {
        $this->convenio = $convenio;
        return $this;
    }

    protected $convenio;
}
