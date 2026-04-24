<?php

namespace Eduardokum\LaravelBoleto\Pagamento;

use Exception;
use Throwable;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Eduardokum\LaravelBoleto\Util;
use Eduardokum\LaravelBoleto\Pessoa;
use Eduardokum\LaravelBoleto\MagicTrait;
use Eduardokum\LaravelBoleto\Boleto\Render\Pdf;
use Eduardokum\LaravelBoleto\Boleto\Render\Html;
use Eduardokum\LaravelBoleto\Boleto\Render\PdfCaixa;
use Eduardokum\LaravelBoleto\Exception\ValidationException;
use Eduardokum\LaravelBoleto\Contracts\Pessoa as PessoaContract;
use Eduardokum\LaravelBoleto\Contracts\Pagamento\Pagamento as PagamentoContract;

/**
 * Class AbstractPagamento
 */
abstract class AbstractPagamento implements PagamentoContract
{
    use MagicTrait;

    const TIPO_CHAVEPIX_CPF = 'cpf';
    const TIPO_CHAVEPIX_CNPJ = 'cnpj';
    const TIPO_CHAVEPIX_CELULAR = 'celular';
    const TIPO_CHAVEPIX_EMAIL = 'email';
    const TIPO_CHAVEPIX_ALEATORIA = 'aleatoria';

    /**
     * Campos necessários para o boleto
     *
     * @var array
     */
    private $camposObrigatorios = [
        'numero',
        'agencia',
        'conta',
    ];

    protected $protectedFields = [];

    /**
     * @var string
     */
    protected $id;

    /**
     * Código do banco
     *
     * @var string
     */
    protected $codigoBanco;

    /**
     * Moeda
     *
     * @var int
     */
    protected $moeda = 9;

    /**
     * Variáveis adicionais.
     *
     * @var array
     */
    public $variaveis_adicionais = [];

    /**
     * Valor total do boleto
     *
     * @var float
     */
    public $valor;

    /**
     * Data de vencimento
     *
     * @var Carbon
     */
    public $dataVencimento;

    /**
     * Data de pagamento
     *
     * @var Carbon
     */
    public $dataPagamento;


    /**
     * Número do documento
     *
     * @var int
     */
    public $numeroDocumento;

    /**
     * Define o número definido pelo cliente para compor o Nosso Número
     *
     * @var int
     */
    public $numero;

    /**
     * Define o número definido pelo cliente para controle da remessa
     *
     * @var string
     */
    public $numeroControle;

    /**
     * Campo de uso do banco no boleto
     *
     * @var string
     */
    protected $usoBanco;


    /**
     * Agência
     *
     * @var string
     */
    protected $agencia;

    /**
     * Dígito da agência
     *
     * @var string
     */
    protected $agenciaDv;

    /**
     * Conta
     *
     * @var string
     */
    protected $conta;

    /**
     * Dígito da conta
     *
     * @var string
     */
    protected $contaDv;

    /**
     * Tipo de conta
     *
     * @var string
     */
    protected $tipoConta;

    /**
     * Forma de iniciação
     *
     * @var string
     */
    protected $formaIniciacao;


    /**
     * Entidade beneficiária (quem emite o boleto)
     *
     * @var PessoaContract
     */
    public $beneficiario;

    /**
     * Entidade pagadora (de quem se cobra o boleto)
     *
     * @var PessoaContract
     */
    public $pagador;


    /**
     * Cache do nosso número para evitar processamento desnecessário.
     *
     * @var string
     */
    protected $campoNossoNumero;


    /**
     * Valor Recebido
     */
    public $valorRecebido;


    /**
     * Chave Pix para criação de boleto com pix
     * @var null
     */
    private $pixChave = null;

    /**
     * Tipo da chave pix
     * @var null
     */
    private $pixChaveTipo = null;

    /**
     * Código de barras do boleto a ser liquidado (44 dígitos).
     * Utilizado em pagamentos de títulos em cobrança (segmento J/J-52).
     *
     * @var string|null
     */
    protected $codigoBarras = null;

    /**
     * Valor nominal do título (antes de descontos/acréscimos).
     * Quando não informado, assume o mesmo de getValor().
     *
     * @var float|null
     */
    protected $valorTitulo = null;

    /**
     * Desconto + abatimento concedidos no pagamento do boleto.
     *
     * @var float
     */
    protected $desconto = 0;

    /**
     * Mora + multa acrescidos no pagamento do boleto.
     *
     * @var float
     */
    protected $acrescimo = 0;

    /**
     * Sacador avalista (opcional) - para segmento J-52.
     *
     * @var PessoaContract|null
     */
    protected $sacadorAvalista = null;

    /**
     * AbstractPagamento constructor.
     *
     * @param array $params
     */
    public function __construct($params = [])
    {
        Util::fillClass($this, $params);
        // Marca a data de vencimento para daqui a 5 dias, caso não especificada
        if (! $this->getDataVencimento()) {
            $this->setDataVencimento(new Carbon(date('Y-m-d', strtotime('+5 days'))));
        }
    }

    /**
     * @return array
     */
    public function getProtectedFields()
    {
        return $this->protectedFields;
    }

    /**
     * Seta os campos obrigatórios
     *
     * @return $this
     */
    protected function setCamposObrigatorios()
    {
        $args = func_get_args();
        $this->camposObrigatorios = [];
        foreach ($args as $arg) {
            $this->addCampoObrigatorio($arg);
        }

        return $this;
    }

    /**
     * Adiciona os campos obrigatórios
     *
     * @return $this
     */
    protected function addCampoObrigatorio()
    {
        $args = func_get_args();
        foreach ($args as $arg) {
            ! is_array($arg) || call_user_func_array([$this, __FUNCTION__], $arg);
            ! is_string($arg) || array_push($this->camposObrigatorios, $arg);
        }

        return $this;
    }

    /**
     * @param $id
     * @return AbstractPagamento
     */
    public function setID($id)
    {
        $this->id = $this->validateId($id);

        return $this;
    }

    /**
     * @return string
     */
    public function getID()
    {
        return $this->id;
    }

    /**
     * Define a agência
     *
     * @param string $agencia
     *
     * @return AbstractPagamento
     */
    public function setAgencia($agencia)
    {
        $this->agencia = (string) $agencia;

        return $this;
    }

    /**
     * Retorna a agência
     *
     * @return string
     */
    public function getAgencia()
    {
        return $this->agencia;
    }

    /**
     * Define o dígito da agência
     *
     * @param string $agenciaDv
     *
     * @return AbstractPagamento
     */
    public function setAgenciaDv($agenciaDv)
    {
        $this->agenciaDv = $agenciaDv;

        return $this;
    }

    /**
     * Retorna o dígito da agência
     *
     * @return string
     */
    public function getAgenciaDv()
    {
        return $this->agenciaDv;
    }

    /**
     * Retorna a forma de iniciação
     *
     * @return string
     */
    public function getFormaIniciacao()
    {
        return $this->formaIniciacao;
    }

    /**
     * Define a forma de iniciação
     *
     * @param string $formaIniciacao
     * 
     * @return AbstractPagamento
     * @throws ValidationException
     */
    public function setFormaIniciacao($formaIniciacao)
    {
        $this->formaIniciacao = $formaIniciacao;
    }


    /**
     * Define a entidade beneficiario
     *
     * @param $beneficiario
     *
     * @return AbstractPagamento
     * @throws ValidationException
     */
    public function setBeneficiario($beneficiario)
    {
        Util::addPessoa($this->beneficiario, $beneficiario);
        $this->beneficiario->setTipo(Pessoa::TIPO_BENEFICIARIO);

        return $this;
    }

    /**
     * Retorna a entidade beneficiário
     *
     * @return PessoaContract
     */
    public function getBeneficiario()
    {
        return $this->beneficiario;
    }

    /**
     * Define o tipo de conta
     *
     * @param string $tipoConta
     *
     * @return AbstractPagamento
     */
    public function setTipoConta($tipoConta)
    {
        $this->tipoConta = $tipoConta;

        return $this;
    }

    /**
     * Retorna o tipo de conta
     *
     * @return string
     */
    public function getTipoConta()
    {
        return $this->tipoConta;
    }

    public function setCodigoBanco($codigoBanco)
    {
        $this->codigoBanco = $codigoBanco;
        return $this;
    }


    /**
     * Retorna o código do banco
     *
     * @return string
     */
    public function getCodigoBanco()
    {
        return $this->codigoBanco;
    }

    /**
     * Define o número da conta
     *
     * @param string $conta
     *
     * @return AbstractPagamento
     */
    public function setConta($conta)
    {
        $this->conta = (string) $conta;

        return $this;
    }

    /**
     * Define o número da conta
     *
     * @param string $conta
     *
     * @return AbstractPagamento
     */
    public function setContaCorrente($conta)
    {
        $this->conta = (string) $conta;

        return $this;
    }

    /**
     * Retorna o número da conta
     *
     * @return string
     */
    public function getConta()
    {
        return $this->conta;
    }

    /**
     * Define o dígito verificador da conta
     *
     * @param string $contaDv
     *
     * @return AbstractPagamento
     */
    public function setContaDv($contaDv)
    {
        $this->contaDv = $contaDv;

        return $this;
    }

    /**
     * Define o dígito verificador da conta
     *
     * @param string $contaDv
     *
     * @return AbstractPagamento
     */
    public function setContaCorrenteDv($contaDv)
    {
        $this->contaDv = $contaDv;

        return $this;
    }

    /**
     * Retorna o dígito verificador da conta
     *
     * @return string
     */
    public function getContaDv()
    {
        return $this->contaDv;
    }

    /**
     * Define a data de vencimento
     *
     * @param Carbon $dataVencimento
     *
     * @return AbstractPagamento
     */
    public function setDataVencimento(Carbon $dataVencimento)
    {
        $this->dataVencimento = $dataVencimento;

        return $this;
    }

    /**
     * Retorna a data de vencimento
     *
     * @return Carbon
     */
    public function getDataVencimento()
    {
        return $this->dataVencimento;
    }

    /**
     * Define a data de vencimento
     *
     * @param Carbon $dataPagamento
     *
     * @return AbstractPagamento
     */
    public function setDataPagamento(Carbon $dataPagamento)
    {
        $this->dataPagamento = $dataPagamento;

        return $this;
    }

    /**
     * Retorna a data de vencimento
     *
     * @return Carbon
     */
    public function getDataPagamento()
    {
        return $this->dataPagamento;
    }

    /**
     * Retorna a data do juro após
     *
     * @return Carbon
     */
    public function getDataVencimentoApos()
    {
        return $this->getDataVencimento()->copy()->addDays((int) $this->getJurosApos());
    }


    /**
     * Define o campo Número do documento
     *
     * @param int $numeroDocumento
     *
     * @return AbstractPagamento
     */
    public function setNumeroDocumento($numeroDocumento)
    {
        $this->numeroDocumento = $numeroDocumento;

        return $this;
    }

    /**
     * Retorna o campo Número do documento
     *
     * @return int
     */
    public function getNumeroDocumento()
    {
        return $this->numeroDocumento;
    }

    /**
     * Define o número  definido pelo cliente para compor o nosso número
     *
     * @param int $numero
     *
     * @return AbstractPagamento
     */
    public function setNumero($numero)
    {
        $this->numero = $numero;

        return $this;
    }

    /**
     * Retorna o número definido pelo cliente para compor o nosso número
     *
     * @return int
     */
    public function getNumero()
    {
        return $this->numero;
    }

    /**
     * Define o número  definido pelo cliente para controle da remessa
     *
     * @param string $numeroControle
     *
     * @return AbstractPagamento
     */
    public function setNumeroControle($numeroControle)
    {
        $this->numeroControle = $numeroControle;

        return $this;
    }

    /**
     * Retorna o número definido pelo cliente para controle da remessa
     *
     * @return string
     */
    public function getNumeroControle()
    {
        return $this->numeroControle;
    }

    /**
     * Define o campo Uso do banco
     *
     * @param string $usoBanco
     *
     * @return AbstractPagamento
     */
    public function setUsoBanco($usoBanco)
    {
        $this->usoBanco = $usoBanco;

        return $this;
    }

    /**
     * Retorna o campo Uso do banco
     *
     * @return string
     */
    public function getUsoBanco()
    {
        return $this->usoBanco;
    }


    /**
     * Define a data de geração do boleto
     *
     * @param Carbon $dataProcessamento
     *
     * @return AbstractPagamento
     */
    public function setDataProcessamento(Carbon $dataProcessamento)
    {
        $this->dataProcessamento = $dataProcessamento;

        return $this;
    }

    /**
     * Retorna a data de geração do boleto
     *
     * @return Carbon
     */
    public function getDataProcessamento()
    {
        return $this->dataProcessamento;
    }


    /**
     * Define a moeda utilizada pelo boleto
     *
     * @param int $moeda
     *
     * @return AbstractPagamento
     */
    public function setMoeda($moeda)
    {
        $this->moeda = $moeda;

        return $this;
    }

    /**
     * Retorna a moeda utilizada pelo boleto
     *
     * @return int
     */
    public function getMoeda()
    {
        return $this->moeda;
    }

    /**
     * Define o objeto do pagador
     *
     * @param $pagador
     *
     * @return AbstractPagamento
     * @throws ValidationException
     */
    public function setPagador($pagador)
    {
        Util::addPessoa($this->pagador, $pagador);
        $this->pagador->setTipo(Pessoa::TIPO_PAGADOR);

        return $this;
    }

    /**
     * Retorna o objeto do pagador
     *
     * @return PessoaContract
     */
    public function getPagador()
    {
        return $this->pagador;
    }


    /**
     * Define o valor total do boleto (incluindo taxas)
     *
     * @param string $valor
     *
     * @return AbstractPagamento
     */
    public function setValor($valor)
    {
        $this->valor = Util::nFloat($valor, 2, false);

        return $this;
    }

    /**
     * Retorna o valor total do boleto (incluindo taxas)
     *
     * @return string
     */
    public function getValor()
    {
        return Util::nFloat($this->valor, 2, false);
    }


    /**
     * Mostra exception ao erroneamente tentar setar o nosso número
     *
     * @throws ValidationException
     */
    public function setNossoNumero($nossoNumero)
    {
        throw new ValidationException('Não é possível definir o nosso número diretamente. Utilize o método setNumero.');
    }

    /**
     * Retorna o Nosso Número calculado.
     *
     * @return string
     */
    public function getNossoNumero()
    {
        if (empty($this->campoNossoNumero)) {
            return $this->campoNossoNumero = $this->gerarNossoNumero();
        }

        return $this->campoNossoNumero;
    }

    /**
     * Método que valida se o banco tem todos os campos obrigatórios preenchidos
     *
     * @param $messages
     *
     * @return bool
     */
    public function isValid(&$messages)
    {
        foreach ($this->camposObrigatorios as $campo) {
            $test = call_user_func([$this, 'get' . Str::camel($campo)]);
            if ($test === '' || is_null($test)) {
                $messages .= "Campo $campo está em branco";

                return false;
            }
        }
        if (empty($this->campoNossoNumero) && empty($this->gerarNossoNumero())) {
            $messages .= 'Campo nosso número está em branco';

            return false;
        }

        return true;
    }

    /**
     * Retorna o campo Agência/Beneficiário do boleto
     *
     * @return string
     */
    public function getAgenciaCodigoBeneficiario()
    {
        $agencia = rtrim(sprintf('%s-%s', $this->getAgencia(), $this->getAgenciaDv()), '-');
        $conta = rtrim(sprintf('%s-%s', $this->getConta(), $this->getContaDv()), '-');

        return $agencia . ' / ' . $conta;
    }


    /**
     * @return null
     */
    public function getPixChave()
    {
        return $this->pixChave;
    }

    /**
     * @param null $pixChave
     * @return AbstractPagamento
     */
    public function setPixChave($pixChave)
    {
        $this->pixChave = $pixChave;

        return $this;
    }

    /**
     * @return null
     */
    public function getPixChaveTipo()
    {
        return $this->pixChaveTipo;
    }

    /**
     * @param null $pixChaveTipo
     * @return AbstractPagamento
     * @throws ValidationException
     */
    public function setPixChaveTipo($pixChaveTipo)
    {
        if (! in_array($pixChaveTipo, [self::TIPO_CHAVEPIX_CPF, self::TIPO_CHAVEPIX_CNPJ, self::TIPO_CHAVEPIX_CELULAR, self::TIPO_CHAVEPIX_EMAIL, self::TIPO_CHAVEPIX_ALEATORIA])) {
            throw new ValidationException(sprintf('Tipo de chave %s não é válido', $pixChaveTipo));
        }
        $this->pixChaveTipo = $pixChaveTipo;

        return $this;
    }

    /**
     * Define o código de barras do boleto (44 dígitos) ou aceita a linha digitável
     * (47 dígitos) convertendo-a automaticamente.
     *
     * @param string|null $codigoBarras
     * @return AbstractPagamento
     * @throws ValidationException
     */
    public function setCodigoBarras($codigoBarras)
    {
        if ($codigoBarras === null || $codigoBarras === '') {
            $this->codigoBarras = null;

            return $this;
        }

        $codigoBarras = Util::onlyNumbers($codigoBarras);

        if (strlen($codigoBarras) === 47 || strlen($codigoBarras) === 48) {
            $codigoBarras = Util::IPTE2CodigoBarras($codigoBarras);
        }

        if (strlen($codigoBarras) !== 44) {
            throw new ValidationException('Código de barras/linha digitável inválido: deve conter 44 (código de barras) ou 47/48 (linha digitável) dígitos numéricos');
        }

        $this->codigoBarras = $codigoBarras;

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
     * Atalho para setCodigoBarras aceitando também a linha digitável do boleto.
     *
     * @param string|null $linhaDigitavel
     * @return AbstractPagamento
     * @throws ValidationException
     */
    public function setLinhaDigitavel($linhaDigitavel)
    {
        return $this->setCodigoBarras($linhaDigitavel);
    }

    /**
     * @param float|null $valorTitulo
     * @return AbstractPagamento
     */
    public function setValorTitulo($valorTitulo)
    {
        $this->valorTitulo = $valorTitulo === null ? null : Util::nFloat($valorTitulo, 2, false);

        return $this;
    }

    /**
     * Retorna o valor nominal do título (quando não informado, retorna o valor do pagamento).
     *
     * @return string
     */
    public function getValorTitulo()
    {
        return Util::nFloat($this->valorTitulo ?? $this->valor, 2, false);
    }

    /**
     * @param float $desconto
     * @return AbstractPagamento
     */
    public function setDesconto($desconto)
    {
        $this->desconto = Util::nFloat($desconto, 2, false);

        return $this;
    }

    /**
     * @return string
     */
    public function getDesconto()
    {
        return Util::nFloat($this->desconto, 2, false);
    }

    /**
     * @param float $acrescimo
     * @return AbstractPagamento
     */
    public function setAcrescimo($acrescimo)
    {
        $this->acrescimo = Util::nFloat($acrescimo, 2, false);

        return $this;
    }

    /**
     * @return string
     */
    public function getAcrescimo()
    {
        return Util::nFloat($this->acrescimo, 2, false);
    }

    /**
     * @param PessoaContract|array $sacadorAvalista
     * @return AbstractPagamento
     * @throws ValidationException
     */
    public function setSacadorAvalista($sacadorAvalista)
    {
        Util::addPessoa($this->sacadorAvalista, $sacadorAvalista);

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
     * Indica se o pagamento é uma liquidação de boleto (possui código de barras).
     *
     * @return bool
     */
    public function isBoleto()
    {
        return ! empty($this->codigoBarras);
    }

    /**
     * @return bool
     * @throws ValidationException
     */
    public function validarPix()
    {
        if ($this->getPixChave() || $this->getPixChaveTipo()) {
            if (! $this->getPixChave()) {
                throw new ValidationException('Informado tipo de chave de Pix porém não foi informado a chave');
            }
            if (! $this->getPixChaveTipo()) {
                throw new ValidationException('Informado chave de Pix porém não foi informado o tipo de chave');
            }

            switch ($this->getPixChaveTipo()) {
                case self::TIPO_CHAVEPIX_CPF:
                    if (! Util::validarCpf($this->getPixChave())) {
                        throw new ValidationException(sprintf('Chave do tipo CPF é invalida: %s', $this->getPixChave()));
                    }
                    break;
                case self::TIPO_CHAVEPIX_CNPJ:
                    if (! Util::validarCnpj($this->getPixChave())) {
                        throw new ValidationException(sprintf('Chave do tipo CNPJ é invalida: %s', $this->getPixChave()));
                    }
                    break;
                case self::TIPO_CHAVEPIX_EMAIL:
                    if (! filter_var($this->getPixChave(), FILTER_VALIDATE_EMAIL)) {
                        throw new ValidationException(sprintf('Chave do tipo EMAIL é invalida: %s', $this->getPixChave()));
                    }
                    break;
                case self::TIPO_CHAVEPIX_CELULAR:
                    if (! preg_match('/^(\+\d{2}\s?)?[-.\s]?\(?\d{2}\)?[-.\s]?(\d\s?)?\d{4}[-.\s]?\d{4}$/', $this->getPixChave())) {
                        throw new ValidationException(sprintf('Chave do tipo CELULAR é invalida: %s', $this->getPixChave()));
                    }
                    break;
                case self::TIPO_CHAVEPIX_ALEATORIA:
                    if (! preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $this->getPixChave())) {
                        throw new ValidationException(sprintf('Chave do tipo ALEATÓRIA é invalida: %s', $this->getPixChave()));
                    }
                    break;
            }

            return true;
        }

        return false;
    }

    /**
     * Render PDF
     *
     * @param bool $print
     * @param bool $instrucoes
     *
     * @return string
     * @throws ValidationException
     */
    public function renderPDF($print = false, $instrucoes = true)
    {
        if ($this->codigoBanco == 104) {
            $pdf = new PdfCaixa();
        } else {
            $pdf = new Pdf();
        }
        $pdf->addBoleto($this);
        ! $print || $pdf->showPrint();
        $instrucoes || $pdf->hideInstrucoes();

        return $pdf->gerarBoleto('S', null);
    }

    /**
     * Render HTML
     *
     * @param bool $print
     * @param bool $instrucoes
     *
     * @return string
     * @throws Throwable
     */
    public function renderHTML($print = false, $instrucoes = true)
    {
        $html = new Html();
        $html->addBoleto($this);
        ! $print || $html->showPrint();
        $instrucoes || $html->hideInstrucoes();

        return $html->gerarBoleto();
    }

    /**
     * @return $this
     */
    public function copy()
    {
        return clone $this;
    }

    /**
     * On clone clean variables
     */
    public function __clone()
    {
        $this->campoLivre = null;
        $this->campoNossoNumero = null;
        $this->campoLinhaDigitavel = null;
        $this->campoCodigoBarras = null;
    }

    /**
     * Return Boleto Array.
     *
     * @return array
     * @throws ValidationException
     */
    public function toArray()
    {
        $nosso_numero = null;
        try {
            $nosso_numero = $this->getNossoNumero();
        } catch (Exception $e) {
        }

        $this->validarPix();

        return array_merge([
            'beneficiario'    => [
                'nome'              => $this->getBeneficiario()->getNome(),
                'endereco'          => $this->getBeneficiario()->getEndereco(),
                'bairro'            => $this->getBeneficiario()->getBairro(),
                'cep'               => $this->getBeneficiario()->getCep(),
                'uf'                => $this->getBeneficiario()->getUf(),
                'cidade'            => $this->getBeneficiario()->getCidade(),
                'documento'         => $this->getBeneficiario()->getDocumento(),
                'nome_documento'    => $this->getBeneficiario()->getNomeDocumento(),
                'endereco2'         => $this->getBeneficiario()->getCepCidadeUf(),
                'endereco_completo' => $this->getBeneficiario()->getEnderecoCompleto(),
            ],
            'codigo_banco'        => $this->getCodigoBanco(),
            'codigo_banco_com_dv' => $this->getCodigoBancoComDv(),
            'data_vencimento'     => $this->getDataVencimento(),
            'data_processamento'  => $this->getDataProcessamento(),
            'valor'               => Util::nReal($this->getValor(), 2, false),
            'pagador' => [
                'nome'              => $this->getPagador()->getNome(),
                'endereco'          => $this->getPagador()->getEndereco(),
                'bairro'            => $this->getPagador()->getBairro(),
                'cep'               => $this->getPagador()->getCep(),
                'uf'                => $this->getPagador()->getUf(),
                'cidade'            => $this->getPagador()->getCidade(),
                'documento'         => $this->getPagador()->getDocumento(),
                'nome_documento'    => $this->getPagador()->getNomeDocumento(),
                'endereco2'         => $this->getPagador()->getCepCidadeUf(),
                'endereco_completo' => $this->getPagador()->getEnderecoCompleto(),
            ],
            'numero'                             => $this->getNumero(),
            'numero_documento'                   => $this->getNumeroDocumento(),
            'numero_controle'                    => $this->getNumeroControle(),
            'agencia_codigo_beneficiario'        => $this->getAgenciaCodigoBeneficiario(),
            'nosso_numero'                       => $nosso_numero,
            'uso_banco'                          => $this->getUsoBanco(),
            'pix_chave'                          => $this->getPixChave(),
            'pix_chave_tipo'                     => $this->getPixChaveTipo(),
        ], $this->variaveis_adicionais);
    }
}
