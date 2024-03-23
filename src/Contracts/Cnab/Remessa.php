<?php
namespace Xpendi\CnabBoleto\Contracts\Cnab;

interface Remessa extends Cnab
{
    public function gerar();
}
