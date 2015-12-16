<?php
namespace Eduardokum\LaravelBoleto\Boleto;

use Carbon\Carbon;
use Eduardokum\LaravelBoleto\Boleto\Render\Pdf;
use Eduardokum\LaravelBoleto\Util;

class AbstractBoleto
{



    protected $banco;

    public $logo;
    public $agencia;
    public $conta;
    public $carteira;
    public $carteiraDescricao;
    public $numero;

    public $identificacao;
    public $especieDocumento;
    public $aceite = 'N';
    public $dataDocumento;
    public $dataProcessamento;
    public $dataVencimento;
    public $valor;
    public $codigoBaixa;
    public $numeroMoeda = 9;
    public $nossoNumero;
    public $cedenteDocumento;
    public $cedenteNome;
    public $cedenteEndereco;
    public $cedenteCidadeUF;
    public $sacadoDocumento;
    public $sacadoNome;
    public $sacadoEndereco;
    public $sacadoCidadeUF;

    protected $demonstrativos = [];
    protected $instrucoes = [];
    protected $agenciaConta;
    protected $linha;
    protected $codigoBarras;
    protected $localPagamento = 'Pagável em qualquer Banco até o vencimento';

    /**
     * AbstractBoleto constructor.
     *
     * @param $banco
     */
    public function __construct($banco)
    {
        $this->banco = $banco;
        $this->dataProcessamento = Carbon::now();
    }

    public function getValor()
    {
        return $this->valor;
    }

    public function getLogo()
    {
        return $this->logo ? $this->logo : "http://dummyimage.com/300x70/f5/0.png&text=Sem+Logo";
    }

    public function getBanco($verificacao = false)
    {
        if($verificacao)
        {
            return sprintf('%s-%s', $this->banco, Util::modulo11($this->banco));
        }
        return $this->banco;
    }

    public function getIdentificacao()
    {
        return $this->identificacao;
    }

    public function getCodigoBaixa()
    {
        return $this->codigoBaixa;
    }

    public function getAgenciaConta()
    {
        return $this->agenciaConta;
    }

    public function getAgencia()
    {
        return $this->agencia;
    }

    public function getConta()
    {
        return $this->conta;
    }

    public function getNossoNumero()
    {
        return $this->nossoNumero;
    }

    public function getNumero()
    {
        return $this->numero;
    }

    public function getLinha()
    {
        return $this->linha;
    }

    public function getCodigoBarras()
    {
        return $this->codigoBarras;
    }

    public function getDataVencimento()
    {
        return is_string($this->dataVencimento) ? Carbon::createFromFormat('Y-m-d', $this->dataVencimento) : $this->dataVencimento;
    }

    public function getDataProcessamento()
    {
        return is_string($this->dataProcessamento) ? Carbon::createFromFormat('Y-m-d', $this->dataProcessamento) : $this->dataProcessamento;
    }

    public function getDataDocumento()
    {
        return is_string($this->dataDocumento) ? Carbon::createFromFormat('Y-m-d', $this->dataDocumento) : $this->dataDocumento;
    }

    public function getDemonstrativos()
    {
        return $this->demonstrativos;
    }

    public function getInstrucoes()
    {
        return $this->instrucoes;
    }

    public function getLocalPagamento()
    {
        return $this->localPagamento;
    }

    public function getEspecieDocumento()
    {
        return $this->especieDocumento;
    }

    public function getAceite()
    {
        return is_bool($this->aceite) || is_numeric($this->aceite) ? ($this->aceite ? 'S' : 'N') : $this->aceite;
    }

    public function getCarteira($descticao = false)
    {
        return $this->carteira . ($descticao?' - '.$this->carteiraDescricao:'');
    }

    public function getCedenteDocumento()
    {
        return $this->cedenteDocumento;
    }

    public function getCedenteNome()
    {
        return $this->cedenteNome;
    }

    public function getCedenteEndereco()
    {
        return $this->cedenteEndereco;
    }

    public function getCedenteCidadeUF()
    {
        return $this->cedenteCidadeUF;
    }

    public function getSacadoNome()
    {
        return $this->sacadoNome;
    }

    public function getSacadoDocumento()
    {
        return $this->sacadoDocumento;
    }

    public function getSacadoEndereco()
    {
        return $this->sacadoEndereco;
    }

    public function getSacadoCidadeUF()
    {
        return $this->sacadoCidadeUF;
    }

    /**
     * Add a demonstrative string to a billet.
     *
     * @param $demonstrativo
     *
     * @throws \Exception
     */
    public function addDemonstrative($demonstrativo)
    {
        if(count($demonstrativo) >= 5 )
        {
            throw new \Exception('Too many demonstrativos. Max of 5');
        }

        $this->demonstrativos[] = $demonstrativo;
    }

    /**
     * Add a instruction string to a billet.
     *
     * @param $instrucao
     *
     * @throws \Exception
     */
    public function addInstruction($instrucao)
    {
        if(count($instrucao) >= 5 )
        {
            throw new \Exception('Too many instrucoes. Max of 5');
        }

        $this->instrucoes[] = $instrucao;
    }

    protected function preProcessamento()
    {}

    protected function gerarCodigoBarras()
    {
        throw new \Exception('O método gerarCodigoBarras precisa ser implementando');
    }

    protected function gerarLinha()
    {
        throw new \Exception('O método gerarLinha precisa ser implementando');
    }

    public function processar()
    {
        if(method_exists($this, 'preProcessamento'))
        {
            $this->preProcessamento();
        }

        $this->gerarCodigoBarras();
        $this->gerarLinha();
    }

    public function render()
    {
        if(empty($this->getLinha()))
        {
            $this->processar();
        }

        $pdf = new Pdf();
        $pdf->addBoleto($this);
        return $pdf->gerarBoleto($pdf::OUTPUT_STANDARD, false);
    }

}