Sicredi
=======

This bank has the following mandatory fields:

:idremessa: Sequence number of send. (size: 7)
:agencia: Account keeping agency. (size: 4)
:conta: Account number. (size: 5)
:codigoCliente: Customer/Beneficiary code with the banking institution. Generally, the customer code is the same as the account number without the check digit, but in cases such as changing an account between branches, that number changes. (size: 5)

.. code-block:: php

    // for 400 positions
    $send = new Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400\Banco\Sicredi;

    // Or, for 240 positions
    $send = new Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab240\Banco\Sicredi;

    $send->setBeneficiario($beneficiario)
        ->setIdremessa(1)
        ->setCarteira(1)
        ->setAgencia(1111)
        ->setConta(22222)
        ->setCodigoCliente(12345);

Or, Simply:

.. code-block:: php

    $sendArray = [
        'beneficiario' => $beneficiario,
        'idremessa' => 1,
        'carteira' => 1,
        'agencia' => 1111,
        'conta' => 22222,
        'codigoCliente' => 12345,
    ];

    // for 400 positions
    $send = new Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400\Banco\Sicredi($sendArray);

    // Or, for 240 positions
    $send = new Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab240\Banco\Sicredi($sendArray);


.. ATTENTION::
    To generate the file see the :ref:`send` session.