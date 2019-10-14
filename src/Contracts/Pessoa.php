<?php
namespace Eduardokum\LaravelBoleto\Contracts;

interface Pessoa
{
    public function getNome();
    public function getNomeDocumento();
    public function getDocumento();
    public function getBairro();
    public function getEndereco();
    public function getCepCidadeUf();
    public function getEnderecoCompleto();
    public function getCep();
    public function getCidade();
    public function getUf();
    public function isDda();

    public function setNome($nome);
    public function setNomeDocumento($nomeDocumento);
    public function setDocumento($documento);
    public function setBairro($bairro);
    public function setEndereco($endereco);
    public function setCepCidadeUf($cepCidadeUf);
    public function setEnderecoCompleto($enderecoCompleto);
    public function setCep($cep);
    public function setCidade($cidade);
    public function setUf($uf);
    public function setDda($dda);

    public function toArray();
}
