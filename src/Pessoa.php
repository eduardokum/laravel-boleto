<?php
namespace Eduardokum\LaravelBoleto;

use Eduardokum\LaravelBoleto\Contracts\Pessoa as PessoaContract;
use Eduardokum\LaravelBoleto\Util;

class Pessoa implements PessoaContract
{
    /**
     * @var string
     */
    protected $nome;
    /**
     * @var string
     */
    protected $endereco;
    /**
     * @var string
     */
    protected $bairro;
    /**
     * @var string
     */
    protected $cep;
    /**
     * @var string
     */
    protected $uf;
    /**
     * @var string
     */
    protected $cidade;
    /**
     * @var string
     */
    protected $documento;

    /**
     * Cria a pessoa passando os parametros.
     *
     * @param $nome
     * @param $documento
     * @param null      $endereco
     * @param null      $cep
     * @param null      $cidade
     * @param null      $uf
     *
     * @return Pessoa
     */
    public static function create($nome, $documento, $endereco = null, $bairro = null, $cep = null, $cidade = null, $uf = null)
    {
        return new static([
            'nome' => $nome,
            'endereco' => $endereco,
            'bairro' => $bairro,
            'cep' => $cep,
            'uf' => $uf,
            'cidade' => $cidade,
            'documento' => $documento,
        ]);
    }

    /**
     * Construtor
     *
     * @param array $params
     */
    public function __construct($params = [])
    {
        Util::fillClass($this, $params);
    }
    /**
     * Define o CEP
     *
     * @param string $cep
     */
    public function setCep($cep)
    {
        $this->cep = $cep;
    }
    /**
     * Retorna o CEP
     *
     * @return string
     */
    public function getCep()
    {
        return Util::maskString(Util::onlyNumbers($this->cep), '#####-###');
    }
    /**
     * Define a cidade
     *
     * @param string $cidade
     */
    public function setCidade($cidade)
    {
        $this->cidade = $cidade;
    }
    /**
     * Retorna a cidade
     *
     * @return string
     */
    public function getCidade()
    {
        return $this->cidade;
    }

    /**
     * Define o documento (CPF, CNPJ ou CEI)
     *
     * @param string $documento
     *
     * @throws \Exception
     */
    public function setDocumento($documento)
    {
        $documento = substr(Util::onlyNumbers($documento), -14);
        if (!in_array(strlen($documento), [10, 11, 14, 0])) {
            throw new \Exception('Documento inválido');
        }
        $this->documento = $documento;
    }
    /**
     * Retorna o documento (CPF ou CNPJ)
     *
     * @return string
     */
    public function getDocumento()
    {
        if ($this->getTipoDocumento() == 'CPF') {
            return Util::maskString(Util::onlyNumbers($this->documento), '###.###.###-##');
        } elseif ($this->getTipoDocumento() == 'CEI') {
            return Util::maskString(Util::onlyNumbers($this->documento), '##.#####.#-##');
        }
        return Util::maskString(Util::onlyNumbers($this->documento), '##.###.###/####-##');
    }
    /**
     * Define o endereço
     *
     * @param string $endereco
     */
    public function setEndereco($endereco)
    {
        $this->endereco = $endereco;
    }
    /**
     * Retorna o endereço
     *
     * @return string
     */
    public function getEndereco()
    {
        return $this->endereco;
    }
    /**
     * Define o bairro
     *
     * @param string $bairro
     */
    public function setBairro($bairro)
    {
        $this->bairro = $bairro;
    }
    /**
     * Retorna o bairro
     *
     * @return string
     */
    public function getBairro()
    {
        return $this->bairro;
    }
    /**
     * Define o nome
     *
     * @param string $nome
     */
    public function setNome($nome)
    {
        $this->nome = $nome;
    }
    /**
     * Retorna o nome
     *
     * @return string
     */
    public function getNome()
    {
        return $this->nome;
    }
    /**
     * Define a UF
     *
     * @param string $uf
     */
    public function setUf($uf)
    {
        $this->uf = $uf;
    }
    /**
     * Retorna a UF
     *
     * @return string
     */
    public function getUf()
    {
        return $this->uf;
    }
    /**
     * Retorna o nome e o documento formatados
     *
     * @return string
     */
    public function getNomeDocumento()
    {
        if (!$this->getDocumento()) {
            return $this->getNome();
        } else {
            return $this->getNome() . ' / ' . $this->getTipoDocumento() . ': ' . $this->getDocumento();
        }
    }
    /**
     * Retorna se o tipo do documento é CPF ou CNPJ ou Documento
     *
     * @return string
     */
    public function getTipoDocumento()
    {
        $cpf_cnpj_cei = Util::onlyNumbers($this->documento);

        if (strlen($cpf_cnpj_cei) == 11) {
            return 'CPF';
        } elseif (strlen($cpf_cnpj_cei) == 10) {
            return 'CEI';
        }
        
        return 'CNPJ';
    }
    /**
     * Retorna o endereço formatado para a linha 2 de endereço
     *
     * Ex: 71000-000 - Brasília - DF
     *
     * @return string
     */
    public function getCepCidadeUf()
    {
        $dados = array_filter(array($this->getCep(), $this->getCidade(), $this->getUf()));
        return implode(' - ', $dados);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'nome' => $this->getNome(),
            'endereco' => $this->getEndereco(),
            'bairro' => $this->getBairro(),
            'cep' => $this->getCep(),
            'uf' => $this->getUf(),
            'cidade' => $this->getCidade(),
            'documento' => $this->getDocumento(),
            'nome_documento' => $this->getNomeDocumento(),
            'endereco2' => $this->getCepCidadeUf(),
        ];
    }
}
