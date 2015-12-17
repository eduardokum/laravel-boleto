<?php
namespace Eduardokum\LaravelBoleto\Cnab\Retorno\Banco;

use Eduardokum\LaravelBoleto\Cnab\Contracts\Retorno;
use Eduardokum\LaravelBoleto\Cnab\Retorno\AbstractCnab;

class Caixa extends AbstractCnab implements Retorno
{

    public $codigoTransmissao;

    public function __construct($file)
    {
        parent::__construct($file);

        $this->banco = self::COD_BANCO_CEF;
        $this->bancoDesc = $this->bancos[self::COD_BANCO_CEF];
        $this->codigoTransmissao = (int)substr($this->file[0], 26, 16);
    }

}