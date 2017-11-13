.. _render:

Render
======

There are 2 ways to render the bill

1. In the :ref:`bill` object itself there are the ``renderPDF()`` and ``renderHTML()`` methods that render it individual.
2. Instantiating a rendering class by adding the desired amount of tickets and calling the ``render()`` method;


See below the explanation of each one of them:

.. _pdf:

PDF
---

To render in PDF you first need a :ref:`bill` instance.

Render individually
^^^^^^^^^^^^^^^^^^^

.. code-block:: php

    $boleto->renderPDF($print = false, $instrucoes = true);

    // This will generate a PDF string
    $boleto->renderPDF();

    // If you want to show a print window after rendering pass true on the first argument
    $boleto->renderPDF(true);

    // If you want to hide the print instructions pass false in the second argument
    $boleto->renderPDF(false, false);

Render multiple
^^^^^^^^^^^^^^^

.. code-block:: php

    $pdf = new Eduardokum\LaravelBoleto\Boleto\Render\Pdf();

    // Add as many bills as you want.
    $pdf->addBoleto($boleto);

    // Or, Simply
    $pdf->addBoletos([
        $boleto1,
        $boleto2,
        $boleto3,
    ]);

    // If you want to show a print window after rendering.
    $pdf->showPrint();

    // If you want to hide the print instructions.
    $pdf->hideInstrucoes();

    // To Render
    $pdf->gerarBoleto($dest = self::OUTPUT_STANDARD, $save_path = null);

**Options available for PDF destinations:**

====================  ==================================================================
Pdf::OUTPUT_STANDARD  Return a PDF with headers.
Pdf::OUTPUT_DOWNLOAD  Force download.
Pdf::OUTPUT_SAVE      Save PDF on disk, require a second parameter with a ``save_path``.
Pdf::OUTPUT_STRING    Return a PDF string.
====================  ==================================================================


.. _html:

HTML
----

To render in HTML you first need a :ref:`bill` instance.

Render individually
^^^^^^^^^^^^^^^^^^^

.. code-block:: php

    $boleto->renderHTML($print = false, $instrucoes = true);

    // This will generate a HTML string.
    $boleto->renderHTML();

    // If you want to show a print window after rendering pass true on the first argument.
    $boleto->renderHTML(true);

    // If you want to hide the print instructions pass false in the second argument.
    $boleto->renderHTML(false, false);

Render multiple
^^^^^^^^^^^^^^^

.. code-block:: php

    $html = new Eduardokum\LaravelBoleto\Boleto\Render\Html();

    // Add as many bills as you want
    $html->addBoleto($boleto);

    // Or, Simply
    $html->addBoletos([
        $boleto1,
        $boleto2,
        $boleto3,
    ]);

    // If you want to show a print window after rendering.
    $html->showPrint();

    // If you want to hide the print instructions.
    $html->hideInstrucoes();

    // To Render, this will return a html string.
    $html->gerarBoleto();

    // Html also provides a ``carnê`` as a form of rendering.
    $html->gerarCarne();