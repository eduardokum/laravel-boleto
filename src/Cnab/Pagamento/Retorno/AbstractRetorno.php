<?php

namespace Eduardokum\LaravelBoleto\Cnab\Pagamento\Retorno;

use Countable;
use SeekableIterator;
use OutOfBoundsException;
use Eduardokum\LaravelBoleto\Util;
use Eduardokum\LaravelBoleto\Exception\ValidationException;

abstract class AbstractRetorno implements Countable, SeekableIterator
{
    /**
     * Se o arquivo já foi processado
     * @var bool
     */
    protected $processado = false;

    /**
     * Código do banco
     * @var string
     */
    protected $codigoBanco;

    /**
     * Incremento de detalhes
     * @var int
     */
    protected $increment = 0;

    /**
     * Arquivo transformado em array por linha
     * @var array
     */
    protected $file;

    /**
     * @var mixed
     */
    protected $header;

    /**
     * @var mixed
     */
    protected $trailer;

    /**
     * @var array
     */
    protected $detalhe = [];

    /**
     * Helper de totais
     * @var array
     */
    protected $totais = [];

    /**
     * @var int
     */
    protected $_position = 1;

    /**
     * @param string|array $file
     * @throws ValidationException
     */
    public function __construct($file)
    {
        if (is_array($file)) {
            $this->file = $file;
        } elseif (is_string($file)) {
            $this->file = Util::file2array($file);
        } else {
            throw new ValidationException('Arquivo inválido');
        }
    }

    /**
     * @return mixed
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * @return mixed
     */
    public function getTrailer()
    {
        return $this->trailer;
    }

    /**
     * @return array
     */
    public function getDetalhes()
    {
        return $this->detalhe;
    }

    /**
     * @param int $i
     * @return mixed
     * @throws ValidationException
     */
    public function getDetalhe($i)
    {
        if (!array_key_exists($i, $this->detalhe)) {
            throw new ValidationException("Detalhe $i não existe");
        }

        return $this->detalhe[$i];
    }

    /**
     * @return mixed
     */
    protected function detalheAtual()
    {
        return $this->detalhe[$this->increment];
    }

    /**
     * @return array
     */
    public function getTotais()
    {
        return $this->totais;
    }

    /**
     * @return string
     */
    public function getBanco()
    {
        return $this->codigoBanco;
    }

    /**
     * @return bool
     */
    public function isProcessado()
    {
        return $this->processado;
    }

    /**
     * Remove trecho de uma string
     *
     * @param int $i
     * @param int $f
     * @param string $str
     *
     * @return string
     */
    protected function rem($i, $f, &$str)
    {
        return Util::remove($i, $f, $str);
    }

    /**
     * Retorna o tipo do segmento
     *
     * @param string $linha
     * @return string
     */
    protected function getSegmentType($linha)
    {
        return $this->rem(14, 14, $linha);
    }

    /**
     * Processa o arquivo
     *
     * @return mixed
     */
    abstract public function processar();

    /**
     * Implementação do Countable
     */
    public function count(): int
    {
        return count($this->detalhe);
    }

    /**
     * Implementação do SeekableIterator
     */
    public function rewind(): void
    {
        $this->_position = 1;
    }

    public function current(): mixed
    {
        return $this->detalhe[$this->_position];
    }

    public function key(): mixed
    {
        return $this->_position;
    }

    public function next(): void
    {
        ++$this->_position;
    }

    public function valid(): bool
    {
        return isset($this->detalhe[$this->_position]);
    }

    public function seek($position): void
    {
        $this->_position = $position;

        if (!$this->valid()) {
            throw new OutOfBoundsException("Posição inválida ($position)");
        }
    }
}
