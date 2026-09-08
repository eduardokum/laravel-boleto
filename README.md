 [![Packagist](https://img.shields.io/packagist/v/eduardokum/laravel-boleto.svg?style=flat-square)](https://github.com/eduardokum/laravel-boleto)
[![Packagist](https://img.shields.io/packagist/dt/eduardokum/laravel-boleto.svg?style=flat-square)](https://github.com/eduardokum/laravel-boleto)
[![Packagist](https://img.shields.io/packagist/l/eduardokum/laravel-boleto.svg?style=flat-square)](https://github.com/eduardokum/laravel-boleto)
[![build](https://github.com/eduardokum/laravel-boleto/actions/workflows/build.yml/badge.svg)](https://github.com/eduardokum/laravel-boleto/actions/workflows/build.yml)
[![GitHub forks](https://img.shields.io/github/forks/eduardokum/laravel-boleto.svg?style=social&label=Fork)](https://github.com/eduardokum/laravel-boleto)

# Laravel Boleto
Pacote para gerar boletos, remessas e leitura de retorno.

**Projeto atualizado para o PHP 7.2, utilize sempre a última versão do PHP**

**Suporte ao Laravel 6 em diante**

[Documentação do Projeto](https://laravel-boleto.readthedocs.io/)

## Preenchimento do pagador por CPF/CNPJ

Recurso opcional e aditivo (não altera o núcleo de geração do boleto) para montar o pagador a partir de um CPF ou CNPJ, sem digitar nome e endereço na mão.

O recurso é composto por três peças:

* `Contracts\PessoaLookup`: contrato de consulta. Recebe o documento e devolve um array normalizado.
* `PessoaLookup\CpfCnpjComBrLookup`: implementação de referência que consulta a API da [CPF.CNPJ](https://www.cpfcnpj.com.br/).
* `PessoaLookup\PessoaResolver`: monta uma `Pessoa` nativa do pacote (via `Pessoa::create`), pronta para o `setPagador` ou para a chave `pagador` do boleto.

```php
use Eduardokum\LaravelBoleto\PessoaLookup\PessoaResolver;
use Eduardokum\LaravelBoleto\PessoaLookup\CpfCnpjComBrLookup;

$lookup = new CpfCnpjComBrLookup('SEU_TOKEN');
$resolver = new PessoaResolver($lookup);

// Detecta CPF (11 dígitos) ou CNPJ (14 dígitos) automaticamente
$pagador = $resolver->porDocumento('27.865.757/0001-02');

// Também dá para ser explícito
$pagador = $resolver->porCpf('000.000.000-00');
$pagador = $resolver->porCnpj('27.865.757/0001-02');

$boleto = new Eduardokum\LaravelBoleto\Boleto\Banco\Bancoob([
    // ... demais campos do boleto
    'pagador' => $pagador,
]);
```

O endereço da `Pessoa` é um campo único, então o resolver concatena logradouro, número e complemento; bairro, CEP, cidade e UF vão em campos próprios.

### Token da API

O token é gerado no painel da CPF.CNPJ em API > Tokens e fica atrelado ao IP de origem das requisições. Para testes existe um token público: `5ae973d7a997af13f0aaf2bf60e65803`.

Por padrão o `CpfCnpjComBrLookup` usa o pacote 3 para CPF (nome e endereço) e o pacote 5 para CNPJ (razão social, nome fantasia e endereço da matriz). O pacote 6 pode ser passado no construtor quando se quer também situação cadastral, porte e Simples Nacional/SIMEI:

```php
$lookup = new CpfCnpjComBrLookup('SEU_TOKEN', 3, 6);
```

O transporte HTTP usa a extensão cURL (já exigida pelo pacote), mas aceita um callable no formato `function ($url) { return $corpo; }` no construtor, o que permite reaproveitar um cliente já presente na aplicação ou simular respostas em testes.

A CPF.CNPJ oferece consulta em tempo real (D+0), cobertura nacional e, no pacote 6, os dados de Simples Nacional e SIMEI. Um exemplo completo está em `exemplos/pagador_cpfcnpj.php`.

## Doações

**Estamos em busca de *doadores* e *patrocinadores* para ajudar a financiar parte do desenvolvimento deste pacote** 

Este é um projeto totalmente *OpenSource*, para usa-lo, copia-lo e modifica-lo você não paga absolutamente nada. Porém para continuarmos a mante-lo de forma adequada é necessária alguma contribuição seja feita, seja auxiliando na codificação, na documentação, na realização de testes e identificação de falhas e BUGs.

Mas também, caso você ache que qualquer informação obtida aqui, lhe foi útil e que isso vale de algum dinheiro e está disposto a doar algo, sinta-se livre para enviar qualquer quantia, seja diretamente ao autor ou através do PayPal e do PagSeguro.

<a target="_blank" href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=QPDFT3UXS6PTL&lc=BR&item_name=Laravel%20boleto&item_number=laravel%2dboleto&currency_code=BRL&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted">
        <img alt="Doar com Paypal" src="https://www.paypalobjects.com/pt_BR/BR/i/btn/btn_donateCC_LG.gif"/></a>
<a target="_blank" href="https://pag.ae/bhn79Hc">
	<img alt="Doar com Pagseguro" src="https://stc.pagseguro.uol.com.br/public/img/botoes/doacoes/120x53-doar.gif"/></a>
        
*Agradecemos a contribuição.*

[![DigitalOcean Referral Badge](https://web-platforms.sfo2.cdn.digitaloceanspaces.com/WWW/Badge%203.svg)](https://www.digitalocean.com/?refcode=b99ef059ce2e&utm_campaign=Referral_Invite&utm_medium=Referral_Program&utm_source=badge)

## Apoiadores (Doações)

* Leandro Henrique Siqueira
* Daniela Seco
* Alberto Yorimasa Kaneto
* Luciano Martins
* CAJU (ARAUJO & PRADO) 
* FABRÍCIO WICKERT
