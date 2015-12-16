<?php
namespace Eduardokum\LaravelBoleto\Cnab\Contracts\Remessa\Banco;

use Eduardokum\LaravelBoleto\Cnab\Contracts\Remessa;

interface Bb extends Remessa
{
    public function gerar();
}