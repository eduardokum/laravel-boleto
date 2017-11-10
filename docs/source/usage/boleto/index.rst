.. _bill:

Boleto
======

All banks have information that is shared such as:

:logo: Path with logo image.
:dataVencimento: Date the bill expires.
:dataDocumento: Bill date.
:dataProcessamento: Creation date bill.
:valor: Bill amount.
:multa: Percentage of fee to be charged.
:juros: Percentage of interest to be charged.
:jurosApos: How many days after expired will be charged the interest.
:diasProtesto: How many days after expired will be protested.
:numero: Bill number. (Will be used to generate the ``Nosso Número``)
:numeroDocumento: Your internal bill number.
:numeroControle: Any control that you may have, the same one sent will be returned in :ref:`return`
:descricaoDemonstrativo: Texts that will be shown in the bill ``Demonostrativo`` field.
:instrucoes: Texts that will be shown in the bill ``Instruções ao caixa`` field.
:instrucoesImpressao: Texts that will be shown at the top of the bill with instructions for printing or anything else you want to communicate.
:aceite: If the bill is accepted by the customer
:especieDoc: Kind of bill. (Usually ``DM - Duplicata Mercantil``)
:pagador: The :ref:`payer`.
:beneficiario: The :ref:`recipient`

.. toctree::
    :caption: Special features of banks

    bancoob
    banrisul
    bancobrasil
    banconordeste
    bradesco
    caixa
    hsbc
    itau
    santander
    sicredi