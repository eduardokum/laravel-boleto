<?php

namespace Eduardokum\LaravelBoleto\Cnab\Pagamento\Retorno;

use Eduardokum\LaravelBoleto\Exception\ValidationException;

/**
 * Gerador simples de retorno fake para testes
 */
class FakeRetornoPagamento
{
    /**
     * Gera retorno fake baseado no código do banco
     * 
     * @param string $codigoBanco Código do banco (077, 341, etc)
     * @param string $arquivoRemessa Caminho do arquivo de remessa
     * @param string|null $ocorrenciaForcada Se informado, força todos com mesma ocorrência. Se null, gera mix realista
     * @return string Conteúdo do arquivo de retorno
     * @throws ValidationException
     */
    public static function gerar($codigoBanco, $arquivoRemessa, $ocorrenciaForcada = null)
    {
        if (!file_exists($arquivoRemessa)) {
            throw new ValidationException("Arquivo de remessa não encontrado: $arquivoRemessa");
        }

        $linhas = file($arquivoRemessa, FILE_IGNORE_NEW_LINES);

        if (empty($linhas)) {
            throw new ValidationException("Arquivo de remessa está vazio");
        }

        // Chama a função específica do banco
        switch ($codigoBanco) {
            case '077': // Inter
                return self::gerarRetornoInter($linhas, $ocorrenciaForcada);

            case '341': // Itaú
                return self::gerarRetornoItau($linhas, $ocorrenciaForcada);

            case '756': // Bancoob
                return self::gerarRetornoBancoob($linhas, $ocorrenciaForcada);

            default:
                throw new ValidationException("Banco $codigoBanco não suportado");
        }
    }

    /**
     * Gera retorno fake para Banco Inter
     * Usa as especificações e ocorrências já definidas na classe Inter
     * 
     * @param array $linhas
     * @param string|null $ocorrenciaForcada
     * @return string
     */
    private static function gerarRetornoInter(array $linhas, $ocorrenciaForcada = null)
    {
        // Ocorrências possíveis do Inter (baseado na classe Inter.php)
        $ocorrenciasSucesso = ['00', 'BD']; // ~70% sucesso
        $ocorrenciasRejeicao = ['HF', 'AG', 'AR', 'AP']; // ~20% rejeições gerais
        $ocorrenciasPix = ['PM', 'PJ', 'PA']; // ~10% erros PIX

        $retorno = [];
        $totalValorEfetivado = 0;
        $numeroPagamento = 0;

        foreach ($linhas as $linha) {
            $linha = str_pad($linha, 240, ' ');
            $tipoRegistro = substr($linha, 7, 1);

            switch ($tipoRegistro) {
                case '0': // Header do arquivo
                    // Muda código remessa (1) para retorno (2)
                    $linha = substr_replace($linha, '2', 142, 1);
                    // Atualiza data/hora
                    $linha = substr_replace($linha, date('dmYHis'), 143, 14);
                    break;

                case '3': // Detalhe (Segmento A ou B)
                    $segmento = substr($linha, 13, 1);

                    if ($segmento === 'A') {
                        $numeroPagamento++;

                        // Define ocorrência para este pagamento
                        if ($ocorrenciaForcada !== null) {
                            // Se forçou uma ocorrência, usa ela
                            $ocorrencia = $ocorrenciaForcada;
                        } else {
                            // Gera mix realista
                            $rand = rand(1, 100);
                            if ($rand <= 70) {
                                // 70% aprovados
                                $ocorrencia = $ocorrenciasSucesso[array_rand($ocorrenciasSucesso)];
                            } elseif ($rand <= 90) {
                                // 20% rejeições gerais
                                $ocorrencia = $ocorrenciasRejeicao[array_rand($ocorrenciasRejeicao)];
                            } else {
                                // 10% erros PIX
                                $ocorrencia = $ocorrenciasPix[array_rand($ocorrenciasPix)];
                            }
                        }

                        // Pega valor original da remessa
                        $valorOriginal = substr($linha, 119, 15);

                        // Preenche data de efetivação
                        $ehSucesso = in_array($ocorrencia, $ocorrenciasSucesso);
                        $dataEfetivacao = $ehSucesso ? date('dmY') : '00000000';
                        $linha = substr_replace($linha, str_pad($dataEfetivacao, 8, '0', STR_PAD_LEFT), 154, 8);

                        // Preenche valor efetivado
                        $valorEfetivado = $ehSucesso ? $valorOriginal : str_pad('0', 15, '0', STR_PAD_LEFT);
                        $linha = substr_replace($linha, $valorEfetivado, 162, 15);

                        // Acumula total
                        if ($ehSucesso) {
                            $totalValorEfetivado += (float) $valorEfetivado;
                        }

                        // Preenche ocorrência
                        $linha = substr_replace($linha, str_pad($ocorrencia, 10, ' '), 230, 10);
                    } elseif ($segmento === 'B') {
                        // Usa a mesma ocorrência do segmento A correspondente
                        // (pega da linha anterior processada)
                        $ocorrenciaSegmentoA = trim(substr($retorno[count($retorno) - 1], 230, 10));
                        $linha = substr_replace($linha, str_pad($ocorrenciaSegmentoA, 10, ' '), 230, 10);
                    }
                    break;

                case '5': // Trailer do lote
                    // Atualiza valor total do lote
                    $linha = substr_replace($linha, str_pad((int) $totalValorEfetivado, 18, '0', STR_PAD_LEFT), 23, 18);
                    break;
            }

            $retorno[] = $linha;
        }

        return implode("\r\n", $retorno) . "\r\n";
    }

    /**
     * Gera retorno fake para Itaú
     * 
     * @param array $linhas
     * @param string|null $ocorrenciaForcada
     * @return string
     */
    private static function gerarRetornoItau(array $linhas, $ocorrenciaForcada = null)
    {
        // Ocorrências possíveis do Itaú (similar ao Inter)
        $ocorrenciasSucesso = ['00', 'BD'];
        $ocorrenciasRejeicao = ['HF', 'AG', 'AR', 'AP'];
        $ocorrenciasPix = ['PM', 'PJ', 'PA'];

        $retorno = [];
        $totalValorEfetivado = 0;
        $numeroPagamento = 0;

        foreach ($linhas as $linha) {
            $linha = str_pad($linha, 240, ' ');
            $tipoRegistro = substr($linha, 7, 1);

            switch ($tipoRegistro) {
                case '0': // Header
                    $linha = substr_replace($linha, '2', 142, 1);
                    $linha = substr_replace($linha, date('dmYHis'), 143, 14);
                    break;

                case '3': // Detalhe
                    $segmento = substr($linha, 13, 1);

                    if ($segmento === 'A') {
                        $numeroPagamento++;

                        // Define ocorrência
                        if ($ocorrenciaForcada !== null) {
                            $ocorrencia = $ocorrenciaForcada;
                        } else {
                            $rand = rand(1, 100);
                            if ($rand <= 70) {
                                $ocorrencia = $ocorrenciasSucesso[array_rand($ocorrenciasSucesso)];
                            } elseif ($rand <= 90) {
                                $ocorrencia = $ocorrenciasRejeicao[array_rand($ocorrenciasRejeicao)];
                            } else {
                                $ocorrencia = $ocorrenciasPix[array_rand($ocorrenciasPix)];
                            }
                        }

                        $valorOriginal = substr($linha, 119, 15);
                        $ehSucesso = in_array($ocorrencia, $ocorrenciasSucesso);
                        $dataEfetivacao = $ehSucesso ? date('dmY') : '00000000';
                        $valorEfetivado = $ehSucesso ? $valorOriginal : str_pad('0', 15, '0', STR_PAD_LEFT);

                        $linha = substr_replace($linha, str_pad($dataEfetivacao, 8, '0', STR_PAD_LEFT), 154, 8);
                        $linha = substr_replace($linha, $valorEfetivado, 162, 15);
                        $linha = substr_replace($linha, str_pad($ocorrencia, 10, ' '), 230, 10);

                        if ($ehSucesso) {
                            $totalValorEfetivado += (float) $valorEfetivado;
                        }
                    } elseif ($segmento === 'B') {
                        $ocorrenciaSegmentoA = trim(substr($retorno[count($retorno) - 1], 230, 10));
                        $linha = substr_replace($linha, str_pad($ocorrenciaSegmentoA, 10, ' '), 230, 10);
                    }
                    break;

                case '5': // Trailer lote
                    $linha = substr_replace($linha, str_pad((int) $totalValorEfetivado, 18, '0', STR_PAD_LEFT), 23, 18);
                    break;
            }

            $retorno[] = $linha;
        }

        return implode("\r\n", $retorno) . "\r\n";
    }

    /**
     * Gera retorno fake para Bancoob
     * 
     * @param array $linhas
     * @param string|null $ocorrenciaForcada
     * @return string
     */
    private static function gerarRetornoBancoob(array $linhas, $ocorrenciaForcada = null)
    {
        // Ocorrências possíveis do Bancoob
        $ocorrenciasSucesso = ['00', 'BD'];
        $ocorrenciasRejeicao = ['HF', 'AG', 'AR', 'AP'];
        $ocorrenciasPix = ['PM', 'PJ', 'PA'];

        $retorno = [];
        $totalValorEfetivado = 0;
        $numeroPagamento = 0;

        foreach ($linhas as $linha) {
            $linha = str_pad($linha, 240, ' ');
            $tipoRegistro = substr($linha, 7, 1);

            switch ($tipoRegistro) {
                case '0': // Header
                    $linha = substr_replace($linha, '2', 142, 1);
                    $linha = substr_replace($linha, date('dmYHis'), 143, 14);
                    break;

                case '3': // Detalhe
                    $segmento = substr($linha, 13, 1);

                    if ($segmento === 'A') {
                        $numeroPagamento++;

                        // Define ocorrência
                        if ($ocorrenciaForcada !== null) {
                            $ocorrencia = $ocorrenciaForcada;
                        } else {
                            $rand = rand(1, 100);
                            if ($rand <= 70) {
                                $ocorrencia = $ocorrenciasSucesso[array_rand($ocorrenciasSucesso)];
                            } elseif ($rand <= 90) {
                                $ocorrencia = $ocorrenciasRejeicao[array_rand($ocorrenciasRejeicao)];
                            } else {
                                // $ocorrencia = $ocorrenciasPix[array_rand($ocorrenciasPix)];
                                $ocorrencia = '00';
                            }
                        }

                        $valorOriginal = substr($linha, 119, 15);
                        $ehSucesso = in_array($ocorrencia, $ocorrenciasSucesso);
                        $dataEfetivacao = $ehSucesso ? date('dmY') : '00000000';
                        $valorEfetivado = $ehSucesso ? $valorOriginal : str_pad('0', 15, '0', STR_PAD_LEFT);

                        $linha = substr_replace($linha, str_pad($dataEfetivacao, 8, '0', STR_PAD_LEFT), 154, 8);
                        $linha = substr_replace($linha, $valorEfetivado, 162, 15);
                        $linha = substr_replace($linha, str_pad($ocorrencia, 10, ' '), 230, 10);

                        if ($ehSucesso) {
                            $totalValorEfetivado += (float) $valorEfetivado;
                        }
                    } elseif ($segmento === 'B') {
                        $ocorrenciaSegmentoA = trim(substr($retorno[count($retorno) - 1], 230, 10));
                        $linha = substr_replace($linha, str_pad($ocorrenciaSegmentoA, 10, ' '), 230, 10);
                    }
                    break;

                case '5': // Trailer lote
                    $linha = substr_replace($linha, str_pad((int) $totalValorEfetivado, 18, '0', STR_PAD_LEFT), 23, 18);
                    break;
            }

            $retorno[] = $linha;
        }

        return implode("\r\n", $retorno) . "\r\n";
    }

    /**
     * Salva retorno em arquivo
     * 
     * @param string $conteudo
     * @param string $caminhoDestino
     * @return bool
     */
    public static function salvar($conteudo, $caminhoDestino)
    {
        $diretorio = dirname($caminhoDestino);

        if (!is_dir($diretorio)) {
            mkdir($diretorio, 0755, true);
        }

        return file_put_contents($caminhoDestino, $conteudo) !== false;
    }

    /**
     * Gera e faz download do retorno
     * 
     * @param string $codigoBanco
     * @param string $arquivoRemessa
     * @param string|null $ocorrenciaForcada Se null, gera mix realista
     * @return void
     */
    public static function download($codigoBanco, $arquivoRemessa, $ocorrenciaForcada = null)
    {
        $conteudo = self::gerar($codigoBanco, $arquivoRemessa, $ocorrenciaForcada);
        $nomeArquivo = 'retorno_fake_' . $codigoBanco . '_' . date('YmdHis') . '.ret';

        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename="' . $nomeArquivo . '"');
        header('Content-Length: ' . strlen($conteudo));

        echo $conteudo;
        exit;
    }
}
