<?php
namespace Eduardokum\LaravelBoleto\Cnab\Retorno\Detalhe;

use Eduardokum\LaravelBoleto\Cnab\Contracts\Retorno\Detalhe as DetalheContract;
use Eduardokum\LaravelBoleto\Cnab\Retorno\AbstractDetalhe;

class Bb extends AbstractDetalhe implements DetalheContract
{
    const TIPO_COMPARTILHADA = 'compartilhada';
    const TIPO_VENDOR = 'vendor';
    const TIPO_NORMAL = 'normal';

    public $id;
    public $auxiliar = false;
}