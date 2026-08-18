# Library architecture

## Two independent domains

| | **Collection** (receiving) | **Payment** (paying) |
|---|---|---|
| Model | `src/Boleto/Banco/X.php` extends `Boleto/AbstractBoleto` | `src/Pagamento/AbstractPagamento.php` (single model, no per-bank class) |
| Remittance | `src/Cnab/Remessa/Cnab400\|Cnab240/Banco/X.php` | `src/Cnab/Pagamento/Cnab240/Banco/X.php` |
| Return | `src/Cnab/Retorno/Cnab400\|Cnab240/Banco/X.php` | `src/Cnab/Pagamento/Retorno/Cnab240/Banco/X.php` |
| Contract | `src/Contracts/Boleto/Boleto.php` | `src/Contracts/Pagamento/Pagamento.php` |
| Return factory | `src/Cnab/Retorno/Factory.php` | `src/Cnab/Pagamento/Retorno/Factory.php` |
| Fake return | `src/Cnab/Retorno/FakeRetorno.php` | `src/Cnab/Pagamento/Retorno/FakeRetornoPagamento.php` |

Collection has both 400 and 240. Payment is **240 only**. There is no `Pagamento/Banco/<Bank>.php` class — the payment model is generic (`src/Pagamento/Banco/Banco.php`); bank differences live in the remittance class.

## Collection layers

### `Boleto/Banco/X.php` extends `AbstractBoleto`

Commonly overridden:

- `$codigoBanco`, `$carteiras`
- `$especiesCodigo` — document kind to bank code map (used by `getEspecieDocCodigo($default, $layout)`, which takes a 240/400 layout argument for when the tables differ)
- `gerarNossoNumero()` — number + check digit
- `getNossoNumeroBoleto()` — how it is printed on the boleto
- `getCampoLivre()` — positions 20-44 of the barcode; **a classic source of bugs**
- `parseCampoLivre($campoLivre)` static — the inverse, used when importing a boleto from a barcode. Skip it and imports silently return wrong data.
- `$variaveis_adicionais` / `$mostrarEnderecoFichaCompensacao` — rendering

### `Cnab/Remessa/Cnab400/Banco/X.php` extends `Cnab400\AbstractRemessa`

`AbstractRemessa` API (`src/Cnab/Remessa/AbstractRemessa.php`):

| Member | Use |
|---|---|
| `add($start, $end, $value)` | writes a positional field; validates length and overlap |
| `iniciaHeader()` / `iniciaDetalhe()` / `iniciaTrailer()` | required before the record's `add()` calls |
| `header()` / `addBoleto()` / `trailer()` / `gerar()` | abstract, to implement |
| `addCampoObrigatorio('idremessa')` | in the constructor, when the bank requires an extra field |
| `$fimLinha`, `$fimArquivo` | terminators; vary by bank |
| `$carteiras` | accepted wallets; validated in `isValid()` |
| `resolveCodigoDesconto()`, `resolveValorDesconto()`, `isDescontoPercentual()`, `resolveValorDescontoAbsoluto()` | translate the canonical discount into the bank's format — **use these helpers, don't reimplement** |
| `nomeSugerido()` | file name the bank requires (see attention points) |
| `save()`, `download()` | output |

For CNAB 240 the structure gains a file header, batch header, segments, batch trailer and file trailer, each with its own counters.

### `Cnab/Retorno/Cnab400/Banco/X.php` extends `Cnab400\AbstractRetorno`

Implements `processarHeader()`, `processarDetalhe()`, `processarTrailer()`. On 240, also `processarHeaderLote()` and `processarTrailerLote()`.

It fills the canonical `Cnab/Retorno/Cnab400/{Header,Detalhe,Trailer}` objects — the library's consumer must never need to know the bank's layout.

## Registration points (easy to forget)

| File | What to do |
|---|---|
| `src/Contracts/Boleto/Boleto.php` | `const COD_BANCO_X = '999';` |
| `src/Contracts/Pagamento/Pagamento.php` | same — the two contracts keep separate lists and both need the code |
| `src/Util.php` — `$aBancos` map | `COD_BANCO_X => 'Banco\\X'` — without it the return `Factory` cannot resolve the class |
| `src/Util.php` — `controlePmisto` (two `switch` blocks, one for header and one for detail) | copies fields from the remittance into the fake return; without it, fake-return tests end up with empty branch/account/"nosso número" |
| `src/CalculoDV.php` | one method per bank, e.g. `cresolNossoNumero()`, `cresolContaCorrente()` |
| `logos/<code>.png` | boleto rendering breaks without the logo |
| `src/Cnab/Remessa/Traits/` | rule shared between the bank's 400 and 240 (e.g. `FaixaNossoNumeroCresol`) |

## Status constants (collection)

`src/Contracts/Boleto/Boleto.php`:

```
STATUS_REGISTRO = 1
STATUS_ALTERACAO = 2
STATUS_BAIXA = 3
STATUS_ALTERACAO_DATA = 4
STATUS_ALTERACAO_VALOR = 5
STATUS_CUSTOM = 99
```

The remittance translates status into the **bank's** occurrence code. `STATUS_CUSTOM` uses `$boleto->getComando()` for codes the library does not model. Every new remittance must handle all five; ignoring one means sending "registration" when the consumer asked for a write-off.
