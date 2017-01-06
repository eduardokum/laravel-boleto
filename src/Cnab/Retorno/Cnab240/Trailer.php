<?php

namespace Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab240;

use \Eduardokum\LaravelBoleto\Contracts\Cnab\Retorno\Cnab240\Trailer as TrailerContract;

class Trailer implements TrailerContract
{
    use MagicTrait;
    /**
     * @var integer
     */
    protected $numeroLoteRemessa;

    /**
     * @var integer
     */
    protected $tipoRegistro;

    /**
     * @var integer
     */
    protected $qtdLotesArquivo;

    /**
     * @var integer
     */
    protected $qtdRegistroArquivo;

    /**
     * @return mixed
     */
    public function getTipoRegistro()
    {
        return $this->tipoRegistro;
    }

    /**
     * @param mixed $numeroLoteRemessa
     *
     * @return $this
     */
    public function setNumeroLoteRemessa($numeroLoteRemessa)
    {
        $this->numeroLoteRemessa = $numeroLoteRemessa;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getNumeroLoteRemessa()
    {
        return $this->numeroLoteRemessa;
    }

    /**
     * @param mixed $qtdLotesArquivo
     *
     * @return $this
     */
    public function setQtdLotesArquivo($qtdLotesArquivo)
    {
        $this->qtdLotesArquivo = $qtdLotesArquivo;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getQtdLotesArquivo()
    {
        return $this->qtdLotesArquivo;
    }

    /**
     * @param mixed $qtdRegistroArquivo
     *
     * @return $this
     */
    public function setQtdRegistroArquivo($qtdRegistroArquivo)
    {
        $this->qtdRegistroArquivo = $qtdRegistroArquivo;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getQtdRegistroArquivo()
    {
        return $this->qtdRegistroArquivo;
    }

    /**
     * @param mixed $tipoRegistro
     *
     * @return $this
     */
    public function setTipoRegistro($tipoRegistro)
    {
        $this->tipoRegistro = $tipoRegistro;

        return $this;
    }
}