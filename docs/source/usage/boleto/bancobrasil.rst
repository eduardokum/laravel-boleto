Banco do Brasil
===============

This bank has the following mandatory fields:

:numero: Bill number.

    .. hlist::
        :columns: 1

        *  ``convenio 4`` (size: 7)
        *  ``convenio 6`` (size: 5)
        *  ``convenio 7`` (size: 10)
:agencia: Account keeping agency. (size: 4)
:conta: Account number. (size: 8)
:convenio: Number of agreement with the bank. (size: 4, 6 and 7)
:variacaoCarteira: Bank contracts variation (size: 3)

**Available bank contracts:**

==  ========================================
11  Cobrança com registro Simples
12  Cobrança com registro Indexada
15  Cobrança com registro  Prêmios de Seguro
17  Cobrança com registro Direta Especial
18  Cobrança com registro Simples
31  Cobrança com registro Caucionada
51  Cobrança com registro Descontada
==  ========================================

.. code-block:: php

    $bb = new Eduardokum\LaravelBoleto\Boleto\Banco\Bb;
    $bb->setLogo('/path/to/logo.png')
        ->setDataVencimento('1997-10-07')
        ->setValor('100')
        ->setNumero(1)
        ->setNumeroDocumento(1)
        ->setPagador($pagador)
        ->setBeneficiario($beneficiario)
        ->setCarteira(11)
        ->setAgencia(1111)
        ->setConvenio(1231237)
        ->setConta(22222)
        ->setDescricaoDemonstrativo(['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'])
        ->setInstrucoes(['instrucao 1', 'instrucao 2', 'instrucao 3']);

    // You can add more ``Demonstrativos`` or ``Instrucoes`` on this way:

    $bb->addDescricaoDemonstrativo('demonstrativo 4');
    $bb->addInstrucoes('instrucao 2');

Or, Simply:

.. code-block:: php

    $bb = new Eduardokum\LaravelBoleto\Boleto\Banco\Bb([
        'logo' => '/path/to/logo.png',
        'dataVencimento' => '1997-10-07',
        'valor' => 100,
        'numero' => 1,
        'numeroDocumento' => 1,
        'pagador' => $pagador,
        'beneficiario' => $beneficiario,
        'carteira' => 11,
        'agencia' => 1111,
        'convenio' => 1231237,
        'conta' => 22222,
        'multa' => 1, // 1% do valor do boleto após o vencimento
        'juros' => 1, // 1% ao mês do valor do boleto
        'jurosApos' => 0 // quant. de dias para começar a cobrança de juros,
        'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
        'instrucoes' => ['instrucao 1', 'instrucao 2', 'instrucao 3'],
    ]);

.. ATTENTION::
    To render this object see the :ref:`render` session.
