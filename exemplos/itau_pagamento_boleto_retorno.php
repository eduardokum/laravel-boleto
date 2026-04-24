<?php
/**
 * Smoke test: gera um arquivo de retorno sintético com liquidação de boletos
 * (segmentos J / J-52) e um segmento Z de autenticação eletrônica, e então
 * processa com o parser de retorno do Itaú.
 */

require 'autoload.php';

use Eduardokum\LaravelBoleto\Cnab\Pagamento\Retorno\Cnab240\Banco\Itau as RetornoItau;

// ---- construção manual do arquivo (sem testar o writer de retorno) ----
$pad = function ($str, $len, $type = 'X') {
    $str = (string) $str;
    if ($type === '9') {
        return str_pad($str, $len, '0', STR_PAD_LEFT);
    }

    return mb_substr(str_pad($str, $len, ' '), 0, $len);
};

// Header arquivo
$linhas = [];
$linhas[] = str_pad('34100000', 14)
    . '080'
    . '2' . $pad('12345678000190', 14, '9')
    . str_repeat(' ', 20)
    . $pad('01234', 5, '9') . ' '
    . $pad('567890', 12, '9') . ' ' . '1'
    . $pad('MINHA EMPRESA LTDA', 30) . $pad('ITAU', 30)
    . str_repeat(' ', 10)
    . '2' // arquivo retorno
    . '23042026' . '103000'
    . str_repeat('0', 9)
    . '00000'
    . str_repeat(' ', 69);

// Header lote - forma 30 (boletos Itaú)
$linhas[] = '34100011' . 'C' . '20' . '30' . '030' . ' '
    . '2' . $pad('12345678000190', 14, '9')
    . str_repeat(' ', 20)
    . $pad('01234', 5, '9') . ' '
    . $pad('567890', 12, '9') . ' ' . '1'
    . $pad('MINHA EMPRESA LTDA', 30)
    . str_repeat(' ', 30)
    . str_repeat(' ', 10)
    . $pad('AV PAULISTA 1000', 30)
    . str_repeat(' ', 5) . str_repeat(' ', 15)
    . $pad('SAO PAULO', 20) . '01310100' . 'SP'
    . str_repeat(' ', 8) . str_repeat(' ', 10);

// Segmento J - boleto pago (ocorrência 00)
$codigoBarras = '34191790010123456789012345678901234567890000'; // 44 dig
$segJ = '34100013' . '00001' . 'J' . '000'
    . substr($codigoBarras, 0, 3)                 // banco
    . substr($codigoBarras, 3, 1)                 // moeda
    . substr($codigoBarras, 4, 1)                 // dv
    . substr($codigoBarras, 5, 4)                 // fator
    . substr($codigoBarras, 9, 10)                // valor codigo de barras
    . substr($codigoBarras, 19, 25)               // campo livre
    . $pad('FORNECEDOR A SA', 30)                 // nome favorecido
    . '10052026'                                  // data vencimento
    . $pad('15075', 15, '9')                      // valor titulo 150.75
    . $pad('0', 15, '9')                          // desconto
    . $pad('0', 15, '9')                          // acrescimo
    . '23042026'                                  // data pagamento
    . $pad('15075', 15, '9')                      // valor pagamento
    . str_repeat('0', 15)                         // zeros
    . $pad('PED00001', 20)                        // seu numero
    . str_repeat(' ', 13)                         // brancos
    . $pad('NN12345', 15)                         // nosso numero
    . '00        ';                               // ocorrencia 00 (pago)
$linhas[] = $segJ;

// Segmento J-52
$segJ52 = '34100013' . '00002' . 'J' . '000'
    . '52'                                         // identificação J-52
    . '2' . $pad('12345678000190', 15, '9')        // sacado CNPJ
    . $pad('MINHA EMPRESA LTDA', 40)               // nome sacado
    . '2' . $pad('11222333000144', 15, '9')        // cedente CNPJ
    . $pad('FORNECEDOR A SA', 40)                  // nome cedente
    . '0' . $pad('0', 15, '9')                     // sem sacador avalista
    . str_repeat(' ', 40)                          // nome sacador (branco)
    . str_repeat(' ', 53);                         // brancos
$linhas[] = $segJ52;

// Segmento Z - Autenticação eletrônica do boleto pago
$segZ = '34100013' . '00003' . 'Z'
    . $pad('AUTENTICACAO-ELETRONICA-XYZ123', 64)
    . $pad('PED00001', 20)
    . str_repeat(' ', 5)
    . $pad('NN12345', 15)
    . str_repeat(' ', 122);
$linhas[] = $segZ;

// Trailer lote (4 registros: header + J + J52 + Z + trailer = 5)
$linhas[] = '34100015' . str_repeat(' ', 9)
    . $pad('5', 6, '9')
    . $pad('15075', 18, '9')
    . str_repeat('0', 18)
    . str_repeat(' ', 171)
    . str_repeat(' ', 10);

// Trailer arquivo
$linhas[] = '34199999' . str_repeat(' ', 9)
    . $pad('1', 6, '9')
    . $pad('7', 6, '9')
    . str_repeat(' ', 211);

// Garantir 240 chars em cada linha
foreach ($linhas as $i => $linha) {
    $linhas[$i] = mb_substr(str_pad($linha, 240), 0, 240);
    if (strlen($linhas[$i]) !== 240) {
        throw new \RuntimeException("Linha $i com tamanho invalido: " . strlen($linhas[$i]));
    }
}

$path = __DIR__ . DIRECTORY_SEPARATOR . 'arquivos' . DIRECTORY_SEPARATOR . 'itau_pagamento_boleto_retorno.ret';
file_put_contents($path, implode("\r\n", $linhas) . "\r\n");

echo "Arquivo sintetico gerado em: $path\n\n";

// ---- processamento ----
$retorno = new RetornoItau($path);
$retorno->processar();

echo "Totais:\n";
print_r($retorno->getTotais());

foreach ($retorno->getDetalhes() as $i => $d) {
    echo "\n--- Detalhe " . ($i + 1) . " ---\n";
    echo "Ocorrencia        : [" . $d->getOcorrencia() . "] " . $d->getOcorrenciaDescricao() . " (" . $d->getOcorrenciaTipo() . ")\n";
    echo "Tipo Pagamento    : " . $d->getTipoPagamento() . "\n";
    echo "Seu Numero        : " . $d->getSeuNumero() . "\n";
    echo "Nosso Numero      : " . $d->getNossoNumero() . "\n";
    echo "Codigo de Barras  : " . $d->getCodigoBarras() . "\n";
    echo "Banco favorecido  : " . $d->getCodigoBancoFavorecido() . "\n";
    echo "Data vencimento   : " . $d->getDataVencimento() . "\n";
    echo "Data pagamento    : " . $d->getDataPagamento() . "\n";
    echo "Valor titulo      : R$ " . number_format((float) $d->getValorTitulo(), 2, ',', '.') . "\n";
    echo "Desconto          : R$ " . number_format((float) $d->getDesconto(), 2, ',', '.') . "\n";
    echo "Acrescimo         : R$ " . number_format((float) $d->getAcrescimo(), 2, ',', '.') . "\n";
    echo "Valor pago        : R$ " . number_format((float) $d->getValor(), 2, ',', '.') . "\n";
    echo "Autenticacao      : " . $d->getAutenticacao() . "\n";

    if ($s = $d->getSacado()) {
        echo "Sacado            : " . $s->getNome() . " / " . $s->getTipoDocumento() . ": " . $s->getDocumento() . "\n";
    }
    if ($c = $d->getCedente()) {
        echo "Cedente           : " . $c->getNome() . " / " . $c->getTipoDocumento() . ": " . $c->getDocumento() . "\n";
    }
}
