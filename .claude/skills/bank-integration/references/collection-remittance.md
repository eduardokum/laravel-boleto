# Collection — boleto and remittance

## The boleto: what has to be right

| Element | Where | Pitfall |
|---|---|---|
| "Nosso número" | `gerarNossoNumero()` | The check digit can be non-numeric (Cresol uses "P" when the remainder is 1). If the layout declares the field numeric, those numbers must be **skipped**, not truncated |
| Free field (barcode positions 20-44) | `getCampoLivre()` | Composition varies by bank: branch, wallet, "nosso número", assignor code, account, constants. The assignor code is **not always the checking account** |
| `parseCampoLivre()` | static, on the boleto | The inverse of the above. Without it, importing a boleto from a barcode silently returns wrong data |
| Document kind | `$especiesCodigo` + `getEspecieDocCodigo($default, $layout)` | The table can differ between 240 and 400 within the same bank — that's what the `$layout` argument is for |
| Wallet | `$carteiras` | In several banks the code printed on the boleto differs from the one sent in the remittance |

## Charges and benefits

### Fine (multa)

`setMulta($percentage)` / `getMulta()`; `setMultaApos($days)` / `getMultaApos()`.

The stored value is a **percentage**. Layouts usually require a pair: fine code (0 = none, 1 = fixed amount, 2 = percentage — check the manual, it varies) plus the value. Sending a percentage into a fixed-amount field is either a rejection or an absurd charge.

### Interest (juros)

`setJuros($percentage)` / `getJuros()` — percentage **per month**.
`getMoraDia()` — the library already converts: `Util::percent(amount, interest) / 30`. Use `getMoraDia()` when the manual's field is "daily interest amount"; use `getJuros()` when it is a percentage.
`setJurosApos($days)` / `getJurosApos()`.

### Discount — **use the `AbstractRemessa` helpers, don't reimplement**

The consuming application sends a **canonical code**; the library translates per bank. Canonical types in `Contracts/Boleto/Boleto.php`:

```
TIPO_DESCONTO_NENHUM                        = '0'
TIPO_DESCONTO_VALOR_FIXO                    = '1'   fixed amount until the given date
TIPO_DESCONTO_PERCENTUAL                    = '2'   percentage until the given date
TIPO_DESCONTO_VALOR_ANTECIPACAO_DIA_CORRIDO = '3'
TIPO_DESCONTO_VALOR_ANTECIPACAO_DIA_UTIL    = '4'
TIPO_DESCONTO_PERCENTUAL_DIA_CORRIDO        = '5'
TIPO_DESCONTO_PERCENTUAL_DIA_UTIL           = '6'
TIPO_DESCONTO_CANCELAMENTO                  = '7'
```

In the bank's remittance class, declare the **canonical to bank code** map:

```php
protected $tiposDescontoSuportados = [
    BoletoContract::TIPO_DESCONTO_VALOR_FIXO => '1',   // code per the bank's manual
    BoletoContract::TIPO_DESCONTO_PERCENTUAL => '2',
];
```

Then use:

- `resolveCodigoDesconto($boleto)` — returns the bank's code; raises `ValidationException` if the type is not in the map
- `resolveValorDesconto($boleto)` — raw value (percentage or amount, per the type)
- `resolveValorDescontoAbsoluto($boleto)` — **always in BRL**, converting percentages; for layouts **without a discount-type field** (e.g. Cresol's CNAB 400)

Rule: layout with a type field → `resolveCodigoDesconto` + `resolveValorDesconto`. Layout without one → `resolveValorDescontoAbsoluto`.

Always pair the amount with the **discount date** (`getDataDesconto()`); an amount above zero with no date is a rejection.

### Rebate and protest

- Rebate (abatimento): its own field, not to be confused with discount.
- Protest: `getDiasProtesto($default)`. `setTipoProtesto`: 0 do not protest, 1 calendar days, 2 business days, 3 negative-list calendar days, 4 do not negative-list.
- `getDiasBaixaAutomatica($default)` — many banks forbid protest and automatic write-off together; the boleto class should raise an exception (the Cresol pattern).
- **Layout that does not support protest**: raise `ValidationException` in `addBoleto()` naming the alternative. Never drop the instruction silently.

## Occurrence codes (remittance)

Map **every** status, with named constants:

| Status | Typical occurrence |
|---|---|
| `STATUS_REGISTRO` | 01 — title entry |
| `STATUS_BAIXA` | 02 — write-off request |
| `STATUS_ALTERACAO` / `STATUS_ALTERACAO_DATA` | 06 — due date change |
| `STATUS_ALTERACAO_VALOR` | the bank's own code |
| `STATUS_CUSTOM` | `sprintf('%2.02s', $boleto->getComando())` |

The pattern in `addBoleto()`: write the registration occurrence, then overwrite it with `add()` at the same position according to the status. Hardcoding `'01'` was a real bug (Inter) — every write-off request became a new registration.

## CNAB 400 — skeleton

```php
protected function header()   { $this->iniciaHeader(); /* add()... */ return $this; }
public function addBoleto(BoletoContract $boleto) {
    // layout-specific validation BEFORE accepting the boleto
    $this->boletos[] = $boleto;
    $this->iniciaDetalhe();
    // add()...
    $this->add(395, 400, Util::formatCnab('9', $this->iRegistros + 1, 6)); // sequence
    return $this;
}
protected function trailer()  { $this->iniciaTrailer(); /* ... */ $this->add(395, 400, Util::formatCnab('9', $this->getCount(), 6)); return $this; }
```

`Util::formatCnab($type, $value, $length, $decimals = 0)` — `'9'` numeric, zero-padded left; `'X'` alphanumeric, space-padded right. The 4th argument gives the implied decimals (`13, 2` = 13 positions with 2 decimal places).

## CNAB 240 — structure and counters

Records: file header (0), batch header (1), details (3, with segments P/Q/R/S/Y in collection), batch trailer (5), file trailer (9).

What breaks most:

- **Layout version** for the file and for the batch — each bank has its own, and it can vary by batch type
- **Counters**: the record sequence **within the batch** restarts per batch; the batch trailer counts the batch's records (including or excluding header/trailer, per the manual); the file trailer counts batches and total records
- **Segment R** (fine / discounts 2 and 3) only exists when there is such data; sending it empty can be rejected
- Branch/account: numeric zero-padded or alphanumeric space-padded — Banrisul required the switch

## File name

Several banks require a naming pattern (Itaú: 8 characters; Sicredi: its own). Implement `nomeSugerido()` and use `save($path, true)` / `download()` with it. Skipping this makes the bank refuse the upload even with correct content.
