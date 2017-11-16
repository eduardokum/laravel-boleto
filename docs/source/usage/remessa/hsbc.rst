HSBC
====

This bank has the following mandatory fields:

:agencia: Account keeping agency. (size: 4)
:conta: Account number. (size: 6)
:contaDv: Account number verification code. (size: 1)

.. code-block:: php

    // for 400 positions
    $send = new Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400\Banco\Hsbc;

    // Or, for 240 positions
    $send = new Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab240\Banco\Hsbc;

    $send->setBeneficiario($beneficiario)
        ->setCarteira('CSB')
        ->setAgencia(1111)
        ->setConta(222222)
        ->setContaDv(2);

Or, Simply:

.. code-block:: php

    $sendArray = [
        'beneficiario' => $beneficiario,
        'carteira' => 'CSB',
        'agencia' => 1111,
        'conta' => 222222,
        'contaDv' => 2,
    ];

    // for 400 positions
    $send = new Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400\Banco\Hsbc($sendArray);

    // Or, for 240 positions
    $send = new Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab240\Banco\Hsbc($sendArray);

.. ATTENTION::
    To generate the file see the :ref:`send` session.