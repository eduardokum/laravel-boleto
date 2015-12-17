<?php
namespace Eduardokum\LaravelBoleto\Cnab\Retorno;

class Factory
{

    /**
     * @param $file
     *
     * @return Banco\Bb|Banco\Bradesco|Banco\Caixa|Banco\Hsbc|Banco\Itau|Banco\Santander
     * @throws \Exception
     */
    public static function make($file)
    {
        if(file_exists($file))
        {
            $file_content = file($file);
        }
        else
        {
            throw new \Exception("Arquivo: $file, não existe");
        }

        if( strlen(rtrim($file_content[0])) != '400' ) {
            throw new \Exception("Arquivo: $file, não é um arquivo CNAB 400 posições válido");
        }

        if( substr( $file_content[0], 0, 9) != '02RETORNO' ) {
            throw new \Exception("Arquivo: $file, não é um arquivo de retorno");
        }

        $banco = substr( $file_content[0], 76, 3);

        switch($banco)
        {
            case AbstractCnab::COD_BANCO_BB:
                return new Banco\Bb($file_content);
                break;
            case AbstractCnab::COD_BANCO_SANTANDER:
                return new Banco\Santander($file_content);
                break;
            case AbstractCnab::COD_BANCO_CEF:
                return new Banco\Caixa($file_content);
                break;
            case AbstractCnab::COD_BANCO_BRADESCO:
                return new Banco\Bradesco($file_content);
                break;
            case AbstractCnab::COD_BANCO_ITAU:
                return new Banco\Itau($file_content);
                break;
            case AbstractCnab::COD_BANCO_HSBC:
                return new Banco\Hsbc($file_content);
                break;
            default:
                throw new \Exception("Banco: $banco, inválido");
        }

        return $instancia;
    }
}