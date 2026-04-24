<?php

namespace Eduardokum\LaravelBoleto\Cnab\Pagamento\Retorno\Cnab240;

use Carbon\Carbon;
use Eduardokum\LaravelBoleto\Util;
use Eduardokum\LaravelBoleto\Pessoa;
use Eduardokum\LaravelBoleto\MagicTrait;
use Eduardokum\LaravelBoleto\Exception\ValidationException;
use Eduardokum\LaravelBoleto\Contracts\Pessoa as PessoaContract;

class Detalhe
{
    use MagicTrait;

    /**
     * Tipos de ocorrência
     */
    const OCORRENCIA_PAGO = 'pago';
    const OCORRENCIA_REJEITADO = 'rejeitado';
    const OCORRENCIA_PENDENTE = 'pendente';
    const OCORRENCIA_CANCELADO = 'cancelado';
    const OCORRENCIA_ERRO = 'erro';
    const OCORRENCIA_OUTROS = 'outros';

    /**
     * Código da ocorrência do banco
     * @var string
     */
    protected $ocorrencia;

    /**
     * Tipo da ocorrência (pago, rejeitado, pendente, etc)
     * @var string
     */
    protected $ocorrenciaTipo;

    /**
     * Descrição da ocorrência
     * @var string
     */
    protected $ocorrenciaDescricao;

    /**
     * @var int
     */
    protected $numeroControle;

    /**
     * Número do documento/controle
     * @var string
     */
    protected $numeroDocumento;

    /**
     * Nosso número (identificação do banco)
     * @var string
     */
    protected $nossoNumero;

    /**
     * Seu número (identificação da empresa)
     * @var string
     */
    protected $seuNumero;

    /**
     * Data do pagamento
     * @var Carbon
     */
    protected $dataPagamento;

    /**
     * Data da ocorrência
     * @var Carbon
     */
    protected $dataOcorrencia;

    /**
     * Data real de efetivação
     * @var Carbon
     */
    protected $dataEfetivacao;

    /**
     * Valor do pagamento
     * @var float
     */
    protected $valor;

    /**
     * Valor real efetivado
     * @var float
     */
    protected $valorRealEfetivado;

    /**
     * Valor da tarifa
     * @var float
     */
    protected $valorTarifa;

    /**
     * Dados do favorecido/beneficiário
     * @var PessoaContract
     */
    protected $favorecido;

    /**
     * Código do banco do favorecido
     * @var string
     */
    protected $codigoBancoFavorecido;

    /**
     * Agência do favorecido
     * @var string
     */
    protected $agenciaFavorecido;

    /**
     * Conta do favorecido
     * @var string
     */
    protected $contaFavorecido;

    /**
     * Tipo de pagamento (TED, PIX, DOC, etc)
     * @var string
     */
    protected $tipoPagamento;

    /**
     * Mensagem de erro
     * @var string
     */
    protected $error;

    /**
     * Código(s) de rejeição
     * @var array
     */
    protected $rejeicoes = [];

    /**
     * Informações adicionais
     * @var array
     */
    protected $informacoesAdicionais = [];

    /**
     * Código de barras do boleto liquidado (segmento J).
     *
     * @var string|null
     */
    protected $codigoBarras;

    /**
     * Valor nominal do título (segmento J).
     *
     * @var float|null
     */
    protected $valorTitulo;

    /**
     * Valor de desconto + abatimento aplicado no pagamento (segmento J).
     *
     * @var float|null
     */
    protected $desconto;

    /**
     * Valor de mora + multa aplicado no pagamento (segmento J).
     *
     * @var float|null
     */
    protected $acrescimo;

    /**
     * Data de vencimento nominal do título (segmento J).
     *
     * @var Carbon|null
     */
    protected $dataVencimento;

    /**
     * Dados do sacado do boleto (segmento J-52).
     *
     * @var PessoaContract|null
     */
    protected $sacado;

    /**
     * Dados do cedente do boleto (segmento J-52).
     *
     * @var PessoaContract|null
     */
    protected $cedente;

    /**
     * Dados do sacador avalista (segmento J-52).
     *
     * @var PessoaContract|null
     */
    protected $sacadorAvalista;

    /**
     * Autenticação eletrônica do pagamento (segmento Z).
     *
     * @var string|null
     */
    protected $autenticacao;

    /**
     * @return string
     */
    public function getOcorrencia()
    {
        return $this->ocorrencia;
    }

    /**
     * @param string $ocorrencia
     * @return Detalhe
     */
    public function setOcorrencia($ocorrencia)
    {
        $this->ocorrencia = $ocorrencia;
        return $this;
    }

    /**
     * @return bool
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
    public function getOcorrenciaTipo()
    {
        return $this->ocorrenciaTipo;
    }

    /**
     * @param string $ocorrenciaTipo
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
    public function getNumeroDocumento()
    {
        return $this->numeroDocumento;
    }

    /**
     * @param string $numeroDocumento
     * @return Detalhe
     */
    public function setNumeroDocumento($numeroDocumento)
    {
        $this->numeroDocumento = $numeroDocumento;
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
    public function getNossoNumero()
    {
        return $this->nossoNumero;
    }

    /**
     * @param string $nossoNumero
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
    public function getSeuNumero()
    {
        return $this->seuNumero;
    }

    /**
     * @param string $seuNumero
     * @return Detalhe
     */
    public function setSeuNumero($seuNumero)
    {
        $this->seuNumero = $seuNumero;
        return $this;
    }

    /**
     * @param string $format
     * @return Carbon|null|string
     */
    public function getDataPagamento($format = 'd/m/Y')
    {
        return $this->dataPagamento instanceof Carbon
            ? $format === false ? $this->dataPagamento : $this->dataPagamento->format($format)
            : null;
    }

    /**
     * @param string $dataPagamento
     * @param string $format
     * @return Detalhe
     */
    public function setDataPagamento($dataPagamento, $format = 'dmY')
    {
        $this->dataPagamento = trim($dataPagamento, '0 ') ? Carbon::createFromFormat($format, $dataPagamento) : null;
        return $this;
    }

    /**
     * @param string $format
     * @return Carbon|null|string
     */
    public function getDataOcorrencia($format = 'd/m/Y')
    {
        return $this->dataOcorrencia instanceof Carbon
            ? $format === false ? $this->dataOcorrencia : $this->dataOcorrencia->format($format)
            : null;
    }

    /**
     * @param string $dataOcorrencia
     * @param string $format
     * @return Detalhe
     */
    public function setDataOcorrencia($dataOcorrencia, $format = 'dmY')
    {
        $this->dataOcorrencia = trim($dataOcorrencia, '0 ') ? Carbon::createFromFormat($format, $dataOcorrencia) : null;
        return $this;
    }

    /**
     * @param string $format
     * @return Carbon|null|string
     */
    public function getDataEfetivacao($format = 'd/m/Y')
    {
        return $this->dataEfetivacao instanceof Carbon
            ? $format === false ? $this->dataEfetivacao : $this->dataEfetivacao->format($format)
            : null;
    }

    /**
     * @param string $dataEfetivacao
     * @param string $format
     * @return Detalhe
     */
    public function setDataEfetivacao($dataEfetivacao, $format = 'dmY')
    {
        $this->dataEfetivacao = trim($dataEfetivacao, '0 ') ? Carbon::createFromFormat($format, $dataEfetivacao) : null;
        return $this;
    }

    /**
     * @return float
     */
    public function getValor()
    {
        return $this->valor;
    }

    /**
     * @param float $valor
     * @return Detalhe
     */
    public function setValor($valor)
    {
        $this->valor = $valor;
        return $this;
    }

    /**
     * @return float
     */
    public function getValorRealEfetivado()
    {
        return $this->valorRealEfetivado;
    }

    /**
     * @param float $valorRealEfetivado
     * @return Detalhe
     */
    public function setValorRealEfetivado($valorRealEfetivado)
    {
        $this->valorRealEfetivado = $valorRealEfetivado;
        return $this;
    }

    /**
     * @return float
     */
    public function getValorTarifa()
    {
        return $this->valorTarifa;
    }

    /**
     * @param float $valorTarifa
     * @return Detalhe
     */
    public function setValorTarifa($valorTarifa)
    {
        $this->valorTarifa = $valorTarifa;
        return $this;
    }

    /**
     * @return PessoaContract
     */
    public function getFavorecido()
    {
        return $this->favorecido;
    }

    /**
     * @param array|PessoaContract $favorecido
     * @return Detalhe
     * @throws ValidationException
     */
    public function setFavorecido($favorecido)
    {
        Util::addPessoa($this->favorecido, $favorecido);
        return $this;
    }

    /**
     * @return string
     */
    public function getCodigoBancoFavorecido()
    {
        return $this->codigoBancoFavorecido;
    }

    /**
     * @param string $codigoBancoFavorecido
     * @return Detalhe
     */
    public function setCodigoBancoFavorecido($codigoBancoFavorecido)
    {
        $this->codigoBancoFavorecido = $codigoBancoFavorecido;
        return $this;
    }

    /**
     * @return string
     */
    public function getAgenciaFavorecido()
    {
        return $this->agenciaFavorecido;
    }

    /**
     * @param string $agenciaFavorecido
     * @return Detalhe
     */
    public function setAgenciaFavorecido($agenciaFavorecido)
    {
        $this->agenciaFavorecido = $agenciaFavorecido;
        return $this;
    }

    /**
     * @return string
     */
    public function getContaFavorecido()
    {
        return $this->contaFavorecido;
    }

    /**
     * @param string $contaFavorecido
     * @return Detalhe
     */
    public function setContaFavorecido($contaFavorecido)
    {
        $this->contaFavorecido = $contaFavorecido;
        return $this;
    }

    /**
     * @return string
     */
    public function getTipoPagamento()
    {
        return $this->tipoPagamento;
    }

    /**
     * @param string $tipoPagamento
     * @return Detalhe
     */
    public function setTipoPagamento($tipoPagamento)
    {
        $this->tipoPagamento = $tipoPagamento;
        return $this;
    }

    /**
     * Retorna se foi pago com sucesso
     * @return bool
     */
    public function isPago()
    {
        return $this->getOcorrenciaTipo() == self::OCORRENCIA_PAGO;
    }

    /**
     * Retorna se foi rejeitado
     * @return bool
     */
    public function isRejeitado()
    {
        return $this->getOcorrenciaTipo() == self::OCORRENCIA_REJEITADO;
    }

    /**
     * Retorna se tem erro
     * @return bool
     */
    public function hasError()
    {
        return $this->getOcorrenciaTipo() == self::OCORRENCIA_ERRO || $this->isRejeitado();
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
     * @return Detalhe
     */
    public function setError($error)
    {
        $this->ocorrenciaTipo = self::OCORRENCIA_ERRO;
        $this->error = $error;
        return $this;
    }

    /**
     * @return array
     */
    public function getRejeicoes()
    {
        return $this->rejeicoes;
    }

    /**
     * @param array $rejeicoes
     * @return Detalhe
     */
    public function setRejeicoes(array $rejeicoes)
    {
        $this->rejeicoes = $rejeicoes;
        return $this;
    }

    /**
     * Adiciona uma rejeição
     * @param string $codigo
     * @param string $descricao
     * @return Detalhe
     */
    public function addRejeicao($codigo, $descricao)
    {
        $this->rejeicoes[$codigo] = $descricao;
        return $this;
    }

    /**
     * @return array
     */
    public function getInformacoesAdicionais()
    {
        return $this->informacoesAdicionais;
    }

    /**
     * @param array $informacoesAdicionais
     * @return Detalhe
     */
    public function setInformacoesAdicionais(array $informacoesAdicionais)
    {
        $this->informacoesAdicionais = $informacoesAdicionais;
        return $this;
    }

    /**
     * Adiciona uma informação adicional
     * @param string $chave
     * @param mixed $valor
     * @return Detalhe
     */
    public function addInformacaoAdicional($chave, $valor)
    {
        $this->informacoesAdicionais[$chave] = $valor;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCodigoBarras()
    {
        return $this->codigoBarras;
    }

    /**
     * @param string|null $codigoBarras
     * @return Detalhe
     */
    public function setCodigoBarras($codigoBarras)
    {
        $this->codigoBarras = $codigoBarras;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getValorTitulo()
    {
        return $this->valorTitulo;
    }

    /**
     * @param float|null $valorTitulo
     * @return Detalhe
     */
    public function setValorTitulo($valorTitulo)
    {
        $this->valorTitulo = $valorTitulo;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getDesconto()
    {
        return $this->desconto;
    }

    /**
     * @param float|null $desconto
     * @return Detalhe
     */
    public function setDesconto($desconto)
    {
        $this->desconto = $desconto;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getAcrescimo()
    {
        return $this->acrescimo;
    }

    /**
     * @param float|null $acrescimo
     * @return Detalhe
     */
    public function setAcrescimo($acrescimo)
    {
        $this->acrescimo = $acrescimo;
        return $this;
    }

    /**
     * @param string $format
     * @return Carbon|null|string
     */
    public function getDataVencimento($format = 'd/m/Y')
    {
        return $this->dataVencimento instanceof Carbon
            ? ($format === false ? $this->dataVencimento : $this->dataVencimento->format($format))
            : null;
    }

    /**
     * @param string $dataVencimento
     * @param string $format
     * @return Detalhe
     */
    public function setDataVencimento($dataVencimento, $format = 'dmY')
    {
        $this->dataVencimento = trim($dataVencimento, '0 ') ? Carbon::createFromFormat($format, $dataVencimento) : null;
        return $this;
    }

    /**
     * @return PessoaContract|null
     */
    public function getSacado()
    {
        return $this->sacado;
    }

    /**
     * @param array|PessoaContract $sacado
     * @return Detalhe
     * @throws ValidationException
     */
    public function setSacado($sacado)
    {
        Util::addPessoa($this->sacado, $sacado);
        return $this;
    }

    /**
     * @return PessoaContract|null
     */
    public function getCedente()
    {
        return $this->cedente;
    }

    /**
     * @param array|PessoaContract $cedente
     * @return Detalhe
     * @throws ValidationException
     */
    public function setCedente($cedente)
    {
        Util::addPessoa($this->cedente, $cedente);
        return $this;
    }

    /**
     * @return PessoaContract|null
     */
    public function getSacadorAvalista()
    {
        return $this->sacadorAvalista;
    }

    /**
     * @param array|PessoaContract $sacadorAvalista
     * @return Detalhe
     * @throws ValidationException
     */
    public function setSacadorAvalista($sacadorAvalista)
    {
        Util::addPessoa($this->sacadorAvalista, $sacadorAvalista);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAutenticacao()
    {
        return $this->autenticacao;
    }

    /**
     * @param string|null $autenticacao
     * @return Detalhe
     */
    public function setAutenticacao($autenticacao)
    {
        $this->autenticacao = $autenticacao;
        return $this;
    }

    /**
     * Indica se o detalhe corresponde a uma liquidação de boleto (segmento J).
     *
     * @return bool
     */
    public function isBoleto()
    {
        return ! empty($this->codigoBarras);
    }
}
