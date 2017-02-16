[![Packagist](https://img.shields.io/packagist/v/eduardokum/laravel-boleto.svg?style=flat-square)](https://github.com/eduardokum/laravel-boleto)
[![Packagist](https://img.shields.io/packagist/dt/eduardokum/laravel-boleto.svg?style=flat-square)](https://github.com/eduardokum/laravel-boleto)
[![Packagist](https://img.shields.io/packagist/l/eduardokum/laravel-boleto.svg?style=flat-square)](https://github.com/eduardokum/laravel-boleto)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/eduardokum/laravel-boleto/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/eduardokum/laravel-boleto/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/eduardokum/laravel-boleto/badges/build.png?b=master)](https://scrutinizer-ci.com/g/eduardokum/laravel-boleto/build-status/master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/eduardokum/laravel-boleto/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/eduardokum/laravel-boleto/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/eduardokum/laravel-boleto/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/eduardokum/laravel-boleto/?branch=master)
[![Build Status](https://travis-ci.org/eduardokum/laravel-boleto.svg?branch=master)](https://travis-ci.org/eduardokum/laravel-boleto)
[![GitHub forks](https://img.shields.io/github/forks/eduardokum/laravel-boleto.svg?style=social&label=Fork)](https://github.com/eduardokum/laravel-boleto)

# Laravel Boleto
Pacote para gerar boletos, remessas e leitura de retorno.

## Para dúvidas ou sugestões utilize o nosso grupo de discussão

## Requerimentos
- [PHP Extensão Intl](http://php.net/manual/pt_BR/book.intl.php)


## Links
- [Documentação da API](http://eduardokum.github.io/laravel-boleto/)
- [Grupo de Discussão](https://groups.google.com/d/forum/laravel-boleto)
- [Grupo no Telegram](https://t.me/laravelBoleto)

## Bancos suportados

Banco | Boleto | Remessa 400 | Remessa 240 | Retorno 400 | Retorno 240
----- | ------ | ----------- | ----------- | ----------- | ----------- |
 Banco do Brasil | :white_check_mark: | :white_check_mark: | | :white_check_mark: | |
 Bancoob (Sicoob) | :white_check_mark: * | :white_check_mark: | | | :white_check_mark: * |
 Banrisul | :white_check_mark: | :white_check_mark: | | :white_check_mark: | |
 Bradesco | :white_check_mark: | :white_check_mark: | | :white_check_mark: | |
 Caixa | :white_check_mark: | :white_check_mark: | | :white_check_mark: | |
 Hsbc | :white_check_mark: | :white_check_mark: | | :white_check_mark: | |
 Itau | :white_check_mark: | :white_check_mark: | | :white_check_mark: | |
 Santander | :white_check_mark: | :white_check_mark: | :white_check_mark: | :white_check_mark: | :white_check_mark: |
 Sicredi | :white_check_mark: | :white_check_mark: | | :white_check_mark: * | |

**\* necessita de homologação**

## Instalação
Via composer:

composer require eduardokum/laravel-boleto

Ou adicione manualmente ao seu composer.json:

"eduardokum/laravel-boleto": "dev-master"

## Gerar boleto


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

### Criando o objeto boleto

#### Campos númericos e suas funções
- **numero**: campo numérico utilizado para a criação do nosso numero. (identificação do título no banco)*
- **numeroControle**: campo de livre utilização. até 25 caracteres. *(identificação do título na empresa)*
- **numeroDocumento**: campo utilizado para informar ao que o documento se referente *(duplicata, nf, np, ns, etc...)*


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
```

### Gerando o boleto

**Gerando o boleto a partir da instância do objeto (somente um boleto)**

```php
$boleto->renderPDF();
// ou
$boleto->renderHTML();

// Os dois métodos aceita como parâmetro 2 boleano.
// 1º Se True após renderizado irá mostrar a janela de impressão. O Valor default é false.
// 2º Se False irá esconder as instruções de impressão. O valor default é true
$boleto->renderPDF(true, false); // mostra a janela de impressão e esconde as instruções de impressão
```
```php
/*
 * O comportamento padrão para os métodos renderPDF e renderHTM é retornar uma string pura.
 * Para gerar um retorno no controller do laravel utilize da seguinte forma:
 */

// PDF
return response($boleto->renderPDF(), 200, [
    'Content-Type' => 'application/pdf',
    'Content-Disposition' => 'inline; boleto.pdf',
]);

// HTML
return response($boleto->renderHTML());

```

**Gerando boleto a partir da instância do render**


```php
// Gerar em PDF
$pdf = new Eduardokum\LaravelBoleto\Boleto\Render\Pdf();

$pdf->addBoleto($boleto);
// Ou para adicionar um array de boletos
$pdf->addBoletos($boletos);

// Quando não informado parâmetros ele se comportará como Pdf::OUTPUT_STANDARD, enviando o buffer do pdf com os headers apropriados.
$pdf->gerarBoleto();

// Para mostrar a janela de impressão no load do PDF
$pdf->showPrint();

// Para remover as intruções de impressão
$pdf->hideInstrucoes();

// O método gerarBoleto da classe PDF aceita como parâmetro:
//	1º destino: constante com os destinos disponíveis. Ex: Pdf::OUTPUT_SAVE.
//	2º path: caminho absoluto para salvar o pdf quando destino for Pdf::OUTPUT_SAVE.
//Ex:
$pdf->gerarBoleto(Pdf::OUTPUT_SAVE, storage_path('app/boletos/meu_boleto.pdf')); // salva o boleto na pasta.
$pdf_inline = $pdf->gerarBoleto(Pdf::OUTPUT_STRING); // retorna o boleto em formato string.
$pdf->gerarBoleto(Pdf::OUTPUT_DOWNLOAD); // força o download pelo navegador.

// Gerar em HTML
$html = new Eduardokum\LaravelBoleto\Boleto\Render\Html();
$html->addBoleto($boleto);
// Ou para adicionar um array de boletos
$html->addBoletos($boletos);

// Para mostrar a janela de impressão no load da página
$html->showPrint();

// Para remover as intruções de impressão
$html->hideInstrucoes();

$html->gerarBoleto();

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

// Adicionar um boleto.
$remessa->addBoleto($boleto);

// Ou para adicionar um array de boletos
$boletos = [];
$boletos[] = $boleto1;
$boletos[] = $boleto2;
$boletos[] = $boleto3;
$remessa->addBoletos($boletos);

echo $remessa->gerar();
```

## Tratar retorno

```php
$retorno = \Eduardokum\LaravelBoleto\Cnab\Retorno\Factory::make('full_path_arquivo_retorno');
$retorno->processar();
echo $retorno->getBancoNome();

// Retorno implementa \SeekableIterator sendo assim podemos utilizar o foreach da seguinte forma:
foreach($retorno as $registro) {
	var_dump($registro->getDados());
}

// Ou também podemos:
$detalheCollection = $retorno->getDetalhes();
foreach($detalheCollection as $detalhe) {
	var_dump($detalhe->getDados());
}

// Ou até mesmo do jeito laravel
$detalheCollection->each(function ($detalhe, $index) {
    var_dump($detalhe->getDados())
});
```

**Métodos disponíveis:**

```php
$retorno->getDetalhes();

$retorno->getHeader();

$retorno->getTrailer();
```
