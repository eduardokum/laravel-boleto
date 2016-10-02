<?php

namespace Eduardokum\LaravelBoleto\Contracts\Cnab\Retorno\Cnab240;


interface TrailerArquivo
{

    /**
     * @return mixed
     */
    public function getTipoRegistro();

    /**
     * @return mixed
     */
    public function getNumeroLoteRemessa();

    /**
     * @return mixed
     */
    public function getQtdLotesArquivo();

    /**
     * @return mixed
     */
    public function getQtdRegistroArquivo();
}