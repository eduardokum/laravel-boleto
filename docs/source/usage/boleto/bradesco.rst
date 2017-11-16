Bradesco
========

This bank has the following mandatory fields:

:numero: Bill number. (size: 11)
:agencia: Account keeping agency. (size: 4)
:conta: Account number. (size: 7)
:cip: Code used to identify specific messages to the recipient. [default: 000] (size: 3)

**Available bank contracts:**

==  ==========================================
09  Com Registro
21  Com Registro - Pagável somente no Bradesco
26  Com Registro – Emissão na Internet
==  ==========================================

.. code-block:: php

    $bradesco = new Eduardokum\LaravelBoleto\Boleto\Banco\Bradesco;
    $bradesco->setLogo('/path/to/logo.png')
        ->setDataVencimento('1997-10-07')
        ->setValor('100')
        ->setNumero(1)
        ->setNumeroDocumento(1)
        ->setPagador($pagador)
        ->setBeneficiario($beneficiario)
        ->setCarteira('09')
        ->setAgencia(1111)
        ->setConta(2222222)
        ->setDescricaoDemonstrativo(['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'])
        ->setInstrucoes(['instrucao 1', 'instrucao 2', 'instrucao 3']);

    // You can add more ``Demonstrativos`` or ``Instrucoes`` on this way:

    $bradesco->addDescricaoDemonstrativo('demonstrativo 4');
    $bradesco->addInstrucoes('instrucao 2');

Or, Simply:

.. code-block:: php

    $bradesco = new Eduardokum\LaravelBoleto\Boleto\Banco\Bradesco([
        'logo' => '/path/to/logo.png',
        'dataVencimento' => '1997-10-07',
        'valor' => 100,
        'numero' => 1,
        'numeroDocumento' => 1,
        'pagador' => $pagador,
        'beneficiario' => $beneficiario,
        'carteira' => '09',
        'agencia' => 1111,
        'conta' => 2222222,
        'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
        'instrucoes' => ['instrucao 1', 'instrucao 2', 'instrucao 3'],
    ]);

.. ATTENTION::
    To render this object see the :ref:`render` session.
