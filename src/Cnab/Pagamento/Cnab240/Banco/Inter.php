<?php

namespace Eduardokum\LaravelBoleto\Cnab\Pagamento\Cnab240\Banco;

use Eduardokum\LaravelBoleto\Cnab\Pagamento\Cnab240\AbstractPagamento;
use Eduardokum\LaravelBoleto\Contracts\Cnab\Pagamento as PagamentoRemessaContract;
use Eduardokum\LaravelBoleto\Contracts\Pagamento\Pagamento as PagamentoContract;
use Eduardokum\LaravelBoleto\Pagamento\Banco\Inter as BancoInter;
use Eduardokum\LaravelBoleto\Util;

/**
 * Class Inter
 * @package Eduardokum\LaravelBoleto\Cnab\Pagamento\Cnab240\Banco
 */
class Inter extends AbstractPagamento implements PagamentoRemessaContract
{
    const BANCO = '077';
    const LOTE_SERVICO = '0001';
    const TIPO_REGISTRO = '0';
    const TIPO_DOCUMENTO_EMPRESA = '2'; // CNPJ
    const CODIGO_REMESSA = '1';
    const VERSAO_LAYOUT = '107';
    const DENSIDADE_GRAVACAO = '01600';

    // Constantes para segmentos
    const TIPO_REGISTRO_DETALHE = '3';
    const CODIGO_SEGMENTO_A = 'A';
    const CODIGO_SEGMENTO_B = 'B';
    const TIPO_MOVIMENTO = '0';
    const CODIGO_INSTRUCAO_MOVIMENTO = '00';
    const CODIGO_CAMARA_CENTRALIZADORA = '000';
    const TIPO_MOEDA = 'BRL';
    const QUANTIDADE_MOEDA = '000000000000000';
    const TIPO_CONTA_CORRENTE = '01';
    const FINALIDADE_TED_SALARIOS = '00004';
    const FINALIDADE_TED_FORNECEDORES = '00005';
    const FINALIDADE_TED_CREDITO_CONTA = '00010';

    // Constantes específicas para PIX
    const FORMA_INICIACAO_PIX_TELEFONE = '01'; // Chave PIX - Telefone
    const FORMA_INICIACAO_PIX_EMAIL = '02'; // Chave PIX - Email
    const FORMA_INICIACAO_PIX_CPF_CNPJ = '03'; // Chave PIX - CPF/CNPJ
    const FORMA_INICIACAO_PIX_ALEATORIA = '04'; // Chave PIX - Aleatória
    const FORMA_INICIACAO_PIX_DADOS_BANCARIOS = '05'; // Dados bancários
    const TIPO_DOCUMENTO_CPF = '1';
    const TIPO_DOCUMENTO_CNPJ = '2';

    // Constantes para campos de retorno (preenchidos pelo banco)
    const CAMPO_BRANCO = '';
    const DATA_RETORNO_VAZIA = '00000000';
    const VALOR_RETORNO_VAZIO = '000000000000000';

    // Constantes para header e trailer
    const NOME_BANCO = 'BANCO INTER';
    const AGENCIA_EMPRESA = '00001';
    const AGENCIA_DV_EMPRESA = '9';
    const LOTE_SERVICO_TRAILER = '9999';
    const TIPO_REGISTRO_TRAILER = '9';
    const TIPO_REGISTRO_HEADER_LOTE = '1';
    const TIPO_OPERACAO = 'C';
    const VERSAO_LAYOUT_LOTE = '046';
    const TIPO_REGISTRO_TRAILER_LOTE = '5';

    /**
     * Inter constructor.
     * @param array $params
     */
    public function __construct(array $params = [])
    {
        parent::__construct($params);
        $this->codigoBanco = self::BANCO;

        // Configurar tipo de pagamento se fornecido
        if (isset($params['tipoPagamento'])) {
            $this->tipoPagamento = $params['tipoPagamento'];
        }

        // Configurar tipo de serviço se fornecido
        if (isset($params['tipoServico'])) {
            $this->tipoServico = $params['tipoServico'];
        }
    }

    /**
     * Código do banco
     * @var string
     */
    protected $codigoBanco = self::BANCO;

    /**
     * Define as carteiras disponíveis para este banco
     * @var array
     */
    protected $carteiras = [];

    /**
     * Tipo de pagamento (TED, PIX, DOC, etc.)
     * @var string
     */
    protected $tipoPagamento = 'TED';

    /**
     * Tipo de serviço
     * @var string
     */
    protected $tipoServico = '01';

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
     * @return Inter
     * @throws \Exception
     */
    protected function header()
    {
        $this->iniciaHeader();

        $this->add(1, 3, self::BANCO); // Código do banco na compensação
        $this->add(4, 7, self::LOTE_SERVICO); // Lote de serviço
        $this->add(8, 8, self::TIPO_REGISTRO); // Tipo de registro
        $this->add(9, 17, self::CAMPO_BRANCO); // Campo em branco
        $this->add(18, 18, self::TIPO_DOCUMENTO_EMPRESA); // Tipo de documento da empresa (CNPJ)
        $this->add(19, 32, Util::onlyNumbers($this->getPagador()->getDocumento())); // CPF/CNPJ da empresa
        $this->add(33, 52, self::CAMPO_BRANCO); // Campo em branco
        $this->add(53, 57, self::AGENCIA_EMPRESA); // Agência mantenedora da conta da empresa
        $this->add(58, 58, self::AGENCIA_DV_EMPRESA); // Dígito verificador da agência
        $this->add(59, 70, Util::formatCnab('9L', $this->getConta(), 12)); // Número da conta corrente da empresa
        $this->add(71, 71, $this->getContaDv()); // Dígito verificador da conta
        $this->add(72, 72, self::CAMPO_BRANCO); // Campo em branco
        $this->add(73, 102, Util::formatCnab('X', $this->getPagador()->getNome(), 30)); // Nome da empresa
        $this->add(103, 132, Util::formatCnab('X', self::NOME_BANCO, 30)); // Nome do banco
        $this->add(133, 142, self::CAMPO_BRANCO); // Campo em branco
        $this->add(143, 143, self::CODIGO_REMESSA); // Código de remessa
        $this->add(144, 151, $this->getDataRemessa('dmY')); // Data de geração do arquivo
        $this->add(152, 157, $this->getDataRemessa('His')); // Hora de geração do arquivo
        $this->add(158, 163, str_pad($this->getIdremessa(), 6, '0', STR_PAD_LEFT)); // Número sequencial do arquivo
        $this->add(164, 166, self::VERSAO_LAYOUT); // Número da versão do layout do arquivo
        $this->add(167, 171, self::DENSIDADE_GRAVACAO); // Densidade de gravação do arquivo
        $this->add(172, 191, self::CAMPO_BRANCO); // Para uso reservado do banco
        $this->add(192, 211, self::CAMPO_BRANCO); // Para uso reservado da empresa
        $this->add(212, 240, self::CAMPO_BRANCO); // Uso exclusivo FEBRABAN / CNAB

        return $this;
    }

    /**
     * @return Inter
     * @throws \Exception
     */
    protected function headerLote()
    {
        $this->iniciaHeaderLote();

        $this->add(1, 3, self::BANCO); // Código do banco na compensação
        $this->add(4, 7, self::LOTE_SERVICO); // Lote de serviço
        $this->add(8, 8, self::TIPO_REGISTRO_HEADER_LOTE); // Tipo de registro
        $this->add(9, 9, self::TIPO_OPERACAO); // Tipo da operação
        $this->add(10, 11, $this->getTipoServico()); // Tipo do serviço
        $this->add(12, 13, $this->getFormaLancamento()); // Forma de lançamento (varia por tipo)
        $this->add(14, 16, self::VERSAO_LAYOUT_LOTE); // Número da versão do layout do Lote
        $this->add(17, 17, self::CAMPO_BRANCO); // Campo em branco
        $this->add(18, 18, self::TIPO_DOCUMENTO_EMPRESA); // Tipo de documento da empresa (CNPJ)
        $this->add(19, 32, Util::onlyNumbers($this->getPagador()->getDocumento())); // CPF/CNPJ da empresa
        $this->add(33, 52, self::CAMPO_BRANCO); // Campo em branco
        $this->add(53, 57, self::AGENCIA_EMPRESA); // Agência mantenedora da conta da empresa
        $this->add(58, 58, self::AGENCIA_DV_EMPRESA); // Dígito verificador da agência
        $this->add(59, 70, Util::formatCnab('9L', $this->getConta(), 12)); // Número da conta corrente da empresa
        $this->add(71, 71, $this->getContaDv()); // Dígito verificador da conta
        $this->add(72, 72, self::CAMPO_BRANCO); // Campo em branco
        $this->add(73, 102, Util::formatCnab('X', $this->getPagador()->getNome(), 30)); // Nome da empresa
        $this->add(103, 142, self::CAMPO_BRANCO); // Informação genérica opcional
        $this->add(143, 172, Util::formatCnab('X', $this->getPagador()->getEndereco(), 30)); // Nome da Rua, Av, Pça, Etc.
        $this->add(173, 177, self::CAMPO_BRANCO); // Número do local da empresa
        $this->add(178, 192, self::CAMPO_BRANCO); // Casa, Apto, Sala, Etc.
        $this->add(193, 212, Util::formatCnab('X', $this->getPagador()->getCidade(), 20)); // Nome da cidade da empresa
        $cep = Util::onlyNumbers($this->getPagador()->getCep());
        $this->add(213, 217, substr($cep, 0, 5)); // CEP da empresa
        $this->add(218, 220, substr($cep, 5, 3)); // Complemento do CEP
        $this->add(221, 222, $this->getPagador()->getUf()); // Sigla do estado da empresa
        $this->add(223, 230, self::CAMPO_BRANCO); // Campo em branco
        $this->add(231, 240, self::CAMPO_BRANCO); // Códigos das ocorrências para retorno

        return $this;
    }

    /**
     * @param PagamentoContract $pagamento
     * @return Inter
     * @throws \Exception
     */
    public function addPagamento(PagamentoContract $pagamento)
    {
        $this->pagamentos[] = $pagamento;

        switch ($this->getTipoPagamento()) {
            case 'PIX': // PIX
                $this->segmentoAPix($pagamento);
                $this->segmentoBPix($pagamento);
                break;
            default: // TED
                $this->segmentoA($pagamento);
                $this->segmentoB($pagamento);
                break;
        }

        return $this;
    }

    /**
     * @return Inter
     * @throws \Exception
     */
    protected function trailerLote()
    {
        $this->iniciaTrailerLote();

        $this->add(1, 3, self::BANCO); // Código do banco na compensação
        $this->add(4, 7, self::LOTE_SERVICO); // Lote de serviço (Número do lote)
        $this->add(8, 8, self::TIPO_REGISTRO_TRAILER_LOTE); // Tipo de registro
        $this->add(9, 17, self::CAMPO_BRANCO); // Campo em branco
        $this->add(18, 23, str_pad($this->getCountRegistrosLote(), 6, '0', STR_PAD_LEFT)); // Quantidade de lotes no arquivo
        $this->add(24, 41, str_pad($this->getValorTotalLote(), 18, '0', STR_PAD_LEFT)); // Somatória dos valores
        $this->add(42, 59, self::QUANTIDADE_MOEDA); // Somatória de quantidade de moedas
        $this->add(60, 65, self::CAMPO_BRANCO); // Número aviso de débito
        $this->add(66, 230, self::CAMPO_BRANCO); // Campo em branco
        $this->add(231, 240, self::CAMPO_BRANCO); // Códigos das ocorrências para retorno

        return $this;
    }

    /**
     * @return Inter
     * @throws \Exception
     */
    protected function trailer()
    {
        $this->iniciaTrailer();

        $this->add(1, 3, self::BANCO); // Código do banco na compensação
        $this->add(4, 7, self::LOTE_SERVICO_TRAILER); // Lote de serviço
        $this->add(8, 8, self::TIPO_REGISTRO_TRAILER); // Tipo de registro
        $this->add(9, 17, self::CAMPO_BRANCO); // Campo em branco
        $this->add(18, 23, str_pad($this->getCountLotes(), 6, '0', STR_PAD_LEFT)); // Quantidade de lotes do arquivo
        $this->add(24, 29, str_pad($this->getCount(), 6, '0', STR_PAD_LEFT)); // Quantidade de registros do arquivo
        $this->add(30, 240, self::CAMPO_BRANCO); // Campo em branco

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
    protected function getNomeBanco()
    {
        return 'BANCO INTER';
    }

    /**
     * @return string
     */
    protected function getVersaoLayout()
    {
        return self::VERSAO_LAYOUT;
    }

    /**
     * Retorna a quantidade de lotes do arquivo
     * @return int
     */
    protected function getCountLotes()
    {
        return 1; // Para pagamentos, geralmente há apenas 1 lote
    }

    /**
     * Retorna a quantidade de registros no lote
     * @return int
     */
    protected function getCountRegistrosLote()
    {
        return $this->iRegistrosLote + 2; // +2 para incluir header e trailer do lote
    }

    /**
     * Retorna o valor total do lote
     * @return string
     */
    protected function getValorTotalLote()
    {
        // Por enquanto retorna 0, será implementado quando adicionarmos os pagamentos
        return '000000000000000000'; // 18 zeros
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
        return $this->tipoServico ?? '01'; // Padrão para TED
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
                return '03'; // TED
            case 'PIX':
                return '45'; // Transferência via PIX
            case 'COBRANCA_PROPRIO':
                return '30'; // Pagamentos do próprio banco Inter
            case 'COBRANCA_OUTROS':
                return '31'; // Pagamentos de outros bancos
            case 'COBRANCA_QR':
                return '47'; // Pagamento de cobrança com QRCode
            case 'CONVENIO_CONTAS':
                return '11'; // Contas e Tributos
            case 'CONVENIO_ISS_PROPRIO':
                return '80'; // Tributos municipais ISS-LCP 157 próprio Banco
            case 'CONVENIO_ISS_OUTROS':
                return '81'; // Tributos municipais ISS-LCP 157 outros Bancos
            default:
                return '03'; // Padrão TED
        }
    }

    /**
     * Adiciona um segmento A para TED
     *
     * @param BancoInter $pagamento
     * @return $this
     */
    public function segmentoA($pagamento)
    {
        $this->iniciaDetalhe();

        $this->add(1, 3, self::BANCO); // Código do banco na compensação
        $this->add(4, 7, self::LOTE_SERVICO); // Lote de serviço
        $this->add(8, 8, self::TIPO_REGISTRO_DETALHE); // Tipo de registro
        $this->add(9, 13, Util::formatCnab('9L', $this->iRegistrosLote, 5)); // Número sequencial do registro detalhe
        $this->add(14, 14, self::CODIGO_SEGMENTO_A); // Código de segmento do registro detalhe
        $this->add(15, 15, self::TIPO_MOVIMENTO); // Tipo de movimento
        $this->add(16, 17, self::CODIGO_INSTRUCAO_MOVIMENTO); // Código da instrução para movimento
        $this->add(18, 20, self::CODIGO_CAMARA_CENTRALIZADORA); // Código da câmara centralizadora
        $this->add(21, 23, Util::formatCnab('9L', $pagamento->getBanco(), 3)); // [Favorecido] Código do banco
        $this->add(24, 28, Util::formatCnab('9L', $pagamento->getAgencia(), 5)); // [Favorecido] Agência mantenedora da conta
        $this->add(29, 29, $pagamento->getAgenciaDv()); // [Favorecido] Dígito verificador da agência
        $this->add(30, 41, Util::formatCnab('9L', $pagamento->getConta(), 12)); // [Favorecido] Número da conta corrente
        $this->add(42, 42, $pagamento->getContaDv()); // [Favorecido] Dígito verificador da conta
        $this->add(43, 43, self::CAMPO_BRANCO); // Campo em branco
        $this->add(44, 73, Util::formatCnab('X', $pagamento->getBeneficiario()->getNome(), 30)); // [Favorecido] Nome
        $this->add(74, 93, Util::formatCnab('X', $pagamento->getNumeroDocumento(), 20)); // Número do documento atribuído para empresa

        $dataPagamento = $pagamento->getDataPagamento() ? date('dmY', strtotime($pagamento->getDataPagamento())) : date('dmY');

        $this->add(94, 101, $dataPagamento); // Data do pagamento
        $this->add(102, 104, self::TIPO_MOEDA); // Tipo da moeda
        $this->add(105, 119, self::QUANTIDADE_MOEDA); // Quantidade da moeda

        $valor = $pagamento->getValor() ? number_format($pagamento->getValor(), 2, '', '') : '000000000000000';

        $this->add(120, 134, Util::formatCnab('9L', $valor, 15)); // Valor do pagamento
        $this->add(135, 154, self::CAMPO_BRANCO); // Número do documento atribuído pelo banco
        $this->add(155, 162, self::DATA_RETORNO_VAZIA); // Data real da efetivação do pagamento (Retorno)
        $this->add(163, 177, self::VALOR_RETORNO_VAZIO); // Valor real da efetivação do pagamento (Retorno)
        $this->add(178, 199, self::CAMPO_BRANCO); // Campo em branco
        $this->add(200, 201, $pagamento->getTipoConta() ?? self::TIPO_CONTA_CORRENTE); // [Identificação do Pagamento] Tipo de conta
        $this->add(202, 219, self::CAMPO_BRANCO); // Campo em branco (conforme Item 27 da imagem)
        $this->add(220, 224, '00010'); // [Identificação do Pagamento] Número do CPF/CNPJ
        $this->add(225, 230, self::CAMPO_BRANCO); // Códigos das ocorrências para retorno
        $this->add(231, 240, self::CAMPO_BRANCO); // Campo em branco

        $this->iRegistrosLote++;
        return $this;
    }

    /**
     * Adiciona um segmento A para PIX
     *
     * @param BancoInter $pagamento
     * @return $this
     */
    public function segmentoAPix($pagamento)
    {
        $this->iniciaDetalhe();

        $this->add(1, 3, self::BANCO); // Código do banco na compensação
        $this->add(4, 7, self::LOTE_SERVICO); // Lote de serviço
        $this->add(8, 8, self::TIPO_REGISTRO_DETALHE); // Tipo de registro
        $this->add(9, 13, str_pad($this->iRegistrosLote, 5, '0', STR_PAD_LEFT)); // Número sequencial do registro detalhe
        $this->add(14, 14, self::CODIGO_SEGMENTO_A); // Código de segmento do registro detalhe
        $this->add(15, 15, self::TIPO_MOVIMENTO); // Tipo de movimento
        $this->add(16, 17, self::CODIGO_INSTRUCAO_MOVIMENTO); // Código da instrução para movimento
        $this->add(18, 20, self::CODIGO_CAMARA_CENTRALIZADORA); // Código da câmara centralizadora

        // Dados bancários do favorecido (preenchidos apenas se forma de iniciação = "05")
        $formaIniciacao = $pagamento->getFormaIniciacao() ?? self::FORMA_INICIACAO_PIX_DADOS_BANCARIOS;
        if ($formaIniciacao == self::FORMA_INICIACAO_PIX_DADOS_BANCARIOS) {
            $this->add(21, 23, Util::formatCnab('9L', $pagamento->getBanco(), 3)); // [Favorecido] Código do banco
            $this->add(24, 28, Util::formatCnab('9L', $pagamento->getAgencia(), 5)); // [Favorecido] Agência mantenedora da conta
            $this->add(29, 29, $pagamento->getAgenciaDv()); // [Favorecido] Dígito verificador da agência
            $this->add(30, 41, Util::formatCnab('9L', $pagamento->getConta(), 12)); // [Favorecido] Número da conta corrente
            $this->add(42, 42, $pagamento->getContaDv()); // [Favorecido] Dígito verificador da conta
            $this->add(44, 73, Util::formatCnab('X', $pagamento->getBeneficiario()->getNome(), 30)); // [Favorecido] Nome
        } else {
            $this->add(21, 23, '000'); // [Favorecido] Código do banco
            $this->add(24, 28, '00000'); // [Favorecido] Agência mantenedora da conta
            $this->add(29, 29, '0'); // [Favorecido] Dígito verificador da agência
            $this->add(30, 41, '000000000000'); // [Favorecido] Número da conta corrente
            $this->add(42, 42, '0'); // [Favorecido] Dígito verificador da conta
            $this->add(44, 73, '000000000000000000000000000000'); // [Favorecido] Nome
        }

        $this->add(43, 43, self::CAMPO_BRANCO); // Campo em branco
        $this->add(74, 93, Util::formatCnab('X', $pagamento->getNumeroDocumento() ?? '', 20)); // Número do documento atribuído para empresa
        $dataPagamento = $pagamento->getDataVencimento() ? date('dmY', strtotime($pagamento->getDataVencimento())) : date('dmY');
        $this->add(94, 101, $dataPagamento); // Data do pagamento
        $this->add(102, 104, self::TIPO_MOEDA); // Tipo da moeda
        $this->add(105, 119, self::QUANTIDADE_MOEDA); // Quantidade da moeda
        $valor = $pagamento->getValor() ? number_format($pagamento->getValor(), 2, '', '') : '000000000000000';
        $this->add(120, 134, Util::formatCnab('9L', $valor, 15)); // Valor do pagamento
        $this->add(135, 154, self::CAMPO_BRANCO); // Número do documento atribuído pelo banco
        $this->add(155, 162, self::DATA_RETORNO_VAZIA); // Data real da efetivação do pagamento (Retorno)
        $this->add(163, 177, self::VALOR_RETORNO_VAZIO); // Valor real da efetivação do pagamento (Retorno)

        $this->add(178, 191, Util::formatCnab('9L', $pagamento->getBeneficiario()->getDocumento(), 14)); // [Identificação do Pagamento] Número do CPF/CNPJ
        $this->add(192, 199, str_pad($pagamento->getBanco() ?? '0', 8, '0', STR_PAD_LEFT)); // [Identificação do Pagamento] Código do ISPB
        if ($formaIniciacao == self::FORMA_INICIACAO_PIX_DADOS_BANCARIOS)
            $this->add(200, 201, $pagamento->getTipoConta() ?? self::TIPO_CONTA_CORRENTE); // [Identificação do Pagamento] Tipo de conta
        else
            $this->add(200, 201, '00');
        $this->add(202, 230, self::CAMPO_BRANCO); // Campo em branco (conforme Item 27 da imagem)
        $this->add(231, 240, self::CAMPO_BRANCO); // Códigos das ocorrências para retorno

        $this->iRegistrosLote++;
        return $this;
    }

    /**
     * Adiciona um segmento B para PIX
     *
     * @param BancoInter $pagamento
     * @return $this
     */
    public function segmentoBPix($pagamento)
    {
        $this->iniciaDetalhe();

        $this->add(1, 3, self::BANCO); // Código do banco na compensação
        $this->add(4, 7, self::LOTE_SERVICO); // Lote de serviço
        $this->add(8, 8, self::TIPO_REGISTRO_DETALHE); // Tipo de registro
        $this->add(9, 13, str_pad($this->iRegistrosLote, 5, '0', STR_PAD_LEFT)); // Número sequencial do registro detalhe
        $this->add(14, 14, self::CODIGO_SEGMENTO_B); // Código de segmento do registro detalhe
        $this->add(15, 17, $pagamento->getFormaIniciacao() ?? self::FORMA_INICIACAO_PIX_CPF_CNPJ); // Forma de iniciação (tipo de chave)
        $this->add(18, 18, $pagamento->getBeneficiario()->getTipoDocumento() == 'CPF' ? self::TIPO_DOCUMENTO_CPF : self::TIPO_DOCUMENTO_CNPJ); // [Favorecido] Tipo de documento

        // CPF/CNPJ (preenchido apenas se forma de iniciação = "03")
        $formaIniciacao = $pagamento->getFormaIniciacao() ?? self::FORMA_INICIACAO_PIX_CPF_CNPJ;
        if ($formaIniciacao == self::FORMA_INICIACAO_PIX_CPF_CNPJ) {
            $this->add(19, 32, Util::formatCnab('9L', $pagamento->getBeneficiario()->getDocumento(), 14)); // [Favorecido] CPF/CNPJ
        } else {
            $this->add(19, 32, self::CAMPO_BRANCO); // [Favorecido] CPF/CNPJ
        }

        $this->add(33, 67, Util::formatCnab('X', $pagamento->getTxId() ?? '', 35)); // TX ID (Opcional)
        $this->add(68, 127, self::CAMPO_BRANCO); // Campo em branco

        // Chave PIX (preenchida apenas se forma de iniciação = "01", "02" ou "04")
        if (in_array($formaIniciacao, [self::FORMA_INICIACAO_PIX_TELEFONE, self::FORMA_INICIACAO_PIX_EMAIL, self::FORMA_INICIACAO_PIX_ALEATORIA])) {
            $this->add(128, 226, Util::formatCnab('X', $pagamento->getPixChave() ?? '', 99)); // Chave Pix (telefone, e-mail ou aleatória)
        } else {
            $this->add(128, 226, self::CAMPO_BRANCO); // Chave Pix
        }

        $this->add(227, 232, self::CAMPO_BRANCO); // Campo em branco
        $this->add(233, 240, self::CAMPO_BRANCO); // [Favorecido] Código ISPB

        $this->iRegistrosLote++;
        return $this;
    }

    /**
     * Adiciona um segmento B para TED
     *
     * @param BancoInter $pagamento
     * @return $this
     */
    public function segmentoB($pagamento)
    {
        $this->iniciaDetalhe();

        $this->add(1, 3, self::BANCO); // Código do banco na compensação
        $this->add(4, 7, self::LOTE_SERVICO); // Lote de serviço
        $this->add(8, 8, self::TIPO_REGISTRO_DETALHE); // Tipo de registro
        $this->add(9, 13, Util::formatCnab('9L', $this->iRegistrosLote, 5)); // Número sequencial do registro detalhe
        $this->add(14, 14, self::CODIGO_SEGMENTO_B); // Código de segmento do registro detalhe
        $this->add(15, 17, self::CAMPO_BRANCO); // Campo em branco
        $this->add(18, 18, $pagamento->getBeneficiario()->getTipoDocumento() == 'CPF' ? self::TIPO_DOCUMENTO_CPF : self::TIPO_DOCUMENTO_CNPJ); // [Favorecido] Tipo de documento
        $this->add(19, 32, Util::formatCnab('9L', $pagamento->getBeneficiario()->getDocumento(), 14)); // [Favorecido] CPF/CNPJ
        $this->add(33, 67, Util::formatCnab('X', $pagamento->getBeneficiario()->getEndereco(), 35)); // [Favorecido] Logradouro
        $this->add(68, 72, Util::formatCnab('X', '', 5)); // [Favorecido] Número do local
        $this->add(73, 87, Util::formatCnab('X', '', 15)); // [Favorecido] Complemento
        $this->add(88, 102, Util::formatCnab('X', $pagamento->getBeneficiario()->getBairro(), 15)); // [Favorecido] Bairro
        $this->add(103, 117, Util::formatCnab('X', $pagamento->getBeneficiario()->getCidade(), 15)); // [Favorecido] Nome da cidade

        $this->add(118, 125, Util::formatCnab('9L', $pagamento->getBeneficiario()->getCep(), 8)); // [Favorecido] CEP
        $this->add(126, 127, Util::formatCnab('X', $pagamento->getBeneficiario()->getUf(), 2)); // [Favorecido] Sigla do estado
        $this->add(128, 232, self::CAMPO_BRANCO); // Campo em branco
        $this->add(233, 240, self::CAMPO_BRANCO); // [Favorecido] Código ISPB

        $this->iRegistrosLote++;
        return $this;
    }
}
