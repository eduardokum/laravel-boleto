# Attention points — accumulated acceptance criteria

This file is **alive**. Every line is a point that already caused trouble in some integration and therefore became a mandatory acceptance item for all the next ones. Walk it as a checklist before closing a layer; at the end of each integration, **add the new points** that showed up (protocol at the bottom).

An item is ticked only when it was **verified in the generated or parsed file** — not when it "looks right in the code".

## A. Source of truth

- [ ] Manual identified by file name, version and date, and confirmed with the developer
- [ ] Checked whether the bank's collection and payment use different manuals (most do)
- [ ] Manual recorded in a comment at the top of every implemented class
- [ ] No position, code or table copied from another bank — everything came from this bank's manual
- [ ] Any mismatch between the manual and the bank's real file taken to the developer, not resolved unilaterally

## B. Library registration

- [ ] `COD_BANCO_<X>` present in **both** contracts (Boleto and Pagamento)
- [ ] Entry in `Util`'s `$aBancos` map — proven by a real test going through `Factory::make()`
- [ ] `Util::controlePmisto` filled in (header **and** detail) if any test uses a fake return
- [ ] Bank-specific check digit implemented in `CalculoDV` and verified against a sample number from the manual
- [ ] `logos/<code>.png` exists

## C. File structure

- [ ] Every line exactly the layout's length (400 or 240)
- [ ] Line and file terminators as the bank requires (`\r\n` vs `\n`) — verified in bytes
- [ ] Record types in the right order (0/1/9 on 400; 0/1/3/5/9 on 240)
- [ ] File name follows the bank's required pattern (`nomeSugerido()`), where one exists
- [ ] Encoding correct, and no accents or special characters where the manual forbids them

## D. Counters and sequences

- [ ] Record sequence starts at 1 with no gaps — **counted on the generated lines**
- [ ] Sequence within a batch restarts per batch (240)
- [ ] Batch trailer matches the batch's real record count, including or excluding header and trailer exactly as the manual states
- [ ] File trailer matches the batch count and the total record count
- [ ] Per-batch and file amount totals match the sum of the details

## E. Account identification

- [ ] Branch, account and check digit in the manual's format: numeric zero-padded **or** alphanumeric space-padded — not assumed
- [ ] Beneficiary / assignor / agreement code confirmed with the developer (it is not always the checking account)
- [ ] Wallet: verified that the code sent in the remittance is the same one printed on the boleto
- [ ] The company identification field in the header assembled field by field, not as a single string

## F. "Nosso número" and barcode (collection)

- [ ] "Nosso número" plus check digit verified against a sample from the manual
- [ ] Non-numeric check digit handled (some banks can produce a letter) — either the layout accepts it, or the number is skipped, never truncated
- [ ] Number range / limit validated, where the bank has one
- [ ] Free field (positions 20-44) assembled field by field and verified against a real barcode
- [ ] `parseCampoLivre()` implemented and tested as the inverse of `getCampoLivre()`
- [ ] Typeable line verified with an external validator or a bank-provided sample

## G. Amounts, dates and documents

- [ ] Implied decimals correct in every amount field (the same amount can have different lengths in different segments)
- [ ] Date format verified **per record** (`dmy`, `dmY`, `Ymd` vary within the same file)
- [ ] Document type consistent with the length (11 = CPF, 14 = CNPJ) and in the manual's format (`1`/`2` or `01`/`02`)
- [ ] Identifiers (your number, control number, document number) respect the maximum length — validated, never silently truncated
- [ ] `seuNumero` carries the consuming application's identifier, not the remittance id

## H. Charges and benefits (collection)

- [ ] Fine: code + amount + grace period, using the manual's code (percentage ≠ fixed amount)
- [ ] Interest: chosen between monthly percentage and daily amount (`getMoraDia()`), per the manual's field
- [ ] Discount: mapped through `$tiposDescontoSuportados` plus the `AbstractRemessa` helpers; unsupported types raise an exception
- [ ] Discount paired with a date; an amount above zero with no date never reaches the file
- [ ] Layouts without a discount-type field use `resolveValorDescontoAbsoluto()`
- [ ] Rebate handled as its own field, distinct from discount
- [ ] Protest and automatic write-off: the bank's mutual-exclusion rule respected
- [ ] An instruction the layout cannot carry raises `ValidationException` explaining the alternative

## I. Occurrences and statuses (collection)

- [ ] All five statuses (`REGISTRO`, `BAIXA`, `ALTERACAO`, `ALTERACAO_DATA`, `ALTERACAO_VALOR`) mapped to the bank's codes
- [ ] `STATUS_CUSTOM` honored through `getComando()`
- [ ] No occurrence code hardcoded in the detail record
- [ ] Every code is a named constant, not a bare literal

## J. Payment — batches and modalities

- [ ] Service type and credit method taken from the manual's table, one per modality
- [ ] Correct segments per credit method (A+B, J+J-52, N, O…)
- [ ] Batch grouping rule confirmed: what defines a batch, what cannot share a batch, and what cannot share a **file**
- [ ] The bank's restrictions (e.g. PIX isolated, same-bank boletos in their own batch) implemented as `ValidationException`
- [ ] PIX: key type, transfer type, clearing house and initiation method per the manual
- [ ] Taxes / utility bills: credit method derived from the first digit of the collection barcode
- [ ] Collection barcode handled with its own rules, not the boleto's
- [ ] Optional segments emitted only when there is data for them

## K. Return files (collection and payment)

- [ ] The factory resolves the right class from the real file — tested
- [ ] **Every** occurrence code in the manual mapped to a canonical type, with an explicit fallback at the end
- [ ] No duplicated codes across the mapping lists
- [ ] Rejections carry both the reason code **and** its description
- [ ] Payment: all occurrence code pairs parsed, not just the first
- [ ] Unknown segments handled (log / `OUTROS`), never silently ignored
- [ ] Title amount, amount received and amount credited/settled kept distinct
- [ ] Barcode / typeable line rebuilt in the return checks out — or is left empty for lack of data

## L. Closing

- [ ] `phpunit --filter <Bank>` green, with the output pasted into the report
- [ ] Full suite green if `Abstract*`, `Util` or `Contracts` were touched
- [ ] No TODOs, skipped tests, empty tests or trivial asserts
- [ ] Report states: manual used, layers out of scope, fields not implemented, what depends on certification
- [ ] If the return was tested only against a generated fixture, that is **written down** in the report
- [ ] **New points added to this file** (protocol below)

---

## Protocol: recording a new point

At the end of every integration — and whenever an integration bug surfaces later — ask: *"what caught me here that would catch me again on the next bank?"*

Add a line to the matching group (or start a new group) that meets all three criteria:

1. **Generic** — it holds for the next bank, it doesn't describe one specific bank
2. **Verifiable** — it can be ticked by looking at a generated or parsed file
3. **Actionable** — it says what to do, not just what to avoid

```markdown
- [ ] <what to verify, in the imperative> — <why it exists, half a line>
```

If a point is too specific to generalize, it does not become an acceptance item: it becomes a comment in that bank's code, with the position and the manual section.

A point that proves unnecessary across three consecutive integrations may be removed — note the removal in the commit message.
