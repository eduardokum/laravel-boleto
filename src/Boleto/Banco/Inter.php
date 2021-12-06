<?php
namespace Eduardokum\LaravelBoleto\Boleto\Banco;

use Carbon\Carbon;
use Eduardokum\LaravelBoleto\Boleto\AbstractBoleto;
use Eduardokum\LaravelBoleto\Contracts\Boleto\BoletoAPI as BoletoAPIContract;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto;
use Eduardokum\LaravelBoleto\Util;
use Illuminate\Support\Arr;

class Inter extends AbstractBoleto implements BoletoAPIContract
{
    public function __construct(array $params = [])
    {
        parent::__construct($params);
        $this->setCamposObrigatorios('operacao');
    }
    protected $agencia = '0001';
    protected $carteira = '112';
    /**
     * Código do banco
     *
     * @var string
     */
    protected $codigoBanco = Boleto::COD_BANCO_INTER;
    /**
     * Define as carteiras disponíveis para este banco
     * '02' => Com registro | '09' => Com registro | '06' => Sem Registro | '21' => Com Registro - Pagável somente no Bradesco | '22' => Sem Registro - Pagável somente no Bradesco | '25' => Sem Registro - Emissão na Internet | '26' => Com Registro - Emissão na Internet
     *
     * @var array
     */
    protected $carteiras = ['112'];
    /**
     * Espécie do documento, coódigo para remessa
     *
     * @var string
     */
    protected $especiesCodigo = [
    ];
    /**
     * @var string
     */
    protected $operacao;
    /**
     * @var string[]
     */
    protected $protectedFields = [
    ];
    /**
     * @return string
     */
    public function getOperacao()
    {
        return $this->operacao;
    }
    /**
     * @param $operacao
     *
     * @return $this
     */
    public function setOperacao($operacao)
    {
        $this->operacao = $operacao;

        return $this;
    }
    /**
     * Gera o Nosso Número.
     *
     * @return null
     */
    protected function gerarNossoNumero()
    {
        return null;
    }

    /**
     * Método que retorna o nosso numero usado no boleto. alguns bancos possuem algumas diferenças.
     *
     * @return string
     */
    public function getNossoNumeroBoleto()
    {
        return $this->getAgencia() . Util::modulo11($this->getAgencia()) . '/'
             . $this->getCarteira() . '/'
            . substr($this->getNossoNumero(), 0, -1) . '-' . substr($this->getNossoNumero(), -1);

    }

    /**
     * @return string
     */
    public function getAgenciaCodigoBeneficiario(){
        return $this->getAgencia() . Util::modulo11($this->getAgencia()) . ' / ' .
            $this->getConta() . Util::modulo11($this->getConta());
    }
    /**
     * Método para gerar o código da posição de 20 a 44
     *
     * @return string
     */
    protected function getCampoLivre()
    {
        if ($this->campoLivre) {
            return $this->campoLivre;
        }

        $campoLivre = Util::numberFormatGeral('0001', 4);
        $campoLivre .= Util::numberFormatGeral('112', 3);
        $campoLivre .= Util::numberFormatGeral($this->getOperacao(), 7);
        $campoLivre .= Util::numberFormatGeral($this->getNossoNumero(), 11);

        return $this->campoLivre = $campoLivre;
    }

    /**
     * Seta dias para baixa automática
     *
     * @param int $baixaAutomatica
     *
     * @return $this
     * @throws \Exception
     */
    public function setDiasBaixaAutomatica($baixaAutomatica)
    {
        if (!in_array($baixaAutomatica, [0, 30, 60])) {
            throw new \Exception('Baixa automática válida somente 0, 30, 60');
        }
        $this->diasBaixaAutomatica = $baixaAutomatica >= 0 ? $baixaAutomatica : 0;
        return $this;
    }

    /**
     * Método onde qualquer boleto deve extender para gerar o código da posição de 20 a 44
     *
     * @param $campoLivre
     *
     * @return array
     */
    public static function parseCampoLivre($campoLivre) {
        return [
            'convenio' => substr($campoLivre, 7, 7),
            'agenciaDv' => null,
            'contaCorrenteDv' => null,
            'agencia' => substr($campoLivre, 0, 4),
            'carteira' => substr($campoLivre, 4, 3),
            'nossoNumero' => substr($campoLivre, 14, 10),
            'nossoNumeroDv' => substr($campoLivre, 24, 1),
            'nossoNumeroFull' => substr($campoLivre, 14, 11),
            'contaCorrente' => null,
        ];
    }

    /**
     * Return Boleto Array.
     *
     * @return array
     */
    public function toArrayAPI()
    {
        $diasBaixaAutomatica = $this->getDiasBaixaAutomatica();
        if ($diasBaixaAutomatica == 60) {
            $diasBaixaAutomatica = 'SESSENTA';
        } elseif ($diasBaixaAutomatica == 30) {
            $diasBaixaAutomatica = 'TRINTA';
        } else {
            $diasBaixaAutomatica = 'ZERO';
        }

        $enderecoSplit = function($endereco) {
            $endereco = explode(',', $endereco);
            return [
                'endereco' => $endereco[0],
                'numero' => array_key_exists(1, $endereco)
                    ? Util::onlyNumbers(explode(' ', trim($endereco[1]))[0])
                    : 0
            ];
        };

        $desconto = $descontoDefault = [
            'codigoDesconto' => 'NAOTEMDESCONTO',
            'data' => '',
            'taxa' => 0,
            'valor' => 0,
        ];
        if ($this->getDesconto()) {
            $desconto = [
                'codigoDesconto' => 'VALORFIXODATAINFORMADA',
                'data' => $this->getDataDesconto()->format('Y-m-d'),
                'taxa' => 0,
                'valor' => Util::nFloat($this->getDesconto()),
            ];
        }

        $multa = [
            'codigoMulta' => 'NAOTEMMULTA',
            'data' => '',
            'taxa' => 0,
            'valor' => 0,
        ];
        if ($this->getMulta()) {
            $multa = [
                'codigoMulta' => 'VALORFIXO',
                'data' => $this->getDataVencimento()->format('Y-m-d'),
                'taxa' => 0,
                'valor' => Util::nFloat($this->getMulta()),
            ];
        }

        $mora = [
            'codigoMora' => 'ISENTO',
            'data' => '',
            'taxa' => 0,
            'valor' => 0,
        ];
        if ($this->getJuros()) {
            $mora = [
                'codigoMora' => 'TAXAMENSAL',
                'data' => $this->getDataVencimento()->addDays($this->getJurosApos() ?: 0)->format('Y-m-d'),
                'taxa' => Util::nFloat($this->getJuros()),
                'valor' => 0,
            ];
        }

        $mensagem = array_filter($this->getDescricaoDemonstrativo());
        foreach ($mensagem as $k => $m) {
            $mensagem['linha' . ($k+1)] = $m;
            unset($mensagem[$k]);
        }

        return array_filter([
            'seuNumero'           => $this->getNumero(),
            'cnpjCPFBeneficiario' => sprintf('%014s', Util::onlyNumbers($this->getBeneficiario()->getDocumento())),
            'valorNominal'        => Util::nFloat($this->getValor(), 2, false),
            'dataEmissao'         => $this->getDataDocumento()->format('Y-m-d'),
            'dataVencimento'      => $this->getDataVencimento()->format('Y-m-d'),
            'dataLimite'          => 'SESSENTA',
            'numDiasAgenda'       => $diasBaixaAutomatica,
            'mensagem'            => $mensagem,
            'desconto1'           => $desconto,
            'desconto2'           => $descontoDefault,
            'desconto3'           => $descontoDefault,
            'multa'               => $multa,
            'mora'                => $mora,
            'pagador'             => [
                'tipoPessoa' => strlen(Util::onlyNumbers($this->getPagador()->getDocumento())) == 14 ? 'JURIDICA' : 'FISICA',
                'nome'       => $this->getPagador()->getNome(),
                'endereco'   => $enderecoSplit($this->getPagador()->getEndereco())['endereco'],
                'numero'     => $enderecoSplit($this->getPagador()->getEndereco())['numero'],
                'bairro'     => $this->getPagador()->getBairro(),
                'cep'        => Util::onlyNumbers($this->getPagador()->getCep()),
                'uf'         => $this->getPagador()->getUf(),
                'cidade'     => $this->getPagador()->getCidade(),
                'cnpjCpf'    => Util::onlyNumbers($this->getPagador()->getDocumento()),
            ],
        ]);
    }

    /**
     * @param $boleto
     * @param $appends
     *
     * @return Inter
     * @throws \Exception
     */
    public static function createFromAPI($boleto, $appends)
    {
        if(!array_key_exists('beneficiario', $appends)) {
            throw new \Exception('Informe o beneficiario');
        }
        if(!array_key_exists('conta', $appends)) {
            throw new \Exception('Informe a conta');
        }
        $ipte = Util::IPTE2Variveis($boleto->linhaDigitavel);

        return new self(array_merge(array_filter([
            'nossoNumero'     => $boleto->nossoNumero,
            'valor'           => $boleto->valorNominal,
            'numero'          => $boleto->seuNumero,
            'numeroDocumento' => $boleto->seuNumero,
            'aceite'          => 'S',
            'especieDoc'      => 'DM',
            'dataVencimento'  => Carbon::createFromFormat('d/m/Y', $boleto->dataVencimento),
            'pagador'         => [
                'nome'      => $boleto->nomeSacado,
                'documento' => $boleto->cnpjCpfSacado,
            ],
            'multa'           => Arr::get($boleto, 'multa.valor', 0),
            'juros'           => Arr::get($boleto, 'juros.taxa', 0),
            'desconto'        => Arr::get($boleto, 'desconto1.taxa', 0),
            'data_desconto'   => Arr::get($boleto, 'desconto1.data'),
            'carteira'        => $ipte['campo_livre_parsed']['carteira'],
            'operacao'        => $ipte['campo_livre_parsed']['convenio'],
        ]), $appends));
    }


    /**
     * DEFAULTS
     */
    public function setCarteira($carteira)
    {
        $this->carteira = '112';
        return $this;
    }
    public function setAgencia($agencia)
    {
        $this->agencia = '0001';
        return $this;
    }
    public function setEspecieDoc($especieDoc)
    {
        $this->especieDoc = 'DM';
        return $this;
    }
    public function setNossoNumero($nossoNumero)
    {
        $this->campoNossoNumero = $nossoNumero;
    }
}
