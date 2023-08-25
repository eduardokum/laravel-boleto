<?php

namespace Eduardokum\LaravelBoleto\Tests\Remessa;

use Eduardokum\LaravelBoleto\Boleto\Banco as Boleto;
use Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab240\Banco as Remessa;
use Eduardokum\LaravelBoleto\Pessoa;
use Eduardokum\LaravelBoleto\Tests\TestCase;
use Exception;

class RemessaCnab240Test extends TestCase
{

    protected static $pagador;
    protected static $beneficiario;

    public static function setUpBeforeClass() : void
    {
        self::$beneficiario = new Pessoa(
            [
                'nome' => 'ACME',
                'endereco' => 'Rua um, 123',
                'cep' => '99999-999',
                'uf' => 'UF',
                'cidade' => 'CIDADE',
                'documento' => '99.999.999/9999-99',
            ]
        );

        self::$pagador = new Pessoa(
            [
                'nome' => 'Cliente',
                'endereco' => 'Rua um, 123',
                'bairro' => 'Bairro',
                'cep' => '99999-999',
                'uf' => 'UF',
                'cidade' => 'CIDADE',
                'documento' => '999.999.999-99',
            ]
        );
    }

    public static function tearDownAfterClass() : void
    {
        $aFiles = [
            __DIR__,
            'files',
            'cnab240',
        ];
        $files = glob(implode(DIRECTORY_SEPARATOR, $aFiles) . '/*'); // get all file names
        foreach($files as $file){
            if(is_file($file))
                @unlink($file);
        }
    }
    public function testRemessaItauCnab240(){
        $boleto = new Boleto\Itau([
            'logo' => realpath(__DIR__ . '/../logos/') . DIRECTORY_SEPARATOR . '033.png',
            'dataVencimento' => new \Carbon\Carbon(),
            'valor' => 100,
            'multa' => false,
            'juros' => false,
            'numero' => 1,
            'numeroDocumento' => 1,
            'pagador' => self::$pagador,
            'beneficiario' => self::$beneficiario,
            'diasBaixaAutomatica' => 0,
            'carteira' => '109',
            'agencia' => '9999',
            'conta' => '99999',
            'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
            'instrucoes' =>  ['instrucao 1', 'instrucao 2', 'instrucao 3'],
            'aceite' => 'S',
            'especieDoc' => 'DM',
        ]);

        $remessa = new Remessa\Itau([
            'agencia' => '9999',
            'carteira' => '109',
            'conta' => '99999',
            'beneficiario' => self::$beneficiario,
        ]);
        $remessa->addBoleto($boleto);

        $file = implode(DIRECTORY_SEPARATOR, [
            __DIR__,
            'files',
            'cnab240',
            'itau.txt'
        ]);

        $file2 = $remessa->save($file);

        $this->assertFileExists($file);
        $this->assertEquals($file, $file2);
    }


    /**
     * @throws Exception
     */
    public function testRemessaBBcnab240()
    {
        $boleto = new Boleto\Bb;
        $boleto->setLogo(realpath(__DIR__ . '/../logos/') . DIRECTORY_SEPARATOR . '001.png')
        ->setDataVencimento(new \Carbon\Carbon('2023-08-05'))
        ->setValor('1000')
        ->setNumero(549908869455)
        ->setNumeroDocumento(549908869455)
        ->setAceite('S')
        ->setBeneficiario(self::$beneficiario)
        ->setPagador(self::$pagador)
        ->setDescricaoDemonstrativo(['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'])
        ->setInstrucoes(['instrucao 1', 'instrucao 2', 'instrucao 3'])
        ->setCarteira(11)
        ->setAgencia(1111)
        ->setConta(22222)
        ->setConvenio('1115122');

        $remessa = new Remessa\Bb;
        $remessa->setBeneficiario(self::$beneficiario)
        ->setCarteira(11)
        ->setAgencia(1111)
        ->setConvenio('1115122')
        ->setVariacaoCarteira('017')
        ->setConta(22222);

        $remessa->addBoleto($boleto);

        $file = implode(DIRECTORY_SEPARATOR, [
            __DIR__,
            'files',
            'cnab240',
            'bb.txt'
        ]);
        $file2 = $remessa->save($file);
        $this->assertEquals($file, $file2);
    }


    /**
     * @throws Exception
     */
    public  function  testverficaCodigoConvenioBancoDoBrasil()
    {

        $beneficiario = new Pessoa(
            [
                'nome' => 'EVOLUÇÃO EDUCAÇÃO INFANTIL',
                'endereco' => 'Rua um, 123',
                'cep' => '55026145',
                'uf' => 'PE',
                'cidade' => 'CARUARU',
                'documento' => '31.355.289/0001-95',
            ]
        );

        $pagador = new Pessoa(
            [
                'nome' => 'ALBERES LUIZ DA SILVA',
                'endereco' => 'RUA MARIA DO CARMO PONTES',
                'bairro' => 'Bairro',
                'cep' => '55024735',
                'uf' => 'PE',
                'cidade' => 'CIDADE',
                'documento' => 'CARUARU',
            ]
        );
        $boleto = new Boleto\Bb;
        $boleto->setLogo(realpath(__DIR__ . '/../logos/') . DIRECTORY_SEPARATOR . '001.png')
            ->setDataVencimento(new \Carbon\Carbon('2023-08-05'))
            ->setValor('1000')
            ->setNumero(549908869455)
            ->setNumeroDocumento(549908869455)
            ->setAceite('S')
            ->setBeneficiario($beneficiario)
            ->setPagador($pagador)
            ->setDescricaoDemonstrativo(['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'])
            ->setInstrucoes(['instrucao 1', 'instrucao 2', 'instrucao 3'])
            ->setCarteira(17)
            ->setAgencia(5742)
            ->setConta(26326)
            ->setConvenio('3563937');

        $remessa = new Remessa\Bb;
        $remessa->setBeneficiario($beneficiario)
            ->setCarteira(17)
            ->setAgencia(5742)
            ->setConvenio('3563937')
            ->setVariacaoCarteira('019')
            ->setConta(22222);

        $remessa->addBoleto($boleto);

        $file = implode(DIRECTORY_SEPARATOR, [
            __DIR__,
            'files',
            'cnab240',
            'bb.txt'
        ]);
        $file2 = $remessa->save($file);

        $conteudo =  file_get_contents($file2);
        $codigioConvenio =  substr($conteudo,32, 19);
        $this->assertEquals('003563937001417019 ', $codigioConvenio);
    }

    /**
     * @throws Exception
     */
    public  function  testSegementoPBancoDoBrasilIdentificacaoDistribuicao()
    {

        $linhaSegmento = 3;

        $beneficiario = new Pessoa(
            [
                'nome' => 'EVOLUÇÃO EDUCAÇÃO INFANTIL',
                'endereco' => 'Rua um, 123',
                'cep' => '55026145',
                'uf' => 'PE',
                'cidade' => 'CARUARU',
                'documento' => '31.355.289/0001-95',
            ]
        );

        $pagador = new Pessoa(
            [
                'nome' => 'ALBERES LUIZ DA SILVA',
                'endereco' => 'RUA MARIA DO CARMO PONTES',
                'bairro' => 'Bairro',
                'cep' => '55024735',
                'uf' => 'PE',
                'cidade' => 'CIDADE',
                'documento' => 'CARUARU',
            ]
        );
        $boleto = new Boleto\Bb;
        $boleto->setLogo(realpath(__DIR__ . '/../logos/') . DIRECTORY_SEPARATOR . '001.png')
            ->setDataVencimento(new \Carbon\Carbon('2023-08-05'))
            ->setValor('1000')
            ->setNumero(549908869455)
            ->setNumeroDocumento(549908869455)
            ->setAceite('S')
            ->setBeneficiario($beneficiario)
            ->setPagador($pagador)
            ->setDescricaoDemonstrativo(['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'])
            ->setInstrucoes(['instrucao 1', 'instrucao 2', 'instrucao 3'])
            ->setCarteira(17)
            ->setAgencia(5742)
            ->setConta(26326)
            ->setConvenio('3563937');

        $remessa = new Remessa\Bb;
        $remessa->setBeneficiario($beneficiario)
            ->setCarteira(17)
            ->setAgencia(5742)
            ->setConvenio('3563937')
            ->setVariacaoCarteira('019')
            ->setConta(22222);

        $remessa->addBoleto($boleto);

        $file = implode(DIRECTORY_SEPARATOR, [
            __DIR__,
            'files',
            'cnab240',
            'bb.txt'
        ]);
        $file2 = $remessa->save($file);
        $arrayArquivo =  file($file2, FILE_IGNORE_NEW_LINES);
        $conteudo = $arrayArquivo[$linhaSegmento -1];

        $codigioConvenio =  substr($conteudo,62, 1);
        $this->assertEquals('0', $codigioConvenio);
    }






}