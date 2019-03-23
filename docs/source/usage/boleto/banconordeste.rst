Banco do Nordeste
=================

This bank has the following mandatory fields:

:numero: Bill number. (size: 7)
:agencia: Account keeping agency. (size: 4)
:conta: Account number. (size: 7)

**Available bank contracts:**

==  =================================================
21  Cobrança Simples - Boleto Emitido Pelo Cliente
31  Cobrança Caucionada - Boleto Emitido Pelo Cliente
41  Cobrança Vinculada - Boleto Emitido Pelo Cliente
==  =================================================

.. code-block:: php

    $bnb = new Eduardokum\LaravelBoleto\Boleto\Banco\Bnb;
    $bnb->setLogo('/path/to/logo.png')
        ->setDataVencimento('1997-10-07')
        ->setValor('100')
        ->setNumero(1)
        ->setNumeroDocumento(1)
        ->setPagador($pagador)
        ->setBeneficiario($beneficiario)
        ->setCarteira(21)
        ->setAgencia(1111)
        ->setConta(22222)
        ->setDescricaoDemonstrativo(['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'])
        ->setInstrucoes(['instrucao 1', 'instrucao 2', 'instrucao 3']);

    // You can add more ``Demonstrativos`` or ``Instrucoes`` on this way:

    $bnb->addDescricaoDemonstrativo('demonstrativo 4');
    $bnb->addInstrucoes('instrucao 2');

Or, Simply:

.. code-block:: php

    $bnb = new Eduardokum\LaravelBoleto\Boleto\Banco\Bnb([
        'logo' => '/path/to/logo.png',
        'dataVencimento' => '1997-10-07',
        'valor' => 100,
        'numero' => 1,
        'numeroDocumento' => 1,
        'pagador' => $pagador,
        'beneficiario' => $beneficiario,
        'carteira' => 21,
        'agencia' => 1111,
        'conta' => 22222,
        'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
        'instrucoes' => ['instrucao 1', 'instrucao 2', 'instrucao 3'],
    ]);

.. ATTENTION::
    To render this object see the :ref:`render` session.
