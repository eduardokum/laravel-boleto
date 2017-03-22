<?php
namespace Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab400;

use Carbon\Carbon;
use Eduardokum\LaravelBoleto\Contracts\Cnab\Retorno\Cnab400\Detalhe as DetalheContract;
use Eduardokum\LaravelBoleto\MagicTrait;

class Detalhe implements DetalheContract
{
    use MagicTrait;
    /**
     * @var string
     */
    protected $nossoNumero;
    /**
     * @var string
     */
    protected $numeroDocumento;
    /**
     * @var string
     */
    protected $numeroControle;
    /**
     * @var string
     */
    protected $ocorrencia;
    /**
     * @var string
     */
    protected $ocorrenciaTipo;
    /**
     * @var string
     */
    protected $ocorrenciaDescricao;
    /**
     * @var Carbon
     */
    protected $dataOcorrencia;
    /**
     * @var Carbon
     */
    protected $dataVencimento;
    /**
     * @var Carbon
     */
    protected $dataCredito;
    /**
     * @var string
     */
    protected $valor;
    /**
     * @var string
     */
    protected $valorTarifa;
    /**
     * @var string
     */
    protected $valorIOF;
    /**
     * @var string
     */
    protected $valorAbatimento;
    /**
     * @var string
     */
    protected $valorDesconto;
    /**
     * @var string
     */
    protected $valorRecebido;
    /**
     * @var string
     */
    protected $valorMora;
    /**
     * @var string
     */
    protected $valorMulta;
    /**
     * @var string
     */
    protected $error;

    /**
     * @return string
     */
    public function getNossoNumero()
    {
        return $this->nossoNumero;
    }

    /**
     * @param string $nossoNumero
     *
     * @return Detalhe
     */
    public function setNossoNumero($nossoNumero)
    {
        $this->nossoNumero = $nossoNumero;

        return $this;
    }

    /**
     * @return string
     */
    public function getNumeroDocumento()
    {
        return $this->numeroDocumento;
    }

    /**
     * @param string $numeroDocumento
     *
     * @return Detalhe
     */
    public function setNumeroDocumento($numeroDocumento)
    {
        $this->numeroDocumento = ltrim(trim($numeroDocumento, ' '), '0');

        return $this;
    }

    /**
     * @return string
     */
    public function getNumeroControle()
    {
        return $this->numeroControle;
    }

    /**
     * @param string $numeroControle
     *
     * @return Detalhe
     */
    public function setNumeroControle($numeroControle)
    {
        $this->numeroControle = $numeroControle;

        return $this;
    }

    /**
     * @return boolean
     */
    public function hasOcorrencia()
    {
        $ocorrencias = func_get_args();

        if (count($ocorrencias) == 0 && !empty($this->getOcorrencia())) {
            return true;
        }

        if (count($ocorrencias) == 1 && is_array(func_get_arg(0))) {
            $ocorrencias = func_get_arg(0);
        }

        if (in_array($this->getOcorrencia(), $ocorrencias)) {
            return true;
        }

        return false;
    }

    /**
     * @return string
     */
    public function getOcorrencia()
    {
        return $this->ocorrencia;
    }

    /**
     * @param string $ocorrencia
     *
     * @return Detalhe
     */
    public function setOcorrencia($ocorrencia)
    {
        $this->ocorrencia = sprintf('%02s', $ocorrencia);

        return $this;
    }

    /**
     * @return string
     */
    public function getOcorrenciaDescricao()
    {
        return $this->ocorrenciaDescricao;
    }

    /**
     * @param string $ocorrenciaDescricao
     *
     * @return Detalhe
     */
    public function setOcorrenciaDescricao($ocorrenciaDescricao)
    {
        $this->ocorrenciaDescricao = $ocorrenciaDescricao;

        return $this;
    }

    /**
     * @return string
     */
    public function getOcorrenciaTipo()
    {
        return $this->ocorrenciaTipo;
    }

    /**
     * @param string $ocorrenciaTipo
     *
     * @return Detalhe
     */
    public function setOcorrenciaTipo($ocorrenciaTipo)
    {
        $this->ocorrenciaTipo = $ocorrenciaTipo;

        return $this;
    }

    /**
     * @param string $format
     *
     * @return mixed
     */
    public function getDataOcorrencia($format = 'd/m/Y')
    {
        return $this->dataOcorrencia instanceof Carbon
        ? $format === false ? $this->dataOcorrencia : $this->dataOcorrencia->format($format)
        : null;
    }

    /**
     * @param string $dataOcorrencia
     *
     * @return Detalhe
     */
    public function setDataOcorrencia($dataOcorrencia, $format = 'dmy')
    {
        $this->dataOcorrencia = trim($dataOcorrencia, '0 ') ? Carbon::createFromFormat($format, $dataOcorrencia) : null;

        return $this;
    }

    /**
     * @param string $format
     *
     * @return mixed
     */
    public function getDataVencimento($format = 'd/m/Y')
    {
        return $this->dataVencimento instanceof Carbon
        ? $format === false ? $this->dataVencimento : $this->dataVencimento->format($format)
        : null;
    }

    /**
     * @param string $dataVencimento
     *
     * @return Detalhe
     */
    public function setDataVencimento($dataVencimento, $format = 'dmy')
    {
        $this->dataVencimento = trim($dataVencimento, '0 ') ? Carbon::createFromFormat($format, $dataVencimento) : null;

        return $this;
    }

    /**
     * @param string $format
     *
     * @return mixed
     */
    public function getDataCredito($format = 'd/m/Y')
    {
        return $this->dataCredito instanceof Carbon
        ? $format === false ? $this->dataCredito : $this->dataCredito->format($format)
        : null;
    }

    /**
     * @param string $dataCredito
     *
     * @return Detalhe
     */
    public function setDataCredito($dataCredito, $format = 'dmy')
    {
        $this->dataCredito = trim($dataCredito, '0 ') ? Carbon::createFromFormat($format, $dataCredito) : null;

        return $this;
    }

    /**
     * @return string
     */
    public function getValor()
    {
        return $this->valor;
    }

    /**
     * @param string $valor
     *
     * @return Detalhe
     */
    public function setValor($valor)
    {
        $this->valor = $valor;

        return $this;
    }

    /**
     * @return string
     */
    public function getValorTarifa()
    {
        return $this->valorTarifa;
    }

    /**
     * @param string $valorTarifa
     *
     * @return Detalhe
     */
    public function setValorTarifa($valorTarifa)
    {
        $this->valorTarifa = $valorTarifa;

        return $this;
    }

    /**
     * @return string
     */
    public function getValorIOF()
    {
        return $this->valorIOF;
    }

    /**
     * @param string $valorIOF
     *
     * @return Detalhe
     */
    public function setValorIOF($valorIOF)
    {
        $this->valorIOF = $valorIOF;

        return $this;
    }

    /**
     * @return string
     */
    public function getValorAbatimento()
    {
        return $this->valorAbatimento;
    }

    /**
     * @param string $valorAbatimento
     *
     * @return Detalhe
     */
    public function setValorAbatimento($valorAbatimento)
    {
        $this->valorAbatimento = $valorAbatimento;

        return $this;
    }

    /**
     * @return string
     */
    public function getValorDesconto()
    {
        return $this->valorDesconto;
    }

    /**
     * @param string $valorDesconto
     *
     * @return Detalhe
     */
    public function setValorDesconto($valorDesconto)
    {
        $this->valorDesconto = $valorDesconto;

        return $this;
    }

    /**
     * @return float
     */
    public function getValorRecebido()
    {
        return $this->valorRecebido;
    }

    /**
     * @param string $valorRecebido
     *
     * @return Detalhe
     */
    public function setValorRecebido($valorRecebido)
    {
        $this->valorRecebido = $valorRecebido;

        return $this;
    }

    /**
     * @return string
     */
    public function getValorMora()
    {
        return $this->valorMora;
    }

    /**
     * @param string $valorMora
     *
     * @return Detalhe
     */
    public function setValorMora($valorMora)
    {
        $this->valorMora = $valorMora;

        return $this;
    }

    /**
     * @return string
     */
    public function getValorMulta()
    {
        return $this->valorMulta;
    }

    /**
     * @param string $valorMulta
     *
     * @return Detalhe
     */
    public function setValorMulta($valorMulta)
    {
        $this->valorMulta = $valorMulta;

        return $this;
    }

    /**
     * Retorna se tem erro.
     *
     * @return bool
     */
    public function hasError()
    {
        return $this->getOcorrencia() == self::OCORRENCIA_ERRO;
    }

    /**
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @param string $error
     *
     * @return Detalhe
     */
    public function setError($error)
    {
        $this->ocorrenciaTipo = self::OCORRENCIA_ERRO;
        $this->error = $error;

        return $this;
    }
}
