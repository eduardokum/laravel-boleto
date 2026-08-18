# Payment — CNAB 240 remittance

Payment exists **only in CNAB 240**. The model (`src/Pagamento/Banco/Banco.php`, over `Pagamento/AbstractPagamento.php`) is generic: every bank difference lives in `src/Cnab/Pagamento/Cnab240/Banco/<X>.php`.

## Taxonomy — three axes that decide everything

Before writing any segment, answer all three (asking the developer whatever is unclear):

### 1. Service type (batch header)

Defines the nature of the batch. In Itaú's SISPAG, for example: `20` suppliers, `22` taxes. Varies by bank.

### 2. Credit method / forma de lançamento (batch header)

Defines **which segments** the batch carries. Itaú examples:

| Method | Meaning | Segments |
|---|---|---|
| `41` | TED | A + B |
| `45` | PIX | A + B (B carries the key data) |
| `30` | Boleto payable **at the same bank** | J + J-52 |
| `31` | Boleto payable at **another bank** | J + J-52 |
| `13` | Utility bill (water/power/telecom) — barcode segment 2/3/4 | O |
| `19` | Municipal tax (IPTU/ISS) — barcode segment 1 | O |
| `91` | GNRE and barcoded taxes — barcode segment 9 | O |

Same-bank account credit, DOC, savings and payment orders have their own methods. **Check the bank's own table** — the numbers do not carry over between banks.

### 3. Destination segment

| Segment | Content |
|---|---|
| **A** | Account credit / TED / PIX — payee, bank, branch, account, amount, date |
| **B** | Payee complement: document, address; in the PIX variant, key type and value |
| **C** | Optional amount complement (when the bank uses it) |
| **J** | Boleto payment: barcode, amounts, dates |
| **J-52** | Optional J record: payer / beneficiary / guarantor data |
| **N** | Taxes **without** a barcode (DARF, GPS, FGTS…) — sub-types per tax |
| **O** | Taxes and utility bills **with** a barcode |
| **W** | Variable complement, bank-specific use |

Rule of thumb: **has a barcode → J (boleto) or O (tax/utility); has none → A (transfer) or N (tax)**.

## PIX

- Its own credit method (Itaú: `45`) and its own clearing house code (`009` — SPI).
- Key type, using the bank's codes: phone, email, CPF/CNPJ, random. In the canonical model: `TIPO_CHAVEPIX_CPF|CNPJ|CELULAR|EMAIL|ALEATORIA` (`Pagamento/AbstractPagamento.php`); the bank class translates them into the manual's numeric codes.
- Transfer type: checking account, payment account, savings, or **by key** — it changes how segment B is filled.
- Initiation method (`getFormaIniciacao()`) when the manual requires it.
- **File-level restriction**: at Itaú a PIX batch cannot share a file with other types — the class raises `ValidationException` in `validarSeparacaoPix()`. Always ask the developer and the manual whether a new bank has an equivalent restriction.

## Multi-batch

`AbstractPagamento::agruparPagamentosPorTipo()` groups payments by type (`getTipoPagamentoDoPagamento()`), creating **one batch per type**, numbered sequentially. `gerar()` assembles file header → N × (headerLoteMulti + segments + trailerLoteMulti) → trailerMulti.

When implementing a bank:

- Override `gerarSegmentos($payment)` (required — the parent throws).
- Override `headerLoteMulti()` / `trailerLoteMulti()` / `getValorTotalLoteMulti()` with the manual's positions.
- Override `agruparPagamentosPorTipo()` when the bank has an extra rule (e.g. separating same-bank boletos from other-bank ones into methods 30 and 31; blocking PIX from mixing).
- The parent's default type is `'TED'` when the payment carries no type. If the bank cannot accept that default, validate and raise an exception.

## Counters — rejection cause number one

| Counter | Rule |
|---|---|
| Record sequence within the batch | restarts per batch, starts at 1, no gaps |
| Record count in the batch trailer | does it include the batch header and trailer? the manual decides |
| Batch count in the file trailer | `getCountLotes()` |
| Total record count in the file trailer | `getCount()` |
| Per-batch amount totals | `getValorTotalLoteMulti()` — implied decimals |

Already seen in this repo: duplicated counter (Banrisul), wrong batch count (Itaú), out-of-order segment sequence (Sicoob, twice). **Check against the generated file by actually counting the lines.**

## Field formatting

- Amounts: implied decimals, zero-padded, unsigned, no separator. Different segments use different lengths for the same amount.
- Payee document: type (`1`/`2` or `01`/`02`) consistent with the length (11 = CPF, 14 = CNPJ). This has broken before (`getDocumento`/`tipoDocumento`).
- Dates: `Ymd` in 240 for most banks — confirm per record.
- Currency: `BRL`/`REA` depending on the segment (Itaú's segment O uses `REA`).
- Payee notice, clearing house, movement type and instruction code: named constants, values from the manual.

## Barcodes on taxes and utility bills

A collection/arrecadação barcode has **four 11-digit blocks** and its own typeable-line rules — different from a boleto. The first digit identifies the segment (1 city hall, 2 water, 3 power, 4 telecom, 9 GNRE/reserved), and that digit is what selects the credit method. Effective amount vs reference amount depends on the value identifier (position 3). This was a real bug (`93f627c`, Itaú taxes).

## File name and certification

Same rule as collection: implement `nomeSugerido()` when the bank requires a naming pattern. Payment almost always requires certification with the bank — close the report stating what still depends on it.
