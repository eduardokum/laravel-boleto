# laravel-boleto
Pacote para gerar boletos e remessas

[![Latest Stable Version](https://poser.pugx.org/eduardokum/laravel-boleto/version)](https://packagist.org/packages/eduardokum/laravel-boleto) 
[![Latest Unstable Version](https://poser.pugx.org/eduardokum/laravel-boleto/v/unstable)](//packagist.org/packages/eduardokum/laravel-boleto) 
[![Total Downloads](https://poser.pugx.org/eduardokum/laravel-boleto/downloads)](https://packagist.org/packages/eduardokum/laravel-boleto)
[![License](https://poser.pugx.org/eduardokum/laravel-boleto/license)](https://packagist.org/packages/eduardokum/laravel-boleto)

## Gerar boleto

Gerando somente 1

### Criando o beneficiário ou pagador

```php
$beneficiario = new \Eduardokum\LaravelBoleto\Boleto\Pessoa([
    'nome' => 'ACME',
    'endereco' => 'Rua um, 123',
    'cep' => '99999-999',
    'uf' => 'UF',
    'cidade' => 'CIDADE',
    'documento' => '99.999.999/9999-99',
]);

$pagador = new \Eduardokum\LaravelBoleto\Boleto\Pessoa([
    'nome' => 'Cliente',
    'endereco' => 'Rua um, 123',
    'cep' => '99999-999',
    'uf' => 'UF',
    'cidade' => 'CIDADE',
    'documento' => '999.999.999-99',
]);
```

```php
$boletoArray = [
	'logo' => 'path/para/o/logo',
	'dataVencimento' => \Carbon\Carbon('1790-01-01'),
	'valor' => 100.00,
	'multa' => 10.00, // porcento
	'juros' => 2.00, // porcento ao mes
	'juros_apos' =>  1, // juros e multa após
	'diasProtesto' => false, // protestar após, se for necessário
	'numero' => 1,
	'numeroDocumento' => 1,
	'pagador' => $pagador, // Objeto PessoaContract
	'beneficiario' => $beneficiario, // Objeto PessoaContract
	'agencia' => 9999,
	'agenciaDv' => 9, // se possuir
	'conta' => 99999,
	'contaDv' => 9, // se possuir
	'carteira' => 99,
	'convenio' => 9999999, // se possuir
	'variacaoCarteira' => 99, // se possuir
	'range' => 99999, // se possuir
	'codigoCliente' => 99999, // se possuir
	'ios' => 0,
	'descricaoDemonstrativo' => ['msg1', 'msg2', 'msg3'], // máximo de 5
	'instrucoes' =>  ['inst1', 'inst2'], // máximo de 5
	'aceite' => 1,
	'especieDoc' => 'DM',
];

$boleto = new \Eduardokum\LaravelBoleto\Boleto\Banco\Bb($boletoArray);

$boleto->renderPDF();
// ou
$boleto->renderHTML();

```


Gerando mais de 1, não chamar a função render() do boleto e usar: (SOMENTE PDF)

```php
$pdf = new Eduardokum\LaravelBoleto\Boleto\Render\Pdf();

$pdf->addBoleto($boleto);

$pdf->gerarBoleto();
```

## Gerar remessa

```php
$remessaArray = [
	'agencia' => 9999,
	'agenciaDv' => 9, // se possuir
	'conta' => 99999,
	'contaDv' => 9, // se possuir
	'carteira' => 99,
	'convenio' => 9999999, // se possuir
	'range' => 99999, // se possuir
	'codigoCliente' => 99999, // se possuir
	'variacaoCarteira' => 99, // se possuir
	'codigoTransmissao' => 99999999999999999999, // se possuir
	'beneficiario' => $beneficiario,
];

$remessa = new \Eduardokum\LaravelBoleto\Cnab\Remessa\Banco\Bb($remessaArray);

$remessa->addBoleto($boleto); // Objeto de boleto gerado, BoletoContract

echo $remessa->gerar();
```

## Tratar retorno

```php
$retorno = \Eduardokum\LaravelBoleto\Cnab\Retorno\Factory::make('full_path_arquivo_retorno');
$retorno->processar();

echo $retorn->getBancoNome();
foreach($retorno as $registro)
{
	dd($registro->getDados());
}
```

Métodos disponíveis:

```php
$retorno->getDetalhes();

$retorno->getHeader();

$retorno->getTrailer());
```