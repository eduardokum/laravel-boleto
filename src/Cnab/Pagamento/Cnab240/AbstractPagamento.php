<?php

namespace Eduardokum\LaravelBoleto\Cnab\Pagamento\Cnab240;

use ForceUTF8\Encoding;
use Eduardokum\LaravelBoleto\Exception\ValidationException;
use Eduardokum\LaravelBoleto\Cnab\Pagamento\AbstractPagamento as AbstractPagamentoGeneric;
use Eduardokum\LaravelBoleto\Pagamento\Banco\Banco;
use Eduardokum\LaravelBoleto\Util;

abstract class AbstractPagamento extends AbstractPagamentoGeneric
{
    protected $tamanho_linha = 240;

    /**
     * Caractere de fim de linha
     *
     * @var string
     */
    protected $fimLinha = "\r\n";

    /**
     * Caractere de fim de arquivo
     *
     * @var null
     */
    protected $fimArquivo = "\r\n";

    /**
     * Array contendo o cnab.
     *
     * @var array
     */
    protected $aRegistros = [
        self::HEADER       => [],
        self::HEADER_LOTE  => [],
        self::DETALHE      => [],
        self::TRAILER_LOTE => [],
        self::TRAILER      => [],
    ];

    /**
     * Quantidade de registros do lote.
     */
    protected $iRegistrosLote;

    /**
     * Array de lotes organizados por tipo de pagamento
     * @var array
     */
    protected $lotes = [];

    /**
     * Contador de lotes
     * @var int
     */
    protected $loteCounter = 0;

    /**
     * Função para gerar o cabeçalho do arquivo.
     *
     * @return mixed
     */
    abstract protected function header();

    /**
     * Função para gerar o cabeçalho do lote.
     *
     * @return mixed
     */
    abstract protected function headerLote();

    /**
     * Função que gera o trailer (footer) do lote.
     *
     * @return mixed
     */
    abstract protected function trailerLote();

    /**
     * Função que gera o trailer (footer) do arquivo.
     *
     * @return mixed
     */
    abstract protected function trailer();

    /**
     * Retorna o header do lote.
     *
     * @return mixed
     */
    protected function getHeaderLote()
    {
        return $this->aRegistros[self::HEADER_LOTE];
    }

    /**
     * Retorna o trailer do lote.
     *
     * @return mixed
     */
    protected function getTrailerLote()
    {
        return $this->aRegistros[self::TRAILER_LOTE];
    }

    /**
     * Inicia a edição do header
     */
    protected function iniciaHeader()
    {
        $this->aRegistros[self::HEADER] = array_fill(0, $this->tamanho_linha, ' ');
        $this->tamanhos_linha[self::HEADER] = $this->tamanho_linha;
        $this->atual = &$this->aRegistros[self::HEADER];
        $this->tamanho_atual = &$this->tamanhos_linha[self::HEADER];
    }

    /**
     * Inicia a edição do header do lote
     */
    protected function iniciaHeaderLote()
    {
        $this->iRegistrosLote = 0;
        $this->aRegistros[self::HEADER_LOTE] = array_fill(0, $this->tamanho_linha, ' ');
        $this->tamanhos_linha[self::HEADER_LOTE] = $this->tamanho_linha;
        $this->atual = &$this->aRegistros[self::HEADER_LOTE];
        $this->tamanho_atual = &$this->tamanhos_linha[self::HEADER_LOTE];
    }

    /**
     * Inicia a edição do trailer (footer) do lote.
     */
    protected function iniciaTrailerLote()
    {
        $this->aRegistros[self::TRAILER_LOTE] = array_fill(0, $this->tamanho_linha, ' ');
        $this->tamanhos_linha[self::TRAILER_LOTE] = $this->tamanho_linha;
        $this->atual = &$this->aRegistros[self::TRAILER_LOTE];
        $this->tamanho_atual = &$this->tamanhos_linha[self::TRAILER_LOTE];
    }

    /**
     * Inicia a edição do trailer (footer) do arquivo.
     */
    protected function iniciaTrailer()
    {
        $this->aRegistros[self::TRAILER] = array_fill(0, $this->tamanho_linha, ' ');
        $this->tamanhos_linha[self::TRAILER] = $this->tamanho_linha;
        $this->atual = &$this->aRegistros[self::TRAILER];
        $this->tamanho_atual = &$this->tamanhos_linha[self::TRAILER];
    }

    /**
     * Inicia uma nova linha de detalhe e marca com a atual de edição
     */
    protected function iniciaDetalhe()
    {
        $this->iRegistros++;
        $this->iRegistrosLote++;
        $this->aRegistros[self::DETALHE][$this->iRegistros] = array_fill(0, $this->tamanho_linha, ' ');
        $this->tamanhos_linha[self::DETALHE][$this->iRegistros] = $this->tamanho_linha;
        $this->atual = &$this->aRegistros[self::DETALHE][$this->iRegistros];
        $this->tamanho_atual = &$this->tamanhos_linha[self::DETALHE][$this->iRegistros];
    }

    /**
     * Função que mostra a quantidade de linhas do arquivo.
     *
     * @return int
     */
    protected function getCountDetalhes()
    {
        return count($this->aRegistros[self::DETALHE]);
    }

    /**
     * Função que mostra a quantidade de linhas do arquivo.
     *
     * @return int
     */
    protected function getCount()
    {
        return $this->getCountDetalhes() + 4;
    }

    /**
     * Gera o arquivo, retorna a string.
     *
     * @return string
     * @throws ValidationException
     */
    public function gerar()
    {
        if (! $this->isValid($messages)) {
            throw new ValidationException('Campos requeridos pelo banco, aparentam estar ausentes ' . $messages);
        }

        $stringRemessa = '';

        // Agrupa pagamentos por tipo
        $this->agruparPagamentosPorTipo();

        if (empty($this->lotes)) {
            throw new ValidationException('Nenhum pagamento foi adicionado');
        }

        // Header do arquivo
        $this->header();
        $stringRemessa .= $this->valida($this->getHeader()) . $this->fimLinha;

        // Processa cada lote
        foreach ($this->lotes as $tipoPagamento => $lote) {
            // Header do lote
            $this->headerLoteMulti($lote);
            $stringRemessa .= $this->valida($this->getHeaderLote()) . $this->fimLinha;

            // Gera segmentos diretamente para cada pagamento do lote
            foreach ($lote['pagamentos'] as $pagamento) {
                $this->gerarSegmentos($pagamento);
            }

            // Detalhes do lote
            foreach ($this->getDetalhes() as $detalhe) {
                $stringRemessa .= $this->valida($detalhe) . $this->fimLinha;
            }

            // Trailer do lote
            $this->trailerLoteMulti($lote);
            $stringRemessa .= $this->valida($this->getTrailerLote()) . $this->fimLinha;

            // Limpa detalhes para o próximo lote
            $this->aRegistros[self::DETALHE] = [];
            $this->iRegistros = 0;
            $this->iRegistrosLote = 0;
        }

        // Trailer do arquivo
        $this->trailerMulti();
        $stringRemessa .= $this->valida($this->getTrailer()) . $this->fimArquivo;

        return Encoding::toUTF8($stringRemessa);
    }

    /**
     * Agrupa pagamentos por tipo automaticamente
     */
    protected function agruparPagamentosPorTipo()
    {
        $this->lotes = [];
        $this->loteCounter = 0;

        foreach ($this->pagamentos as $pagamento) {
            $tipoPagamento = $this->getTipoPagamentoDoPagamento($pagamento);

            // Se não existe lote para este tipo, cria um novo
            if (!isset($this->lotes[$tipoPagamento])) {
                $this->lotes[$tipoPagamento] = [
                    'numero' => ++$this->loteCounter,
                    'tipo' => $tipoPagamento,
                    'pagamentos' => []
                ];
            }

            // Adiciona o pagamento ao lote
            $this->lotes[$tipoPagamento]['pagamentos'][] = $pagamento;
        }
    }

    /**
     * Retorna o tipo de pagamento de um pagamento específico
     * 
     * @param Banco $pagamento
     * @return string
     */
    protected function getTipoPagamentoDoPagamento(Banco $pagamento)
    {
        // Se o pagamento tem um método getTipoPagamento, usa ele
        if (method_exists($pagamento, 'getTipoPagamento')) {
            return $pagamento->getTipoPagamento();
        }

        // Tenta acessar a propriedade diretamente (caso seja pública)
        if (isset($pagamento->tipoPagamento) && !empty($pagamento->tipoPagamento)) {
            return $pagamento->tipoPagamento;
        }

        // Se o pagamento tem uma propriedade tipoPagamento, usa ela
        if (property_exists($pagamento, 'tipoPagamento') && !empty($pagamento->tipoPagamento)) {
            return $pagamento->tipoPagamento;
        }

        // Se não, usa o tipo padrão 'TED'
        return 'TED';
    }

    /**
     * Header do lote para múltiplos lotes (sobrescrever nas classes filhas)
     *
     * @param array $lote
     * @return mixed
     */
    protected function headerLoteMulti(array $lote)
    {
        return $this->headerLote();
    }

    /**
     * Trailer do lote para múltiplos lotes (sobrescrever nas classes filhas)
     *
     * @param array $lote
     * @return mixed
     */
    protected function trailerLoteMulti(array $lote)
    {
        return $this->trailerLote();
    }

    /**
     * Trailer do arquivo para múltiplos lotes (sobrescrever nas classes filhas)
     *
     * @return mixed
     */
    protected function trailerMulti()
    {
        return $this->trailer();
    }

    /**
     * Retorna a quantidade de lotes
     *
     * @return int
     */
    protected function getCountLotes()
    {
        return count($this->lotes);
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

        // Soma todos os valores dos pagamentos no lote
        foreach ($lote['pagamentos'] as $pagamento) {
            if (method_exists($pagamento, 'getValor') && $pagamento->getValor() > 0)
                $valorTotal += $pagamento->getValor();
        }

        return Util::formatCnab('9L', $valorTotal * 100, 18);
    }

    /**
     * Processa um pagamento gerando os segmentos necessários
     *
     * @param Banco $pagamento
     * @return void
     */
    protected function processarPagamento(Banco $pagamento)
    {
        // Gera os segmentos diretamente sem chamar addPagamento novamente
        $this->gerarSegmentos($pagamento);
    }

    /**
     * Gera os segmentos de um pagamento (sobrescrever nas classes filhas)
     *
     * @param Banco $pagamento
     * @return void
     */
    protected function gerarSegmentos(Banco $pagamento)
    {
        // Método abstrato - deve ser implementado pelas classes filhas
        // Não chama addPagamento aqui para evitar duplicação
        throw new \Exception('Método gerarSegmentos deve ser implementado pela classe filha');
    }
}
