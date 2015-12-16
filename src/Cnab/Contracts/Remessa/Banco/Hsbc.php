<?php
namespace Eduardokum\LaravelBoleto\Cnab\Contracts\Remessa\Banco;

use Eduardokum\LaravelBoleto\Cnab\Contracts\Remessa;

interface Hsbc extends Remessa
{
    public function gerar();
}