.. _payer:

Pagador
=======

To create a ``Pagador`` you need to create an instance of the ``Pessoa`` object.

.. NOTE::
    ``Pagador`` is the person receiving the charge.

.. code-block:: php

    $pagador = new \Eduardokum\LaravelBoleto\Pessoa;
    $pagador->setDocumento('00.000.000/0000-00')
        ->setNome('Company co.')
        ->setCep('00000-000')
        ->setEndereco('Street name, 123')
        ->setBairro('district')
        ->setUf('UF')
        ->setCidade('City')
		->setOutros(['phone' => '(99) 99999-9999', 'email' => 'person@domain.com']);

Or, Simply:

.. code-block:: php

    $pagador = new \Eduardokum\LaravelBoleto\Pessoa([
        'documento' => '00.000.000/0000-00',
        'nome'      => 'Company co.',
        'cep'       => '00000-000',
        'endereco'  => 'Street name, 123',
        'bairro' => 'district',
        'uf'        => 'UF',
        'cidade'    => 'City',
		'outros'    => ['phone' => '+55 99 99999-9999', 'email' => 'person@domain.com'],
    ]);


.. ATTENTION::
    The :ref:`bill` requires an instance of ``Pagador``.