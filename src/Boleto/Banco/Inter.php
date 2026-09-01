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
        $formasRecebimento[] = 'BOLETO';
        try {
            if ($this->validarPix()) {
                $formasRecebimento[] = 'PIX';
            }
        } catch (\Exception $e) {
        }

        $desconto = null;
        if ($this->getDesconto()) {
            $desconto = [
                'codigo'         => 'VALORFIXODATAINFORMADA',
                'quantidadeDias' => $this->getDataDesconto()->diffInDays($this->getDataVencimento()),
                'valor'          => Util::nFloat($this->getDesconto()),
            ];
        }

        $multa = null;
        if ($this->getMulta()) {
            $multa = [
                'codigo' => 'PERCENTUAL',
                'taxa'   => Util::nFloat($this->getMulta()),
            ];
        }

        $mora = null;
        if ($this->getJuros()) {
            $mora = [
                'codigo' => 'TAXAMENSAL',
                'taxa'   => Util::nFloat($this->getJuros()),
            ];
        }

        $mensagem = array_filter($this->getDescricaoDemonstrativo());
        foreach ($mensagem as $k => $m) {
            $mensagem['linha' . ($k + 1)] = $m;
            unset($mensagem[$k]);
        }

        return array_filter([
            'seuNumero'      => $this->getNumero(),
            'valorNominal'   => Util::nFloat($this->getValor(), 2, false),
            'dataVencimento' => $this->getDataVencimento()->format('Y-m-d'),
            'numDiasAgenda'  => min(60, $this->getDiasBaixaAutomatica()),
            'pagador'        => [
                'cpfCnpj'    => Util::onlyAlphanumber($this->getPagador()->getDocumento()),
                'tipoPessoa' => strlen(Util::onlyAlphanumber($this->getPagador()->getDocumento())) == 14 ? 'JURIDICA' : 'FISICA',
                'nome'       => $this->getPagador()->getNome(),
                'endereco'   => $this->getPagador()->getEndereco(),
                'cidade'     => $this->getPagador()->getCidade(),
                'cep'        => Util::onlyNumbers($this->getPagador()->getCep()),
                'uf'         => $this->getPagador()->getUf(),
                'email'      => $this->getPagador()->getEmail(),
                'bairro'     => $this->getPagador()->getBairro(),
            ],
            'formasRecebimento' => $formasRecebimento,
            'mensagem'          => $mensagem,
            'desconto'          => $desconto,
            'multa'             => $multa,
            'mora'              => $mora,
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
        if (is_object($boleto)) {
            $boleto = json_decode(json_encode($boleto), true);
        }

        if (! array_key_exists('beneficiario', $appends)) {
            throw new ValidationException('Informe o beneficiario');
        }
        if (! array_key_exists('conta', $appends)) {
            throw new ValidationException('Informe a conta');
        }
        $ipte = Util::IPTE2Variveis(Arr::get($boleto, 'boleto.linhaDigitavel'));

        $aSituacao = [
            'PAGO'             => AbstractBoleto::SITUACAO_PAGO,
            'RECEBIDO'         => AbstractBoleto::SITUACAO_PAGO,
            'MARCADO_RECEBIDO' => AbstractBoleto::SITUACAO_PAGO,
            'BAIXADO'          => AbstractBoleto::SITUACAO_BAIXADO,
            'CANCELADO'        => AbstractBoleto::SITUACAO_BAIXADO,
            'EXPIRADO'         => AbstractBoleto::SITUACAO_BAIXADO,
            'VENCIDO'          => AbstractBoleto::SITUACAO_ABERTO,
            'A_RECEBER'        => AbstractBoleto::SITUACAO_ABERTO,
            'ATRASADO'         => AbstractBoleto::SITUACAO_ABERTO,
            'FALHA_EMISSAO'    => AbstractBoleto::SITUACAO_REJEITADO,
        ];

        $dateUS = preg_match('/[0-9]{4}-[0-9]{2}-[0-9]{2}.*?/', Arr::get($boleto, 'cobranca.dataSituacao'));

        $inter = new self(array_merge(array_filter([
            'valorRecebido' => Arr::get($boleto, 'cobranca.valorTotalRecebido'),
            'situacao'      => Arr::get($aSituacao, $boleto['cobranca']['situacao'], $boleto['cobranca']['situacao']),
            'dataSituacao'  => Arr::get($boleto, 'cobranca.dataSituacao')
                ? Carbon::createFromFormat($dateUS ? 'Y-m-d' : 'd/m/Y', Arr::get($boleto, 'cobranca.dataSituacao'))
                : Carbon::now(),
            'nossoNumero'     => Arr::get($boleto, 'boleto.nossoNumero'),
            'valor'           => Arr::get($boleto, 'cobranca.valorNominal'),
            'numero'          => Arr::get($boleto, 'cobranca.seuNumero'),
            'numeroDocumento' => Arr::get($boleto, 'cobranca.seuNumero'),
            'aceite'          => 'S',
            'especieDoc'      => 'DM',
            'dataVencimento'  => Carbon::createFromFormat($dateUS ? 'Y-m-d' : 'd/m/Y', Arr::get($boleto, 'cobranca.dataVencimento')),
            'pagador'         => array_filter([
                'nome'      => Arr::get($boleto, 'cobranca.pagador.nome'),
                'documento' => Arr::get($boleto, 'cobranca.pagador.cpfCnpj'),
                'endereco'  => trim(Arr::get($boleto, 'cobranca.pagador.endereco') . ' ' . Arr::get($boleto, 'cobranca.pagador.complemento')),
                'bairro'    => Arr::get($boleto, 'cobranca.pagador.bairro'),
                'cep'       => Arr::get($boleto, 'cobranca.pagador.cep'),
                'uf'        => Arr::get($boleto, 'cobranca.pagador.uf'),
                'cidade'    => Arr::get($boleto, 'cobranca.pagador.cidade'),
            ]),
            'multa'         => Arr::get($boleto, 'cobranca.multa.valor', 0),
            'juros'         => Arr::get($boleto, 'cobranca.mora.taxa', 0),
            'desconto'      => Arr::get($boleto, 'cobranca.desconto.0.taxa', 0),
            'data_desconto' => Arr::get($boleto, 'cobranca.desconto.0.quantidadeDias'),
            'carteira'      => $ipte['campo_livre_parsed']['carteira'],
            'operacao'      => $ipte['campo_livre_parsed']['convenio'],
        ]), $appends));

        if (isset($boleto->pix) && ! empty($boleto->pix->pixCopiaECola)) {
            $inter->convertPixCopiaECola($boleto->pix->pixCopiaECola);
        }

        return $inter;
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
