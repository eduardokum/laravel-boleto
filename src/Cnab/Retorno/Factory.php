<?php
namespace Eduardokum\LaravelBoleto\Cnab\Retorno;

use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;
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
        if (!$file_content = Util::file2array($file)) {
            throw new \Exception("Arquivo: não existe");
        }

        if (!Util::isHeaderRetorno($file_content[0])) {
            throw new \Exception("Arquivo: $file, não é um arquivo de retorno");
        }

        $instancia = self::getBancoClass($file_content);
        return $instancia->processar();
    }

    /**
     * @param $file_content
     *
     * @return mixed
     * @throws \Exception
     */
    private static function getBancoClass($file_content)
    {
        $banco = '';
        $namespace = '';
        if (Util::isCnab400($file_content)) {
            $banco = mb_substr($file_content[0], 76, 3);
            $namespace = __NAMESPACE__ . '\\Cnab400\\';
        } elseif (Util::isCnab240($file_content)) {
            $banco = mb_substr($file_content[0], 0, 3);
            $namespace = __NAMESPACE__ . '\\Cnab240\\';
        }

        $bancoClass = $namespace . Util::getBancoClass($banco);

        if (!class_exists($bancoClass)) {
            throw new \Exception("Banco não possui essa versão de CNAB");
        }

        return new $bancoClass($file_content);
    }
}
