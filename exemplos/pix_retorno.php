<?php

require 'autoload.php';
$retorno = \Eduardokum\LaravelBoleto\Cnab\Retorno\Factory::make(__DIR__ . DIRECTORY_SEPARATOR . 'arquivos' . DIRECTORY_SEPARATOR . 'santander_pix.ret');
$retorno->processar();

echo $retorno->getBancoNome();
dd([
    'ID'                              => $retorno->getDetalhe(1)->getId(),
    'CHAVE'                           => $retorno->getDetalhe(1)->getPixChave(),
    'CHAVE_TIPO'                      => $retorno->getDetalhe(1)->getPixChaveTipo(),
    'COPIA_E_COLA_VEIO_NO_RETORNO'    => $retorno->getDetalhe(1)->getPixQrCode(),
    'COPIA_E_COLA_GERADO_PELA_CLASSE' => $retorno->getDetalhe(1)->gerarPixCopiaECola('NOMEEMPRESA', 'SÃO PAULO', true),
]);
