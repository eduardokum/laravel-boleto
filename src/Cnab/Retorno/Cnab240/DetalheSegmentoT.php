<?php

namespace Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab240;

use Carbon\Carbon;
use Eduardokum\LaravelBoleto\Contracts\Cnab\Retorno\Cnab240\DetalheSegmentoT as SegmentoT;

class DetalheSegmentoT implements SegmentoT
{
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
     * @var string
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
     * @return mixed
     */
    public function getCodigoBancoCompensacao()
    {
        return $this->codigoBancoCompensacao;
    }

    /**
     * @param mixed $codigoBancoCompensacao
     */
    public function setCodigoBancoCompensacao($codigoBancoCompensacao)
    {
        $this->codigoBancoCompensacao = $codigoBancoCompensacao;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getNumeroLoteRetorno()
    {
        return $this->numeroLoteRetorno;
    }

    /**
     * @param mixed $numeroLoteRetorno
     */
    public function setNumeroLoteRetorno($numeroLoteRetorno)
    {
        $this->numeroLoteRetorno = $numeroLoteRetorno;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTipoRegistro()
    {
        return $this->tipoRegistro;
    }

    /**
     * @param mixed $tipoRegistro
     */
    public function setTipoRegistro($tipoRegistro)
    {
        $this->tipoRegistro = $tipoRegistro;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getNumeroSequencialRegistroLote()
    {
        return $this->numeroSequencialRegistroLote;
    }

    /**
     * @param mixed $numeroSequencialRegistroLote
     */
    public function setNumeroSequencialRegistroLote($numeroSequencialRegistroLote)
    {
        $this->numeroSequencialRegistroLote = $numeroSequencialRegistroLote;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCodigoSegmentoRegistroDetalhe()
    {
        return $this->codigoSegmentoRegistroDetalhe;
    }

    /**
     * @param mixed $codigoSegmentoRegistroDetalhe
     */
    public function setCodigoSegmentoRegistroDetalhe($codigoSegmentoRegistroDetalhe)
    {
        $this->codigoSegmentoRegistroDetalhe = $codigoSegmentoRegistroDetalhe;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDigitoAgenciaCedente()
    {
        return $this->digitoAgenciaCedente;
    }

    /**
     * @param mixed $digitoAgenciaCedente
     */
    public function setDigitoAgenciaCedente($digitoAgenciaCedente)
    {
        $this->digitoAgenciaCedente = $digitoAgenciaCedente;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAgenciaCedente()
    {
        return $this->agenciaCedente;
    }

    /**
     * @param mixed $agenciaCedente
     */
    public function setAgenciaCedente($agenciaCedente)
    {
        $this->agenciaCedente = $agenciaCedente;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAgenciaCedenteDigito()
    {
        return $this->agenciaCedenteDigito;
    }

    /**
     * @param mixed $agenciaCedenteDigito
     */
    public function setAgenciaCedenteDigito($agenciaCedenteDigito)
    {
        $this->agenciaCedenteDigito = $agenciaCedenteDigito;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getContaCorrente()
    {
        return $this->contaCorrente;
    }

    /**
     * @param mixed $contaCorrente
     */
    public function setContaCorrente($contaCorrente)
    {
        $this->contaCorrente = $contaCorrente;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getContaDigito()
    {
        return $this->contaDigito;
    }

    /**
     * @param mixed $contaDigito
     */
    public function setContaDigito($contaDigito)
    {
        $this->contaDigito = $contaDigito;

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
     */
    public function setNossoNumero($nossoNumero)
    {
        $this->nossoNumero = $nossoNumero;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCodigoCarteira()
    {
        return $this->codigoCarteira;
    }

    /**
     * @param mixed $codigoCarteira
     */
    public function setCodigoCarteira($codigoCarteira)
    {
        $this->codigoCarteira = $codigoCarteira;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSeuNumero()
    {
        return $this->seuNumero;
    }

    /**
     * @param mixed $seuNumero
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
     */
    public function setDataVencimento($dataVencimento)
    {
        $this->dataVencimento = trim($dataVencimento, '0 ') ? Carbon::createFromFormat('dmy', $dataVencimento) : null;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getValorTitulo()
    {
        return $this->valorTitulo;
    }

    /**
     * @param mixed $valorTitulo
     */
    public function setValorTitulo($valorTitulo)
    {
        $this->valorTitulo = $valorTitulo;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getNumeroBancoCobradorRecebedor()
    {
        return $this->numeroBancoCobradorRecebedor;
    }

    /**
     * @param mixed $numeroBancoCobradorRecebedor
     */
    public function setNumeroBancoCobradorRecebedor($numeroBancoCobradorRecebedor)
    {
        $this->numeroBancoCobradorRecebedor = $numeroBancoCobradorRecebedor;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAgenciaCobradoraRecebedora()
    {
        return $this->agenciaCobradoraRecebedora;
    }

    /**
     * @param mixed $agenciaCobradoraRecebedora
     */
    public function setAgenciaCobradoraRecebedora($agenciaCobradoraRecebedora)
    {
        $this->agenciaCobradoraRecebedora = $agenciaCobradoraRecebedora;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCodigoMoeda()
    {
        return $this->codigoMoeda;
    }

    /**
     * @param mixed $codigoMoeda
     */
    public function setCodigoMoeda($codigoMoeda)
    {
        $this->codigoMoeda = $codigoMoeda;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTipoInscriçãoSacado()
    {
        return $this->tipoInscriçãoSacado;
    }

    /**
     * @param mixed $tipoInscriçãoSacado
     */
    public function setTipoInscriçãoSacado($tipoInscriçãoSacado)
    {
        $this->tipoInscriçãoSacado = $tipoInscriçãoSacado;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getNumeroInscricaoSacado()
    {
        return $this->numeroInscricaoSacado;
    }

    /**
     * @param mixed $numeroInscricaoSacado
     */
    public function setNumeroInscricaoSacado($numeroInscricaoSacado)
    {
        $this->numeroInscricaoSacado = $numeroInscricaoSacado;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getNomeSacado()
    {
        return $this->nomeSacado;
    }

    /**
     * @param mixed $nomeSacado
     */
    public function setNomeSacado($nomeSacado)
    {
        $this->nomeSacado = $nomeSacado;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getContaCobranca()
    {
        return $this->contaCobranca;
    }

    /**
     * @param mixed $contaCobranca
     */
    public function setContaCobranca($contaCobranca)
    {
        $this->contaCobranca = $contaCobranca;

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
     */
    public function setValorTarifa($valorTarifa)
    {
        $this->valorTarifa = $valorTarifa;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getIdentificacaoRejeicao()
    {
        return $this->identificacaoRejeicao;
    }

    /**
     * @param mixed $identificacaoRejeicao
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
     * @return Detalhe
     */
    public function setNumeroDocumento($numeroDocumento)
    {
        $this->numeroDocumento = ltrim(trim($numeroDocumento, ' '), '0');

        return $this;
    }

    /**
     * @return mixed
     */
    public function getValorPagoSacado()
    {
        return $this->valorPagoSacado;
    }

    /**
     * @param mixed $valorPagoSacado
     */
    public function setValorPagoSacado($valorPagoSacado)
    {
        $this->valorPagoSacado = $valorPagoSacado;

        return $this;
    }

}