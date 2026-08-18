# Collection — return file

## Responsibility

**Translating the bank's file into a canonical state is the library's job, not the consuming application's.** Whoever uses the library must never need to know any bank's occurrence codes. A return implementation that merely copies raw codes into `Detalhe` is incomplete.

## How the factory resolves the bank

`Cnab/Retorno/Factory::make($file)`:

1. `Util::isHeaderRetorno()` validates that it is a return file
2. CNAB 400 → bank code at header positions **77-79** (`mb_substr($line, 76, 3)`)
   CNAB 240 → positions **1-3**
3. `Util::getBancoClass($bank)` looks up the `$aBancos` map in `src/Util.php` — without the entry you get "Banco não possui essa versão de CNAB"
4. Instantiates `Cnab\Retorno\Cnab400\Banco\<X>` and calls `processar()`

If the bank does not write its own code in the return header (it happens), that needs handling — ask the developer what the real file's signature looks like.

## Methods to implement

CNAB 400: `processarHeader()`, `processarDetalhe()`, `processarTrailer()`.
CNAB 240: plus `processarHeaderLote()` and `processarTrailerLote()`.

Each receives the line already exploded into a position array and fills the canonical `Cnab/Retorno/Cnab400/{Header,Detalhe,Trailer}` objects.

## Occurrence mapping — the heart of the return

Every bank occurrence code must land on a canonical type (`Contracts/Cnab/Retorno/Detalhe`):

```
OCORRENCIA_LIQUIDADA  = 1   settled
OCORRENCIA_BAIXADA    = 2   written off
OCORRENCIA_ENTRADA    = 3   registered
OCORRENCIA_ALTERACAO  = 4   changed
OCORRENCIA_PROTESTADA = 5   protested
OCORRENCIA_OUTROS     = 6   other
OCORRENCIA_ERRO       = 9   error
```

Pattern:

```php
$d->setOcorrencia($this->rem(109, 110, $detalhe));           // the bank's raw code
$d->setOcorrenciaDescricao(...);                              // description from the manual's table
if (in_array($d->getOcorrencia(), ['06', '07', '08'])) {      // constants from the manual
    $d->setOcorrenciaTipo($d::OCORRENCIA_LIQUIDADA);
}
// ...
$d->setOcorrenciaTipo($d::OCORRENCIA_OUTROS);                 // explicit fallback at the end
```

Rules:

- **No code without a type.** Always finish with an `OCORRENCIA_OUTROS` fallback, never leave it `null`.
- **Rejections** (`setRejeicao()`) need the reason. A rejection occurrence means `OCORRENCIA_ERRO` plus the reason code and description, otherwise the consumer cannot tell what to fix.
- Don't duplicate codes across lists — duplicates already caused inconsistent mapping (Itaú).

## Financial fields on the detail record

Fill whatever the manual offers: `setValor`, `setValorRecebido`, `setValorTarifa`, `setValorOutrasDespesas`, `setValorIOF`, `setValorAbatimento`, `setValorDesconto`, `setDataOcorrencia`, `setDataVencimento`, `setDataCredito`.

`setDataOcorrencia($date, $format = 'dmy')` — the format varies by bank and by record; check the manual before accepting the default.

A distinction that matters: **title amount** ≠ **amount received** ≠ **amount credited** (received minus fees). Mixing the three is a reconciliation bug that only surfaces at closing.

## Barcode / typeable line in the return

Some flows rebuild the boleto from the return file. If the return carries "nosso número", branch and account, the typeable line must come out right — this was a real bug (free field built with the wrong operation number). If the return does not carry enough data, don't invent it: leave it empty.

## Fake return (no real bank file)

`Cnab/Retorno/FakeRetorno.php` plus `Util::controlePmisto` generate a return from the remittance for testing. It requires both `Util` `switch` blocks filled in for the bank (header and detail), copying the positions that bank's return expects.

A fake validates the **parser**, not the **bank's layout**. Always declare that limitation in the report and ask the developer for a real `.ret` when one exists.
