<?php
namespace Eduardokum\LaravelBoleto\Cnab\Remessa;

use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;
use Eduardokum\LaravelBoleto\Contracts\Pessoa as PessoaContract;
use Eduardokum\LaravelBoleto\Util;

abstract class AbstractRemessa
{
    const HEADER = 'header';
    const HEADER_LOTE = 'header_lote';
    const DETALHE = 'detalhe';
    const TRAILER_LOTE = 'trailer_lote';
    const TRAILER = 'trailer';

    protected $tamanho_linha = false;

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
        self::HEADER => [],
        self::DETALHE => [],
        self::TRAILER => [],
    ];

    /**
     * Variavel com ponteiro para linha que esta sendo editada.
     *
     * @var
     */
    protected $atual;

    /**
     * Caracter de fim de linha
     *
     * @var string
     */
    protected $fimLinha = "\n";

    /**
     * Caracter de fim de arquivo
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
     * Agência
     *
     * @var int
     */
    protected $agencia;
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
     * @var array
     */
    protected $carteiras = [];

    /**
     * Entidade beneficiario (quem esta gerando a remessa)
     *
     * @var PessoaContract
     */
    protected $beneficiario;

    /**
     * Construtor
     *
     * @param array $params Parâmetros iniciais para construção do objeto
     */
    public function __construct($params = array())
    {
        foreach ($params as $param => $value)
        {
            if (method_exists($this, 'set' . ucwords($param))) {
                $this->{'set' . ucwords($param)}($value);
            }
        }
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
     * @param PessoaContract $beneficiario
     *
     * @return AbstractRemessa
     */
    public function setBeneficiario(PessoaContract $beneficiario)
    {
        $this->beneficiario = $beneficiario;

        return $this;
    }
    /**
     * Define a agência
     *
     * @param  int $agencia
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
     * Define o número da conta
     *
     * @param  int $conta
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
     * @param  int $contaDv
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
     * @param  string $carteira
     * @return AbstractRemessa
     * @throws \Exception
     */
    public function setCarteira($carteira)
    {
        if (!in_array($carteira, $this->getCarteiras())) {
            throw new \Exception("Carteira não disponível!");
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
     * @return array
     */
    public function getCarteiras()
    {
        return $this->carteiras;
    }
    /**
     * Método que valida se o banco tem todos os campos obrigadotorios preenchidos
     *
     * @return boolean
     */
    public function isValid() 
    {
        if ($this->carteira == '' || $this->agencia == '' || $this->conta == '' || !$this->beneficiario instanceof PessoaContract) {
            return false;
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
     * @param BoletoContract $detalhe
     *
     * @return mixed
     */
    abstract public function addBoleto(BoletoContract $detalhe);

    /**
     * Função que gera o trailer (footer) do arquivo.
     *
     * @return mixed
     */
    abstract protected function trailer();

    /**
     * Função que mostra a quantidade de linhas do arquivo.
     *
     * @return int
     */
    protected function getCount()
    {
        return count($this->aRegistros[self::DETALHE]) + 2;
    }

    /**
     * Função para adicionar multiplos boletos.
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
     * @param integer $i
     * @param integer $f
     * @param $value
     *
     * @return array
     * @throws \Exception
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
     * @return \Illuminate\Support\Collection
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
     *
     * @return string
     * @throws \Exception
     */
    protected function valida(array $a) 
    {
        if($this->tamanho_linha === false)
        {
            throw new \Exception('Classe remessa deve informar o tamanho da linha');
        }

        $a = array_filter($a, 'strlen');
        if (count($a) != $this->tamanho_linha) {
            throw new \Exception(sprintf('$a não possui %s posições, possui: %s', $this->tamanho_linha, count($a)));
        }
        return implode('', $a);
    }

    /**
     * Gera o arquivo, retorna a string.
     *
     * @return string
     * @throws \Exception
     */
    public function gerar()
    {
        throw new \Exception('Método não implementado');
    }

    /**
     * Salva o arquivo no path informado
     *
     * @param $path
     *
     * @return mixed
     * @throws \Exception
     */
    public function save($path)
    {
        $folder = dirname($path);
        if (!is_dir($folder)) {
            mkdir($folder, 0777, true);
        }

        if (!is_writable(dirname($path))) {
            throw new \Exception('Path ' . $folder . ' não possui permissao de escrita');
        }

        $string = $this->gerar();
        file_put_contents($path, $string);

        return $path;
    }
}