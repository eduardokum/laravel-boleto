# Testing

## Where tests live

| Layer | File |
|---|---|
| Boleto | `tests/Boleto/<Bank>Test.php` |
| Collection remittance | `tests/Remessa/<Bank>Cnab400Test.php` / `<Bank>Cnab240Test.php` |
| Collection return | `tests/Retorno/<Bank>Cnab400Test.php` |
| Return fixture | `tests/Retorno/files/cnab400/<bank>.ret` |

All extend `Eduardokum\LaravelBoleto\Tests\TestCase` (Orchestra Testbench).

## Running

```bash
vendor/bin/phpunit --filter <Bank>     # just this bank
vendor/bin/phpunit                     # full suite (mandatory if Abstract*/Util were touched)
```

Use `phpunit_gt_81.xml` when the environment runs PHP > 8.1.

## Remittance test pattern

Fixed, deterministic data (never the `TestCase` random helpers for positional assertions). Private `boleto()` / `remessa()` builders with `array_merge` so each test varies one field:

```php
private function boleto(array $params = []) {
    return new BoletoBank(array_merge([...defaults...], $params));
}
```

Tests that **must** exist:

1. **Structure** — line count, exact `strlen` (400/240), record type of each line
2. **Header** — every field via `substr($line, $start - 1, $length)`, against the manual's value
3. **Detail** — same, covering the variable fields: "nosso número" + check digit, branch/account/check digit, wallet, amounts, dates, document kind, payer document, sequence
4. **Trailer** — totals
5. **Occurrences** — one test per status (write-off, due date change, amount change, custom) asserting the code at the right position
6. **Charges** — fine, interest, discount (each supported type), rebate
7. **Rejections** — `expectException(ValidationException::class)` for whatever the layout cannot carry
8. **CNAB 240** — counters: per-batch sequence, records in the batch trailer, batches and records in the file trailer

Each test's docblock states **which manual rule** it verifies — the pattern used in the Cresol tests. That is what turns the test suite into documentation of the integration.

## Return test pattern

```php
$retorno = Factory::make(__DIR__ . '/files/cnab400/<bank>.ret');
$retorno->processar();
```

Cover: the factory resolves the right class (which proves the `Util::$aBancos` registration works), the header, each occurrence type present in the fixture, amounts and dates, and the trailer.

## Without a real bank file

1. Ask the developer — the best route, and they may have a certification `.ret`.
2. Without one: generate the return from the remittance using `FakeRetorno` + `Util::controlePmisto` (fill both `switch` blocks for the bank).
3. **Declare the limitation in the report**: "the return was validated only against a generated fixture, not against a real bank file".

A fixture hand-built from the manual is acceptable if the developer confirms the positions — but then the test proves the parser, not the layout.

## Definition of done

- `vendor/bin/phpunit --filter <Bank>` green, with the output pasted into the report
- Full suite green if `Abstract*`, `Util` or `Contracts` were touched
- No `markTestSkipped`, empty tests or trivial asserts (`assertTrue(true)`)
- No TODOs in production code
