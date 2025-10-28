<?php

namespace Eduardokum\LaravelBoleto\Cnab\Pagamento\Cnab240\Banco;

use Eduardokum\LaravelBoleto\Cnab\Pagamento\Cnab240\AbstractPagamento;
use Eduardokum\LaravelBoleto\Contracts\Cnab\Pagamento as PagamentoRemessaContract;
use Eduardokum\LaravelBoleto\Contracts\Pagamento\Pagamento as PagamentoContract;
use Eduardokum\LaravelBoleto\Pagamento\Banco\Banco;
use Eduardokum\LaravelBoleto\Util;

/**
 * Class Caixa
 * @package Eduardokum\LaravelBoleto\Cnab\Pagamento\Cnab240\Banco
 */
class Caixa extends AbstractPagamento implements PagamentoRemessaContract
{
    const BANCO = '104'; // Código da Caixa Econômica Federal na compensação
    const LOTE_SERVICO = '0000'; // Lote de serviço (header do arquivo)
    const TIPO_REGISTRO = '0'; // Tipo de registro (header do arquivo)
    const TIPO_DOCUMENTO_CPF = '1'; // CPF
    const TIPO_DOCUMENTO_CNPJ = '2'; // CNPJ
    const CODIGO_REMESSA = '1'; // Código de remessa (1=REMESSA, 2=RETORNO)
    const VERSAO_LAYOUT = '080'; // Versão do layout do arquivo (conforme especificação)
    const NOME_BANCO = 'CAIXA'; // Nome do banco
    const AMBIENTE_TESTE = 'T'; // Ambiente de teste
    const AMBIENTE_PRODUCAO = 'P'; // Ambiente de produção
    const DENSIDADE_GRAVACAO = '01600'; // Densidade de gravação
    const CAMPO_BRANCO = '';

    // Constantes para trailer do arquivo
    const LOTE_SERVICO_TRAILER = '9999'; // Lote de serviço (trailer do arquivo)
    const TIPO_REGISTRO_TRAILER = '9'; // Tipo de registro (trailer do arquivo)

    // Constantes para header do lote
    const LOTE_SERVICO_HEADER = '0001'; // Lote de serviço (header do lote)
    const TIPO_REGISTRO_HEADER_LOTE = '1'; // Tipo de registro (header do lote)
    const TIPO_OPERACAO = 'C'; // Tipo da operação (Crédito)
    const FORMA_LANCAMENTO_TED = '41'; // Forma de lançamento (TED)
    const FORMA_LANCAMENTO_PIX = '45'; // Forma de lançamento (PIX)
    const VERSAO_LAYOUT_LOTE = '041'; // Versão do layout do lote

    // Constantes para tipos de compromisso
    const TIPO_COMPROMISSO_PAGAMENTO_FORNECEDOR = '01'; // Pagamento a Fornecedor
    const TIPO_COMPROMISSO_PAGAMENTO_SALARIOS = '02'; // Pagamento de Salários
    const TIPO_COMPROMISSO_AUTOPAGAMENTO = '03'; // Autopagamento
    const TIPO_COMPROMISSO_SALARIO_AMPLIACAO = '06'; // Salário Ampliação de Base
    const TIPO_COMPROMISSO_DEBITO_CONTA = '11'; // Débito em Conta

    // Constantes para câmaras de compensação
    const CAMARA_COMPENSACAO_TED = '018'; // Finalidade TED
    const CAMARA_COMPENSACAO_DOC_OP = '700'; // Finalidade DOC e OP
    const CAMARA_COMPENSACAO_CREDITO_CONTA = '000'; // Crédito em Conta e Guia de Depósito Judicial
    const CAMARA_COMPENSACAO_BOLETO_ISPB = '888'; // Boletos/ISPB

    // Constantes para tipos de moeda
    const MOEDA_REAL = 'BRL'; // Real
    const MOEDA_DOLAR = 'USD'; // Dólar Americano
    const MOEDA_UFR = 'UFR'; // UFIR
    const MOEDA_TRD = 'TRD'; // Taxa Referencial Diária

    // Constantes para tipos de conta TED
    const TIPO_CONTA_SEM_CONTA = '0'; // Sem conta
    const TIPO_CONTA_CORRENTE = '1'; // Conta corrente
    const TIPO_CONTA_POUPANCA = '2'; // Poupança

    // Constantes para indicadores de parcelamento
    const INDICADOR_PARCELAMENTO_DATA_FIXA = '1'; // Data Fixa
    const INDICADOR_PARCELAMENTO_PERIODICO = '2'; // Periódico
    const INDICADOR_PARCELAMENTO_DIA_UTIL = '3'; // Dia útil

    // Constantes para SEGMENTO B
    const CODIGO_SEGMENTO_B = 'B';
    const USO_FEBRABAN_SEGMENTO_B = '';
    const VALOR_ZERO_DOCUMENTO = '0000000000000';
    const VALOR_ZERO_ABATIMENTO = '0000000000000';
    const VALOR_ZERO_DESCONTO = '0000000000000';
    const VALOR_ZERO_MORA = '0000000000000';
    const VALOR_ZERO_MULTA = '0000000000000';
    const CODIGO_DOCUMENTO_FAVORECIDO_VAZIO = '';

    // Constantes para trailer do lote
    const LOTE_SERVICO_TRAILER_LOTE = '0001'; // Lote de serviço (trailer do lote)
    const TIPO_REGISTRO_TRAILER_LOTE = '5'; // Tipo de registro (trailer do lote)
    const USO_EXCLUSIVO_FEBRABAN_TRAILER = ''; // Uso exclusivo FEBRABAN (trailer)
    const SOMATORIA_QTDE_MOEDA_ZERO = '0000000000000'; // Somatório quantidade moeda (zeros)
    const NUMERO_AVISO_DEBITO_ZERO = '000000'; // Número aviso débito (zeros)
    const USO_EXCLUSIVO_FEBRABAN_2 = ''; // Uso exclusivo FEBRABAN-2
    const OCORRENCIAS_TRAILER = ''; // Ocorrências (espaços)

    // Constantes para trailer do arquivo
    const LOTE_SERVICO_TRAILER_ARQUIVO = '9999'; // Lote de serviço (trailer do arquivo)
    const TIPO_REGISTRO_TRAILER_ARQUIVO = '9'; // Tipo de registro (trailer do arquivo)
    const USO_EXCLUSIVO_FEBRABAN_ARQUIVO = ''; // Uso exclusivo FEBRABAN (arquivo)
    const QTDE_CONTAS_CONCILIACAO_ZERO = '000000'; // Quantidade de contas para conciliação (zeros)
    const USO_EXCLUSIVO_FEBRABAN_ARQUIVO_2 = ''; // Uso exclusivo FEBRABAN-2 (arquivo)

    /**
     * Código do banco
     *
     * @var string
     */
    protected $codigoBanco = self::BANCO;

    /**
     * Define as carteiras disponíveis para cada banco
     *
     * @var array
     */
    protected $carteiras = [];

    /**
     * Tipo de pagamento (TED, DOC, PIX, etc.)
     * @var string
     */
    protected $tipoPagamento = 'TED';

    /**
     * Caracter de fim de linha
     * @var string
     */
    protected $fimLinha = "\r\n";

    /**
     * Caracter de fim de arquivo
     * @var null
     */
    protected $fimArquivo = "\r\n";

    /**
     * Código do convênio
     * @var string|null
     */
    protected $convenio;

    /**
     * Tipo de serviço
     * @var string
     */
    protected $tipoServico = '20';

    /**
     * Parâmetro de transmissão (campo 0.08)
     * @var string|null
     */
    protected $parametroTransmissao;

    public function __construct(array $params = [])
    {
        parent::__construct($params);
        $this->setCamposObrigatorios('convenio');

        // Configurar tipo de serviço se fornecido
        if (isset($params['tipoServico']))
            $this->tipoServico = $params['tipoServico'];

        // Configurar tipo de pagamento se fornecido
        if (isset($params['tipoPagamento']))
            $this->tipoPagamento = $params['tipoPagamento'];
    }

    /**
     * Cria o header do arquivo CNAB 240 conforme especificação da Caixa Econômica Federal
     * @return Caixa
     * @throws \Exception
     */
    protected function header()
    {
        $this->iniciaHeader();

        $this->add(1, 3, self::BANCO); // 0.01 - Código do Banco (001-003)
        $this->add(4, 7, self::LOTE_SERVICO); // 0.02 - Lote de serviço (004-007)
        $this->add(8, 8, self::TIPO_REGISTRO); // 0.03 - Código de Registro (008-008)
        $this->add(9, 17, self::CAMPO_BRANCO); // 0.04 - Filler (009-017) - preencher com espaços
        $this->add(18, 18, Util::formatCnab('9L', $this->getPagador()->getTipoDocumento() == 'CPF' ? self::TIPO_DOCUMENTO_CPF : self::TIPO_DOCUMENTO_CNPJ, 1)); // 0.05 - Tipo de inscrição (018-018)
        $this->add(19, 32, Util::formatCnab('9L', $this->getPagador()->getDocumento(), 14)); // 0.06 - Número de inscrição (019-032)
        $this->add(33, 38, Util::formatCnab('9L', $this->getConvenio(), 6)); // 0.07 - Código convênio no Banco (033-038)
        $this->add(39, 40, Util::formatCnab('9L', $this->getParametroTransmissao() ?: 0, 2)); // 0.08 - Parâmetro de transmissão (039-040)
        $this->add(41, 41, $this->getAmbiente() == 'teste' ? self::AMBIENTE_TESTE : self::AMBIENTE_PRODUCAO); // 0.09 - Ambiente Cliente (041-041)
        $this->add(42, 42, self::CAMPO_BRANCO); // 0.10 - Ambiente CAIXA (042-042)
        $this->add(43, 45, Util::formatCnab('X', '', 3)); // 0.11 - Origem Aplicativo (043-045)
        $this->add(46, 49, Util::formatCnab('9L', 0, 4)); // 0.12 - Número de versão (046-049)
        $this->add(50, 52, Util::formatCnab('X', '', 3)); // 0.13 - Filler (050-052)
        $this->add(53, 57, Util::formatCnab('9L', $this->getAgencia(), 5)); // 0.14* - Agência da conta corrente (053-057)
        $this->add(58, 58, $this->getAgenciaDv()); // 0.15* - DV da Agência (058-058)
        $this->add(59, 70, Util::formatCnab('9L', $this->getConta(), 12)); // 0.16* - Número da conta corrente (059-070)
        $this->add(71, 71, Util::formatCnab('X', $this->getContaDv(), 1)); // 0.17* - DV da conta (071-071)
        $this->add(72, 72, self::CAMPO_BRANCO); // 0.18* - DV da Agência/Conta (072-072)
        $this->add(73, 102, Util::formatCnab('X', $this->getPagador()->getNome(), 30)); // 0.19 - Nome da Empresa (073-102)
        $this->add(103, 132, Util::formatCnab('X', self::NOME_BANCO, 30)); // 0.20 - Nome do Banco (103-132)
        $this->add(133, 142, Util::formatCnab('X', '', 10)); // 0.21 - Filler (133-142)
        $this->add(143, 143, self::CODIGO_REMESSA); // 0.22 - Tipo do arquivo (143-143)
        $this->add(144, 151, $this->getDataRemessa('dmY')); // 0.23 - Data geração do arquivo (144-151)
        $this->add(152, 157, date('His')); // 0.24 - Hora de geração do arquivo (152-157)
        $this->add(158, 163, Util::formatCnab('9L', $this->getIdremessa(), 6)); // 0.25 - NSA (158-163)
        $this->add(164, 166, self::VERSAO_LAYOUT); // 0.26 - Versão do leiaute (164-166)
        $this->add(167, 171, self::DENSIDADE_GRAVACAO); // 0.27 - Densidade de gravação (167-171)
        $this->add(172, 191, Util::formatCnab('X', $this->isExclusivoPix() ? 'PIX' : '', 20)); // 0.28 - Exclusivo Pix (172-191)
        $this->add(192, 211, Util::formatCnab('X', '', 20)); // 0.29 - Reservado para a empresa (192-211)
        $this->add(212, 222, Util::formatCnab('X', '', 11)); // 0.30 - Uso exclusivo FEBRABAN (212-222)
        $this->add(223, 225, Util::formatCnab('X', '', 3)); // 0.31 - Ident. Cobrança (223-225)
        $this->add(226, 228, '000'); // 0.32 - Uso exclusivo das VAN (226-228)
        $this->add(229, 230, Util::formatCnab('X', '', 2)); // 0.33 - Tipo de serviço (229-230)
        $this->add(231, 240, Util::formatCnab('X', '', 10)); // 0.34 - Ocorrência Cob. Sem papel (231-240)

        return $this;
    }

    /**
     * Verifica se é arquivo exclusivo para PIX
     * @return bool
     */
    protected function isExclusivoPix()
    {
        // Implementar lógica para verificar se é exclusivo PIX
        return false;
    }

    /**
     * Retorna o número sequencial do arquivo
     * @return int
     */
    /**
     * Retorna o código do convênio
     * @return string|null
     */
    protected function getConvenio()
    {
        return $this->convenio ?? null;
    }

    /**
     * Define o código do convênio
     * @param string $convenio
     * @return $this
     */
    public function setConvenio($convenio)
    {
        $this->convenio = $convenio;
        return $this;
    }

    /**
     * Retorna o ambiente (teste ou produção)
     * @return string
     */
    protected function getAmbiente()
    {
        // Implementar lógica para determinar ambiente
        return 'producao';
    }

    /**
     * Define o parâmetro de transmissão (campo 0.08)
     * @param string $parametro
     * @return $this
     */
    public function setParametroTransmissao($parametro)
    {
        $this->parametroTransmissao = $parametro;
        return $this;
    }

    /**
     * Retorna o parâmetro de transmissão (campo 0.08)
     * @return string|null
     */
    protected function getParametroTransmissao()
    {
        return $this->parametroTransmissao;
    }

    /**
     * Função para gerar o cabeçalho do lote.
     *
     * @return Caixa
     * @throws \Exception
     */
    protected function headerLote()
    {
        $this->iniciaHeaderLote();

        // Tabela do manual (HEADER DE LOTE "1")
        $this->add(1, 3, self::BANCO); // 1.01 Código do Banco
        $this->add(4, 7, self::LOTE_SERVICO_HEADER); // 1.02 Lote de Serviço
        $this->add(8, 8, self::TIPO_REGISTRO_HEADER_LOTE); // 1.03 Código do Registro = 1
        $this->add(9, 9, self::TIPO_OPERACAO); // 1.04 Tipo de Operação (C/D)
        $this->add(10, 11, $this->getTipoServico()); // 1.05 Tipo de Serviço (G025)
        $this->add(12, 13, $this->getFormaLancamento()); // 1.06 Forma de Lançamento (G029)
        $this->add(14, 16, self::VERSAO_LAYOUT_LOTE); // 1.07 Versão do leiaute do lote = 041
        $this->add(17, 17, self::CAMPO_BRANCO); // 1.08 Filler (espaço)
        $this->add(18, 18, Util::formatCnab('9L', $this->getPagador()->getTipoDocumento() == 'CPF' ? self::TIPO_DOCUMENTO_CPF : self::TIPO_DOCUMENTO_CNPJ, 1)); // 1.09 Tipo de inscrição
        $this->add(19, 32, Util::formatCnab('9L', $this->getPagador()->getDocumento(), 14)); // 1.10 Número de inscrição
        $this->add(33, 38, Util::formatCnab('9L', $this->getConvenio(), 6)); // 1.11 Código Convênio no Banco
        $this->add(39, 40, self::TIPO_COMPROMISSO_PAGAMENTO_FORNECEDOR); // 1.12 Tipo de Compromisso
        $this->add(41, 44, '0000'); // 1.13 Código do Compromisso
        $this->add(45, 46, Util::formatCnab('9L', $this->getParametroTransmissao() ?: 0, 2)); // 1.14 Parâmetro de Transmissão
        $this->add(47, 52, Util::formatCnab('X', '', 6)); // 1.15 Filler (espaços)
        $this->add(53, 57, Util::formatCnab('9L', $this->getAgencia(), 5)); // 1.16* Agência da Conta Corrente
        $this->add(58, 58, Util::formatCnab('X', $this->getAgenciaDv(), 1)); // 1.17* DV da Agência (opcional)
        $this->add(59, 70, Util::formatCnab('9L', $this->getConta(), 12)); // 1.18* Número da Conta Corrente
        $this->add(71, 71, Util::formatCnab('X', $this->getContaDv(), 1)); // 1.19* DV da Conta Corrente
        $this->add(72, 72, self::CAMPO_BRANCO); // 1.20* Dígito da Agência/Conta (espaço)
        $this->add(73, 102, Util::formatCnab('X', $this->getPagador()->getNome(), 30)); // 1.21 Nome da Empresa
        $this->add(103, 142, Util::formatCnab('X', '', 40)); // 1.22 Mensagem de Aviso 1
        $this->add(143, 172, Util::formatCnab('X', $this->getPagador()->getEndereco(), 30)); // 1.23 Logradouro
        $this->add(173, 177, Util::formatCnab('9L', 0, 5)); // 1.24 Número no local
        $this->add(178, 192, Util::formatCnab('X', '', 15)); // 1.25 Complemento
        $this->add(193, 212, Util::formatCnab('X', $this->getPagador()->getCidade(), 20)); // 1.26 Cidade
        $cep = Util::formatCnab('9L', $this->getPagador()->getCep(), 8);
        $this->add(213, 217, substr($cep, 0, 5)); // 1.27 CEP
        $this->add(218, 220, substr($cep, 5, 3)); // 1.28 Complemento CEP
        $this->add(221, 222, Util::formatCnab('X', $this->getPagador()->getUf(), 2)); // 1.29 Sigla do Estado
        $this->add(223, 230, Util::formatCnab('X', '', 8)); // 1.30 Uso exclusivo FEBRABAN
        $this->add(231, 240, Util::formatCnab('X', '', 10)); // 1.31 Ocorrências

        return $this;
    }

    /**
     * Função que gera o trailer (footer) do lote.
     *
     * @return Caixa
     * @throws \Exception
     */
    protected function trailerLote()
    {
        $this->iniciaTrailerLote();

        // Somatório dos valores do lote
        $valorTotal = array_reduce($this->pagamentos, function ($acum, $pag) {
            return $acum + (method_exists($pag, 'getValor') ? $pag->getValor() : 0);
        }, 0);

        // Quantidade de registros no lote (inclui header e trailer do lote)
        $quantidadeRegistros = (count($this->pagamentos) * 2) + 2; // segmentos A e B por pagamento + header + trailer

        $this->add(1, 3, self::BANCO); // 5.01 Código do Banco
        $this->add(4, 7, self::LOTE_SERVICO_TRAILER_LOTE); // 5.02 Lote de Serviço (mesmo do header de lote)
        $this->add(8, 8, self::TIPO_REGISTRO_TRAILER_LOTE); // 5.03 Código do registro = 5
        $this->add(9, 17, Util::formatCnab('X', '', 9)); // 5.04 Uso exclusivo FEBRABAN (espaços)
        $this->add(18, 23, Util::formatCnab('9L', $quantidadeRegistros, 6)); // 5.05 Quantidade de Registros no Lote
        $this->add(24, 41, Util::formatCnab('9L', $valorTotal, 18)); // 5.06 Somatória dos Valores
        $this->add(42, 59, Util::formatCnab('9L', 0, 18)); // 5.07 Somatório Qtde Moeda (zeros)
        $this->add(60, 65, Util::formatCnab('9L', 0, 6)); // 5.08 Número Aviso Débito (zeros)
        $this->add(66, 230, Util::formatCnab('X', '', 165)); // 5.09 Uso exclusivo FEBRABAN-2 (espaços)
        $this->add(231, 240, Util::formatCnab('X', '', 10)); // 5.10 Ocorrências (espaços)

        return $this;
    }

    /**
     * Função que gera o trailer (footer) do arquivo.
     *
     * @return Caixa
     * @throws \Exception
     */
    protected function trailer()
    {
        $this->iniciaTrailer();

        // Quantidades considerando suporte a múltiplos lotes (fallback para 1 lote)
        $qtdLotes = $this->getCountLotes() ?: 1;
        if (!empty($this->lotes)) {
            $qtdRegistrosArquivo = $this->getCountMulti();
        } else {
            $qtdRegistrosArquivo = 4 + (count($this->pagamentos) * 2); // header arq + header lote + (A/B por pagamento) + trailer lote + trailer arq
        }

        $this->add(1, 3, self::BANCO); // 9.01 Código do Banco
        $this->add(4, 7, self::LOTE_SERVICO_TRAILER_ARQUIVO); // 9.02 Lote de Serviço = 9999
        $this->add(8, 8, self::TIPO_REGISTRO_TRAILER_ARQUIVO); // 9.03 Código do registro = 9
        $this->add(9, 17, Util::formatCnab('X', '', 9)); // 9.04 Uso exclusivo FEBRABAN (espaços)
        $this->add(18, 23, Util::formatCnab('9L', $qtdLotes, 6)); // 9.05 Quantidade de Lotes no Arquivo
        $this->add(24, 29, Util::formatCnab('9L', $qtdRegistrosArquivo, 6)); // 9.06 Quantidade de Registros do Arquivo
        $this->add(30, 35, Util::formatCnab('9L', 0, 6)); // 9.07 Quantidade de Contas para Conciliação (zeros)
        $this->add(36, 240, Util::formatCnab('X', '', 205)); // 9.08 Uso exclusivo FEBRABAN (espaços)

        return $this;
    }

    /**
     * Header do lote para múltiplos lotes
     *
     * @param array $lote { numero:int, pagamentos:PagamentoContract[] }
     * @return Caixa
     * @throws \Exception
     */
    protected function headerLoteMulti(array $lote)
    {
        $this->iniciaHeaderLote();

        $this->add(1, 3, self::BANCO); // 1.01
        $this->add(4, 7, Util::formatCnab('9L', $lote['numero'], 4)); // 1.02 número do lote
        $this->add(8, 8, self::TIPO_REGISTRO_HEADER_LOTE); // 1.03
        $this->add(9, 9, self::TIPO_OPERACAO); // 1.04
        $this->add(10, 11, $this->getTipoServico()); // 1.05
        $this->add(12, 13, $this->getFormaLancamentoPorTipo($lote['tipo'])); // 1.06
        $this->add(14, 16, self::VERSAO_LAYOUT_LOTE); // 1.07
        $this->add(17, 17, self::CAMPO_BRANCO); // 1.08
        $this->add(18, 18, Util::formatCnab('9L', $this->getPagador()->getTipoDocumento() == 'CPF' ? self::TIPO_DOCUMENTO_CPF : self::TIPO_DOCUMENTO_CNPJ, 1)); // 1.09
        $this->add(19, 32, Util::formatCnab('9L', $this->getPagador()->getDocumento(), 14)); // 1.10
        $this->add(33, 38, Util::formatCnab('9L', $this->getConvenio(), 6)); // 1.11
        $this->add(39, 40, self::TIPO_COMPROMISSO_PAGAMENTO_FORNECEDOR); // 1.12
        $this->add(41, 44, '0000'); // 1.13
        $this->add(45, 46, Util::formatCnab('9L', $this->getParametroTransmissao() ?: 0, 2)); // 1.14
        $this->add(47, 52, Util::formatCnab('X', '', 6)); // 1.15
        $this->add(53, 57, Util::formatCnab('9L', $this->getAgencia(), 5)); // 1.16
        $this->add(58, 58, Util::formatCnab('X', $this->getAgenciaDv(), 1)); // 1.17
        $this->add(59, 70, Util::formatCnab('9L', $this->getConta(), 12)); // 1.18
        $this->add(71, 71, Util::formatCnab('X', $this->getContaDv(), 1)); // 1.19
        $this->add(72, 72, self::CAMPO_BRANCO); // 1.20
        $this->add(73, 102, Util::formatCnab('X', $this->getPagador()->getNome(), 30)); // 1.21
        $this->add(103, 142, Util::formatCnab('X', '', 40)); // 1.22
        $this->add(143, 172, Util::formatCnab('X', $this->getPagador()->getEndereco(), 30)); // 1.23
        $this->add(173, 177, Util::formatCnab('9L', 0, 5)); // 1.24
        $this->add(178, 192, Util::formatCnab('X', '', 15)); // 1.25
        $this->add(193, 212, Util::formatCnab('X', $this->getPagador()->getCidade(), 20)); // 1.26
        $cep = Util::formatCnab('9L', $this->getPagador()->getCep(), 8);
        $this->add(213, 217, substr($cep, 0, 5)); // 1.27
        $this->add(218, 220, substr($cep, 5, 3)); // 1.28
        $this->add(221, 222, Util::formatCnab('X', $this->getPagador()->getUf(), 2)); // 1.29
        $this->add(223, 230, Util::formatCnab('X', '', 8)); // 1.30
        $this->add(231, 240, Util::formatCnab('X', '', 10)); // 1.31

        return $this;
    }

    /**
     * Trailer do lote para múltiplos lotes
     *
     * @param array $lote
     * @return Caixa
     * @throws \Exception
     */
    protected function trailerLoteMulti(array $lote)
    {
        $this->iniciaTrailerLote();

        // Quantidade de registros neste lote
        $qtdRegistros = (count($lote['pagamentos']) * 2) + 2;
        $valorTotal = $this->getValorTotalLoteMulti($lote);

        $this->add(1, 3, self::BANCO); // 5.01
        $this->add(4, 7, Util::formatCnab('9L', $lote['numero'], 4)); // 5.02
        $this->add(8, 8, self::TIPO_REGISTRO_TRAILER_LOTE); // 5.03
        $this->add(9, 17, Util::formatCnab('X', '', 9)); // 5.04
        $this->add(18, 23, Util::formatCnab('9L', $qtdRegistros, 6)); // 5.05
        $this->add(24, 41, Util::formatCnab('9L', $valorTotal, 18)); // 5.06
        $this->add(42, 59, Util::formatCnab('9L', 0, 18)); // 5.07
        $this->add(60, 65, Util::formatCnab('9L', 0, 6)); // 5.08
        $this->add(66, 230, Util::formatCnab('X', '', 165)); // 5.09
        $this->add(231, 240, Util::formatCnab('X', '', 10)); // 5.10

        return $this;
    }

    /**
     * Trailer do arquivo para múltiplos lotes
     * @return Caixa
     * @throws \Exception
     */
    protected function trailerMulti()
    {
        $this->iniciaTrailer();

        $this->add(1, 3, self::BANCO); // 9.01
        $this->add(4, 7, self::LOTE_SERVICO_TRAILER_ARQUIVO); // 9.02 = 9999
        $this->add(8, 8, self::TIPO_REGISTRO_TRAILER_ARQUIVO); // 9.03 = 9
        $this->add(9, 17, Util::formatCnab('X', '', 9)); // 9.04 brancos
        $this->add(18, 23, Util::formatCnab('9L', $this->getCountLotes(), 6)); // 9.05 Qtde de lotes
        $this->add(24, 29, Util::formatCnab('9L', $this->getCountMulti(), 6)); // 9.06 Qtde de registros
        $this->add(30, 35, Util::formatCnab('9L', 0, 6)); // 9.07 Qtde contas conciliação (zeros)
        $this->add(36, 240, Util::formatCnab('X', '', 205)); // 9.08 brancos

        return $this;
    }

    // --------- Métodos auxiliares para múltiplos lotes ---------

    protected function getCountLotes()
    {
        return !empty($this->lotes) ? count($this->lotes) : 0;
    }

    protected function getCountMulti()
    {
        if (empty($this->lotes)) {
            return 0;
        }
        $totalRegistros = 0;
        foreach ($this->lotes as $lote) {
            $totalRegistros += (count($lote['pagamentos']) * 2) + 2; // header + trailer do lote
        }
        $totalRegistros += 2; // header e trailer do arquivo
        return $totalRegistros;
    }

    protected function getValorTotalLote()
    {
        $valorTotal = 0;
        foreach ($this->pagamentos as $pagamento) {
            if (method_exists($pagamento, 'getValor')) {
                $valorTotal += $pagamento->getValor();
            }
        }
        return $valorTotal;
    }

    protected function getValorTotalLoteMulti(array $lote)
    {
        $valorTotal = 0;
        foreach ($lote['pagamentos'] as $pagamento) {
            if (method_exists($pagamento, 'getValor')) {
                $valorTotal += $pagamento->getValor();
            }
        }
        return $valorTotal;
    }


    /**
     * Retorna o tipo de pagamento
     * @return string
     */
    protected function getTipoPagamento()
    {
        return $this->tipoPagamento ?? 'TED';
    }


    /**
     * Retorna o tipo de serviço
     * @return string
     */
    protected function getTipoServico()
    {
        return $this->tipoServico ?? '20'; // Padrão para Caixa Econômica Federal
    }


    /**
     * Retorna a forma de lançamento baseada no tipo de pagamento
     * @return string
     */
    protected function getFormaLancamento()
    {
        $tipoPagamento = $this->getTipoPagamento();

        switch ($tipoPagamento) {
            case 'TED':
                return '41'; // TED
            default:
                return '41'; // Padrão TED
        }
    }


    /**
     * Retorna a forma de lançamento baseada no tipo de pagamento específico
     *
     * @param string $tipoPagamento
     * @return string
     */
    protected function getFormaLancamentoPorTipo($tipoPagamento)
    {
        switch ($tipoPagamento) {
            case 'TED':
                return '41'; // TED
            default:
                return '41'; // Padrão TED
        }
    }


    /**
     * Função para adicionar detalhe ao arquivo.
     *
     * @param PagamentoContract $pagamento
     *
     * @return Caixa
     * @throws \Exception
     */
    public function addPagamento(PagamentoContract $pagamento)
    {
        $this->pagamentos[] = $pagamento;
        return $this;
    }

    /**
     * Gera os segmentos de um pagamento baseado no tipo
     *
     * @param \Eduardokum\LaravelBoleto\Pagamento\Banco\Banco $pagamento
     * @return void
     * @throws \Exception
     */
    protected function gerarSegmentos(\Eduardokum\LaravelBoleto\Pagamento\Banco\Banco $pagamento)
    {
        $tipoPagamento = $this->getTipoPagamentoDoPagamento($pagamento);

        // Caixa Econômica Federal suporta apenas TED via CNAB240
        $this->segmentoA($pagamento);
        $this->segmentoB($pagamento);
    }

    /**
     * Adiciona um segmento A para TED
     *
     * @param \Eduardokum\LaravelBoleto\Pagamento\Banco\Banco $pagamento
     * @return $this
     * @throws \Exception
     */
    public function segmentoA(\Eduardokum\LaravelBoleto\Pagamento\Banco\Banco $pagamento)
    {
        $this->iniciaDetalhe();

        // De/Até exatamente conforme a tabela do manual
        $this->add(1, 3, self::BANCO); // A.01 Código do Banco
        $this->add(4, 7, self::LOTE_SERVICO_HEADER); // A.02 Lote de Serviço (mesmo do header de lote)
        $this->add(8, 8, '3'); // A.03 Código do Registro
        $this->add(9, 13, Util::formatCnab('9L', $this->iRegistrosLote, 5)); // A.04 NSR
        $this->add(14, 14, 'A'); // A.05 Código do Segmento
        $this->add(15, 15, '0'); // A.06 Tipo Movimento (0 inclusão)
        $this->add(16, 17, '00'); // A.07 Código Instrução Movimento
        $this->add(18, 20, self::CAMARA_COMPENSACAO_TED); // A.08 Câmara de Compensação (padrão TED 018)

        // Dados do favorecido (destino)
        $this->add(21, 23, Util::formatCnab('9L', $pagamento->getCodigoBanco(), 3)); // A.09 Código Banco Destino
        $this->add(24, 28, Util::formatCnab('9L', $pagamento->getAgencia(), 5)); // A.10 Código Agência Destino
        $this->add(29, 29, Util::formatCnab('X', $pagamento->getAgenciaDv(), 1)); // A.11 DV Agência Destino
        $this->add(30, 41, Util::formatCnab('9L', $pagamento->getConta(), 12)); // A.12 Conta Corrente Destino
        $this->add(42, 42, Util::formatCnab('X', $pagamento->getContaDv(), 1)); // A.13 DV Conta Destino
        $this->add(43, 43, self::CAMPO_BRANCO); // A.14 DV Agência/Conta Destino

        $this->add(44, 73, Util::formatCnab('X', $pagamento->getBeneficiario()->getNome(), 30)); // A.15 Nome do Terceiro
        $this->add(74, 79, Util::formatCnab('9L', $pagamento->getNumeroDocumento(), 6)); // A.16 Nº Documento atribuído pela Empresa
        $this->add(80, 92, Util::formatCnab('X', '', 13)); // A.17 Filler
        $this->add(93, 93, self::TIPO_CONTA_CORRENTE); // A.18 Tipo de conta – Finalidade TED
        $this->add(94, 101, $pagamento->getDataVencimento()->format('dmY')); // A.19 Data Vencimento
        $this->add(102, 104, self::MOEDA_REAL); // A.20 Tipo de moeda
        $this->add(105, 119, Util::formatCnab('9L', 0, 15)); // A.21 Quantidade de moeda (zeros para BRL)
        $this->add(120, 134, Util::formatCnab('9L', $pagamento->getValor(), 15)); // A.22 Valor Lançamento
        $this->add(135, 143, Util::formatCnab('X', '', 9)); // A.23 Número Documento Banco (brancos)
        $this->add(144, 146, Util::formatCnab('X', '', 3)); // A.24 Filler
        $this->add(147, 148, '01'); // A.25 Quantidade de Parcelas (01=única)
        $this->add(149, 149, 'N'); // A.26 Indicador de Bloqueio
        $this->add(150, 150, self::INDICADOR_PARCELAMENTO_DATA_FIXA); // A.27 Indicador Forma de Parcelamento
        $this->add(151, 152, Util::formatCnab('9L', 0, 2)); // A.28 Período/dia de vencimento
        $this->add(153, 154, Util::formatCnab('9L', 0, 2)); // A.29 Número Parcela
        $this->add(155, 162, Util::formatCnab('9L', 0, 8)); // A.30 Data da efetivação (zeros na remessa)
        $this->add(163, 177, Util::formatCnab('9L', 0, 15)); // A.31 Valor Real Efetivado (zeros)
        $this->add(178, 217, Util::formatCnab('X', '', 40)); // A.32 Informação 2
        $this->add(218, 219, '00'); // A.33 Finalidade DOC
        $this->add(220, 229, Util::formatCnab('X', '', 10)); // A.34 Uso FEBRABAN
        $this->add(230, 230, '0'); // A.35 Aviso ao Favorecido
        $this->add(231, 240, Util::formatCnab('X', '', 10)); // A.36 Ocorrências

        return $this;
    }

    /**
     * Adiciona um segmento B para TED
     *
     * @param \Eduardokum\LaravelBoleto\Pagamento\Banco\Banco $pagamento
     * @return $this
     * @throws \Exception
     */
    public function segmentoB(\Eduardokum\LaravelBoleto\Pagamento\Banco\Banco $pagamento)
    {
        $this->iniciaDetalhe();

        $this->add(1, 3, self::BANCO); // B.01 - Código do Banco (001-003)
        $this->add(4, 7, self::LOTE_SERVICO_HEADER); // B.02 - Lote de serviço (004-007)
        $this->add(8, 8, '3'); // B.03 - Código do Registro (008-008)
        $this->add(9, 13, Util::formatCnab('9L', $this->iRegistrosLote, 5)); // B.04 - NSR - Número sequencial do registro
        $this->add(14, 14, self::CODIGO_SEGMENTO_B); // B.05 - Código do segmento (014-014)
        $this->add(15, 17, Util::formatCnab('X', '', 3)); // B.06 - Uso FEBRABAN (015-017) - espaços
        $this->add(18, 18, Util::formatCnab('9L', $pagamento->getBeneficiario()->getTipoDocumento() == 'CPF' ? self::TIPO_DOCUMENTO_CPF : self::TIPO_DOCUMENTO_CNPJ, 1)); // B.07 - Tipo de inscrição (018-018)
        $this->add(19, 32, Util::formatCnab('9L', $pagamento->getBeneficiario()->getDocumento(), 14)); // B.08 - Número de inscrição (019-032)
        $this->add(33, 62, Util::formatCnab('X', $pagamento->getBeneficiario()->getEndereco(), 30)); // B.09 - Logradouro (033-062)
        $this->add(63, 67, Util::formatCnab('9L', 0, 5)); // B.10 - Número no local (063-067)
        $this->add(68, 82, Util::formatCnab('X', '', 15)); // B.11 - Complemento (068-082)
        $this->add(83, 97, Util::formatCnab('X', $pagamento->getBeneficiario()->getBairro(), 15)); // B.12 - Bairro (083-097)
        $this->add(98, 117, Util::formatCnab('X', $pagamento->getBeneficiario()->getCidade(), 20)); // B.13 - Cidade (098-117)
        $cepBenef = Util::formatCnab('9L', $pagamento->getBeneficiario()->getCep(), 8);
        $this->add(118, 122, substr($cepBenef, 0, 5)); // B.14 - CEP (118-122)
        $this->add(123, 125, substr($cepBenef, 5, 3)); // B.15 - Complemento CEP (123-125)
        $this->add(126, 127, Util::formatCnab('X', $pagamento->getBeneficiario()->getUf(), 2)); // B.16 - Sigla do Estado (126-127)
        $this->add(128, 135, $pagamento->getDataVencimento()->format('dmY')); // B.17 - Data de vencimento (128-135) - DDMMAAAA
        $this->add(136, 150, Util::formatCnab('9L', 0, 15)); // B.18 - Valor do Documento (136-150) - zeros
        $this->add(151, 165, Util::formatCnab('9L', 0, 15)); // B.19 - Valor do Abatimento (151-165) - zeros
        $this->add(166, 180, Util::formatCnab('9L', 0, 15)); // B.20 - Valor do Desconto (166-180) - zeros
        $this->add(181, 195, Util::formatCnab('9L', 0, 15)); // B.21 - Valor da Mora (181-195) - zeros
        $this->add(196, 210, Util::formatCnab('9L', 0, 15)); // B.22 - Valor da Multa (196-210) - zeros
        $this->add(211, 225, Util::formatCnab('X', '', 15)); // B.23 - Código Documento Favorecido (211-225)
        $this->add(226, 240, Util::formatCnab('X', '', 15)); // B.24 - Uso da FEBRABAN (226-240)

        return $this;
    }



    /**
     * Retorna o tipo de pagamento baseado no pagamento
     * Caixa Econômica Federal suporta apenas TED via CNAB240
     *
     * @param \Eduardokum\LaravelBoleto\Pagamento\Banco\Banco $pagamento
     * @return string
     */
    protected function getTipoPagamentoDoPagamento(\Eduardokum\LaravelBoleto\Pagamento\Banco\Banco $pagamento)
    {
        // Caixa Econômica Federal suporta apenas TED via CNAB240
        return 'TED';
    }
}
