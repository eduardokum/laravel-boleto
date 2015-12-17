<?php
namespace Eduardokum\LaravelBoleto\Cnab\Remessa;

use Eduardokum\LaravelBoleto\Cnab\Contracts\Cnab;

abstract class AbstractCnab implements Cnab
{

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
    public $idremessa;

    /**
     * Carteira de cobrança.
     *
     * @var
     */
    public $carteira;

    /**
     * Array com as variaveis requeridas para a classe.
     *
     * @var array
     */
    public $variaveisRequeridas = [];


    /**
     * Função para gerar o cabeçalho do arquivo.
     *
     * @return mixed
     */
    protected abstract function header();

    /**
     * Função para adicionar detalhe ao arquivo.
     *
     * @param Detalhe $detalhe
     *
     * @return mixed
     */
    public abstract function addDetalhe(Detalhe $detalhe);

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
        $i--;

        if ($i > 398 || $f > 400) {
            throw new \Exception('$ini ou $fim ultrapassam o limite máximo de 400');
        }

        if ($f < $i) {
            throw new \Exception('$ini é maior que o $fim');
        }

        $t = $f - $i;

        if (strlen($value) > $t) {
            throw new \Exception('String $valor maior que o tamanho definido em $ini e $fim: $valor= ' . strlen($value) . ' e tamanho é de: ' . $t);
        }

        $value = sprintf("%{$t}s", $value);
        $value = str_split($value, 1);

        return array_splice($this->atual, $i, $t, $value);
    }

    /**
     * Inicia a edição do header
     */
    protected function iniciaHeader()
    {
        $this->validaVariaveisRequeridas();

        $this->aRegistros[self::HEADER] = array_fill(0,400, ' ');
        $this->atual = &$this->aRegistros[self::HEADER];
    }

    /**
     * Inicia a edição do trailer (footer).
     */
    protected function iniciaTrailer()
    {
        $this->validaVariaveisRequeridas();

        $this->aRegistros[self::TRAILER] = array_fill(0,400, ' ');
        $this->atual = &$this->aRegistros[self::TRAILER];
    }

    /**
     * Inicia uma nova linha de detalhe e marca com a atual de edição
     */
    protected function iniciaDetalhe()
    {
        $this->validaVariaveisRequeridas();

        $this->iRegistros++;
        $this->aRegistros[self::DETALHE][$this->iRegistros] = array_fill(0,400, ' ');
        $this->atual = &$this->aRegistros[self::DETALHE][$this->iRegistros];
    }

    /**
     * Retorna a carteira
     *
     * @param string $default
     *
     * @return string
     */
    protected function getCarteira($default = ' ')
    {
        return $this->carteira ? $this->carteira : $default;
    }

    /**
     * Retorna o id da remessa
     *
     * @return string
     */
    protected function getID()
    {
        return $this->idremessa;
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

    private function validaVariaveisRequeridas()
    {

        $this->variaveisRequeridas =
            array_merge(
                $this->variaveisRequeridas,
                [
                    'idremessa',
                    'carteira'
                ]
            );

        $errors = false;
        $aErrors = [];
        foreach($this->variaveisRequeridas as $var)
        {
            if(!isset($this->$var))
            {
                $errors = true;
                $aErrors[] = $var;
            }
        }

        if($errors)
        {
            throw new \Exception('As variáveis [' . implode(', ', $aErrors) . '] devem ser preenchidas');
        }
    }

    /**
     * Gera o arquivo, retorna a string.
     *
     * @return string
     * @throws \Exception
     */
    public function gerar()
    {
        $stringRemessa = '';
        if($this->iRegistros < 1)
        {
            throw new \Exception('Nenhuma linha detalhe foi adicionada');
        }

        $this->header();
        $stringRemessa .= $this->valida($this->aRegistros[self::HEADER]) . $this->fimLinha;

        foreach($this->aRegistros[self::DETALHE] as $i => $detalhe)
        {
            $stringRemessa .= $this->valida($detalhe) . $this->fimLinha;
        }

        $this->trailer();
        $stringRemessa .= $this->valida($this->aRegistros[self::TRAILER]);

        if(!empty($this->fim_arquivo)) {
            $stringRemessa .= $this->fimArquivo;
        }

        return $stringRemessa;
    }

    public function __call($name, $arguments)
    {
        if(strtolower(substr($name, 0, 3)) == 'get')
        {
            $name = lcfirst(substr($name, 3));
            if(property_exists($this, $name))
            {
                return $this->$name;
            }
        }

        throw new \Exception('Método ' . $name . ' não existe');
    }
}