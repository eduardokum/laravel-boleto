<?php

namespace Eduardokum\LaravelBoleto\Contracts\Cnab;

use Illuminate\Support\Collection;

interface RetornoCnab240 extends Retorno
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
     * @return Retorno\Detalhe
     */
    public function getDetalhe($i);

    /**
     * @return Retorno\Cnab240\Header
     */
    public function getHeader();

    /**
     * @return Retorno\Cnab240\HeaderLote
     */
    public function getHeaderLote();

    /**
     * @return Retorno\Cnab240\TrailerLote
     */
    public function getTrailerLote();

    /**
     * @return Retorno\Cnab240\Trailer
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
