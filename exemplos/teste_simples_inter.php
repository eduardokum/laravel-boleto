<?php

require 'autoload.php';

use Eduardokum\LaravelBoleto\Cnab\Pagamento\Retorno\Factory;

/**
 * TESTE RÁPIDO - Retorno de Pagamento Inter
 * 
 * Execute: php teste_simples_inter.php
 */

function testarRetornoInter($arquivoRetorno)
{
    echo "🧪 Iniciando teste...\n\n";

    try {
        // 1. Verifica se o arquivo existe
        if (!file_exists($arquivoRetorno)) {
            throw new Exception("❌ Arquivo não encontrado: $arquivoRetorno");
        }
        echo "✓ Arquivo encontrado\n";

        // 2. Verifica se é retorno de pagamento
        if (!Factory::isRetornoPagamento($arquivoRetorno)) {
            throw new Exception("❌ Arquivo não é um retorno de pagamento válido");
        }
        echo "✓ Arquivo é um retorno de pagamento válido\n";

        // 3. Processa o arquivo
        $retorno = Factory::make($arquivoRetorno);
        echo "✓ Arquivo processado com sucesso\n\n";

        // 4. Valida estrutura básica
        if (!$retorno->getHeader()) {
            throw new Exception("❌ Header não encontrado");
        }
        echo "✓ Header OK\n";

        if (!$retorno->getTrailer()) {
            throw new Exception("❌ Trailer não encontrado");
        }
        echo "✓ Trailer OK\n";

        if (count($retorno->getDetalhes()) == 0) {
            throw new Exception("⚠ Nenhum detalhe encontrado (arquivo vazio?)");
        }
        echo "✓ Detalhes encontrados: " . count($retorno->getDetalhes()) . "\n\n";

        // 5. Exibe resumo
        echo "📊 RESUMO:\n";
        echo "   Banco: " . $retorno->getHeader()->getNomeBanco() . "\n";
        echo "   Empresa: " . $retorno->getHeader()->getNomeEmpresa() . "\n";
        echo "   Total de pagamentos: " . count($retorno->getDetalhes()) . "\n";

        $totais = $retorno->getTotais();
        echo "   Pagos: " . $totais['pagos'] . "\n";
        echo "   Rejeitados: " . $totais['rejeitados'] . "\n";
        echo "   Pendentes: " . $totais['pendentes'] . "\n";
        echo "   Cancelados: " . $totais['cancelados'] . "\n";
        echo "   Com erro: " . $totais['erros'] . "\n";
        echo "   Valor total: R$ " . number_format($totais['valor_total'], 2, ',', '.') . "\n\n";

        // 6. Testa métodos dos detalhes
        echo "🔍 Testando métodos dos detalhes:\n";
        $detalhe = $retorno->getDetalhe(1);

        echo "   getSeuNumero(): " . ($detalhe->getSeuNumero() ?: 'N/A') . "\n";
        echo "   getNossoNumero(): " . ($detalhe->getNossoNumero() ?: 'N/A') . "\n";
        echo "   getTipoPagamento(): " . ($detalhe->getTipoPagamento() ?: 'N/A') . "\n";
        echo "   getValor(): R$ " . number_format($detalhe->getValor() ?: 0, 2, ',', '.') . "\n";
        echo "   getDataPagamento(): " . ($detalhe->getDataPagamento() ?: 'N/A') . "\n";
        echo "   getDataEfetivacao(): " . ($detalhe->getDataEfetivacao() ?: 'N/A') . "\n";
        echo "   getOcorrenciaTipo(): " . ($detalhe->getOcorrenciaTipo() ?: 'N/A') . "\n";
        echo "   getOcorrenciaDescricao(): " . ($detalhe->getOcorrenciaDescricao() ?: 'N/A') . "\n";
        echo "   isPago(): " . ($detalhe->isPago() ? 'SIM' : 'NÃO') . "\n";
        echo "   isRejeitado(): " . ($detalhe->isRejeitado() ? 'SIM' : 'NÃO') . "\n";
        echo "   hasError(): " . ($detalhe->hasError() ? 'SIM' : 'NÃO') . "\n\n";

        // 7. Testa favorecido
        if ($favorecido = $detalhe->getFavorecido()) {
            echo "👤 Dados do Favorecido:\n";
            echo "   Nome: " . $favorecido->getNome() . "\n";
            echo "   Documento: " . $favorecido->getDocumento() . "\n";
            echo "   Banco: " . $detalhe->getCodigoBancoFavorecido() . "\n";
            echo "   Agência: " . $detalhe->getAgenciaFavorecido() . "\n";
            echo "   Conta: " . $detalhe->getContaFavorecido() . "\n\n";
        }

        // 8. Testa lotes
        $lotes = $retorno->getLotes();
        echo "📦 Lotes: " . count($lotes) . "\n";
        foreach ($lotes as $numero => $lote) {
            echo "   Lote $numero:\n";
            echo "     Tipo serviço: " . $lote['header']->getTipoServico() . "\n";
            echo "     Forma lançamento: " . $lote['header']->getFormaLancamento();

            $forma = $lote['header']->getFormaLancamento();
            if ($forma == '45') echo " (PIX)\n";
            elseif ($forma == '03') echo " (TED)\n";
            else echo "\n";

            echo "     Qtd registros: " . $lote['trailer']->getQtdRegistroLote() . "\n";
            echo "     Valor total: R$ " . number_format($lote['trailer']->getValorTotalPagamentos(), 2, ',', '.') . "\n";
        }

        echo "\n";
        echo str_repeat("=", 60) . "\n";
        echo "✅ TODOS OS TESTES PASSARAM!\n";
        echo str_repeat("=", 60) . "\n";

        return true;
    } catch (Exception $e) {
        echo "\n";
        echo str_repeat("=", 60) . "\n";
        echo "❌ TESTE FALHOU!\n";
        echo str_repeat("=", 60) . "\n";
        echo "Erro: " . $e->getMessage() . "\n";
        echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
        return false;
    }
}

// ========================================
// EXECUTE O TESTE
// ========================================

$arquivoTeste = __DIR__ . '/arquivos/CI240_65505476_30092025144638.ret';

echo "\n";
echo str_repeat("=", 60) . "\n";
echo "TESTE SIMPLES - RETORNO DE PAGAMENTO INTER\n";
echo str_repeat("=", 60) . "\n";
echo "Arquivo: $arquivoTeste\n";
echo str_repeat("=", 60) . "\n";
echo "\n";

$sucesso = testarRetornoInter($arquivoTeste);

exit($sucesso ? 0 : 1);
