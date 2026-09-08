<?php

namespace Eduardokum\LaravelBoleto\Tests\PessoaLookup;

use Eduardokum\LaravelBoleto\Tests\TestCase;
use Eduardokum\LaravelBoleto\PessoaLookup\CpfCnpjComBrLookup;
use Eduardokum\LaravelBoleto\Exception\ValidationException;

class CpfCnpjComBrLookupTest extends TestCase
{
    private function respostaCpf()
    {
        return json_encode([
            'status'      => 1,
            'cpf'         => '000.000.000-00',
            'nome'        => 'Test Token',
            'endereco'    => 'Rua A',
            'numero'      => '100 B',
            'complemento' => 'Apto 03',
            'bairro'      => 'Centro',
            'cep'         => '99999123',
            'cidade'      => 'Sao Paulo',
            'uf'          => 'SP',
            'ibge'        => '1234567',
            'pacoteUsado' => 3,
        ]);
    }

    private function respostaCnpj()
    {
        return json_encode([
            'status'         => 1,
            'cnpj'           => '27.865.757/0001-02',
            'tipo'           => 'Matriz',
            'razao'          => 'TOKEN TEST LTDA',
            'fantasia'       => 'TOKEN TEST',
            'matrizEndereco' => [
                'cep'         => '0000-111',
                'tipo'        => 'Rua',
                'logradouro'  => 'Rua A',
                'numero'      => '1',
                'complemento' => 'Sala 1',
                'bairro'      => 'Centro',
                'cidade'      => 'Montes Claros',
                'uf'          => 'MG',
            ],
            'ibge'           => [
                'cidade' => ['ibge_id' => 3143302],
            ],
            'pacoteUsado'    => 5,
        ]);
    }

    private function transporteFixo($corpo, &$urlChamada = null)
    {
        return function ($url) use ($corpo, &$urlChamada) {
            $urlChamada = $url;

            return $corpo;
        };
    }

    public function testConsultarCpfNormaliza()
    {
        $lookup = new CpfCnpjComBrLookup('token-teste', 3, 5, $this->transporteFixo($this->respostaCpf(), $url));

        $dados = $lookup->consultarCpf('000.000.000-00');

        $this->assertEquals('Test Token', $dados['nome']);
        $this->assertNull($dados['nomeFantasia']);
        $this->assertEquals('00000000000', $dados['documento']);
        $this->assertEquals('Rua A', $dados['logradouro']);
        $this->assertEquals('100 B', $dados['numero']);
        $this->assertEquals('Apto 03', $dados['complemento']);
        $this->assertEquals('Centro', $dados['bairro']);
        $this->assertEquals('99999123', $dados['cep']);
        $this->assertEquals('Sao Paulo', $dados['cidade']);
        $this->assertEquals('SP', $dados['uf']);
        $this->assertStringContainsString('/3/00000000000', $url);
    }

    public function testConsultarCnpjNormaliza()
    {
        $lookup = new CpfCnpjComBrLookup('token-teste', 3, 5, $this->transporteFixo($this->respostaCnpj(), $url));

        $dados = $lookup->consultarCnpj('27.865.757/0001-02');

        $this->assertEquals('TOKEN TEST LTDA', $dados['nome']);
        $this->assertEquals('TOKEN TEST', $dados['nomeFantasia']);
        $this->assertEquals('27865757000102', $dados['documento']);
        $this->assertEquals('Rua A', $dados['logradouro']);
        $this->assertEquals('1', $dados['numero']);
        $this->assertEquals('Sala 1', $dados['complemento']);
        $this->assertEquals('Centro', $dados['bairro']);
        $this->assertEquals('0000-111', $dados['cep']);
        $this->assertEquals('Montes Claros', $dados['cidade']);
        $this->assertEquals('MG', $dados['uf']);
        $this->assertEquals(3143302, $dados['ibge']);
        $this->assertStringContainsString('/5/27865757000102', $url);
    }

    public function testPacoteCnpjConfiguravel()
    {
        $lookup = new CpfCnpjComBrLookup('token-teste', 3, 6, $this->transporteFixo($this->respostaCnpj(), $url));

        $lookup->consultarCnpj('27865757000102');

        $this->assertStringContainsString('/6/27865757000102', $url);
    }

    public function testStatusZeroLancaExcecaoComMensagemECodigo()
    {
        $corpo = json_encode([
            'status'     => 0,
            'cpf'        => '',
            'nome'       => null,
            'erro'       => 'CPF invalido!',
            'erroCodigo' => 100,
        ]);

        $lookup = new CpfCnpjComBrLookup('token-teste', 3, 5, $this->transporteFixo($corpo));

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('CPF invalido!');

        $lookup->consultarCpf('00000000000');
    }

    public function testRespostaVaziaLancaExcecao()
    {
        $lookup = new CpfCnpjComBrLookup('token-teste', 3, 5, $this->transporteFixo(''));

        $this->expectException(ValidationException::class);

        $lookup->consultarCpf('00000000000');
    }

    public function testErroHttpPropaga()
    {
        $transporte = function ($url) {
            throw new ValidationException('API CPF/CNPJ respondeu com HTTP 500');
        };

        $lookup = new CpfCnpjComBrLookup('token-teste', 3, 5, $transporte);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('HTTP 500');

        $lookup->consultarCnpj('27865757000102');
    }

    public function testJsonInvalidoLancaExcecao()
    {
        $lookup = new CpfCnpjComBrLookup('token-teste', 3, 5, $this->transporteFixo('<html>erro</html>'));

        $this->expectException(ValidationException::class);

        $lookup->consultarCpf('00000000000');
    }

    public function testDocumentoCpfInvalido()
    {
        $lookup = new CpfCnpjComBrLookup('token-teste', 3, 5, $this->transporteFixo($this->respostaCpf()));

        $this->expectException(ValidationException::class);

        $lookup->consultarCpf('123');
    }

    public function testDocumentoCnpjInvalido()
    {
        $lookup = new CpfCnpjComBrLookup('token-teste', 3, 5, $this->transporteFixo($this->respostaCnpj()));

        $this->expectException(ValidationException::class);

        $lookup->consultarCnpj('123');
    }

    public function testTokenVazioLancaExcecao()
    {
        $this->expectException(ValidationException::class);

        new CpfCnpjComBrLookup('   ');
    }
}
