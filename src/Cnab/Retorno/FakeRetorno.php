<?php

namespace Eduardokum\LaravelBoleto\Cnab\Retorno;

use Eduardokum\LaravelBoleto\Exception\ValidationException;

/**
 * Gerador de retorno fake de COBRANÇA para testes/QA.
 *
 * Gêmeo do FakeRetornoPagamento (lado Pagamento): pega o arquivo de remessa
 * já gerado pela lib e o transforma num retorno que o próprio parser da lib lê
 * de volta. Preserva nosso número, código de controle e valor (o match do
 * retorno depende do controle). Suporta um MIX de ocorrências por título para
 * simular o mais próximo do retorno bancário real.
 *
 * Arquitetura PLUGÁVEL: cada banco/layout tem um PROFILE (códigos de ocorrência,
 * conjuntos de liquidação/rejeição e motivo de rejeição default — tudo específico
 * do banco). Adicionar um banco = adicionar um profile em profile() + (no caso de
 * CNAB400) o transformador do layout. Banco/layout sem profile => falha clara.
 */
class FakeRetorno
{
    /** Sequência do mix realista, em nomes canônicos (traduzidos por banco). */
    const DEFAULT_MIX = ['confirm', 'pay', 'reject', 'cancel'];

    /** Nomes amigáveis dos bancos (para mensagens). */
    const BANKS = ['001' => 'Banco do Brasil', '341' => 'Itaú', '077' => 'Inter', '104' => 'Caixa', '033' => 'Santander', '748' => 'Sicredi', '756' => 'Bancoob', '041' => 'Banrisul', '085' => 'Ailos', '237' => 'Bradesco'];

    /**
     * @param string      $bankCode    Código do banco (ex.: '001')
     * @param string|int  $layout      Layout CNAB ('240' ou '400')
     * @param string      $remittanceFile Caminho do arquivo de remessa gerado
     * @param null|string|array $spec  null = mix realista; string 'confirm'/'06' ou 'reject:45'/'03:45' = força todos;
     *                                 array lista ['confirm','reject:45',...] = por índice (cicla);
     *                                 array assoc [controle => 'pay'] = por código de controle.
     * @return string Conteúdo do arquivo de retorno
     * @throws ValidationException
     */
    public static function gerar($bankCode, $layout, $remittanceFile, $spec = null)
    {
        if (!file_exists($remittanceFile)) {
            throw new ValidationException("Arquivo de remessa não encontrado: $remittanceFile");
        }

        $lines = file($remittanceFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if (empty($lines)) {
            throw new ValidationException('Arquivo de remessa está vazio');
        }

        $bank    = str_pad((string) $bankCode, 3, '0', STR_PAD_LEFT);
        $layout  = (string) $layout;
        $profile = self::profile($bank, $layout);

        if ($profile['layout'] === '240') {
            return self::cnab240($profile, $lines, $spec);
        }

        if ($profile['layout'] === '400' && $profile['bank'] === '077') {
            return self::cnab400Inter($profile, $lines, $spec);
        }

        // CNAB400 é específico por banco — implementar o transformador no profile correspondente.
        throw new ValidationException("FakeRetorno: transformador CNAB{$profile['layout']} não implementado para " . (self::BANKS[$bank] ?? "banco $bank") . '.');
    }

    /**
     * Profile do banco/layout. Falha claro quando não implementado.
     *
     * Cada profile define os códigos de ocorrência DAQUELE banco (eles diferem entre
     * bancos) e o motivo de rejeição default. Ex.: BB usa 02 entrada, 06 liquidação,
     * 09 baixa, 03 rejeição (motivo default 63 = "Entrada para Título já Cadastrado").
     */
    private static function profile($bank, $layout)
    {
        $profiles = [
            '001' => [
                '240' => [
                    'bank'   => '001',
                    'layout' => '240',
                    // canônico => código do banco
                    'occurrences' => [
                        'confirm' => '02',
                        'pay'     => '06',
                        'cancel'  => '09',
                        'reject'  => '03',
                        'change'  => '14',
                    ],
                    'settlement' => ['06', '17'],       // códigos que preenchem valor recebido (segmento U)
                    'rejection'  => ['03', '26', '30'], // códigos que carregam motivo (pos T 214-223)
                    'defaultRejectionReason' => '63',
                ],
            ],
            // Ocorrências direto de Cnab\Retorno\Cnab400\Banco\Inter::processarDetalhe() ($ocorrencias).
            // Rejeição não usa código — o campo 241-380 é texto livre ("Entrada rejeitada" + motivo).
            '077' => [
                '400' => [
                    'bank'   => '077',
                    'layout' => '400',
                    'occurrences' => [
                        'confirm' => '02', // Em aberto
                        'pay'     => '06', // Pago
                        'cancel'  => '07', // Baixado
                        'reject'  => '03', // Erro
                        'change'  => '14', // Alteração de data de vencimento
                    ],
                    'settlement' => ['06'],
                    'rejection'  => ['03'],
                    'defaultRejectionReason' => 'Motivo fake de teste',
                ],
            ],
        ];

        if (!isset($profiles[$bank][$layout])) {
            $name = self::BANKS[$bank] ?? "banco $bank";
            throw new ValidationException("FakeRetorno (cobrança) ainda não suporta $name em CNAB$layout. Implemente o profile em FakeRetorno::profile().");
        }

        return $profiles[$bank][$layout];
    }

    /**
     * Transforma a remessa CNAB240 (padrão FEBRABAN) em retorno, segundo o profile do banco.
     */
    private static function cnab240(array $profile, array $lines, $spec)
    {
        $bank       = $profile['bank'];
        $fileHeader  = null;
        $batchHeader = null;
        $titles      = [];
        $current     = null;

        foreach ($lines as $line) {
            $line = str_pad($line, 240, ' ');
            $type = self::field($line, 8, 8);

            if ($type === '0') {
                $fileHeader = $line;
            } elseif ($type === '1') {
                $batchHeader = $line;
            } elseif ($type === '3') {
                $segment = strtoupper(self::field($line, 14, 14));
                if ($segment === 'P') {
                    if ($current) {
                        $titles[] = $current;
                    }
                    $current = [
                        'nossoNumero'    => self::field($line, 38, 57),
                        'wallet'         => self::field($line, 58, 58) ?: '1',
                        'documentNumber' => self::field($line, 63, 77),
                        'dueDate'        => self::field($line, 78, 85),
                        'value'          => self::field($line, 86, 100),
                        'control'        => self::field($line, 196, 220),
                        'payerDocument'  => '',
                        'payerName'      => '',
                    ];
                } elseif ($segment === 'Q' && $current) {
                    $current['payerDocument'] = self::field($line, 19, 33);
                    $current['payerName']     = self::field($line, 34, 73);
                }
            }
        }
        if ($current) {
            $titles[] = $current;
        }

        if (empty($titles)) {
            throw new ValidationException('Nenhum título (segmento P) encontrado na remessa.');
        }

        $today  = date('dmY');
        $output = [];

        $output[] = self::set($fileHeader ?: self::blank($bank, '0'), [[143, 143, '2'], [144, 151, $today]]);
        $output[] = self::set($batchHeader ?: self::blank($bank, '1'), [[9, 9, 'T']]);

        $seq = 0;
        foreach ($titles as $i => $t) {
            [$occurrence, $reason] = self::resolveOccurrence($profile, $spec, $i, $t['control']);
            $isSettlement = in_array($occurrence, $profile['settlement'], true);
            $isRejection  = in_array($occurrence, $profile['rejection'], true);

            $seq++;
            $output[] = self::set(self::blank($bank, '3'), [
                [9, 13, self::num($seq, 5)],
                [14, 14, 'T'],
                [16, 17, $occurrence],
                [38, 57, str_pad($t['nossoNumero'], 20, ' ', STR_PAD_RIGHT)],
                [58, 58, $t['wallet']],
                [59, 73, self::num($t['documentNumber'], 15)],
                [74, 81, $t['dueDate'] ?: $today],
                [82, 96, self::num($t['value'], 15)],
                [106, 130, str_pad($t['control'], 25, ' ', STR_PAD_RIGHT)],
                [134, 148, self::num($t['payerDocument'], 15)],
                [149, 188, str_pad(substr($t['payerName'], 0, 40), 40, ' ', STR_PAD_RIGHT)],
                [199, 213, self::num(0, 15)],
                [214, 223, $isRejection ? str_pad($reason, 10, '0', STR_PAD_RIGHT) : self::num(0, 10)],
            ]);

            $seq++;
            $output[] = self::set(self::blank($bank, '3'), [
                [9, 13, self::num($seq, 5)],
                [14, 14, 'U'],
                [18, 32, self::num(0, 15)],
                [33, 47, self::num(0, 15)],
                [48, 62, self::num(0, 15)],
                [63, 77, self::num(0, 15)],
                [78, 92, self::num($isSettlement ? $t['value'] : 0, 15)],
                [93, 107, self::num(0, 15)],
                [108, 122, self::num(0, 15)],
                [123, 137, self::num(0, 15)],
                [138, 145, $isSettlement ? $today : self::num(0, 8)],
                [146, 153, $isSettlement ? $today : self::num(0, 8)],
            ]);
        }

        $batchRecords = count($titles) * 2 + 2;
        $output[] = self::set(self::blank($bank, '5'), [[4, 7, '0001'], [18, 23, self::num($batchRecords, 6)]]);
        $fileRecords = $batchRecords + 2;
        $output[] = self::set(self::blank($bank, '9'), [[4, 7, '9999'], [18, 23, self::num(1, 6)], [24, 29, self::num($fileRecords, 6)]]);

        return implode("\r\n", $output) . "\r\n";
    }

    /**
     * Transforma a remessa CNAB400 do Banco Inter em retorno, segundo o profile do banco.
     *
     * Layout de remessa e de retorno usam faixas de posição DIFERENTES para o mesmo dado
     * (confirmado em Cnab\Remessa\Cnab400\Banco\Inter::addBoleto() x
     * Cnab\Retorno\Cnab400\Banco\Inter::processarDetalhe()) — os campos são lidos pela posição
     * de remessa e regravados na posição de retorno, nunca copiados byte a byte. O nosso número
     * é fabricado aqui: no Inter é o banco quem atribui esse número, então a remessa de registro
     * sempre o envia zerado — só existe de verdade a partir do retorno.
     */
    private static function cnab400Inter(array $profile, array $lines, $spec)
    {
        $bank   = $profile['bank'];
        $titles = [];

        foreach ($lines as $line) {
            $line = str_pad($line, 400, ' ');
            if (self::field($line, 1, 1) !== '1') continue;

            $titles[] = [
                'control'        => self::field($line, 38, 62),
                'documentNumber' => self::field($line, 111, 120),
                'dueDate'        => self::field($line, 121, 126),
                'value'          => self::field($line, 127, 139),
            ];
        }

        if (empty($titles)) {
            throw new ValidationException('Nenhum título (registro tipo 1) encontrado na remessa.');
        }

        $today  = date('dmy');
        $output = [];

        $output[] = self::set(str_repeat(' ', 400), [
            [1, 1, '0'],
            [2, 2, '2'],
            [3, 9, 'RETORNO'],
            [77, 79, $bank],
            [95, 100, $today],
            [395, 400, self::num(1, 6)],
        ]);

        $seq        = 1;
        $totalValor = 0;
        foreach ($titles as $i => $t) {
            [$occurrence, $reason] = self::resolveOccurrence($profile, $spec, $i, $t['control']);
            $isSettlement = in_array($occurrence, $profile['settlement'], true);
            $isRejection  = in_array($occurrence, $profile['rejection'], true);

            $seq++;
            $output[] = self::set(str_repeat(' ', 400), [
                [1, 1, '1'],
                [21, 23, '112'], // carteira
                [38, 62, str_pad(trim($t['control']), 25, ' ', STR_PAD_RIGHT)],
                [71, 81, self::num(90000000000 + $i, 11)], // nosso número — atribuído pelo Inter, fabricado aqui
                [90, 91, $occurrence],
                [92, 97, $today],
                [98, 107, str_pad(trim($t['documentNumber']), 10, ' ', STR_PAD_RIGHT)],
                [119, 124, $t['dueDate'] ?: $today],
                [125, 137, self::num($t['value'], 13)],
                [160, 172, self::num($isSettlement ? $t['value'] : 0, 13)], // valor recebido
                [173, 178, $isSettlement ? $today : '000000'],
                [241, 380, $isRejection ? str_pad(substr('Entrada rejeitada' . $reason, 0, 140), 140, ' ', STR_PAD_RIGHT) : str_repeat(' ', 140)],
                [395, 400, self::num($seq, 6)],
            ]);

            if ($isSettlement) $totalValor += (int) preg_replace('/\D/', '', $t['value']);
        }

        $seq++;
        $output[] = self::set(str_repeat(' ', 400), [
            [1, 1, '9'],
            [18, 25, self::num(count($titles), 8)],
            [121, 132, self::num($totalValor, 12)],
            [395, 400, self::num($seq, 6)],
        ]);

        return implode("\r\n", $output) . "\r\n";
    }

    /**
     * Decide a ocorrência (código do banco) e o motivo (rejeição) de um título conforme o $spec.
     * Aceita nome canônico (confirm/pay/cancel/reject/change) ou código bruto do banco.
     *
     * @return array [string occurrence, string reason]
     */
    private static function resolveOccurrence(array $profile, $spec, int $index, string $control)
    {
        if ($spec === null) {
            $value = self::DEFAULT_MIX[$index % count(self::DEFAULT_MIX)];
        } elseif (is_string($spec)) {
            $value = $spec;
        } elseif (is_array($spec)) {
            $control = trim($control);
            if (array_key_exists($control, $spec)) {
                $value = $spec[$control];
            } else {
                $list = array_values($spec);
                $value = $list[$index % count($list)];
            }
        } else {
            $value = 'confirm';
        }

        $parts = explode(':', (string) $value);
        $code  = trim($parts[0]);

        // Traduz nome canônico -> código do banco; se já for código bruto, mantém.
        if (isset($profile['occurrences'][$code])) {
            $code = $profile['occurrences'][$code];
        }

        $occurrence = str_pad($code, 2, '0', STR_PAD_LEFT);
        $reason     = $parts[1] ?? $profile['defaultRejectionReason'];

        return [$occurrence, $reason];
    }

    /** Lê um campo 1-based [start, end] da linha, sem espaços nas pontas. */
    private static function field(string $line, int $start, int $end)
    {
        return trim(substr($line, $start - 1, $end - $start + 1));
    }

    /** Aplica os campos [start, end, value] numa linha (1-based), mantendo 240 chars. */
    private static function set(string $line, array $fields)
    {
        $line = str_pad($line, 240, ' ');
        foreach ($fields as [$start, $end, $value]) {
            $len   = $end - $start + 1;
            $value = substr((string) $value, 0, $len);
            $value = str_pad($value, $len, ' ', STR_PAD_RIGHT);
            $line  = substr_replace($line, $value, $start - 1, $len);
        }
        return $line;
    }

    /** Campo numérico zero-filled à esquerda. Aceita string já numérica (mantém) ou número. */
    private static function num($value, int $len)
    {
        $value = preg_replace('/\D/', '', (string) $value);
        $value = $value === '' ? '0' : $value;
        return str_pad(substr($value, -$len), $len, '0', STR_PAD_LEFT);
    }

    /** Linha vazia de 240 chars com banco (1-3) e tipo de registro (pos 8) setados. */
    private static function blank(string $bank, string $recordType)
    {
        return self::set(str_repeat(' ', 240), [[1, 3, $bank], [8, 8, $recordType]]);
    }
}
