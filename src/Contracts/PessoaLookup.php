<?php

namespace Eduardokum\LaravelBoleto\Contracts;

/**
 * Contrato para consulta de dados cadastrais a partir de um documento.
 *
 * A implementacao recebe um CPF ou CNPJ e devolve um array normalizado com as
 * chaves consumidas por Pessoa::create (nome, nomeFantasia, documento,
 * logradouro, numero, complemento, bairro, cep, cidade, uf). Isso permite
 * preencher o pagador de um boleto sem alterar o nucleo do pacote.
 */
interface PessoaLookup
{
    /**
     * Consulta um CPF e devolve os dados normalizados.
     *
     * @param string $cpf
     *
     * @return array
     */
    public function consultarCpf($cpf);

    /**
     * Consulta um CNPJ e devolve os dados normalizados.
     *
     * @param string $cnpj
     *
     * @return array
     */
    public function consultarCnpj($cnpj);
}
