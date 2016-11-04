<?php
namespace Eduardokum\LaravelBoleto\Contracts\Boleto\Render;

Interface Pdf
{

    public function gerarBoleto($dest = self::OUTPUT_STANDARD, $save_path = null, $print = false);

}