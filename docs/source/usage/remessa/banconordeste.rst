Banco do Nordeste
=================
This bank has the following mandatory fields:

:agencia: Account keeping agency. (size: 4)
:conta: Account number. (size: 7)

.. code-block:: php

    // for 400 positions
    $send = new Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400\Banco\Bnb;

    // Or, for 240 positions
    $send = new Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab240\Banco\Bnb;

    $send->setBeneficiario($beneficiario)
        ->setCarteira(21)
        ->setAgencia(1111)
        ->setConta(22222);

Or, Simply:

.. code-block:: php

    $sendArray = [
        'beneficiario' => $beneficiario,
        'carteira' => 21,
        'agencia' => 1111,
        'conta' => 22222,
    ];

    // for 400 positions
    $send = new Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400\Banco\Bnb($sendArray);

    // Or, for 240 positions
    $send = new Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab240\Banco\Bnb($sendArray);

.. ATTENTION::
    To generate the file see the :ref:`send` session.