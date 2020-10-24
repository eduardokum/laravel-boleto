.. _bill:

Boleto
======

All banks have information that is shared such as:

:logo: Path with logo image. [default: Without logo]
:carteira: Bank contracts.
:dataVencimento: Date the bill expires.
:dataDesconto: Maximum date for granting the discount. [default: ``dataVencimento``]
:dataDocumento: Bill date. [default: today]
:dataProcessamento: Creation date bill. [default: today]
:desconto: Bill discount amount.
:valor: Bill amount.
:multa: Percentage of fee to be charged (per month). [default: false]
:juros: Percentage of interest to be charged (per month). [default: false]
:jurosApos: How many days after expired will be charged the interest. [default: 0]
:diasProtesto: How many days after expired will be protested. [default: 0]
:numero: Bill number. (Will be used to generate the ``Nosso Número``)
:numeroDocumento: Your internal bill number.
:numeroControle: Any control that you may have, the same one sent will be returned in :ref:`return`
:descricaoDemonstrativo: Texts that will be shown in the bill ``Demonostrativo`` field.
:instrucoes: Texts that will be shown in the bill ``Instruções ao caixa`` field.
:instrucoesImpressao: Texts that will be shown at the top of the bill with instructions for printing or anything else you want to communicate.
:aceite: If the bill is accepted by the customer. [default: N]
:especieDoc: Kind of bill. (Usually ``DM - Duplicata Mercantil``) [default: DM]
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
    render


.. seealso::

   `API bill docs <https://eduardokum.github.io/laravel-boleto/namespace-Eduardokum.LaravelBoleto.Boleto.html>`_
      Documentation for return objects.

   `Examples <https://github.com/eduardokum/laravel-boleto/tree/master/exemplos>`_
      Examples of use
