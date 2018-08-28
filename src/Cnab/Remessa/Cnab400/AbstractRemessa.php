<?php
namespace Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400;

use Eduardokum\LaravelBoleto\Cnab\Remessa\AbstractRemessa as AbstractRemessaGeneric;
use ForceUTF8\Encoding;

abstract class AbstractRemessa extends AbstractRemessaGeneric
{
    protected $tamanho_linha = 400;

    /**
     * Inicia a edição do header
     */
    protected function iniciaHeader()
    {
        $this->aRegistros[self::HEADER] = array_fill(0, $this->tamanho_linha, ' ');
        $this->atual = &$this->aRegistros[self::HEADER];
    }

    /**
     * Inicia a edição do trailer (footer).
     */
    protected function iniciaTrailer()
    {
        $this->aRegistros[self::TRAILER] = array_fill(0, $this->tamanho_linha, ' ');
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
        if (!$this->isValid($messages)) {
            throw new \Exception('Campos requeridos pelo banco, aparentam estar ausentes ' . $messages);
        }

        $stringRemessa = '';
        if ($this->iRegistros < 1) {
            throw new \Exception('Nenhuma linha detalhe foi adicionada');
        }

        $this->header();
        $stringRemessa .= $this->valida($this->getHeader()) . $this->fimLinha;

        foreach ($this->getDetalhes() as $i => $detalhe) {
            $stringRemessa .= $this->valida($detalhe) . $this->fimLinha;
        }

        $this->trailer();
        $stringRemessa .= $this->valida($this->getTrailer()) . $this->fimArquivo;

        return Encoding::toUTF8($stringRemessa);
    }
}
