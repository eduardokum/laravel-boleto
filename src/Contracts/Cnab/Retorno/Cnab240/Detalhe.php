<?php

namespace Eduardokum\LaravelBoleto\Contracts\Cnab\Retorno\Cnab240;

use Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab240\SegmentoT;
use Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab240\SegmentoU;
use Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab240\SegmentoY;

interface Detalhe
{

    const OCORRENCIA_LIQUIDADA = 1;
    const OCORRENCIA_BAIXADA = 2;
    const OCORRENCIA_ENTRADA = 3;
    const OCORRENCIA_ALTERACAO = 4;
    const OCORRENCIA_PROTESTADA = 5;
    const OCORRENCIA_OUTROS = 6;
    const OCORRENCIA_ERRO = 9;

    /**
     * @return SegmentoT
     */
    public function getSegmentoT();

    /**
     * @return SegmentoU
     */
    public function getSegmentoU();

    /**
     * @return SegmentoY
     */
    public function getSegmentoY();

    /**
     * @return boolean
     */
    public function hasOcorrencia();

    /**
     * @return string
     */
    public function getOcorrenciaDescricao();

    /**
     * @return string
     */
    public function getOcorrenciaTipo();

    /**
     * @return mixed
     */
    public function getCodigoOcorrencia();

    /**
     * @return string
     */
    public function getError();

    /**
     * @return string
     */
    public function getErrorCode();

    /**
     * @return boolean
     */
    public function hasError();

    /**
     * @return array
     */
    public function toArray();

}