<?php

namespace Eduardokum\LaravelBoleto\Pagamento\Banco;

use Eduardokum\LaravelBoleto\Pagamento\AbstractPagamento;
use Eduardokum\LaravelBoleto\Contracts\Pagamento\Pagamento;

class Banco extends AbstractPagamento implements Pagamento
{
    public function __construct(array $params = [])
    {
        parent::__construct($params);
    }
}
