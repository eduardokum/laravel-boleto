# Payment — CNAB 240 return file

`src/Cnab/Pagamento/Retorno/Cnab240/Banco/<X>.php`, resolved by `Cnab/Pagamento/Retorno/Factory` (bank code at positions 1-3; CNAB 240 only).

Implements `processarHeader()`, `processarHeaderLote()`, `processarDetalhe()`, `processarTrailerLote()`, `processarTrailer()`.

## Canonical state

`Cnab/Pagamento/Retorno/Cnab240/Detalhe` — occurrence types:

```
OCORRENCIA_PAGO       = 'pago'        paid
OCORRENCIA_REJEITADO  = 'rejeitado'   rejected
OCORRENCIA_PENDENTE   = 'pendente'    pending
OCORRENCIA_CANCELADO  = 'cancelado'   cancelled
OCORRENCIA_ERRO       = 'erro'        error
OCORRENCIA_OUTROS     = 'outros'      other
```

In a payment 240 the occurrence field usually arrives as a **list of concatenated 2-character codes** (Itaú packs up to 5 pairs into 10 positions). Each pair is a distinct reason:

- `00` (or `BD`, depending on the bank) = paid / settled
- the remaining pairs = rejections or pending states, each with its own description

So: **parse every pair**, feed `setRejeicoes(array)` with each code plus description, and set `setOcorrenciaTipo()` from the whole set — not from the first pair alone. Ignoring pairs 2..5 hides the real rejection reason.

## Fields to fill

Beyond the occurrence: `setDataPagamento`, `setDataEfetivacao`, `setValor`, `setValorRealEfetivado`, `setValorTarifa`, `setSeuNumero`, `setNossoNumero`, `setFavorecido` (bank, branch, account, check digit, name, document), `setCamara`, `setCodigoIspb`, `setFinalidadeTed`, `setTipoPagamento`, `setCodigoBarras`, `setValorTitulo`, `setDesconto`, `setAcrescimo`, `setInformacoesAdicionais`.

Date defaults on the payment `Detalhe` are `dmY` — confirm your bank's format before accepting them.

**Requested amount ≠ amount actually settled.** Banks pay partially or net of fees; reconciliation uses `valorRealEfetivado`.

## Segments in the return

The return mirrors the remittance's segments: A/B for transfers, J/J-52 for boletos, O for barcoded taxes, N for non-barcoded taxes. Each segment has its own positions — **a `switch` on the segment code (typically position 14 of the detail record) with one method per segment** keeps this readable. Itaú's segment O was implemented exactly this way (`3f65fec`).

A segment present in the file and left unhandled is a detail lost in silence. Handle the unknown with a log or `OCORRENCIA_OUTROS`, never with a mute `continue`.

## Your number ("seu número")

`seuNumero` is the **consuming application's unique identifier**, not the remittance id. Confusing the two breaks reconciliation on the consumer's side (`ecb3e10`). Write the payment's identifier in the remittance; return it through `setSeuNumero()` in the return.
