Banco do Brasil
===============

This bank has the following mandatory fields:

:agencia: Account keeping agency. (size: 4)
:conta: Account number. (size: 8)
:convenio: Number of agreement with the bank. (size: 4, 6 and 7)
:variacaoCarteira: Bank contracts variation (size: 3) [optional]

.. code-block:: php

    // for 400 positions
    $send = new Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400\Banco\Bb;

    // Or, for 240 positions
    $send = new Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab240\Banco\Bb;

    $send->setBeneficiario($beneficiario)
        ->setCarteira(11)
        ->setAgencia(1111)
        ->setConvenio(1231237)
        // ->setVariacaoCarteira(017)
        ->setConta(22222);

Or, Simply:

.. code-block:: php

    $sendArray = [
        'beneficiario' => $beneficiario,
        'carteira' => 11,
        'agencia' => 1111,
        'convenio' => 1231237,
        // 'variacaoCarteira' => '017',
        'conta' => 22222,
    ];

    // for 400 positions
    $send = new Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400\Banco\Bb($sendArray);

    // Or, for 240 positions
    $send = new Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab240\Banco\Bb($sendArray);

.. ATTENTION::
    To generate the file see the :ref:`send` session.