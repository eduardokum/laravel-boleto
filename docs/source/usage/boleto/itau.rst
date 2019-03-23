Itáu
====

This bank has the following mandatory fields:

:numero: Bill number. (size: 8)
:agencia: Account keeping agency. (size: 4)
:conta: Account number. (size: 5)

**Available bank contracts:**

===  ==========================================
109  Direta Eletrônica Sem Emissão - Simples
110  Direta Eletrônica Sem Emissão - Simples
111  Direta Eletrônica Sem Emissão - Simples
112  Escritural Eletrônica - simples / contratual
115  Carteira 115
121  Direta Eletrônica Emissão Parcial - Simples/Contra
188  Carteira 188
180  Direta Eletrônica Emissão Integral
===  ==========================================

.. code-block:: php

    $itau = new Eduardokum\LaravelBoleto\Boleto\Banco\Itau;
    $itau->setLogo('/path/to/logo.png')
        ->setDataVencimento('1997-10-07')
        ->setValor('100')
        ->setNumero(1)
        ->setNumeroDocumento(1)
        ->setPagador($pagador)
        ->setBeneficiario($beneficiario)
        ->setCarteira(109)
        ->setAgencia(1111)
        ->setConta(22222)
        ->setDescricaoDemonstrativo(['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'])
        ->setInstrucoes(['instrucao 1', 'instrucao 2', 'instrucao 3']);

    // You can add more ``Demonstrativos`` or ``Instrucoes`` on this way:

    $itau->addDescricaoDemonstrativo('demonstrativo 4');
    $itau->addInstrucoes('instrucao 2');

Or, Simply:

.. code-block:: php

    $itau = new Eduardokum\LaravelBoleto\Boleto\Banco\Itau([
        'logo' => '/path/to/logo.png',
        'dataVencimento' => '1997-10-07',
        'valor' => 100,
        'numero' => 1,
        'numeroDocumento' => 1,
        'pagador' => $pagador,
        'beneficiario' => $beneficiario,
        'carteira' => 109,
        'agencia' => 1111,
        'conta' => 22222,
        'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
        'instrucoes' => ['instrucao 1', 'instrucao 2', 'instrucao 3'],
    ]);

.. ATTENTION::
    To render this object see the :ref:`render` session.
