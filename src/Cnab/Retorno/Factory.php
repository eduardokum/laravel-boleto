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
        if ($file == '') {
            throw new \Exception("file url is required.");
        } elseif (file_exists($file)) {
            $file_content = file($file);
        } elseif (is_string($file)) {
            $file_content = explode(PHP_EOL, $file);
        } else {
            throw new \Exception("Arquivo: $file, não existe");
        }

        if (!Util::isHeaderRetorno($file_content[0])) {
            throw new \Exception("Arquivo: $file, não é um arquivo de retorno");
        }

        $bancoClass = self::getBancoClass($banco);
        $instancia = new $bancoClass($file_content);
        return $instancia->processar();
    }

    /**
     * @param $banco
     *
     * @return mixed
     * @throws \Exception
     */
    private static function getBancoClass($banco) {

        if (Util::isCnab400($file_content)) {
            /**  Cnab 400 */
            $banco = substr($file_content[0], 76, 3);
            $namespace = __NAMESPACE__ . '\\Cnab400\\';
        } elseif (Util::isCnab240($file_content)) {
            /** Cnab 240 */
            $banco = substr($file_content[0], 0, 3);
            $namespace = __NAMESPACE__ . '\\Cnab240\\';
        }

        $aBancos = [
            BoletoContract::COD_BANCO_BB => 'Banco\\Bb',
            BoletoContract::COD_BANCO_SANTANDER => 'Banco\\Santander',
            BoletoContract::COD_BANCO_CEF => 'Banco\\Caixa',
            BoletoContract::COD_BANCO_BRADESCO => 'Banco\\Bradesco',
            BoletoContract::COD_BANCO_ITAU => 'Banco\\Itau',
            BoletoContract::COD_BANCO_HSBC => 'Banco\\Hsbc',
            BoletoContract::COD_BANCO_SICREDI => 'Banco\\Sicredi',
            BoletoContract::COD_BANCO_BANRISUL => 'Banco\\Banrisul',
        ];

        if(array_key_exists($banco, $aBancos)) {
            return $namespace.$aBancos[$banco];
        }

        throw new \Exception("Banco: $banco, inválido");
    }
}
