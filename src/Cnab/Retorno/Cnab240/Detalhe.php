<?php

namespace Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab240;

use Carbon\Carbon;
use Eduardokum\LaravelBoleto\Util;
use Eduardokum\LaravelBoleto\Pessoa;
use Eduardokum\LaravelBoleto\MagicTrait;
use Eduardokum\LaravelBoleto\Exception\ValidationException;
use Eduardokum\LaravelBoleto\Contracts\Pessoa as PessoaContract;
use Eduardokum\LaravelBoleto\Contracts\Cnab\Retorno\Cnab240\Detalhe as DetalheContract;

class Detalhe implements DetalheContract
{
    use MagicTrait;

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
     * @var int
     */
    protected $numeroControle;

    /**
     * @var string
     */
    protected $numeroDocumento;

    /**
     * @var string
     */
    protected $nossoNumero;

    /**
     * @var string
     */
    protected $carteira;

    /**
     * @var Carbon
     */
    protected $dataVencimento;

    /**
     * @var Carbon
     */
    protected $dataOcorrencia;

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
    protected $valorRecebido;

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
    protected $valorOutrosCreditos;

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
    protected $valorMora;

    /**
     * @var string
     */
    protected $valorMulta;

    /**
     * @var PessoaContract
     */
    protected $pagador;

    /**
     * @var array
     */
    protected $cheques = [];

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
    public function getOcorrencia()
    {
        return $this->ocorrencia;
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
     * @param string $ocorrencia
     *
     * @return Detalhe
     */
    public function setOcorrencia($ocorrencia)
    {
        $this->ocorrencia = $ocorrencia;

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
     * @return int
     */
    public function getNumeroControle()
    {
        return $this->numeroControle;
    }

    /**
     * @param int $numeroControle
     *
     * @return Detalhe
     */
    public function setNumeroControle($numeroControle)
    {
        $this->numeroControle = $numeroControle;

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
        $this->numeroDocumento = $numeroDocumento;

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
     * @param string $format
     *
     * @return Carbon|null|string
     */
    public function getDataVencimento($format = 'd/m/Y')
    {
        return $this->dataVencimento instanceof Carbon
            ? $format === false ? $this->dataVencimento : $this->dataVencimento->format($format)
            : null;
    }

    /**
     * @param        $dataVencimento
     *
     * @param string $format
     *
     * @return Detalhe
     */
    public function setDataVencimento($dataVencimento, $format = 'dmY')
    {
        $this->dataVencimento = trim($dataVencimento, '0 ') ? Carbon::createFromFormat($format, $dataVencimento) : null;

        return $this;
    }

    /**
     * @param string $format
     *
     * @return Carbon|null|string
     */
    public function getDataCredito($format = 'd/m/Y')
    {
        return $this->dataCredito instanceof Carbon
            ? $format === false ? $this->dataCredito : $this->dataCredito->format($format)
            : null;
    }

    /**
     * @param        $dataCredito
     *
     * @param string $format
     *
     * @return Detalhe
     */
    public function setDataCredito($dataCredito, $format = 'dmY')
    {
        $this->dataCredito = trim($dataCredito, '0 ') ? Carbon::createFromFormat($format, $dataCredito) : null;

        return $this;
    }

    /**
     * @param string $format
     *
     * @return Carbon|null|string
     */
    public function getDataOcorrencia($format = 'd/m/Y')
    {
        return $this->dataOcorrencia instanceof Carbon
            ? $format === false ? $this->dataOcorrencia : $this->dataOcorrencia->format($format)
            : null;
    }

    /**
     * @param        $dataOcorrencia
     *
     * @param string $format
     *
     * @return Detalhe
     */
    public function setDataOcorrencia($dataOcorrencia, $format = 'dmY')
    {
        $this->dataOcorrencia = trim($dataOcorrencia, '0 ') ? Carbon::createFromFormat($format, $dataOcorrencia) : null;

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
     * @return string
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
    public function getValorOutrosCreditos()
    {
        return $this->valorOutrosCreditos;
    }

    /**
     * @param string $valorOutrosCreditos
     *
     * @return Detalhe
     */
    public function setValorOutrosCreditos($valorOutrosCreditos)
    {
        $this->valorOutrosCreditos = $valorOutrosCreditos;

        return $this;
    }

    /**
     * @return PessoaContract
     */
    public function getPagador()
    {
        return $this->pagador;
    }

    /**
     * @param $pagador
     *
     * @return Detalhe
     * @throws ValidationException
     */
    public function setPagador($pagador)
    {
        Util::addPessoa($this->pagador, $pagador);

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
     * @return array
     */
    public function getCheques()
    {
        return $this->cheques;
    }

    /**
     * @param array $cheques
     *
     * @return Detalhe
     */
    public function setCheques(array $cheques)
    {
        $this->cheques = $cheques;

        return $this;
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
