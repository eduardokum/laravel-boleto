<?php

namespace Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab240;

use Carbon\Carbon;
use Eduardokum\LaravelBoleto\MagicTrait;
use Eduardokum\LaravelBoleto\Contracts\Cnab\Retorno\Cnab240\Header as HeaderContract;

class Header implements HeaderContract
{
    use MagicTrait;

    /**
     * @var int
     */
    protected $codBanco;

    /**
     * @var string
     */
    protected $nomeBanco;

    /**
     * @var int
     */
    protected $codigoRemessaRetorno;

    /**
     * @var string
     */
    protected $loteServico;

    /**
     * @var string
     */
    protected $tipoRegistro;

    /**
     * @var Carbon
     */
    protected $data;

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
    protected $agenciaDv;

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
    protected $contaDv;

    /**
     * @var string
     */
    protected $codigoCedente;

    /**
     * @var string
     */
    protected $horaGeracao;

    /**
     * @var string
     */
    protected $convenio;

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
     *
     * @return Header
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
    public function getAgenciaDv()
    {
        return $this->agenciaDv;
    }

    /**
     * @param string $agenciaDv
     *
     * @return Header
     */
    public function setAgenciaDv($agenciaDv)
    {
        $this->agenciaDv = $agenciaDv;

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
     * @param string $numeroSequencialArquivo
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
    public function getContaDv()
    {
        return $this->contaDv;
    }

    /**
     * @param string $contaDv
     *
     * @return Header
     */
    public function setContaDv($contaDv)
    {
        $this->contaDv = $contaDv;

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
     * @param string $format
     *
     * @return Header
     */
    public function setData($data, $format = 'dmY')
    {
        $this->data = trim($data, '0 ') ? Carbon::createFromFormat($format, $data) : null;

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
     * @return int
     */
    public function getCodBanco()
    {
        return $this->codBanco;
    }

    /**
     * @param int $codBanco
     *
     * @return Header
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
     *
     * @return Header
     */
    public function setCodigoRemessaRetorno($codigoRemessaRetorno)
    {
        $this->codigoRemessaRetorno = $codigoRemessaRetorno;

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
     *
     * @return Header
     */
    public function setNomeBanco($nomeBanco)
    {
        $this->nomeBanco = $nomeBanco;

        return $this;
    }
}
