<?php

require 'autoload.php';

use Eduardokum\LaravelBoleto\Cnab\Pagamento\Retorno\Factory;

echo "========== TESTE DE RETORNO DE PAGAMENTO - BANCO INTER ==========\n\n";

try {
    // Caminho para o arquivo de retorno
    // Você pode usar um arquivo real do Inter ou criar um mock para testes
    $arquivoRetorno = __DIR__ . '/arquivos/CI240_65505476_30092025144638.ret';

    // Verifica se o arquivo existe
    if (!file_exists($arquivoRetorno)) {
        echo "⚠ Arquivo não encontrado: $arquivoRetorno\n";
        echo "Crie um arquivo de retorno do Inter ou use o exemplo abaixo para criar um mock.\n";
        exit;
    }

    echo "📄 Processando arquivo: $arquivoRetorno\n\n";

    // Processa o arquivo de retorno
    $retorno = Factory::make($arquivoRetorno);

    echo "✓ Arquivo processado com sucesso!\n\n";

    // ========== HEADER DO ARQUIVO ==========
    echo "========== HEADER DO ARQUIVO ==========\n";
    $header = $retorno->getHeader();
    echo "Banco: " . $header->getCodBanco() . " - " . $header->getNomeBanco() . "\n";
    echo "Empresa: " . $header->getNomeEmpresa() . "\n";
    echo "CNPJ/CPF: " . $header->getNumeroInscricao() . "\n";
    echo "Agência: " . $header->getAgencia() . "-" . $header->getAgenciaDv() . "\n";
    echo "Conta: " . $header->getConta() . "-" . $header->getContaDv() . "\n";
    echo "Data do arquivo: " . $header->getData() . "\n";
    echo "Hora do arquivo: " . $header->getHora() . "\n";
    echo "Nº Sequencial: " . $header->getNumeroSequencialArquivo() . "\n";
    echo "Versão Layout: " . $header->getVersaoLayoutArquivo() . "\n\n";

    // ========== TOTALIZADORES ==========
    echo "========== TOTALIZADORES ==========\n";
    $totais = $retorno->getTotais();
    echo "Total de pagamentos: " . count($retorno->getDetalhes()) . "\n";
    echo "✓ Pagamentos efetivados: " . $totais['pagos'] . "\n";
    echo "✗ Pagamentos rejeitados: " . $totais['rejeitados'] . "\n";
    echo "⏳ Pagamentos pendentes: " . $totais['pendentes'] . "\n";
    echo "❌ Pagamentos cancelados: " . $totais['cancelados'] . "\n";
    echo "⚠ Pagamentos com erro: " . $totais['erros'] . "\n";
    echo "💰 Valor total pago: R$ " . number_format($totais['valor_total'], 2, ',', '.') . "\n\n";

    // ========== LOTES ==========
    $lotes = $retorno->getLotes();
    echo "========== LOTES PROCESSADOS ==========\n";
    echo "Quantidade de lotes: " . count($lotes) . "\n\n";

    foreach ($lotes as $numeroLote => $lote) {
        echo "--- Lote $numeroLote ---\n";
        $headerLote = $lote['header'];
        echo "  Tipo Operação: " . $headerLote->getTipoOperacao() . "\n";
        echo "  Tipo Serviço: " . $headerLote->getTipoServico() . "\n";
        echo "  Forma Lançamento: " . $headerLote->getFormaLancamento();

        // Identifica o tipo de pagamento pela forma de lançamento
        $formaLancamento = $headerLote->getFormaLancamento();
        if ($formaLancamento == '45') {
            echo " (PIX)\n";
        } elseif ($formaLancamento == '03') {
            echo " (TED)\n";
        } else {
            echo " (Desconhecido)\n";
        }

        $trailerLote = $lote['trailer'];
        echo "  Qtd Registros: " . $trailerLote->getQtdRegistroLote() . "\n";
        echo "  Qtd Pagamentos: " . $trailerLote->getQtdPagamentos() . "\n";
        echo "  Valor Total: R$ " . number_format($trailerLote->getValorTotalPagamentos(), 2, ',', '.') . "\n\n";
    }

    // ========== DETALHES DOS PAGAMENTOS ==========
    echo "========== DETALHES DOS PAGAMENTOS ==========\n\n";

    $pagamentosComSucesso = [];
    $pagamentosComErro = [];
    $pagamentosPendentes = [];

    foreach ($retorno->getDetalhes() as $index => $detalhe) {
        if ($detalhe->isPago()) {
            $pagamentosComSucesso[] = $detalhe;
        } elseif ($detalhe->hasError() || $detalhe->isRejeitado()) {
            $pagamentosComErro[] = $detalhe;
        } else {
            $pagamentosPendentes[] = $detalhe;
        }
    }

    // Lista pagamentos com sucesso
    if (count($pagamentosComSucesso) > 0) {
        echo "✓ PAGAMENTOS EFETUADOS COM SUCESSO (" . count($pagamentosComSucesso) . "):\n";
        echo str_repeat("-", 80) . "\n";

        foreach ($pagamentosComSucesso as $i => $detalhe) {
            echo "\n[" . ($i + 1) . "] Seu Número: " . $detalhe->getSeuNumero() . "\n";
            echo "    Nosso Número: " . $detalhe->getNossoNumero() . "\n";
            echo "    Tipo: " . $detalhe->getTipoPagamento() . "\n";
            echo "    Valor Solicitado: R$ " . number_format($detalhe->getValor(), 2, ',', '.') . "\n";
            echo "    Valor Efetivado: R$ " . number_format($detalhe->getValorRealEfetivado() ?: 0, 2, ',', '.') . "\n";
            echo "    Data Pagamento: " . $detalhe->getDataPagamento() . "\n";
            echo "    Data Efetivação: " . ($detalhe->getDataEfetivacao() ?: 'N/A') . "\n";

            if ($favorecido = $detalhe->getFavorecido()) {
                echo "    Favorecido: " . $favorecido->getNome() . "\n";
                echo "    CPF/CNPJ: " . $favorecido->getDocumento() . "\n";
                echo "    Banco: " . $detalhe->getCodigoBancoFavorecido() . "\n";
                echo "    Agência: " . $detalhe->getAgenciaFavorecido() . "\n";
                echo "    Conta: " . $detalhe->getContaFavorecido() . "\n";
            }

            echo "    Status: " . $detalhe->getOcorrenciaTipo() . "\n";
            echo "    Ocorrência: [" . $detalhe->getOcorrencia() . "] " . $detalhe->getOcorrenciaDescricao() . "\n";
        }
        echo "\n";
    }

    // Lista pagamentos pendentes
    if (count($pagamentosPendentes) > 0) {
        echo "\n⏳ PAGAMENTOS PENDENTES (" . count($pagamentosPendentes) . "):\n";
        echo str_repeat("-", 80) . "\n";

        foreach ($pagamentosPendentes as $i => $detalhe) {
            echo "\n[" . ($i + 1) . "] Seu Número: " . $detalhe->getSeuNumero() . "\n";
            echo "    Tipo: " . $detalhe->getTipoPagamento() . "\n";
            echo "    Valor: R$ " . number_format($detalhe->getValor(), 2, ',', '.') . "\n";
            echo "    Status: " . $detalhe->getOcorrenciaTipo() . "\n";
            echo "    Ocorrência: [" . $detalhe->getOcorrencia() . "] " . $detalhe->getOcorrenciaDescricao() . "\n";
        }
        echo "\n";
    }

    // Lista pagamentos com erro
    if (count($pagamentosComErro) > 0) {
        echo "\n✗ PAGAMENTOS COM ERRO/REJEITADOS (" . count($pagamentosComErro) . "):\n";
        echo str_repeat("-", 80) . "\n";

        foreach ($pagamentosComErro as $i => $detalhe) {
            echo "\n[" . ($i + 1) . "] Seu Número: " . $detalhe->getSeuNumero() . "\n";
            echo "    Tipo: " . $detalhe->getTipoPagamento() . "\n";
            echo "    Valor: R$ " . number_format($detalhe->getValor(), 2, ',', '.') . "\n";

            if ($favorecido = $detalhe->getFavorecido()) {
                echo "    Favorecido: " . $favorecido->getNome() . "\n";
            }

            echo "    Status: " . $detalhe->getOcorrenciaTipo() . "\n";
            echo "    Ocorrência: [" . $detalhe->getOcorrencia() . "] " . $detalhe->getOcorrenciaDescricao() . "\n";

            if ($detalhe->getError()) {
                echo "    ⚠ Erro: " . $detalhe->getError() . "\n";
            }

            if (count($detalhe->getRejeicoes()) > 0) {
                echo "    Rejeições:\n";
                foreach ($detalhe->getRejeicoes() as $codigo => $descricao) {
                    echo "      • [$codigo] $descricao\n";
                }
            }
        }
        echo "\n";
    }

    // ========== TRAILER DO ARQUIVO ==========
    echo "\n========== TRAILER DO ARQUIVO ==========\n";
    $trailer = $retorno->getTrailer();
    echo "Quantidade de lotes: " . $trailer->getQtdLotesArquivo() . "\n";
    echo "Quantidade de registros: " . $trailer->getQtdRegistroArquivo() . "\n";

    echo "\n" . str_repeat("=", 80) . "\n";
    echo "✓ TESTE CONCLUÍDO COM SUCESSO!\n";
    echo str_repeat("=", 80) . "\n";
} catch (\Eduardokum\LaravelBoleto\Exception\ValidationException $e) {
    echo "\n❌ ERRO DE VALIDAÇÃO:\n";
    echo $e->getMessage() . "\n\n";
    echo "Stack trace:\n";
    echo $e->getTraceAsString() . "\n";
} catch (\Exception $e) {
    echo "\n❌ ERRO GERAL:\n";
    echo $e->getMessage() . "\n\n";
    echo "Stack trace:\n";
    echo $e->getTraceAsString() . "\n";
}
