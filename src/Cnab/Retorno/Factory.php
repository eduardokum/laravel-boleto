<?php
/**
 *   Copyright (c) 2016 Eduardo Gusmão
 *
 *   Permission is hereby granted, free of charge, to any person obtaining a
 *   copy of this software and associated documentation files (the "Software"),
 *   to deal in the Software without restriction, including without limitation
 *   the rights to use, copy, modify, merge, publish, distribute, sublicense,
 *   and/or sell copies of the Software, and to permit persons to whom the
 *   Software is furnished to do so, subject to the following conditions:
 *
 *   The above copyright notice and this permission notice shall be included in all
 *   copies or substantial portions of the Software.
 *
 *   THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 *   INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR
 *   PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 *   COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 *   WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR
 *   IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace Eduardokum\LaravelBoleto\Cnab\Retorno;

use Eduardokum\LaravelBoleto\Contracts\Cnab\Cnab;
use Eduardokum\LaravelBoleto\Contracts\Cnab\Retorno\Cnab400;

class Factory
{

    /**
     * @param $file
     *
     * @return Retorno
     * @throws \Exception
     */
    public static function make($file)
    {
        if (file_exists($file)) {
            $file_content = file($file);
        } elseif (is_string($file)) {
            $file_content = explode(PHP_EOL, $file);
        } else {
            throw new \Exception("Arquivo: $file, não existe");
        }

        if (strlen(rtrim($file_content[0])) != '400') {
            throw new \Exception("Arquivo: $file, não é um arquivo CNAB 400 posições válido");
        }

        if (substr($file_content[0], 0, 9) != '02RETORNO') {
            throw new \Exception("Arquivo: $file, não é um arquivo de retorno");
        }

        $banco = substr($file_content[0], 76, 3);
        switch ($banco) {
            case Cnab::COD_BANCO_BB:
                $instancia = new Banco\Bb($file_content);
                break;
            case Cnab::COD_BANCO_SANTANDER:
                $instancia = new Banco\Santander($file_content);
                break;
            case Cnab::COD_BANCO_CEF:
                $instancia = new Banco\Caixa($file_content);
                break;
            case Cnab::COD_BANCO_BRADESCO:
                $instancia = new Banco\Bradesco($file_content);
                break;
            case Cnab::COD_BANCO_ITAU:
                $instancia = new Banco\Itau($file_content);
                break;
            case Cnab::COD_BANCO_HSBC:
                $instancia = new Banco\Hsbc($file_content);
                break;
            default:
                throw new \Exception("Banco: $banco, inválido");
        }

        return $instancia->processar();
    }
}
