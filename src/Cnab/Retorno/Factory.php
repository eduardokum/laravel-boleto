<?php
namespace Eduardokum\LaravelBoleto\Cnab\Retorno;

use Eduardokum\LaravelBoleto\Contracts\Cnab\Cnab;
use Eduardokum\LaravelBoleto\Contracts\Cnab\Retorno;
use Eduardokum\LaravelBoleto\Util;

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
        if ($file == '') {
            throw new \Exception("file url is required.");
        } elseif (file_exists($file)) {
            $file_content = file($file);
        } elseif (is_string($file)) {
            $file_content = explode(PHP_EOL, $file);
        } else {
            throw new \Exception("Arquivo: $file, não existe");
        }

        if (!Util::isCnab400($file_content) && !Util::isCnab240($file_content)) {

            throw new \Exception("Formato do arquivo não identificado.");

        } elseif (Util::isCnab400($file_content)) {

            /**  Cnab 400 */

            if (substr($file_content[0], 0, 9) != '02RETORNO') {
                throw new \Exception("Arquivo: $file, não é um arquivo de retorno");
            }

            $banco = substr($file_content[0], 76, 3);

            switch ($banco) {
                case Cnab::COD_BANCO_BB:
                    $instancia = new Cnab400\Banco\Bb($file_content);
                    break;
                case Cnab::COD_BANCO_SANTANDER:
                    $instancia = new Cnab400\Banco\Santander($file_content);
                    break;
                case Cnab::COD_BANCO_CEF:
                    $instancia = new Cnab400\Banco\Caixa($file_content);
                    break;
                case Cnab::COD_BANCO_BRADESCO:
                    $instancia = new Cnab400\Banco\Bradesco($file_content);
                    break;
                case Cnab::COD_BANCO_ITAU:
                    $instancia = new Cnab400\Banco\Itau($file_content);
                    break;
                case Cnab::COD_BANCO_HSBC:
                    $instancia = new Cnab400\Banco\Hsbc($file_content);
                    break;
                default:
                    throw new \Exception("Banco: $banco, inválido");
            }

        } else if (Util::isCnab240($file_content)) {

            /** Cnab 240 */

            if (substr($file_content[0], 142, 1) != '2') {
                throw new \Exception("Arquivo: $file, não é um arquivo retorno");
            }

            $banco = substr($file_content[0], 0, 3);

            switch ($banco) {
                case Cnab::COD_BANCO_SANTANDER:
                    $instancia = new Cnab240\Banco\Santander($file_content);
                    break;
                default:
                    throw new \Exception("Banco: $banco, inválido");
            }

        }

        return $instancia->processar();

    }
}
