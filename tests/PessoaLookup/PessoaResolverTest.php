<?php

namespace Eduardokum\LaravelBoleto\Tests\PessoaLookup;

use Eduardokum\LaravelBoleto\Pessoa;
use Eduardokum\LaravelBoleto\Tests\TestCase;
use Eduardokum\LaravelBoleto\Contracts\PessoaLookup;
use Eduardokum\LaravelBoleto\PessoaLookup\PessoaResolver;
use Eduardokum\LaravelBoleto\PessoaLookup\CpfCnpjComBrLookup;
use Eduardokum\LaravelBoleto\Exception\ValidationException;

class PessoaResolverTest extends TestCase
{
    private function lookupFake()
    {
        return new class implements PessoaLookup {
            public function consultarCpf($cpf)
            {
                return [
                    'nome'         => 'Test Token',
                    'nomeFantasia' => null,
                    'documento'    => '00000000000',
                    'logradouro'   => 'Rua A',
                    'numero'       => '100 B',
                    'complemento'  => 'Apto 03',
                    'bairro'       => 'Centro',
                    'cep'          => '99999123',
                    'cidade'       => 'Sao Paulo',
                    'uf'           => 'SP',
                ];
            }

            public function consultarCnpj($cnpj)
            {
                return [
                    'nome'         => 'TOKEN TEST LTDA',
                    'nomeFantasia' => 'TOKEN TEST',
                    'documento'    => '27865757000102',
                    'logradouro'   => 'Rua A',
                    'numero'       => '1',
                    'complemento'  => 'Sala 1',
                    'bairro'       => 'Centro',
                    'cep'          => '0000111',
                    'cidade'       => 'Montes Claros',
                    'uf'           => 'MG',
                ];
            }
        };
    }

    public function testPorCpfMontaPessoa()
    {
        $resolver = new PessoaResolver($this->lookupFake());
        $pessoa = $resolver->porCpf('000.000.000-00');

        $this->assertInstanceOf(Pessoa::class, $pessoa);
        $this->assertEquals('Test Token', $pessoa->getNome());
        $this->assertEquals('CPF', $pessoa->getTipoDocumento());
        $this->assertEquals('000.000.000-00', $pessoa->getDocumento());
        $this->assertEquals('Centro', $pessoa->getBairro());
        $this->assertEquals('Sao Paulo', $pessoa->getCidade());
        $this->assertEquals('SP', $pessoa->getUf());
    }

    public function testPorCnpjMontaPessoaComEnderecoConcatenado()
    {
        $resolver = new PessoaResolver($this->lookupFake());
        $pessoa = $resolver->porCnpj('27.865.757/0001-02');

        $this->assertEquals('TOKEN TEST LTDA', $pessoa->getNome());
        $this->assertEquals('TOKEN TEST', $pessoa->getNomeFantasia());
        $this->assertEquals('CNPJ', $pessoa->getTipoDocumento());
        $this->assertEquals('27.865.757/0001-02', $pessoa->getDocumento());
        $this->assertEquals('Rua A, 1 - Sala 1', $pessoa->getEndereco());
    }

    public function testEnderecoConcatenaLogradouroNumeroComplemento()
    {
        $resolver = new PessoaResolver($this->lookupFake());
        $pessoa = $resolver->porCpf('00000000000');

        $this->assertEquals('Rua A, 100 B - Apto 03', $pessoa->getEndereco());
    }

    public function testPorDocumentoDetectaCpf()
    {
        $resolver = new PessoaResolver($this->lookupFake());
        $pessoa = $resolver->porDocumento('000.000.000-00');

        $this->assertEquals('CPF', $pessoa->getTipoDocumento());
        $this->assertEquals('Test Token', $pessoa->getNome());
    }

    public function testPorDocumentoDetectaCnpj()
    {
        $resolver = new PessoaResolver($this->lookupFake());
        $pessoa = $resolver->porDocumento('27.865.757/0001-02');

        $this->assertEquals('CNPJ', $pessoa->getTipoDocumento());
        $this->assertEquals('TOKEN TEST LTDA', $pessoa->getNome());
    }

    public function testPorDocumentoInvalidoLancaExcecao()
    {
        $resolver = new PessoaResolver($this->lookupFake());

        $this->expectException(ValidationException::class);

        $resolver->porDocumento('123');
    }

    public function testIntegracaoLookupResolverComTransporteMockado()
    {
        $corpo = json_encode([
            'status'         => 1,
            'cnpj'           => '27.865.757/0001-02',
            'razao'          => 'TOKEN TEST LTDA',
            'fantasia'       => 'TOKEN TEST',
            'matrizEndereco' => [
                'cep'         => '39400-000',
                'logradouro'  => 'Rua A',
                'numero'      => '1',
                'complemento' => 'Sala 1',
                'bairro'      => 'Centro',
                'cidade'      => 'Montes Claros',
                'uf'          => 'MG',
            ],
        ]);

        $lookup = new CpfCnpjComBrLookup('token-teste', 3, 5, function ($url) use ($corpo) {
            return $corpo;
        });

        $resolver = new PessoaResolver($lookup);
        $pessoa = $resolver->porDocumento('27865757000102');

        $this->assertEquals('TOKEN TEST LTDA', $pessoa->getNome());
        $this->assertEquals('Rua A, 1 - Sala 1', $pessoa->getEndereco());
        $this->assertEquals('39400-000', $pessoa->getCep());
    }
}
