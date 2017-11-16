Caixa Econônica Federal
=======================

This bank has the following mandatory fields:

:idremessa: Sequence number of send. (size: 5)
:agencia: Account keeping agency. (size: 4)
:codigoCliente: Recipient number. (size: 6)

.. code-block:: php

    // for 400 positions
    $send = new Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400\Banco\Caixa;

    // Or, for 240 positions
    $send = new Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab240\Banco\Caixa;

    $send->setBeneficiario($beneficiario)
        ->setIdremessa(1)
        ->setCarteira('RG')
        ->setAgencia(1111)
        ->setCodigoCliente(222222);

Or, Simply:

.. code-block:: php

    $sendArray = [
        'beneficiario' => $beneficiario,
        'idremessa' => 1,
        'carteira' => 'RG',
        'agencia' => 1111,
        'codigoCliente' => 222222,
    ];

    // for 400 positions
    $send = new Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400\Banco\Caixa($sendArray);

    // Or, for 240 positions
    $send = new Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab240\Banco\Caixa($sendArray);

.. ATTENTION::
    To generate the file see the :ref:`send` session.