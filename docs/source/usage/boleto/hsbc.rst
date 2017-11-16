HSBC
====

This bank has the following mandatory fields:

:numero: Bill number. (size: 5)
:agencia: Account keeping agency. (size: 4)
:conta: Account number. (size: 6)
:range: Range to start bill number. (size: 5)
:contaDv: Account number verification code. (size: 1)

**Available bank contracts:**

===  ============
CSB  Carteira CSB
===  ============

.. code-block:: php

    $hsbc = new Eduardokum\LaravelBoleto\Boleto\Banco\Hsbc;
    $hsbc->setLogo('/path/to/logo.png')
        ->setDataVencimento('1997-10-07')
        ->setValor('100')
        ->setNumero(1)
        ->setNumeroDocumento(1)
        ->setPagador($pagador)
        ->setBeneficiario($beneficiario)
        ->setCarteira('CSB')
        ->setAgencia(1111)
        ->setConta(222222)
        ->setContaDv(2)
        ->setRange(99999)
        ->setDescricaoDemonstrativo(['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'])
        ->setInstrucoes(['instrucao 1', 'instrucao 2', 'instrucao 3']);

    // You can add more ``Demonstrativos`` or ``Instrucoes`` on this way:

    $hsbc->addDescricaoDemonstrativo('demonstrativo 4');
    $hsbc->addInstrucoes('instrucao 2');

Or, Simply:

.. code-block:: php

    $hsbc = new Eduardokum\LaravelBoleto\Boleto\Banco\Hsbc([
        'logo' => '/path/to/logo.png',
        'dataVencimento' => '1997-10-07',
        'valor' => 100,
        'numero' => 1,
        'numeroDocumento' => 1,
        'pagador' => $pagador,
        'beneficiario' => $beneficiario,
        'carteira' => 'CSB',
        'agencia' => 1111,
        'conta' => 222222,
        'contaDv' => 2,
        'range' => 99999,
        'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
        'instrucoes' => ['instrucao 1', 'instrucao 2', 'instrucao 3'],
    ]);

.. ATTENTION::
    To render this object see the :ref:`render` session.
