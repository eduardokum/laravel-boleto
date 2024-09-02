<?php

namespace Eduardokum\LaravelBoleto\Contracts;

interface NotaFiscal
{
    public function getChave();

    public function getData($format = 'dmy');

    public function getValor();

    public function getNumero();
}
