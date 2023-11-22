<?php

namespace Eduardokum\LaravelBoleto\Tests\Remessa;

use Eduardokum\LaravelBoleto\Boleto\Banco\Caixa;
use Eduardokum\LaravelBoleto\Pessoa;
use Eduardokum\LaravelBoleto\Tests\TestCase;

class CaixaCnab240Test extends  TestCase
{


    protected static $pagador;
    protected static $beneficiario;

    /**
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        self::$beneficiario = new Pessoa(
            [
                'nome' => 'COLÉGIO CRISTÃO MOC',
                'endereco' => 'Rua um, 123',
                'cep' => '39402209',
                'uf' => 'MG',
                'cidade' => 'MONTES CLAROS',
                'documento' => '21.365.374/0001-80',
            ]
        );

        self::$pagador = new Pessoa(
            [
                'nome' => 'ALUNO TESTE DO PROESC',
                'endereco' => 'AVENIDA JOSE FERREIRA DO AMARAL',
                'bairro' => 'SAO LAZARO',
                'cep' => '68908420',
                'uf' => 'MG',
                'cidade' => 'MACAPA',
                'documento' => '72613595990',
            ]
        );
    }


    /**
     * @throws \Exception
     */
    public function test_tipo_de_inscricao_do_beneficiario()
    {

        $linhaSegmento = 1;

        $boleto = new Caixa();
        $boleto->setLogo(realpath(__DIR__ . '/../logos/') . DIRECTORY_SEPARATOR . '104.png')
            ->setDataVencimento(new \Carbon\Carbon())
            ->setValor('200')
            ->setNumero(717230357272)
            ->setNumeroDocumento(717230357272)
            ->setCarteira('RG')
            ->setAgencia('3115')
            ->setConta('1416')
            ->setCodigoCliente(1416)
            ->setMulta(1)
            ->setJuros(1)
            ->setPagador(self::$pagador)
            ->setDescricaoDemonstrativo([])
            ->setInstrucoes([]);


        $remessa = new \Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400\Banco\Caixa([
            'agencia' => '3115',
            'carteira' => 'RG',
            'conta' => '1416',
            'beneficiario' => self::$beneficiario,
            'Idremessa' => 1,
            'codigoCliente' => '1416'
        ]);
        $remessa->addBoleto($boleto);

        $file = implode(DIRECTORY_SEPARATOR, [
            __DIR__,
            'files',
            'cnab240',
            'caixa.txt'
        ]);

        $file2 = $remessa->save($file);
        $arrayArquivo = file($file2, FILE_IGNORE_NEW_LINES);
        $conteudo = $arrayArquivo[1];

        $indentificaoDistribuicao = substr($conteudo, 18, 1);
        $this->assertEquals('1', $indentificaoDistribuicao);
    }

    public function testBoletoLine()
    {

        $linhaSegmento = 1;

        $boleto = new Caixa();
        $boleto->setLogo(realpath(__DIR__ . '/../logos/') . DIRECTORY_SEPARATOR . '104.png')
            ->setDataVencimento(new \Carbon\Carbon())
            ->setValor('200')
            ->setNumero(717230357272)
            ->setNumeroDocumento(717230357272)
            ->setCarteira('RG')
            ->setAgencia('3115')
            ->setConta('1416')
            ->setCodigoCliente(1416)
            ->setMulta(1)
            ->setJuros(1)
            ->setPagador(self::$pagador)
            ->setDescricaoDemonstrativo([])
            ->setInstrucoes([]);


        $remessa = new \Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400\Banco\Caixa([
            'agencia' => '3115',
            'carteira' => 'RG',
            'conta' => '1416',
            'beneficiario' => self::$beneficiario,
            'Idremessa' => 1,
            'codigoCliente' => '1416'
        ]);
        $remessa->addBoleto($boleto);

        $file = implode(DIRECTORY_SEPARATOR, [
            __DIR__,
            'files',
            'cnab240',
            'caixa.txt'
        ]);

        $file2 = $remessa->save($file);
        $arrayArquivo = file($file2, FILE_IGNORE_NEW_LINES);
        $conteudo = $arrayArquivo[1];

        $indentificaoDistribuicao = substr($conteudo, 18, 1);
    }

    /**
     * @throws \Exception
     */
    public function test_mensagem_caixa()
    {
        $linhaHeader = 1;

        $boleto = new Caixa();
        $boleto->setLogo(realpath(__DIR__ . '/../logos/') . DIRECTORY_SEPARATOR . '104.png')
            ->setDataVencimento(new \Carbon\Carbon())
            ->setValor('200')
            ->setNumero(717230357272)
            ->setNumeroDocumento(717230357272)
            ->setCarteira('RG')
            ->setAgencia('3115')
            ->setConta('1416')
            ->setCodigoCliente(1416)
            ->setMulta(1)
            ->setJuros(1)
            ->setPagador(self::$pagador)
            ->setDescricaoDemonstrativo([])
            ->setInstrucoes([]);
        $remessa = new \Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab240\Banco\Caixa([
            'agencia' => '3115',
            'carteira' => 'RG',
            'conta' => '1416',
            'beneficiario' => self::$beneficiario,
            'Idremessa' => 1,
            'codigoCliente' => '1416',
            'mensagem1' =>'xxxxx'
        ]);
        $remessa->addBoleto($boleto);

        $file = implode(DIRECTORY_SEPARATOR, [
            __DIR__,
            'files',
            'cnab240',
            'caixa.txt'
        ]);

        $file2 = $remessa->save($file);
        $arrayArquivo = file($file2, FILE_IGNORE_NEW_LINES);
       $this->assertTrue(str_contains($arrayArquivo[$linhaHeader],'xxxxx'));
    }


    public function test_mensagem_caixa_segmento_p()
    {
        $linhaSegmentoP = 4;

        $boleto = new Caixa();
        $boleto->setLogo(realpath(__DIR__ . '/../logos/') . DIRECTORY_SEPARATOR . '104.png')
            ->setDataVencimento(new \Carbon\Carbon())
            ->setValor('200')
            ->setNumero(717230357272)
            ->setNumeroDocumento(717230357272)
            ->setCarteira('RG')
            ->setAgencia('3115')
            ->setConta('1416')
            ->setCodigoCliente(1416)
            ->setMulta(1)
            ->setJuros(1)
            ->setPagador(self::$pagador)
            ->setDescricaoDemonstrativo([])
            ->setInstrucoes([]);
        $remessa = new \Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab240\Banco\Caixa([
            'agencia' => '3115',
            'carteira' => 'RG',
            'conta' => '1416',
            'beneficiario' => self::$beneficiario,
            'Idremessa' => 1,
            'codigoCliente' => '1416',
            'mensagem3' =>'xxxxx'
        ]);
        $remessa->addBoleto($boleto);

        $file = implode(DIRECTORY_SEPARATOR, [
            __DIR__,
            'files',
            'cnab240',
            'caixa.txt'
        ]);

        $file2 = $remessa->save($file);
        $arrayArquivo = file($file2, FILE_IGNORE_NEW_LINES);
        var_dump($arrayArquivo);
        $this->assertTrue(str_contains($arrayArquivo[$linhaSegmentoP],'xxxxx'));
    }


}