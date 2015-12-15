<?php
namespace Eduardokum\LaravelBoleto\Cnab\Contracts\Retorno;

interface Detalhe
{

    public function getNumero();

    public function getNumeroDocumento();

    public function getNumeroControle();

    public function getDataVencimento();

    public function getDataDocumento();

    public function getCarteira();

    public function getTipoCobranca();

    public function getEspecie();

    public function getAceite();

    public function getInstrucao1();

    public function getInstrucao2();

    public function getDataLimiteDesconto();

    public function getValorDesconto();

    public function getvalorIOF();

    public function getValorMora();

    public function getValorAbatimento();

    public function getValor();

    public function getTipoMoeda();

    public function getDiasProtesto();

    public function getDataMulta();

    public function getTaxaMulta();

    public function getValorMulta();

    public function getXDiasMulta();

    public function getNaoReceberDias();

    public function getSacadoTipoDocumento();

    public function getSacadoDocumento();

    public function getSacadoNome();

    public function getSacadoEndereco();

    public function getSacadoBairro();

    public function getSacadoCEP();

    public function getSacadoCidade();

    public function getSacadoEstado();

    public function getSacadorAvalista();

}