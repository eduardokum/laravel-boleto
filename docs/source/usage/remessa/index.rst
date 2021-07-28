.. _send:

Remessa
=======

There are 2 types of ``Remessas``: 240 positions and 400 positions.

**Options available by bank:**

=========================  ====  ====
Banco                      240   400
=========================  ====  ====
Bancoob                    yes   yes
Banrisul                   yes*  yes
Banco do Brasil            yes*  yes
Banco do Nordeste          no    yes*
Bradesco                   yes*  yes
Caixa Econônica Federal    yes*  yes
HSBC                       no    yes
Itaú                       yes*  yes
Santander                  yes*  yes
Sicredi                    yes*  yes
=========================  ====  ====

.. note::
    *** requires homologation**

All banks have information that is shared such as:

:carteira: Bank contracts.
:beneficiario: The :ref:`recipient`

All banks have the same methods for file generation. What changes are the fields required by each bank:

.. code-block:: php

    // Add a single bill to a send object. Here need a instance of Boleto.
    $send->addBoleto(BoletoContract $detalhe);

    // Add multiples bill to a send object. Here need a array of instances of Boleto.
    $send->addBoletos(BoletoContract[] $boletos);

    // Return a string of file.
    // It depends on the instance, 240 or 400 positions.
    $send->gerar();

    // Saves the string to a file on the disk whose path was passed in $path argument.
    $send->save($path);

    // Force file download.
    // If you pass the $filename argument it overwrites the name in the download.
    $send->download($filename = null);


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


.. seealso::

   `API send docs <https://eduardokum.github.io/laravel-boleto/namespace-Eduardokum.LaravelBoleto.Cnab.Remessa.html>`_
      Documentation for return objects.

   `Examples <https://github.com/eduardokum/laravel-boleto/tree/master/exemplos>`_
      Examples of use
