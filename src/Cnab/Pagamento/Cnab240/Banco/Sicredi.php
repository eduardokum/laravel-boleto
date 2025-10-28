<?php

namespace Eduardokum\LaravelBoleto\Cnab\Pagamento\Cnab240\Banco;

use Eduardokum\LaravelBoleto\Cnab\Pagamento\Cnab240\AbstractPagamento;
use Eduardokum\LaravelBoleto\Contracts\Cnab\Pagamento as PagamentoRemessaContract;
use Eduardokum\LaravelBoleto\Contracts\Pagamento\Pagamento as PagamentoContract;
use Eduardokum\LaravelBoleto\Pagamento\Banco\Banco;
use Eduardokum\LaravelBoleto\Util;

/**
 * Class Sicredi
 * @package Eduardokum\LaravelBoleto\Cnab\Pagamento\Cnab240\Banco
 */
class Sicredi extends AbstractPagamento implements PagamentoRemessaContract
{
    const BANCO = '748';
    const CODIGO_ISPB = '01181521';
    const LOTE_SERVICO = '0001';
    const TIPO_REGISTRO = '0';
    const CODIGO_REMESSA = '1';
    const VERSAO_LAYOUT = '082';
    const DENSIDADE_GRAVACAO = '01600';

    // Constantes para segmentos
    const TIPO_REGISTRO_DETALHE = '3';
    const CODIGO_SEGMENTO_A = 'A';
    const CODIGO_SEGMENTO_B = 'B';
    const TIPO_MOVIMENTO = '0';
    const CODIGO_INSTRUCAO_MOVIMENTO = '00';
    const CODIGO_CAMARA_CENTRALIZADORA = '018';
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
    const NOME_BANCO = 'SICREDI';
    const AGENCIA_EMPRESA = '00001';
    const AGENCIA_DV_EMPRESA = '9';
    const LOTE_SERVICO_TRAILER = '9999';
    const TIPO_REGISTRO_TRAILER = '9';
    const TIPO_REGISTRO_HEADER_LOTE = '1';
    const TIPO_OPERACAO = 'C';
    const VERSAO_LAYOUT_LOTE = '042';
    const TIPO_REGISTRO_TRAILER_LOTE = '5';

    /**
     * Sicredi constructor.
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
     * @return Sicredi
     * @throws \Exception
     */
    protected function header()
    {
        $this->iniciaHeader();

        $this->add(1, 3, self::BANCO); // 01.0 - Código do banco na compensação (G001)
        $this->add(4, 7, '0000'); // 02.0 - Lote de serviço (G002)
        $this->add(8, 8, self::TIPO_REGISTRO); // 03.0 - Tipo de registro (G003)
        $this->add(9, 17, self::CAMPO_BRANCO); // 04.0 - Uso exclusivo SICREDI (G004)
        $this->add(18, 18, Util::formatCnab('9L', $this->getPagador()->getTipoDocumento() == 'CPF' ? self::TIPO_DOCUMENTO_CPF : self::TIPO_DOCUMENTO_CNPJ, 1)); // 05.0 - Tipo de inscrição (G005)
        $this->add(19, 32, Util::formatCnab('9L', $this->getPagador()->getDocumento(), 14)); // 06.0 - Número de inscrição (G006)

        $convenio = $this->getConvenio() ?? '';

        $this->add(33, 52, Util::formatCnab('X', $convenio, 20)); // 07.0 - Código do convênio no banco (G007)
        $this->add(53, 57, Util::formatCnab('9L', $this->getAgencia(), 5)); // 08.0 - Agência mantenedora da conta (G008)
        $this->add(58, 58, Util::formatCnab('X', $this->getAgenciaDv(), 1)); // 09.0 - Dígito verificador da agência (G009)
        $this->add(59, 70, Util::formatCnab('9L', $this->getConta(), 12)); // 10.0 - Número da conta corrente (G010)
        $this->add(71, 71, Util::formatCnab('9L', $this->getContaDv(), 1)); // 11.0 - Dígito verificador da conta (G011)
        $this->add(72, 72, self::CAMPO_BRANCO); // 12.0 - Dígito verificador da Ag/conta (G012)
        $this->add(73, 102, Util::formatCnab('X', $this->getPagador()->getNome(), 30)); // 13.0 - Nome da empresa (G013)
        $this->add(103, 132, Util::formatCnab('X', self::NOME_BANCO, 30)); // 14.0 - Nome do banco (G014)
        $this->add(133, 142, self::CAMPO_BRANCO); // 15.0 - Uso exclusivo SICREDI (G004)
        $this->add(143, 143, self::CODIGO_REMESSA); // 16.0 - Código remessa / retorno (G015)
        $this->add(144, 151, $this->getDataRemessa('dmY')); // 17.0 - Data de geração do arquivo (G016)
        $this->add(152, 157, $this->getDataRemessa('His')); // 18.0 - Hora de geração do arquivo (G017)
        $this->add(158, 163, Util::formatCnab('9L', $this->getIdremessa(), 6)); // 19.0 - Número sequencial do arquivo (G018)
        $this->add(164, 166, self::VERSAO_LAYOUT); // 20.0 - Nº da versão do leiaute (G019)
        $this->add(167, 171, self::DENSIDADE_GRAVACAO); // 21.0 - Densidade de gravação (G020)
        $this->add(172, 191, self::CAMPO_BRANCO); // 22.0 - Para uso reservado do banco (G021)
        $this->add(192, 211, self::CAMPO_BRANCO); // 23.0 - Para uso reservado da empresa (G022)
        $this->add(212, 240, self::CAMPO_BRANCO); // 24.0 - Uso exclusivo SICREDI (G004)

        return $this;
    }

    /**
     * @return Sicredi
     * @throws \Exception
     */
    protected function headerLote()
    {
        $this->iniciaHeaderLote();

        $this->add(1, 3, self::BANCO); // 01.1 - Código do banco na compensação (G001)
        $this->add(4, 7, self::LOTE_SERVICO); // 02.1 - Lote de serviço (G002)
        $this->add(8, 8, self::TIPO_REGISTRO_HEADER_LOTE); // 03.1 - Tipo de registro (G003)
        $this->add(9, 9, self::TIPO_OPERACAO); // 04.1 - Tipo da operação (G028)
        $this->add(10, 11, $this->getTipoServico()); // 05.1 - Tipo do serviço (G025)
        $this->add(12, 13, $this->getFormaLancamento()); // 06.1 - Forma de lançamento (G029)
        $this->add(14, 16, self::VERSAO_LAYOUT_LOTE); // 07.1 - Nº da versão do layout do lote (G030)
        $this->add(17, 17, self::CAMPO_BRANCO); // 08.1 - Uso exclusivo SICREDI (G004)
        $this->add(18, 18, Util::formatCnab('9L', $this->getPagador()->getTipoDocumento() == 'CPF' ? self::TIPO_DOCUMENTO_CPF : self::TIPO_DOCUMENTO_CNPJ, 1)); // 09.1 - Tipo de inscrição (G005)
        $this->add(19, 32, Util::formatCnab('9L', $this->getPagador()->getDocumento(), 14)); // 10.1 - Número de inscrição (G006)
        $this->add(33, 52, self::CAMPO_BRANCO); // 11.1 - Código do convênio no banco (G007)
        $this->add(53, 57, self::AGENCIA_EMPRESA); // 12.1 - Agência mantenedora da conta (G008)
        $this->add(58, 58, self::AGENCIA_DV_EMPRESA); // 13.1 - Dígito verificador da agência (G009)
        $this->add(59, 70, Util::formatCnab('9L', $this->getConta(), 12)); // 14.1 - Número da conta corrente (G010)
        $this->add(71, 71, $this->getContaDv()); // 15.1 - Dígito verificador da conta (G011)
        $this->add(72, 72, self::CAMPO_BRANCO); // 16.1 - Dígito verificador da ag/conta (G012)
        $this->add(73, 102, Util::formatCnab('X', $this->getPagador()->getNome(), 30)); // 17.1 - Nome da empresa (G013)
        $this->add(103, 142, self::CAMPO_BRANCO); // 18.1 - Mensagem (G031)
        $this->add(143, 172, Util::formatCnab('X', $this->getPagador()->getEndereco(), 30)); // 19.1 - Nome da rua, av, pça, etc (G032)
        $this->add(173, 177, self::CAMPO_BRANCO); // 20.1 - Número do local (G032)
        $this->add(178, 192, self::CAMPO_BRANCO); // 21.1 - Casa, apto, sala, etc (G032)
        $this->add(193, 212, Util::formatCnab('X', $this->getPagador()->getCidade(), 20)); // 22.1 - Nome da cidade (G033)
        $cep = Util::formatCnab('9L', $this->getPagador()->getCep(), 8);
        $this->add(213, 217, substr($cep, 0, 5)); // 23.1 - CEP (G034)
        $this->add(218, 220, substr($cep, 5, 3)); // 24.1 - Complemento do CEP (G035)
        $this->add(221, 222, Util::formatCnab('X', $this->getPagador()->getUf(), 2)); // 25.1 - Sigla do Estado (G036)
        $this->add(223, 230, self::CAMPO_BRANCO); // 26.1 - Uso exclusivo SICREDI (G004)
        $this->add(231, 240, self::CAMPO_BRANCO); // 27.1 - Códigos das ocorrências p/ retorno (G059)

        return $this;
    }

    /**
     * @param PagamentoContract $pagamento
     * @return Sicredi
     * @throws \Exception
     */
    public function addPagamento(PagamentoContract $pagamento)
    {
        $this->pagamentos[] = $pagamento;
        return $this;
    }

    /**
     * @return Sicredi
     * @throws \Exception
     */
    protected function trailerLote()
    {
        $this->iniciaTrailerLote();

        $this->add(1, 3, self::BANCO); // 01.5 - Código do banco na compensação (G001)
        $this->add(4, 7, self::LOTE_SERVICO); // 02.5 - Lote de serviço (G002)
        $this->add(8, 8, self::TIPO_REGISTRO_TRAILER_LOTE); // 03.5 - Tipo de registro (G003)
        $this->add(9, 17, self::CAMPO_BRANCO); // 04.5 - Uso exclusivo SICREDI (G004)
        $this->add(18, 23, Util::formatCnab('9L', $this->getCountRegistrosLote(), 6)); // 05.5 - Quantidade de registros do lote (G057)
        $this->add(24, 41, Util::formatCnab('9L', $this->getValorTotalLote(), 18)); // 06.5 - Somatória dos valores (P007)
        $this->add(42, 59, Util::formatCnab('9L', self::QUANTIDADE_MOEDA, 18)); // 07.5 - Somatória de quantidade de moedas (G058)
        $this->add(60, 65, self::CAMPO_BRANCO); // 08.5 - Número aviso de débito (G066)
        $this->add(66, 230, self::CAMPO_BRANCO); // 09.5 - Uso exclusivo SICREDI (G004)
        $this->add(231, 240, self::CAMPO_BRANCO); // 10.5 - Códigos das ocorrências para retorno (G059)

        return $this;
    }

    /**
     * @return Sicredi
     * @throws \Exception
     */
    protected function trailer()
    {
        $this->iniciaTrailer();

        $this->add(1, 3, self::BANCO); // 01.9 - Código do banco na compensação (G001)
        $this->add(4, 7, self::LOTE_SERVICO_TRAILER); // 02.9 - Lote de serviço (G002)
        $this->add(8, 8, self::TIPO_REGISTRO_TRAILER); // 03.9 - Tipo de registro (G003)
        $this->add(9, 17, self::CAMPO_BRANCO); // 04.9 - Uso exclusivo SICREDI (G004)
        $this->add(18, 23, Util::formatCnab('9L', $this->getCountLotes(), 6)); // 05.9 - Quantidade de lotes do arquivo (G049)
        $this->add(24, 29, Util::formatCnab('9L', $this->getCount(), 6)); // 06.9 - Quantidade de registros do arquivo (G056)
        $this->add(30, 35, Util::formatCnab('9L', $this->getCountLotes(), 6)); // 07.9 - Quantidade de contas para conciliação (G037)
        $this->add(36, 240, self::CAMPO_BRANCO); // 08.9 - Uso exclusivo SICREDI (G004)

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
        return 'SICREDI';
    }

    /**
     * @return string
     */
    protected function getVersaoLayout()
    {
        return self::VERSAO_LAYOUT;
    }

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
     * Código do convênio
     * @var string
     */
    protected $convenio;

    /**
     * Retorna a quantidade de lotes do arquivo
     * @return int
     */
    protected function getCountLotes()
    {
        return count($this->lotes);
    }

    /**
     * Retorna a quantidade de registros no lote
     * @return int
     */
    protected function getCountRegistrosLote()
    {
        $countDetalhes = $this->getCountDetalhes();
        return $countDetalhes + 2; // header do lote (1) + registros de detalhe + trailer do lote (1)
    }

    /**
     * Retorna o valor total do lote
     * @return string
     */
    protected function getValorTotalLote()
    {
        $valorTotal = 0;

        foreach ($this->pagamentos as $pagamento) {
            if (method_exists($pagamento, 'getValor') && $pagamento->getValor() > 0)
                $valorTotal += $pagamento->getValor();
        }

        return Util::formatCnab('9L', $valorTotal * 100, 18);
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
        return $this->tipoServico ?? '01';
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
                return '03';
            case 'PIX':
                return '45';
            default:
                return '03';
        }
    }

    /**
     * Header do lote para múltiplos lotes
     *
     * @param array $lote
     * @return Sicredi
     * @throws \Exception
     */
    protected function headerLoteMulti(array $lote)
    {
        $this->iniciaHeaderLote();

        $this->add(1, 3, self::BANCO); // 01.1 - Código do banco na compensação (G001)
        $this->add(4, 7, Util::formatCnab('9L', $lote['numero'], 4)); // 02.1 - Lote de serviço (G002) - Número do lote dinâmico
        $this->add(8, 8, self::TIPO_REGISTRO_HEADER_LOTE); // 03.1 - Tipo de registro (G003)
        $this->add(9, 9, self::TIPO_OPERACAO); // 04.1 - Tipo da operação (G028)
        $this->add(10, 11, $this->getTipoServicoPorTipoPagamento($lote['tipo'])); // 05.1 - Tipo do serviço (G025) - Baseado no tipo do pagamento
        $this->add(12, 13, $this->getFormaLancamentoPorTipo($lote['tipo'])); // 06.1 - Forma de lançamento (G029) - Baseado no tipo do pagamento
        $this->add(14, 16, self::VERSAO_LAYOUT_LOTE); // 07.1 - Nº da versão do layout do lote (G030)
        $this->add(17, 17, self::CAMPO_BRANCO); // 08.1 - Uso exclusivo SICREDI (G004)
        $this->add(18, 18, Util::formatCnab('9L', $this->getPagador()->getTipoDocumento() == 'CPF' ? self::TIPO_DOCUMENTO_CPF : self::TIPO_DOCUMENTO_CNPJ, 1)); // 09.1 - Tipo de inscrição (G005)
        $this->add(19, 32, Util::formatCnab('9L', $this->getPagador()->getDocumento(), 14)); // 10.1 - Número de inscrição (G006)

        $convenio = $this->getConvenio() ?? '';

        $this->add(33, 52, Util::formatCnab('X', $convenio, 20)); // 11.1 - Código do convênio no banco (G007)
        $this->add(53, 57, Util::formatCnab('9L', $this->getAgencia(), 5)); // 12.1 - Agência mantenedora da conta (G008)
        $this->add(58, 58, $this->getAgenciaDv()); // 13.1 - Dígito verificador da agência (G009)
        $this->add(59, 70, Util::formatCnab('9L', $this->getConta(), 12)); // 14.1 - Número da conta corrente (G010)
        $this->add(71, 71, $this->getContaDv()); // 15.1 - Dígito verificador da conta (G011)
        $this->add(72, 72, self::CAMPO_BRANCO); // 16.1 - Dígito verificador da ag/conta (G012)
        $this->add(73, 102, Util::formatCnab('X', $this->getPagador()->getNome(), 30)); // 17.1 - Nome da empresa (G013)
        $this->add(103, 142, self::CAMPO_BRANCO); // 18.1 - Mensagem (G031)
        $this->add(143, 172, Util::formatCnab('X', $this->getPagador()->getEndereco(), 30)); // 19.1 - Nome da rua, av, pça, etc (G032)
        $this->add(173, 177, self::CAMPO_BRANCO); // 20.1 - Número do local (G032)
        $this->add(178, 192, self::CAMPO_BRANCO); // 21.1 - Casa, apto, sala, etc (G032)
        $this->add(193, 212, Util::formatCnab('X', $this->getPagador()->getCidade(), 20)); // 22.1 - Nome da cidade (G033)
        $cep = Util::formatCnab('9L', $this->getPagador()->getCep(), 8);
        $this->add(213, 217, substr($cep, 0, 5)); // 23.1 - CEP (G034)
        $this->add(218, 220, substr($cep, 5, 3)); // 24.1 - Complemento do CEP (G035)
        $this->add(221, 222, Util::formatCnab('X', $this->getPagador()->getUf(), 2)); // 25.1 - Sigla do Estado (G036)
        $this->add(223, 230, self::CAMPO_BRANCO); // 26.1 - Uso exclusivo SICREDI (G004)
        $this->add(231, 240, self::CAMPO_BRANCO); // 27.1 - Códigos das ocorrências p/ retorno (G059)

        return $this;
    }

    /**
     * Trailer do lote para múltiplos lotes
     *
     * @param array $lote
     * @return Sicredi
     * @throws \Exception
     */
    protected function trailerLoteMulti(array $lote)
    {
        $this->iniciaTrailerLote();

        $this->add(1, 3, self::BANCO); // 01.5 - Código do banco na compensação (G001)
        $this->add(4, 7, Util::formatCnab('9L', $lote['numero'], 4)); // 02.5 - Lote de serviço (G002)
        $this->add(8, 8, self::TIPO_REGISTRO_TRAILER_LOTE); // 03.5 - Tipo de registro (G003)
        $this->add(9, 17, self::CAMPO_BRANCO); // 04.5 - Uso exclusivo SICREDI (G004)
        $this->add(18, 23, Util::formatCnab('9L', $this->getCountRegistrosLote(), 6)); // 05.5 - Quantidade de registros do lote (G057)
        $this->add(24, 41, Util::formatCnab('9L', $this->getValorTotalLoteMulti($lote), 18)); // 06.5 - Somatória dos valores (P007)
        $this->add(42, 59, Util::formatCnab('9L', self::QUANTIDADE_MOEDA, 18)); // 07.5 - Somatória de quantidade de moedas (G058)
        $this->add(60, 65, self::CAMPO_BRANCO); // 08.5 - Número aviso de débito (G066)
        $this->add(66, 230, self::CAMPO_BRANCO); // 09.5 - Uso exclusivo SICREDI (G004)
        $this->add(231, 240, self::CAMPO_BRANCO); // 10.5 - Códigos das ocorrências para retorno (G059)

        return $this;
    }

    /**
     * Trailer do arquivo para múltiplos lotes
     *
     * @return Sicredi
     * @throws \Exception
     */
    protected function trailerMulti()
    {
        $this->iniciaTrailer();

        $this->add(1, 3, self::BANCO); // 01.9 - Código do banco na compensação (G001)
        $this->add(4, 7, self::LOTE_SERVICO_TRAILER); // 02.9 - Lote de serviço (G002)
        $this->add(8, 8, self::TIPO_REGISTRO_TRAILER); // 03.9 - Tipo de registro (G003)
        $this->add(9, 17, self::CAMPO_BRANCO); // 04.9 - Uso exclusivo SICREDI (G004)
        $this->add(18, 23, Util::formatCnab('9L', $this->getCountLotes(), 6)); // 05.9 - Quantidade de lotes do arquivo (G049)
        $this->add(24, 29, Util::formatCnab('9L', $this->getCountMulti(), 6)); // 06.9 - Quantidade de registros do arquivo (G056)
        $this->add(30, 35, Util::formatCnab('9L', $this->getCountLotes(), 6)); // 07.9 - Quantidade de contas para conciliação (G037)
        $this->add(36, 240, self::CAMPO_BRANCO); // 08.9 - Uso exclusivo SICREDI (G004)

        return $this;
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
                return '41'; // TED - Transferência entre Clientes (Tipo Serviço 20)
            case 'PIX':
                return '45'; // Transferência via PIX
            case 'DOC':
                return '03'; // DOC (Tipo Serviço 20)
            default:
                return '03';
        }
    }

    /**
     * Retorna o tipo de serviço baseado no tipo de pagamento
     *
     * @param string $tipoPagamento
     * @return string
     */
    protected function getTipoServicoPorTipoPagamento($tipoPagamento)
    {
        switch ($tipoPagamento) {
            case 'TED':
            case 'DOC':
            case 'PIX':
                return '20'; // Pagamento Fornecedor
            case 'TRIBUTOS':
                return '22'; // Pagamento de Contas, Tributos e Impostos
            case 'SALARIO':
                return '30'; // Pagamento Salários/Folha de Pagamento
            default:
                return '20'; // Padrão: Pagamento Fornecedor
        }
    }

    /**
     * Retorna a quantidade total de registros para múltiplos lotes
     *
     * @return int
     */
    protected function getCountMulti()
    {
        $totalRegistros = 0;
        foreach ($this->lotes as $lote) {
            $totalRegistros += count($lote['pagamentos']) * 2;
            $totalRegistros += 2;
        }
        $totalRegistros += 2;
        return $totalRegistros;
    }

    /**
     * Adiciona um segmento A para DOC/TED
     *
     * @param Banco $pagamento
     * @return $this
     */
    public function segmentoA(Banco $pagamento)
    {
        $this->iniciaDetalhe();

        $this->add(1, 3, self::BANCO); // 01.3A - Código do banco na compensação (G001)
        $this->add(4, 7, self::LOTE_SERVICO); // 02.3A - Lote de serviço (G002)
        $this->add(8, 8, self::TIPO_REGISTRO_DETALHE); // 03.3A - Tipo de registro (G003)
        $this->add(9, 13, Util::formatCnab('9L', $this->iRegistrosLote, 5)); // 04.3A - Número sequencial do registro no lote (G038)
        $this->add(14, 14, self::CODIGO_SEGMENTO_A); // 05.3A - Código de segmento do reg. detalhe (G039)
        $this->add(15, 15, self::TIPO_MOVIMENTO); // 06.3A - Tipo de movimento (G060)
        $this->add(16, 17, self::CODIGO_INSTRUCAO_MOVIMENTO); // 07.3A - Código da instrução para movimento (G061)
        $this->add(18, 20, self::CODIGO_CAMARA_CENTRALIZADORA); // 08.3A - Código da câmara centralizadora (P001)
        $this->add(21, 23, Util::formatCnab('9L', $pagamento->getCodigoBanco(), 3)); // 09.3A - Código do banco do favorecido (P002)
        $this->add(24, 28, Util::formatCnab('9L', $pagamento->getAgencia(), 5)); // 10.3A - Agência mantenedora da conta do favorecido (G008)
        $this->add(29, 29, Util::formatCnab('9L', $pagamento->getAgenciaDv(), 1)); // 11.3A - Dígito verificador da agência do favorecido (G009)
        $this->add(30, 41, Util::formatCnab('9L', $pagamento->getConta(), 12)); // 12.3A - Número da conta corrente do favorecido (G010)
        $this->add(42, 42, Util::formatCnab('9L', $pagamento->getContaDv(), 1)); // 13.3A - Dígito verificador da conta do favorecido (G011)
        $this->add(43, 43, self::CAMPO_BRANCO); // 14.3A - Dígito verificador da agência/conta do favorecido (G012)
        $this->add(44, 73, Util::formatCnab('X', $pagamento->getBeneficiario()->getNome(), 30)); // 15.3A - Nome do favorecido (G013)
        $this->add(74, 93, Util::formatCnab('X', $pagamento->getNumeroDocumento() . '-' . $pagamento->getNumeroControle(), 20)); // 16.3A - Número do documento atribuído pela empresa (G064)

        $dataPagamento = $pagamento->getDataPagamento() ? date('dmY', strtotime($pagamento->getDataPagamento())) : date('dmY');
        $this->add(94, 101, $dataPagamento); // 17.3A - Data do pagamento (P009)
        $this->add(102, 104, self::TIPO_MOEDA); // 18.3A - Tipo da moeda (G040)
        $this->add(105, 119, self::QUANTIDADE_MOEDA); // 19.3A - Quantidade da moeda (G041)

        $valor = $pagamento->getValor() ? number_format($pagamento->getValor(), 2, '', '') : '000000000000000';
        $this->add(120, 134, Util::formatCnab('9L', $valor, 15)); // 20.3A - Valor do pagamento (P010)
        $this->add(135, 154, self::CAMPO_BRANCO); // 21.3A - Número do documento atribuído pelo banco (G043)
        $this->add(155, 162, self::DATA_RETORNO_VAZIA); // 22.3A - Data real da efetivação do pagamento (P003)
        $this->add(163, 177, self::VALOR_RETORNO_VAZIO); // 23.3A - Valor real da efetivação do pagamento (P004)
        $this->add(178, 217, self::CAMPO_BRANCO); // 24.3A - Outras informações (G031)
        $this->add(218, 219, '00'); // 25.3A - Complemento tipo serviço (P005)
        $this->add(220, 224, '00005'); // 26.3A - Código finalidade da TED (P011)
        $this->add(225, 226, self::CAMPO_BRANCO); // 27.3A - Complemento de finalidade de pagamento (P013)
        $this->add(227, 229, self::CAMPO_BRANCO); // 28.3A - Uso exclusivo SICREDI (G004)
        $this->add(230, 230, '0'); // 29.3A - Aviso ao favorecido (P006)
        $this->add(231, 240, self::CAMPO_BRANCO); // 29.3A - Códigos das ocorrências para retorno (G059)

        return $this;
    }

    /**
     * Adiciona um segmento B para DOC/TED
     *
     * @param Banco $pagamento
     * @return $this
     */
    public function segmentoB(Banco $pagamento)
    {
        $this->iniciaDetalhe();

        $this->add(1, 3, self::BANCO); // 01.3B - Código do banco na compensação (G001)
        $this->add(4, 7, self::LOTE_SERVICO); // 02.3B - Lote de serviço (G002)
        $this->add(8, 8, self::TIPO_REGISTRO_DETALHE); // 03.3B - Tipo do registro (G003)
        $this->add(9, 13, Util::formatCnab('9L', $this->iRegistrosLote, 5)); // 04.3B - Nº sequencial do registro no lote (G038)
        $this->add(14, 14, self::CODIGO_SEGMENTO_B); // 05.3B - Código de segmento do reg. detalhe (G039)
        $this->add(15, 17, self::CAMPO_BRANCO); // 06.3B - Uso exclusivo SICREDI (G004)
        $this->add(18, 18, Util::formatCnab('9L', $pagamento->getBeneficiario()->getTipoDocumento() == 'CPF' ? self::TIPO_DOCUMENTO_CPF : self::TIPO_DOCUMENTO_CNPJ, 1)); // 07.3B - Tipo de inscrição do favorecido (G005)
        $this->add(19, 32, Util::formatCnab('9L', $pagamento->getBeneficiario()->getDocumento(), 14)); // 08.3B - N° de inscrição do favorecido (G006)
        $this->add(33, 62, Util::formatCnab('X', $pagamento->getBeneficiario()->getEndereco(), 30)); // 09.3B - Nome da rua, av, pça, etc (G032)
        $this->add(63, 67, self::CAMPO_BRANCO); // 10.3B - Nº do local (G032)
        $this->add(68, 82, self::CAMPO_BRANCO); // 11.3B - Casa, apto, etc (G032)
        $this->add(83, 97, Util::formatCnab('X', $pagamento->getBeneficiario()->getBairro(), 15)); // 12.3B - Bairro (G032)
        $this->add(98, 117, Util::formatCnab('X', $pagamento->getBeneficiario()->getCidade(), 20)); // 13.3B - Nome da cidade (G033)

        $cep = Util::formatCnab('9L', $pagamento->getBeneficiario()->getCep(), 8);
        $this->add(118, 122, substr($cep, 0, 5)); // 14.3B - CEP (G034)
        $this->add(123, 125, substr($cep, 5, 3)); // 15.3B - Complemento do CEP (G035)
        $this->add(126, 127, Util::formatCnab('X', $pagamento->getBeneficiario()->getUf(), 2)); // 16.3B - Sigla do Estado (G036)
        $this->add(128, 135, Util::formatCnab('9L', '0', 8)); // 17.3B - Data do vencimento nominal (G044)
        $this->add(136, 150, Util::formatCnab('9L', '0', 15)); // 18.3B - Valor do documento nominal (G042)
        $this->add(151, 165, Util::formatCnab('9L', '0', 15)); // 19.3B - Valor do abatimento (G045)
        $this->add(166, 180, Util::formatCnab('9L', '0', 15)); // 20.3B - Valor do desconto (G046)
        $this->add(181, 195, Util::formatCnab('9L', '0', 15)); // 21.3B - Valor da mora (G047)
        $this->add(196, 210, Util::formatCnab('9L', '0', 15)); // 22.3B - Valor da multa (G048)
        $this->add(211, 225, self::CAMPO_BRANCO); // 23.3B - Código/documento do favorecido (P008)
        $this->add(226, 226, '0'); // 24.3B - Aviso ao favorecido (P006)
        $this->add(227, 232, self::CAMPO_BRANCO); // 25.3B - Código UG Centralizadora para SIAPE (P012)
        $this->add(233, 240, self::CODIGO_ISPB); // 26.3B - Código ISPB (P015)

        return $this;
    }

    /**
     * Gera os segmentos de um pagamento baseado no tipo
     *
     * @param \Eduardokum\LaravelBoleto\Pagamento\Banco\Banco $pagamento
     * @return void
     */
    protected function gerarSegmentos(\Eduardokum\LaravelBoleto\Pagamento\Banco\Banco $pagamento)
    {
        $tipoPagamento = $this->getTipoPagamentoDoPagamento($pagamento);

        switch ($tipoPagamento) {
            case 'TED':
            case 'DOC':
                $this->segmentoA($pagamento);
                $this->segmentoB($pagamento);
                break;
            case 'PIX':
                // TODO: Implementar segmentos para PIX
                break;
            default:
                $this->segmentoA($pagamento);
                $this->segmentoB($pagamento);
                break;
        }
    }

    /**
     * Retorna o valor total de um lote específico
     *
     * @param array $lote
     * @return string
     */
    protected function getValorTotalLoteMulti(array $lote)
    {
        $valorTotal = 0;

        foreach ($lote['pagamentos'] as $pagamento) {
            if (method_exists($pagamento, 'getValor') && $pagamento->getValor() > 0)
                $valorTotal += $pagamento->getValor();
        }

        return Util::formatCnab('9L', $valorTotal * 100, 18);
    }
}
