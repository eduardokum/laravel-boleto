<?php
/**
 * Created by PhpStorm.
 * User: Eduardo
 * Date: 25/11/2016
 * Time: 07:31
 */

namespace Eduardokum\LaravelBoleto\Cnab\Retorno;

use Countable;
use ReflectionClass;
use SeekableIterator;
use OutOfBoundsException;
use Eduardokum\LaravelBoleto\Util;
use Illuminate\Support\Collection;
use Eduardokum\LaravelBoleto\Exception\ValidationException;
use Eduardokum\LaravelBoleto\Contracts\Cnab\Retorno\Cnab240\Header as Header240Contract;
use Eduardokum\LaravelBoleto\Contracts\Cnab\Retorno\Cnab400\Header as Header400Contract;
use Eduardokum\LaravelBoleto\Contracts\Cnab\Retorno\Cnab240\Detalhe as Detalhe240Contract;
use Eduardokum\LaravelBoleto\Contracts\Cnab\Retorno\Cnab240\Trailer as Trailer240Contract;
use Eduardokum\LaravelBoleto\Contracts\Cnab\Retorno\Cnab400\Detalhe as Detalhe400Contract;
use Eduardokum\LaravelBoleto\Contracts\Cnab\Retorno\Cnab400\Trailer as Trailer400Contract;

abstract class AbstractRetorno implements Countable, SeekableIterator
{
    /**
     * Se cnab ja foi processado
     *
     * @var bool
     */
    protected $processado = false;

    /**
     * Código do banco
     *
     * @var string
     */
    protected $codigoBanco;

    /**
     * Incremento de detalhes
     *
     * @var int
     */
    protected $increment = 0;

    /**
     * Arquivo transformado em array por linha.
     *
     * @var array
     */
    protected $file;

    /**
     * @var Header240Contract|Header400Contract
     */
    protected $header;

    /**
     * @var Trailer240Contract|Trailer400Contract
     */
    protected $trailer;

    /**
     * @var Detalhe240Contract[]|Detalhe400Contract[]
     */
    protected $detalhe = [];

    /**
     * Helper de totais.
     *
     * @var array
     */
    protected $totais = [];

    /**
     * @var int
     */
    protected $_position = 1;

    /**
     * @param string $file
     * @throws ValidationException
     */
    public function __construct($file)
    {
        $this->_position = 1;

        if (! $this->file = Util::file2array($file)) {
            throw new ValidationException('Arquivo: não existe');
        }

        $r = new ReflectionClass('\Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto');
        $constantNames = $r->getConstants();
        $bancosDisponiveis = [];
        foreach ($constantNames as $constantName => $codigoBanco) {
            if (preg_match('/^COD_BANCO.*/', $constantName)) {
                $bancosDisponiveis[] = $codigoBanco;
            }
        }

        if (! Util::isHeaderRetorno($this->file[0])) {
            throw new ValidationException('Arquivo de retorno inválido');
        }

        $banco = Util::isCnab400($this->file[0]) ? mb_substr($this->file[0], 76, 3) : mb_substr($this->file[0], 0, 3);
        if (! in_array($banco, $bancosDisponiveis)) {
            throw new ValidationException(sprintf('Banco: %s, inválido', $banco));
        }
    }

    /**
     * Retorna o código do banco
     *
     * @return string
     */
    public function getCodigoBanco()
    {
        return $this->codigoBanco;
    }

    /**
     * @return string
     */
    public function getBancoNome()
    {
        return Util::$bancos[$this->codigoBanco];
    }

    /**
     * @return int
     */
    public function getTipo()
    {
        return Util::isCnab400($this->file[0]) ? 400 : 240;
    }

    /**
     * @return mixed
     */
    public function getFileContent()
    {
        return implode(PHP_EOL, $this->file);
    }

    /**
     * @return Collection
     */
    public function getDetalhes()
    {
        return new Collection($this->detalhe);
    }

    /**
     * @param $i
     *
     * @return Detalhe240Contract|Detalhe400Contract|null
     */
    public function getDetalhe($i)
    {
        return array_key_exists($i, $this->detalhe) ? $this->detalhe[$i] : null;
    }

    /**
     * @return Header240Contract|Header400Contract
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * @return Trailer240Contract|Trailer400Contract
     */
    public function getTrailer()
    {
        return $this->trailer;
    }

    /**
     * @return array
     */
    public function getTotais()
    {
        return $this->totais;
    }

    /**
     * Retorna o detalhe atual.
     *
     * @return Detalhe240Contract|Detalhe400Contract
     */
    protected function detalheAtual()
    {
        return $this->detalhe[$this->increment];
    }

    /**
     * Se está processado
     *
     * @return bool
     */
    protected function isProcessado()
    {
        return $this->processado;
    }

    /**
     * Seta cnab como processado
     *
     * @return $this
     */
    protected function setProcessado()
    {
        $this->processado = true;

        return $this;
    }

    /**
     * Incrementa o detalhe.
     */
    abstract protected function incrementDetalhe();

    /**
     * Processa o arquivo
     *
     * @return $this
     */
    abstract protected function processar();

    /**
     * Retorna o array.
     *
     * @return array
     */
    abstract protected function toArray();

    /**
     * Remove trecho do array.
     *
     * @param $i
     * @param $f
     * @param $array
     *
     * @return string
     * @throws ValidationException
     */
    protected function rem($i, $f, &$array)
    {
        return Util::remove($i, $f, $array);
    }

    public function current()
    {
        return $this->detalhe[$this->_position];
    }

    public function next()
    {
        $this->_position++;
    }

    public function key()
    {
        return $this->_position;
    }

    public function valid()
    {
        return isset($this->detalhe[$this->_position]);
    }

    public function rewind()
    {
        $this->_position = 1;
    }

    public function count()
    {
        return count($this->detalhe);
    }

    public function seek($offset)
    {
        $this->_position = $offset;
        if (! $this->valid()) {
            throw new OutOfBoundsException('"Posição inválida "$position"');
        }
    }
}
