Banrisul
========

This bank has the following mandatory fields:

:agencia: Account keeping agency. (size: 4)
:conta: Account number. (size: 5)
:codigoCliente: Recipient number. (size: 13)
:codigoClienteOfficeBanking: Recipient number. (size: 10) [required when Bank contract is 'R', 'S', 'X']

.. code-block:: php

    // for 400 positions
    $send = new Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400\Banco\Banrisul;

    // Or, for 240 positions
    $send = new Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab240\Banco\Banrisul;

    $send->setBeneficiario($beneficiario)
        ->setCarteira(1)
        ->setAgencia(1111)
        ->setCodigoCliente(1234567)
        // ->setCodigoClienteOfficeBanking(1234567890)
        ->setConta(22222);

Or, Simply:

.. code-block:: php

    $sendArray = [
        'beneficiario' => $beneficiario,
        'carteira' => 1,
        'agencia' => 1111,
        'codigoCliente' => 1234567,
        // 'codigoClienteOfficeBanking' => '1234567890',
        'conta' => 22222,
    ];

    // for 400 positions
    $send = new Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400\Banco\Banrisul($sendArray);

    // Or, for 240 positions
    $send = new Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab240\Banco\Banrisul($sendArray);

.. ATTENTION::
    To generate the file see the :ref:`send` session.