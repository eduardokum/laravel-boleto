<?php

namespace Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab240;

use Carbon\Carbon;
use Eduardokum\LaravelBoleto\Contracts\Cnab\Retorno\Cnab240\DetalheSegmentoT as DetalheSegmentoTContract;

class DetalheSegmentoT implements DetalheSegmentoTContract
{

    /**
     * @var int
     */
    protected $numeroDocumento;

    /**
     * @var string
     */
    protected $identificador;

    /**
     * @var string
     */
    private $codigoBancoCompensacao;

    /**
     * @var string
     */
    private $numeroLoteRetorno;

    /**
     * @var string
     */
    private $tipoRegistro;

    /**
     * @var string
     */
    private $numeroSequencialRegistroLote;

    /**
     * @var string
     */
    private $codigoSegmentoRegistroDetalhe;

    /**
     * @var string
     */
    private $agenciaCedente;

    /**
     * @var string
     */
    private $agenciaCedenteDigito;

    /**
     * @var string
     */
    private $contaCorrente;

    /**
     * @var string
     */
    private $contaDigito;

    /**
     * @var string
     */
    private $nossoNumero;

    /**
     * @var string
     */
    private $codigoCarteira;

    /**
     * @var string
     */
    private $seuNumero;

    /**
     * @var Carbon
     */
    private $dataVencimento;

    /**
     * @var string
     */
    private $valorTitulo;

    /**
     * @var string
     */
    private $numeroBancoCobradorRecebedor;

    /**
     * @var string
     */
    private $agenciaCobradoraRecebedora;

    /**
     * @var string
     */
    private $codigoMoeda;

    /**
     * @var string
     */
    private $tipoInscriçãoSacado;

    /**
     * @var string
     */
    private $numeroInscricaoSacado;

    /**
     * @var string
     */
    private $nomeSacado;

    /**
     * @var string
     */
    private $contaCobranca;

    /**
     * @var string
     */
    private $valorTarifa;

    /**
     * @var string
     */
    private $identificacaoRejeicao;

    /**
     * @var string
     */
    private $digitoAgenciaCedente;

    /**
     * @var string
     */
    private $valorPagoSacado;

    /**
     * @return string
     */
    public function getCodigoBancoCompensacao()
    {
        return $this->codigoBancoCompensacao;
    }

    /**
     * @param mixed $codigoBancoCompensacao
     *
     * @return $this
     */
    public function setCodigoBancoCompensacao($codigoBancoCompensacao)
    {
        $this->codigoBancoCompensacao = $codigoBancoCompensacao;

        return $this;
    }

    /**
     * @return string
     */
    public function getNumeroLoteRetorno()
    {
        return $this->numeroLoteRetorno;
    }

    /**
     * @param mixed $numeroLoteRetorno
     *
     * @return $this
     */
    public function setNumeroLoteRetorno($numeroLoteRetorno)
    {
        $this->numeroLoteRetorno = $numeroLoteRetorno;

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
     * @param mixed $tipoRegistro
     *
     * @return $this
     */
    public function setTipoRegistro($tipoRegistro)
    {
        $this->tipoRegistro = $tipoRegistro;

        return $this;
    }

    /**
     * @return string
     */
    public function getNumeroSequencialRegistroLote()
    {
        return $this->numeroSequencialRegistroLote;
    }

    /**
     * @param mixed $numeroSequencialRegistroLote
     *
     * @return $this
     */
    public function setNumeroSequencialRegistroLote($numeroSequencialRegistroLote)
    {
        $this->numeroSequencialRegistroLote = $numeroSequencialRegistroLote;

        return $this;
    }

    /**
     * @return string
     */
    public function getCodigoSegmentoRegistroDetalhe()
    {
        return $this->codigoSegmentoRegistroDetalhe;
    }

    /**
     * @param mixed $codigoSegmentoRegistroDetalhe
     *
     * @return $this
     */
    public function setCodigoSegmentoRegistroDetalhe($codigoSegmentoRegistroDetalhe)
    {
        $this->codigoSegmentoRegistroDetalhe = $codigoSegmentoRegistroDetalhe;

        return $this;
    }

    /**
     * @return string
     */
    public function getDigitoAgenciaCedente()
    {
        return $this->digitoAgenciaCedente;
    }

    /**
     * @param mixed $digitoAgenciaCedente
     *
     * @return $this
     */
    public function setDigitoAgenciaCedente($digitoAgenciaCedente)
    {
        $this->digitoAgenciaCedente = $digitoAgenciaCedente;

        return $this;
    }

    /**
     * @return string
     */
    public function getAgenciaCedente()
    {
        return $this->agenciaCedente;
    }

    /**
     * @param mixed $agenciaCedente
     *
     * @return $this
     */
    public function setAgenciaCedente($agenciaCedente)
    {
        $this->agenciaCedente = $agenciaCedente;

        return $this;
    }

    /**
     * @return string
     */
    public function getAgenciaCedenteDigito()
    {
        return $this->agenciaCedenteDigito;
    }

    /**
     * @param mixed $agenciaCedenteDigito
     *
     * @return $this
     */
    public function setAgenciaCedenteDigito($agenciaCedenteDigito)
    {
        $this->agenciaCedenteDigito = $agenciaCedenteDigito;

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
     * @param mixed $contaCorrente
     *
     * @return $this
     */
    public function setContaCorrente($contaCorrente)
    {
        $this->contaCorrente = $contaCorrente;

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
     * @param mixed $contaDigito
     *
     * @return $this
     */
    public function setContaDigito($contaDigito)
    {
        $this->contaDigito = $contaDigito;

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
     * @param mixed $nossoNumero
     *
     * @return $this
     */
    public function setNossoNumero($nossoNumero)
    {
        $this->nossoNumero = $nossoNumero;

        return $this;
    }

    /**
     * @return string
     */
    public function getCodigoCarteira()
    {
        return $this->codigoCarteira;
    }

    /**
     * @param mixed $codigoCarteira
     *
     * @return $this
     */
    public function setCodigoCarteira($codigoCarteira)
    {
        $this->codigoCarteira = $codigoCarteira;

        return $this;
    }

    /**
     * @return string
     */
    public function getSeuNumero()
    {
        return $this->seuNumero;
    }

    /**
     * @param mixed $seuNumero
     *
     * @return $this
     */
    public function setSeuNumero($seuNumero)
    {
        $this->seuNumero = $seuNumero;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDataVencimento($format = 'd/m/Y')
    {
        return $this->dataVencimento instanceof Carbon
            ? $format === false ? $this->dataVencimento : $this->dataVencimento->format($format)
            : null;
    }

    /**
     * @param mixed $dataVencimento
     *
     * @return $this
     */
    public function setDataVencimento($dataVencimento, $format = 'dmY')
    {
        $this->dataVencimento = trim($dataVencimento, '0 ') ? Carbon::createFromFormat($format, $dataVencimento) : null;

        return $this;
    }

    /**
     * @return string
     */
    public function getValorTitulo()
    {
        return $this->valorTitulo;
    }

    /**
     * @param mixed $valorTitulo
     *
     * @return $this
     */
    public function setValorTitulo($valorTitulo)
    {
        $this->valorTitulo = $valorTitulo;

        return $this;
    }

    /**
     * @return string
     */
    public function getNumeroBancoCobradorRecebedor()
    {
        return $this->numeroBancoCobradorRecebedor;
    }

    /**
     * @param mixed $numeroBancoCobradorRecebedor
     *
     * @return $this
     */
    public function setNumeroBancoCobradorRecebedor($numeroBancoCobradorRecebedor)
    {
        $this->numeroBancoCobradorRecebedor = $numeroBancoCobradorRecebedor;

        return $this;
    }

    /**
     * @return string
     */
    public function getAgenciaCobradoraRecebedora()
    {
        return $this->agenciaCobradoraRecebedora;
    }

    /**
     * @param mixed $agenciaCobradoraRecebedora
     *
     * @return $this
     */
    public function setAgenciaCobradoraRecebedora($agenciaCobradoraRecebedora)
    {
        $this->agenciaCobradoraRecebedora = $agenciaCobradoraRecebedora;

        return $this;
    }

    /**
     * @return string
     */
    public function getCodigoMoeda()
    {
        return $this->codigoMoeda;
    }

    /**
     * @param mixed $codigoMoeda
     *
     * @return $this
     */
    public function setCodigoMoeda($codigoMoeda)
    {
        $this->codigoMoeda = $codigoMoeda;

        return $this;
    }

    /**
     * @return string
     */
    public function getTipoInscriçãoSacado()
    {
        return $this->tipoInscriçãoSacado;
    }

    /**
     * @param mixed $tipoInscriçãoSacado
     *
     * @return $this
     */
    public function setTipoInscriçãoSacado($tipoInscriçãoSacado)
    {
        $this->tipoInscriçãoSacado = $tipoInscriçãoSacado;

        return $this;
    }

    /**
     * @return string
     */
    public function getNumeroInscricaoSacado()
    {
        return $this->numeroInscricaoSacado;
    }

    /**
     * @param mixed $numeroInscricaoSacado
     *
     * @return $this
     */
    public function setNumeroInscricaoSacado($numeroInscricaoSacado)
    {
        $this->numeroInscricaoSacado = $numeroInscricaoSacado;

        return $this;
    }

    /**
     * @return string
     */
    public function getNomeSacado()
    {
        return $this->nomeSacado;
    }

    /**
     * @param mixed $nomeSacado
     *
     * @return $this
     */
    public function setNomeSacado($nomeSacado)
    {
        $this->nomeSacado = $nomeSacado;

        return $this;
    }

    /**
     * @return string
     */
    public function getContaCobranca()
    {
        return $this->contaCobranca;
    }

    /**
     * @param mixed $contaCobranca
     *
     * @return $this
     */
    public function setContaCobranca($contaCobranca)
    {
        $this->contaCobranca = $contaCobranca;

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
     * @param mixed $valorTarifa
     *
     * @return $this
     */
    public function setValorTarifa($valorTarifa)
    {
        $this->valorTarifa = $valorTarifa;

        return $this;
    }

    /**
     * @return string
     */
    public function getIdentificacaoRejeicao()
    {
        return $this->identificacaoRejeicao;
    }

    /**
     * @param mixed $identificacaoRejeicao
     *
     * @return $this
     */
    public function setIdentificacaoRejeicao($identificacaoRejeicao)
    {
        $this->identificacaoRejeicao = $identificacaoRejeicao;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getIdentificador()
    {
        return $this->identificador;
    }

    /**
     * @param mixed $identificador
     *
     * @return $this
     */
    public function setIdentificador($identificador)
    {
        $this->identificador = $identificador;

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
     *
     * @return DetalheSegmentoT
     */
    public function setNumeroDocumento($numeroDocumento)
    {
        $this->numeroDocumento = (int) $numeroDocumento;

        return $this;
    }

    /**
     * @return string
     */
    public function getValorPagoSacado()
    {
        return $this->valorPagoSacado;
    }

    /**
     * @param mixed $valorPagoSacado
     *
     * @return $this
     */
    public function setValorPagoSacado($valorPagoSacado)
    {
        $this->valorPagoSacado = $valorPagoSacado;

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
     * Determine if an attribute exists.
     *
     * @param  string $key
     * @return bool
     */
    public function __isset($key)
    {
        return isset($this->$key);
    }
}