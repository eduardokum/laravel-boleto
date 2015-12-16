<?php
namespace Eduardokum\LaravelBoleto\Cnab\Contracts;

interface Cnab
{
    const HEADER = 'header';
    const DETALHE = 'detalhe';
    const TRAILER = 'trailer';

    const COD_BANCO_BB = '001';
    const COD_BANCO_SANTANDER = '033';
    const COD_BANCO_CEF = '104';
    const COD_BANCO_BRADESCO = '237';
    const COD_BANCO_ITAU = '341';
    const COD_BANCO_HSBC = '399';
}