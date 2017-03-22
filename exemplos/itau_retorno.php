<?php
require 'autoload.php';
$retorno = \Eduardokum\LaravelBoleto\Cnab\Retorno\Factory::make(__DIR__ . DIRECTORY_SEPARATOR . 'arquivos' . DIRECTORY_SEPARATOR . 'itau.ret');
$retorno->processar();

echo $retorno->getBancoNome();
dd($retorno->getDetalhes());
