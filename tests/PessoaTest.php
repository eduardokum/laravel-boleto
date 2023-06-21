<?php

namespace Eduardokum\LaravelBoleto\Tests;

use Exception;
use Eduardokum\LaravelBoleto\Util;
use Eduardokum\LaravelBoleto\Pessoa;
use PHPUnit\Framework\Constraint\StringContains;

class PessoaTest extends TestCase
{
    public function testPessoaCriandoConstrutor()
    {

        $nome = 'Cliente';
        $endereco = 'Rua um, 123';
        $bairro = 'Bairro';
        $cep = '99999999';
        $uf = 'UF';
        $cidade = 'CIDADE';
        $documento = '99999999999';
        $email = 'email@dominio.com';

        $pessoa = new Pessoa([
            'nome' => $nome,
            'endereco' => $endereco,
            'bairro' => $bairro,
            'cep' => $cep,
            'uf' => $uf,
            'cidade' => $cidade,
            'documento' => $documento,
            'email' => $email,
        ]);

        $this->assertEquals($nome, $pessoa->getNome());
        $this->assertEquals($endereco, $pessoa->getEndereco());
        $this->assertEquals($bairro, $pessoa->getBairro());
        $this->assertEquals(Util::maskString($cep, '#####-###'), $pessoa->getCep());
        $this->assertEquals($uf, $pessoa->getUf());
        $this->assertEquals($cidade, $pessoa->getCidade());
        $this->assertEquals($email, $pessoa->getEmail());
        $this->assertEquals(Util::maskString($documento, '###.###.###-##'), $pessoa->getDocumento());
        $this->assertEquals('CPF', $pessoa->getTipoDocumento());

        $this->assertThat($pessoa->getCepCidadeUf(), new StringContains(Util::maskString($cep, '#####-###'), false));
        $this->assertThat($pessoa->getCepCidadeUf(), new StringContains(Util::maskString($cep, '#####-###'), false));
        $this->assertThat($pessoa->getCepCidadeUf(), new StringContains($cidade, false));
        $this->assertThat($pessoa->getCepCidadeUf(), new StringContains($uf, false));

        $this->assertThat($pessoa->getNomeDocumento(), new StringContains($nome, false));
        $this->assertThat($pessoa->getNomeDocumento(), new StringContains('CPF', false));
        $this->assertThat($pessoa->getNomeDocumento(), new StringContains(Util::maskString($documento, '###.###.###-##'), false));

        $pessoa->setDocumento('');
        $this->assertEquals($nome, $pessoa->getNomeDocumento());

        $documento = '99999999999999';
        $pessoa->setDocumento($documento);
        $this->assertEquals(Util::maskString($documento, '##.###.###/####-##'), $pessoa->getDocumento());
        $this->assertEquals('CNPJ', $pessoa->getTipoDocumento());

        $documento = '9999999999';
        $pessoa->setDocumento($documento);
        $this->assertEquals(Util::maskString($documento, '##.#####.#-##'), $pessoa->getDocumento());
        $this->assertEquals('CEI', $pessoa->getTipoDocumento());

    }

    public function testPessoaDocumentoErrado()
    {
        $this->expectException(Exception::class);

        $pessoa = new Pessoa([
            'documento' => '99999',
        ]);
    }

    public function testPessoaCriandoMetodoCreate()
    {

        $nome = 'Cliente';
        $endereco = 'Rua um, 123';
        $bairro = 'Bairro';
        $cep = '99999999';
        $uf = 'UF';
        $cidade = 'CIDADE';
        $documento = '99999999999';
        $email = 'email@dominio.com';

        $pessoa = new Pessoa([
            'nome' => $nome,
            'endereco' => $endereco,
            'bairro' => $bairro,
            'cep' => $cep,
            'uf' => $uf,
            'cidade' => $cidade,
            'documento' => $documento,
            'email' => $email,
        ]);

        $pessoa2 = Pessoa::create($nome, $documento, $endereco, $bairro, $cep, $cidade, $uf, $email);

        $pessoa_contrutor = new \ReflectionClass($pessoa);
        $pessoa_create = new \ReflectionClass($pessoa2);

        $propriedades = $pessoa_contrutor->getProperties();

        foreach ($propriedades as $propriedade) {

            $propriedade->setAccessible(true);
            $valor_1 = $propriedade->getValue($pessoa);

            $propriedade_create = $pessoa_create->getProperty($propriedade->getName());

            $propriedade_create->setAccessible(true);
            $valor_2 = $propriedade_create->getValue($pessoa2);

            $this->assertEquals($valor_1, $valor_2);
        }

    }

    public function testPessoaMascara()
    {

        $pessoa = new Pessoa;

        $pessoa->setDocumento('99.999.999/9999-99');
        $this->assertEquals('CNPJ', $pessoa->getTipoDocumento());
        $this->assertEquals('99.999.999/9999-99', $pessoa->getDocumento());
        $pessoa->setDocumento('99999999999999');
        $this->assertEquals('CNPJ', $pessoa->getTipoDocumento());
        $this->assertEquals('99.999.999/9999-99', $pessoa->getDocumento());

        $pessoa->setDocumento('999.999.999-99');
        $this->assertEquals('CPF', $pessoa->getTipoDocumento());
        $this->assertEquals('999.999.999-99', $pessoa->getDocumento());
        $pessoa->setDocumento('99999999999');
        $this->assertEquals('CPF', $pessoa->getTipoDocumento());
        $this->assertEquals('999.999.999-99', $pessoa->getDocumento());

        $pessoa->setDocumento('99.99999.9-99');
        $this->assertEquals('CEI', $pessoa->getTipoDocumento());
        $this->assertEquals('99.99999.9-99', $pessoa->getDocumento());
        $pessoa->setDocumento('9999999999');
        $this->assertEquals('CEI', $pessoa->getTipoDocumento());
        $this->assertEquals('99.99999.9-99', $pessoa->getDocumento());

    }
}
