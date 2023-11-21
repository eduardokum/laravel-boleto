<?php

namespace Eduardokum\LaravelBoleto\Contracts\Cnab;

use Illuminate\Support\Collection;

interface RetornoCnab400 extends Retorno
{
    /**
     * @return mixed
     */
    public function getCodigoBanco();

    /**
     * @return mixed
     */
    public function getBancoNome();

    /**
     * @return Collection
     */
    public function getDetalhes();

    /**
     * @return Retorno\Cnab400\Detalhe
     */
    public function getDetalhe($i);

    /**
     * @return Retorno\Cnab400\Header
     */
    public function getHeader();

    /**
     * @return Retorno\Cnab400\Trailer
     */
    public function getTrailer();

    /**
     * @return string
     */
    public function processar();

    /**
     * @return array
     */
    public function toArray();
}
