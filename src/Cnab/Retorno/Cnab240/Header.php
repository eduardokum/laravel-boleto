<?php

namespace Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab240;

use Carbon\Carbon;
use Eduardokum\LaravelBoleto\Contracts\Cnab\Retorno\Cnab240\Header as HeaderContract;

class Header implements HeaderContract
{
    /**
     * @var integer
     */
    protected $codBanco;

    /**
     * @var string
     */
    protected $contaCorrente;

    /**
     * @var integer
     */
    protected $contaCorrenteDigito;

    /**
     * @var string
     */
    protected $nomeBanco;

    /**
     * @var integer
     */
    protected $codigoRemessaRetorno;

    /**
     * @var string
     */
    protected $operacao;

    /**
     * @var string
     */
    protected $servicoCodigo;

    /**
     * @var string
     */
    protected $servico;

    /**
     * @var string
     */
    protected $loteServico;

    /**
     * @var string
     */
    protected $tipoRegistro;

    /**
     * @var string
     */
    protected $tipoInscricao;

    /**
     * @var string
     */
    protected $agencia;
    /**
     * @var string
     */
    protected $agenciaDigito;

    /**
     * @var string
     */
    protected $nomeEmpresa;

    /**
     * @var string
     */
    protected $numeroSequencialArquivo;

    /**
     * @var string
     */
    protected $versaoLayoutArquivo;

    /**
     * @var string
     */
    protected $numeroInscricao;

    /**
     * @var string
     */
    protected $conta;

    /**
     * @var string
     */
    protected $contaDigito;

    /**
     * @var string
     */
    protected $codigoCedente;

    /**
     * @var string
     */
    protected $codigoCliente;

    /**
     * @return string
     */
    public function getOperacao()
    {
        return $this->operacao;
    }

    /**
     * @param string $operacao
     *
     * @return Header
     */
    public function setOperacao($operacao)
    {
        $this->operacao = $operacao;

        return $this;
    }

    /**
     * @return string
     */
    public function getServicoCodigo()
    {
        return $this->servicoCodigo;
    }

    /**
     * @param string $servicoCodigo
     *
     * @return Header
     */
    public function setServicoCodigo($servicoCodigo)
    {
        $this->servicoCodigo = $servicoCodigo;

        return $this;
    }

    /**
     * @return string
     */
    public function getServico()
    {
        return $this->servico;
    }

    /**
     * @param string $servico
     *
     * @return Header
     */
    public function setServico($servico)
    {
        $this->servico = $servico;

        return $this;
    }

    /**
     * @return string
     */
    public function getLoteServico()
    {
        return $this->loteServico;
    }

    /**
     * @param string $loteServico
     *
     * @return Header
     */
    public function setLoteServico($loteServico)
    {
        $this->loteServico = $loteServico;

        return $this;
    }

    /**
     * @return string
     */
    public function getTipoRegistro()
    {
        return $this->tipoRegistro;
    }

    /**
     * @param string $tipoRegistro
     *
     * @return Header
     */
    public function setTipoRegistro($tipoRegistro)
    {
        $this->tipoRegistro = $tipoRegistro;

        return $this;
    }

    /**
     * @return string
     */
    public function getTipoInscricao()
    {
        return $this->tipoInscricao;
    }

    /**
     * @param string $tipoInscricao
     */
    public function setTipoInscricao($tipoInscricao)
    {
        $this->tipoInscricao = $tipoInscricao;

        return $this;
    }

    /**
     * @return string
     */
    public function getAgencia()
    {
        return $this->agencia;
    }

    /**
     * @param string $agencia
     *
     * @return Header
     */
    public function setAgencia($agencia)
    {
        $this->agencia = ltrim(trim($agencia, ' '), '0');

        return $this;
    }

    /**
     * @return string
     */
    public function getAgenciaDigito()
    {
        return $this->agenciaDigito;
    }

    /**
     * @param string $agenciaDigito
     *
     * @return Header
     */
    public function setAgenciaDigito($agenciaDigito)
    {
        $this->agenciaDigito = $agenciaDigito;

        return $this;
    }

    /**
     * @return string
     */
    public function getNomeEmpresa()
    {
        return $this->nomeEmpresa;
    }

    /**
     * @param string $nomeEmpresa
     *
     * @return Header
     */
    public function setNomeEmpresa($nomeEmpresa)
    {
        $this->nomeEmpresa = $nomeEmpresa;

        return $this;
    }

    /**
     * @return string
     */
    public function getHoraGeracao()
    {
        return $this->horaGeracao;
    }

    /**
     * @param string $horaGeracao
     *
     * @return Header
     */
    public function setHoraGeracao($horaGeracao)
    {
        $this->horaGeracao = $horaGeracao;

        return $this;
    }

    /**
     * @return string
     */
    public function getNumeroSequencialArquivo()
    {
        return $this->numeroSequencialArquivo;
    }

    /**
     * @param string $horaGeracao
     *
     * @return Header
     */
    public function setNumeroSequencialArquivo($numeroSequencialArquivo)
    {
        $this->numeroSequencialArquivo = $numeroSequencialArquivo;

        return $this;
    }

    /**
     * @return string
     */
    public function getVersaoLayoutArquivo()
    {
        return $this->versaoLayoutArquivo;
    }

    /**
     * @param string $versaoLayoutArquivo
     *
     * @return Header
     */
    public function setVersaoLayoutArquivo($versaoLayoutArquivo)
    {
        $this->versaoLayoutArquivo = $versaoLayoutArquivo;

        return $this;
    }

    /**
     * @return string
     */
    public function getNumeroInscricao()
    {
        return $this->numeroInscricao;
    }

    /**
     * @param string $numeroInscricao
     *
     * @return Header
     */
    public function setNumeroInscricao($numeroInscricao)
    {
        $this->numeroInscricao = $numeroInscricao;

        return $this;
    }

    /**
     * @return string
     */
    public function getConta()
    {
        return $this->conta;
    }

    /**
     * @param string $conta
     *
     * @return Header
     */
    public function setConta($conta)
    {
        $this->conta = ltrim(trim($conta, ' '), '0');

        return $this;
    }

    /**
     * @return string
     */
    public function getContaDigito()
    {
        return $this->contaDigito;
    }

    /**
     * @param string $contaDigito
     *
     * @return Header
     */
    public function setContaDigito($contaDigito)
    {
        $this->contaDigito = $contaDigito;

        return $this;
    }

    /**
     * @return string
     */
    public function getCodigoCedente()
    {
        return $this->codigoCedente;
    }

    /**
     * @param string $codigoCedente
     *
     * @return Header
     */
    public function setCodigoCedente($codigoCedente)
    {
        $this->codigoCedente = $codigoCedente;

        return $this;
    }

    /**
     * @param string $format
     *
     * @return string
     */
    public function getData($format = 'd/m/Y')
    {
        return $this->data instanceof Carbon
            ? $format === false ? $this->data : $this->data->format($format)
            : null;
    }

    /**
     * @param string $data
     *
     * @return Header
     */
    public function setData($data)
    {
        $this->data = trim($data, '0 ') ? Carbon::createFromFormat('dmy', $data) : null;

        return $this;
    }

    /**
     * @return string
     */
    public function getConvenio()
    {
        return $this->convenio;
    }

    /**
     * @param string $convenio
     *
     * @return Header
     */
    public function setConvenio($convenio)
    {
        $this->convenio = $convenio;

        return $this;
    }

    /**
     * @return string
     */
    public function getCodigoCliente()
    {
        return $this->codigoCliente;
    }

    /**
     * @param string $codigoCliente
     */
    public function setCodigoCliente($codigoCliente)
    {
        $this->codigoCliente = $codigoCliente;
    }

    /**
     * @return int
     */
    public function getCodBanco()
    {
        return $this->codBanco;
    }

    /**
     * @param int $codBanco
     */
    public function setCodBanco($codBanco)
    {
        $this->codBanco = $codBanco;

        return $this;
    }

    /**
     * @return int
     */
    public function getCodigoRemessaRetorno()
    {
        return $this->codigoRemessaRetorno;
    }

    /**
     * @param int $codigoRemessaRetorno
     */
    public function setCodigoRemessaRetorno($codigoRemessaRetorno)
    {
        $this->codigoRemessaRetorno = $codigoRemessaRetorno;

        return $this;
    }

    /**
     * @return string
     */
    public function getContaCorrente()
    {
        return $this->contaCorrente;
    }

    /**
     * @param string $contaCorrente
     */
    public function setContaCorrente($contaCorrente)
    {
        $this->contaCorrente = $contaCorrente;

        return $this;
    }

    /**
     * @return int
     */
    public function getContaCorrenteDigito()
    {
        return $this->contaCorrenteDigito;
    }

    /**
     * @param int $contaCorrenteDigito
     */
    public function setContaCorrenteDigito($contaCorrenteDigito)
    {
        $this->contaCorrenteDigito = $contaCorrenteDigito;

        return $this;
    }

    /**
     * @return string
     */
    public function getNomeBanco()
    {
        return $this->nomeBanco;
    }

    /**
     * @param string $nomeBanco
     */
    public function setNomeBanco($nomeBanco)
    {
        $this->nomeBanco = $nomeBanco;

        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $vars = array_keys(get_class_vars(self::class));
        $aRet = [];
        foreach ($vars as $var) {
            $methodName = 'get' . ucfirst($var);
            $aRet[$var] = method_exists($this, $methodName)
                ? $this->$methodName()
                : $this->$var;
        }
        return $aRet;
    }

    /**
     * Fast set method.
     *
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        if (property_exists($this, $name)) {
            $this->$name = $value;
        }
    }

    /**
     * Fast get method.
     *
     * @param $name
     */
    public function __get($name)
    {
        if (property_exists($this, $name)) {
            $method = 'get' . ucwords($name);
            return $this->{$method}();
        }
    }

    /**
     * Determine if an attribute exists on the header.
     *
     * @param  string $key
     * @return bool
     */
    public function __isset($key)
    {
        return isset($this->$key);
    }
}