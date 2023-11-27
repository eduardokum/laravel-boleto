<?php

namespace Eduardokum\LaravelBoleto\Cnab\Remessa;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Eduardokum\LaravelBoleto\Util;
use Illuminate\Support\Collection;
use Eduardokum\LaravelBoleto\Exception\ValidationException;
use Eduardokum\LaravelBoleto\Contracts\Pessoa as PessoaContract;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;

abstract class AbstractRemessa
{
    const HEADER = 'header';
    const HEADER_LOTE = 'header_lote';
    const DETALHE = 'detalhe';
    const TRAILER_LOTE = 'trailer_lote';
    const TRAILER = 'trailer';

    protected $tamanho_linha = false;

    /**
     * Campos necessários para a remessa
     *
     * @var array
     */
    private $camposObrigatorios = [
        'carteira',
        'agencia',
        'conta',
        'beneficiario',
    ];

    /**
     * @var array
     */
    protected $boletos = [];

    /**
     * Código do banco
     *
     * @var string
     */
    protected $codigoBanco;

    /**
     * Contagem dos registros Detalhes
     *
     * @var int
     */
    protected $iRegistros = 0;

    /**
     * Array contendo o cnab.
     *
     * @var array
     */
    protected $aRegistros = [
        self::HEADER  => [],
        self::DETALHE => [],
        self::TRAILER => [],
    ];

    /**
     * Variável com ponteiro para linha que esta sendo editada.
     *
     * @var
     */
    protected $atual;

    /**
     * Caractere de fim de linha
     *
     * @var string
     */
    protected $fimLinha = "\n";

    /**
     * Caractere de fim de arquivo
     *
     * @var null
     */
    protected $fimArquivo = null;

    /**
     * ID do arquivo remessa, sequencial.
     *
     * @var
     */
    protected $idremessa;

    /**
     * A data que será informada no header da remessa
     *
     * @var Carbon;
     */
    protected $dataRemessa = null;

    /**
     * Agência
     *
     * @var int
     */
    protected $agencia;

    /**
     * Dígito da conta
     *
     * @var int
     */
    protected $agenciaDv;

    /**
     * Conta
     *
     * @var int
     */
    protected $conta;

    /**
     * Dígito da conta
     *
     * @var int
     */
    protected $contaDv;

    /**
     * Carteira de cobrança.
     *
     * @var
     */
    protected $carteira;

    /**
     * Define as carteiras disponíveis para cada banco
     *
     * @var array|bool
     */
    protected $carteiras = [];

    /**
     * Entidade beneficiária (quem está gerando a remessa)
     *
     * @var PessoaContract
     */
    protected $beneficiario;

    /**
     * Construtor
     *
     * @param array $params Parâmetros iniciais para construção do objeto
     */
    public function __construct($params = [])
    {
        Util::fillClass($this, $params);
    }

    /**
     * @return string
     */
    public function getFimLinha()
    {
        return $this->fimLinha;
    }

    /**
     * Informa a data da remessa a ser gerada
     *
     * @param $data
     */
    public function setDataRemessa($data)
    {
        $this->dataRemessa = $data;
    }

    /**
     * Retorna a data da remessa a ser gerada
     *
     * @param $format
     *
     * @return string;
     */
    public function getDataRemessa($format)
    {
        if (is_null($this->dataRemessa)) {
            return Carbon::now()->format($format);
        }

        return $this->dataRemessa->format($format);
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
     * Retorna o código do banco
     *
     * @return string
     */
    public function getCodigoBanco()
    {
        return $this->codigoBanco;
    }

    /**
     * @return mixed
     */
    public function getIdremessa()
    {
        return $this->idremessa;
    }

    /**
     * @param mixed $idremessa
     *
     * @return AbstractRemessa
     */
    public function setIdremessa($idremessa)
    {
        $this->idremessa = $idremessa;

        return $this;
    }

    /**
     * @return PessoaContract
     */
    public function getBeneficiario()
    {
        return $this->beneficiario;
    }

    /**
     * @param $beneficiario
     *
     * @return AbstractRemessa
     * @throws ValidationException
     */
    public function setBeneficiario($beneficiario)
    {
        Util::addPessoa($this->beneficiario, $beneficiario);

        return $this;
    }

    /**
     * Define a agência
     *
     * @param int $agencia
     *
     * @return AbstractRemessa
     */
    public function setAgencia($agencia)
    {
        $this->agencia = (string) $agencia;

        return $this;
    }

    /**
     * Retorna a agência
     *
     * @return int
     */
    public function getAgencia()
    {
        return $this->agencia;
    }

    /**
     * Define a agência
     *
     * @param int $agenciaDv
     *
     * @return AbstractRemessa
     */
    public function setAgenciaDv($agenciaDv)
    {
        $this->agenciaDv = (string) $agenciaDv;

        return $this;
    }

    /**
     * Retorna a agência
     *
     * @return int
     */
    public function getAgenciaDv()
    {
        return $this->agenciaDv;
    }

    /**
     * Define o número da conta
     *
     * @param int $conta
     *
     * @return AbstractRemessa
     */
    public function setConta($conta)
    {
        $this->conta = (string) $conta;

        return $this;
    }

    /**
     * Retorna o número da conta
     *
     * @return int
     */
    public function getConta()
    {
        return $this->conta;
    }

    /**
     * Define o dígito verificador da conta
     *
     * @param int $contaDv
     *
     * @return AbstractRemessa
     */
    public function setContaDv($contaDv)
    {
        $this->contaDv = substr($contaDv, -1);

        return $this;
    }

    /**
     * Retorna o dígito verificador da conta
     *
     * @return int
     */
    public function getContaDv()
    {
        return $this->contaDv;
    }

    /**
     * Define o código da carteira (Com ou sem registro)
     *
     * @param string $carteira
     *
     * @return AbstractRemessa
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
     * Retorna o código da carteira (Com ou sem registro)
     *
     * @return string
     */
    public function getCarteiraNumero()
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

        return true;
    }

    /**
     * Função para gerar o cabeçalho do arquivo.
     *
     * @return mixed
     */
    abstract protected function header();

    /**
     * Função para adicionar detalhe ao arquivo.
     *
     * @param BoletoContract $boleto
     *
     * @return mixed
     */
    abstract public function addBoleto(BoletoContract $boleto);

    /**
     * Função que gera o trailer (footer) do arquivo.
     *
     * @return mixed
     */
    abstract protected function trailer();

    /**
     * Função para adicionar múltiplos boletos.
     *
     * @param array $boletos
     *
     * @return $this
     */
    public function addBoletos(array $boletos)
    {
        foreach ($boletos as $boleto) {
            $this->addBoleto($boleto);
        }

        return $this;
    }

    /**
     * Função para add valor a linha nas posições informadas.
     *
     * @param int $i
     * @param int $f
     * @param         $value
     *
     * @return array
     * @throws ValidationException
     */
    protected function add($i, $f, $value)
    {
        return Util::adiciona($this->atual, $i, $f, $value);
    }

    /**
     * Retorna o header do arquivo.
     *
     * @return mixed
     */
    protected function getHeader()
    {
        return $this->aRegistros[self::HEADER];
    }

    /**
     * Retorna os detalhes do arquivo
     *
     * @return Collection
     */
    protected function getDetalhes()
    {
        return collect($this->aRegistros[self::DETALHE]);
    }

    /**
     * Retorna o trailer do arquivo.
     *
     * @return mixed
     */
    protected function getTrailer()
    {
        return $this->aRegistros[self::TRAILER];
    }

    /**
     * Valida se a linha esta correta.
     *
     * @param array $a
     * @param int $extendido
     *
     * @return string
     * @throws ValidationException
     */
    protected function valida(array $a, $extendido = 0)
    {
        if ($this->tamanho_linha === false) {
            throw new ValidationException('Classe remessa deve informar o tamanho da linha');
        }

        $a = array_filter($a, 'mb_strlen');
        if (count($a) != $this->tamanho_linha + $extendido) {
            throw new ValidationException(sprintf('$a não possui %s posições, possui: %s', $this->tamanho_linha, count($a)));
        }

        return implode('', $a);
    }

    /**
     * Gera o arquivo, retorna a string.
     *
     * @return string
     * @throws ValidationException
     */
    abstract public function gerar();

    /**
     * Salva o arquivo no path informado
     *
     * @param      $path
     * @param bool $suggestName
     *
     * @return mixed
     * @throws ValidationException
     */
    public function save($path, $suggestName = false)
    {
        $folder = dirname($path);
        if (! is_dir($folder)) {
            mkdir($folder, 0777, true);
        }

        if (! is_writable(dirname($path))) {
            throw new ValidationException('Path ' . $folder . ' não possui permissao de escrita');
        }

        if ($suggestName) {
            $path = rtrim(dirname($path), '/') . '/' . ltrim($this->nomeSugerido(), '/');
        }

        $string = $this->gerar();
        file_put_contents($path, $string);

        return $path;
    }

    /**
     * @return string
     */
    public function nomeSugerido()
    {
        return 'remessa.txt';
    }

    /**
     * Realiza o download da string retornada do método gerar
     *
     * @param null $filename
     *
     * @throws ValidationException
     */
    public function download($filename = null)
    {
        if ($filename === null) {
            $filename = $this->nomeSugerido();
        }
        header('Content-type: text/plain');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo $this->gerar();
    }
}
