Bradesco
========

This bank has the following mandatory fields:

:idremessa: Sequence number of send. (size: 7)
:agencia: Account keeping agency. (size: 4)
:conta: Account number. (size: 7)
:codigoCliente: Recipient number. (size: 20). [optional, if not pass, The class will automatically generate]

.. code-block:: php

    // for 400 positions
    $send = new Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400\Banco\Bradesco;

    // Or, for 240 positions
    $send = new Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab240\Banco\Bradesco;

    $send->setBeneficiario($beneficiario)
        ->setIdremessa(1)
        ->setCarteira('09')
        ->setAgencia(1111)
        ->setCodigoCliente('12345678901234567890')
        ->setConta(2222222);

Or, Simply:

.. code-block:: php

    $sendArray = [
        'beneficiario' => $beneficiario,
        'idremessa' => 1,
        'carteira' => '09',
        'agencia' => 1111,
        'codigoCliente' => 12345678901234567890,
        'conta' => 2222222,
    ];

    // for 400 positions
    $send = new Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400\Banco\Bradesco($sendArray);

    // Or, for 240 positions
    $send = new Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab240\Banco\Bradesco($sendArray);

.. ATTENTION::
    To generate the file see the :ref:`send` session.