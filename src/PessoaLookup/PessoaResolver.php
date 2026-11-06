<?php

namespace Eduardokum\LaravelBoleto\PessoaLookup;

use Eduardokum\LaravelBoleto\Util;
use Eduardokum\LaravelBoleto\Pessoa;
use Eduardokum\LaravelBoleto\Contracts\PessoaLookup;
use Eduardokum\LaravelBoleto\Exception\ValidationException;

/**
 * Monta uma Pessoa a partir de um documento consultado por um PessoaLookup.
 *
 * O objeto devolvido e uma Pessoa nativa do pacote, pronta para ser usada em
 * $boleto->setPagador($pessoa) ou no array 'pagador' => $pessoa, sem qualquer
 * alteracao no nucleo de geracao do boleto.
 */
class PessoaResolver
{
    /**
     * @var PessoaLookup
     */
    protected $lookup;

    /**
     * @param PessoaLookup $lookup
     */
    public function __construct(PessoaLookup $lookup)
    {
        $this->lookup = $lookup;
    }

    /**
     * Resolve uma Pessoa a partir de um CPF.
     *
     * @param string $cpf
     *
     * @return Pessoa
     * @throws ValidationException
     */
    public function porCpf($cpf)
    {
        return $this->montarPessoa($this->lookup->consultarCpf($cpf));
    }

    /**
     * Resolve uma Pessoa a partir de um CNPJ.
     *
     * @param string $cnpj
     *
     * @return Pessoa
     * @throws ValidationException
     */
    public function porCnpj($cnpj)
    {
        return $this->montarPessoa($this->lookup->consultarCnpj($cnpj));
    }

    /**
     * Resolve uma Pessoa detectando CPF (11 digitos) ou CNPJ (14 digitos).
     *
     * @param string $documento
     *
     * @return Pessoa
     * @throws ValidationException
     */
    public function porDocumento($documento)
    {
        $digitos = Util::onlyNumbers($documento);
        $tamanho = strlen($digitos);

        if ($tamanho == 11) {
            return $this->porCpf($digitos);
        }

        if ($tamanho == 14) {
            return $this->porCnpj($digitos);
        }

        throw new ValidationException('Documento invalido, informe um CPF (11 digitos) ou CNPJ (14 digitos)');
    }

    /**
     * Constroi a Pessoa a partir dos dados normalizados do lookup.
     *
     * O endereco da Pessoa e um campo unico, entao logradouro, numero e
     * complemento sao concatenados aqui; bairro, cep, cidade e uf tem campos
     * proprios.
     *
     * @param array $dados
     *
     * @return Pessoa
     */
    protected function montarPessoa(array $dados)
    {
        $dados = array_merge([
            'nome'         => null,
            'nomeFantasia' => null,
            'documento'    => null,
            'logradouro'   => null,
            'numero'       => null,
            'complemento'  => null,
            'bairro'       => null,
            'cep'          => null,
            'cidade'       => null,
            'uf'           => null,
        ], $dados);

        return Pessoa::create(
            $dados['nome'],
            $dados['documento'],
            $this->montarEndereco($dados['logradouro'], $dados['numero'], $dados['complemento']),
            $dados['bairro'],
            $dados['cep'],
            $dados['cidade'],
            $dados['uf'],
            null,
            $dados['nomeFantasia']
        );
    }

    /**
     * Concatena logradouro, numero e complemento em uma unica string.
     *
     * @param string|null $logradouro
     * @param string|null $numero
     * @param string|null $complemento
     *
     * @return string|null
     */
    protected function montarEndereco($logradouro, $numero, $complemento)
    {
        $endereco = trim((string) $logradouro);

        if (trim((string) $numero) !== '') {
            $endereco = $endereco !== '' ? $endereco . ', ' . trim($numero) : trim($numero);
        }

        if (trim((string) $complemento) !== '') {
            $endereco = $endereco !== '' ? $endereco . ' - ' . trim($complemento) : trim($complemento);
        }

        return $endereco !== '' ? $endereco : null;
    }
}
