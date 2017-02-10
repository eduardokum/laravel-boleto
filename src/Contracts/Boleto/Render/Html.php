<?php
namespace Eduardokum\LaravelBoleto\Contracts\Boleto\Render;

interface Html
{
    public function writeCss();

    public function getImagemCodigoDeBarras($codigo_barras);

    public function gerarBoleto();
}
