<?php

namespace Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400;

use ForceUTF8\Encoding;
use Eduardokum\LaravelBoleto\Exception\ValidationException;
use Eduardokum\LaravelBoleto\Cnab\Remessa\AbstractRemessa as AbstractRemessaGeneric;

abstract class AbstractRemessa extends AbstractRemessaGeneric
{
    protected $tamanho_linha = 400;

    protected $tamanhos_linha = [];

    /**
     * Inicia a edição do header
     */
    protected function iniciaHeader()
    {
        $this->aRegistros[self::HEADER] = array_fill(0, $this->tamanho_linha, ' ');
        $this->tamanhos_linha[self::HEADER] = $this->tamanho_linha;
        $this->atual = &$this->aRegistros[self::HEADER];
    }

    /**
     * Inicia a edição do trailer (footer).
     */
    protected function iniciaTrailer()
    {
        $this->aRegistros[self::TRAILER] = array_fill(0, $this->tamanho_linha, ' ');
        $this->tamanhos_linha[self::TRAILER] = $this->tamanho_linha;
        $this->atual = &$this->aRegistros[self::TRAILER];
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
        return $this->getCountDetalhes() + 2;
    }

    /**
     * Inicia uma nova linha de detalhe e marca com a atual de edição
     */
    protected function iniciaDetalhe()
    {
        $this->iRegistros++;
        $this->aRegistros[self::DETALHE][$this->iRegistros] = array_fill(0, $this->tamanho_linha, ' ');
        $this->tamanhos_linha[self::DETALHE][$this->iRegistros] = $this->tamanho_linha;
        $this->atual = &$this->aRegistros[self::DETALHE][$this->iRegistros];
    }

    /**
     * Inicia uma nova linha de detalhe extendido e marca com a atual de edição
     */
    protected function iniciaDetalheExtendido($extencao = 44)
    {
        $this->iRegistros++;
        $this->aRegistros[self::DETALHE][$this->iRegistros] = array_fill(0, $this->tamanho_linha + $extencao, ' ');
        $this->tamanhos_linha[self::DETALHE][$this->iRegistros] = $this->tamanho_linha + $extencao;
        $this->atual = &$this->aRegistros[self::DETALHE][$this->iRegistros];
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
        if ($this->iRegistros < 1) {
            throw new ValidationException('Nenhuma linha detalhe foi adicionada');
        }

        $this->header();
        $stringRemessa .= $this->valida($this->getHeader()) . $this->fimLinha;

        foreach ($this->getDetalhes() as $i => $detalhe) {
            if ($this->tamanhos_linha[self::DETALHE][$i] != 400) {
                $stringRemessa .= $this->valida($detalhe, $this->tamanhos_linha[self::DETALHE][$i] - 400) . $this->fimLinha;
            } else {
                $stringRemessa .= $this->valida($detalhe) . $this->fimLinha;
            }
        }

        $this->trailer();
        $stringRemessa .= $this->valida($this->getTrailer()) . $this->fimArquivo;

        return Encoding::toUTF8($stringRemessa);
    }
}
