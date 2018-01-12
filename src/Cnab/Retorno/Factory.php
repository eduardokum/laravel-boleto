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

        $aBancos = [
            BoletoContract::COD_BANCO_BB => 'Banco\\Bb',
            BoletoContract::COD_BANCO_SANTANDER => 'Banco\\Santander',
            BoletoContract::COD_BANCO_CEF => 'Banco\\Caixa',
            BoletoContract::COD_BANCO_BRADESCO => 'Banco\\Bradesco',
            BoletoContract::COD_BANCO_ITAU => 'Banco\\Itau',
            BoletoContract::COD_BANCO_HSBC => 'Banco\\Hsbc',
            BoletoContract::COD_BANCO_SICREDI => 'Banco\\Sicredi',
            BoletoContract::COD_BANCO_BANRISUL => 'Banco\\Banrisul',
            BoletoContract::COD_BANCO_BANCOOB => 'Banco\\Bancoob',
            BoletoContract::COD_BANCO_BNB => 'Banco\\Bnb',
        ];

        if (array_key_exists($banco, $aBancos)) {
            $bancoClass = $namespace . Util::getBancoClass($banco);
            return new $bancoClass($file_content);
        }

        throw new \Exception("Banco: $banco, inválido");
    }
}
