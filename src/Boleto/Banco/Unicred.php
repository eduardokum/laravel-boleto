<?php

namespace Eduardokum\LaravelBoleto\Boleto\Banco;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Eduardokum\LaravelBoleto\Util;
use Eduardokum\LaravelBoleto\CalculoDV;
use Eduardokum\LaravelBoleto\Boleto\AbstractBoleto;
use Eduardokum\LaravelBoleto\Contracts\Boleto\BoletoAPI as BoletoContract;

class Unicred extends AbstractBoleto implements BoletoContract
{
    /**
     * Código do banco
     * @var string
     */
    protected $codigoBanco = self::COD_BANCO_UNICRED;

    /**
     * Define as carteiras disponíveis para este banco
     * @var array
     */
    protected $carteiras = ['21'];

    /**
     * Linha de local de pagamento
     *
     * @var string
     */
    protected $localPagamento = 'PAGÁVEL EM QUALQUER AGÊNCIA BANCÁRIA/CORRESPONDENTE BANCÁRIO';

    /**
     * ESPÉCIE DO DOCUMENTO: de acordo com o ramo de atividade
     * @var string
     */
    protected $especiesCodigo = [
        'DM'     => 'DM', //'Duplicata Mercantil',
        'NP'     => 'NP', //'Nota Promissória',
        'NS'     => 'NS', //'Nota de Seguro',
        'CS'     => 'CS', //'Cobrança Seriada',
        'REC'    => 'REC', //'Recibo',
        'LC'     => 'LC', //'Letras de Câmbio',
        'ND'     => 'ND', //'Nota de Débito',
        'DS'     => 'DS', //'Duplicata de Serviços',
        'Outros' => 'Outros',
    ];

    /**
     * Trata-se de código utilizado para identificar mensagens especificas ao cedente, sendo
     * que o mesmo consta no cadastro do Banco, quando não houver código cadastrado preencher
     * com zeros "000".
     *
     * @var int
     */
    protected $cip = '000';

    /**
     * Variaveis adicionais.
     *
     * @var array
     */
    public $variaveis_adicionais = [
        'cip'        => '000',
        'mostra_cip' => true,
    ];

    /**
     * Código do cliente (é código do cedente, também chamado de código do beneficiário) é o código do emissor junto ao banco e precisa ser buscado junto ao gerente de contas essa informação
     *
     * @var string
     */
    protected $codigoCliente;

    /**
     * Define o numero da variação da carteira.
     *
     * @var string|null
     */
    protected $variacao_carteira = null;

    /**
     * Tipo de Juros
     */
    const TIPO_JURO_VALOR_DIARIO = '1';
    const TIPO_JURO_TAXA_DIARIA = '2';
    const TIPO_JURO_TAXA_MENSAL = '3';
    const TIPO_JURO_ISENTO = '5';

    /**
     * Tipo de Juro
     *
     * @var int
     */
    protected $tipoJuro = self::TIPO_JURO_ISENTO;

    /**
     * Tipo de Juro Válidos
     *
     * @var array<int>
     */
    protected $tipoJurosValidos = [
        'VALOR_DIARIO' => self::TIPO_JURO_VALOR_DIARIO,
        'TAXA_DIARIA' => self::TIPO_JURO_TAXA_DIARIA,
        'TAXA_MENSAL' => self::TIPO_JURO_TAXA_MENSAL,
        'ISENTO' => self::TIPO_JURO_ISENTO,
    ];

    /**
     * Tipo de Multa
     */
    const TIPO_MULTA_VALOR_FIXO = '1';
    const TIPO_MULTA_TAXA = '2';
    const TIPO_MULTA_ISENTO = '3';

    /**
     * Tipo de Multa
     *
     * @var string
     */
    protected $tipoMulta = self::TIPO_MULTA_ISENTO;

    /**
     * Tipo de Multas Válidos
     *
     * @var array<int>
     */
    protected $tipoMultasValidos = [
        'ISENTO' => self::TIPO_MULTA_ISENTO,
        'VALOR_FIXO' => self::TIPO_MULTA_VALOR_FIXO,
        'TAXA' => self::TIPO_MULTA_TAXA
    ];

    /**
     * Define a Tipo de Juro
     *
     * @param ?string $tipoJuro
     * @return AbstractBoleto
     */
    public function setTipoJuro($tipoJuro)
    {
        if(!isset($this->tipoJurosValidos[$tipoJuro])) {
            throw new \Exception("Tipo de juro não disponível!");
        }

        $this->tipoJuro = $this->tipoJurosValidos[$tipoJuro];

        return $this;
    }

    /**
     * Retorna Tipo de Juro
     *
     * @return string
     */
    public function getTipoJuro()
    {
        return $this->tipoJuro;
    }

    /**
     * Define a Tipo de Multa
     *
     * @param string $tipoMulta
     * @return AbstractBoleto
     */
    public function setTipoMulta($tipoMulta)
    {
        if(!isset($this->tipoMultasValidos[$tipoMulta])) {
            throw new \Exception("Tipo de multa não disponível!");
        }

        $this->tipoMulta = $this->tipoMultasValidos[$tipoMulta];

        return $this;
    }

    /**
     * Retorna Tipo de Multa
     *
     * @return string
     */
    public function getTipoMulta()
    {
        return $this->tipoMulta;
    }

    /**
     * Define o número da variação da carteira.
     *
     * @param  string|null $variacao_carteira
     * @return Unicred
     */
    public function setVariacaoCarteira($variacao_carteira)
    {
        $this->variacao_carteira = $variacao_carteira;

        return $this;
    }

    /**
     * Retorna o número da variacao de carteira
     *
     * @return string|null
     */
    public function getVariacaoCarteira()
    {
        return $this->variacao_carteira;
    }

    /**
     * Gera o Nosso Número. Formado com 11(onze) caracteres, sendo 10 dígitos
     * para o nosso número é um digito para o digito verificador. Ex.: 9999999999-D.
     * Obs.: O Nosso Número é um identificador do boleto, devendo ser atribuído
     * Nosso Número diferenciado para cada um.
     *
     * @return string
     */
    protected function gerarNossoNumero()
    {
        return Util::numberFormatGeral($this->getNumero(), 10) . CalculoDV::unicredNossoNumero($this->getNumero());
    }

    /**
     * Método que retorna o nosso numero usado no boleto, formato XXXXXXXXXX-D. alguns bancos possuem algumas diferenças.
     *
     * @return string
     */
    public function getNossoNumeroBoleto()
    {
        return substr_replace($this->getNossoNumero(), '-', -1, 0);
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

        $nossoNumero = $this->getNossoNumero();

        $campoLivre = Util::numberFormatGeral($this->getAgencia(), 4); //Agência BENEFICIÁRIO (Sem o dígito verificador, completar com zeros à esquerda quando necessário)
        $campoLivre .= Util::numberFormatGeral($this->getConta() . $this->getContaDv(), 10); //Conta do BENEFICIÁRIO (Com o dígito verificador - Completar com zeros à esquerda quando necessário)
        $campoLivre .= Util::numberFormatGeral($nossoNumero, 11); //Nosso Número (Com o dígito verificador)

        return $this->campoLivre = $campoLivre;
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
            // 'convenio' => null,
            'agenciaDv'       => null,
            'contaCorrenteDv' => null,
            'agencia'         => substr($campoLivre, 0, 4),
            'nossoNumero'     => substr($campoLivre, 14, 10),
            'nossoNumeroDv'   => substr($campoLivre, 24, 1),
            'nossoNumeroFull' => substr($campoLivre, 14, 11),
            'contaCorrente'   => substr($campoLivre, 4, 10),
        ];
    }

    /**
     * AGÊNCIA / CÓDIGO DO BENEFICIÁRIO: deverá ser preenchido com o código da agência,
     * contendo 4 (quatro) caracteres / Conta Corrente com 10 (dez) caracteres. Ex.
     * 9999/999999999-9. Obs.: Preencher com zeros à direita quando necessário.
     * @return string
     */
    public function getAgenciaCodigoBeneficiario()
    {
        return $this->getAgencia() . ' / ' . Util::numberFormatGeral($this->getConta(), 9) . '-' . $this->getContaDv();
    }

    /**
     * Define o campo CIP do boleto
     *
     * @param int $cip
     * @return Unicred
     */
    public function setCip($cip)
    {
        $this->cip = $cip;
        $this->variaveis_adicionais['cip'] = $this->getCip();

        return $this;
    }

    /**
     * Retorna o campo CIP do boleto
     *
     * @return string
     */
    public function getCip()
    {
        return Util::numberFormatGeral($this->cip, 3);
    }

    /**
     * Seta o código do cliente.
     *
     * @param mixed $codigoCliente
     *
     * @return Unicred
     */
    public function setCodigoCliente($codigoCliente)
    {
        $this->codigoCliente = $codigoCliente;

        return $this;
    }

    /**
     * Retorna o codigo do cliente.
     *
     * @return string
     */
    public function getCodigoCliente()
    {
        return $this->codigoCliente;
    }

    /**
     * Return Boleto Array.
     *
     * @return array
     */
    public function toAPI()
    {
        $data = [
            'beneficiarioVariacaoCarteira' => $this->getVariacaoCarteira(),
            'seuNumero'     => $this->getNumero(),
            'valor'         => Util::nFloat($this->getValor(), 2, false),
            'vencimento'    => $this->getDataVencimento()->format('Y-m-d'),
            'nossoNumero'   => null,
            'pagador' => [
                'nomeRazaoSocial' => substr($this->getPagador()->getNome(), 0, 40),
                'tipoPessoa'      => strlen(Util::onlyNumbers($this->getPagador()->getDocumento())) == 14 ? 'J' : 'F',
                'numeroDocumento' => Util::onlyNumbers($this->getPagador()->getDocumento()),
                'nomeFantasia'    => $this->getPagador()->getNomeFantasia(),
                'email'           => $this->getPagador()->getEmail(),
                'endereco' => [
                    'logradouro' => $this->getPagador()->getEndereco(),
                    'bairro'     => $this->getPagador()->getBairro(),
                    'cidade'     => $this->getPagador()->getCidade(),
                    'uf'         => $this->getPagador()->getUf(),
                    'cep'        => Util::onlyNumbers($this->getPagador()->getCep())
                ]
            ],
            'mensagensFichaCompensacao' => array_filter(array_map(function($instrucao) {
                return is_null($instrucao) ? null : trim($instrucao);
            }, $this->getInstrucoes()))
        ];

        if ($this->getDesconto()) {
            $data['desconto'] = [
                'indicador' => '0',
                'dataLimite' => $this->getDataDesconto()->format('Y-m-d'),
                'valor' => Util::nFloat($this->getDesconto()),
            ];
        }

        if ($this->getMulta()) {
            $data['multa'] = [
                'codigo' => $this->getTipoMulta(),
                'dataInicio' => ($this->getDataVencimento()->copy())->addDay()->format('Y-m-d'),
                'valor' => Util::nFloat($this->getMulta()),
            ];
        }

        if ($this->getJuros()) {
            $data['juros'] = [
                'codigo' => $this->getTipoJuro(),
                'dataInicio' => ($this->getDataVencimento()->copy())->addDays($this->getJurosApos() > 0 ? $this->getJurosApos() : 1)->format('Y-m-d'),
                'valor' => Util::nFloat($this->getJuros()),
            ];
        }

        return array_filter($data);
    }

    /**
     * @param object $boleto
     * @param array $appends
     *
     * @return BoletoContract
     * @throws \Exception
     */
    public static function fromAPI($boleto, $appends=[])
    {
        if(!array_key_exists('beneficiario', $appends)) {
            throw new \Exception('Informe o beneficiario');
        }

        if(!array_key_exists('conta', $appends)) {
            throw new \Exception('Informe a conta');
        }

        $ipte = Util::IPTE2Variveis($boleto->linhaDigitavel);

        $aSituacao = [
            'PAGO'      => AbstractBoleto::SITUACAO_PAGO,
            'LIQUIDADO' => AbstractBoleto::SITUACAO_PAGO,
            'BAIXADO'   => AbstractBoleto::SITUACAO_BAIXADO,
            'VENCIDO'   => AbstractBoleto::SITUACAO_ABERTO,
            'ABERTO'    => AbstractBoleto::SITUACAO_ABERTO,
            'EXPIRADO'  => AbstractBoleto::SITUACAO_BAIXADO,
        ];
        $dateUS = preg_match('/[0-9]{4}-[0-9]{2}-[0-9]{2}.*/', $boleto->dataDeVencimento);

        return new static(array_merge(array_filter([
            'nossoNumero'       => $boleto->nossoNumero,
            'dataSituacao'      => Carbon::now(),
            'valorRecebido'     => $boleto->valor,
            'situacao'          => Arr::get($aSituacao, $boleto->status, $boleto->status),
            'dataVencimento'    => Carbon::createFromFormat($dateUS ? 'Y-m-d' : 'd/m/Y', $boleto->dataDeVencimento),
            'valor'             => $boleto->valor,
            'carteira'          => isset($ipte['campo_livre_parsed']['carteira']) ? $ipte['campo_livre_parsed']['carteira'] : '21',
            'operacao'          => isset($ipte['campo_livre_parsed']['convenio']) ? $ipte['campo_livre_parsed']['convenio'] : null,
        ]), $appends));
    }

    /**
     * Mostra exception ao erroneamente tentar setar o nosso número
     *
     * @param string $nossoNumero
     * @return void
     */
    public function setNossoNumero($nossoNumero)
    {
        $nnClean = substr(Util::onlyNumbers($nossoNumero), -11);

        $this->campoNossoNumero = $nnClean;
    }
}
