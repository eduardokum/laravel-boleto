Utilities
=========

isCnab240
^^^^^^^^^


Test if a content is a cnab 240 positions valid.

.. code-block:: php

    Util::isCnab240($content)


isCnab400
^^^^^^^^^

Test if a content is a cnab 400 positions valid.

.. code-block:: php

    Util::isCnab400($content)


IPTE2CodigoBarras
^^^^^^^^^^^^^^^^^

Converts a digitable line from the bill to the string that generates the bar code

.. code-block:: php

    Util::IPTE2CodigoBarras($ipte)


IPTE2Variveis
^^^^^^^^^^^^^

It parses the bill's digitable line, extracting the contained variables

.. code-block:: php

    Util::IPTE2Variveis($ipte)


array2Controle
^^^^^^^^^^^^^^

Converts an array to a control string that can be passed in the bill.

.. code-block:: php

    Util::array2Controle(array $array)
    // Example
    $array = [
        'A' => 47885,
        'B' => 212,
        'C' => 9598,
    ];
    Util::array2Controle($array);
    // will return
    A47885B212C9598


controle2array
^^^^^^^^^^^^^^

It parses the bill's digitable line, extracting the contained variables

.. code-block:: php

    Util::controle2array($control)
    // Example
    Util::controle2array('A47885B212C9598')
    // will return
    [
        'A' => 47885,
        'B' => 212,
        'C' => 9598,
    ]


fatorVencimento
^^^^^^^^^^^^^^^

Converts a date to the expiration factor

.. code-block:: php

    Util::fatorVencimento($date, $format = 'Y-m-d')
    // Example
    Util::fatorVencimento('2018-01-01')
    // will return
    7391



fatorVencimentoBack
^^^^^^^^^^^^^^^^^^^

Converts an expiration factor to the corresponding date.

.. code-block:: php

    Util::fatorVencimentoBack($factor, $format = 'Y-m-d')
    // Example
    Util::fatorVencimentoBack('7391')
    // will return
    '2018-01-01'

    // or
    Util::fatorVencimentoBack('7391', false)
    // will return a instance of Carbon.
    Carbon\Carbon Object
    (
        [date] => 2018-01-01 00:00:00.000000
        [timezone_type] => 3
        [timezone] => America/Sao_Paulo
    )