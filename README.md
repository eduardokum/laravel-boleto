# laravel-boleto
Pacote para gerar boletos e remessas 

## Gerar boleto

Gerando somente 1

```php
$boleto = new \Eduardokum\LaravelBoleto\Boleto\Banco\Bb();
$boleto->agencia = '1234';
$boleto->conta = '123456';
$boleto->carteira = '12';
$boleto->numero = '1';
$boleto->convenio = '1234567';
$boleto->contrato = '1234567';
$boleto->identificacao = 'Boleto 1';
$boleto->especieDocumento = 'DM';
$boleto->aceite = 'N';
$boleto->dataDocumento = '2015-10-21';
$boleto->valor = '100';
$boleto->dataVencimento = '2015-10-21';
$boleto->nossoNumero = '123';
$boleto->cedenteDocumento = '99.999.999-9999/99';
$boleto->cedenteNome = 'Acme';
$boleto->cedenteEndereco = 'Rua, 123';
$boleto->cedenteCidadeUF = 'Cidade - UF';
$boleto->sacadoDocumento = '999.999.999-99';
$boleto->sacadoNome = 'Cliente';
$boleto->sacadoEndereco = 'Rua, 123';
$boleto->sacadoCidadeUF = 'Cidade - UF';

$boleto->processar();

$boleto->render();
```

Gerando mais de 1, não chamar a função render() do boleto e usar:

```php
$pdf = new Eduardokum\LaravelBoleto\Boleto\Render\Pdf();

$pdf->addBoleto($boleto);

$pdf->gerarBoleto();
```

## Gerar remessa

```php
$remessa = new \Eduardokum\LaravelBoleto\Cnab\Remessa\Banco\Bb();
$remessa->idremessa = 1;
$remessa->carteira = '5';
$remessa->agencia = '2144';
$remessa->conta = '1300112774';
$remessa->carteiraVariacao = '12';
$remessa->convenio = '123123';
$remessa->cedenteNome = 'NEW LIFE SAO PAULO COMERCIO DE PRODUTOS HOSPI';
$remessa->cedenteDocumento = '03.993.940/0001-17';

$detalhe = new \Eduardokum\LaravelBoleto\Cnab\Remessa\Detalhe();
$detalhe->numero = '1';
$detalhe->numeroDocumento = '1';
$detalhe->dataVencimento = '2015-10-21';
$detalhe->dataDocumento = '2015-10-21';
$detalhe->tipoCobrancaBB = \Eduardokum\LaravelBoleto\Cnab\Remessa\Banco\Bb::TIPO_COBRANCA_SIMPLES;
$detalhe->especie = 'DM';
$detalhe->aceite = 'N';
$detalhe->instrucao1 = '00';
$detalhe->instrucao2 = '00';
$detalhe->dataLimiteDesconto = '2015-10-21';
$detalhe->valorDesconto = '0';
$detalhe->valorIOF = '0';
$detalhe->valorMora = '0';
$detalhe->valorAbatimento = '0';
$detalhe->valor = '100';
$detalhe->diasProtesto = '0';
$detalhe->dataMulta = '2015-10-2';
$detalhe->taxaMulta = '0';
$detalhe->valorMulta = '0';
$detalhe->xDiasMulta = '0';
$detalhe->naoReceberDias = '0';
$detalhe->sacadoDocumento = '999.999.999-99';
$detalhe->sacadoNome = 'Cliente';
$detalhe->sacadoEndereco = 'Rua, 123';
$detalhe->sacadoBairro = 'Bairro';
$detalhe->sacadoCEP = '99999-999';
$detalhe->sacadoCidade = 'Cidade';
$detalhe->sacadoEstado = 'UF';
$detalhe->sacadorAvalista = '';
$detalhe->setNumeroControle(['C'=>'123', 'P'=>'123']);

$remessa->addDetalhe($detalhe);

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