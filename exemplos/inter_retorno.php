<?php
require 'autoload.php';
$retorno = \Xpendi\CnabBoleto\Cnab\Retorno\Factory::make(__DIR__ . DIRECTORY_SEPARATOR . 'arquivos' . DIRECTORY_SEPARATOR . 'inter.ret');
$retorno->processar();

echo $retorno->getBancoNome();
dd($retorno->getDetalhes());
