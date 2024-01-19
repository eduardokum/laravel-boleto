<?php

require 'autoload.php';
$retorno = Eduardokum\LaravelBoleto\Cnab\Retorno\Factory::make(__DIR__ . DIRECTORY_SEPARATOR . 'arquivos' . DIRECTORY_SEPARATOR . 'delbank.ret');
$retorno->processar();

echo $retorno->getBancoNome();
dd($retorno->getDetalhes());
