<?php
namespace Eduardokum\LaravelBoleto\Contracts\Api;

use Carbon\Carbon;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto;
use Eduardokum\LaravelBoleto\Contracts\Pessoa as PessoaContract;

interface Api
{
    public function createBoleto(Boleto $boleto);
    public function retrieveNossoNumero($nossoNumero);
    public function retrieveID($id);
    public function cancelNossoNumero($nossoNumero, $motivo);
    public function cancelID($id, $motivo);
    public function retrieveList($inputedParams = []);
    public function getPdfNossoNumero($nossoNumero);
    public function getPdfID($id);
    public function createWebhook($url, $type = 'all');
    public function retrieve(Boleto $boleto);
    public function cancel(Boleto $boleto, $motivo);
    public function getPdf(Boleto $boleto);
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
