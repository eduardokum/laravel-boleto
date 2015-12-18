<?php
namespace Eduardokum\LaravelBoleto\Cnab\Contracts\Retorno;

interface Detalhe
{
    public function getTipoOcorrencia();
    public function getErro();
}