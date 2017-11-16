Santander
=========

This bank has the following mandatory fields:

:numero: Bill number. (size: 12)
:codigoCliente: Account number. (size: 7)

**Available bank contracts:**

===  ================
101  Cobrança Simples
201  Penhor Rápida
===  ================

.. code-block:: php

    $santander = new Eduardokum\LaravelBoleto\Boleto\Banco\Santander;
    $santander->setLogo('/path/to/logo.png')
        ->setDataVencimento('1997-10-07')
        ->setValor('100')
        ->setNumero(1)
        ->setNumeroDocumento(1)
        ->setPagador($pagador)
        ->setBeneficiario($beneficiario)
        ->setCarteira(101)
        ->setCodigoCliente(2222222)
        ->setDescricaoDemonstrativo(['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'])
        ->setInstrucoes(['instrucao 1', 'instrucao 2', 'instrucao 3']);

    // You can add more ``Demonstrativos`` or ``Instrucoes`` on this way:

    $santander->addDescricaoDemonstrativo('demonstrativo 4');
    $santander->addInstrucoes('instrucao 2');

Or, Simply:

.. code-block:: php

    $santander = new Eduardokum\LaravelBoleto\Boleto\Banco\Santander([
        'logo' => '/path/to/logo.png',
        'dataVencimento' => '1997-10-07',
        'valor' => 100,
        'numero' => 1,
        'numeroDocumento' => 1,
        'pagador' => $pagador,
        'beneficiario' => $beneficiario,
        'carteira' => 101,
        'codigoCliente' => 2222222,
        'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
        'instrucoes' => ['instrucao 1', 'instrucao 2', 'instrucao 3'],
    ]);

.. ATTENTION::
    To render this object see the :ref:`render` session.
