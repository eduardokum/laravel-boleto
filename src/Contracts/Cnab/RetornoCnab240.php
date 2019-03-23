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
     * @return Retorno\Header
     */
    public function getHeader();

    /**
     * @return Retorno\HeaderLote
     */
    public function getHeaderLote();

    /**
     *  @return Retorno\TrailerLote
     */
    public function getTrailerLote();

    /**
     *  @return Retorno\Trailer
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
