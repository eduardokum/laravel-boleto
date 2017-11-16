Santander
=========

This bank has the following mandatory fields:

:agencia: Account keeping agency. (size: 4)
:conta: Account number. (size: 8)
:codigoCliente: Account number. (size: 7)

.. code-block:: php

    // for 400 positions
    $send = new Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400\Banco\Santander;

    // Or, for 240 positions
    $send = new Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab240\Banco\Santander;

    $send->setBeneficiario($beneficiario)
        ->setCarteira(101)
        ->setAgencia(1111)
        ->setCodigoCliente(2222222)
        ->setConta(22222222);

Or, Simply:

.. code-block:: php

    $sendArray = [
        'beneficiario' => $beneficiario,
        'carteira' => 101,
        'agencia' => 1111,
        'codigoCliente' => 2222222,
        'conta' => 22222222,
    ];

    // for 400 positions
    $send = new Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400\Banco\Santander($sendArray);

    // Or, for 240 positions
    $send = new Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab240\Banco\Santander($sendArray);

.. ATTENTION::
    To generate the file see the :ref:`send` session.