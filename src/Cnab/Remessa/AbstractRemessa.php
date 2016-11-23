<?php
namespace Eduardokum\LaravelBoleto\Cnab\Remessa;

use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;
use Eduardokum\LaravelBoleto\Contracts\Pessoa as PessoaContract;
use Eduardokum\LaravelBoleto\Util;

abstract class AbstractRemessa
{
    const HEADER = 'header';
    const DETALHE = 'detalhe';
    const TRAILER = 'trailer';

    /**
     * Código do banco
     * @var string
     */
    protected $codigoBanco;

    /**
     * Contagem dos registros Detalhes
     * @var int
     */
    protected $iRegistros = 0;

    /**
     * Array contendo o cnab.
     *
     * @var array
     */
    private $aRegistros = [
        self::HEADER => [],
        self::DETALHE => [],
        self::TRAILER => [],
    ];

    /**
     * Variavel com ponteiro para linha que esta sendo editada.
     * @var
     */
    private $atual;

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
     * @var int
     */
    protected $agencia;
    /**
     * Dígito da agência
     * @var string|int
     */
    protected $agenciaDv;
    /**
     * Conta
     * @var int
     */
    protected $conta;
    /**
     * Dígito da conta
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
     * @var array
     */
    protected $carteiras = [];

    /**
     * Entidade beneficiario (quem esta gerando a remessa)
     * @var PessoaContract
     */
    protected $beneficiario;

    /**
     * Construtor
     *
     * @param array $params Parâmetros iniciais para construção do objeto
     */
    public function  __construct($params = array())
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
     * @return AbstractCnab
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
     * @return AbstractCnab
     */
    public function setBeneficiario(PessoaContract $beneficiario)
    {
        $this->beneficiario = $beneficiario;

        return $this;
    }
    /**
     * Define a agência
     *
     * @param int $agencia
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
     * @return int
     */
    public function getAgencia()
    {
        return $this->agencia;
    }
    /**
     * Define o dígito da agência
     *
     * @param string|int $agenciaDv
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
     * @return string|int
     */
    public function getAgenciaDv()
    {
        return $this->agenciaDv;
    }
    /**
     * Define o número da conta
     *
     * @param int $conta
     * @return AbstractBoleto
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
     * @return AbstractBoleto
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
     * @return AbstractBoleto
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
    public function isValid(){
        if($this->agencia == '' || $this->conta == '')
        {
            return false;
        }
        return true;
    }

    /**
     * Função para gerar o cabeçalho do arquivo.
     *
     * @return mixed
     */
    protected abstract function header();

    /**
     * Função para adicionar detalhe ao arquivo.
     *
     * @param BoletoContract $detalhe
     *
     * @return mixed
     */
    public abstract function addBoleto(BoletoContract $detalhe);

    /**
     * Função que gera o trailer (footer) do arquivo.
     *
     * @return mixed
     */
    protected abstract function trailer();

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
    public function addBoletos(array $boletos) {
        foreach ($boletos as $boleto) {
            $this->addBoleto($boleto);
        }

        return $this;
    }

    /**
     * Função para add valor a linha nas posições informadas.
     *
     * @param $i
     * @param $f
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
     * Inicia a edição do header
     */
    protected function iniciaHeader()
    {
        $this->aRegistros[self::HEADER] = array_fill(0,400, ' ');
        $this->atual = &$this->aRegistros[self::HEADER];
    }

    /**
     * Inicia a edição do trailer (footer).
     */
    protected function iniciaTrailer()
    {
        $this->aRegistros[self::TRAILER] = array_fill(0,400, ' ');
        $this->atual = &$this->aRegistros[self::TRAILER];
    }

    /**
     * Inicia uma nova linha de detalhe e marca com a atual de edição
     */
    protected function iniciaDetalhe()
    {
        $this->iRegistros++;
        $this->aRegistros[self::DETALHE][$this->iRegistros] = array_fill(0,400, ' ');
        $this->atual = &$this->aRegistros[self::DETALHE][$this->iRegistros];
    }

    /**
     * Retorna o header do arquivo.
     * @return mixed
     */
    protected function getHeader() {
        return $this->aRegistros[self::HEADER];
    }

    /**
     * Retorna os detalhes do arquivo
     * @return \Illuminate\Support\Collection
     */
    protected function getDetalhes() {
        return collect($this->aRegistros[self::DETALHE]);
    }

    /**
     * Retorna o trailer do arquivo.
     * @return mixed
     */
    protected function getTrailer() {
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
    private function valida(array $a) {
        $a = array_filter($a, 'strlen');
        if (count($a) != 400) {
            throw new \Exception('$a não possui 400 posições, possui: ' . count($a));
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

        if(!$this->isValid())
        {
            throw new \Exception('Campos requeridos pelo banco, aparentam estar ausentes');
        }

        $stringRemessa = '';
        if($this->iRegistros < 1)
        {
            throw new \Exception('Nenhuma linha detalhe foi adicionada');
        }

        $this->header();
        $stringRemessa .= $this->valida($this->getHeader()) . $this->fimLinha;

        foreach($this->getDetalhes() as $i => $detalhe)
        {
            $stringRemessa .= $this->valida($detalhe) . $this->fimLinha;
        }

        $this->trailer();
        $stringRemessa .= $this->valida($this->getTrailer());
        $stringRemessa .= $this->fimArquivo;

        return $stringRemessa;
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
        if(!is_dir($folder)) {
            mkdir($folder, 0777, true);
        }

        if(!is_writable(dirname($path))) {
            throw new \Exception('Path ' . $folder . ' não possui permissao de escrita');
        }

        $string = $this->gerar();
        file_put_contents($path, $string);

        return $path;
    }
//
//    public function __call($name, $arguments)
//    {
//        if(strtolower(substr($name, 0, 3)) == 'get')
//        {
//            $name = lcfirst(substr($name, 3));
//            if(property_exists($this, $name))
//            {
//                return $this->$name;
//            }
//        }
//
//        throw new \Exception('Método ' . $name . ' não existe');
//    }
}