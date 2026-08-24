<?php

namespace Eduardokum\LaravelBoleto\Cnab\Pagamento\Cnab240\Banco;

use Eduardokum\LaravelBoleto\Cnab\Pagamento\Cnab240\AbstractPagamento;
use Eduardokum\LaravelBoleto\Contracts\Cnab\Pagamento as PagamentoRemessaContract;
use Eduardokum\LaravelBoleto\Contracts\Pagamento\Pagamento as PagamentoContract;
use Eduardokum\LaravelBoleto\Exception\ValidationException;
use Eduardokum\LaravelBoleto\Pagamento\Banco\Banco;
use Eduardokum\LaravelBoleto\Util;

/**
 * Class Bancoob
 * @package Eduardokum\LaravelBoleto\Cnab\Pagamento\Cnab240\Banco
 */
class Bancoob extends AbstractPagamento implements PagamentoRemessaContract
{
    const BANCO = '756'; // Código do Bancoob na compensação
    const LOTE_SERVICO = '0000'; // Lote de serviço (header do arquivo)
    const TIPO_REGISTRO = '0'; // Tipo de registro (header do arquivo)
    const TIPO_DOCUMENTO_CPF = '1'; // CPF
    const TIPO_DOCUMENTO_CNPJ = '2'; // CNPJ
    const CODIGO_REMESSA = '1'; // Código de remessa
    const VERSAO_LAYOUT = '087'; // Versão do layout do arquivo (conforme especificação)
    const DENSIDADE_GRAVACAO = '01600'; // Densidade de gravação do arquivo
    const NOME_BANCO = 'BANCOOB'; // Nome do banco
    const CAMPO_BRANCO = '';

    // Constantes para trailer do arquivo
    const LOTE_SERVICO_TRAILER = '9999'; // Lote de serviço (trailer do arquivo)
    const TIPO_REGISTRO_TRAILER = '9'; // Tipo de registro (trailer do arquivo)
    const QUANTIDADE_CONTAS_CONCILIACAO = 0; // 07.9 Qtde de contas p/ conciliação (G037) - vide getCountContasConciliacao()

    // Constantes para header do lote
    const LOTE_SERVICO_HEADER = '0001'; // Lote de serviço (header do lote)
    const TIPO_REGISTRO_HEADER_LOTE = '1'; // Tipo de registro (header do lote)
    const TIPO_OPERACAO = 'C'; // Tipo da operação (Crédito)
    const TIPO_SERVICO = '10'; // Tipo do serviço (Transferência entre contas)
    const FORMA_LANCAMENTO = '41'; // Forma de lançamento (TED)
    const VERSAO_LAYOUT_LOTE = '045'; // Versão do layout do lote
    const INDICATIVO_FORMA_PAGAMENTO = '01'; // Indicativo da forma de pagamento

    // Constantes para trailer do lote
    const LOTE_SERVICO_TRAILER_LOTE = '0001'; // Lote de serviço (trailer do lote)
    const TIPO_REGISTRO_TRAILER_LOTE = '5'; // Tipo de registro (trailer do lote)
    const QUANTIDADE_MOEDA_ZERO = 0; // Quantidade de moeda (geralmente 0 para pagamentos)
    const NUMERO_AVISO_DEBITO_ZERO = 0; // Número do aviso de débito (preenchido pelo banco no retorno)

    // Constantes para segmentos de detalhe
    const TIPO_REGISTRO_DETALHE = '3'; // Tipo de registro (detalhe)
    const CODIGO_SEGMENTO_A = 'A'; // Código do segmento A
    const CODIGO_SEGMENTO_B = 'B'; // Código do segmento B
    const TIPO_MOVIMENTO = '0'; // Tipo de movimento
    const CODIGO_INSTRUCAO_MOVIMENTO = '00'; // Código da instrução para movimento
    const CODIGO_CAMARA_CENTRALIZADORA = '018'; // Código da câmara centralizadora
    const TIPO_MOEDA = 'BRL'; // Tipo da moeda (Real brasileiro)
    const QUANTIDADE_MOEDA = '000000000000000'; // Quantidade da moeda (15 zeros)
    const CODIGO_FINALIDADE_TED = '00010'; // Código finalidade da TED
    const AVISO_FAVORECIDO = '0'; // Aviso ao favorecido
    // 22.3A (P003) e 23.3A (P004) são campos Num preenchidos apenas no retorno. Na remessa vão
    // zerados — e precisam ir com a largura completa do campo: Util::adiciona() alinha à direita
    // com ESPAÇOS, então um 0 inteiro viraria '       0' num campo numérico, o que o item 10 do
    // guia (pág. 36) rejeita como "campo do tipo Numérico informado com caracteres especiais".
    const DATA_REAL_ZERO = '00000000'; // 22.3A Data real da efetivação (8 zeros na remessa)
    const VALOR_REAL_ZERO = '000000000000000'; // 23.3A Valor real da efetivação (15 zeros na remessa)

    /**
     * Bancoob constructor.
     * @param array $params
     */
    public function __construct(array $params = [])
    {
        parent::__construct($params);
        $this->codigoBanco = self::BANCO;

        // addCampoObrigatorio() acrescenta; setCamposObrigatorios() ZERA a lista antes de
        // acrescentar. A chamada anterior era setCamposObrigatorios('convenio'), que descartava
        // 'agencia', 'conta' e 'pagador' herdados do abstrato — uma remessa sem agência e sem
        // conta passava por isValid() e saía com 00000/000000000000 no header.
        //
        // 'contaDv' entra na lista porque o campo 11.0 (G011, pág. 38) é Num e Obrigatório: sem
        // o dígito, a posição 71 sairia em branco, violando o item 2.2 do guia (pág. 9). Preencher
        // com zero não é alternativa — fabricaria o DV de uma conta que não é a da empresa.
        $this->addCampoObrigatorio('convenio', 'contaDv');
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
     * Cria o header do arquivo CNAB 240 conforme especificação do Bancoob
     * @return Bancoob
     * @throws \Exception
     */
    protected function header()
    {
        $this->iniciaHeader();

        $this->add(1, 3, self::BANCO); // 01.0 Banco - Código do Banco na Compensação
        $this->add(4, 7, self::LOTE_SERVICO); // 02.0 Controle Lote - Lote de Serviço
        $this->add(8, 8, self::TIPO_REGISTRO); // 03.0 Registro - Tipo de Registro
        $this->add(9, 17, self::CAMPO_BRANCO); // 04.0 CNAB - Uso Exclusivo FEBRABAN / CNAB
        $this->add(18, 18, Util::formatCnab('9L', $this->getPagador()->getTipoDocumento() == 'CPF' ? self::TIPO_DOCUMENTO_CPF : self::TIPO_DOCUMENTO_CNPJ, 1)); // 05.0 Empresa Inscrição Tipo - Tipo de Inscrição da Empresa
        $this->add(19, 32, Util::formatCnab('9L', $this->getPagador()->getDocumento(), 14)); // 06.0 Empresa Inscrição Número - Número de Inscrição da Empresa

        $convenio = $this->getConvenio() ?? '';

        $this->add(33, 52, Util::formatCnab('X', $convenio, 20)); // 07.0 Empresa Convênio - Código do Convênio
        $this->add(53, 57, Util::formatCnab('9L', $this->getAgencia(), 5)); // 08.0 Empresa Agência Código - Agência Mantenedora da Conta
        $this->add(58, 58, $this->getAgenciaDv()); // 09.0 Empresa Agência DV - Dígito Verificador da Agência
        $this->add(59, 70, Util::formatCnab('9L', $this->getConta(), 12)); // 10.0 Empresa Conta Corrente Número - Número da Conta Corrente
        $this->add(71, 71, $this->getContaDv()); // 11.0 Empresa Conta Corrente DV - Dígito Verificador da Conta
        $this->add(72, 72, $this->getAgenciaContaDv()); // 12.0 Empresa DV - Dígito Verificador da Ag/Conta
        $this->add(73, 102, Util::formatCnab('X', $this->getPagador()->getNome(), 30)); // 13.0 Nome - Nome da Empresa
        $this->add(103, 132, Util::formatCnab('X', self::NOME_BANCO, 30)); // 14.0 Nome do Banco - Nome do Banco
        $this->add(133, 142, self::CAMPO_BRANCO); // 15.0 CNAB - Uso Exclusivo FEBRABAN / CNAB
        $this->add(143, 143, self::CODIGO_REMESSA); // 16.0 Arquivo Código - Código Remessa / Retorno
        $this->add(144, 151, $this->getDataRemessa('dmY')); // 17.0 Arquivo Data de Geração - Data de Geração do Arquivo
        $this->add(152, 157, $this->getDataRemessa('His')); // 18.0 Arquivo Hora de Geração - Hora de Geração do Arquivo
        $this->add(158, 163, Util::formatCnab('9L', $this->getIdremessa(), 6)); // 19.0 Arquivo Seqüência (NSA) - Número Seqüencial do Arquivo
        $this->add(164, 166, self::VERSAO_LAYOUT); // 20.0 Layout do Arquivo - Nº da Versão do Layout do Arquivo
        $this->add(167, 171, self::DENSIDADE_GRAVACAO); // 21.0 Densidade - Densidade de Gravação do Arquivo
        $this->add(172, 191, self::CAMPO_BRANCO); // 22.0 Reservado Banco - Para Uso Reservado do Banco
        $this->add(192, 211, self::CAMPO_BRANCO); // 23.0 Reservado Empresa - Para Uso Reservado da Empresa
        $this->add(212, 240, self::CAMPO_BRANCO); // 24.0 CNAB - Uso Exclusivo FEBRABAN / CNAB

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
     * Retorna o dígito verificador da agência/conta DA EMPRESA PAGADORA (campos 12.0 e 16.1).
     *
     * G012 (pág. 39): este campo NÃO é uma cópia do DV da conta. É a 2ª posição do dígito
     * verificador, e só existe para os bancos que usam duas posições no DV da conta corrente.
     * Exemplo do manual: C/C 45981-36 → G011 (pos. 71) = 3 e G012 (pos. 72) = 6.
     *
     * Só é usado nas três posições 72 (header do arquivo e headers de lote), onde a conta é
     * sempre a da empresa no Sicoob — DV de um dígito só. Logo o campo, Alfa e Opcional, fica
     * em branco. A implementação anterior devolvia substr($this->getContaDv(), -1), repetindo o
     * mesmo dígito nas posições 71 e 72 e fabricando um DV que a conta não tem.
     *
     * Um DV de duas posições sequer chega até aqui: Cnab\Pagamento\AbstractPagamento::setContaDv()
     * aplica substr($contaDv, -1) e guarda apenas o último caractere. Se algum dia o Sicoob
     * passar a usar DV de duas posições, é esse setter compartilhado que precisa mudar.
     *
     * Para o campo equivalente do favorecido (14.3A), vide getAgenciaContaDvFavorecido().
     *
     * @return string
     */
    protected function getAgenciaContaDv()
    {
        return self::CAMPO_BRANCO;
    }

    /**
     * Retorna o dígito verificador da agência/conta DO FAVORECIDO (campo 14.3A).
     *
     * Ao contrário da posição 72, aqui a conta é a de destino e pode estar em qualquer banco —
     * inclusive num que use duas posições no DV da conta corrente. Conforme G012 (pág. 39), o
     * campo recebe a 2ª posição desse dígito; quando o DV tem uma posição só, fica em branco.
     *
     * O campo 13.3A (pos. 42), que é Num, já recebe a 1ª posição via Util::formatCnab, que
     * trunca pelo início. G012 é Alfa, então aqui o valor vai sem passar por formatação
     * numérica: um DV não numérico continua válido neste campo.
     *
     * O modelo de pagamento preserva o DV inteiro — Pagamento\AbstractPagamento::setContaDv()
     * não trunca, diferente do setter homônimo do lado da remessa —, então o dígito informado
     * pela aplicação chega até aqui.
     *
     * @param Banco $pagamento
     * @return string
     */
    protected function getAgenciaContaDvFavorecido($pagamento)
    {
        $contaDv = (string) $pagamento->getContaDv();

        return mb_strlen($contaDv) > 1 ? mb_substr($contaDv, 1, 1) : self::CAMPO_BRANCO;
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
     * Cria o header do lote CNAB 240 conforme especificação do Bancoob
     * @return Bancoob
     * @throws \Exception
     */
    protected function headerLote()
    {
        $this->iniciaHeaderLote();

        $this->add(1, 3, self::BANCO); // 01.1 Banco - Código do Banco na Compensação
        $this->add(4, 7, self::LOTE_SERVICO_HEADER); // 02.1 Controle Lote - Lote de Serviço
        $this->add(8, 8, self::TIPO_REGISTRO_HEADER_LOTE); // 03.1 Registro - Tipo de Registro
        $this->add(9, 9, self::TIPO_OPERACAO); // 04.1 Operação - Tipo da Operação
        $this->add(10, 11, self::TIPO_SERVICO); // 05.1 Serviço - Tipo do Serviço
        $this->add(12, 13, self::FORMA_LANCAMENTO); // 06.1 Serviço - Forma Lançamento
        $this->add(14, 16, self::VERSAO_LAYOUT_LOTE); // 07.1 Layout do Lote - Nº da Versão do Layout do Lote
        $this->add(17, 17, self::CAMPO_BRANCO); // 08.1 CNAB - Uso Exclusivo da FEBRABAN/CNAB
        $this->add(18, 18, $this->getPagador()->getTipoDocumento() == 'CPF' ? self::TIPO_DOCUMENTO_CPF : self::TIPO_DOCUMENTO_CNPJ); // 09.1 Inscrição Tipo - Tipo de Inscrição da Empresa
        $this->add(19, 32, Util::formatCnab('9L', $this->getPagador()->getDocumento(), 14)); // 10.1 Inscrição Número - Número de Inscrição da Empresa

        $convenio = $this->getConvenio() ?? '';

        $this->add(33, 52, Util::formatCnab('X', $convenio, 20)); // 11.1 Convênio - Código do Convênio no Banco
        $this->add(53, 57, Util::formatCnab('9L', $this->getAgencia(), 5)); // 12.1 Empresa Agência Código - Agência Mantenedora da Conta
        $this->add(58, 58, $this->getAgenciaDv()); // 13.1 Empresa Agência DV - Dígito Verificador da Agência
        $this->add(59, 70, Util::formatCnab('9L', $this->getConta(), 12)); // 14.1 Empresa Conta Corrente Número - Número da Conta Corrente
        $this->add(71, 71, $this->getContaDv()); // 15.1 Empresa Conta Corrente DV - Dígito Verificador da Conta
        $this->add(72, 72, $this->getAgenciaContaDv()); // 16.1 Empresa Conta Corrente DV - Dígito Verificador da Ag/Conta
        $this->add(73, 102, Util::formatCnab('X', $this->getPagador()->getNome(), 30)); // 17.1 Nome - Nome da Empresa
        $this->add(103, 142, Util::formatCnab('X', '', 40)); // 18.1 Informação 1 - Mensagem
        $this->add(143, 172, Util::formatCnab('X', $this->getPagador()->getEndereco(), 30)); // 19.1 Endereço da Empresa Logradouro - Nome da Rua, Av, Pça, Etc
        $this->add(173, 177, Util::formatCnab('9L', '', 5)); // 20.1 Endereço da Empresa Número - Número do Local
        $this->add(178, 192, Util::formatCnab('X', '', 15)); // 21.1 Endereço da Empresa Complemento - Casa, Apto, Sala, Etc
        $this->add(193, 212, Util::formatCnab('X', $this->getPagador()->getCidade(), 20)); // 22.1 Endereço da Empresa Cidade - Nome da Cidade

        $cep = Util::formatCnab('9L', $this->getPagador()->getCep(), 8);

        $this->add(213, 217, substr($cep, 0, 5)); // 23.1 Endereço da Empresa CEP - CEP
        $this->add(218, 220, substr($cep, 5, 3)); // 24.1 Endereço da Empresa Complemento CEP - Complemento do CEP
        $this->add(221, 222, Util::formatCnab('X', $this->getPagador()->getUf(), 2)); // 25.1 Endereço da Empresa Estado - Sigla do Estado
        $this->add(223, 224, self::INDICATIVO_FORMA_PAGAMENTO); // 26.1 Indicativo de Forma de Pagamento - Indicativo da Forma de Pagamento do Serviço
        $this->add(225, 230, Util::formatCnab('X', '', 6)); // 27.1 CNAB - Uso Exclusivo FEBRABAN/CNAB
        $this->add(231, 240, Util::formatCnab('X', '', 10)); // 28.1 Ocorrências - Códigos das Ocorrências p/ Retorno

        return $this;
    }

    /**
     * Cria o trailer do lote CNAB 240 conforme especificação do Bancoob
     * @return Bancoob
     * @throws \Exception
     */
    protected function trailerLote()
    {
        $this->iniciaTrailerLote();

        $this->add(1, 3, self::BANCO); // 01.5 Banco - Código do Banco na Compensação
        $this->add(4, 7, self::LOTE_SERVICO_TRAILER_LOTE); // 02.5 Controle Lote - Lote de Serviço
        $this->add(8, 8, self::TIPO_REGISTRO_TRAILER_LOTE); // 03.5 Registro - Tipo de Registro
        $this->add(9, 17, self::CAMPO_BRANCO); // 04.5 CNAB - Uso Exclusivo FEBRABAN/CNAB
        $this->add(18, 23, Util::formatCnab('9L', $this->getCountRegistrosLote(), 6)); // 05.5 Qtde de Registros - Quantidade de Registros do Lote
        $this->add(24, 41, Util::formatCnab('9L', $this->getValorTotalLote(), 18)); // 06.5 Totais Valor - Somatória dos Valores
        $this->add(42, 59, Util::formatCnab('9L', self::QUANTIDADE_MOEDA_ZERO, 18)); // 07.5 Totais Qtde de Moeda - Somatória de Quantidade de Moedas
        $this->add(60, 65, Util::formatCnab('9L', self::NUMERO_AVISO_DEBITO_ZERO, 6)); // 08.5 Número Aviso Débito - Número Aviso de Débito
        $this->add(66, 230, self::CAMPO_BRANCO); // 09.5 CNAB - Uso Exclusivo FEBRABAN/CNAB
        $this->add(231, 240, Util::formatCnab('X', '', 10)); // 10.5 Ocorrências - Códigos das Ocorrências para Retorno

        return $this;
    }

    /**
     * Cria o trailer do arquivo CNAB 240 conforme especificação do Bancoob
     * @return Bancoob
     * @throws \Exception
     */
    protected function trailer()
    {
        $this->iniciaTrailer();

        $this->add(1, 3, self::BANCO); // 01.9 Banco - Código do Banco na Compensação
        $this->add(4, 7, self::LOTE_SERVICO_TRAILER); // 02.9 Controle Lote - Lote de Serviço
        $this->add(8, 8, self::TIPO_REGISTRO_TRAILER); // 03.9 Registro - Tipo de Registro
        $this->add(9, 17, self::CAMPO_BRANCO); // 04.9 CNAB - Uso Exclusivo FEBRABAN/CNAB
        $this->add(18, 23, Util::formatCnab('9L', $this->getCountLotes(), 6)); // 05.9 Qtde. de Lotes - Quantidade de Lotes do Arquivo
        $this->add(24, 29, Util::formatCnab('9L', $this->getCountRegistros(), 6)); // 06.9 Totais Qtde. de Registros - Quantidade de Registros do Arquivo
        $this->add(30, 35, Util::formatCnab('9L', $this->getCountContasConciliacao(), 6)); // 07.9 Qtde. de Contas Concil. - Qtde de Contas p/ Conc. (Lotes)
        $this->add(36, 240, self::CAMPO_BRANCO); // 08.9 CNAB - Uso Exclusivo FEBRABAN/CNAB

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
     * Retorna a quantidade de lotes de conciliação bancária do arquivo (campo 07.9).
     *
     * G037 (pág. 42) não conta pagamentos: conta os lotes de Conciliação Bancária, ou seja,
     * a somatória dos registros de tipo 1 cujo Tipo de Operação (G028) seja 'E'. É um campo
     * específico do serviço de Conciliação Bancária.
     *
     * Esta classe emite exclusivamente lotes de crédito (self::TIPO_OPERACAO = 'C'), logo o
     * arquivo nunca carrega lote de conciliação e o campo é sempre zero. Se um dia a classe
     * passar a gerar lotes com Tipo de Operação 'E', este método precisa contá-los.
     *
     * @return int
     */
    protected function getCountContasConciliacao()
    {
        return self::QUANTIDADE_CONTAS_CONCILIACAO;
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
     * Header do lote para múltiplos lotes
     *
     * @param array $lote
     * @return Bancoob
     * @throws \Exception
     */
    protected function headerLoteMulti(array $lote)
    {
        $this->iniciaHeaderLote();

        $this->add(1, 3, self::BANCO); // 01.1 Banco - Código do Banco na Compensação
        $this->add(4, 7, Util::formatCnab('9L', $lote['numero'], 4)); // 02.1 Controle Lote - Lote de Serviço (número do lote)
        $this->add(8, 8, self::TIPO_REGISTRO_HEADER_LOTE); // 03.1 Registro - Tipo de Registro
        $this->add(9, 9, self::TIPO_OPERACAO); // 04.1 Operação - Tipo da Operação
        $this->add(10, 11, self::TIPO_SERVICO); // 05.1 Serviço - Tipo do Serviço
        $this->add(12, 13, self::FORMA_LANCAMENTO); // 06.1 Serviço - Forma Lançamento
        $this->add(14, 16, self::VERSAO_LAYOUT_LOTE); // 07.1 Layout do Lote - Nº da Versão do Layout do Lote
        $this->add(17, 17, self::CAMPO_BRANCO); // 08.1 CNAB - Uso Exclusivo da FEBRABAN/CNAB
        $this->add(18, 18, $this->getPagador()->getTipoDocumento() == 'CPF' ? self::TIPO_DOCUMENTO_CPF : self::TIPO_DOCUMENTO_CNPJ); // 09.1 Inscrição Tipo - Tipo de Inscrição da Empresa
        $this->add(19, 32, Util::formatCnab('9L', $this->getPagador()->getDocumento(), 14)); // 10.1 Inscrição Número - Número de Inscrição da Empresa

        $convenio = $this->getConvenio() ?? '';

        $this->add(33, 52, Util::formatCnab('X', $convenio, 20)); // 11.1 Convênio - Código do Convênio no Banco
        $this->add(53, 57, Util::formatCnab('9L', $this->getAgencia(), 5)); // 12.1 Empresa Agência Código - Agência Mantenedora da Conta
        $this->add(58, 58, $this->getAgenciaDv()); // 13.1 Empresa Agência DV - Dígito Verificador da Agência
        $this->add(59, 70, Util::formatCnab('9L', $this->getConta(), 12)); // 14.1 Empresa Conta Corrente Número - Número da Conta Corrente
        $this->add(71, 71, $this->getContaDv()); // 15.1 Empresa Conta Corrente DV - Dígito Verificador da Conta
        $this->add(72, 72, $this->getAgenciaContaDv()); // 16.1 Empresa Conta Corrente DV - Dígito Verificador da Ag/Conta
        $this->add(73, 102, Util::formatCnab('X', $this->getPagador()->getNome(), 30)); // 17.1 Nome - Nome da Empresa
        $this->add(103, 142, Util::formatCnab('X', '', 40)); // 18.1 Informação 1 - Mensagem
        $this->add(143, 172, Util::formatCnab('X', $this->getPagador()->getEndereco(), 30)); // 19.1 Endereço da Empresa Logradouro - Nome da Rua, Av, Pça, Etc
        $this->add(173, 177, Util::formatCnab('9L', '', 5)); // 20.1 Endereço da Empresa Número - Número do Local
        $this->add(178, 192, Util::formatCnab('X', '', 15)); // 21.1 Endereço da Empresa Complemento - Casa, Apto, Sala, Etc
        $this->add(193, 212, Util::formatCnab('X', $this->getPagador()->getCidade(), 20)); // 22.1 Endereço da Empresa Cidade - Nome da Cidade

        $cep = Util::formatCnab('9L', $this->getPagador()->getCep(), 8);

        $this->add(213, 217, substr($cep, 0, 5)); // 23.1 Endereço da Empresa CEP - CEP
        $this->add(218, 220, substr($cep, 5, 3)); // 24.1 Endereço da Empresa Complemento CEP - Complemento do CEP
        $this->add(221, 222, Util::formatCnab('X', $this->getPagador()->getUf(), 2)); // 25.1 Endereço da Empresa Estado - Sigla do Estado
        $this->add(223, 224, self::INDICATIVO_FORMA_PAGAMENTO); // 26.1 Indicativo de Forma de Pagamento - Indicativo da Forma de Pagamento do Serviço
        $this->add(225, 230, Util::formatCnab('X', '', 6)); // 27.1 CNAB - Uso Exclusivo FEBRABAN/CNAB
        $this->add(231, 240, Util::formatCnab('X', '', 10)); // 28.1 Ocorrências - Códigos das Ocorrências p/ Retorno

        return $this;
    }

    /**
     * Trailer do lote para múltiplos lotes
     *
     * @param array $lote
     * @return Bancoob
     * @throws \Exception
     */
    protected function trailerLoteMulti(array $lote)
    {
        $this->iniciaTrailerLote();

        $this->add(1, 3, self::BANCO); // 01.5 Banco - Código do Banco na Compensação
        $this->add(4, 7, Util::formatCnab('9L', $lote['numero'], 4)); // 02.5 Controle Lote - Lote de Serviço (Número do lote)
        $this->add(8, 8, self::TIPO_REGISTRO_TRAILER_LOTE); // 03.5 Registro - Tipo de Registro
        $this->add(9, 17, self::CAMPO_BRANCO); // 04.5 CNAB - Uso Exclusivo FEBRABAN/CNAB
        $this->add(18, 23, Util::formatCnab('9L', $this->getCountRegistrosLote(), 6)); // 05.5 Qtde de Registros - Quantidade de Registros do Lote
        $this->add(24, 41, Util::formatCnab('9L', $this->getValorTotalLoteMulti($lote), 18)); // 06.5 Totais Valor - Somatória dos Valores
        $this->add(42, 59, Util::formatCnab('9L', self::QUANTIDADE_MOEDA_ZERO, 18)); // 07.5 Totais Qtde de Moeda - Somatória de Quantidade de Moedas
        $this->add(60, 65, Util::formatCnab('9L', self::NUMERO_AVISO_DEBITO_ZERO, 6)); // 08.5 Número Aviso Débito - Número Aviso de Débito
        $this->add(66, 230, self::CAMPO_BRANCO); // 09.5 CNAB - Uso Exclusivo FEBRABAN/CNAB
        $this->add(231, 240, Util::formatCnab('X', '', 10)); // 10.5 Ocorrências - Códigos das Ocorrências para Retorno

        return $this;
    }

    /**
     * Trailer do arquivo para múltiplos lotes
     *
     * @return Bancoob
     * @throws \Exception
     */
    protected function trailerMulti()
    {
        $this->iniciaTrailer();

        $this->add(1, 3, self::BANCO); // 01.9 Banco - Código do Banco na Compensação
        $this->add(4, 7, self::LOTE_SERVICO_TRAILER); // 02.9 Controle Lote - Lote de Serviço
        $this->add(8, 8, self::TIPO_REGISTRO_TRAILER); // 03.9 Registro - Tipo de Registro
        $this->add(9, 17, self::CAMPO_BRANCO); // 04.9 CNAB - Uso Exclusivo FEBRABAN/CNAB
        $this->add(18, 23, Util::formatCnab('9L', $this->getCountLotes(), 6)); // 05.9 Qtde. de Lotes - Quantidade de Lotes do Arquivo
        $this->add(24, 29, Util::formatCnab('9L', $this->getCountMulti(), 6)); // 06.9 Totais Qtde. de Registros - Quantidade de Registros do Arquivo
        $this->add(30, 35, Util::formatCnab('9L', $this->getCountContasConciliacao(), 6)); // 07.9 Qtde. de Contas Concil. - Qtde de Contas p/ Conc. (Lotes)
        $this->add(36, 240, self::CAMPO_BRANCO); // 08.9 CNAB - Uso Exclusivo FEBRABAN/CNAB

        return $this;
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
     * Gera os segmentos de um pagamento
     *
     * @param \Eduardokum\LaravelBoleto\Pagamento\Banco\Banco $pagamento
     * @return void
     */
    protected function gerarSegmentos(\Eduardokum\LaravelBoleto\Pagamento\Banco\Banco $pagamento)
    {
        $this->segmentoA($pagamento);
        $this->segmentoB($pagamento);
    }


    /**
     * Adiciona um pagamento
     * @param PagamentoContract $pagamento
     * @return Bancoob
     * @throws \Exception
     */
    public function addPagamento(PagamentoContract $pagamento)
    {
        $this->pagamentos[] = $pagamento;
        return $this;
    }

    /**
     * Cria o segmento A CNAB 240 conforme especificação do Bancoob
     * @param Banco $pagamento
     * @return Bancoob
     * @throws \Exception
     */
    public function segmentoA($pagamento)
    {
        $this->iniciaDetalhe();

        // Controle
        $this->add(1, 3, self::BANCO); // 01.3A Banco - Código do Banco na Compensação
        $this->add(4, 7, self::LOTE_SERVICO_HEADER); // 02.3A Lote - Lote de Serviço
        $this->add(8, 8, self::TIPO_REGISTRO_DETALHE); // 03.3A Registro - Tipo de Registro
        $this->add(9, 13, Util::formatCnab('9L', $this->iRegistrosLote, 5)); // 04.3A N° do Registro - Nº Seqüencial do Registro no Lote

        // Serviço
        $this->add(14, 14, self::CODIGO_SEGMENTO_A); // 05.3A Segmento - Código de Segmento do Reg. Detalhe
        $this->add(15, 15, self::TIPO_MOVIMENTO); // 06.3A Movimento Tipo - Tipo de Movimento
        $this->add(16, 17, self::CODIGO_INSTRUCAO_MOVIMENTO); // 07.3A Movimento Código - Código da Instrução p/ Movimento
        $this->add(18, 20, self::CODIGO_CAMARA_CENTRALIZADORA); // 08.3A Câmara - Código da Câmara Centralizadora
        $this->add(21, 23, Util::formatCnab('9L', $pagamento->getCodigoBanco(), 3)); // 09.3A Banco - Código do Banco do Favorecido

        // Favorecido - dados da conta de destino, informados pela aplicação no próprio pagamento.
        // Guia Sicoob CNAB 240 v4.0, item 7.2 (pág. 16): 10.3A e 12.3A são a agência e a conta
        // DO FAVORECIDO (G008/G010), ambas obrigatórias. Nunca a conta da empresa pagadora,
        // que já consta no header do arquivo (53-70) e no header do lote (53-70).
        $this->validaContaFavorecido($pagamento);

        $this->add(24, 28, Util::formatCnab('9L', $pagamento->getAgencia(), 5)); // 10.3A Agência Código - Ag. Mantenedora da Cta do Favor.
        $this->add(29, 29, Util::formatCnab('X', $pagamento->getAgenciaDv(), 1)); // 11.3A Agência DV - Digito Verificador da Agência
        $this->add(30, 41, Util::formatCnab('9L', $pagamento->getConta(), 12)); // 12.3A Conta Corrente Número - Número da Conta Corrente
        $this->add(42, 42, Util::formatCnab('9L', $pagamento->getContaDv(), 1)); // 13.3A Conta Corrente DV - Digito Verificador da Conta
        $this->add(43, 43, $this->getAgenciaContaDvFavorecido($pagamento)); // 14.3A DV - Digito Verificador da AG/Conta
        $this->add(44, 73, Util::formatCnab('X', $pagamento->getBeneficiario()->getNome(), 30)); // 15.3A Nome - Nome do Favorecido

        // Crédito
        $this->add(74, 93, Util::formatCnab('X', $pagamento->getNumeroControle(), 20)); // 16.3A Seu Número - Nº do Docum. Atribuído p/ Empresa

        $dataPagamento = $pagamento->getDataPagamento() ? date('dmY', strtotime($pagamento->getDataPagamento())) : date('dmY');

        $this->add(94, 101, $dataPagamento); // 17.3A Data Pagamento - Data do Pagamento
        $this->add(102, 104, self::TIPO_MOEDA); // 18.3A Moeda Tipo - Tipo da Moeda (BRL)
        $this->add(105, 119, self::QUANTIDADE_MOEDA); // 19.3A Moeda Quantidade - Quantidade da Moeda
        $this->add(120, 134, Util::formatCnab('9L', $pagamento->getValor(), 15)); // 20.3A Valor Pagamento - Valor do Pagamento
        $this->add(135, 154, Util::formatCnab('X', $pagamento->getNossoNumero(), 20)); // 21.3A Nosso Número - Nº do Docum. Atribuído pelo Banco
        $this->add(155, 162, self::DATA_REAL_ZERO); // 22.3A Data Real - Data Real da Efetivação Pagto
        $this->add(163, 177, self::VALOR_REAL_ZERO); // 23.3A Valor Real - Valor Real da Efetivação do Pagto
        $this->add(178, 217, Util::formatCnab('X', '', 40)); // 24.3A Informação 2 - Outras Informações
        $this->add(218, 219, self::CAMPO_BRANCO); // 25.3A Código Finalidade Doc - Compl. Tipo Serviço
        $this->add(220, 224, self::CODIGO_FINALIDADE_TED); // 26.3A Código Finalidade TED - Codigo finalidade da TED
        $this->add(225, 226, self::CAMPO_BRANCO); // 27.3A Código Finalidade Complementar - Complemento de finalidade pagto.
        $this->add(227, 229, self::CAMPO_BRANCO); // 28.3A CNAB - Uso Exclusivo FEBRABAN/CNAB
        $this->add(230, 230, self::AVISO_FAVORECIDO); // 29.3A Aviso - Aviso ao Favorecido
        $this->add(231, 240, self::CAMPO_BRANCO); // 29.3A Ocorrências - Códigos das Ocorrências p/ Retorno

        return $this;
    }

    /**
     * Garante que a conta de destino do Segmento A foi informada pela aplicação.
     *
     * Sem esta checagem, agência/conta vazias passariam por Util::formatCnab('9L', ...)
     * e virariam '00000' / '000000000000' — um destino zerado que o layout aceita
     * silenciosamente e o banco só recusa depois, no retorno.
     *
     * Guia Sicoob CNAB 240 v4.0, item 7.2 (pág. 16): campos 10.3A (G008), 12.3A (G010) e
     * 13.3A (G011) são Obrigatórios.
     *
     * O DV da conta entra na checagem pelo mesmo motivo: o campo 13.3A é Num, e o item 2.2
     * (pág. 9) manda preencher campo Num com zeros. Um DV ausente viraria '0' — um dígito
     * fabricado, apontando para outra conta. Campo Num que carrega identidade e veio vazio
     * deve falhar, não ser preenchido.
     *
     * O DV da agência (11.3A) fica de fora: é Alfa e Opcional, então brancos são o
     * preenchimento correto quando não há dígito.
     *
     * @param Banco $pagamento
     * @return void
     * @throws ValidationException
     */
    protected function validaContaFavorecido($pagamento)
    {
        $faltando = [];

        if (Util::onlyNumbers($pagamento->getAgencia()) === '') {
            $faltando[] = 'agência (10.3A)';
        }

        if (Util::onlyNumbers($pagamento->getConta()) === '') {
            $faltando[] = 'conta corrente (12.3A)';
        }

        if ((string) $pagamento->getContaDv() === '') {
            $faltando[] = 'dígito verificador da conta (13.3A)';
        }

        if ($faltando) {
            throw new ValidationException(sprintf(
                'Favorecido "%s": não informado(s) %s. O Segmento A do Sicoob exige esses campos '
                . 'para identificar a conta de destino. Informe-os no objeto de pagamento via '
                . 'setAgencia() / setConta() / setContaDv().',
                $pagamento->getBeneficiario() ? $pagamento->getBeneficiario()->getNome() : '?',
                implode(', ', $faltando)
            ));
        }
    }

    /**
     * Cria o segmento B CNAB 240 conforme especificação do Bancoob
     * @param Banco $pagamento
     * @return Bancoob
     * @throws \Exception
     */
    public function segmentoB($pagamento)
    {
        $this->iniciaDetalhe();

        // Controle
        $this->add(1, 3, self::BANCO); // 01.3B Banco - Código do Banco na Compensação
        $this->add(4, 7, self::LOTE_SERVICO_HEADER); // 02.3B Controle Lote - Lote de Serviço
        $this->add(8, 8, self::TIPO_REGISTRO_DETALHE); // 03.3B Registro - Tipo do Registro
        $this->add(9, 13, Util::formatCnab('9L', $this->iRegistrosLote, 5)); // 04.3B N° do Registro - Nº Seqüencial do Registro no Lote

        // Serviço
        $this->add(14, 14, self::CODIGO_SEGMENTO_B); // 05.3B Segmento - Código de Segmento do Reg. Detalhe
        // 06.3B (G100, pág. 48): Forma de Iniciação. Obrigatório apenas quando a forma de
        // lançamento for Pix Transferência (G029 = 45). Para TED (41) o campo é Opcional.
        $this->add(15, 17, self::CAMPO_BRANCO); // 06.3B Identificação do favorecido - Forma de Iniciação

        // Favorecido
        $this->add(18, 18, $pagamento->getBeneficiario()->getTipoDocumento() == 'CPF' ? self::TIPO_DOCUMENTO_CPF : self::TIPO_DOCUMENTO_CNPJ); // 07.3B Inscrição Tipo - Tipo de Inscrição do Favorecido
        $this->add(19, 32, Util::formatCnab('9L', $pagamento->getBeneficiario()->getDocumento(), 14)); // 08.3B Inscrição Número - N° de Inscrição do Favorecido

        // 09.3B Dados Complementares - Informação 10 (33-67).
        // Guia Sicoob CNAB 240 v4.0, item 7.3 (pág. 18): o Segmento B não expõe campos de
        // endereço avulsos; expõe três blocos (Informação 10, 11 e 12) cujo conteúdo interno
        // é definido em G101 (pág. 48-49). Para lançamentos que não sejam Pix, a Informação 10
        // é integralmente o logradouro do favorecido, ocupando as 35 posições do bloco.
        $this->add(33, 67, Util::formatCnab('X', $pagamento->getBeneficiario()->getEndereco(), 35)); // Informação 10 - Logradouro do Favorecido

        // 10.3B Dados Complementares - Informação 11 (68-127), subdividida conforme G101.
        // O número do local é campo Num: sem separador de logradouro em Pessoa, vai zerado
        // (o número, quando existe, já vem embutido no logradouro acima).
        $this->add(68, 72, Util::formatCnab('9L', '', 5)); // Informação 11 - Número do Local
        $this->add(73, 87, Util::formatCnab('X', '', 15)); // Informação 11 - Complemento (Casa, Apto, Etc)
        $this->add(88, 102, Util::formatCnab('X', $pagamento->getBeneficiario()->getBairro(), 15)); // Informação 11 - Bairro
        $this->add(103, 117, Util::formatCnab('X', $pagamento->getBeneficiario()->getCidade(), 15)); // Informação 11 - Cidade

        $cep = Util::formatCnab('9L', $pagamento->getBeneficiario()->getCep(), 8);

        $this->add(118, 122, substr($cep, 0, 5)); // Informação 11 - CEP
        $this->add(123, 125, substr($cep, 5, 3)); // Informação 11 - Complemento do CEP
        $this->add(126, 127, Util::formatCnab('X', $pagamento->getBeneficiario()->getUf(), 2)); // Informação 11 - Sigla do Estado

        // 11.3B Dados Complementares - Informação 12 (128-226), subdividida conforme G101.
        $this->add(128, 135, $pagamento->getDataVencimento()->format('dmY')); // Informação 12 - Data do Vencimento (Nominal)
        $this->add(136, 150, Util::formatCnab('9L', $pagamento->getValor(), 15)); // Informação 12 - Valor do Documento (Nominal)
        $this->add(151, 165, Util::formatCnab('9L', 0, 15)); // Informação 12 - Valor do Abatimento
        $this->add(166, 180, Util::formatCnab('9L', $pagamento->getDesconto() ?: 0, 15)); // Informação 12 - Valor do Desconto
        $this->add(181, 195, Util::formatCnab('9L', $pagamento->getJuros() ?: 0, 15)); // Informação 12 - Valor da Mora
        $this->add(196, 210, Util::formatCnab('9L', $pagamento->getMulta() ?: 0, 15)); // Informação 12 - Valor da Multa
        $this->add(211, 225, Util::formatCnab('X', $pagamento->getNumeroControle(), 15)); // Informação 12 - Código/Documento do Favorecido
        $this->add(226, 226, self::AVISO_FAVORECIDO); // Informação 12 - Aviso ao Favorecido
        $this->add(227, 232, Util::formatCnab('9L', 0, 6)); // 12.3B Código UG Centralizadora - Uso Exclusivo para o SIAPE
        $this->add(233, 240, self::CAMPO_BRANCO); // 13.3B CNAB - Uso Exclusivo FEBRABAN/CNAB

        return $this;
    }
}
