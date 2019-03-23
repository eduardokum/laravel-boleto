Bancoob
=======

This bank has the following mandatory fields:

:agencia: Account keeping agency. (size: 4)
:conta: Account number. (size: 8)
:convenio: Number of agreement with the bank. (size: 7)

.. code-block:: php

    // for 400 positions
    $send = new Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400\Banco\Bancoob;

    // Or, for 240 positions
    $send = new Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab240\Banco\Bancoob;

    $send->setBeneficiario($beneficiario)
        ->setCarteira(1)
        ->setAgencia(1111)
        ->setConvenio(123123)
        ->setConta(22222);

Or, Simply:

.. code-block:: php

    $sendArray = [
        'beneficiario' => $beneficiario,
        'carteira' => 1,
        'agencia' => 1111,
        'convenio' => 123123,
        'conta' => 22222,
    ];

    // for 400 positions
    $send = new Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400\Banco\Bancoob($sendArray);

    // Or, for 240 positions
    $send = new Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab240\Banco\Bancoob($sendArray);

.. ATTENTION::
    To generate the file see the :ref:`send` session.