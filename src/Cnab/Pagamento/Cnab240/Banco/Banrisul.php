<?php

namespace Eduardokum\LaravelBoleto\Cnab\Pagamento\Cnab240\Banco;

use Eduardokum\LaravelBoleto\Cnab\Pagamento\Cnab240\AbstractPagamento;
use Eduardokum\LaravelBoleto\Contracts\Cnab\Pagamento as PagamentoRemessaContract;
use Eduardokum\LaravelBoleto\Contracts\Pagamento\Pagamento as PagamentoContract;
use Eduardokum\LaravelBoleto\Pagamento\Banco\Banco;
use Eduardokum\LaravelBoleto\Util;

/**
 * Class Banrisul
 * Remessa de Pagamento CNAB 240 - Layout BRR Contas a Pagar
 *
 * @package Eduardokum\LaravelBoleto\Cnab\Pagamento\Cnab240\Banco
 */
class Banrisul extends AbstractPagamento implements PagamentoRemessaContract
{
    const BANCO = '041';
    const NOME_BANCO = 'BANRISUL';
    const LOTE_SERVICO = '0000';
    const LOTE_SERVICO_HEADER = '0001';
    const TIPO_REGISTRO_HEADER = '0';
    const TIPO_REGISTRO_HEADER_LOTE = '1';
    const TIPO_REGISTRO_DETALHE = '3';
    const TIPO_REGISTRO_TRAILER_LOTE = '5';
    const TIPO_REGISTRO_TRAILER = '9';
    const LOTE_SERVICO_TRAILER = '9999';
    const TIPO_DOCUMENTO_CPF = '1';
    const TIPO_DOCUMENTO_CNPJ = '2';
    const CODIGO_REMESSA = '1';
    const VERSAO_LAYOUT = '040';
    const TIPO_OPERACAO = 'C';
    const CAMPO_BRANCO = '';

    // Formas de lancamento
    const FORMA_LANCAMENTO_CREDITO_CC = '01';
    const FORMA_LANCAMENTO_DOC_TED = '03';
    const FORMA_LANCAMENTO_OP = '10';

    // Tipos de servico
    const TIPO_SERVICO_PAGAMENTO_FORNECEDOR = '20';
    const TIPO_SERVICO_TED = '12';
    const TIPO_SERVICO_PAGAMENTO_SALARIOS = '30';
    const TIPO_SERVICO_PAGAMENTO_DIVERSO = '98';

    // Segmentos
    const CODIGO_SEGMENTO_A = 'A';
    const CODIGO_SEGMENTO_B = 'B';

    // Movimento
    const TIPO_MOVIMENTO = '0';
    const CODIGO_INSTRUCAO_MOVIMENTO = '00';

    // Camara compensacao
    const CODIGO_CAMARA_COMPENSACAO = '010';

    // Moeda
    const TIPO_MOEDA = 'BRL';

    // Aviso
    const AVISO_FAVORECIDO_NAO = '0';

    /**
     * Codigo do convenio no banco (informado pela agencia)
     *
     * @var string
     */
    protected $convenio;

    /**
     * Tipo de pagamento (TED, DOC, PIX)
     *
     * @var string
     */
    protected $tipoPagamento = 'TED';

    /**
     * Tipo de servico
     *
     * @var string
     */
    protected $tipoServico = '20';

    /**
     * Codigo do banco
     *
     * @var string
     */
    protected $codigoBanco = self::BANCO;

    /**
     * Carteiras disponiveis
     *
     * @var array
     */
    protected $carteiras = [];

    /**
     * Banrisul constructor.
     *
     * @param array $params
     */
    public function __construct(array $params = [])
    {
        parent::__construct($params);
        $this->codigoBanco = self::BANCO;

        $this->addCampoObrigatorio('convenio');

        if (isset($params['tipoPagamento'])) {
            $this->tipoPagamento = $params['tipoPagamento'];
        }
        if (isset($params['tipoServico'])) {
            $this->tipoServico = $params['tipoServico'];
        }
        if (isset($params['convenio'])) {
            $this->convenio = $params['convenio'];
        }
    }

    /**
     * @return string|null
     */
    public function getConvenio()
    {
        return $this->convenio;
    }

    /**
     * @param string $convenio
     * @return $this
     */
    public function setConvenio($convenio)
    {
        $this->convenio = $convenio;
        return $this;
    }

    /**
     * @return string
     */
    public function getCodigoBanco()
    {
        return self::BANCO;
    }

    /**
     * @return string
     */
    public function getTipoPagamento()
    {
        return $this->tipoPagamento;
    }

    /**
     * @param string $tipoPagamento
     * @return $this
     */
    public function setTipoPagamento($tipoPagamento)
    {
        $this->tipoPagamento = $tipoPagamento;
        return $this;
    }

    /**
     * @return string
     */
    public function getTipoServico()
    {
        return $this->tipoServico ?? '20';
    }

    /**
     * Retorna a forma de lancamento baseada no tipo de pagamento
     *
     * Para Banrisul:
     * 01 - Credito em C/C (mesmo banco)
     * 03 - DOC/TED (outros bancos)
     *
     * @return string
     */
    public function getFormaLancamento()
    {
        return $this->getFormaLancamentoPorTipo($this->tipoPagamento);
    }

    /**
     * Retorna a forma de lancamento por tipo
     *
     * @param string $tipoPagamento
     * @return string
     */
    protected function getFormaLancamentoPorTipo($tipoPagamento)
    {
        switch (strtoupper($tipoPagamento)) {
            case 'CC':
            case 'CREDITO_CC':
                return self::FORMA_LANCAMENTO_CREDITO_CC; // 01
            case 'DOC':
            case 'TED':
            default:
                return self::FORMA_LANCAMENTO_DOC_TED; // 03
        }
    }

    /**
     * Retorna o tipo de servico por tipo de pagamento
     *
     * @param string $tipoPagamento
     * @return string
     */
    protected function getTipoServicoPorTipo($tipoPagamento)
    {
        switch (strtoupper($tipoPagamento)) {
            case 'TED':
                return '12'; // T.E.D.
            case 'DOC':
                return '20'; // Pagamento Fornecedor (DOC usa tipo servico 20 com forma 03)
            case 'CC':
            case 'CREDITO_CC':
                return '20'; // Pagamento Fornecedor
            default:
                return $this->getTipoServico();
        }
    }

    /**
     * Header de Arquivo - Registro Tipo '0'
     *
     * @return $this
     * @throws \Exception
     */
    protected function header()
    {
        $this->iniciaHeader();

        // 001-003: Codigo do Banco na Compensacao (041)
        $this->add(1, 3, self::BANCO);
        // 004-007: Lote de Servico (0000 para header de arquivo)
        $this->add(4, 7, self::LOTE_SERVICO);
        // 008-008: Tipo de Registro (0 = header de arquivo)
        $this->add(8, 8, self::TIPO_REGISTRO_HEADER);
        // 009-017: Uso exclusivo Febraban/Cnab - Brancos
        $this->add(9, 17, self::CAMPO_BRANCO);
        // 018-018: Tipo de Inscricao da Empresa (1=CPF, 2=CNPJ)
        $this->add(18, 18, Util::formatCnab('9L', $this->getTipoInscricao(), 1));
        // 019-032: Numero da inscricao da Empresa (CPF/CNPJ)
        $this->add(19, 32, Util::formatCnab('9L', $this->getPagador()->getDocumento(), 14));
        // 033-037: Codigo do Convenio no Banco
        $this->add(33, 37, Util::formatCnab('9L', $this->getConvenio(), 5));
        // 038-052: Brancos
        $this->add(38, 52, self::CAMPO_BRANCO);
        // 053-057: Agencia mantenedora da Conta (0AAAA)
        $this->add(53, 57, Util::formatCnab('9L', $this->getAgencia(), 5));
        // 058-058: '0' (zero) - Constante
        $this->add(58, 58, '0');
        // 059-061: '000' (zeros)
        $this->add(59, 61, '000');
        // 062-071: Numero da Conta Corrente
        $this->add(62, 71, Util::formatCnab('9L', $this->getConta(), 10));
        // 072-072: 0 (zero) ou branco
        $this->add(72, 72, '0');
        // 073-102: Nome da Empresa
        $this->add(73, 102, Util::formatCnab('X', $this->getPagador()->getNome(), 30));
        // 103-132: Nome do Banco
        $this->add(103, 132, Util::formatCnab('X', self::NOME_BANCO, 30));
        // 133-142: Brancos (uso exclusivo Febraban/Cnab)
        $this->add(133, 142, self::CAMPO_BRANCO);
        // 143-143: Codigo Remessa/Retorno (1=Remessa)
        $this->add(143, 143, self::CODIGO_REMESSA);
        // 144-151: Data de geracao do Arquivo (DDMMAAAA)
        $this->add(144, 151, $this->getDataRemessa('dmY'));
        // 152-157: Hora de geracao do Arquivo (HHMMSS)
        $this->add(152, 157, $this->getDataRemessa('His'));
        // 158-163: Numero Sequencial do Arquivo
        $this->add(158, 163, Util::formatCnab('9L', $this->getIdremessa(), 6));
        // 164-166: Numero da versao do leiaute do arquivo (020, 030 ou 040)
        $this->add(164, 166, self::VERSAO_LAYOUT);
        // 167-171: Densidade de gravacao de Arquivo (BPI)
        $this->add(167, 171, Util::formatCnab('9', '0', 5));
        // 172-191: Para uso do Banco
        $this->add(172, 191, self::CAMPO_BRANCO);
        // 192-211: Para uso Empresa
        $this->add(192, 211, self::CAMPO_BRANCO);
        // 212-240: Brancos (uso exclusivo Febraban/Cnab)
        $this->add(212, 240, self::CAMPO_BRANCO);

        return $this;
    }

    /**
     * Header de Lote - Registro Tipo '1'
     *
     * @return $this
     * @throws \Exception
     */
    protected function headerLote()
    {
        $this->iniciaHeaderLote();

        // 001-003: Codigo do Banco na Compensacao (041)
        $this->add(1, 3, self::BANCO);
        // 004-007: Lote de Servico
        $this->add(4, 7, Util::formatCnab('9L', self::LOTE_SERVICO_HEADER, 4));
        // 008-008: Tipo de Registro (1 = Header de Lote)
        $this->add(8, 8, self::TIPO_REGISTRO_HEADER_LOTE);
        // 009-009: Tipo de Operacao (C = Credito)
        $this->add(9, 9, self::TIPO_OPERACAO);
        // 010-011: Tipo de Servico
        $this->add(10, 11, Util::formatCnab('9L', $this->getTipoServico(), 2));
        // 012-013: Forma de Lancamento
        $this->add(12, 13, Util::formatCnab('9L', $this->getFormaLancamento(), 2));
        // 014-016: Numero da versao do header do lote (igual ao header do arquivo)
        $this->add(14, 16, self::VERSAO_LAYOUT);
        // 017-017: Brancos
        $this->add(17, 17, self::CAMPO_BRANCO);
        // 018-018: Tipo de Inscricao da Empresa (1=CPF, 2=CNPJ)
        $this->add(18, 18, Util::formatCnab('9L', $this->getTipoInscricao(), 1));
        // 019-032: Numero de Inscricao da Empresa (CPF ou CNPJ)
        $this->add(19, 32, Util::formatCnab('9L', $this->getPagador()->getDocumento(), 14));
        // 033-037: Codigo do Convenio no Banco
        $this->add(33, 37, Util::formatCnab('9L', $this->getConvenio(), 5));
        // 038-052: Brancos
        $this->add(38, 52, self::CAMPO_BRANCO);
        // 053-057: Agencia Mantenedora da Conta (0AAAA)
        $this->add(53, 57, Util::formatCnab('9L', $this->getAgencia(), 5));
        // 058-061: Zeros
        $this->add(58, 61, Util::formatCnab('9', '0', 4));
        // 062-071: Numero da Conta Corrente
        $this->add(62, 71, Util::formatCnab('9L', $this->getConta(), 10));
        // 072-072: Branco
        $this->add(72, 72, self::CAMPO_BRANCO);
        // 073-102: Nome da Empresa
        $this->add(73, 102, Util::formatCnab('X', $this->getPagador()->getNome(), 30));
        // 103-142: Brancos
        $this->add(103, 142, self::CAMPO_BRANCO);
        // 143-172: Endereco
        $this->add(143, 172, Util::formatCnab('X', $this->getPagador()->getEndereco(), 30));
        // 173-177: Numero do local
        $this->add(173, 177, Util::formatCnab('9L', '', 5));
        // 178-192: Complemento
        $this->add(178, 192, self::CAMPO_BRANCO);
        // 193-212: Nome da Cidade
        $this->add(193, 212, Util::formatCnab('X', $this->getPagador()->getCidade(), 20));
        // 213-220: CEP
        $this->add(213, 220, Util::formatCnab('9L', $this->getPagador()->getCep(), 8));
        // 221-222: Sigla do Estado
        $this->add(221, 222, Util::formatCnab('X', $this->getPagador()->getUf(), 2));
        // 223-224: Classificacao da ordem dos registros (brancos = nao reordena)
        $this->add(223, 224, self::CAMPO_BRANCO);
        // 225-230: Uso exclusivo da Febraban/Cnab - Brancos
        $this->add(225, 230, self::CAMPO_BRANCO);
        // 231-240: Codigos de ocorrencias p/retorno
        $this->add(231, 240, self::CAMPO_BRANCO);

        return $this;
    }

    /**
     * Segmento A - Pagamento C/C, DOC, OP e TED
     *
     * @param Banco $pagamento
     * @return $this
     * @throws \Exception
     */
    public function segmentoA($pagamento)
    {
        $this->iniciaDetalhe();

        // 001-003: Codigo do Banco na Compensacao (041)
        $this->add(1, 3, self::BANCO);
        // 004-007: Lote de Servico
        $this->add(4, 7, Util::formatCnab('9L', self::LOTE_SERVICO_HEADER, 4));
        // 008-008: Tipo de Registro (3 = Detalhe)
        $this->add(8, 8, self::TIPO_REGISTRO_DETALHE);
        // 009-013: Numero sequencial do registro no Lote
        $this->add(9, 13, Util::formatCnab('9L', $this->iRegistrosLote, 5));
        // 014-014: Codigo do Segmento (A)
        $this->add(14, 14, self::CODIGO_SEGMENTO_A);
        // 015-015: Tipo de Movimento (0 = Inclusao)
        $this->add(15, 15, self::TIPO_MOVIMENTO);
        // 016-017: Codigo de Instrucao do movimento (00 = Inclusao)
        $this->add(16, 17, self::CODIGO_INSTRUCAO_MOVIMENTO);
        // 018-020: Codigo da Camara de Compensacao (010)
        $this->add(18, 20, Util::formatCnab('9L', self::CODIGO_CAMARA_COMPENSACAO, 3));
        // 021-023: Codigo do Banco do favorecido
        $this->add(21, 23, Util::formatCnab('9L', $pagamento->getCodigoBanco(), 3));
        // 024-028: Agencia mantenedora da conta do favorecido
        $this->add(24, 28, Util::formatCnab('9L', $pagamento->getAgencia(), 5));
        // 029-029: 0 (zero) ou branco
        $this->add(29, 29, '0');
        // 030-042: Numero da Conta Corrente do favorecido (13 digitos com DV)
        $contaFavorecido = Util::formatCnab('9L', $pagamento->getConta(), 12) . Util::formatCnab('X', $pagamento->getContaDv() ?? '0', 1);
        $this->add(30, 42, $contaFavorecido);
        // 043-043: 0 (zero) ou branco
        $this->add(43, 43, '0');
        // 044-073: Nome do Favorecido
        $this->add(44, 73, Util::formatCnab('X', $pagamento->getBeneficiario()->getNome(), 30));
        // 074-088: Seu Numero (numero do documento atribuido pela Empresa)
        $this->add(74, 88, Util::formatCnab('X', $pagamento->getNumeroControle(), 15));
        // 089-093: Finalidade do cliente para TED ou DOC
        $finalidade = $this->getFinalidadePagamento($pagamento);
        $this->add(89, 93, Util::formatCnab('9L', $finalidade, 5));
        // 094-101: Data do Credito (DDMMAAAA)
        $dataPagamento = $pagamento->getDataPagamento() ? $pagamento->getDataPagamento()->format('dmY') : date('dmY');
        $this->add(94, 101, Util::formatCnab('9L', $dataPagamento, 8));
        // 102-104: Tipo de moeda (BRL)
        $this->add(102, 104, self::TIPO_MOEDA);
        // 105-119: Zeros
        $this->add(105, 119, Util::formatCnab('9', '0', 15));
        // 120-134: Valor a ser creditado
        $valor = $pagamento->getValor() ? $pagamento->getValor() * 100 : 0;
        $this->add(120, 134, Util::formatCnab('9L', $valor, 15));
        // 135-154: Nosso Numero (preenchido no retorno)
        $this->add(135, 154, self::CAMPO_BRANCO);
        // 155-162: Data da efetivacao do Credito (retorno)
        $this->add(155, 162, Util::formatCnab('9', '0', 8));
        // 163-177: Valor do Credito efetuado (retorno)
        $this->add(163, 177, Util::formatCnab('9', '0', 15));
        // 178-182: Brancos
        $this->add(178, 182, self::CAMPO_BRANCO);
        // 183-202: CIT (Codigo Identificador de Transferencia) TED
        $this->add(183, 202, self::CAMPO_BRANCO);
        // 203-203: Tipo de Inscricao do favorecido (1=CPF, 2=CNPJ)
        $tipoDocFav = $pagamento->getBeneficiario()->getTipoDocumento() == 'CPF' ? self::TIPO_DOCUMENTO_CPF : self::TIPO_DOCUMENTO_CNPJ;
        $this->add(203, 203, $tipoDocFav);
        // 204-217: Numero de Inscricao (CPF ou CNPJ) do favorecido
        $this->add(204, 217, Util::formatCnab('9L', $pagamento->getBeneficiario()->getDocumento(), 14));
        // 218-219: Para TED em Conta Poupanca (11) ou brancos
        $this->add(218, 219, self::CAMPO_BRANCO);
        // 220-229: Brancos (Uso exclusivo Febraban/Cnab)
        $this->add(220, 229, self::CAMPO_BRANCO);
        // 230-230: 0 - nao emite aviso ao favorecido
        $this->add(230, 230, self::AVISO_FAVORECIDO_NAO);
        // 231-240: Codigos das ocorrencias de retorno
        $this->add(231, 240, self::CAMPO_BRANCO);

        return $this;
    }

    /**
     * Segmento B - Dados complementares do favorecido (DOC/TED)
     *
     * @param Banco $pagamento
     * @return $this
     * @throws \Exception
     */
    public function segmentoB($pagamento)
    {
        $this->iniciaDetalhe();

        // 001-003: Codigo do Banco na Compensacao (041)
        $this->add(1, 3, self::BANCO);
        // 004-007: Lote de Servico
        $this->add(4, 7, Util::formatCnab('9L', self::LOTE_SERVICO_HEADER, 4));
        // 008-008: Tipo de Registro (3 = Detalhe)
        $this->add(8, 8, self::TIPO_REGISTRO_DETALHE);
        // 009-013: Numero Sequencial do Registro no Lote
        $this->add(9, 13, Util::formatCnab('9L', $this->iRegistrosLote, 5));
        // 014-014: Codigo de Segmento (B)
        $this->add(14, 14, self::CODIGO_SEGMENTO_B);
        // 015-017: Uso exclusivo FEBRABAN/CNAB - Brancos
        $this->add(15, 17, self::CAMPO_BRANCO);
        // 018-018: Tipo de Inscricao (1=CPF, 2=CNPJ)
        $tipoDoc = $pagamento->getBeneficiario()->getTipoDocumento() == 'CPF' ? self::TIPO_DOCUMENTO_CPF : self::TIPO_DOCUMENTO_CNPJ;
        $this->add(18, 18, $tipoDoc);
        // 019-032: Numero de Inscricao do Favorecido
        $this->add(19, 32, Util::formatCnab('9L', $pagamento->getBeneficiario()->getDocumento(), 14));
        // 033-062: Nome da Rua, Av, Praca, Etc
        $this->add(33, 62, Util::formatCnab('X', $pagamento->getBeneficiario()->getEndereco(), 30));
        // 063-067: Numero do Local
        $this->add(63, 67, Util::formatCnab('9L', '', 5));
        // 068-082: Casa, Apto, Etc
        $this->add(68, 82, self::CAMPO_BRANCO);
        // 083-097: Bairro
        $this->add(83, 97, Util::formatCnab('X', $pagamento->getBeneficiario()->getBairro(), 15));
        // 098-117: Cidade do Favorecido
        $this->add(98, 117, Util::formatCnab('X', $pagamento->getBeneficiario()->getCidade(), 20));
        // 118-122: CEP (5 primeiros digitos)
        $cep = preg_replace('/[^0-9]/', '', $pagamento->getBeneficiario()->getCep());
        $this->add(118, 122, Util::formatCnab('9L', substr($cep, 0, 5), 5));
        // 123-125: Complemento do CEP (3 ultimos digitos)
        $this->add(123, 125, Util::formatCnab('X', substr($cep, 5, 3), 3));
        // 126-127: Sigla do Estado
        $this->add(126, 127, Util::formatCnab('X', $pagamento->getBeneficiario()->getUf(), 2));
        // 128-135: Data do Vencimento (DDMMAAAA)
        $dataVencimento = $pagamento->getDataVencimento() ? $pagamento->getDataVencimento()->format('dmY') : date('dmY');
        $this->add(128, 135, Util::formatCnab('9L', $dataVencimento, 8));
        // 136-150: Valor do Documento (Nominal)
        $valorDoc = $pagamento->getValor() ? $pagamento->getValor() * 100 : 0;
        $this->add(136, 150, Util::formatCnab('9L', $valorDoc, 15));
        // 151-165: Valor do Abatimento
        $this->add(151, 165, Util::formatCnab('9', '0', 15));
        // 166-180: Valor do Desconto
        $this->add(166, 180, Util::formatCnab('9', '0', 15));
        // 181-195: Valor da Mora
        $this->add(181, 195, Util::formatCnab('9', '0', 15));
        // 196-210: Valor da Multa
        $this->add(196, 210, Util::formatCnab('9', '0', 15));
        // 211-225: Codigo / Documento do Favorecido
        $this->add(211, 225, self::CAMPO_BRANCO);
        // 226-226: Aviso ao Favorecido (0 = Nao Emite)
        $this->add(226, 226, self::AVISO_FAVORECIDO_NAO);
        // 227-232: Uso Exclusivo para o SIAPE
        $this->add(227, 232, self::CAMPO_BRANCO);
        // 233-240: Codigo do ISPB do Banco Destinatario
        $this->add(233, 240, Util::formatCnab('9', '0', 8));

        return $this;
    }

    /**
     * Trailer de Lote - Registro Tipo '5'
     *
     * @return $this
     * @throws \Exception
     */
    protected function trailerLote()
    {
        $this->iniciaTrailerLote();

        // 001-003: Codigo do Banco na Compensacao (041)
        $this->add(1, 3, self::BANCO);
        // 004-007: Lote de Servico (igual ao header do lote)
        $this->add(4, 7, Util::formatCnab('9L', self::LOTE_SERVICO_HEADER, 4));
        // 008-008: Tipo de Registro (5 = Trailer de Lote)
        $this->add(8, 8, self::TIPO_REGISTRO_TRAILER_LOTE);
        // 009-017: Brancos
        $this->add(9, 17, self::CAMPO_BRANCO);
        // 018-023: Total de registros no lote (header + detalhes + trailer)
        $this->add(18, 23, Util::formatCnab('9L', $this->getCountRegistrosLote(), 6));
        // 024-041: Somatorio dos valores (soma dos valores dos registros tipo 3)
        $this->add(24, 41, Util::formatCnab('9L', $this->getValorTotalLote(), 18));
        // 042-059: 0 (zeros) - Somatorio de quantidade de moedas
        $this->add(42, 59, Util::formatCnab('9', '0', 18));
        // 060-230: Uso exclusivo Febraban/CNAB - Brancos
        $this->add(60, 230, self::CAMPO_BRANCO);
        // 231-240: Codigos de ocorrencia para retorno
        $this->add(231, 240, self::CAMPO_BRANCO);

        return $this;
    }

    /**
     * Trailer de Arquivo - Registro Tipo '9'
     *
     * @return $this
     * @throws \Exception
     */
    protected function trailer()
    {
        $this->iniciaTrailer();

        // 001-003: Codigo do Banco na Compensacao (041)
        $this->add(1, 3, self::BANCO);
        // 004-007: Lote de Servico (9999 = Constante)
        $this->add(4, 7, self::LOTE_SERVICO_TRAILER);
        // 008-008: Tipo de Registro (9 = Trailer de Arquivo)
        $this->add(8, 8, self::TIPO_REGISTRO_TRAILER);
        // 009-017: Brancos - Uso exclusivo Febraban/CNAB
        $this->add(9, 17, self::CAMPO_BRANCO);
        // 018-023: Quantidade do Total de Lotes no Arquivo
        $this->add(18, 23, Util::formatCnab('9L', 1, 6));
        // 024-029: Quantidade de Registros no Arquivo (0+1+3+5+9)
        $this->add(24, 29, Util::formatCnab('9L', $this->getCount(), 6));
        // 030-035: "000000" (zeros)
        $this->add(30, 35, Util::formatCnab('9', '0', 6));
        // 036-240: Brancos - Uso exclusivo Febraban/CNAB
        $this->add(36, 240, self::CAMPO_BRANCO);

        return $this;
    }

    /**
     * Adiciona um pagamento
     *
     * @param PagamentoContract $pagamento
     * @return $this
     */
    public function addPagamento(PagamentoContract $pagamento)
    {
        $this->pagamentos[] = $pagamento;
        return $this;
    }

    /**
     * Gera os segmentos de um pagamento
     *
     * @param Banco $pagamento
     * @return void
     */
    protected function gerarSegmentos(Banco $pagamento)
    {
        $this->segmentoA($pagamento);
        $this->segmentoB($pagamento);
    }

    /**
     * Header do lote para multiplos lotes
     *
     * @param array $lote
     * @return $this
     * @throws \Exception
     */
    protected function headerLoteMulti(array $lote)
    {
        $this->iniciaHeaderLote();

        // 001-003: Codigo do Banco na Compensacao (041)
        $this->add(1, 3, self::BANCO);
        // 004-007: Lote de Servico (sequencial)
        $this->add(4, 7, Util::formatCnab('9L', $lote['numero'], 4));
        // 008-008: Tipo de Registro (1 = Header de Lote)
        $this->add(8, 8, self::TIPO_REGISTRO_HEADER_LOTE);
        // 009-009: Tipo de Operacao (C = Credito)
        $this->add(9, 9, self::TIPO_OPERACAO);
        // 010-011: Tipo de Servico
        $this->add(10, 11, Util::formatCnab('9L', $this->getTipoServicoPorTipo($lote['tipo']), 2));
        // 012-013: Forma de Lancamento
        $this->add(12, 13, Util::formatCnab('9L', $this->getFormaLancamentoPorTipo($lote['tipo']), 2));
        // 014-016: Numero da versao do header do lote
        $this->add(14, 16, self::VERSAO_LAYOUT);
        // 017-017: Brancos
        $this->add(17, 17, self::CAMPO_BRANCO);
        // 018-018: Tipo de Inscricao da Empresa
        $this->add(18, 18, Util::formatCnab('9L', $this->getTipoInscricao(), 1));
        // 019-032: Numero de Inscricao da Empresa
        $this->add(19, 32, Util::formatCnab('9L', $this->getPagador()->getDocumento(), 14));
        // 033-037: Codigo do Convenio no Banco
        $this->add(33, 37, Util::formatCnab('9L', $this->getConvenio(), 5));
        // 038-052: Brancos
        $this->add(38, 52, self::CAMPO_BRANCO);
        // 053-057: Agencia Mantenedora da Conta (0AAAA)
        $this->add(53, 57, Util::formatCnab('9L', $this->getAgencia(), 5));
        // 058-061: Zeros
        $this->add(58, 61, Util::formatCnab('9', '0', 4));
        // 062-071: Numero da Conta Corrente
        $this->add(62, 71, Util::formatCnab('9L', $this->getConta(), 10));
        // 072-072: Branco
        $this->add(72, 72, self::CAMPO_BRANCO);
        // 073-102: Nome da Empresa
        $this->add(73, 102, Util::formatCnab('X', $this->getPagador()->getNome(), 30));
        // 103-142: Brancos
        $this->add(103, 142, self::CAMPO_BRANCO);
        // 143-172: Endereco
        $this->add(143, 172, Util::formatCnab('X', $this->getPagador()->getEndereco(), 30));
        // 173-177: Numero do local
        $this->add(173, 177, Util::formatCnab('9L', '', 5));
        // 178-192: Complemento
        $this->add(178, 192, self::CAMPO_BRANCO);
        // 193-212: Nome da Cidade
        $this->add(193, 212, Util::formatCnab('X', $this->getPagador()->getCidade(), 20));
        // 213-220: CEP
        $this->add(213, 220, Util::formatCnab('9L', $this->getPagador()->getCep(), 8));
        // 221-222: Sigla do Estado
        $this->add(221, 222, Util::formatCnab('X', $this->getPagador()->getUf(), 2));
        // 223-224: Classificacao da ordem dos registros
        $this->add(223, 224, self::CAMPO_BRANCO);
        // 225-230: Uso exclusivo da Febraban/Cnab
        $this->add(225, 230, self::CAMPO_BRANCO);
        // 231-240: Codigos de ocorrencias p/retorno
        $this->add(231, 240, self::CAMPO_BRANCO);

        return $this;
    }

    /**
     * Trailer do lote para multiplos lotes
     *
     * @param array $lote
     * @return $this
     * @throws \Exception
     */
    protected function trailerLoteMulti(array $lote)
    {
        $this->iniciaTrailerLote();

        // 001-003: Codigo do Banco na Compensacao (041)
        $this->add(1, 3, self::BANCO);
        // 004-007: Lote de Servico
        $this->add(4, 7, Util::formatCnab('9L', $lote['numero'], 4));
        // 008-008: Tipo de Registro (5 = Trailer de Lote)
        $this->add(8, 8, self::TIPO_REGISTRO_TRAILER_LOTE);
        // 009-017: Brancos
        $this->add(9, 17, self::CAMPO_BRANCO);
        // 018-023: Total de registros no lote
        $totalRegistros = count($lote['pagamentos']) * 2 + 2; // segmentos A+B por pagamento + header + trailer
        $this->add(18, 23, Util::formatCnab('9L', $totalRegistros, 6));
        // 024-041: Somatorio dos valores
        $this->add(24, 41, $this->getValorTotalLoteMulti($lote));
        // 042-059: 0 (zeros)
        $this->add(42, 59, Util::formatCnab('9', '0', 18));
        // 060-230: Brancos
        $this->add(60, 230, self::CAMPO_BRANCO);
        // 231-240: Codigos de ocorrencia para retorno
        $this->add(231, 240, self::CAMPO_BRANCO);

        return $this;
    }

    /**
     * Trailer do arquivo para multiplos lotes
     *
     * @return $this
     * @throws \Exception
     */
    protected function trailerMulti()
    {
        $this->iniciaTrailer();

        // 001-003: Codigo do Banco na Compensacao (041)
        $this->add(1, 3, self::BANCO);
        // 004-007: Lote de Servico (9999)
        $this->add(4, 7, self::LOTE_SERVICO_TRAILER);
        // 008-008: Tipo de Registro (9)
        $this->add(8, 8, self::TIPO_REGISTRO_TRAILER);
        // 009-017: Brancos
        $this->add(9, 17, self::CAMPO_BRANCO);
        // 018-023: Quantidade de Lotes do Arquivo
        $this->add(18, 23, Util::formatCnab('9L', $this->getCountLotes(), 6));
        // 024-029: Quantidade de Registros no Arquivo
        $this->add(24, 29, Util::formatCnab('9L', $this->getCountMulti(), 6));
        // 030-035: "000000" (zeros)
        $this->add(30, 35, Util::formatCnab('9', '0', 6));
        // 036-240: Brancos
        $this->add(36, 240, self::CAMPO_BRANCO);

        return $this;
    }

    /**
     * Retorna o tipo de inscricao da empresa (1=CPF, 2=CNPJ)
     *
     * @return string
     */
    protected function getTipoInscricao()
    {
        return $this->getPagador()->getTipoDocumento() == 'CPF' ? self::TIPO_DOCUMENTO_CPF : self::TIPO_DOCUMENTO_CNPJ;
    }

    /**
     * Retorna a finalidade do pagamento para TED/DOC
     *
     * @param Banco $pagamento
     * @return string
     */
    protected function getFinalidadePagamento(Banco $pagamento)
    {
        // Se o pagamento tem uma finalidade definida, usa ela
        if (method_exists($pagamento, 'getFinalidade') && !empty($pagamento->getFinalidade())) {
            return $pagamento->getFinalidade();
        }

        // Verifica se e mesma titularidade (mesma empresa pagadora e favorecido)
        $docPagador = $this->getPagador()->getDocumento();
        $docFavorecido = $pagamento->getBeneficiario()->getDocumento();
        if ($docPagador == $docFavorecido) {
            return '00110'; // TED Mesma Titularidade
        }

        // Padrao: Pagamento de Fornecedores (diferente titularidade)
        return '00005';
    }

    /**
     * Retorna a quantidade de registros no lote
     *
     * @return int
     */
    protected function getCountRegistrosLote()
    {
        // header lote + segmentos A e B por pagamento + trailer lote
        return count($this->pagamentos) * 2 + 2;
    }

    /**
     * Retorna o valor total do lote
     *
     * @return string
     */
    protected function getValorTotalLote()
    {
        $valorTotal = 0;
        foreach ($this->pagamentos as $pagamento) {
            if (method_exists($pagamento, 'getValor') && $pagamento->getValor() > 0) {
                $valorTotal += $pagamento->getValor();
            }
        }
        return Util::formatCnab('9L', $valorTotal * 100, 18);
    }

    /**
     * Retorna a quantidade total de registros para multiplos lotes
     *
     * @return int
     */
    protected function getCountMulti()
    {
        $totalRegistros = 0;
        foreach ($this->lotes as $lote) {
            $totalRegistros += count($lote['pagamentos']) * 2; // Segmento A + B
            $totalRegistros += 2; // Header + Trailer do lote
        }
        $totalRegistros += 2; // Header + Trailer do arquivo
        return $totalRegistros;
    }
}
