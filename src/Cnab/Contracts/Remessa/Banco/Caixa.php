<?php
namespace Eduardokum\LaravelBoleto\Cnab\Contracts\Remessa\Banco;

use Eduardokum\LaravelBoleto\Cnab\Contracts\Remessa;

interface Caixa extends Remessa
{
    public function gerar();
}