<?php

namespace Eduardokum\LaravelBoleto\Webhook;

use Exception;
use Carbon\Carbon;
use Eduardokum\LaravelBoleto\Util;
use Eduardokum\LaravelBoleto\MagicTrait;

class Boleto
{
    use MagicTrait;

    const OCORRENCIA_LIQUIDADA = 1;
    const OCORRENCIA_BAIXADA = 2;
    const OCORRENCIA_ENTRADA = 3;
    const OCORRENCIA_ALTERACAO = 4;
    const OCORRENCIA_PROTESTADA = 5;
    const OCORRENCIA_OUTROS = 6;
    const OCORRENCIA_ERRO = 9;
    const OCORRENCIA_ORIGEM_BOLETO = 'boleto';
    const OCORRENCIA_ORIGEM_PIX = 'pix';

    /**
     * @var
     */
    public $numero;

    /**
     * @var
     */
    public $numeroDocumento;

    /**
     * @var
     */
    public $nossoNumero;

    /**
     * @var Carbon
     */
    public $dataOcorrencia;

    /**
     * @var
     */
    public $valor;

    /**
     * @var
     */
    public $valorRecebido;

    /**
     * @var
     */
    public $ocorrenciaTipo;

    /**
     * @var
     */
    public $ocorrenciaOrigem;

    /**
     * @var
     */
    public $txid;

    /**
     * @var
     */
    public $pix;

    /**
     * @var
     */
    public $codigoBarras;

    /**
     * @var
     */
    public $linhaDigitavel;

    /**
     * @var
     */
    public $linhaDigitavelFormatada;

    /**
     * @var Carbon
     */
    public $dataVencimento;

    /**
     * @var
     */
    public $valorTarifa;

    /**
     * @var
     */
    public $motivo;

    /**
     * @return mixed
     */
    public function getNumero()
    {
        return $this->numero;
    }

    /**
     * @param mixed $numero
     * @return Boleto
     */
    public function setNumero($numero)
    {
        $this->numero = $numero;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getNumeroDocumento()
    {
        return $this->numeroDocumento;
    }

    /**
     * @param mixed $numeroDocumento
     * @return Boleto
     */
    public function setNumeroDocumento($numeroDocumento)
    {
        $this->numeroDocumento = $numeroDocumento;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getNossoNumero()
    {
        return $this->nossoNumero;
    }

    /**
     * @param mixed $nossoNumero
     * @return Boleto
     */
    public function setNossoNumero($nossoNumero)
    {
        $this->nossoNumero = $nossoNumero;

        return $this;
    }

    /**
     * @return Carbon
     */
    public function getDataOcorrencia()
    {
        return $this->dataOcorrencia;
    }

    /**
     * @return mixed
     */
    public function getOcorrenciaOrigem()
    {
        return $this->ocorrenciaOrigem;
    }

    /**
     * @param mixed $ocorrenciaOrigem
     * @return Boleto
     */
    public function setOcorrenciaOrigem($ocorrenciaOrigem)
    {
        $this->ocorrenciaOrigem = $ocorrenciaOrigem;

        return $this;
    }

    /**
     * @param Carbon $dataOcorrencia
     * @return Boleto
     */
    public function setDataOcorrencia(Carbon $dataOcorrencia)
    {
        $this->dataOcorrencia = $dataOcorrencia;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getValor()
    {
        return $this->valor;
    }

    /**
     * @param mixed $valor
     * @return Boleto
     */
    public function setValor($valor)
    {
        $this->valor = $valor;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getValorRecebido()
    {
        return $this->valorRecebido;
    }

    /**
     * @param mixed $valorRecebido
     * @return Boleto
     */
    public function setValorRecebido($valorRecebido)
    {
        $this->valorRecebido = $valorRecebido;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getOcorrenciaTipo()
    {
        return $this->ocorrenciaTipo;
    }

    /**
     * @param mixed $ocorrenciaTipo
     * @return Boleto
     */
    public function setOcorrenciaTipo($ocorrenciaTipo)
    {
        $this->ocorrenciaTipo = $ocorrenciaTipo;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTxid()
    {
        return $this->txid;
    }

    /**
     * @param mixed $txid
     * @return Boleto
     */
    public function setTxid($txid)
    {
        $this->txid = $txid;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPix()
    {
        return $this->pix;
    }

    /**
     * @param mixed $pix
     * @return Boleto
     */
    public function setPix($pix)
    {
        $this->pix = $pix;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCodigoBarras()
    {
        return $this->codigoBarras;
    }

    /**
     * @param mixed $codigoBarras
     * @return Boleto
     */
    public function setCodigoBarras($codigoBarras)
    {
        $this->codigoBarras = Util::onlyNumbers($codigoBarras);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLinhaDigitavelFormatada()
    {
        return $this->linhaDigitavelFormatada;
    }

    /**
     * @return mixed
     */
    public function getLinhaDigitavel()
    {
        return $this->linhaDigitavel;
    }

    /**
     * @param mixed $linhaDigitavel
     * @return Boleto
     */
    public function setLinhaDigitavel($linhaDigitavel)
    {
        $this->linhaDigitavel = Util::onlyNumbers($linhaDigitavel);
        try {
            $this->linhaDigitavelFormatada = Util::formatLinhaDigitavel($this->getLinhaDigitavel());
        } catch (Exception $e) {
        }

        return $this;
    }

    /**
     * @return Carbon
     */
    public function getDataVencimento()
    {
        return $this->dataVencimento;
    }

    /**
     * @param Carbon $dataVencimento
     * @return Boleto
     */
    public function setDataVencimento(Carbon $dataVencimento)
    {
        $this->dataVencimento = $dataVencimento;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getValorTarifa()
    {
        return $this->valorTarifa;
    }

    /**
     * @param mixed $valorTarifa
     * @return Boleto
     */
    public function setValorTarifa($valorTarifa)
    {
        $this->valorTarifa = $valorTarifa;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMotivo()
    {
        return $this->motivo;
    }

    /**
     * @param mixed $motivo
     * @return Boleto
     */
    public function setMotivo($motivo)
    {
        $this->motivo = $motivo;

        return $this;
    }
}
