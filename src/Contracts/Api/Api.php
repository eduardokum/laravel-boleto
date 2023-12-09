<?php

namespace Eduardokum\LaravelBoleto\Contracts\Api;

use Eduardokum\LaravelBoleto\Contracts\Boleto\BoletoAPI;

interface Api
{
    public function createBoleto(BoletoAPI $boleto);

    public function retrieveNossoNumero($nossoNumero);

    public function retrieveID($id);

    public function cancelNossoNumero($nossoNumero, $motivo);

    public function cancelID($id, $motivo);

    public function retrieveList($inputedParams = []);

    public function getPdfNossoNumero($nossoNumero);

    public function getPdfID($id);

    public function createWebhook($url, $type = 'all');

    public function retrieve(BoletoAPI $boleto);

    public function cancel(BoletoAPI $boleto, $motivo);

    public function getPdf(BoletoAPI $boleto);

    public function getBaseUrl();

    public function getConta();

    public function getCertificado();

    public function getCertificadoChave();

    public function getCertificadoSenha();

    public function getIdentificador();

    public function getSenha();

    public function getCnpj();

    public function getClientId();

    public function getClientSecret();

    public function getAccessToken();

    public function getBeneficiario();

    public function unsetDebug();

    public function isDebug();

    public function getLog();

    public function clearLog();
}
