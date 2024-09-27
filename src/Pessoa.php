<?php

namespace Eduardokum\LaravelBoleto;

use Eduardokum\LaravelBoleto\Exception\ValidationException;
use Eduardokum\LaravelBoleto\Contracts\Pessoa as PessoaContract;

class Pessoa implements PessoaContract
{
    const TIPO_PAGADOR = 'pagador';
    const TIPO_BENEFICIARIO = 'beneficiario';
    const TIPO_SACADOR = 'sacadorAvalista';

    /**
     * @var string
     */
    protected $tipo;

    /**
     * @var string
     */
    protected $nome;

    /**
     * @var string|null
     */
    protected $nomeFantasia = null;

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
     * @var string
     */
    protected $email;

    /**
     * @var bool
     */
    protected $dda = false;

    /**
     * @param      $nome
     * @param      $documento
     * @param string|null $endereco
     * @param string|null $bairro
     * @param string|null $cep
     * @param string|null $cidade
     * @param string|null $uf
     * @param string|null $email
     * @param string|null $nomeFantasia
     * @return Pessoa
     */
    public static function create($nome, $documento, $endereco = null, $bairro = null, $cep = null, $cidade = null, $uf = null, $email = null, $nomeFantasia = null)
    {
        return new static([
            'nome'         => $nome,
            'nomeFantasia' => $nomeFantasia,
            'endereco'     => $endereco,
            'bairro'       => $bairro,
            'cep'          => $cep,
            'uf'           => $uf,
            'cidade'       => $cidade,
            'documento'    => $documento,
            'email'        => $email,
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
     * Define o tipo
     *
     * @param $tipo
     * @param bool $force
     * @return Pessoa
     * @throws ValidationException
     */
    public function setTipo($tipo, $force = false)
    {
        if (! in_array($tipo, [self::TIPO_PAGADOR, self::TIPO_BENEFICIARIO, self::TIPO_SACADOR])) {
            throw new ValidationException("Tipo de pessoa inválido [$tipo]");
        }

        if ($this->getTipo() && ! $force) {
            return $this;
        }

        $this->tipo = $tipo;

        return $this;
    }

    /**
     * Retorna o bairro
     *
     * @return string
     */
    public function getTipo()
    {
        return $this->tipo;
    }

    /**
     * Define o CEP
     *
     * @param string $cep
     *
     * @return Pessoa
     */
    public function setCep($cep)
    {
        $this->cep = $cep;

        return $this;
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
     *
     * @return Pessoa
     */
    public function setCidade($cidade)
    {
        $this->cidade = $cidade;

        return $this;
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
     * @return Pessoa
     * @throws ValidationException
     */
    public function setDocumento($documento)
    {
        $documento = substr(Util::onlyNumbers($documento), -14);
        if (! in_array(strlen($documento), [10, 11, 14, 0])) {
            throw new ValidationException('Documento inválido');
        }
        $this->documento = $documento;

        return $this;
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
     *
     * @return Pessoa
     */
    public function setEndereco($endereco)
    {
        $this->endereco = $endereco;

        return $this;
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
     *
     * @return Pessoa
     */
    public function setBairro($bairro)
    {
        $this->bairro = $bairro;

        return $this;
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
     *
     * @return Pessoa
     */
    public function setNome($nome)
    {
        $this->nome = $nome;

        return $this;
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
     * Define o Nome Fantasia
     *
     * @param string $nomeFantasia
     *
     * @return Pessoa
     */
    public function setNomeFantasia($nomeFantasia)
    {
        $this->nomeFantasia = $nomeFantasia;

        return $this;
    }

    /**
     * Retorna o Nome Fantasia
     *
     * @return string
     */
    public function getNomeFantasia()
    {
        return $this->nomeFantasia;
    }

    /**
     * Define a UF
     *
     * @param string $uf
     *
     * @return Pessoa
     */
    public function setUf($uf)
    {
        $this->uf = $uf;

        return $this;
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
        if (! $this->getDocumento()) {
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
        $dados = array_filter([$this->getCep(), $this->getCidade(), $this->getUf()]);

        return implode(' - ', $dados);
    }

    /**
     * Retorna o endereço completo em uma única string
     *
     * Ex.: Rua um, 123 - Bairro Industrial - Brasília - DF - 71000-000
     *
     * @return string
     */
    public function getEnderecoCompleto()
    {
        $dados = array_filter([$this->getEndereco(), $this->getBairro(), $this->getCidade(), $this->getUf(), $this->getCep()]);

        return implode(' - ', $dados);
    }

    /**
     * @return bool
     */
    public function isDda()
    {
        return $this->dda;
    }

    /**
     * @param bool $dda
     *
     * @return Pessoa
     */
    public function setDda($dda)
    {
        $this->dda = $dda;

        return $this;
    }

    /**
     * @param string $email
     *
     * @return Pessoa
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'nome'              => $this->getNome(),
            'endereco'          => $this->getEndereco(),
            'bairro'            => $this->getBairro(),
            'cep'               => $this->getCep(),
            'uf'                => $this->getUf(),
            'cidade'            => $this->getCidade(),
            'documento'         => $this->getDocumento(),
            'nome_documento'    => $this->getNomeDocumento(),
            'endereco2'         => $this->getCepCidadeUf(),
            'endereco_completo' => $this->getEnderecoCompleto(),
            'email'             => $this->getEmail(),
            'dda'               => $this->isDda(),
        ];
    }
}
