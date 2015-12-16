<?php
namespace Eduardokum\LaravelBoleto\Cnab\Contracts\Remessa;

interface Detalhe
{

    public function getOcorrencia();

    public function getNumero($default = ' ');

    public function getNumeroDocumento();

    public function setNumeroControle(array $controle);

    public function getNumeroControle();

    public function getNumeroControleString();

    public function getDataVencimento($default = ' ');

    public function getDataDocumento();

    public function getDataLimiteDesconto($default = ' ');

    public function getDataMulta($default = ' ');

    public function getTipoCobranca($default = ' ');

    public function getEspecie($default = ' ');

    public function getAceite($default = ' ');

    public function getInstrucao1($default = ' ');

    public function getInstrucao2($default = ' ');

    public function getValorDesconto($default = ' ');

    public function getvalorIOF($default = ' ');

    public function getValorMora($default = ' ');

    public function getValorAbatimento($default = ' ');

    public function getValor($default = ' ');

    public function getTipoMoeda($default = ' ');

    public function getDiasProtesto($default = ' ');

    public function getTaxaMulta($default = ' ');

    public function getValorMulta($default = ' ');

    public function getXDiasMulta($default = ' ');

    public function getNaoReceberDias($default = ' ');

    public function getSacadoDocumento($default = ' ');

    public function getSacadoNome($default = ' ');

    public function getSacadoEndereco($default = ' ');

    public function getSacadoBairro($default = ' ');

    public function getSacadoCEP($default = ' ');

    public function getSacadoCidade($default = ' ');

    public function getSacadoEstado($default = ' ');

    public function getSacadorAvalista($default = ' ');

}