<?php
namespace Eduardokum\LaravelBoleto\Cnab\Retorno\Banco;

use Eduardokum\LaravelBoleto\Cnab\Contracts\Retorno;
use Eduardokum\LaravelBoleto\Cnab\Retorno\AbstractCnab;

class Santander extends AbstractCnab implements Retorno
{

    public $agencia;
    public $conta;

    public function __construct($file)
    {
        parent::__construct($file);

        $this->banco = self::COD_BANCO_SANTANDER;
        $this->agencia = (int)substr($this->file[0], 26, 4);
        $this->conta = (int)substr($this->file[0], 30, 8);
    }

}