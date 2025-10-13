<?php

namespace Eduardokum\LaravelBoleto\Cnab\Pagamento\Cnab240\Banco;

use Eduardokum\LaravelBoleto\Cnab\Pagamento\Cnab240\AbstractPagamento;
use Eduardokum\LaravelBoleto\Contracts\Cnab\Pagamento as PagamentoRemessaContract;
use Eduardokum\LaravelBoleto\Contracts\Pagamento\Pagamento as PagamentoContract;
use Eduardokum\LaravelBoleto\Util;

/**
 * Class Bb
 * @package Eduardokum\LaravelBoleto\Cnab\Pagamento\Cnab240\Banco
 */
class Bb extends AbstractPagamento implements PagamentoRemessaContract
{
    const BANCO = '001';
    const CODIGO_ISPB = '00000000';
    const LOTE_SERVICO = '0000';
    const TIPO_REGISTRO = '0';
    const CODIGO_REMESSA = '1';
    const VERSAO_LAYOUT = '107';
    const DENSIDADE_GRAVACAO = '00000';

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

    // Constantes específicas para Banco do Brasil
    const TIPO_INSCRICAO_CPF = '1';
    const TIPO_INSCRICAO_CNPJ = '2';
    const CODIGO_BANCO_BRASIL = '0126';
    const ARQUIVO_TESTE = 'TS';
    const ARQUIVO_PRODUCAO = '';

    // Constantes para header e trailer
    const NOME_BANCO = 'BANCO DO BRASIL';
    const LOTE_SERVICO_TRAILER = '9999';
    const TIPO_REGISTRO_TRAILER = '9';
    const TIPO_REGISTRO_HEADER_LOTE = '1';
    const TIPO_OPERACAO = 'C';
    const VERSAO_LAYOUT_LOTE = '046';
    const TIPO_REGISTRO_TRAILER_LOTE = '5';
    const USO_EXCLUSIVO_VANS = '000';
    const CODIGO_OCORRENCIAS = '0000000000';

    // Tipos de Serviço
    const SERVICO_PAGAMENTO_FORNECEDOR = '20';
    const SERVICO_PAGAMENTO_SALARIO = '30';
    const SERVICO_PAGAMENTOS_DIVERSOS = '98';

    // Formas de Lançamento
    const FORMA_CONTA_CORRENTE = '01';
    const FORMA_DOC_TED = '03';
    const FORMA_POUPANCA = '05';
    const FORMA_TED_OUTRA_TITULARIDADE = '41';
    const FORMA_TED_MESMA_TITULARIDADE = '43';

    // Códigos de Câmara Centralizadora
    const CAMARA_TED_STR_CIP = '018';
    const CAMARA_DOC_COMPE = '700';

    // Códigos de Movimento
    const MOVIMENTO_INCLUSAO = '0';
    const MOVIMENTO_EXCLUSAO = '9';
    const INSTRUCAO_INCLUSAO = '00';
    const INSTRUCAO_EXCLUSAO = '99';

    // Códigos de Finalidade DOC
    const FINALIDADE_DOC_PADRAO = '01';

    // Códigos de Finalidade TED
    const FINALIDADE_TED_PADRAO = '00010';

    // Códigos de Finalidade Complementar
    const FINALIDADE_COMPLEMENTAR_PADRAO = '00';

    // Aviso ao Fornecedor
    const AVISO_FORNECEDOR_NAO = '0';
    const AVISO_FORNECEDOR_SIM = '1';

    // Segmento B
    const AVISO_FAVORECIDO_NAO = '0';
    const AVISO_FAVORECIDO_SIM = '1';

    // Trailer do Lote
    const NUMERO_AVISO_DEBITO = '000000';

    // Trailer do Arquivo
    const TIPO_REGISTRO_TRAILER_ARQUIVO = '9';
    const QUANTIDADE_CONTAS_CONCILIACAO = '000000';

    /**
     * Bb constructor.
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

        // Configurar convênio se fornecido
        if (isset($params['convenio'])) {
            $this->convenio = $params['convenio'];
        }

        // Configurar se é arquivo de teste
        if (isset($params['arquivoTeste'])) {
            $this->arquivoTeste = $params['arquivoTeste'];
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
    protected $tipoServico = '00';

    /**
     * Número do convênio
     * @var string
     */
    protected $convenio = '';

    /**
     * Se é arquivo de teste
     * @var bool
     */
    protected $arquivoTeste = false;

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
     * @return Bb
     * @throws \Exception
     */
    protected function header()
    {
        $this->iniciaHeader();

        $this->add(1, 3, Util::formatCnab('9L', self::BANCO, 3)); // 01.0 - Código do Banco na Compensação (posições 1-3)
        $this->add(4, 7, Util::formatCnab('9L', self::LOTE_SERVICO, 4)); // 02.0 - Lote de Serviço (posições 4-7)
        $this->add(8, 8, Util::formatCnab('9L', self::TIPO_REGISTRO, 1)); // 03.0 - Tipo de Registro (posição 8)
        $this->add(9, 17, Util::formatCnab('X', '', 9)); // 04.0 - Uso Exclusivo FEBRABAN/CNAB (posições 9-17)

        $tipoInscricao = $this->getPagador()->getTipoDocumento() == 'CPF' ? self::TIPO_INSCRICAO_CPF : self::TIPO_INSCRICAO_CNPJ;
        $this->add(18, 18, Util::formatCnab('9L', $tipoInscricao, 1)); // 05.0 - Tipo de Inscrição da Empresa (posição 18)
        $this->add(19, 32, Util::formatCnab('9L', $this->getPagador()->getDocumento(), 14)); // 06.0 - Número de Inscrição da Empresa (posições 19-32)

        $convenioFormatado = Util::formatCnab('9L', $this->convenio, 9); // BB1 - Nº do Convênio (posições 33-41)
        $codigoBanco = Util::formatCnab('9L', self::CODIGO_BANCO_BRASIL, 4); // BB2 - Código (posições 42-45)
        $usoReservado = Util::formatCnab('X', '', 5); // BB3 - Uso Reservado do Banco (posições 46-50)
        $arquivoTeste = $this->arquivoTeste ? self::ARQUIVO_TESTE : Util::formatCnab('X', '', 2); // BB4 - Arquivo de teste (posições 51-52)
        $this->add(33, 52, $convenioFormatado . $codigoBanco . $usoReservado . $arquivoTeste); // 07.0 - Código do Convênio no Banco (posições 33-52)

        $this->add(53, 57, Util::formatCnab('9L', $this->getAgencia(), 5)); // 08.0 - Agência Mantenedora da Conta (posições 53-57)
        $agenciaDv = $this->getAgenciaDv() ?: '';
        $this->add(58, 58, Util::formatCnab('X', $agenciaDv, 1)); // 09.0 - Dígito Verificador da Agência (posição 58)
        $this->add(59, 70, Util::formatCnab('9L', $this->getConta(), 12)); // 10.0 - Número da Conta Corrente (posições 59-70)
        $contaDv = $this->getContaDv() ?: '';
        $this->add(71, 71, Util::formatCnab('X', $contaDv, 1)); // 11.0 - Dígito Verificador da Conta (posição 71)
        $this->add(72, 72, Util::formatCnab('X', '0', 1)); // 12.0 - Dígito Verificador da Ag/Conta (posição 72)
        $this->add(73, 102, Util::formatCnab('X', $this->getPagador()->getNome(), 30)); // 13.0 - Nome da Empresa (posições 73-102)
        $this->add(103, 132, Util::formatCnab('X', self::NOME_BANCO, 30)); // 14.0 - Nome do Banco (posições 103-132)
        $this->add(133, 142, Util::formatCnab('X', '', 10)); // 15.0 - Uso Exclusivo FEBRABAN/CNAB (posições 133-142)
        $this->add(143, 143, Util::formatCnab('9L', self::CODIGO_REMESSA, 1)); // 16.0 - Código Remessa/Retorno (posição 143)
        $this->add(144, 151, $this->getDataRemessa('dmY')); // 17.0 - Data de Geração do Arquivo (posições 144-151)
        $this->add(152, 157, $this->getDataRemessa('His')); // 18.0 - Hora de Geração do Arquivo (posições 152-157)
        $this->add(158, 163, Util::formatCnab('9L', $this->getIdremessa(), 6)); // 19.0 - Número Sequencial do Arquivo (posições 158-163)
        $this->add(164, 166, Util::formatCnab('9L', self::VERSAO_LAYOUT, 3)); // 20.0 - Nº Versão do Layout do Arquivo (posições 164-166)
        $this->add(167, 171, Util::formatCnab('9L', self::DENSIDADE_GRAVACAO, 5)); // 21.0 - Densidade de Gravação do Arquivo (posições 167-171)
        $this->add(172, 191, Util::formatCnab('X', '', 20)); // 22.0 - Para Uso Reservado do Banco (posições 172-191)
        $this->add(192, 211, Util::formatCnab('X', '', 20)); // 23.0 - Para Uso Reservado da Empresa (posições 192-211)
        $this->add(212, 222, Util::formatCnab('X', '', 11)); // 24.0 - Para Uso Exclusivo FEBRABAN CNAB (posições 212-222)
        $this->add(223, 225, Util::formatCnab('X', '', 3)); // 25.0 - Identificação cobrança sem papel (posições 223-225)
        $this->add(226, 228, Util::formatCnab('9L', self::USO_EXCLUSIVO_VANS, 3)); // 26.0 - Uso exclusivo das VANS (posições 226-228)
        $this->add(229, 230, Util::formatCnab('9L', '00', 2)); // 27.0 - Tipo de Serviço (posições 229-230)
        $this->add(231, 240, Util::formatCnab('X', self::CODIGO_OCORRENCIAS, 10)); // 28.0 - Códigos de Ocorrências (posições 231-240)

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
        return self::NOME_BANCO;
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

        // Soma todos os valores dos pagamentos no lote
        foreach ($this->pagamentos as $pagamento) {
            if (method_exists($pagamento, 'getValor') && $pagamento->getValor() > 0) {
                $valorTotal += $pagamento->getValor();
            }
        }

        return Util::formatCnab('9L', $valorTotal * 100, 18);
    }

    /**
     * Retorna a quantidade total de registros do arquivo
     * @return int
     */
    protected function getCount()
    {
        $countDetalhes = $this->getCountDetalhes();
        return 1 + 1 + $countDetalhes + 1 + 1; // Header arquivo + Header lote + Detalhes + Trailer lote + Trailer arquivo
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
        return $this->tipoServico ?? '00'; // Padrão para Banco do Brasil
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
            case 'DOC':
                return '03'; // DOC
            case 'PIX':
                return '41'; // PIX - TED Outra Titularidade
            case 'TRANSFERENCIA_PROPRIO':
                return '01'; // Conta Corrente
            default:
                return '03'; // Padrão TED
        }
    }

    /**
     * Adiciona um pagamento à remessa
     *
     * @param PagamentoContract $pagamento
     * @return Bb
     * @throws \Eduardokum\LaravelBoleto\Exception\ValidationException
     */
    public function addPagamento(PagamentoContract $pagamento)
    {
        $this->pagamentos[] = $pagamento;
        return $this;
    }

    /**
     * Gera os segmentos baseado no tipo de pagamento
     *
     * @param \Eduardokum\LaravelBoleto\Pagamento\Banco\Banco $pagamento
     * @return Bb
     * @throws \Exception
     */
    protected function gerarSegmentos(\Eduardokum\LaravelBoleto\Pagamento\Banco\Banco $pagamento)
    {
        $this->segmentoA($pagamento);
        $this->segmentoB($pagamento);

        return $this;
    }

    /**
     * Adiciona um segmento A
     *
     * @param PagamentoContract $pagamento
     * @return Bb
     * @throws \Exception
     */
    public function segmentoA($pagamento)
    {
        $this->iniciaDetalhe();

        $this->add(1, 3, self::BANCO); // 01.3A - Código do Banco na Compensação (posições 1-3)
        $this->add(4, 7, self::LOTE_SERVICO); // 02.3A - Lote de Serviço (posições 4-7)
        $this->add(8, 8, self::TIPO_REGISTRO_DETALHE); // 03.3A - Tipo de Registro (posição 8)
        $this->add(9, 13, Util::formatCnab('9L', $this->iRegistrosLote + 1, 5)); // 04.3A - Nº Seqüencial do Registro no Lote (posições 9-13)
        $this->add(14, 14, self::CODIGO_SEGMENTO_A); // 05.3A - Código de Segmento no Reg. Detalhe (posição 14)
        $this->add(15, 15, self::MOVIMENTO_INCLUSAO); // 06.3A - Tipo de Movimento (posição 15)
        $this->add(16, 17, self::INSTRUCAO_INCLUSAO); // 07.3A - Código da Instrução p/ Movimento (posições 16-17)

        // 08.3A - Código da Câmara Centralizadora (posições 18-20)
        $camara = $this->getTipoPagamento() == 'DOC' ? self::CAMARA_DOC_COMPE : self::CAMARA_TED_STR_CIP;
        $this->add(18, 20, $camara);

        $this->add(21, 23, Util::formatCnab('9L', $pagamento->getCodigoBanco(), 3)); // 09.3A - Código do Banco Favorecido (posições 21-23)
        $this->add(24, 28, '00000'); // 10.3A - Ag. Mantenedora da Conta Favorec. (posições 24-28)
        $this->add(29, 29, ''); // 11.3A - Dígito Verificador da Agência (posição 29)
        $this->add(30, 41, '000000000000'); // 12.3A - Número da Conta Corrente (posições 30-41)
        $this->add(42, 42, ''); // 13.3A - Dígito Verificador da Conta Corren. (posição 42)
        $this->add(43, 43, ''); // 14.3A - Dígito Verificador Agência/Conta (posição 43)
        $this->add(44, 73, Util::formatCnab('X', $pagamento->getBeneficiario()->getNome(), 30)); // 15.3A - Nome do Favorecido (posições 44-73)
        $this->add(74, 93, Util::formatCnab('X', '', 20)); // 16.3A - N° do Docto Atribuído pela Empresa (posições 74-93)

        $dataPagamento = date('dmY');
        $this->add(94, 101, $dataPagamento); // 17.3A - Data do Pagamento (posições 94-101)
        $this->add(102, 104, self::TIPO_MOEDA); // 18.3A - Tipo da Moeda (posições 102-104)
        $this->add(105, 119, self::QUANTIDADE_MOEDA); // 19.3A - Quantidade da Moeda (posições 105-119)

        $valor = $pagamento->getValor() ? number_format($pagamento->getValor(), 2, '', '') : '0000000000000';
        $this->add(120, 134, Util::formatCnab('9L', $valor, 15)); // 20.3A - Valor do Pagamento (posições 120-134)
        $this->add(135, 154, ''); // 21.3A - N° do Docto Atribuído pelo Banco (posições 135-154)
        $this->add(155, 162, '00000000'); // 22.3A - Data Real da Efetivação do Pagto (posições 155-162)
        $this->add(163, 177, '000000000000000'); // 23.3A - Valor Real da Efetivação do Pagto (posições 163-177)
        $this->add(178, 217, ''); // 24.3A - Outras Informações (posições 178-217)
        $this->add(218, 219, self::FINALIDADE_DOC_PADRAO); // 25.3A - Compl. Tipo Serviço (posições 218-219)
        $this->add(220, 224, self::FINALIDADE_TED_PADRAO); // 26.3A - Código Finalidade da TED (posições 220-224)
        $this->add(225, 226, self::FINALIDADE_COMPLEMENTAR_PADRAO); // 27.3A - Complemente de Finalidade Pagto (posições 225-226)
        $this->add(227, 229, ''); // 28.3A - Uso Exclusivo Febraban (posições 227-229)
        $this->add(230, 230, self::AVISO_FORNECEDOR_NAO); // 29.3A - Aviso ao Fornecedor (posição 230)
        $this->add(231, 240, '0000000000'); // 30.3A - Código das Ocorrências p/ Retorno (posições 231-240)

        $this->iRegistrosLote++;
        return $this;
    }

    /**
     * Adiciona um segmento B
     *
     * @param PagamentoContract $pagamento
     * @return Bb
     * @throws \Exception
     */
    public function segmentoB($pagamento)
    {
        $this->iniciaDetalhe();

        $this->add(1, 3, self::BANCO); // 01.3B - Código do Banco na Compensação (posições 1-3)
        $this->add(4, 7, self::LOTE_SERVICO); // 02.3B - Lote de Serviço (posições 4-7)
        $this->add(8, 8, self::TIPO_REGISTRO_DETALHE); // 03.3B - Tipo de Registro (posição 8)
        $this->add(9, 13, Util::formatCnab('9L', $this->iRegistrosLote + 1, 5)); // 04.3B - Nº Seqüencial do Registro no Lote (posições 9-13)
        $this->add(14, 14, self::CODIGO_SEGMENTO_B); // 05.3B - Código de Segmento do Reg. Detalhe (posição 14)
        $this->add(15, 17, ''); // 06.3B - Uso Exclusivo FEBRABAN/CNAB (posições 15-17)

        // 07.3B - Tipo de Inscrição do Favorecido (posição 18)
        $tipoInscricao = $pagamento->getBeneficiario()->getTipoDocumento() == 'CPF' ? self::TIPO_INSCRICAO_CPF : self::TIPO_INSCRICAO_CNPJ;
        $this->add(18, 18, Util::formatCnab('9L', $tipoInscricao, 1));

        $this->add(19, 32, Util::formatCnab('9L', $pagamento->getBeneficiario()->getDocumento(), 14)); // 08.3B - Nº de Inscrição do Favorecido (posições 19-32)
        $this->add(33, 62, Util::formatCnab('X', $pagamento->getBeneficiario()->getEndereco(), 30)); // 09.3B - Nome da Rua, Av, Pça, Etc (posições 33-62)
        $this->add(63, 67, '00000'); // 10.3B - Nº do Local (posições 63-67)
        $this->add(68, 82, ''); // 11.3B - Casa, Apto, Etc (posições 68-82)
        $this->add(83, 97, Util::formatCnab('X', $pagamento->getBeneficiario()->getBairro(), 15)); // 12.3B - Nome do Bairro (posições 83-97)
        $this->add(98, 117, Util::formatCnab('X', $pagamento->getBeneficiario()->getCidade(), 20)); // 13.3B - Nome da Cidade (posições 98-117)

        $cep = $pagamento->getBeneficiario()->getCep();
        $cepFormatado = Util::formatCnab('9L', $cep, 8);
        $this->add(118, 122, substr($cepFormatado, 0, 5)); // 14.3B - CEP (posições 118-122)
        $this->add(123, 125, substr($cepFormatado, 5, 3)); // 15.3B - Complemento do CEP (posições 123-125)
        $this->add(126, 127, Util::formatCnab('X', $pagamento->getBeneficiario()->getUf(), 2)); // 16.3B - Sigla do Estado (posições 126-127)
        $this->add(128, 135, '00000000'); // 17.3B - Data do Vencimento (Nominal) (posições 128-135)
        $this->add(136, 150, '000000000000000'); // 18.3B - Valor do Documento (Nominal) (posições 136-150)
        $this->add(151, 165, '000000000000000'); // 19.3B - Valor do Abatimento (posições 151-165)
        $this->add(166, 180, '000000000000000'); // 20.3B - Valor do Desconto (posições 166-180)
        $this->add(181, 195, '000000000000000'); // 21.3B - Valor da Mora (posições 181-195)
        $this->add(196, 210, '000000000000000'); // 22.3B - Valor da Multa (posições 196-210)
        $this->add(211, 225, ''); // 23.3B - Código/Documento do Favorecido (posições 211-225)
        $this->add(226, 226, self::AVISO_FAVORECIDO_NAO); // 24.3B - Aviso ao Favorecido (posição 226)
        $this->add(227, 232, '000000'); // 25.3B - Uso Exclusivo para o SIAPE (posições 227-232)
        $this->add(233, 240, ''); // 26.3B - Código ISPB (posições 233-240)

        $this->iRegistrosLote++;
        return $this;
    }

    /**
     * Gera o header do lote (multilote)
     *
     * @return Bb
     * @throws \Eduardokum\LaravelBoleto\Exception\ValidationException
     */
    protected function headerLote()
    {
        $this->iniciaHeaderLote();

        $this->add(1, 3, self::BANCO); // Código do banco na compensação
        $this->add(4, 7, self::LOTE_SERVICO); // Lote de serviço
        $this->add(8, 8, self::TIPO_REGISTRO_HEADER_LOTE); // Tipo de registro
        $this->add(9, 9, self::TIPO_OPERACAO); // Tipo da operação
        $this->add(10, 11, $this->getTipoServico()); // Tipo do serviço
        $this->add(12, 13, $this->getFormaLancamento()); // Forma de lançamento
        $this->add(14, 16, self::VERSAO_LAYOUT_LOTE); // Número da versão do layout do Lote
        $this->add(17, 17, ''); // Campo em branco
        $this->add(18, 18, Util::formatCnab('9L', $this->getPagador()->getTipoDocumento() == 'CPF' ? self::TIPO_INSCRICAO_CPF : self::TIPO_INSCRICAO_CNPJ, 1)); // Tipo de documento da empresa
        $this->add(19, 32, Util::formatCnab('9L', $this->getPagador()->getDocumento(), 14)); // CPF/CNPJ da empresa

        $this->add(33, 41, Util::formatCnab('9L', $this->convenio, 9));
        $this->add(42, 45, Util::formatCnab('9L', self::CODIGO_BANCO_BRASIL, 4));

        $this->add(46, 50, Util::formatCnab('X', '', 5));
        $arquivoTeste = $this->arquivoTeste ? self::ARQUIVO_TESTE : '';
        $this->add(51, 52, Util::formatCnab('X', $arquivoTeste, 2));

        $this->add(53, 57, Util::formatCnab('9L', $this->getAgencia(), 5)); // Agência mantenedora da conta da empresa
        $agenciaDv = $this->getAgenciaDv() ?: '';
        $this->add(58, 58, Util::formatCnab('X', $agenciaDv, 1)); // Dígito verificador da agência
        $this->add(59, 70, Util::formatCnab('9L', $this->getConta(), 12)); // Número da conta corrente da empresa
        $contaDv = $this->getContaDv() ?: '';
        $this->add(71, 71, Util::formatCnab('X', $contaDv, 1)); // Dígito verificador da conta
        $this->add(72, 72, Util::formatCnab('X', '0', 1)); // Dígito verificador da agência/conta
        $this->add(73, 102, Util::formatCnab('X', $this->getPagador()->getNome(), 30)); // Nome da empresa
        $this->add(103, 142, ''); // Informação genérica opcional
        $this->add(143, 172, Util::formatCnab('X', $this->getPagador()->getEndereco(), 30)); // Nome da Rua, Av, Pça, Etc.
        $this->add(173, 177, ''); // Número do local da empresa
        $this->add(178, 192, ''); // Casa, Apto, Sala, Etc.
        $this->add(193, 212, Util::formatCnab('X', $this->getPagador()->getCidade(), 20)); // Nome da cidade da empresa

        $cep = Util::formatCnab('9L', $this->getPagador()->getCep(), 8);
        $this->add(213, 217, substr($cep, 0, 5)); // CEP da empresa
        $this->add(218, 220, substr($cep, 5, 3)); // Complemento do CEP
        $this->add(221, 222, $this->getPagador()->getUf()); // Sigla do estado da empresa
        $this->add(223, 230, ''); // Campo em branco
        $this->add(231, 240, self::CODIGO_OCORRENCIAS); // Códigos das ocorrências para retorno

        return $this;
    }

    /**
     * Gera o trailer do lote (multilote)
     *
     * @return Bb
     * @throws \Eduardokum\LaravelBoleto\Exception\ValidationException
     */
    protected function trailerLote()
    {
        $this->iniciaTrailerLote();

        $this->add(1, 3, self::BANCO); // 01.5 - Código do Banco na Compensação (posições 1-3)
        $this->add(4, 7, self::LOTE_SERVICO); // 02.5 - Lote de Serviço (posições 4-7)
        $this->add(8, 8, self::TIPO_REGISTRO_TRAILER_LOTE); // 03.5 - Tipo de Registro (posição 8)
        $this->add(9, 17, ''); // 04.5 - Uso Exclusivo FEBRABAN/CNAB (posições 9-17)
        $this->add(18, 23, Util::formatCnab('9L', $this->getCountRegistrosLote(), 6)); // 05.5 - Quantidade de Registros do Lote (posições 18-23)
        $this->add(24, 41, Util::formatCnab('9L', $this->getValorTotalLote(), 18)); // 06.5 - Somatória dos Valores (posições 24-41)
        $this->add(42, 59, self::QUANTIDADE_MOEDA); // 07.5 - Somatória de Quantidade de Moedas (posições 42-59)
        $this->add(60, 65, self::NUMERO_AVISO_DEBITO); // 08.5 - Número Aviso Débito (posições 60-65)
        $this->add(66, 230, ''); // 09.5 - Uso Exclusivo FEBRABAN/CNAB (posições 66-230)
        $this->add(231, 240, self::CODIGO_OCORRENCIAS); // 10.5 - Códigos das Ocorrências para Retorno (posições 231-240)

        return $this;
    }

    /**
     * Gera o trailer do arquivo (multilote)
     *
     * @return Bb
     * @throws \Eduardokum\LaravelBoleto\Exception\ValidationException
     */
    protected function trailer()
    {
        $this->iniciaTrailer();

        $this->add(1, 3, self::BANCO); // 01.9 - Código do Banco na Compensação (posições 1-3)
        $this->add(4, 7, self::LOTE_SERVICO_TRAILER); // 02.9 - Lote de Serviço (posições 4-7)
        $this->add(8, 8, self::TIPO_REGISTRO_TRAILER_ARQUIVO); // 03.9 - Tipo de Registro (posição 8)
        $this->add(9, 17, ''); // 04.9 - Uso Exclusivo FEBRABAN/CNAB (posições 9-17)
        $this->add(18, 23, Util::formatCnab('9L', $this->getCountLotes(), 6)); // 05.9 - Quantidade de Lotes do Arquivo (posições 18-23)
        $this->add(24, 29, Util::formatCnab('9L', $this->getCount(), 6)); // 06.9 - Quantidade de Registros do Arquivo (posições 24-29)
        $this->add(30, 35, self::QUANTIDADE_CONTAS_CONCILIACAO); // 07.9 - Quantidade de Contas p/ Conc. (Lotes) (posições 30-35)
        $this->add(36, 240, ''); // 08.9 - Uso Exclusivo FEBRABAN/CNAB (posições 36-240)

        return $this;
    }



    /**
     * Header do lote para múltiplos lotes
     *
     * @param array $lote
     * @return Bb
     * @throws \Exception
     */
    protected function headerLoteMulti(array $lote)
    {
        $this->iniciaHeaderLote();

        $this->add(1, 3, self::BANCO); // Código do banco na compensação
        $this->add(4, 7, Util::formatCnab('9L', $lote['numero'], 4)); // Lote de serviço (número do lote)
        $this->add(8, 8, self::TIPO_REGISTRO_HEADER_LOTE); // Tipo de registro
        $this->add(9, 9, self::TIPO_OPERACAO); // Tipo da operação
        $this->add(10, 11, $this->getTipoServico()); // Tipo do serviço
        $this->add(12, 13, $this->getFormaLancamentoPorTipo($lote['tipo'])); // Forma de lançamento (varia por tipo)
        $this->add(14, 16, self::VERSAO_LAYOUT_LOTE); // Número da versão do layout do Lote
        $this->add(17, 17, ''); // Campo em branco
        $this->add(18, 18, Util::formatCnab('9L', $this->getPagador()->getTipoDocumento() == 'CPF' ? self::TIPO_INSCRICAO_CPF : self::TIPO_INSCRICAO_CNPJ, 1)); // Tipo de documento da empresa
        $this->add(19, 32, Util::formatCnab('9L', $this->getPagador()->getDocumento(), 14)); // CPF/CNPJ da empresa

        $this->add(33, 41, Util::formatCnab('9L', $this->convenio, 9));
        $this->add(42, 45, Util::formatCnab('9L', self::CODIGO_BANCO_BRASIL, 4));

        $this->add(46, 50, Util::formatCnab('X', '', 5));
        $arquivoTeste = $this->arquivoTeste ? self::ARQUIVO_TESTE : '';
        $this->add(51, 52, Util::formatCnab('X', $arquivoTeste, 2));

        $this->add(53, 57, Util::formatCnab('9L', $this->getAgencia(), 5)); // Agência mantenedora da conta da empresa
        $agenciaDv = $this->getAgenciaDv() ?: '';
        $this->add(58, 58, Util::formatCnab('X', $agenciaDv, 1)); // Dígito verificador da agência
        $this->add(59, 70, Util::formatCnab('9L', $this->getConta(), 12)); // Número da conta corrente da empresa
        $contaDv = $this->getContaDv() ?: '';
        $this->add(71, 71, Util::formatCnab('X', $contaDv, 1)); // Dígito verificador da conta
        $this->add(72, 72, Util::formatCnab('X', '0', 1)); // Dígito verificador da agência/conta
        $this->add(73, 102, Util::formatCnab('X', $this->getPagador()->getNome(), 30)); // Nome da empresa
        $this->add(103, 142, ''); // Informação genérica opcional
        $this->add(143, 172, Util::formatCnab('X', $this->getPagador()->getEndereco(), 30)); // Nome da Rua, Av, Pça, Etc.
        $this->add(173, 177, ''); // Número do local da empresa
        $this->add(178, 192, ''); // Casa, Apto, Sala, Etc.
        $this->add(193, 212, Util::formatCnab('X', $this->getPagador()->getCidade(), 20)); // Nome da cidade da empresa

        $cep = Util::formatCnab('9L', $this->getPagador()->getCep(), 8);
        $this->add(213, 217, substr($cep, 0, 5)); // CEP da empresa
        $this->add(218, 220, substr($cep, 5, 3)); // Complemento do CEP
        $this->add(221, 222, $this->getPagador()->getUf()); // Sigla do estado da empresa
        $this->add(223, 230, ''); // Campo em branco
        $this->add(231, 240, self::CODIGO_OCORRENCIAS); // Códigos das ocorrências para retorno

        return $this;
    }

    /**
     * Trailer do lote para múltiplos lotes
     *
     * @param array $lote
     * @return Bb
     * @throws \Exception
     */
    protected function trailerLoteMulti(array $lote)
    {
        $this->iniciaTrailerLote();

        $this->add(1, 3, self::BANCO); // Código do banco na compensação
        $this->add(4, 7, Util::formatCnab('9L', $lote['numero'], 4)); // Lote de serviço (Número do lote)
        $this->add(8, 8, self::TIPO_REGISTRO_TRAILER_LOTE); // Tipo de registro
        $this->add(9, 17, ''); // Campo em branco
        $this->add(18, 23, Util::formatCnab('9L', $this->getCountRegistrosLote(), 6)); // Quantidade de lotes no arquivo
        $this->add(24, 41, Util::formatCnab('9L', $this->getValorTotalLoteMulti($lote), 18)); // Somatória dos valores
        $this->add(42, 59, Util::formatCnab('9L', self::QUANTIDADE_MOEDA, 18)); // Somatória de quantidade de moedas
        $this->add(60, 65, ''); // Número aviso de débito
        $this->add(66, 230, ''); // Campo em branco
        $this->add(231, 240, self::CODIGO_OCORRENCIAS); // Códigos das ocorrências para retorno

        return $this;
    }

    /**
     * Trailer do arquivo para múltiplos lotes
     *
     * @return Bb
     * @throws \Exception
     */
    protected function trailerMulti()
    {
        $this->iniciaTrailer();

        $this->add(1, 3, self::BANCO); // Código do banco na compensação
        $this->add(4, 7, self::LOTE_SERVICO_TRAILER); // Lote de serviço
        $this->add(8, 8, self::TIPO_REGISTRO_TRAILER); // Tipo de registro
        $this->add(9, 17, ''); // Campo em branco
        $this->add(18, 23, Util::formatCnab('9L', $this->getCountLotes(), 6)); // Quantidade de lotes do arquivo
        $this->add(24, 29, Util::formatCnab('9L', $this->getCountMulti(), 6)); // Quantidade de registros do arquivo
        $this->add(30, 240, ''); // Campo em branco

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
                return '03'; // TED
            case 'DOC':
                return '03'; // DOC
            case 'PIX':
                return '41'; // PIX - TED Outra Titularidade
            default:
                return '03'; // Padrão TED
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
            $totalRegistros += count($lote['pagamentos']) * 2; // Segmento A + B para cada pagamento
            $totalRegistros += 2; // Header + Trailer do lote
        }
        $totalRegistros += 2; // Header + Trailer do arquivo
        return $totalRegistros;
    }

    /**
     * Retorna o valor total do lote para múltiplos lotes
     *
     * @param array $lote
     * @return string
     */
    protected function getValorTotalLoteMulti(array $lote)
    {
        $valorTotal = 0;

        // Soma todos os valores dos pagamentos no lote
        foreach ($lote['pagamentos'] as $pagamento) {
            if (method_exists($pagamento, 'getValor') && $pagamento->getValor() > 0) {
                $valorTotal += $pagamento->getValor();
            }
        }

        return Util::formatCnab('9L', $valorTotal * 100, 18);
    }
}
