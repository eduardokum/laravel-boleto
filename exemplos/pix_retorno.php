<?php

require 'autoload.php';
$retorno = Eduardokum\LaravelBoleto\Cnab\Retorno\Factory::make(__DIR__ . DIRECTORY_SEPARATOR . 'arquivos' . DIRECTORY_SEPARATOR . 'ret.TXT');
$retorno->processar();

echo $retorno->getBancoNome();
dd([
    'ID'                              => $retorno->getDetalhe(1)->getId(),
    'LOCATION'                        => $retorno->getDetalhe(1)->getLocation(),
    'COPIA_E_COLA_VEIO_NO_RETORNO'    => $retorno->getDetalhe(1)->getPixQrCode(),
    'COPIA_E_COLA_GERADO_PELA_CLASSE' => $retorno->getDetalhe(1)->gerarPixCopiaECola('NOMEEMPRESA', 'SÃO PAULO', true),
]);
