<?php
namespace Eduardokum\LaravelBoleto\Cnab\Remessa;

use Carbon\Carbon;
use Eduardokum\LaravelBoleto\Util;

class AbstractDetalhe
{
    public $numero;
    public $ocorrencia = '01';
    public $numeroDocumento;
    private $numeroControle;
    public $dataVencimento;
    public $dataDocumento;
    public $tipoCobranca;
    public $especie;
    public $aceite;
    public $instrucao1;
    public $instrucao2;
    public $dataLimiteDesconto;
    public $valorDesconto;
    public $valorIOF;
    public $valorMora;
    public $valorAbatimento;
    public $valor;
    public $tipoMoeda;
    public $diasProtesto;
    public $dataMulta;
    public $taxaMulta;
    public $valorMulta;
    public $xDiasMulta;
    public $naoReceberDias;
    public $sacadoDocumento;
    public $sacadoNome;
    public $sacadoEndereco;
    public $sacadoBairro;
    public $sacadoCEP;
    public $sacadoCidade;
    public $sacadoEstado;
    public $sacadorAvalista;

    public function getNumero($default = ' ')
    {
        return $this->isEmpty($this->numero) ? $default : $this->numero;
    }

    public function getOcorrencia()
    {
        return $this->ocorrencia;
    }

    public function getNumeroDocumento()
    {
        return $this->isEmpty($this->numeroDocumento) ? $this->getNumero() : $this->numeroDocumento;
    }

    public function getNumeroControle()
    {
        return $this->isEmpty($this->numeroControle) ? $this->getNumero() : Util::controle2array($this->numeroControle);
    }

    public function getNumeroControleString()
    {
        return $this->isEmpty($this->numeroControle) ? $this->getNumero() : $this->numeroControle;
    }

    public function setNumeroControle(array $controle)
    {
        $this->numeroControle = Util::array2Controle($controle);
    }

    public function getDataVencimento($default = ' ')
    {
        return $this->isEmpty($this->dataVencimento)
            ? (is_string($default) ? Carbon::createFromFormat('Y-m-d', $default) : $default)
            : (is_string($this->dataVencimento) ? Carbon::createFromFormat('Y-m-d', $this->dataVencimento) : $this->dataVencimento);
    }

    public function getDataDocumento()
    {
        return $this->isEmpty($this->dataDocumento)
            ? Carbon::now()
            : (is_string($this->dataDocumento) ? Carbon::createFromFormat('Y-m-d', $this->dataDocumento) : $this->dataDocumento);
    }

    public function getDataLimiteDesconto($default = ' ')
    {
        return $this->isEmpty($this->dataLimiteDesconto)
            ? $this->getDataVencimento($default)
            : (is_string($this->dataLimiteDesconto) ? Carbon::createFromFormat('Y-m-d', $this->dataLimiteDesconto) : $this->dataLimiteDesconto);
    }

    public function getDataMulta($default = '0')
    {
        return $this->isEmpty($this->dataMulta)
            ? $default
            : (is_string($this->dataMulta) ? Carbon::createFromFormat('Y-m-d', $this->dataMulta) : $this->dataMulta);
    }

    public function getTipoCobranca($default = ' ')
    {
        return $this->isEmpty($this->tipoCobranca) ? $default : $this->tipoCobranca;
    }

    public function getEspecie($default = ' ')
    {
        return $this->isEmpty($this->especie) ? $default : $this->especie;
    }

    public function getAceite($default = ' ')
    {
        return $this->isEmpty($this->aceite) ? $default : $this->aceite;
    }

    public function getInstrucao1($default = ' ')
    {
        return $this->isEmpty($this->instrucao1) ? $default : $this->instrucao1;
    }

    public function getInstrucao2($default = ' ')
    {
        return $this->isEmpty($this->instrucao2) ? $default : $this->instrucao2;
    }

    public function getValorDesconto($default = ' ')
    {
        return Util::nFloat($this->isEmpty($this->valorDesconto) ? $default : $this->valorDesconto);
    }

    public function getvalorIOF($default = ' ')
    {
        return Util::nFloat($this->isEmpty($this->valorIOF) ? $default : $this->valorIOF);
    }

    public function getValorMora($default = ' ')
    {
        return Util::nFloat($this->isEmpty($this->valorMora) ? $default : $this->valorMora);
    }

    public function getValorAbatimento($default = ' ')
    {
        return Util::nFloat($this->isEmpty($this->valorAbatimento) ? $default : $this->valorAbatimento);
    }

    public function getValor($default = ' ')
    {
        return Util::nFloat($this->isEmpty($this->valor) ? $default : $this->valor);
    }

    public function getTipoMoeda($default = ' ')
    {
        return $this->isEmpty($this->tipoMoeda) ? $default : $this->tipoMoeda;
    }

    public function getDiasProtesto($default = ' ')
    {
        return $this->isEmpty($this->diasProtesto) ? $default : $this->diasProtesto;
    }

    public function getTaxaMulta($default = ' ')
    {
        return Util::nFloat($this->isEmpty($this->taxaMulta) ? $default : $this->taxaMulta);
    }

    public function getValorMulta($default = ' ')
    {
        return Util::nFloat($this->isEmpty($this->valorMulta) ? $default : $this->valorMulta);
    }

    public function getXDiasMulta($default = ' ')
    {
        return $this->isEmpty($this->xDiasMulta) ? $default : $this->xDiasMulta;
    }

    public function getNaoReceberDias($default = ' ')
    {
        return $this->isEmpty($this->naoReceberDias) ? $default : $this->naoReceberDias;
    }

    public function getSacadoTipoDocumento()
    {
        return Util::onlyNumbers($this->sacadoDocumento) > 11 ? '02' : '01';
    }

    public function getSacadoDocumento($default = ' ')
    {
        return Util::onlyNumbers($this->isEmpty($this->sacadoDocumento) ? $default : $this->sacadoDocumento);
    }

    public function getSacadoNome($default = ' ')
    {
        return $this->isEmpty($this->sacadoNome) ? $default : $this->sacadoNome;
    }

    public function getSacadoEndereco($default = ' ')
    {
        return $this->isEmpty($this->sacadoEndereco) ? $default : $this->sacadoEndereco;
    }

    public function getSacadoBairro($default = ' ')
    {
        return $this->isEmpty($this->sacadoBairro) ? $default : $this->sacadoBairro;
    }

    public function getSacadoCEP($default = ' ')
    {
        return $this->isEmpty($this->sacadoCEP) ? $default : $this->sacadoCEP;
    }

    public function getSacadoCidade($default = ' ')
    {
        return $this->isEmpty($this->sacadoCidade) ? $default : $this->sacadoCidade;
    }

    public function getSacadoEstado($default = ' ')
    {
        return $this->isEmpty($this->sacadoEstado) ? $default : $this->sacadoEstado;
    }

    public function getSacadorAvalista($default = ' ')
    {
        return $this->isEmpty($this->sacadorAvalista) ? $default : $this->sacadorAvalista;
    }

    private function isEmpty($value)
    {
        return $value == '' || $value == ' ' || $value == null || $value == false;
    }

}