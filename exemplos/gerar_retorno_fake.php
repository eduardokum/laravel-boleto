<?php

/**
 * Exemplo simples de uso do gerador de retorno fake
 */

require 'autoload.php';

use Eduardokum\LaravelBoleto\Cnab\Pagamento\Retorno\FakeRetornoPagamento;
use Eduardokum\LaravelBoleto\Cnab\Pagamento\Retorno\Factory;

// Caminho do arquivo de remessa
$arquivoRemessa = __DIR__ . '/arquivos/remessa_inter.txt';

if (!file_exists($arquivoRemessa)) {
    die("❌ Arquivo de remessa não encontrado: $arquivoRemessa\n");
}

echo "========== GERADOR DE RETORNO FAKE ==========\n\n";

// 1. MIX REALISTA (padrão) - ~70% aprovados, ~20% erros gerais, ~10% erros PIX
echo "1️⃣  Gerando retorno MIX REALISTA...\n";
$retornoMix = FakeRetornoPagamento::gerar('077', $arquivoRemessa);
FakeRetornoPagamento::salvar($retornoMix, __DIR__ . '/arquivos/retorno_fake_mix.ret');
echo "✅ Retorno MIX gerado (70% aprovados, 30% erros variados)\n";

// Processa para ver estatísticas
$processado = Factory::make(__DIR__ . '/arquivos/retorno_fake_mix.ret');
$totais = $processado->getTotais();
echo "   📊 Estatísticas:\n";
echo "      - Pagos: " . ($totais['pagos'] ?? 0) . "\n";
echo "      - Rejeitados: " . ($totais['rejeitados'] ?? 0) . "\n";
echo "      - Cancelados: " . ($totais['cancelados'] ?? 0) . "\n";
echo "      - Total: " . count($processado->getDetalhes()) . " pagamentos\n\n";

// 2. TODOS APROVADOS (forçado)
echo "2️⃣  Gerando retorno TODOS APROVADOS (forçado)...\n";
$retornoAprovado = FakeRetornoPagamento::gerar('077', $arquivoRemessa, '00');
FakeRetornoPagamento::salvar($retornoAprovado, __DIR__ . '/arquivos/retorno_fake_aprovado.ret');
echo "✅ Retorno APROVADO gerado (00 - forçado)\n\n";

// 3. TODOS REJEITADOS - Saldo Insuficiente (forçado)
echo "3️⃣  Gerando retorno TODOS REJEITADOS (forçado)...\n";
$retornoRejeitado = FakeRetornoPagamento::gerar('077', $arquivoRemessa, 'HF');
FakeRetornoPagamento::salvar($retornoRejeitado, __DIR__ . '/arquivos/retorno_fake_rejeitado.ret');
echo "✅ Retorno REJEITADO gerado (HF - Saldo Insuficiente - forçado)\n\n";

// 4. TODOS PIX REJEITADO - Chave Inválida (forçado)
echo "4️⃣  Gerando retorno TODOS PIX REJEITADO (forçado)...\n";
$retornoPixRejeitado = FakeRetornoPagamento::gerar('077', $arquivoRemessa, 'PM');
FakeRetornoPagamento::salvar($retornoPixRejeitado, __DIR__ . '/arquivos/retorno_fake_pix_rejeitado.ret');
echo "✅ Retorno PIX REJEITADO gerado (PM - Chave Inválida - forçado)\n\n";

echo "========== PRONTO! ==========\n";
echo "Arquivos salvos em: " . __DIR__ . "/arquivos/\n\n";

echo "💡 DICA:\n";
echo "   - Sem parâmetro: gera MIX realista (~70% sucesso, ~30% erros)\n";
echo "   - Com parâmetro: força todos com mesma ocorrência\n\n";

echo "📋 Ocorrências disponíveis (Inter):\n";
echo "   Sucesso: 00, BD\n";
echo "   Erros gerais: HF, AG, AR, AP\n";
echo "   Erros PIX: PM, PJ, PA\n";
