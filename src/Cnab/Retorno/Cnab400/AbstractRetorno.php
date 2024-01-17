<?php

namespace Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab400;

use Illuminate\Support\Collection;
use Eduardokum\LaravelBoleto\Exception\ValidationException;
use Eduardokum\LaravelBoleto\Cnab\Retorno\AbstractRetorno as AbstractRetornoGeneric;

/**
 * Class AbstractRetorno
 *
 * @method  Detalhe[] getDetalhes()
 * @method  Detalhe getDetalhe($i)
 * @method  Header getHeader()
 * @method  Trailer getTrailer()
 * @method  Detalhe detalheAtual()
 */
abstract class AbstractRetorno extends AbstractRetornoGeneric
{
    /**
     * @param string $file
     * @throws ValidationException
     */
    public function __construct($file)
    {
        parent::__construct($file);

        $this->header = new Header();
        $this->trailer = new Trailer();
    }

    /**
     * @param array $header
     *
     * @return bool
     */
    abstract protected function processarHeader(array $header);

    /**
     * @param array $detalhe
     *
     * @return bool
     */
    abstract protected function processarDetalhe(array $detalhe);

    /**
     * @param array $trailer
     *
     * @return bool
     */
    abstract protected function processarTrailer(array $trailer);

    /**
     * Incrementa o detalhe.
     */
    protected function incrementDetalhe()
    {
        $this->increment++;
        $this->detalhe[$this->increment] = new Detalhe();
    }

    /**
     * Processa o arquivo
     *
     * @return $this
     * @throws ValidationException
     */
    public function processar()
    {
        if ($this->isProcessado()) {
            return $this;
        }

        if (method_exists($this, 'init')) {
            call_user_func([$this, 'init']);
        }

        foreach ($this->file as $linha) {
            $inicio = $this->rem(1, 1, $linha);

            if ($inicio == '0') {
                $this->processarHeader($linha);
            } elseif ($inicio == '9') {
                $this->processarTrailer($linha);
            } else {
                $this->incrementDetalhe();
                if ($this->processarDetalhe($linha) === false) {
                    unset($this->detalhe[$this->increment]);
                    $this->increment--;
                }
            }
        }
        if (method_exists($this, 'finalize')) {
            call_user_func([$this, 'finalize']);
        }

        return $this->setProcessado();
    }

    /**
     * Retorna o array.
     *
     * @return array
     */
    public function toArray()
    {
        $array = [
            'header'   => $this->header->toArray(),
            'trailer'  => $this->trailer->toArray(),
            'detalhes' => new Collection(),
        ];
        foreach ($this->detalhe as $detalhe) {
            $array['detalhes']->push($detalhe->toArray());
        }

        return $array;
    }
}
