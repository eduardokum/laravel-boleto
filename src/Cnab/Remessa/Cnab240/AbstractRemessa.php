<?php
namespace Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab240;

use Eduardokum\LaravelBoleto\Cnab\Remessa\AbstractRemessa as AbstractRemessaGeneric;

abstract class AbstractRemessa extends AbstractRemessaGeneric
{
    protected $tamanho_linha = 240;

    /**
     * Array contendo o cnab.
     *
     * @var array
     */
    protected $aRegistros = [
        self::HEADER => [],
        self::HEADER_LOTE => [],
        self::DETALHE => [],
        self::TRAILER_LOTE => [],
        self::TRAILER => [],
    ];

    /**
     * Função para gerar o cabeçalho do arquivo.
     *
     * @return mixed
     */
    abstract protected function headerLote();


    /**
     * Função que gera o trailer (footer) do arquivo.
     *
     * @return mixed
     */
    abstract protected function trailerLote();

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
        $this->aRegistros[self::HEADER] = array_fill(0, 240, ' ');
        $this->atual = &$this->aRegistros[self::HEADER];
    }

    /**
     * Inicia a edição do header
     */
    protected function iniciaHeaderLote()
    {
        $this->aRegistros[self::HEADER_LOTE] = array_fill(0, 240, ' ');
        $this->atual = &$this->aRegistros[self::HEADER_LOTE];
    }

    /**
     * Inicia a edição do trailer (footer).
     */
    protected function iniciaTrailerLote()
    {
        $this->aRegistros[self::TRAILER_LOTE] = array_fill(0, 240, ' ');
        $this->atual = &$this->aRegistros[self::TRAILER_LOTE];
    }

    /**
     * Inicia a edição do trailer (footer).
     */
    protected function iniciaTrailer()
    {
        $this->aRegistros[self::TRAILER] = array_fill(0, 240, ' ');
        $this->atual = &$this->aRegistros[self::TRAILER];
    }

    /**
     * Inicia uma nova linha de detalhe e marca com a atual de edição
     */
    protected function iniciaDetalhe()
    {
        $this->iRegistros++;
        $this->aRegistros[self::DETALHE][$this->iRegistros] = array_fill(0, 240, ' ');
        $this->atual = &$this->aRegistros[self::DETALHE][$this->iRegistros];
    }

    /**
     * Gera o arquivo, retorna a string.
     *
     * @return string
     * @throws \Exception
     */
    public function gerar()
    {
        if (!$this->isValid()) {
            throw new \Exception('Campos requeridos pelo banco, aparentam estar ausentes');
        }

        $stringRemessa = '';
        if ($this->iRegistros < 1) {
            throw new \Exception('Nenhuma linha detalhe foi adicionada');
        }

        $this->header();
        $stringRemessa .= $this->valida($this->getHeader()) . $this->fimLinha;

        $this->headerLote();
        $stringRemessa .= $this->valida($this->getHeaderLote()) . $this->fimLinha;

        foreach ($this->getDetalhes() as $i => $detalhe) {
            $stringRemessa .= $this->valida($detalhe) . $this->fimLinha;
        }

        $this->trailerLote();
        $stringRemessa .= $this->valida($this->getTrailerLote()) . $this->fimLinha;

        $this->trailer();
        $stringRemessa .= $this->valida($this->getTrailer()) . $this->fimArquivo;

        return $stringRemessa;
    }
}
