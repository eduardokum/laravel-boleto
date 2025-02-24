<?php

namespace Eduardokum\LaravelBoleto\Boleto;

use Exception;
use Throwable;
use Carbon\Carbon;
use Illuminate\Support\Str;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Eduardokum\LaravelBoleto\Util;
use chillerlan\QRCode\Data\QRMatrix;
use Eduardokum\LaravelBoleto\MagicTrait;
use chillerlan\QRCode\Output\QROutputInterface;
use Eduardokum\LaravelBoleto\Boleto\Render\Pdf;
use Eduardokum\LaravelBoleto\Boleto\Render\Html;
use Eduardokum\LaravelBoleto\Boleto\Render\PdfCaixa;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto;
use Eduardokum\LaravelBoleto\Exception\ValidationException;
use Eduardokum\LaravelBoleto\Contracts\Pessoa as PessoaContract;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;

/**
 * Class AbstractBoleto
 */
abstract class AbstractBoleto implements BoletoContract
{
    use MagicTrait;

    const SITUACAO_REJEITADO = 'rejeitado';
    const SITUACAO_ABERTO = 'aberto';
    const SITUACAO_BAIXADO = 'baixado';
    const SITUACAO_PAGO = 'pago';
    const SITUACAO_PROTESTADO = 'protestado';
    const TIPO_CHAVEPIX_CPF = 'cpf';
    const TIPO_CHAVEPIX_CNPJ = 'cnpj';
    const TIPO_CHAVEPIX_CELULAR = 'celular';
    const TIPO_CHAVEPIX_EMAIL = 'email';
    const TIPO_CHAVEPIX_ALEATORIA = 'aleatoria';
    const QRCODE_ESTILO_QUADRADO = 'square';
    const QRCODE_ESTILO_PONTO = 'dot';

    /**
     * Campos necessários para o boleto
     *
     * @var array
     */
    private $camposObrigatorios = [
        'numero',
        'agencia',
        'conta',
        'carteira',
    ];

    protected $protectedFields = [
        'nossoNumero',
    ];

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
     * Valor total do boleto
     *
     * @var float
     */
    public $valor;

    /**
     * Desconto total do boleto
     *
     * @var float
     */
    public $desconto;

    /**
     * Valor para multa
     *
     * @var float
     */
    public $multa = 0;

    /**
     * Valor para mora juros
     *
     * @var float
     */
    public $juros = 0;

    /**
     * Dias apos vencimento do juros
     *
     * @var int
     */
    public $jurosApos = 0;

    /**
     * Dias para protesto
     *
     * @var int
     */
    public $diasProtesto = 0;

    /**
     * Dias para baixa automática
     *
     * @var int
     */
    public $diasBaixaAutomatica;

    /**
     * Data do documento
     *
     * @var Carbon
     */
    public $dataDocumento;

    /**
     * Data de emissão
     *
     * @var Carbon
     */
    public $dataProcessamento;

    /**
     * Data de vencimento
     *
     * @var Carbon
     */
    public $dataVencimento;

    /**
     * Data de limite de desconto
     *
     * @var Carbon
     */
    public $dataDesconto;

    /**
     * Campo de aceite
     *
     * @var string
     */
    protected $aceite = 'N';

    /**
     * Espécie do documento, geralmente DM (Duplicata Mercantil)
     *
     * @var string
     */
    protected $especieDoc = 'DM';

    /**
     * Espécie do documento, coódigo para remessa
     *
     * @var array
     */
    protected $especiesCodigo = [];

    /**
     * Espécie do documento, coódigo para remessa
     *
     * @var array
     */
    protected $especiesCodigo240 = [];

    /**
     * Espécie do documento, coódigo para remessa
     *
     * @var array
     */
    protected $especiesCodigo400 = [];

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
     * Chave da nfe para cnab de 444 posições
     *
     * @var string
     */
    public $chaveNfe;

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
     * Modalidade de cobrança do cliente, geralmente Cobrança Simples ou Registrada
     *
     * @var string
     */
    protected $carteira;

    /**
     * Define as carteiras disponíveis para cada banco
     *
     * @var array|bool
     */
    protected $carteiras = [];

    /**
     * Define as carteiras disponíveis para cada banco
     *
     * @var array
     */
    protected $carteirasNomes = [];

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
     * Entidade sacadora avalista
     *
     * @var PessoaContract
     */
    public $sacadorAvalista;

    /**
     * Array com as linhas do demonstrativo (descrição do pagamento)
     *
     * @var array
     */
    protected $descricaoDemonstrativo;

    /**
     * Linha de local de pagamento
     *
     * @var string
     */
    protected $localPagamento = 'Pagável em qualquer agência bancária até o vencimento.';

    /**
     * Array com as linhas de instruções
     *
     * @var array
     */
    protected $instrucoes = ['Pagar até a data do vencimento.'];

    /**
     * Array com as linhas de instruções de impressão
     *
     * @var array
     */
    protected $instrucoes_impressao = [];

    /**
     * Localização do logotipo do banco, referente ao diretório de imagens
     *
     * @var string
     */
    protected $logo;

    /**
     * Variáveis adicionais.
     *
     * @var array
     */
    public $variaveis_adicionais = [];

    /**
     * Cache do campo livre para evitar processamento desnecessário.
     *
     * @var string
     */
    protected $campoLivre;

    /**
     * Cache do nosso número para evitar processamento desnecessário.
     *
     * @var string
     */
    protected $campoNossoNumero;

    /**
     * Cache da linha digitável para evitar processamento desnecessário.
     *
     * @var string
     */
    protected $campoLinhaDigitavel;

    /**
     * Cache do código de barras para evitar processamento desnecessário.
     *
     * @var string
     */
    protected $campoCodigoBarras;

    /**
     * Status do boleto, se vai criar alterar ou baixa no banco.
     *
     * @var int
     */
    public $status = BoletoContract::STATUS_REGISTRO;

    /**
     * @var int
     */
    private $status_custom = null;

    /**
     * Mostrar o endereço do beneficiário abaixo da razão e CNPJ na ficha de compensação
     *
     * @var bool
     */
    protected $mostrarEnderecoFichaCompensacao = false;

    /**
     * Situação do boleto no banco, pago aberto protestado...
     *
     * @var string
     */
    public $situacao;

    /**
     * Data da situação
     *
     * @var Carbon
     */
    public $dataSituacao;

    /**
     * Valor Recebido
     */
    public $valorRecebido;

    /**
     * @var string
     */
    private $qrCodeStyle = self::QRCODE_ESTILO_QUADRADO;

    /**
     * Recebe a imagem em base 64 do QR Code do PIX
     *
     * @var ?string
     */
    private $pixQrCode = null;

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
     * AbstractBoleto constructor.
     *
     * @param array $params
     */
    public function __construct($params = [])
    {
        Util::fillClass($this, $params);
        // Marca a data de emissão para hoje, caso não especificada
        if (! $this->getDataDocumento()) {
            $this->setDataDocumento(new Carbon());
        }
        // Marca a data de processamento para hoje, caso não especificada
        if (! $this->getDataProcessamento()) {
            $this->setDataProcessamento(new Carbon());
        }
        // Marca a data de vencimento para daqui a 5 dias, caso não especificada
        if (! $this->getDataVencimento()) {
            $this->setDataVencimento(new Carbon(date('Y-m-d', strtotime('+5 days'))));
        }
        // Marca a data de desconto
        if (! $this->getDataDesconto()) {
            $this->setDataDesconto($this->getDataVencimento());
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
     * @return AbstractBoleto
     * @throws ValidationException
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
     * @return AbstractBoleto
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
     * @return AbstractBoleto
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
     * Define o código da carteira (Com ou sem registro)
     *
     * @param string $carteira
     *
     * @return AbstractBoleto
     * @throws ValidationException
     */
    public function setCarteira($carteira)
    {
        if ($this->getCarteiras() !== false && ! in_array($carteira, $this->getCarteiras())) {
            throw new ValidationException('Carteira não disponível!');
        }
        $this->carteira = $carteira;

        return $this;
    }

    /**
     * Retorna o código da carteira (Com ou sem registro)
     *
     * @return string
     */
    public function getCarteira()
    {
        return $this->carteira;
    }

    /**
     * Retorna as carteiras disponíveis para este banco
     *
     * @return array|bool
     */
    public function getCarteiras()
    {
        return $this->carteiras;
    }

    /**
     * Define a entidade beneficiario
     *
     * @param $beneficiario
     *
     * @return AbstractBoleto
     * @throws ValidationException
     */
    public function setBeneficiario($beneficiario)
    {
        Util::addPessoa($this->beneficiario, $beneficiario);

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
     * @return AbstractBoleto
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
     * @return AbstractBoleto
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
     * @return AbstractBoleto
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
     * @return AbstractBoleto
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
     * @return AbstractBoleto
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
     * Define a data de limite de desconto
     *
     * @param Carbon $dataDesconto
     *
     * @return AbstractBoleto
     */
    public function setDataDesconto(Carbon $dataDesconto)
    {
        $this->dataDesconto = $dataDesconto;

        return $this;
    }

    /**
     * Retorna a data de limite de desconto
     *
     * @return Carbon
     */
    public function getDataDesconto()
    {
        return $this->dataDesconto;
    }

    /**
     * Define a data do documento
     *
     * @param Carbon $dataDocumento
     *
     * @return AbstractBoleto
     */
    public function setDataDocumento(Carbon $dataDocumento)
    {
        $this->dataDocumento = $dataDocumento;

        return $this;
    }

    /**
     * Retorna a data do documento
     *
     * @return Carbon
     */
    public function getDataDocumento()
    {
        return $this->dataDocumento;
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
     * Define o campo aceite
     *
     * @param string $aceite
     *
     * @return AbstractBoleto
     */
    public function setAceite($aceite)
    {
        $this->aceite = $aceite;

        return $this;
    }

    /**
     * Retorna o campo aceite
     *
     * @return string
     */
    public function getAceite()
    {
        return is_numeric($this->aceite) ? ($this->aceite ? 'A' : 'N') : $this->aceite;
    }

    /**
     * Define o campo Espécie Doc, geralmente DM (Duplicata Mercantil)
     *
     * @param string $especieDoc
     *
     * @return AbstractBoleto
     */
    public function setEspecieDoc($especieDoc)
    {
        $this->especieDoc = $especieDoc;

        return $this;
    }

    /**
     * Retorna o campo Espécie Doc, geralmente DM (Duplicata Mercantil)
     *
     * @return string
     */
    public function getEspecieDoc()
    {
        return $this->especieDoc;
    }

    /**
     * Retorna o código da Espécie Doc
     *
     * @param int $default
     * @param int $tipo
     *
     * @return string
     */
    public function getEspecieDocCodigo($default = 99, $tipo = 240)
    {
        if (! empty($this->especiesCodigo240) && $tipo == 240) {
            $especie = $this->especiesCodigo240;
        } elseif (! empty($this->especiesCodigo400) && $tipo == 400) {
            $especie = $this->especiesCodigo400;
        } else {
            $especie = $this->especiesCodigo;
        }

        return key_exists(strtoupper($this->especieDoc), $especie)
            ? $especie[strtoupper($this->getEspecieDoc())]
            : $default;
    }

    /**
     * Define o campo Número do documento
     *
     * @param int $numeroDocumento
     *
     * @return AbstractBoleto
     */
    public function setNumeroDocumento($numeroDocumento)
    {
        $this->numeroDocumento = $numeroDocumento;

        return $this;
    }

    /**
     * Retorna o campo Número do documento
     *
     * @return string
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
     * @return AbstractBoleto
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
     * @return AbstractBoleto
     */
    public function setNumeroControle($numeroControle)
    {
        $this->numeroControle = $numeroControle;

        return $this;
    }

    /**
     * Retorna o número definido pelo cliente para controle da remessa
     *
     * @return int
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
     * @return AbstractBoleto
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
     * Define o campo Chave da nfe para cnab de 444 posições
     *
     * @param string $chaveNfe
     *
     * @return AbstractBoleto
     * @throws ValidationException
     */
    public function setChaveNfe($chaveNfe)
    {
        $chaveNfe = Util::onlyNumbers($chaveNfe);

        if (strlen($chaveNfe) != 44 && ! empty($chaveNfe)) {
            throw new ValidationException('Chave de nfe não possui 44 posições');
        }

        $this->chaveNfe = $chaveNfe;

        return $this;
    }

    /**
     * Retorna o campo Chave da nfe
     *
     * @return string
     */
    public function getChaveNfe()
    {
        if (strlen($this->chaveNfe) != 44) {
            return null;
        }

        return $this->chaveNfe;
    }

    /**
     * Define a data de geração do boleto
     *
     * @param Carbon $dataProcessamento
     *
     * @return AbstractBoleto
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
     * Adiciona uma instrução (máximo 5)
     *
     * @param string $instrucao
     *
     * @return AbstractBoleto
     * @throws ValidationException
     */
    public function addInstrucao($instrucao)
    {
        if (count($this->getInstrucoes()) > 8) {
            throw new ValidationException('Atingido o máximo de 5 instruções.');
        }
        array_push($this->instrucoes, $instrucao);

        return $this;
    }

    /**
     * Define um array com instruções (máximo 8) para pagamento
     *
     * @param array $instrucoes
     *
     * @return AbstractBoleto
     * @throws ValidationException
     */
    public function setInstrucoes(array $instrucoes)
    {
        if (count($instrucoes) > 8) {
            throw new ValidationException('Máximo de 8 instruções.');
        }
        $this->instrucoes = $instrucoes;

        return $this;
    }

    /**
     * Retorna um array com instruções (máximo 8) para pagamento
     *
     * @return array
     */
    public function getInstrucoes()
    {
        return array_slice((array) $this->instrucoes + [null, null, null, null, null, null, null, null], 0, 8);
    }

    /**
     * Define um array com instruções (máximo 5) para impressao
     *
     * @param array $instrucoes_impressao
     *
     * @return AbstractBoleto
     * @throws ValidationException
     */
    public function setInstrucoesImpressao(array $instrucoes_impressao)
    {
        if (count($instrucoes_impressao) > 5) {
            throw new ValidationException('Máximo de 5 instruções.');
        }
        $this->instrucoes_impressao = $instrucoes_impressao;

        return $this;
    }

    /**
     * Retorna um array com instruções (máximo 5) para impressão
     *
     * @return array
     */
    public function getInstrucoesImpressao()
    {
        if (! empty($this->instrucoes_impressao)) {
            return array_slice((array) $this->instrucoes_impressao + [null, null, null, null, null], 0, 5);
        } else {
            return [];
        }
    }

    /**
     * Adiciona um demonstrativo (máximo 5)
     *
     * @param string $descricaoDemonstrativo
     *
     * @return AbstractBoleto
     * @throws ValidationException
     */
    public function addDescricaoDemonstrativo($descricaoDemonstrativo)
    {
        if (count($this->getDescricaoDemonstrativo()) > 5) {
            throw new ValidationException('Atingido o máximo de 5 demonstrativos.');
        }
        array_push($this->descricaoDemonstrativo, $descricaoDemonstrativo);

        return $this;
    }

    /**
     * Define um array com a descrição do demonstrativo (máximo 5)
     *
     * @param array $descricaoDemonstrativo
     *
     * @return AbstractBoleto
     * @throws ValidationException
     */
    public function setDescricaoDemonstrativo(array $descricaoDemonstrativo)
    {
        if (count($descricaoDemonstrativo) > 5) {
            throw new ValidationException('Máximo de 5 demonstrativos.');
        }
        $this->descricaoDemonstrativo = $descricaoDemonstrativo;

        return $this;
    }

    /**
     * Retorna um array com a descrição do demonstrativo (máximo 5)
     *
     * @return array
     */
    public function getDescricaoDemonstrativo()
    {
        return array_slice((array) $this->descricaoDemonstrativo + [null, null, null, null, null], 0, 5);
    }

    /**
     * Define o local de pagamento do boleto
     *
     * @param string $localPagamento
     *
     * @return AbstractBoleto
     */
    public function setLocalPagamento($localPagamento)
    {
        $this->localPagamento = $localPagamento;

        return $this;
    }

    /**
     * Retorna o local de pagamento do boleto
     *
     * @return string
     */
    public function getLocalPagamento()
    {
        return $this->localPagamento;
    }

    /**
     * Define a moeda utilizada pelo boleto
     *
     * @param int $moeda
     *
     * @return AbstractBoleto
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
     * @return AbstractBoleto
     * @throws ValidationException
     */
    public function setPagador($pagador)
    {
        Util::addPessoa($this->pagador, $pagador);

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
     * Define o objeto sacador avalista do boleto
     *
     * @param $sacadorAvalista
     *
     * @return AbstractBoleto
     * @throws ValidationException
     */
    public function setSacadorAvalista($sacadorAvalista)
    {
        Util::addPessoa($this->sacadorAvalista, $sacadorAvalista);

        return $this;
    }

    /**
     * Retorna o objeto sacador avalista do boleto
     *
     * @return PessoaContract
     */
    public function getSacadorAvalista()
    {
        return $this->sacadorAvalista;
    }

    /**
     * Define o valor total do boleto (incluindo taxas)
     *
     * @param string $valor
     *
     * @return AbstractBoleto
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
     * Define o desconto total do boleto (incluindo taxas)
     *
     * @param string $desconto
     *
     * @return AbstractBoleto
     */
    public function setDesconto($desconto)
    {
        $this->desconto = Util::nFloat($desconto, 2, false);

        return $this;
    }

    /**
     * Retorna o desconto total do boleto (incluindo taxas)
     *
     * @return string
     */
    public function getDesconto()
    {
        return Util::nFloat($this->desconto, 2, false);
    }

    /**
     * Seta a % de multa
     *
     * @param float $multa
     *
     * @return AbstractBoleto
     */
    public function setMulta($multa)
    {
        $this->multa = (float) ($multa > 0.00 ? $multa : 0.00);

        return $this;
    }

    /**
     * Retorna % de multa
     *
     * @return float
     */
    public function getMulta()
    {
        return $this->multa;
    }

    /**
     * Seta a % de juros
     *
     * @param float $juros
     *
     * @return AbstractBoleto
     */
    public function setJuros($juros)
    {
        $this->juros = (float) ($juros > 0.00 ? $juros : 0.00);

        return $this;
    }

    /**
     * Retorna % juros
     *
     * @return float
     */
    public function getJuros()
    {
        return $this->juros;
    }

    /**
     * Retorna valor mora diária
     *
     * @return float
     */
    public function getMoraDia()
    {
        if ($this->getJuros() <= 0) {
            return 0;
        }

        return Util::percent($this->getValor(), $this->getJuros()) / 30;
    }

    /**
     * Seta a quantidade de dias apos o vencimento que cobra o juros
     *
     * @param int $jurosApos
     *
     * @return AbstractBoleto
     */
    public function setJurosApos($jurosApos)
    {
        $jurosApos = (int) $jurosApos;
        $this->jurosApos = $jurosApos > 0 ? $jurosApos : 0;

        return $this;
    }

    /**
     * Retorna a quantidade de dias apos o vencimento que cobrar a juros
     *
     * @return int
     */
    public function getJurosApos()
    {
        return $this->jurosApos ? $this->jurosApos : false;
    }

    /**
     * Seta dias para protesto
     *
     * @param int $diasProtesto
     *
     * @return AbstractBoleto
     * @throws ValidationException
     */
    public function setDiasProtesto($diasProtesto)
    {
        $diasProtesto = (int) $diasProtesto;
        $this->diasProtesto = $diasProtesto > 0 ? $diasProtesto : 0;

        if (! empty($diasProtesto) && $this->getDiasBaixaAutomatica() > 0) {
            throw new ValidationException('Você deve usar dias de protesto ou dias de baixa, nunca os 2');
        }

        return $this;
    }

    /**
     * Retorna os diasProtesto
     *
     * @param int $default
     *
     * @return int
     */
    public function getDiasProtesto($default = 0)
    {
        return $this->diasProtesto > 0 ? $this->diasProtesto : $default;
    }

    /**
     * Seta os dias para baixa automática
     *
     * @param int $baixaAutomatica
     * @throws ValidationException
     */
    public function setDiasBaixaAutomatica($baixaAutomatica)
    {
        $exception = sprintf('O banco %s não suporta baixa automática, pode usar também: setDiasProtesto(%s)', basename(get_class($this)), $baixaAutomatica);
        throw new ValidationException($exception);
    }

    /**
     * Retorna os dias de Baixa Automática
     *
     * @param int $default
     *
     * @return int
     */
    public function getDiasBaixaAutomatica($default = 0)
    {
        //Caso não tenha valor definido de dias pra protesto setar 60 dias como valor padrão para baixa automatica.
        //O valor padrão só será utilizado caso não haja nenhum valor definido para baixaAutomatica
        if (empty($this->getDiasProtesto())) {
            $default = (empty($default) ? 60 : $default);
        }

        return $this->diasBaixaAutomatica > 0 ? $this->diasBaixaAutomatica : $default;
    }

    /**
     * Define a localização do logotipo
     *
     * @param string $logo
     *
     * @return AbstractBoleto
     */
    public function setLogo($logo)
    {
        $this->logo = $logo;

        return $this;
    }

    /**
     * Retorna a localização do logotipo
     *
     * @return string
     */
    public function getLogo()
    {
        return $this->logo ? $this->logo : 'http://dummyimage.com/300x70/f5/0.png&text=Sem+Logo';
    }

    /**
     * Retorna o logotipo em Base64, pronto para ser inserido na página
     *
     * @return string
     */
    public function getLogoBase64()
    {
        return 'data:image/' . pathinfo($this->getLogo(), PATHINFO_EXTENSION) .
            ';base64,' . base64_encode(file_get_contents($this->getLogo()));
    }

    /**
     * Retorna a localização do logotipo do banco relativo à pasta de imagens
     *
     * @return string
     */
    public function getLogoBanco()
    {
        return realpath(__DIR__ . '/../../logos/' . $this->getCodigoBanco() . '.png');
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Marca o boleto para ser alterado no banco
     *
     * @return AbstractBoleto
     */
    public function alterarBoleto()
    {
        $this->status = BoletoContract::STATUS_ALTERACAO;

        return $this;
    }

    /**
     * Marca o boleto para alterar data vecimento no banco
     *
     * @return AbstractBoleto
     */
    public function alterarDataDeVencimento()
    {
        $this->status = BoletoContract::STATUS_ALTERACAO_DATA;

        return $this;
    }

    /**
     * Comandar instrução custom
     *
     * @param $instrucao
     *
     * @return AbstractBoleto
     */
    public function comandarInstrucao($instrucao)
    {
        $this->status = BoletoContract::STATUS_CUSTOM;
        $this->status_custom = $instrucao;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getComando()
    {
        return $this->status == Boleto::STATUS_CUSTOM ? $this->status_custom : null;
    }

    /**
     * Marca o boleto para ser baixado no banco
     *
     * @return AbstractBoleto
     */
    public function baixarBoleto()
    {
        $this->status = BoletoContract::STATUS_BAIXA;

        return $this;
    }

    /**
     * Retorna o logotipo do banco em Base64, pronto para ser inserido na página
     *
     * @return string
     */
    public function getLogoBancoBase64()
    {
        return 'data:image/' . pathinfo($this->getLogoBanco(), PATHINFO_EXTENSION) . ';base64,' . base64_encode(file_get_contents($this->getLogoBanco()));
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
     * Método que retorna o nosso número usado no boleto. Alguns bancos possuem algumas diferenças.
     *
     * @return string
     */
    public function getNossoNumeroBoleto()
    {
        return $this->getNossoNumero();
    }

    /**
     * Método onde o Boleto deverá gerar o Nosso Número.
     *
     * @return string
     */
    abstract protected function gerarNossoNumero();

    /**
     * Método onde qualquer boleto deve extender para gerar o código da posição de 20 a 44
     *
     * @return string
     */
    abstract protected function getCampoLivre();

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
     * Retorna o nome da carteira para impressão no boleto
     *
     * Caso o nome da carteira a ser impresso no boleto seja diferente do número
     * Então crie uma variável na classe do banco correspondente $carteirasNomes
     * sendo uma array cujos índices sejam os números das carteiras e os valores
     * seus respectivos nomes
     *
     * @return string
     */
    public function getCarteiraNome()
    {
        return isset($this->carteirasNomes[$this->getCarteira()]) ? $this->carteirasNomes[$this->getCarteira()] : $this->getCarteira();
    }

    /**
     * Retorna o código de barras
     *
     * @return string
     * @throws ValidationException
     */
    public function getCodigoBarras()
    {
        if (! empty($this->campoCodigoBarras)) {
            return $this->campoCodigoBarras;
        }

        if (! $this->isValid($messages)) {
            throw new ValidationException('Campos requeridos pelo banco, aparentam estar ausentes ' . $messages);
        }

        $codigo = Util::numberFormatGeral($this->getCodigoBanco(), 3)
            . $this->getMoeda()
            . Util::fatorVencimento($this->getDataVencimento())
            . Util::numberFormatGeral($this->getValor(), 10)
            . $this->getCampoLivre();

        $resto = Util::modulo11($codigo, 2, 9, 0);
        $dv = (in_array($resto, [0, 10, 11])) ? 1 : $resto;

        return $this->campoCodigoBarras = substr($codigo, 0, 4) . $dv . substr($codigo, 4);
    }

    /**
     * Retorna o código do banco com o dígito verificador
     *
     * @return string
     */
    public function getCodigoBancoComDv()
    {
        $codigoBanco = $this->getCodigoBanco();

        $semX = [BoletoContract::COD_BANCO_CEF, BoletoContract::COD_BANCO_AILOS];
        $x10 = in_array($codigoBanco, $semX) ? 0 : 'X';

        return $codigoBanco . '-' . Util::modulo11($codigoBanco, 2, 9, 0, $x10);
    }

    /**
     * Retorna a linha digitável do boleto
     *
     * @return string
     * @throws ValidationException
     */
    public function getLinhaDigitavel()
    {
        if (! empty($this->campoLinhaDigitavel)) {
            return $this->campoLinhaDigitavel;
        }

        return $this->campoLinhaDigitavel = Util::formatLinhaDigitavel(Util::codigoBarras2LinhaDigitavel($this->getCodigoBarras()));
    }

    /**
     * Retorna se a segunda linha contendo o endereço do beneficiário deve ser exibida na ficha de compensação
     *
     * @return bool
     */
    public function getMostrarEnderecoFichaCompensacao()
    {
        return $this->mostrarEnderecoFichaCompensacao;
    }

    /**
     * Seta se a segunda linha contendo o endereço do beneficiário deve ser exibida na ficha de compensação
     *
     * @param bool $mostrarEnderecoFichaCompensacao
     */
    public function setMostrarEnderecoFichaCompensacao($mostrarEnderecoFichaCompensacao)
    {
        $this->mostrarEnderecoFichaCompensacao = $mostrarEnderecoFichaCompensacao;
    }

    /**
     * @return string
     */
    public function getSituacao()
    {
        return $this->situacao;
    }

    /**
     * @param string $situacao
     *
     * @return AbstractBoleto
     */
    public function setSituacao($situacao)
    {
        $this->situacao = $situacao;

        return $this;
    }

    /**
     * @return Carbon
     */
    public function getDataSituacao()
    {
        return $this->dataSituacao;
    }

    /**
     * @param Carbon $dataSituacao
     *
     * @return AbstractBoleto
     */
    public function setDataSituacao($dataSituacao)
    {
        $this->dataSituacao = $dataSituacao;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getValorRecebido()
    {
        return $this->valorRecebido;
    }

    /**
     * @param mixed $valorRecebido
     *
     * @return AbstractBoleto
     */
    public function setValorRecebido($valorRecebido)
    {
        $this->valorRecebido = $valorRecebido;

        return $this;
    }

    /**
     * @return ?string
     */
    public function getPixQrCode()
    {
        return $this->pixQrCode;
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
     * @return AbstractBoleto
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
     * @return AbstractBoleto
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
     * @return string
     */
    public function getQrCodeStyle()
    {
        return $this->qrCodeStyle;
    }

    /**
     * @param string $qrCodeStyle
     * @return AbstractBoleto
     * @throws ValidationException
     */
    public function setQrCodeStyle($qrCodeStyle)
    {
        if (! in_array($qrCodeStyle, [self::QRCODE_ESTILO_QUADRADO, self::QRCODE_ESTILO_PONTO])) {
            throw new ValidationException(sprintf('Estilo QRCODE %s não é válido', $qrCodeStyle));
        }

        $this->qrCodeStyle = $qrCodeStyle;

        return $this;
    }

    /**
     * @return ?string
     */
    public function getPixQrCodeBase64()
    {
        if ($this->getPixQrCode() == null) {
            return null;
        }
        if (Util::isBase64($this->getPixQrCode())) {
            return 'data://text/plain;base64,' . $this->getPixQrCode();
        }

        if (Str::startsWith($this->getPixQrCode(), 'data:')) {
            return $this->getPixQrCode();
        }

        $options = new QROptions;

        if (defined('\chillerlan\QRCode\QRCode::OUTPUT_IMAGE_PNG')) {
            $options->outputType = QRCode::OUTPUT_IMAGE_PNG;
            $options->eccLevel = QRCode::ECC_L;
        } else {
            $options->outputType = QROutputInterface::GDIMAGE_PNG;
            $options->addQuietzone = true;
        }

        $options->scale = 20;
        $options->quietzoneSize = 1;
        $options->drawLightModules = false;

        if ($this->getQrCodeStyle() == self::QRCODE_ESTILO_PONTO) {
            $options->drawCircularModules = true;
            $options->circleRadius = .5;
            $options->keepAsSquare = [
                QRMatrix::M_FINDER_DOT,
                QRMatrix::M_FINDER_DARK,
            ];
        }
        $qrCode = new QRCode($options);

        return $qrCode->render($this->getPixQrCode());
    }

    /**
     * @param string $pixQrCode
     */
    public function setPixQrCode($pixQrCode)
    {
        $this->pixQrCode = $pixQrCode;
    }

    /**
     * @param $situacao
     *
     * @return bool
     */
    public function isSituacao($situacao)
    {
        return $this->situacao == $situacao;
    }

    /**
     * @return bool
     */
    public function isRejeitado()
    {
        return $this->isSituacao(self::SITUACAO_REJEITADO);
    }

    /**
     * @return bool
     */
    public function isAberto()
    {
        return $this->isSituacao(self::SITUACAO_ABERTO);
    }

    /**
     * @return bool
     */
    public function isBaixado()
    {
        return $this->isSituacao(self::SITUACAO_BAIXADO);
    }

    /**
     * @return bool
     */
    public function isPago()
    {
        return $this->isSituacao(self::SITUACAO_PAGO);
    }

    /**
     * @return bool
     */
    public function isProtestado()
    {
        return $this->isSituacao(self::SITUACAO_PROTESTADO);
    }

    /**
     * @return bool
     */
    public function imprimeBoleto()
    {
        return true;
    }

    /**
     * @param $id
     * @return string
     * @throws ValidationException
     */
    protected function validateId($id)
    {
        return $id;
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
            if (! $this->getID()) {
                throw new ValidationException('ID necessita ser informado para geração da cobrança');
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
     * @return string|null
     * @throws ValidationException
     */
    public function gerarPixCopiaECola()
    {
        if ($this->getPixChave() && $this->getValor() && $this->getID() && $this->getBeneficiario()) {
            $this->setPixQrCode(Util::gerarPixCopiaECola($this->getPixChave(), $this->getValor(), $this->getID(), $this->getBeneficiario()));
        }

        return $this->getPixQrCode();
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
        $nosso_numero = $nosso_numero_boleto = $linha_digitavel = $codigo_barras = null;
        try {
            $nosso_numero = $this->getNossoNumero();
            $nosso_numero_boleto = $this->getNossoNumeroBoleto();
            $linha_digitavel = $this->getLinhaDigitavel();
            $codigo_barras = $this->getCodigoBarras();
        } catch (Exception $e) {
        }

        $this->validarPix();

        return array_merge([
            'linha_digitavel' => $linha_digitavel,
            'codigo_barras'   => $codigo_barras,
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
            'logo_base64'         => $this->getLogoBase64(),
            'logo'                => $this->getLogo(),
            'logo_banco_base64'   => $this->getLogoBancoBase64(),
            'logo_banco'          => $this->getLogoBanco(),
            'codigo_banco'        => $this->getCodigoBanco(),
            'codigo_banco_com_dv' => $this->getCodigoBancoComDv(),
            'especie'             => 'R$',
            'data_vencimento'     => $this->getDataVencimento(),
            'data_processamento'  => $this->getDataProcessamento(),
            'data_documento'      => $this->getDataDocumento(),
            'data_desconto'       => $this->getDataDesconto(),
            'valor'               => Util::nReal($this->getValor(), 2, false),
            'desconto'            => Util::nReal($this->getDesconto(), 2, false),
            'multa'               => Util::nReal($this->getMulta(), 2, false),
            'juros'               => Util::nReal($this->getJuros(), 2, false),
            'juros_apos'          => $this->getJurosApos(),
            'dias_protesto'       => $this->getDiasProtesto(),
            'sacador_avalista'    => $this->getSacadorAvalista()
                ? [
                    'nome'              => $this->getSacadorAvalista()->getNome(),
                    'endereco'          => $this->getSacadorAvalista()->getEndereco(),
                    'bairro'            => $this->getSacadorAvalista()->getBairro(),
                    'cep'               => $this->getSacadorAvalista()->getCep(),
                    'uf'                => $this->getSacadorAvalista()->getUf(),
                    'cidade'            => $this->getSacadorAvalista()->getCidade(),
                    'documento'         => $this->getSacadorAvalista()->getDocumento(),
                    'nome_documento'    => $this->getSacadorAvalista()->getNomeDocumento(),
                    'endereco2'         => $this->getSacadorAvalista()->getCepCidadeUf(),
                    'endereco_completo' => $this->getSacadorAvalista()->getEnderecoCompleto(),
                ]
                : [],
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
            'demonstrativo'                      => $this->getDescricaoDemonstrativo(),
            'instrucoes'                         => $this->getInstrucoes(),
            'instrucoes_impressao'               => $this->getInstrucoesImpressao(),
            'local_pagamento'                    => $this->getLocalPagamento(),
            'numero'                             => $this->getNumero(),
            'numero_documento'                   => $this->getNumeroDocumento(),
            'numero_controle'                    => $this->getNumeroControle(),
            'agencia_codigo_beneficiario'        => $this->getAgenciaCodigoBeneficiario(),
            'nosso_numero'                       => $nosso_numero,
            'nosso_numero_boleto'                => $nosso_numero_boleto,
            'especie_doc'                        => $this->getEspecieDoc(),
            'especie_doc_cod'                    => $this->getEspecieDocCodigo(),
            'aceite'                             => $this->getAceite(),
            'carteira'                           => $this->getCarteira(),
            'carteira_nome'                      => $this->getCarteiraNome(),
            'uso_banco'                          => $this->getUsoBanco(),
            'status'                             => $this->getStatus(),
            'mostrar_endereco_ficha_compensacao' => $this->getMostrarEnderecoFichaCompensacao(),
            'pix_chave'                          => $this->getPixChave(),
            'pix_chave_tipo'                     => $this->getPixChaveTipo(),
            'pix_qrcode'                         => $this->getPixQrCode(),
            'pix_qrcode_image'                   => $this->getPixQrCodeBase64(),
        ], $this->variaveis_adicionais);
    }
}
