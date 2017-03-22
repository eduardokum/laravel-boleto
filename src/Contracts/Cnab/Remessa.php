<?php
namespace Eduardokum\LaravelBoleto\Contracts\Cnab;

interface Remessa extends Cnab
{
    public function gerar();
}
