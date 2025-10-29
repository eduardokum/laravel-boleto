#!/usr/bin/env php
<?php

/**
 * Script CLI para gerar retorno fake via terminal
 * 
 * USO:
 * php gerar_retorno_cli.php <codigo_banco> <arquivo_remessa> [ocorrencia]
 * 
 * EXEMPLOS:
 * php gerar_retorno_cli.php 077 remessa.txt
 * php gerar_retorno_cli.php 077 remessa.txt 00
 * php gerar_retorno_cli.php 077 remessa.txt HF
 */

require __DIR__ . '/autoload.php';

use Eduardokum\LaravelBoleto\Cnab\Pagamento\Retorno\FakeRetornoPagamento;
use Eduardokum\LaravelBoleto\Cnab\Pagamento\Retorno\Factory;

// Cores para terminal
$VERDE = "\033[0;32m";
$VERMELHO = "\033[0;31m";
$AMARELO = "\033[1;33m";
$AZUL = "\033[0;34m";
$RESET = "\033[0m";

echo "\n";
echo "╔══════════════════════════════════════════════════╗\n";
echo "║   GERADOR DE RETORNO FAKE - CNAB 240             ║\n";
echo "╚══════════════════════════════════════════════════╝\n\n";

// Validação de argumentos
if ($argc < 3) {
    echo "{$VERMELHO}❌ ERRO: Argumentos insuficientes{$RESET}\n\n";
    echo "USO:\n";
    echo "  php {$argv[0]} <codigo_banco> <arquivo_remessa> [ocorrencia]\n\n";
    echo "EXEMPLOS:\n";
    echo "  {$AZUL}# Mix realista (70% aprovados, 30% erros){$RESET}\n";
    echo "  php {$argv[0]} 077 remessa.txt\n\n";
    echo "  {$AZUL}# Todos aprovados{$RESET}\n";
    echo "  php {$argv[0]} 077 remessa.txt 00\n\n";
    echo "  {$AZUL}# Todos rejeitados (saldo insuficiente){$RESET}\n";
    echo "  php {$argv[0]} 077 remessa.txt HF\n\n";
    echo "  {$AZUL}# PIX rejeitado (chave inválida){$RESET}\n";
    echo "  php {$argv[0]} 077 remessa.txt PM\n\n";
    echo "BANCOS SUPORTADOS:\n";
    echo "  077 - Banco Inter\n";
    echo "  341 - Itaú\n";
    echo "  756 - Bancoob/Sicoob\n";
    echo "  748 - Sicredi\n\n";
    echo "OCORRÊNCIAS EXEMPLOS:\n";
    echo "  {$VERDE}Sucesso:{$RESET} 00, BD, 03 (Sicredi também: BE, BF, BI, BJ)\n";
    echo "  {$VERMELHO}Erros gerais:{$RESET} HF, AG, AR, AP\n";
    echo "  {$AMARELO}Erros PIX:{$RESET} PM, PJ, PA\n";
    echo "  {$VERMELHO}Erros Sicredi:{$RESET} AA, AB, AC, HA, HB, HC, etc.\n\n";
    exit(1);
}

$codigoBanco = $argv[1];
$arquivoRemessa = $argv[2];
$ocorrencia = $argv[3] ?? null;

echo "📋 Parâmetros:\n";
echo "   Banco: {$AZUL}{$codigoBanco}{$RESET}\n";
echo "   Remessa: {$AZUL}{$arquivoRemessa}{$RESET}\n";
echo "   Ocorrência: " . ($ocorrencia ? "{$AZUL}{$ocorrencia}{$RESET} (forçada)" : "{$AMARELO}MIX REALISTA{$RESET} (padrão)") . "\n\n";

// Verifica se arquivo existe
if (!file_exists($arquivoRemessa)) {
    echo "{$VERMELHO}❌ ERRO: Arquivo de remessa não encontrado!{$RESET}\n";
    echo "   Caminho: {$arquivoRemessa}\n\n";
    exit(1);
}

try {
    echo "🔄 Gerando retorno fake...\n";

    // Gera o retorno
    $retorno = FakeRetornoPagamento::gerar($codigoBanco, $arquivoRemessa, $ocorrencia);

    // Define nome do arquivo de saída
    $nomeArquivo = 'retorno_fake_' . $codigoBanco . '_' . date('YmdHis') . '.ret';
    $caminhoSaida = dirname($arquivoRemessa) . '/' . $nomeArquivo;

    // Salva o arquivo
    FakeRetornoPagamento::salvar($retorno, $caminhoSaida);

    echo "{$VERDE}✅ Retorno gerado com sucesso!{$RESET}\n\n";
    echo "📄 Arquivo salvo:\n";
    echo "   {$VERDE}{$caminhoSaida}{$RESET}\n";
    echo "   Tamanho: " . number_format(strlen($retorno)) . " bytes\n\n";

    // Processa o retorno para mostrar estatísticas
    echo "📊 Processando retorno...\n";
    $processado = Factory::make($caminhoSaida);
    $totais = $processado->getTotais();
    $detalhes = $processado->getDetalhes();

    echo "\n";
    echo "╔══════════════════════════════════════════════════╗\n";
    echo "║              ESTATÍSTICAS DO RETORNO             ║\n";
    echo "╚══════════════════════════════════════════════════╝\n\n";

    $totalPagamentos = count($detalhes);
    $pagos = $totais['pagos'] ?? 0;
    $rejeitados = $totais['rejeitados'] ?? 0;
    $cancelados = $totais['cancelados'] ?? 0;
    $valorTotal = $totais['valor_total'] ?? 0;

    echo "  Total de pagamentos: {$AZUL}{$totalPagamentos}{$RESET}\n";
    echo "  {$VERDE}✓{$RESET} Pagos: {$VERDE}{$pagos}{$RESET} (" . ($totalPagamentos > 0 ? round($pagos / $totalPagamentos * 100, 1) : 0) . "%)\n";
    echo "  {$VERMELHO}✗{$RESET} Rejeitados: {$VERMELHO}{$rejeitados}{$RESET} (" . ($totalPagamentos > 0 ? round($rejeitados / $totalPagamentos * 100, 1) : 0) . "%)\n";
    echo "  {$AMARELO}○{$RESET} Cancelados: {$AMARELO}{$cancelados}{$RESET} (" . ($totalPagamentos > 0 ? round($cancelados / $totalPagamentos * 100, 1) : 0) . "%)\n";
    echo "  💰 Valor total pago: R$ " . number_format($valorTotal, 2, ',', '.') . "\n\n";

    // Mostra detalhes de cada pagamento
    echo "╔══════════════════════════════════════════════════╗\n";
    echo "║           DETALHES DOS PAGAMENTOS                ║\n";
    echo "╚══════════════════════════════════════════════════╝\n\n";

    $contador = 1;
    foreach ($detalhes as $detalhe) {
        $ocorrenciaDetalhe = trim($detalhe->getOcorrencia());
        $cor = in_array($ocorrenciaDetalhe, ['00', 'BD']) ? $VERDE : $VERMELHO;
        $icone = in_array($ocorrenciaDetalhe, ['00', 'BD']) ? '✓' : '✗';

        echo "  {$contador}. {$cor}{$icone}{$RESET} ";
        echo "Doc: " . ($detalhe->getSeuNumero() ?: 'N/A') . " | ";
        echo "Valor: R$ " . number_format($detalhe->getValor(), 2, ',', '.') . " | ";
        echo "Status: {$cor}{$ocorrenciaDetalhe}{$RESET} - " . $detalhe->getOcorrenciaDescricao() . "\n";

        $contador++;

        // Limita exibição a 20 primeiros
        if ($contador > 20 && $totalPagamentos > 20) {
            echo "  ... e mais " . ($totalPagamentos - 20) . " pagamentos\n";
            break;
        }
    }

    echo "\n";
    echo "{$VERDE}╔══════════════════════════════════════════════════╗{$RESET}\n";
    echo "{$VERDE}║                  CONCLUÍDO!                      ║{$RESET}\n";
    echo "{$VERDE}╚══════════════════════════════════════════════════╝{$RESET}\n\n";
} catch (Exception $e) {
    echo "\n{$VERMELHO}❌ ERRO: {$e->getMessage()}{$RESET}\n\n";
    exit(1);
}
