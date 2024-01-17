<?php

namespace Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab400;

use Carbon\Carbon;
use Eduardokum\LaravelBoleto\Util;
use Eduardokum\LaravelBoleto\Pessoa;
use Eduardokum\LaravelBoleto\MagicTrait;
use Eduardokum\LaravelBoleto\Exception\ValidationException;
use Eduardokum\LaravelBoleto\Contracts\Cnab\Retorno\Cnab400\Detalhe as DetalheContract;

class Detalhe implements DetalheContract
{
    use MagicTrait;

    /**
     * @var string
     */
    protected $carteira;

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
    protected $codigoLiquidacao;

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
    protected $valorOutrasDespesas;

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
     * @var
     */
    protected $id;

    /**
     * @var
     */
    protected $pixQrCode;

    /**
     * @var
     */
    protected $pixLocation;

    /**
     * @var string
     */
    protected $error;

    /**
     * @return string
     */
    public function getCarteira()
    {
        return $this->carteira;
    }

    /**
     * @param string $carteira
     *
     * @return Detalhe
     */
    public function setCarteira($carteira)
    {
        $this->carteira = $carteira;

        return $this;
    }

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
     * Getter for codigoLiquidacao
     *
     * @return string
     */
    public function getCodigoLiquidacao()
    {
        return $this->codigoLiquidacao;
    }

    /**
     * Setter for codigoLiquidacao
     *
     * @param string $codigoLiquidacao
     *
     * @return Detalhe
     */
    public function setCodigoLiquidacao($codigoLiquidacao)
    {
        $this->codigoLiquidacao = $codigoLiquidacao;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasOcorrencia()
    {
        $ocorrencias = func_get_args();

        if (count($ocorrencias) == 0 && ! empty($this->getOcorrencia())) {
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
     * @return string
     */
    public function getRejeicao()
    {
        return $this->rejeicao;
    }

    /**
     * @param string $rejeicao
     *
     * @return Detalhe
     */
    public function setRejeicao($rejeicao)
    {
        $this->rejeicao = $rejeicao;

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
    public function getValorOutrasDespesas()
    {
        return $this->valorOutrasDespesas;
    }

    /**
     * @param string $valorOutrasDespesas
     *
     * @return Detalhe
     */
    public function setValorOutrasDespesas($valorOutrasDespesas)
    {
        $this->valorOutrasDespesas = $valorOutrasDespesas;

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
        return $this->getOcorrenciaTipo() == self::OCORRENCIA_ERRO;
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

    /**
     * @param string $error
     *
     * @return Detalhe
     */
    public function appendError($error)
    {
        $this->error .= $error;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return Detalhe
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPixQrCode()
    {
        return $this->pixQrCode;
    }

    /**
     * @param mixed $pixQrCode
     * @return Detalhe
     */
    public function setPixQrCode($pixQrCode)
    {
        $this->pixQrCode = $pixQrCode;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPixLocation()
    {
        return $this->pixLocation;
    }

    /**
     * @param mixed $pixLocation
     * @return Detalhe
     */
    public function setPixLocation($pixLocation)
    {
        $this->pixLocation = $pixLocation;

        return $this;
    }

    /**
     * @param $nome
     * @param $cidade
     * @param bool $force
     * @return string|null
     * @throws ValidationException
     */
    public function gerarPixCopiaECola($nome, $cidade, $force = false)
    {
        if ($this->getPixQrCode() && ! $force) {
            return $this->getPixQrCode();
        }
        if ($this->getPixLocation() && $this->getValor() && $this->getID()) {
            $this->setPixQrCode(Util::gerarPixCopiaECola($this->getPixLocation(), $this->getValor(), $this->getID(), new Pessoa(['nome' => Util::normalizeChars($nome), 'cidade' => Util::normalizeChars($cidade)])));
        }

        return $this->getPixQrCode();
    }
}
