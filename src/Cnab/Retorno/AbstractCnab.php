<?php
namespace Eduardokum\LaravelBoleto\Cnab\Retorno;

use Eduardokum\LaravelBoleto\Cnab\Contracts\Cnab;
use Eduardokum\LaravelBoleto\Cnab\Contracts\Retorno\Detalhe;

abstract class AbstractCnab implements Cnab, \Countable, \SeekableIterator
{

    protected $bancos = [
        '001' => 'Banco do Brasil S.A.',
        '033' => 'Banco Santander (Brasil) S.A.',
        '104' => 'Caixa Econômica Federal',
        '237' => 'Banco Bradesco S.A.',
        '341' => 'Itaú Unibanco S.A.',
        '399' => 'HSBC Bank Brasil S.A. - Banco Múltiplo',
    ];

    protected $file;
    private $_position = 0;
    protected $banco;
    protected $bancoDesc;
    protected $isRetorno = false;

    /**
     * @var Header\Bb|Header\Bradesco|Header\Caixa|Header\Hsbc|Header\Itau|Header\Santander
     */
    protected $header;

    /**
     * @var Trailer\Bb|Trailer\Bradesco|Trailer\Caixa|Trailer\Hsbc|Trailer\Itau|Trailer\Santander
     */
    protected $trailer;

    /**
     * @var Detalhe
     */
    protected $detalhe = [];

    /**
     *
     * @param String $file
     * @throws \Exception
     */
    public function __construct($file) {
        $this->_position = 0;
        if(is_array($file) && strlen(rtrim($file[0], chr(10).chr(13)."\n"."\r")) == 400)
        {
            $this->file = $file;
        }
        else if(is_file($file) && file_exists($file))
        {
            $this->file = file($file);
        }
        else
        {
            throw new \Exception("Arquivo: $file, não existe");
        }

        $this->isRetorno = (substr($this->file[0], 0, 9) == '02RETORNO') ? true : false;
        if(!in_array(substr($this->file[0], 76, 3), array_keys($this->bancos)))
        {
            throw new \Exception(sprintf("Banco: %s, inválido", substr($this->file[0], 76, 3)));
        }
    }

    protected abstract function processarHeader(array $header);

    protected abstract function processarDetalhe(array $detalhe);

    protected abstract function processarTrailer(array $trailer);

    public function processar() {
        if(!$this->isRetorno)
        {
            throw new \Exception("Arquivo de retorno inválido");
        }

        foreach($this->file as $linha) {
            $aLinha = str_split(rtrim($linha, chr(10).chr(13)."\n"."\r"), 1);
            if( $aLinha[0] == '0' ) {
                $this->processarHeader($aLinha);
            } else if( $aLinha[0] == '9' ) {
                $this->processarTrailer($aLinha);
            } else {
                $this->processarDetalhe($aLinha);
            }
        }
        if(method_exists($this,'finalize')) {
            $this->finalize();
        }
        unset($this->cnab);
    }

    protected function rem($i, $f, $array)
    {
        $i--;

        if ($i > 398 || $f > 400) {
            throw new \Exception('$ini ou $fim ultrapassam o limite máximo de 400');
        }

        if ($f < $i) {
            throw new \Exception('$ini é maior que o $fim');
        }

        $t = $f - $i;

        return implode('',array_splice($array, $i, $t));
    }

    public function getDetalhes() {
        return $this->detalhe;
    }

    public function getHeader() {
        return $this->header;
    }

    public function getTrailer() {
        return $this->trailer;
    }

    public function current() {
        return $this->detalhe[$this->_position];
    }

    public function next() {
        ++$this->_position;
    }

    public function prev() {
        --$this->_position;
    }

    public function key() {
        return $this->_position;
    }

    public function valid() {
        return isset($this->detalhe[$this->_position]);
    }

    public function rewind() {
        $this->_position = 0;
    }

    public function count() {
        return count($this->detalhe);
    }

    public function seek($position) {
        $this->_position = $position;
        if (!$this->valid()) {
            throw new \OutOfBoundsException('"Posição inválida "$position"');
        }
    }
}