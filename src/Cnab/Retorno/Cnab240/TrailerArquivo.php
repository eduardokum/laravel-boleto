<?php

namespace Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab240;


class TrailerArquivo
{
    private $numeroLoteRemessa;
    private $tipoRegistro;
    private $qtdLotesArquivo;
    private $qtdRegistroArquivo;

    /**
     * @return mixed
     */
    public function getTipoRegistro()
    {
        return $this->tipoRegistro;
    }

    /**
     * @param mixed $numeroLoteRemessa
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
     */
    public function setTipoRegistro($tipoRegistro)
    {
        $this->tipoRegistro = $tipoRegistro;

        return $this;
    }

}