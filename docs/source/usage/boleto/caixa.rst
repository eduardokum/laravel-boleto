Caixa Econônica Federal
=======================

This bank has the following mandatory fields:

:numero: Bill number. (size: 15)
:agencia: Account keeping agency. (size: 4)
:codigoCliente: Recipient number. (size: 6)

**Available bank contracts:**

==  ============
RG  Com Registro
==  ============

.. code-block:: php

    $caixa = new Eduardokum\LaravelBoleto\Boleto\Banco\Caixa;
    $caixa->setLogo('/path/to/logo.png')
        ->setDataVencimento('1997-10-07')
        ->setValor('100')
        ->setNumero(1)
        ->setNumeroDocumento(1)
        ->setPagador($pagador)
        ->setBeneficiario($beneficiario)
        ->setCarteira('RG')
        ->setAgencia(1111)
        ->setCodigoCliente(222222)
        ->setDescricaoDemonstrativo(['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'])
        ->setInstrucoes(['instrucao 1', 'instrucao 2', 'instrucao 3']);

    // You can add more ``Demonstrativos`` or ``Instrucoes`` on this way:

    $caixa->addDescricaoDemonstrativo('demonstrativo 4');
    $caixa->addInstrucoes('instrucao 2');

Or, Simply:

.. code-block:: php

    $caixa = new Eduardokum\LaravelBoleto\Boleto\Banco\Caixa([
        'logo' => '/path/to/logo.png',
        'dataVencimento' => '1997-10-07',
        'valor' => 100,
        'numero' => 1,
        'numeroDocumento' => 1,
        'pagador' => $pagador,
        'beneficiario' => $beneficiario,
        'carteira' => 'RG',
        'agencia' => 1111,
        'codigoCliente' => 222222,
        'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
        'instrucoes' => ['instrucao 1', 'instrucao 2', 'instrucao 3'],
    ]);

.. ATTENTION::
    To render this object see the :ref:`render` session.
