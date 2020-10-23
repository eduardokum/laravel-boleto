Sicredi
=======

This bank has the following mandatory fields:

:numero: Bill number. (size: 5)
:agencia: Account keeping agency. (size: 4)
:conta: Account number. (size: 5)
:codigoCliente: Customer/Beneficiary code with the banking institution. Generally, the customer code is the same as the account number without the check digit, but in cases such as changing an account between branches, that number changes. (size: 5)
:byte: Byte of our Number Generation. (size: 1)
:posto: ``Posto`` provided by the bank. (size: 2)

**Available bank contracts:**

=  ==========
1  Simples
2  Caucionada
3  Descontada
=  ==========

.. code-block:: php

    $sicredi = new Eduardokum\LaravelBoleto\Boleto\Banco\Sicredi;
    $sicredi->setLogo('/path/to/logo.png')
        ->setDataVencimento('1997-10-07')
        ->setValor('100')
        ->setNumero(1)
        ->setNumeroDocumento(1)
        ->setPagador($pagador)
        ->setBeneficiario($beneficiario)
        ->setCarteira(1)
        ->setPosto(11)
        ->setByte(2)
        ->setAgencia(1111)
        ->setConta(22222)
        ->setCodigoCliente(12345)
        ->setDescricaoDemonstrativo(['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'])
        ->setInstrucoes(['instrucao 1', 'instrucao 2', 'instrucao 3']);

    // You can add more ``Demonstrativos`` or ``Instrucoes`` on this way:

    $sicredi->addDescricaoDemonstrativo('demonstrativo 4');
    $sicredi->addInstrucoes('instrucao 2');

Or, Simply:

.. code-block:: php

    $sicredi = new Eduardokum\LaravelBoleto\Boleto\Banco\Sicredi([
        'logo' => '/path/to/logo.png',
        'dataVencimento' => '1997-10-07',
        'valor' => 100,
        'numero' => 1,
        'numeroDocumento' => 1,
        'pagador' => $pagador,
        'beneficiario' => $beneficiario,
        'carteira' => 1,
        'posto' => 11,
        'byte' => 2,
        'agencia' => 1111,
        'conta' => 22222,
        'codigoCliente' => 12345,
        'multa' => 1, // 1% do valor do boleto após o vencimento
        'juros' => 1, // 1% ao mês do valor do boleto
        'jurosApos' => 0 // quant. de dias para começar a cobrança de juros,
        'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
        'instrucoes' => ['instrucao 1', 'instrucao 2', 'instrucao 3'],
    ]);

.. ATTENTION::
    To render this object see the :ref:`render` session.

