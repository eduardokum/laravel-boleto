<?php

namespace Eduardokum\LaravelBoleto\Pagamento\Banco;

use Eduardokum\LaravelBoleto\Pagamento\AbstractPagamento;
use Eduardokum\LaravelBoleto\Contracts\Pagamento\Pagamento;

class Banco extends AbstractPagamento implements Pagamento
{
    public function __construct(array $params = [])
    {
        parent::__construct($params);
        $this->setCamposObrigatorios('operacao');
    }

    protected $agencia = '0001';

    /**
     * Código do banco
     *
     * @var string
     */
    protected $codigoBanco = Pagamento::COD_BANCO_INTER;

    /**
     * @var string[]
     */
    protected $protectedFields = [];


    public function setAgencia($agencia)
    {
        $this->agencia = $agencia;

        return $this;
    }
}
