[![Packagist](https://img.shields.io/packagist/v/eduardokum/laravel-boleto.svg?style=flat-square)](https://github.com/eduardokum/laravel-boleto)
[![Packagist](https://img.shields.io/packagist/dt/eduardokum/laravel-boleto.svg?style=flat-square)](https://github.com/eduardokum/laravel-boleto)
[![Packagist](https://img.shields.io/packagist/l/eduardokum/laravel-boleto.svg?style=flat-square)](https://github.com/eduardokum/laravel-boleto)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/eduardokum/laravel-boleto/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/eduardokum/laravel-boleto/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/eduardokum/laravel-boleto/badges/build.png?b=master)](https://scrutinizer-ci.com/g/eduardokum/laravel-boleto/build-status/master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/eduardokum/laravel-boleto/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/eduardokum/laravel-boleto/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/eduardokum/laravel-boleto/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/eduardokum/laravel-boleto/?branch=master)
[![Build Status](https://travis-ci.org/eduardokum/laravel-boleto.svg?branch=master)](https://travis-ci.org/eduardokum/laravel-boleto)
[![GitHub forks](https://img.shields.io/github/forks/eduardokum/laravel-boleto.svg?style=social&label=Fork)](https://github.com/eduardokum/laravel-boleto)

# laravel-boleto
Pacote para gerar boletos e remessas

# ATENÇÃO
**Havera uma mudança na forma em que se gera o número dos boletos.**
Hoje o atributo **nossoNumero** é gerado a partir do atributo **numeroDocumento**, porém esse número pode ser um alfanumérico, o que eu não tinha atentado no início do projeto. A grande mudança será que o atributo **numero** que antes era utilizado somente como um número de controle, hoje será usado para gerar o atributo **nossoNumero**, o atributo **numeroDocumento** continuará aparecendo no boleto no campo designado a ele, e será adicionado um novo campo que será utilizado para o número de controle, que será o atributo **numeroControle**.


Essas mudanças seram comitadas no branch **develop** e será definido uma data para o merge no **master**

----------

## Bancos suportados

**Boleto**
- Banco do Brasil
- Bradesco
- Caixa
- Hsbc
- Itau
- Santander
- Sicredi **em processo de homologação**
- Banrisul **necessita homologação**

**Remessa**
- Banco do Brasil [400]
- Bradesco [400]
- Caixa [400]
- Hsbc [400]
- Itau [400]
- Santander [400, 240 **necessita testes**		]
- Sicredi [400]  **em processo de homologação**
- Banrisul [400] **necessita homologação e complemento**

**Retorno**
- Banco do Brasil  [400]
- Bradesco [400]
- Caixa [400]
- Hsbc [400]
- Itau [400]
- Santander [400, 240]
- Sicredi [400] **em testes**

## Instalação
Via composer:

composer require eduardokum/laravel-boleto

Ou adicione manualmente ao seu composer.json:

"eduardokum/laravel-boleto": "dev-master"

## Gerar boleto

Gerando somente 1

### Criando o beneficiário ou pagador

```php
$beneficiario = new \Eduardokum\LaravelBoleto\Pessoa([
    'nome' => 'ACME',
    'endereco' => 'Rua um, 123',
    'cep' => '99999-999',
    'uf' => 'UF',
    'cidade' => 'CIDADE',
    'documento' => '99.999.999/9999-99',
]);

$pagador = new \Eduardokum\LaravelBoleto\Pessoa([
    'nome' => 'Cliente',
    'endereco' => 'Rua um, 123',
    'bairro' => 'Bairro',
    'cep' => '99999-999',
    'uf' => 'UF',
    'cidade' => 'CIDADE',
    'documento' => '999.999.999-99',
]);
```

```php
$boletoArray = [
	'logo' => 'path/para/o/logo', // Logo da empresa
	'dataVencimento' => new \Carbon\Carbon('1790-01-01'),
	'valor' => 100.00,
	'multa' => 10.00, // porcento
	'juros' => 2.00, // porcento ao mes
	'juros_apos' =>  1, // juros e multa após
	'diasProtesto' => false, // protestar após, se for necessário
	'numero' => 1,
	'numeroDocumento' => 1,
	'pagador' => $pagador, // Objeto PessoaContract
	'beneficiario' => $beneficiario, // Objeto PessoaContract
	'agencia' => 9999, // BB, Bradesco, CEF, HSBC, Itáu
	'agenciaDv' => 9, // se possuir
	'conta' => 99999, // BB, Bradesco, CEF, HSBC, Itáu, Santander
	'contaDv' => 9, // Bradesco, HSBC, Itáu
	'carteira' => 99, // BB, Bradesco, CEF, HSBC, Itáu, Santander
	'convenio' => 9999999, // BB
	'variacaoCarteira' => 99, // BB
	'range' => 99999, // HSBC
	'codigoCliente' => 99999, // Bradesco, CEF, Santander
	'ios' => 0, // Santander
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
Ou para adicionar um array de boletos
$pdf->addBoletos($boletos);

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
	'beneficiario' => $beneficiario,
];

$remessa = new \Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400\Banco\Bb($remessaArray);

$remessa->addBoleto($boleto); // Objeto de boleto gerado, BoletoContract
Ou para adicionar um array de boletos
$remessa->addBoletos($boletos);

echo $remessa->gerar();
```

## Tratar retorno

```php
$retorno = \Eduardokum\LaravelBoleto\Cnab\Retorno\Factory::make('full_path_arquivo_retorno');
$retorno->processar();

echo $retorno->getBancoNome();
foreach($retorno as $registro)
{
	dd($registro->getDados());
}
```

**Métodos disponíveis:**

```php
$retorno->getDetalhes();

$retorno->getHeader();

$retorno->getTrailer());
```