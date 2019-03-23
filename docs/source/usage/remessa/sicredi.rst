Sicredi
=======

This bank has the following mandatory fields:

:idremessa: Sequence number of send. (size: 7)
:agencia: Account keeping agency. (size: 4)
:conta: Account number. (size: 5)

.. code-block:: php

    // for 400 positions
    $send = new Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400\Banco\Sicredi;

    // Or, for 240 positions
    $send = new Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab240\Banco\Sicredi;

    $send->setBeneficiario($beneficiario)
        ->setIdremessa(1)
        ->setCarteira(1)
        ->setAgencia(1111)
        ->setConta(22222);

Or, Simply:

.. code-block:: php

    $sendArray = [
        'beneficiario' => $beneficiario,
        'idremessa' => 1,
        'carteira' => 1,
        'agencia' => 1111,
        'conta' => 22222,
    ];

    // for 400 positions
    $send = new Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400\Banco\Sicredi($sendArray);

    // Or, for 240 positions
    $send = new Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab240\Banco\Sicredi($sendArray);


.. ATTENTION::
    To generate the file see the :ref:`send` session.