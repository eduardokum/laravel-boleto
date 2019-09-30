Banrisul
========

This bank has the following mandatory fields:

:numero: Bill number. (size: 8)
:agencia: Account keeping agency. (size: 4)
:conta: Account number. (size: 5)

**Available bank contracts:**

=  ===========================================
1  Cobrança Simples
3  Cobrança Caucionada
4  Cobrança em IGPM
5  Cobrança Caucionada CGB Especial
6  Cobrança Simples Seguradora
7  Cobrança em UFIR
8  Cobrança em IDTR
C  Cobrança Vinculada
D  Cobrança CSB
E  Cobrança Caucionada Câmbio
F  Cobrança Vendor
H  Cobrança Caucionada Dólar
I  Cobrança Caucionada Compror
K  Cobrança Simples INCC-M
M  Cobrança Partilhada
N  Capital de Giro CGB ICM
R  Desconto de Duplicata
S  Vendor Eletrônico – Valor Final (Corrigido)
X  Vendor BDL – Valor Inicial (Valor da NF)
=  ===========================================

.. code-block:: php

    $banrisul = new Eduardokum\LaravelBoleto\Boleto\Banco\Banrisul;
    $banrisul->setLogo('/path/to/logo.png')
        ->setDataVencimento('1997-10-07')
        ->setValor('100')
        ->setNumero(1)
        ->setNumeroDocumento(1)
        ->setPagador($pagador)
        ->setBeneficiario($beneficiario)
        ->setCarteira(1)
        ->setAgencia(1111)
        ->setConta(22222)
        ->setDescricaoDemonstrativo(['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'])
        ->setInstrucoes(['instrucao 1', 'instrucao 2', 'instrucao 3']);

    // You can add more ``Demonstrativos`` or ``Instrucoes`` on this way:

    $banrisul->addDescricaoDemonstrativo('demonstrativo 4');
    $banrisul->addInstrucoes('instrucao 2');

Or, Simply:

.. code-block:: php

    $banrisul = new Eduardokum\LaravelBoleto\Boleto\Banco\Banrisul([
        'logo' => '/path/to/logo.png',
        'dataVencimento' => '1997-10-07',
        'valor' => 100,
        'numero' => 1,
        'numeroDocumento' => 1,
        'pagador' => $pagador,
        'beneficiario' => $beneficiario,
        'carteira' => 1,
        'agencia' => 1111,
        'conta' => 22222,
        'multa' => 1, // 1% do valor do boleto após o vencimento
        'juros' => 1, // 1% ao mês do valor do boleto
        'jurosApos' => 0 // quant. de dias para começar a cobrança de juros,
        'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
        'instrucoes' => ['instrucao 1', 'instrucao 2', 'instrucao 3'],
    ]);

.. ATTENTION::
    To render this object see the :ref:`render` session.
