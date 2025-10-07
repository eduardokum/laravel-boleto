<?php

namespace Eduardokum\LaravelBoleto\Cnab\Pagamento\Retorno;

use Eduardokum\LaravelBoleto\Util;
use Eduardokum\LaravelBoleto\Exception\ValidationException;

class Factory
{
    /**
     * Cria uma instância de retorno de pagamento e processa o arquivo
     *
     * @param string|array $file
     * @return AbstractRetorno
     * @throws ValidationException
     */
    public static function make($file)
    {
        if (!$file_content = is_array($file) ? $file : Util::file2array($file))
            throw new ValidationException('Arquivo: não existe ou está vazio');

        $instancia = self::getBancoClass($file_content);

        return $instancia->processar();
    }

    /**
     * Identifica o banco e retorna a instância apropriada
     *
     * @param array $file_content
     * @return AbstractRetorno
     * @throws ValidationException
     */
    private static function getBancoClass($file_content)
    {
        $banco = '';
        $namespace = '';

        // Identifica se é CNAB 240 (por enquanto só suportamos CNAB 240 para pagamento)
        if (Util::isCnab240($file_content)) {
            $banco = mb_substr($file_content[0], 0, 3);
            $namespace = __NAMESPACE__ . '\\Cnab240\\';
        } else {
            throw new ValidationException('Formato de arquivo não suportado. Apenas CNAB 240 é suportado para retorno de pagamento.');
        }

        $bancoClass = $namespace . Util::getBancoClass($banco);

        if (!class_exists($bancoClass))
            throw new ValidationException("Banco $banco não possui implementação de retorno de pagamento CNAB 240");

        return new $bancoClass($file_content);
    }

    /**
     * Método auxiliar para verificar se o arquivo é de retorno de pagamento
     *
     * @param string|array $file
     * @return bool
     */
    public static function isRetornoPagamento($file)
    {
        try {
            if (!$file_content = is_array($file) ? $file : Util::file2array($file))
                return false;

            if (!Util::isHeaderRetorno($file_content[0]))
                return false;

            if (!Util::isCnab240($file_content))
                return false;

            // Verifica o tipo de serviço no header do lote (registro tipo 1)
            foreach ($file_content as $linha) {
                $recordType = mb_substr($linha, 7, 1);
                if ($recordType == '1') {
                    // Posição 9-10: Tipo de Serviço
                    // 01 = Pagamento
                    // 02 = Cobrança
                    $tipoServico = mb_substr($linha, 9, 2);
                    return $tipoServico == '01';
                }
            }

            return false;
        } catch (\Exception $e) {
            return false;
        }
    }
}
