<?php

namespace Eduardokum\LaravelBoleto\Boleto\Banco;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Eduardokum\LaravelBoleto\Util;
use Eduardokum\LaravelBoleto\Boleto\AbstractBoleto;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto;
use Eduardokum\LaravelBoleto\Exception\ValidationException;
use Eduardokum\LaravelBoleto\Contracts\Boleto\BoletoAPI as BoletoAPIContract;

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
     * @return Inter
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
        return sprintf('00019/112/%011s-%01s', substr($this->getNossoNumero(), 0, -1), substr($this->getNossoNumero(), -1));
    }

    /**
     * @return string
     */
    public function getAgenciaCodigoBeneficiario()
    {
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
     * Seta dia para baixa automática
     *
     * @param int $baixaAutomatica
     *
     * @return Inter
     * @throws ValidationException
     */
    public function setDiasBaixaAutomatica($baixaAutomatica)
    {
        if (! in_array($baixaAutomatica, [0, 30, 60])) {
            throw new ValidationException('Baixa automática válida somente 0, 30, 60');
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
    public static function parseCampoLivre($campoLivre)
    {
        return [
            'convenio'        => substr($campoLivre, 7, 7),
            'agenciaDv'       => null,
            'contaCorrenteDv' => null,
            'agencia'         => substr($campoLivre, 0, 4),
            'carteira'        => substr($campoLivre, 4, 3),
            'nossoNumero'     => substr($campoLivre, 14, 10),
            'nossoNumeroDv'   => substr($campoLivre, 24, 1),
            'nossoNumeroFull' => substr($campoLivre, 14, 11),
            'contaCorrente'   => null,
        ];
    }

    /**
     * Return Boleto Array.
     *
     * @return array
     */
    public function toAPI()
    {
        $diasBaixaAutomatica = $this->getDiasBaixaAutomatica();
        if ($diasBaixaAutomatica == 60) {
            $diasBaixaAutomatica = 'SESSENTA';
        } elseif ($diasBaixaAutomatica == 30) {
            $diasBaixaAutomatica = 'TRINTA';
        } else {
            $diasBaixaAutomatica = 'ZERO';
        }

        $enderecoSplit = function ($endereco) {
            $endereco = explode(',', $endereco);

            return [
                'endereco' => $endereco[0],
                'numero'   => array_key_exists(1, $endereco)
                    ? Util::onlyNumbers(explode(' ', trim($endereco[1]))[0])
                    : 0,
            ];
        };

        $desconto = $descontoDefault = [
            'codigoDesconto' => 'NAOTEMDESCONTO',
            'data'           => '',
            'taxa'           => 0,
            'valor'          => 0,
        ];
        if ($this->getDesconto()) {
            $desconto = [
                'codigoDesconto' => 'VALORFIXODATAINFORMADA',
                'data'           => $this->getDataDesconto()->format('Y-m-d'),
                'taxa'           => 0,
                'valor'          => Util::nFloat($this->getDesconto()),
            ];
        }

        $multa = [
            'codigoMulta' => 'NAOTEMMULTA',
            'data'        => '',
            'taxa'        => 0,
            'valor'       => 0,
        ];
        if ($this->getMulta()) {
            $multa = [
                'codigoMulta' => 'PERCENTUAL',
                'data'        => ($this->getDataVencimento()->copy())->addDay()->format('Y-m-d'),
                'taxa'        => Util::nFloat($this->getMulta()),
                'valor'       => 0,
            ];
        }

        $mora = [
            'codigoMora' => 'ISENTO',
            'data'       => '',
            'taxa'       => 0,
            'valor'      => 0,
        ];
        if ($this->getJuros()) {
            $mora = [
                'codigoMora' => 'TAXAMENSAL',
                'data'       => ($this->getDataVencimento()->copy())->addDays($this->getJurosApos() > 0 ? $this->getJurosApos() : 1)->format('Y-m-d'),
                'taxa'       => Util::nFloat($this->getJuros()),
                'valor'      => 0,
            ];
        }

        $mensagem = array_filter($this->getDescricaoDemonstrativo());
        foreach ($mensagem as $k => $m) {
            $mensagem['linha' . ($k + 1)] = $m;
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
     * @throws ValidationException
     */
    public static function fromAPI($boleto, $appends)
    {
        if (! array_key_exists('beneficiario', $appends)) {
            throw new ValidationException('Informe o beneficiario');
        }
        if (! array_key_exists('conta', $appends)) {
            throw new ValidationException('Informe a conta');
        }
        $ipte = Util::IPTE2Variveis($boleto->linhaDigitavel);

        $aSituacao = [
            'PAGO'     => AbstractBoleto::SITUACAO_PAGO,
            'BAIXADO'  => AbstractBoleto::SITUACAO_BAIXADO,
            'VENCIDO'  => AbstractBoleto::SITUACAO_ABERTO,
            'EXPIRADO' => AbstractBoleto::SITUACAO_BAIXADO,
        ];

        $dateUS = preg_match('/[0-9]{4}-[0-9]{2}-[0-9]{2}.*/', $boleto->dataHoraSituacao);

        return new self(array_merge(array_filter([
            'valorRecebido' => isset($boleto->valorTotalRecebimento) ? $boleto->valorTotalRecebimento : null,
            'situacao'      => Arr::get($aSituacao, $boleto->situacao, $boleto->situacao),
            'dataSituacao'  => $boleto->dataHoraSituacao
                ? Carbon::createFromFormat($dateUS ? 'Y-m-d H:i' : 'd/m/Y H:i', $boleto->dataHoraSituacao)
                : Carbon::now(),
            'nossoNumero'     => $boleto->nossoNumero,
            'valor'           => $boleto->valorNominal,
            'numero'          => $boleto->seuNumero,
            'numeroDocumento' => $boleto->seuNumero,
            'aceite'          => 'S',
            'especieDoc'      => 'DM',
            'dataVencimento'  => Carbon::createFromFormat($dateUS ? 'Y-m-d' : 'd/m/Y', $boleto->dataVencimento),
            'pagador'         => array_filter([
                'nome'      => isset($boleto->pagador) ? $boleto->pagador->nome : $boleto->nomeSacado,
                'documento' => isset($boleto->pagador) ? $boleto->pagador->cpfCnpj : $boleto->cnpjCpfSacado,
                'endereco'  => isset($boleto->pagador) ? trim($boleto->pagador->endereco . ', ' . $boleto->pagador->endereco . ' ' . $boleto->pagador->complemento) : null,
                'bairro'    => isset($boleto->pagador) ? $boleto->pagador->bairro : null,
                'cep'       => isset($boleto->pagador) ? $boleto->pagador->cep : null,
                'uf'        => isset($boleto->pagador) ? $boleto->pagador->uf : null,
                'cidade'    => isset($boleto->pagador) ? $boleto->pagador->cidade : null,
            ]),
            'multa'         => Arr::get($boleto, 'multa.valor', 0),
            'juros'         => Arr::get($boleto, 'juros.taxa', 0),
            'desconto'      => Arr::get($boleto, 'desconto1.taxa', 0),
            'data_desconto' => Arr::get($boleto, 'desconto1.data'),
            'carteira'      => $ipte['campo_livre_parsed']['carteira'],
            'operacao'      => $ipte['campo_livre_parsed']['convenio'],
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
        $nnClean = substr(Util::onlyNumbers($nossoNumero), -11);
        if (strlen($nnClean) > 11) {
            $nnClean = str_replace('00019112', '', $nnClean);
        }
        $this->campoNossoNumero = $nnClean;
    }

    /**
     * @return bool
     */
    public function imprimeBoleto()
    {
        return $this->campoNossoNumero > 0;
    }

    /**
     * @return mixed
     * @throws ValidationException
     */
    public function alterarBoleto()
    {
        throw new ValidationException('Banco Inter só possui comando de registro.');
    }

    /**
     * @return mixed
     * @throws ValidationException
     */
    public function alterarDataDeVencimento()
    {
        throw new ValidationException('Banco Inter só possui comando de registro.');
    }

    /**
     * @param $instrucao
     * @return mixed
     * @throws ValidationException
     */
    public function comandarInstrucao($instrucao)
    {
        throw new ValidationException('Banco Inter só possui comando de registro.');
    }

    /**
     * @return mixed
     * @throws ValidationException
     */
    public function baixarBoleto()
    {
        throw new ValidationException('Banco Inter só possui comando de registro.');
    }
}
