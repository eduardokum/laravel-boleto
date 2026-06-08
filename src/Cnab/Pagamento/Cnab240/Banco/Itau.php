<?php

namespace Eduardokum\LaravelBoleto\Cnab\Pagamento\Cnab240\Banco;

use Eduardokum\LaravelBoleto\Cnab\Pagamento\Cnab240\AbstractPagamento;
use Eduardokum\LaravelBoleto\Contracts\Cnab\Pagamento as PagamentoRemessaContract;
use Eduardokum\LaravelBoleto\Contracts\Pagamento\Pagamento as PagamentoContract;
use Eduardokum\LaravelBoleto\Exception\ValidationException;
use Eduardokum\LaravelBoleto\Pagamento\AbstractPagamento as PagamentoBase;
use Eduardokum\LaravelBoleto\Pagamento\Banco\Banco;
use Eduardokum\LaravelBoleto\Util;

/**
 * Class Itau
 * @package Eduardokum\LaravelBoleto\Cnab\Pagamento\Cnab240\Banco
 */
class Itau extends AbstractPagamento implements PagamentoRemessaContract
{
    const BANCO = '341'; // Código do Itaú na compensação
    const LOTE_SERVICO = '0000'; // Lote de serviço (header do arquivo)
    const TIPO_REGISTRO = '0'; // Tipo de registro (header do arquivo)
    const TIPO_DOCUMENTO_CPF = '1'; // CPF
    const TIPO_DOCUMENTO_CNPJ = '2'; // CNPJ
    const CODIGO_REMESSA = '1'; // Código de remessa (1=REMESSA, 2=RETORNO)
    const VERSAO_LAYOUT = '080'; // Versão do layout do arquivo (conforme especificação)
    const NOME_BANCO = 'ITAU'; // Nome do banco
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
    const FORMA_LANCAMENTO_BOLETO_ITAU = '30'; // Forma de lançamento (Pagamento de título em cobrança no Itaú)
    const FORMA_LANCAMENTO_BOLETO_OUTROS = '31'; // Forma de lançamento (Pagamento de título em cobrança em outros bancos)
    const VERSAO_LAYOUT_LOTE = '045'; // Versão do layout do lote
    const VERSAO_LAYOUT_LOTE_BOLETO = '030'; // Versão do layout do lote para boletos (segmento J/J-52)

    // Tipos de pagamento (agrupamento interno)
    const TIPO_PAGAMENTO_TED = 'TED';
    const TIPO_PAGAMENTO_PIX = 'PIX';
    const TIPO_PAGAMENTO_BOLETO = 'BOLETO';         // legado — não mais usado como agrupador
    const TIPO_PAGAMENTO_BOLETO_ITAU = 'BOLETO_ITAU';    // boletos banco 341 → Form 30
    const TIPO_PAGAMENTO_BOLETO_OUTROS = 'BOLETO_OUTROS'; // boletos outros bancos → Form 31

    // Tipo de serviço para fornecedores (NOTA 4)
    const TIPO_SERVICO_FORNECEDOR = '20';

    // Segmento J / J-52 (liquidação de boletos - NOTAS 18 e 31)
    const CODIGO_SEGMENTO_J = 'J';
    const CODIGO_REGISTRO_OPCIONAL_J52 = '52';

    // Segmento O (arrecadação - concessionárias e tributos com código de barras)
    const CODIGO_SEGMENTO_O = 'O';
    const TIPO_SERVICO_TRIBUTOS = '22';                  // NOTA 4: Tipo de Pagamento = Tributos
    const VERSAO_LAYOUT_LOTE_TRIBUTO = '030';            // Layout do lote para tributos com barras
    const FORMA_LANCAMENTO_CONCESSIONARIA = '13';        // NOTA 5: Saneamento/Energia/Telecom (barcode segmento 2/3/4)
    const FORMA_LANCAMENTO_IPTU = '19';                  // NOTA 5: IPTU/ISS/outros tributos municipais (barcode segmento 1)
    const FORMA_LANCAMENTO_GNRE = '91';                  // NOTA 5: GNRE e tributos com código de barras (barcode segmento 9)
    const MOEDA_REAL_SEGMENTO_O = 'REA';                 // pos 104-106 do segmento O

    // Tipos de pagamento (agrupador interno) — arrecadação. Cada tipo vira um
    // lote separado (manual SISPAG pág 7: "Um lote de serviço só pode conter
    // pagamentos de um único tipo e uma única forma").
    const TIPO_PAGAMENTO_ARRECADACAO_IPTU = 'ARRECADACAO_IPTU';
    const TIPO_PAGAMENTO_ARRECADACAO_CONCESSIONARIA = 'ARRECADACAO_CONCESSIONARIA';
    const TIPO_PAGAMENTO_ARRECADACAO_GNRE = 'ARRECADACAO_GNRE';

    // Constantes para trailer do lote
    const LOTE_SERVICO_TRAILER_LOTE = '0001'; // Lote de serviço (trailer do lote)
    const TIPO_REGISTRO_TRAILER_LOTE = '5'; // Tipo de registro (trailer do lote)
    const QUANTIDADE_MOEDA_ZERO = 0; // Quantidade de moeda (geralmente 0 para pagamentos)

    // Constantes para segmentos de detalhe
    const TIPO_REGISTRO_DETALHE = '3'; // Tipo de registro (detalhe)
    const CODIGO_SEGMENTO_A = 'A'; // Código do segmento A
    const CODIGO_SEGMENTO_B = 'B'; // Código do segmento B
    const TIPO_MOVIMENTO = '0'; // Tipo de movimento
    const CODIGO_INSTRUCAO_MOVIMENTO = '00'; // Código da instrução para movimento
    const CODIGO_CAMARA_CENTRALIZADORA = '000'; // Código da câmara centralizadora
    const TIPO_MOEDA = 'BRL'; // Tipo da moeda (Real brasileiro)
    const QUANTIDADE_MOEDA = '000000000000000'; // Quantidade da moeda (15 zeros)
    const AVISO_FAVORECIDO = '0'; // Aviso ao favorecido

    // Constantes para tipos de chave PIX (NOTA 37 - segmento B PIX, posições 15-16).
    // Aplicável apenas quando o tipo de transferência é "04" (Chave Pix).
    const TIPO_CHAVE_PIX_CELULAR = '01'; // Celular
    const TIPO_CHAVE_PIX_EMAIL = '02'; // E-mail
    const TIPO_CHAVE_PIX_CPF_CNPJ = '03'; // CPF/CNPJ
    const TIPO_CHAVE_PIX_ALEATORIA = '04'; // Chave Aleatória

    // Identificação do Tipo de Transferência PIX (NOTA 36 - segmento A, posições 113-114).
    // Define se o PIX é por dados bancários (CC/Pagamento/Poupança) ou por chave.
    const TIPO_TRANSFER_PIX_CC = '01';        // Conta Corrente
    const TIPO_TRANSFER_PIX_PAGAMENTO = 'PG'; // Conta Pagamento
    const TIPO_TRANSFER_PIX_POUPANCA = '03';  // Conta Poupança
    const TIPO_TRANSFER_PIX_CHAVE = '04';     // Chave Pix

    // Câmara centralizadora (NOTA 35)
    const CAMARA_PIX = '009'; // PIX (SPI)
    const CAMARA_TED_CORRETORA = '888'; // TED para corretora (STR)

    /**
     * Itau constructor.
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
     * Tipo de pagamento (TED, DOC, PIX, etc.)
     * @var string
     */
    protected $tipoPagamento = 'TED';

    /**
     * Número do lote atualmente sendo processado pelo gerador. Setado pelo
     * headerLoteMulti() para que os segmentos (A, B, J, J-52) gravem o mesmo
     * número nas posições 4-7. Sem isso, todos os segmentos cairiam no lote
     * "0001" mesmo quando o arquivo tem múltiplos lotes (ex.: TED + Boleto).
     * @var int
     */
    protected $loteAtualNumero = 1;

    /**
     * Sequencial do pagamento dentro do lote (NOTA 9). Conta pagamentos —
     * não segmentos. O segmento principal (A ou J) incrementa e grava nas
     * posições 9-13; os complementares (B, B-PIX, J-52) reutilizam o mesmo
     * número, conforme: "Para o Segmento 'J-52', 'B', 'C', 'D', 'E', 'F',
     * 'W' e 'Z', por se tratar de complemento de informações, conterá o
     * mesmo número atribuído no Segmento 'A', 'J' e 'N' correspondente."
     *
     * Resetado no início de cada lote (headerLote / headerLoteMulti).
     *
     * @var int
     */
    protected $iSequencialPagamento = 0;

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
     * Cria o header do arquivo CNAB 240 conforme especificação do Itaú
     * @return Itau
     * @throws \Exception
     */
    protected function header()
    {
        $this->iniciaHeader();

        $this->add(1, 3, self::BANCO); // Posição 001-003: Código do Banco na Compensação (341)
        $this->add(4, 7, self::LOTE_SERVICO); // Posição 004-007: Lote de Serviço (0000)
        $this->add(8, 8, self::TIPO_REGISTRO); // Posição 008-008: Tipo de Registro (0)
        $this->add(9, 14, self::CAMPO_BRANCO); // Posição 009-014: Brancos - Complemento de Registro
        $this->add(15, 17, self::VERSAO_LAYOUT); // Posição 015-017: Nº da Versão do Layout do Arquivo (080)
        $this->add(18, 18, Util::formatCnab('9L', $this->getPagador()->getTipoDocumento() == 'CPF' ? self::TIPO_DOCUMENTO_CPF : self::TIPO_DOCUMENTO_CNPJ, 1)); // Posição 018-018: Tipo de Inscrição da Empresa (1=CPF, 2=CNPJ)
        $this->add(19, 32, Util::formatCnab('9L', $this->getPagador()->getDocumento(), 14)); // Posição 019-032: CNPJ/CPF Empresa Debitada (NOTA 1)
        $this->add(33, 52, self::CAMPO_BRANCO); // Posição 033-052: Brancos - Complemento de Registro
        $this->add(53, 57, Util::formatCnab('9L', $this->getAgencia(), 5)); // Posição 053-057: Número Agência Debitada (NOTA 1)
        $this->add(58, 58, self::CAMPO_BRANCO); // Posição 058-058: Brancos - Complemento de Registro
        $this->add(59, 70, Util::formatCnab('9L', $this->getConta(), 12)); // Posição 059-070: Número de C/C Debitada (NOTA 1)
        $this->add(71, 71, self::CAMPO_BRANCO); // Posição 071-071: Brancos - Complemento de Registro
        $this->add(72, 72, $this->getContaDv()); // Posição 072-072: DAC da Agência/Conta Debitada (NOTA 1)
        $this->add(73, 102, Util::formatCnab('X', $this->getPagador()->getNome(), 30)); // Posição 073-102: Nome da Empresa
        $this->add(103, 132, Util::formatCnab('X', self::NOME_BANCO, 30)); // Posição 103-132: Nome do Banco
        $this->add(133, 142, self::CAMPO_BRANCO); // Posição 133-142: Brancos - Complemento de Registro
        $this->add(143, 143, self::CODIGO_REMESSA); // Posição 143-143: Código Remessa/Retorno (1=REMESSA, 2=RETORNO)
        $this->add(144, 151, $this->getDataRemessa('dmY')); // Posição 144-151: Data de Geração do Arquivo (DDMMAAAA)
        $this->add(152, 157, $this->getDataRemessa('His')); // Posição 152-157: Hora de Geração do Arquivo (HHMMSS)
        $this->add(158, 166, Util::formatCnab('9', '0', 9)); // Posição 158-166: Zeros - Complemento de Registro
        $this->add(167, 171, Util::formatCnab('9', '0', 5)); // Posição 167-171: Densidade de Gravação do Arquivo (NOTA 2) - zeros para teleprocessamento
        $this->add(172, 240, self::CAMPO_BRANCO); // Posição 172-240: Brancos - Complemento de Registro

        return $this;
    }

    /**
     * Retorna o código do banco
     * @return string
     */
    public function getCodigoBanco()
    {
        return self::BANCO;
    }

    /**
     * Retorna o nome sugerido para o arquivo de remessa.
     *
     * Itaú exige nome com no máximo 8 caracteres (sem a extensão). Usa o
     * número da remessa zero-padded para 8 dígitos, igual à remessa de
     * cobrança CNAB400.
     *
     * @return string
     */
    public function nomeSugerido()
    {
        $idremessa = $this->getIdremessa() ?: 0;
        return sprintf('%08d.REM', $idremessa);
    }

    /**
     * Retorna o nome do banco
     * @return string
     */
    protected function getNomeBanco()
    {
        return self::NOME_BANCO;
    }

    /**
     * Retorna a versão do layout
     * @return string
     */
    protected function getVersaoLayout()
    {
        return self::VERSAO_LAYOUT;
    }

    /**
     * Retorna o tipo de pagamento
     * @return string
     */
    public function getTipoPagamento()
    {
        return $this->tipoPagamento;
    }

    /**
     * Define o tipo de pagamento
     * @param string $tipoPagamento
     * @return $this
     */
    public function setTipoPagamento($tipoPagamento)
    {
        $this->tipoPagamento = $tipoPagamento;
        return $this;
    }

    /**
     * Retorna o tipo de serviço baseado no tipo de pagamento
     * @return string
     */
    public function getTipoServico()
    {
        return $this->tipoServico ?? '20';
    }

    /**
     * Retorna a forma de lançamento baseada no tipo de pagamento
     * @return string
     */
    public function getFormaLancamento()
    {
        // Verifica se o tipo de pagamento é PIX
        if (isset($this->tipoPagamento) && strtoupper($this->tipoPagamento) === 'PIX') {
            return self::FORMA_LANCAMENTO_PIX; // 45 para PIX
        }

        return self::FORMA_LANCAMENTO_TED; // 41 para TED (padrão)
    }

    /**
     * Cria o header do lote CNAB 240 conforme especificação do Itaú
     * @return Itau
     * @throws \Exception
     */
    protected function headerLote()
    {
        $this->iniciaHeaderLote();
        $this->iSequencialPagamento = 0; // NOTA 9: reseta sequencial de pagamento ao abrir lote

        $this->add(1, 3, self::BANCO); // Posição 001-003: Código do Banco na Compensação (341)
        $this->add(4, 7, self::LOTE_SERVICO_HEADER); // Posição 004-007: Código do Lote (NOTA 3)
        $this->add(8, 8, self::TIPO_REGISTRO_HEADER_LOTE); // Posição 008-008: Tipo de Registro (1)
        $this->add(9, 9, self::TIPO_OPERACAO); // Posição 009-009: Tipo de Operação (C=CRÉDITO)
        $this->add(10, 11, $this->getTipoServico()); // Posição 010-011: Tipo de Pagamento (NOTA 4)
        $this->add(12, 13, $this->getFormaLancamento()); // Posição 012-013: Forma de Pagamento (NOTA 5) - 41=TED, 45=PIX
        $this->add(14, 16, '040'); // Posição 014-016: Nº da Versão do Layout do Lote (040)
        $this->add(17, 17, self::CAMPO_BRANCO); // Posição 017-017: Brancos
        $this->add(18, 18, Util::formatCnab('9L', $this->getPagador()->getTipoDocumento() == 'CPF' ? self::TIPO_DOCUMENTO_CPF : self::TIPO_DOCUMENTO_CNPJ, 1)); // Posição 018-018: Tipo Inscrição Empresa Debitada (1=CPF, 2=CNPJ)
        $this->add(19, 32, Util::formatCnab('9L', $this->getPagador()->getDocumento(), 14)); // Posição 019-032: CNPJ Empresa Debitada (NOTA 1)
        $this->add(33, 36, self::CAMPO_BRANCO); // Posição 033-036: Identificação do Lançamento no Extrato do Favorecido (NOTA 13)
        $this->add(37, 52, self::CAMPO_BRANCO); // Posição 037-052: Brancos
        $this->add(53, 57, Util::formatCnab('9L', $this->getAgencia(), 5)); // Posição 053-057: Número Agência Debitada (NOTA 1)
        $this->add(58, 58, self::CAMPO_BRANCO); // Posição 058-058: Brancos
        $this->add(59, 70, Util::formatCnab('9L', $this->getConta(), 12)); // Posição 059-070: Número de C/C Debitada (NOTA 1)
        $this->add(71, 71, self::CAMPO_BRANCO); // Posição 071-071: Brancos
        $this->add(72, 72, $this->getContaDv()); // Posição 072-072: DAC da Agência/Conta Debitada (NOTA 1)
        $this->add(73, 102, Util::formatCnab('X', $this->getPagador()->getNome(), 30)); // Posição 073-102: Nome da Empresa Debitada
        $this->add(103, 132, self::CAMPO_BRANCO); // Posição 103-132: Finalidade dos Pagtos do Lote (NOTA 6)
        $this->add(133, 142, self::CAMPO_BRANCO); // Posição 133-142: Complemento Histórico C/C Debitada (NOTA 7)
        $this->add(143, 172, Util::formatCnab('X', $this->getPagador()->getEndereco(), 30)); // Posição 143-172: Nome da Rua, Av, Pça, Etc
        $this->add(173, 177, self::CAMPO_BRANCO); // Posição 173-177: Número do Local
        $this->add(178, 192, self::CAMPO_BRANCO); // Posição 178-192: Casa, Apto, Sala, Etc
        $this->add(193, 212, Util::formatCnab('X', $this->getPagador()->getCidade(), 20)); // Posição 193-212: Nome da Cidade

        $cep = Util::formatCnab('9L', $this->getPagador()->getCep(), 8);

        $this->add(213, 220, $cep); // Posição 213-220: CEP
        $this->add(221, 222, Util::formatCnab('X', $this->getPagador()->getUf(), 2)); // Posição 221-222: Sigla do Estado
        $this->add(223, 230, self::CAMPO_BRANCO); // Posição 223-230: Brancos
        $this->add(231, 240, self::CAMPO_BRANCO); // Posição 231-240: Código Ocorrências P/Retorno (NOTA 8)

        return $this;
    }

    /**
     * Cria o trailer do lote CNAB 240 conforme especificação do Itaú
     * @return Itau
     * @throws \Exception
     */
    protected function trailerLote()
    {
        $this->iniciaTrailerLote();

        $this->add(1, 3, self::BANCO); // Posição 001-003: Código do Banco na Compensação (341)
        $this->add(4, 7, self::LOTE_SERVICO_HEADER); // Posição 004-007: Lote de Serviço (NOTA 3)
        $this->add(8, 8, self::TIPO_REGISTRO_TRAILER_LOTE); // Posição 008-008: Tipo de Registro (5)
        $this->add(9, 17, self::CAMPO_BRANCO); // Posição 009-017: Brancos
        $this->add(18, 23, Util::formatCnab('9L', $this->getCountRegistrosLote(), 6)); // Posição 018-023: Qtde Registros do Lote (NOTA 17)
        $this->add(24, 41, Util::formatCnab('9L', $this->getValorTotalLote(), 18)); // Posição 024-041: Soma Valor dos Pgtos do Lote (NOTA 17)
        $this->add(42, 59, Util::formatCnab('9', '0', 18)); // Posição 042-059: Zeros
        $this->add(60, 230, self::CAMPO_BRANCO); // Posição 060-230: Brancos
        $this->add(231, 240, self::CAMPO_BRANCO); // Posição 231-240: Código Ocorrências P/Retorno (NOTA 8)

        return $this;
    }

    /**
     * Cria o trailer do arquivo CNAB 240 conforme especificação do Itaú
     * @return Itau
     * @throws \Exception
     */
    protected function trailer()
    {
        $this->iniciaTrailer();

        $this->add(1, 3, self::BANCO); // Posição 001-003: Código do Banco na Compensação (341)
        $this->add(4, 7, self::LOTE_SERVICO_TRAILER); // Posição 004-007: Lote de Serviço (9999)
        $this->add(8, 8, self::TIPO_REGISTRO_TRAILER); // Posição 008-008: Tipo de Registro (9)
        $this->add(9, 17, self::CAMPO_BRANCO); // Posição 009-017: Brancos
        $this->add(18, 23, Util::formatCnab('9L', 1, 6)); // Posição 018-023: Quantidade de Lotes do Arquivo (sempre 1 para lote único)
        $this->add(24, 29, Util::formatCnab('9L', $this->getCount(), 6)); // Posição 024-029: Quantidade de Registros do Arquivo
        $this->add(30, 35, Util::formatCnab('9L', 0, 6)); // Posição 030-035: Quantidade de Contas para Conciliação (Lotes)
        $this->add(36, 240, self::CAMPO_BRANCO); // Posição 036-240: Brancos

        return $this;
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
     * Retorna a quantidade total de registros do arquivo
     * @return int
     */
    protected function getCountRegistros()
    {
        // Header do arquivo (1) + Header do lote (1) + Registros de detalhe + Trailer do lote (1) + Trailer do arquivo (1)
        $countDetalhes = $this->getCountDetalhes();
        return 4 + $countDetalhes; // 4 registros fixos + registros de detalhe
    }

    /**
     * Retorna a quantidade de registros de detalhe
     * @return int
     */
    protected function getCountDetalhes()
    {
        // Cada pagamento gera registros de detalhe (segmentos A e B)
        return count($this->pagamentos) * 2; // 2 segmentos por pagamento
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
            if (method_exists($pagamento, 'getValor') && $pagamento->getValor() > 0)
                $valorTotal += $pagamento->getValor();
        }

        return Util::formatCnab('9L', $valorTotal * 100, 18);
    }

    /**
     * Adiciona um pagamento
     * @param PagamentoContract $pagamento
     * @return Itau
     * @throws \Exception
     */
    public function addPagamento(PagamentoContract $pagamento)
    {
        $this->pagamentos[] = $pagamento;
        return $this;
    }

    /**
     * Gera os segmentos de um pagamento
     *
     * @param \Eduardokum\LaravelBoleto\Pagamento\Banco\Banco $pagamento
     * @return void
     * @throws \Exception
     */
    protected function gerarSegmentos(\Eduardokum\LaravelBoleto\Pagamento\Banco\Banco $pagamento)
    {
        // Arrecadação (tributos/concessionárias com código de barras de 44 dig
        // iniciado em "8") — Segmento O obrigatório, sem complemento. Manual
        // SISPAG pág 34. Detecção antes de isBoleto porque boleto comum também
        // tem código de barras, mas o produto = "8" identifica arrecadação.
        if ($this->isArrecadacao($pagamento)) {
            $this->segmentoO($pagamento);

            return;
        }

        if ($this->isBoleto($pagamento)) {
            $this->segmentoJ($pagamento);
            $this->segmentoJ52($pagamento);

            return;
        }

        $this->segmentoA($pagamento);

        // Para PIX, o layout do segmento B depende da Identificação do Tipo de
        // Transferência (NOTA 36). Apenas o modelo "Chave" (04) usa o segmento
        // B PIX (com chave de endereçamento). Para 01/PG/03 (dados bancários),
        // o layout é o segmento B padrão — opcional, usado para aviso/email.
        if ($this->isPix($pagamento)) {
            if ($this->resolveTipoTransferenciaPix($pagamento) === '04') {
                $this->segmentoBPix($pagamento);
            } else {
                $this->segmentoB($pagamento);
            }
        } else {
            $this->segmentoB($pagamento);
        }
    }

    /**
     * Verifica se o pagamento é liquidação de boleto (possui código de barras).
     *
     * @param \Eduardokum\LaravelBoleto\Pagamento\Banco\Banco $pagamento
     * @return bool
     */
    protected function isBoleto(\Eduardokum\LaravelBoleto\Pagamento\Banco\Banco $pagamento)
    {
        return method_exists($pagamento, 'isBoleto') && $pagamento->isBoleto();
    }

    protected function isBoletoTipo(string $tipo): bool
    {
        return in_array($tipo, [self::TIPO_PAGAMENTO_BOLETO_ITAU, self::TIPO_PAGAMENTO_BOLETO_OUTROS, self::TIPO_PAGAMENTO_BOLETO], true);
    }

    /**
     * Verifica se o pagamento é arrecadação (tributo/concessionária com código
     * de barras). Identificação pelo Produto = "8" na primeira posição do
     * barcode de 44 dígitos (manual SISPAG Anexo B).
     *
     * @param \Eduardokum\LaravelBoleto\Pagamento\Banco\Banco $pagamento
     * @return bool
     */
    protected function isArrecadacao(\Eduardokum\LaravelBoleto\Pagamento\Banco\Banco $pagamento)
    {
        if (! method_exists($pagamento, 'getCodigoBarras')) {
            return false;
        }
        $barcode = Util::onlyNumbers((string) $pagamento->getCodigoBarras());

        return strlen($barcode) === 44 && $barcode[0] === '8';
    }

    /**
     * Retorna o segmento de arrecadação (2ª posição do barcode):
     *   1=Prefeituras  2=Saneamento  3=Energia/Gás  4=Telecom
     *   5=Tribunais    6=Carnês      7=Multas       9=Demais
     *
     * @param string $barcode
     * @return int
     */
    protected function getArrecadacaoSegmento($barcode)
    {
        return (int) substr(Util::onlyNumbers((string) $barcode), 1, 1);
    }

    /**
     * Retorna o tipo do valor (3ª posição do barcode):
     *   6=Reais módulo 10   7=MoedaVar módulo 10
     *   8=Reais módulo 11   9=MoedaVar módulo 11
     *
     * @param string $barcode
     * @return int
     */
    protected function getArrecadacaoTipoValor($barcode)
    {
        return (int) substr(Util::onlyNumbers((string) $barcode), 2, 1);
    }

    /**
     * Monta a representação numérica (linha digitável de 48 dígitos) a partir do
     * código de barras de arrecadação (44 dígitos), conforme Anexo B do manual
     * SISPAG. O campo "Código de Barras" do Segmento O é X(48), posições 18-65,
     * e recebe a linha digitável — 4 campos de 12 dígitos (11 de dados + 1 DV
     * mód.10) — e não os 44 dígitos do código de barras.
     *
     * @param string $barcode Código de barras de arrecadação (44 dígitos)
     * @return string Linha digitável de 48 dígitos (ou o valor original se != 44)
     */
    protected function getArrecadacaoLinhaDigitavel($barcode)
    {
        $barcode = Util::onlyNumbers((string) $barcode);
        if (strlen($barcode) !== 44) {
            return $barcode;
        }

        $linha = '';
        foreach ([0, 11, 22, 33] as $inicio) {
            $bloco = substr($barcode, $inicio, 11);
            $linha .= $bloco . Util::modulo10($bloco);
        }

        return $linha;
    }

    /**
     * Mapeia o segmento do barcode de arrecadação para a chave de tipo de
     * pagamento (agrupador de lote). Manual SISPAG Nota 5.
     *
     * @param \Eduardokum\LaravelBoleto\Pagamento\Banco\Banco $pagamento
     * @return string
     */
    protected function getTipoArrecadacao(\Eduardokum\LaravelBoleto\Pagamento\Banco\Banco $pagamento)
    {
        $segmento = $this->getArrecadacaoSegmento($pagamento->getCodigoBarras());
        switch ($segmento) {
            case 1:
                return self::TIPO_PAGAMENTO_ARRECADACAO_IPTU;          // Prefeituras → IPTU
            case 2:
            case 3:
            case 4:
                return self::TIPO_PAGAMENTO_ARRECADACAO_CONCESSIONARIA; // Saneamento/Energia/Telecom
            case 9:
            default:
                return self::TIPO_PAGAMENTO_ARRECADACAO_GNRE;          // Demais (GNRE/Tributos)
        }
    }

    /**
     * Verifica se o tipo (agrupador de lote) é de arrecadação.
     *
     * @param string $tipo
     * @return bool
     */
    protected function isArrecadacaoTipo(string $tipo): bool
    {
        return in_array($tipo, [
            self::TIPO_PAGAMENTO_ARRECADACAO_IPTU,
            self::TIPO_PAGAMENTO_ARRECADACAO_CONCESSIONARIA,
            self::TIPO_PAGAMENTO_ARRECADACAO_GNRE,
        ], true);
    }

    /**
     * Resolve o agrupador de lote do pagamento - boletos ficam em lote próprio,
     * demais casos seguem a detecção do parent (TED/PIX).
     *
     * @param \Eduardokum\LaravelBoleto\Pagamento\Banco\Banco $pagamento
     * @return string
     */
    protected function getTipoPagamentoDoPagamento(\Eduardokum\LaravelBoleto\Pagamento\Banco\Banco $pagamento)
    {
        // Arrecadação tem barcode iniciado em "8" — precisa vir antes de
        // isBoleto, que também é true para barcodes (44 dig). Cada segmento
        // do barcode mapeia para uma forma SISPAG distinta (Nota 5), e cada
        // forma exige lote separado (manual pág 7).
        if ($this->isArrecadacao($pagamento)) {
            return $this->getTipoArrecadacao($pagamento);
        }

        if ($this->isBoleto($pagamento)) {
            $bancoFavorecido = substr(Util::onlyNumbers($pagamento->getCodigoBarras()), 0, 3);
            return $bancoFavorecido === self::BANCO
                ? self::TIPO_PAGAMENTO_BOLETO_ITAU
                : self::TIPO_PAGAMENTO_BOLETO_OUTROS;
        }

        if ($this->isPix($pagamento)) {
            return self::TIPO_PAGAMENTO_PIX;
        }

        return parent::getTipoPagamentoDoPagamento($pagamento);
    }

    /**
     * Sobrescrevemos o agrupamento para validar a regra do manual SISPAG
     * (página 7): "Os lotes de serviços de pagamentos na forma de PIX devem
     * ser enviados obrigatoriamente em arquivo separado das demais formas de
     * pagamento". Multi-lote é permitido para combinações que NÃO incluam PIX
     * (ex.: TED + BOLETO no mesmo arquivo). Se PIX aparecer junto com qualquer
     * outro tipo, a geração é abortada.
     *
     * @return void
     * @throws ValidationException
     */
    protected function agruparPagamentosPorTipo()
    {
        parent::agruparPagamentosPorTipo();
        $this->validarSeparacaoPix();
    }

    /**
     * Bloqueia a geração do arquivo se houver lote PIX misturado com outros
     * tipos de pagamento (TED, BOLETO, etc.).
     *
     * @return void
     * @throws ValidationException
     */
    protected function validarSeparacaoPix()
    {
        if (! isset($this->lotes[self::TIPO_PAGAMENTO_PIX])) {
            return;
        }

        $outrosTipos = array_diff(array_keys($this->lotes), [
            self::TIPO_PAGAMENTO_PIX,
        ]);
        if (! empty($outrosTipos)) {
            throw new ValidationException(sprintf(
                'Lotes PIX devem ser enviados em arquivo separado das demais formas de pagamento '
                    . '(manual SISPAG Itaú). Detectados no mesmo arquivo: PIX + %s.',
                implode(', ', $outrosTipos)
            ));
        }
    }

    /**
     * Verifica se o pagamento é do tipo PIX
     *
     * @param \Eduardokum\LaravelBoleto\Pagamento\Banco\Banco $pagamento
     * @return bool
     */
    protected function isPix(\Eduardokum\LaravelBoleto\Pagamento\Banco\Banco $pagamento)
    {
        // 1. Tem chave PIX configurada no pagamento (forte indicador de PIX)
        if (method_exists($pagamento, 'getPixChave') && !empty($pagamento->getPixChave())) {
            return true;
        }

        // 2. Pagamento individual marcado como PIX — cobre o caso de PIX por
        // dados bancários, onde não há chave mas o consumidor sinaliza o tipo
        // diretamente no pagamento (sem chamar setTipoPagamento('PIX') no arquivo).
        if (method_exists($pagamento, 'getTipoPagamento')) {
            $tipoPag = $pagamento->getTipoPagamento();
            if (!empty($tipoPag) && strtoupper((string) $tipoPag) === 'PIX') {
                return true;
            }
        }
        if (isset($pagamento->tipoPagamento) && strtoupper((string) $pagamento->tipoPagamento) === 'PIX') {
            return true;
        }

        // 3. Arquivo todo marcado como PIX (via $itau->setTipoPagamento('PIX'))
        if (isset($this->tipoPagamento) && strtoupper($this->tipoPagamento) === 'PIX') {
            return true;
        }

        return false;
    }

    /**
     * Resolve a Identificação do Tipo de Transferência PIX (NOTA 36) — código
     * gravado nas posições 113-114 do segmento A. Valores válidos:
     *   "01" Conta Corrente | "PG" Conta Pagamento | "03" Poupança | "04" Chave Pix
     *
     * Ordem de precedência:
     *   1. $pagamento->getTipoTransferenciaPix() se existir e for válido
     *   2. Tem chave PIX configurada → "04"
     *   3. tipoConta = POUPANCA/POUP/03 → "03"
     *   4. tipoConta = PAGAMENTO/PG → "PG"
     *   5. Default → "01"
     *
     * @param \Eduardokum\LaravelBoleto\Pagamento\Banco\Banco $pagamento
     * @return string
     */
    protected function resolveTipoTransferenciaPix($pagamento)
    {
        $valoresValidos = ['01', 'PG', '03', '04'];

        if (method_exists($pagamento, 'getTipoTransferenciaPix')) {
            $tipo = $pagamento->getTipoTransferenciaPix();
            if (in_array($tipo, $valoresValidos, true)) {
                return $tipo;
            }
        }

        if (method_exists($pagamento, 'getPixChave') && ! empty($pagamento->getPixChave())) {
            return '04';
        }

        if (method_exists($pagamento, 'getTipoConta')) {
            $tipoConta = strtoupper((string) $pagamento->getTipoConta());
            if (in_array($tipoConta, ['POUPANCA', 'POUP', '03'], true)) {
                return '03';
            }
            if (in_array($tipoConta, ['PAGAMENTO', 'PG'], true)) {
                return 'PG';
            }
        }

        return '01';
    }

    /**
     * Formata o campo Agência/Conta do Favorecido (posições 024-043 do segmento A)
     * conforme NOTA 11 do manual SISPAG. O layout muda dependendo do banco
     * favorecido e do tipo de transferência:
     *
     * - Conta Pagamento (PIX com ident. transferência "PG"):
     *     conta ocupa toda a região 024-043 (20 dígitos numéricos)
     * - Banco favorecido 341 (Itaú) ou 409 (Unibanco):
     *     024     zero
     *     025-028 agência (4 dígitos)
     *     029     branco
     *     030-035 zeros
     *     036-041 conta (6 dígitos)
     *     042     branco
     *     043     DAC (1 dígito)
     * - Outros bancos:
     *     024-028 agência (5 dígitos)
     *     029     branco
     *     030-041 conta (12 dígitos)
     *     042-043 DAC (1 ou 2 chars). Se DAC tem 2 chars, ocupa as duas
     *             posições; se tem 1, branco em 042 e DAC em 043.
     *
     * @param \Eduardokum\LaravelBoleto\Pagamento\Banco\Banco $pagamento
     * @param string $tipoTransferenciaPix '01'|'PG'|'03'|'04'|'' (vazio se não for PIX)
     * @return string string de 20 caracteres
     */
    protected function formatAgenciaContaFavorecido($pagamento, $tipoTransferenciaPix = '')
    {
        $bancoFav = (string) $pagamento->getCodigoBanco();
        $agencia  = (string) $pagamento->getAgencia();
        $conta    = (string) $pagamento->getConta();
        $contaDv  = (string) $pagamento->getContaDv();

        // Conta Pagamento PIX: 20 dígitos contínuos
        if ($tipoTransferenciaPix === 'PG') {
            return Util::formatCnab('9L', $conta, 20);
        }

        // Itaú (341) ou Unibanco (409): layout específico
        if (in_array($bancoFav, ['341', '409'], true)) {
            return Util::formatCnab('9L', '0', 1)             // 024 (zero)
                . Util::formatCnab('9L', $agencia, 4)         // 025-028
                . Util::formatCnab('X', '', 1)                // 029 (branco)
                . Util::formatCnab('9L', '0', 6)              // 030-035 (zeros)
                . Util::formatCnab('9L', $conta, 6)           // 036-041
                . Util::formatCnab('X', '', 1)                // 042 (branco)
                . Util::formatCnab('X', $contaDv, 1);         // 043 (DAC)
        }

        // Outros bancos
        $base = Util::formatCnab('9L', $agencia, 5)           // 024-028
            . Util::formatCnab('X', '', 1)                    // 029 (branco)
            . Util::formatCnab('9L', $conta, 12);             // 030-041

        // DAC com 2 caracteres ocupa 042-043; senão, branco + DAC
        if (strlen($contaDv) >= 2) {
            return $base . substr($contaDv, 0, 2);
        }

        return $base
            . Util::formatCnab('X', '', 1)                    // 042 (branco)
            . Util::formatCnab('X', $contaDv, 1);             // 043
    }

    /**
     * Cria o segmento A CNAB 240 conforme especificação do Itaú
     * @param Banco $pagamento
     * @return Itau
     * @throws \Exception
     */
    public function segmentoA($pagamento)
    {
        $this->iniciaDetalhe();
        $this->iSequencialPagamento++; // NOTA 9: novo pagamento → novo sequencial

        // Para PIX, a câmara centralizadora vai como "009" (SPI) — NOTA 35.
        $isPix = $this->isPix($pagamento);
        $camara = $isPix ? '009' : '000';
        $tipoTransferenciaPix = $isPix ? $this->resolveTipoTransferenciaPix($pagamento) : '';

        $this->add(1, 3, Util::formatCnab('9L', self::BANCO, 3)); // Posição 001-003: Código do Banco na Compensação (341)
        $this->add(4, 7, Util::formatCnab('9L', $this->loteAtualNumero, 4)); // Posição 004-007: Código do Lote (NOTA 3)
        $this->add(8, 8, Util::formatCnab('9L', self::TIPO_REGISTRO_DETALHE, 1)); // Posição 008-008: Tipo de Registro (3)
        $this->add(9, 13, Util::formatCnab('9L', $this->iSequencialPagamento, 5)); // Posição 009-013: Nº Sequencial Registro no Lote (NOTA 9)
        $this->add(14, 14, Util::formatCnab('X', self::CODIGO_SEGMENTO_A, 1)); // Posição 014-014: Código Segmento Reg. Detalhe (A)
        $this->add(15, 17, Util::formatCnab('9L', '000', 3)); // Posição 015-017: Tipo de Movimento (NOTA 10)
        $this->add(18, 20, Util::formatCnab('X', $camara, 3)); // Posição 018-020: Código da Câmara Centralizadora (NOTA 35) — "009" para PIX
        $this->add(21, 23, Util::formatCnab('9L', $pagamento->getCodigoBanco(), 3)); // Posição 021-023: Código Banco Favorecido

        // Posição 024-043: Agência/Conta Favorecido (NOTA 11) — layout depende do banco favorecido
        $this->add(24, 43, $this->formatAgenciaContaFavorecido($pagamento, $tipoTransferenciaPix));

        $this->add(44, 73, Util::formatCnab('X', $pagamento->getBeneficiario()->getNome(), 30)); // Posição 044-073: Nome do Favorecido (NOTA 34)
        $this->add(74, 93, Util::formatCnab('X', $pagamento->getNumeroControle(), 20)); // Posição 074-093: Nº Docto Atribuído pela Empresa (Seu Número)

        // Data de Pagamento
        $dataPagamento = $pagamento->getDataPagamento() ? date('dmY', strtotime($pagamento->getDataPagamento())) : date('dmY');
        $this->add(94, 101, Util::formatCnab('9L', $dataPagamento, 8)); // Posição 094-101: Data Prevista para Pagto (DDMMAAAA)

        $this->add(102, 104, Util::formatCnab('X', 'REA', 3)); // Posição 102-104: Tipo da Moeda (REA ou 009)

        // Posição 105-112: Código ISPB (NOTA 35) — opcional para a maioria dos casos
        $codigoIspb = method_exists($pagamento, 'getCodigoIspb') ? (string) $pagamento->getCodigoIspb() : '';
        $this->add(105, 112, Util::formatCnab('X', $codigoIspb, 8));

        // Posição 113-114: Identificação do Tipo de Transferência (NOTA 36) — só PIX usa
        // 01 CC | PG Conta Pagamento | 03 Poupança | 04 Chave Pix
        $this->add(113, 114, Util::formatCnab('X', $tipoTransferenciaPix, 2));

        $this->add(115, 119, Util::formatCnab('9', '0', 5)); // Posição 115-119: Zeros

        // Valor do Pagamento - 9(13)V9(02) = 15 dígitos com vírgula decimal assumida
        $valor = $pagamento->getValor() ? $pagamento->getValor() * 100 : 0;
        $this->add(120, 134, Util::formatCnab('9L', $valor, 15)); // Posição 120-134: Valor Previsto do Pagto 9(13)V9(2)

        $this->add(135, 149, Util::formatCnab('X', '', 15)); // Posição 135-149: Nosso Número (NOTA 12)
        $this->add(150, 154, Util::formatCnab('X', '', 5)); // Posição 150-154: Brancos (NOTA 42)
        $this->add(155, 162, Util::formatCnab('9L', '0', 8)); // Posição 155-162: Data Real Efetivação do Pagto (DDMMAAAA)
        $this->add(163, 177, Util::formatCnab('9L', '0', 15)); // Posição 163-177: Valor Efetivo 9(13)V9(2)
        $this->add(178, 197, Util::formatCnab('X', '', 20)); // Posição 178-197: Finalidade Detalhe TED/Hist C/C (NOTA 13)
        $this->add(198, 203, Util::formatCnab('9L', '0', 6)); // Posição 198-203: Nº do Doc/TED ou Cheque (NOTA 14)
        $this->add(204, 217, Util::formatCnab('9L', $pagamento->getBeneficiario()->getDocumento(), 14)); // Posição 204-217: Nº de Inscrição do Favorecido CPF/CNPJ (NOTA 15)
        $this->add(218, 219, Util::formatCnab('X', '', 2)); // Posição 218-219: Finalidade Tipo e Status Funcionário (NOTA 30)
        $this->add(220, 224, Util::formatCnab('X', '', 5)); // Posição 220-224: Finalidade TED (NOTA 26)
        $this->add(225, 229, Util::formatCnab('X', '', 5)); // Posição 225-229: Brancos
        $this->add(230, 230, Util::formatCnab('X', '0', 1)); // Posição 230-230: Aviso ao Favorecido (NOTA 16)
        $this->add(231, 240, Util::formatCnab('X', '', 10)); // Posição 231-240: Ocorrências (NOTA 8)

        return $this;
    }

    /**
     * Cria o segmento B CNAB 240 conforme especificação do Itaú (DOC/TED)
     * @param Banco $pagamento
     * @return Itau
     * @throws \Exception
     */
    public function segmentoB($pagamento)
    {
        $this->iniciaDetalhe();

        $this->add(1, 3, self::BANCO); // Posição 001-003: Código do Banco na Compensação (341)
        $this->add(4, 7, Util::formatCnab('9L', $this->loteAtualNumero, 4)); // Posição 004-007: Código do Lote (NOTA 3)
        $this->add(8, 8, self::TIPO_REGISTRO_DETALHE); // Posição 008-008: Tipo de Registro (3)
        $this->add(9, 13, Util::formatCnab('9L', $this->iSequencialPagamento, 5)); // Posição 009-013: Nº Sequencial — complemento de A, repete o número (NOTA 9)
        $this->add(14, 14, self::CODIGO_SEGMENTO_B); // Posição 014-014: Código Segmento Reg. Detalhe (B)
        $this->add(15, 17, self::CAMPO_BRANCO); // Posição 015-017: Brancos
        $this->add(18, 18, $pagamento->getBeneficiario()->getTipoDocumento() == 'CPF' ? self::TIPO_DOCUMENTO_CPF : self::TIPO_DOCUMENTO_CNPJ); // Posição 018-018: Tipo Inscrição do Favorecido (1=CPF, 2=CNPJ)
        $this->add(19, 32, Util::formatCnab('9L', $pagamento->getBeneficiario()->getDocumento(), 14)); // Posição 019-032: Nº de Inscrição do Favorecido CPF/CNPJ (NOTA 15)
        $this->add(33, 62, Util::formatCnab('X', $pagamento->getBeneficiario()->getEndereco(), 30)); // Posição 033-062: Endereço (Nome da Rua, Av, Pça, Etc)
        $this->add(63, 67, Util::formatCnab('9L', '', 5)); // Posição 063-067: Número do Local
        $this->add(68, 82, Util::formatCnab('X', '', 15)); // Posição 068-082: Complemento (Casa, Apto, Etc)
        $this->add(83, 97, Util::formatCnab('X', $pagamento->getBeneficiario()->getBairro(), 15)); // Posição 083-097: Bairro
        $this->add(98, 117, Util::formatCnab('X', $pagamento->getBeneficiario()->getCidade(), 20)); // Posição 098-117: Cidade (Nome da Cidade)

        $cep = Util::formatCnab('9L', $pagamento->getBeneficiario()->getCep(), 8);

        $this->add(118, 125, $cep); // Posição 118-125: CEP
        $this->add(126, 127, Util::formatCnab('X', $pagamento->getBeneficiario()->getUf(), 2)); // Posição 126-127: Estado (Sigla do Estado)
        $this->add(128, 227, Util::formatCnab('X', '', 100)); // Posição 128-227: E-Mail (Endereço de E-Mail) (NOTA 23)
        $this->add(228, 230, self::CAMPO_BRANCO); // Posição 228-230: Brancos
        $this->add(231, 240, self::CAMPO_BRANCO); // Posição 231-240: Ocorrências (NOTA 8)

        return $this;
    }

    /**
     * Cria o segmento B CNAB 240 conforme especificação do Itaú (PIX)
     * @param Banco $pagamento
     * @return Itau
     * @throws \Exception
     */
    public function segmentoBPix($pagamento)
    {
        $this->iniciaDetalhe();

        $this->add(1, 3, self::BANCO); // Posição 001-003: Código do Banco na Compensação (341)
        $this->add(4, 7, Util::formatCnab('9L', $this->loteAtualNumero, 4)); // Posição 004-007: Código do Lote (NOTA 3)
        $this->add(8, 8, self::TIPO_REGISTRO_DETALHE); // Posição 008-008: Tipo de Registro (3)
        $this->add(9, 13, Util::formatCnab('9L', $this->iSequencialPagamento, 5)); // Posição 009-013: Nº Sequencial — complemento de A, repete o número (NOTA 9)
        $this->add(14, 14, self::CODIGO_SEGMENTO_B); // Posição 014-014: Código Segmento Reg. Detalhe (B)

        // Tipo de Chave PIX (NOTA 37) — 01 Telefone | 02 E-mail | 03 CPF/CNPJ | 04 Aleatória
        $tipoChave = $pagamento->getFormaIniciacao() ?? '03'; // Default: 03 = CPF/CNPJ
        $this->add(15, 16, Util::formatCnab('X', $tipoChave, 2)); // Posição 015-016: Tipo Identificação da Chave (NOTA 37)

        $this->add(17, 17, self::CAMPO_BRANCO); // Posição 017-017: Brancos
        $this->add(18, 18, $pagamento->getBeneficiario()->getTipoDocumento() == 'CPF' ? self::TIPO_DOCUMENTO_CPF : self::TIPO_DOCUMENTO_CNPJ); // Posição 018-018: Tipo Inscrição do Favorecido (1=CPF, 2=CNPJ)
        $this->add(19, 32, Util::formatCnab('9L', $pagamento->getBeneficiario()->getDocumento(), 14)); // Posição 019-032: Nº de Inscrição do Favorecido CPF/CNPJ (NOTA 15)
        $this->add(33, 62, self::CAMPO_BRANCO); // Posição 033-062: Brancos

        // Informações entre usuários (NOTA 39)
        $infoUsuarios = $pagamento->getInformacoesEntreUsuarios() ?? '';
        $this->add(63, 127, Util::formatCnab('X', $infoUsuarios, 65)); // Posição 063-127: Informações Entre Usuários (NOTA 39)

        // Chave PIX (NOTA 40)
        $chavePix = $pagamento->getPixChave() ?? '';
        $this->add(128, 227, Util::formatCnab('X', $chavePix, 100)); // Posição 128-227: Chave de Endereçamento (NOTA 40)

        $this->add(228, 230, self::CAMPO_BRANCO); // Posição 228-230: Brancos
        $this->add(231, 240, self::CAMPO_BRANCO); // Posição 231-240: Ocorrências (NOTA 8)

        return $this;
    }

    /**
     * Segmento J - Liquidação de boletos em cobrança no Itaú e em outros bancos.
     *
     * NOTA 18: os campos posições 018-061 correspondem à decomposição do código
     * de barras (banco, moeda, DV, fator de vencimento, valor, campo livre).
     *
     * @param \Eduardokum\LaravelBoleto\Pagamento\Banco\Banco $pagamento
     * @return Itau
     * @throws \Exception
     */
    public function segmentoJ($pagamento)
    {
        $this->iniciaDetalhe();
        $this->iSequencialPagamento++; // NOTA 9: novo pagamento → novo sequencial

        $codigoBarras = Util::onlyNumbers($pagamento->getCodigoBarras());

        $this->add(1, 3, self::BANCO); // 001-003: Código do Banco na Compensação (341)
        $this->add(4, 7, Util::formatCnab('9L', $this->loteAtualNumero, 4)); // 004-007: Código do Lote (NOTA 3)
        $this->add(8, 8, self::TIPO_REGISTRO_DETALHE); // 008-008: Tipo de Registro (3)
        $this->add(9, 13, Util::formatCnab('9L', $this->iSequencialPagamento, 5)); // 009-013: Nº Sequencial Registro no Lote (NOTA 9)
        $this->add(14, 14, self::CODIGO_SEGMENTO_J); // 014-014: Código Segmento (J)
        $this->add(15, 17, Util::formatCnab('9L', '000', 3)); // 015-017: Tipo de Movimento (NOTA 10)

        // 018-061: Decomposição do código de barras (NOTA 18)
        $this->add(18, 20, substr($codigoBarras, 0, 3));   // Código do Banco Favorecido
        $this->add(21, 21, substr($codigoBarras, 3, 1));   // Código da Moeda
        $this->add(22, 22, substr($codigoBarras, 4, 1));   // DV do Código de Barras
        $this->add(23, 26, substr($codigoBarras, 5, 4));   // Fator de Vencimento
        $this->add(27, 36, substr($codigoBarras, 9, 10));  // Valor (9(8)V9(2))
        $this->add(37, 61, substr($codigoBarras, 19, 25)); // Campo Livre

        // Nome do favorecido (beneficiário do pagamento / cedente do boleto)
        $this->add(62, 91, Util::formatCnab('X', $pagamento->getBeneficiario()->getNome(), 30));

        // Data de vencimento (nominal) - DDMMAAAA
        $dataVencimento = $pagamento->getDataVencimento()
            ? $pagamento->getDataVencimento()->format('dmY')
            : date('dmY');
        $this->add(92, 99, Util::formatCnab('9L', $dataVencimento, 8));

        // Valor nominal do título 9(13)V9(02)
        $valorTitulo = (int) round($pagamento->getValorTitulo() * 100);
        $this->add(100, 114, Util::formatCnab('9L', $valorTitulo, 15));

        // Descontos (desconto + abatimento) 9(13)V9(02)
        $valorDesconto = (int) round($pagamento->getDesconto() * 100);
        $this->add(115, 129, Util::formatCnab('9L', $valorDesconto, 15));

        // Acréscimos (mora + multa) 9(13)V9(02)
        $valorAcrescimo = (int) round($pagamento->getAcrescimo() * 100);
        $this->add(130, 144, Util::formatCnab('9L', $valorAcrescimo, 15));

        // Data do pagamento (DDMMAAAA)
        $dataPagamento = $pagamento->getDataPagamento()
            ? $pagamento->getDataPagamento()->format('dmY')
            : date('dmY');
        $this->add(145, 152, Util::formatCnab('9L', $dataPagamento, 8));

        // Valor do pagamento 9(13)V9(02)
        $valorPagamento = (int) round($pagamento->getValor() * 100);
        $this->add(153, 167, Util::formatCnab('9L', $valorPagamento, 15));

        $this->add(168, 182, Util::formatCnab('9', '0', 15)); // 168-182: Zeros (complemento)
        $this->add(183, 202, Util::formatCnab('X', $pagamento->getNumeroControle() ?? '', 20)); // 183-202: Seu Número
        $this->add(203, 215, self::CAMPO_BRANCO); // 203-215: Brancos
        $this->add(216, 230, self::CAMPO_BRANCO); // 216-230: Nosso Número (retorno)
        $this->add(231, 240, self::CAMPO_BRANCO); // 231-240: Ocorrências (retorno)

        return $this;
    }

    /**
     * Segmento J-52 - Identificação do sacado, cedente e sacador avalista
     * (obrigatório para formas de pagamento 30 e 31 - liquidação de boletos).
     *
     * @param \Eduardokum\LaravelBoleto\Pagamento\Banco\Banco $pagamento
     * @return Itau
     * @throws \Exception
     */
    public function segmentoJ52($pagamento)
    {
        $this->iniciaDetalhe();

        // Sacado = quem paga o boleto = empresa pagadora (Header do Arquivo)
        $sacado = $this->getPagador();
        $tipoInscSacado = $sacado->getTipoDocumento() == 'CPF'
            ? self::TIPO_DOCUMENTO_CPF
            : self::TIPO_DOCUMENTO_CNPJ;

        // Cedente = quem emitiu o boleto = beneficiário do pagamento
        $cedente = $pagamento->getBeneficiario();
        $tipoInscCedente = $cedente->getTipoDocumento() == 'CPF'
            ? self::TIPO_DOCUMENTO_CPF
            : self::TIPO_DOCUMENTO_CNPJ;

        // Sacador avalista (opcional)
        $sacadorAvalista = method_exists($pagamento, 'getSacadorAvalista')
            ? $pagamento->getSacadorAvalista()
            : null;

        $this->add(1, 3, self::BANCO); // 001-003: Código do Banco
        $this->add(4, 7, Util::formatCnab('9L', $this->loteAtualNumero, 4)); // 004-007: Código do Lote
        $this->add(8, 8, self::TIPO_REGISTRO_DETALHE); // 008-008: Tipo de Registro (3)
        $this->add(9, 13, Util::formatCnab('9L', $this->iSequencialPagamento, 5)); // 009-013: Nº Sequencial — complemento de J, repete o número (NOTA 9)
        $this->add(14, 14, self::CODIGO_SEGMENTO_J); // 014-014: Segmento (J)
        $this->add(15, 17, Util::formatCnab('9L', '000', 3)); // 015-017: Tipo de Movimento
        $this->add(18, 19, self::CODIGO_REGISTRO_OPCIONAL_J52); // 018-019: Identificação do Registro Opcional (52)

        // Sacado
        $this->add(20, 20, $tipoInscSacado); // 020-020: Tipo Inscrição Sacado
        $this->add(21, 35, Util::formatCnab('9L', $sacado->getDocumento(), 15)); // 021-035: Nº Inscrição Sacado
        $this->add(36, 75, Util::formatCnab('X', $sacado->getNome(), 40)); // 036-075: Nome Sacado

        // Cedente (obrigatório)
        $this->add(76, 76, $tipoInscCedente); // 076-076: Tipo Inscrição Cedente
        $this->add(77, 91, Util::formatCnab('9L', $cedente->getDocumento(), 15)); // 077-091: Nº Inscrição Cedente
        $this->add(92, 131, Util::formatCnab('X', $cedente->getNome(), 40)); // 092-131: Nome Cedente

        // Sacador Avalista (opcional)
        if ($sacadorAvalista !== null) {
            $tipoInscSacador = $sacadorAvalista->getTipoDocumento() == 'CPF'
                ? self::TIPO_DOCUMENTO_CPF
                : self::TIPO_DOCUMENTO_CNPJ;
            $this->add(132, 132, $tipoInscSacador); // 132-132: Tipo Inscrição Sacador
            $this->add(133, 147, Util::formatCnab('9L', $sacadorAvalista->getDocumento(), 15)); // 133-147: Nº Inscrição Sacador
            $this->add(148, 187, Util::formatCnab('X', $sacadorAvalista->getNome(), 40)); // 148-187: Nome Sacador
        } else {
            $this->add(132, 132, '0'); // 132-132: Sem sacador avalista
            $this->add(133, 147, Util::formatCnab('9', '0', 15)); // 133-147: Zeros
            $this->add(148, 187, self::CAMPO_BRANCO); // 148-187: Brancos
        }

        $this->add(188, 240, self::CAMPO_BRANCO); // 188-240: Brancos

        return $this;
    }

    /**
     * Segmento O - Pagamento de Contas de Concessionárias e Tributos com
     * código de barras (manual SISPAG Itaú v085 página 34).
     *
     * Identificação do barcode (44 dígitos, NOTA 18 / Anexo B):
     *   pos 1   = "8" (produto = arrecadação)
     *   pos 2   = segmento (1=Prefeituras, 2=Saneamento, 3=Energia/Gás,
     *             4=Telecom, 9=Demais/Tributos)
     *   pos 3   = identificação do valor (6/7 mod10, 8/9 mod11)
     *   pos 4   = DV do código de barras
     *   pos 5-15  = valor (R$ 9(09)V9(02))
     *   pos 16-19 = empresa/órgão
     *   pos 20-44 = campo livre
     *
     * @param \Eduardokum\LaravelBoleto\Pagamento\Banco\Banco $pagamento
     * @return Itau
     * @throws \Exception
     */
    public function segmentoO($pagamento)
    {
        $this->iniciaDetalhe();
        $this->iSequencialPagamento++; // NOTA 9: novo pagamento → novo sequencial

        $barcode = Util::onlyNumbers((string) $pagamento->getCodigoBarras());

        // Nome da Concessionária / Contribuinte — favorecido do barcode. Usa
        // o beneficiário do pagamento; fallback para o pagador (contribuinte).
        $nomeFavorecido = '';
        if (method_exists($pagamento, 'getBeneficiario') && $pagamento->getBeneficiario()) {
            $nomeFavorecido = (string) $pagamento->getBeneficiario()->getNome();
        }
        if ($nomeFavorecido === '') {
            $nomeFavorecido = (string) $this->getPagador()->getNome();
        }

        $dataVencimento = $pagamento->getDataVencimento()
            ? $pagamento->getDataVencimento()->format('dmY')
            : date('dmY');
        $dataPagamento = $pagamento->getDataPagamento()
            ? $pagamento->getDataPagamento()->format('dmY')
            : date('dmY');

        // Valor a pagar (centavos). Em arrecadações tipo valor 7/9 (moeda
        // variável) o valor não vai aqui — vai em "Quantidade Moeda".
        $tipoValor = $this->getArrecadacaoTipoValor($barcode);
        $moedaVariavel = in_array($tipoValor, [7, 9], true);
        $valorPagamento = (int) round($pagamento->getValor() * 100);

        // Nota Fiscal — obrigatória apenas para GNRE-SP código de receita 10009.9
        // (Substituição Tributária por Operação). Demais: zeros. NOTA 33.
        $notaFiscal = 0;
        if (method_exists($pagamento, 'getNumeroNotaFiscal') && $pagamento->getNumeroNotaFiscal()) {
            $notaFiscal = (int) Util::onlyNumbers((string) $pagamento->getNumeroNotaFiscal());
        }

        $seuNumero = '';
        if (method_exists($pagamento, 'getNumeroControle') && $pagamento->getNumeroControle()) {
            $seuNumero = (string) $pagamento->getNumeroControle();
        }

        $this->add(1, 3, self::BANCO);                                                     // 001-003: Código do Banco (341)
        $this->add(4, 7, Util::formatCnab('9L', $this->loteAtualNumero, 4));               // 004-007: Código do Lote (NOTA 3)
        $this->add(8, 8, self::TIPO_REGISTRO_DETALHE);                                     // 008-008: Tipo de Registro (3)
        $this->add(9, 13, Util::formatCnab('9L', $this->iSequencialPagamento, 5));         // 009-013: Nº Sequencial Registro no Lote (NOTA 9)
        $this->add(14, 14, self::CODIGO_SEGMENTO_O);                                       // 014-014: Código Segmento (O)
        $this->add(15, 17, Util::formatCnab('9L', '000', 3));                              // 015-017: Tipo de Movimento (NOTA 10)

        // 018-065: Código de Barras X(48). Recebe a representação numérica
        // (linha digitável de 48 dígitos = 4 campos de 11 dados + DV mód.10),
        // conforme Anexo B / NOTA 18 do manual SISPAG Itaú. O DAC geral do
        // código de barras (pos 4) é validado pelo banco a partir destes dados.
        // Prioriza a linha digitável ORIGINAL capturada (preserva os DVs do
        // documento); só reconstrói via mód.10 quando ela não foi informada.
        $linhaDigitavel = method_exists($pagamento, 'getLinhaDigitavel')
            ? Util::onlyNumbers((string) $pagamento->getLinhaDigitavel())
            : '';
        if (strlen($linhaDigitavel) !== 48) {
            $linhaDigitavel = $this->getArrecadacaoLinhaDigitavel($barcode);
        }
        $this->add(18, 65, Util::formatCnab('X', $linhaDigitavel, 48));

        $this->add(66, 95, Util::formatCnab('X', $nomeFavorecido, 30));                    // 066-095: Nome Concessionária / Contribuinte
        $this->add(96, 103, Util::formatCnab('9L', $dataVencimento, 8));                   // 096-103: Data Vencimento (DDMMAAAA)
        $this->add(104, 106, self::MOEDA_REAL_SEGMENTO_O);                                 // 104-106: Tipo de Moeda (REA)

        // 107-121: Quantidade de Moeda 9(07)V9(08). Zeros quando moeda = REA
        // (valor real); quando moeda variável (tipo 7/9) deveria conter a
        // quantidade em unidades. Implementação MVP usa zeros (caso comum).
        $this->add(107, 121, Util::formatCnab('9', '0', 15));

        // 122-136: Valor previsto do pagamento 9(13)V9(02). Para moeda variável,
        // manual diz que o valor a pagar é obtido pela quantidade × cotação;
        // este campo pode receber o valor calculado para auditoria.
        $this->add(122, 136, Util::formatCnab('9L', $valorPagamento, 15));

        $this->add(137, 144, Util::formatCnab('9L', $dataPagamento, 8));                   // 137-144: Data do Pagamento
        $this->add(145, 159, Util::formatCnab('9', '0', 15));                              // 145-159: Valor Pago (retorno) — zeros na remessa
        $this->add(160, 162, self::CAMPO_BRANCO);                                          // 160-162: Brancos
        $this->add(163, 171, Util::formatCnab('9L', $notaFiscal, 9));                      // 163-171: Número da Nota Fiscal (NOTA 33)
        $this->add(172, 174, self::CAMPO_BRANCO);                                          // 172-174: Brancos
        $this->add(175, 194, Util::formatCnab('X', $seuNumero, 20));                       // 175-194: Seu Número
        $this->add(195, 215, self::CAMPO_BRANCO);                                          // 195-215: Brancos
        $this->add(216, 230, self::CAMPO_BRANCO);                                          // 216-230: Nosso Número (retorno)
        $this->add(231, 240, self::CAMPO_BRANCO);                                          // 231-240: Ocorrências (retorno)

        unset($moedaVariavel); // reservado para futura validação DV módulo 10/11

        return $this;
    }

    /**
     * Header do lote para múltiplos lotes
     *
     * @param array $lote
     * @return Itau
     * @throws \Exception
     */
    protected function headerLoteMulti(array $lote)
    {
        $this->iniciaHeaderLote();
        $this->iSequencialPagamento = 0; // NOTA 9: reseta sequencial de pagamento ao abrir lote

        // Memoriza o número do lote para que os segmentos (A/B/J/J-52)
        // gravem o mesmo valor nas posições 4-7 — antes ficavam todos em "0001".
        $this->loteAtualNumero = (int) $lote['numero'];

        $isBoleto = $this->isBoletoTipo($lote['tipo']);
        $isArrecadacao = $this->isArrecadacaoTipo($lote['tipo']);

        if ($isArrecadacao) {
            // Tributos (manual pág 33): layout 030, tipo de pagamento 22.
            $versaoLayoutLote = self::VERSAO_LAYOUT_LOTE_TRIBUTO;
            $tipoServico = self::TIPO_SERVICO_TRIBUTOS;
        } elseif ($isBoleto) {
            $versaoLayoutLote = self::VERSAO_LAYOUT_LOTE_BOLETO;
            $tipoServico = self::TIPO_SERVICO_FORNECEDOR;
        } else {
            $versaoLayoutLote = '040';
            $tipoServico = $this->getTipoServico();
        }

        $this->add(1, 3, self::BANCO); // Posição 001-003: Código do Banco na Compensação (341)
        $this->add(4, 7, Util::formatCnab('9L', $lote['numero'], 4)); // Posição 004-007: Código do Lote (NOTA 3)
        $this->add(8, 8, self::TIPO_REGISTRO_HEADER_LOTE); // Posição 008-008: Tipo de Registro (1)
        $this->add(9, 9, self::TIPO_OPERACAO); // Posição 009-009: Tipo de Operação (C=CRÉDITO)
        $this->add(10, 11, Util::formatCnab('9L', $tipoServico, 2)); // Posição 010-011: Tipo de Pagamento (NOTA 4)
        $this->add(12, 13, $this->getFormaLancamentoPorTipo($lote['tipo'], $lote)); // Posição 012-013: Forma de Pagamento (NOTA 5)
        $this->add(14, 16, $versaoLayoutLote); // Posição 014-016: Nº da Versão do Layout do Lote (040 - TED/PIX | 030 - Boleto)
        $this->add(17, 17, self::CAMPO_BRANCO); // Posição 017-017: Brancos
        $this->add(18, 18, Util::formatCnab('9L', $this->getPagador()->getTipoDocumento() == 'CPF' ? self::TIPO_DOCUMENTO_CPF : self::TIPO_DOCUMENTO_CNPJ, 1)); // Posição 018-018: Tipo Inscrição Empresa Debitada (1=CPF, 2=CNPJ)
        $this->add(19, 32, Util::formatCnab('9L', $this->getPagador()->getDocumento(), 14)); // Posição 019-032: CNPJ Empresa Debitada (NOTA 1)
        $this->add(33, 36, self::CAMPO_BRANCO); // Posição 033-036: Identificação do Lançamento no Extrato do Favorecido (NOTA 13)
        $this->add(37, 52, self::CAMPO_BRANCO); // Posição 037-052: Brancos
        $this->add(53, 57, Util::formatCnab('9L', $this->getAgencia(), 5)); // Posição 053-057: Número Agência Debitada (NOTA 1)
        $this->add(58, 58, self::CAMPO_BRANCO); // Posição 058-058: Brancos
        $this->add(59, 70, Util::formatCnab('9L', $this->getConta(), 12)); // Posição 059-070: Número de C/C Debitada (NOTA 1)
        $this->add(71, 71, self::CAMPO_BRANCO); // Posição 071-071: Brancos
        $this->add(72, 72, $this->getContaDv()); // Posição 072-072: DAC da Agência/Conta Debitada (NOTA 1)
        $this->add(73, 102, Util::formatCnab('X', $this->getPagador()->getNome(), 30)); // Posição 073-102: Nome da Empresa Debitada
        $this->add(103, 132, self::CAMPO_BRANCO); // Posição 103-132: Finalidade dos Pagtos do Lote (NOTA 6)
        $this->add(133, 142, self::CAMPO_BRANCO); // Posição 133-142: Complemento Histórico C/C Debitada (NOTA 7)
        $this->add(143, 172, Util::formatCnab('X', $this->getPagador()->getEndereco(), 30)); // Posição 143-172: Nome da Rua, Av, Pça, Etc
        $this->add(173, 177, self::CAMPO_BRANCO); // Posição 173-177: Número do Local
        $this->add(178, 192, self::CAMPO_BRANCO); // Posição 178-192: Casa, Apto, Sala, Etc
        $this->add(193, 212, Util::formatCnab('X', $this->getPagador()->getCidade(), 20)); // Posição 193-212: Nome da Cidade

        $cep = Util::formatCnab('9L', $this->getPagador()->getCep(), 8);

        $this->add(213, 220, $cep); // Posição 213-220: CEP
        $this->add(221, 222, Util::formatCnab('X', $this->getPagador()->getUf(), 2)); // Posição 221-222: Sigla do Estado
        $this->add(223, 230, self::CAMPO_BRANCO); // Posição 223-230: Brancos
        $this->add(231, 240, self::CAMPO_BRANCO); // Posição 231-240: Código Ocorrências P/Retorno (NOTA 8)

        return $this;
    }

    /**
     * Trailer do lote para múltiplos lotes
     *
     * @param array $lote
     * @return Itau
     * @throws \Exception
     */
    protected function trailerLoteMulti(array $lote)
    {
        $this->iniciaTrailerLote();

        $isArrecadacao = $this->isArrecadacaoTipo($lote['tipo']);

        // Arrecadação (Segmento O) = 1 segmento por pagamento. Demais
        // (TED A+B, boleto J+J52) = 2 segmentos por pagamento.
        $segmentosPorPagamento = $isArrecadacao ? 1 : 2;
        $qtdRegistrosLote = (count($lote['pagamentos']) * $segmentosPorPagamento) + 2; // + header e trailer

        $this->add(1, 3, self::BANCO); // Posição 001-003: Código do Banco na Compensação (341)
        $this->add(4, 7, Util::formatCnab('9L', $lote['numero'], 4)); // Posição 004-007: Lote de Serviço (NOTA 3)
        $this->add(8, 8, self::TIPO_REGISTRO_TRAILER_LOTE); // Posição 008-008: Tipo de Registro (5)
        $this->add(9, 17, self::CAMPO_BRANCO); // Posição 009-017: Brancos
        $this->add(18, 23, Util::formatCnab('9L', $qtdRegistrosLote, 6)); // Posição 018-023: Qtde Registros do Lote (NOTA 17)
        $this->add(24, 41, Util::formatCnab('9L', $this->getValorTotalLoteMulti($lote), 18)); // Posição 024-041: Soma Valor dos Pgtos do Lote (NOTA 17)

        if ($isArrecadacao) {
            // Trailer de lote arrecadação (manual pág 36):
            //   042-056: TOTAL QTDE MOEDA 9(07)V9(08) — soma das quantidades de
            //            moeda dos pagamentos do lote (zeros quando moeda REA).
            //   057-230: Brancos
            $this->add(42, 56, Util::formatCnab('9', '0', 15));
            $this->add(57, 230, self::CAMPO_BRANCO);
        } else {
            // Trailer de lote padrão (TED/PIX/Boleto):
            //   042-059: Zeros
            //   060-230: Brancos
            $this->add(42, 59, Util::formatCnab('9', '0', 18));
            $this->add(60, 230, self::CAMPO_BRANCO);
        }

        $this->add(231, 240, self::CAMPO_BRANCO); // Posição 231-240: Código Ocorrências P/Retorno (NOTA 8)

        return $this;
    }

    /**
     * Trailer do arquivo para múltiplos lotes
     *
     * @return Itau
     * @throws \Exception
     */
    protected function trailerMulti()
    {
        $this->iniciaTrailer();

        $this->add(1, 3, self::BANCO); // Posição 001-003: Código do Banco na Compensação (341)
        $this->add(4, 7, self::LOTE_SERVICO_TRAILER); // Posição 004-007: Lote de Serviço (9999)
        $this->add(8, 8, self::TIPO_REGISTRO_TRAILER); // Posição 008-008: Tipo de Registro (9)
        $this->add(9, 17, self::CAMPO_BRANCO); // Posição 009-017: Brancos
        $this->add(18, 23, Util::formatCnab('9L', $this->getCountLotes(), 6)); // Posição 018-023: Quantidade de Lotes do Arquivo
        $this->add(24, 29, Util::formatCnab('9L', $this->getCountMulti(), 6)); // Posição 024-029: Quantidade de Registros do Arquivo
        $this->add(30, 240, self::CAMPO_BRANCO); // Posição 036-240: Brancos

        return $this;
    }

    /**
     * Retorna a forma de lançamento baseada no tipo de pagamento específico.
     *
     * Para boletos (tipo BOLETO), a forma efetiva (30 ou 31) depende do banco
     * favorecido presente no código de barras e é resolvida em runtime pelo
     * método getFormaLancamentoBoleto().
     *
     * @param string     $tipoPagamento
     * @param array|null $lote         Quando informado, permite resolver a forma
     *                                 real para boletos (30 Itaú / 31 outros).
     * @return string
     */
    protected function getFormaLancamentoPorTipo($tipoPagamento, array $lote = null)
    {
        switch ($tipoPagamento) {
            case self::TIPO_PAGAMENTO_TED:
                return self::FORMA_LANCAMENTO_TED;
            case self::TIPO_PAGAMENTO_PIX:
                return self::FORMA_LANCAMENTO_PIX;
            case self::TIPO_PAGAMENTO_BOLETO_ITAU:
                return self::FORMA_LANCAMENTO_BOLETO_ITAU;
            case self::TIPO_PAGAMENTO_BOLETO_OUTROS:
                return self::FORMA_LANCAMENTO_BOLETO_OUTROS;
            case self::TIPO_PAGAMENTO_BOLETO:
                // legado: se ainda chegarem pagamentos com tipo genérico, resolve pelo banco do barcode
                return $lote !== null
                    ? $this->getFormaLancamentoBoleto($lote)
                    : self::FORMA_LANCAMENTO_BOLETO_OUTROS;
            case self::TIPO_PAGAMENTO_ARRECADACAO_IPTU:
                return self::FORMA_LANCAMENTO_IPTU;            // 19
            case self::TIPO_PAGAMENTO_ARRECADACAO_CONCESSIONARIA:
                return self::FORMA_LANCAMENTO_CONCESSIONARIA;  // 13
            case self::TIPO_PAGAMENTO_ARRECADACAO_GNRE:
                return self::FORMA_LANCAMENTO_GNRE;            // 91
            default:
                return self::FORMA_LANCAMENTO_TED;
        }
    }

    /**
     * Resolve a forma de lançamento do lote de boletos: 30 (Itaú) se todos os
     * títulos forem do Itaú, caso contrário 31 (outros bancos).
     *
     * @param array $lote
     * @return string
     */
    protected function getFormaLancamentoBoleto(array $lote)
    {
        foreach ($lote['pagamentos'] as $pagamento) {
            $bancoFavorecido = substr(Util::onlyNumbers($pagamento->getCodigoBarras()), 0, 3);
            if ($bancoFavorecido !== self::BANCO) {
                return self::FORMA_LANCAMENTO_BOLETO_OUTROS;
            }
        }

        return self::FORMA_LANCAMENTO_BOLETO_ITAU;
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
            // Arrecadação (Segmento O) = 1 segmento por pagamento.
            // Demais (A+B, J+J52) = 2 segmentos por pagamento.
            $segmentosPorPagamento = $this->isArrecadacaoTipo($lote['tipo']) ? 1 : 2;
            $totalRegistros += count($lote['pagamentos']) * $segmentosPorPagamento;
            $totalRegistros += 2; // Header + Trailer do lote
        }
        $totalRegistros += 2; // Header + Trailer do arquivo
        return $totalRegistros;
    }

    /**
     * Retorna o valor total de um lote específico (para múltiplos lotes)
     *
     * @param array $lote
     * @return string
     */
    protected function getValorTotalLoteMulti(array $lote)
    {
        $valorTotal = 0;

        // Soma todos os valores dos pagamentos no lote específico
        foreach ($lote['pagamentos'] as $pagamento) {
            if (method_exists($pagamento, 'getValor') && $pagamento->getValor() > 0)
                $valorTotal += $pagamento->getValor();
        }

        return Util::formatCnab('9L', $valorTotal * 100, 18);
    }
}
