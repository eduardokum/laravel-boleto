<?php

namespace Eduardokum\LaravelBoleto;

use Exception;
use Carbon\Carbon;
use NumberFormatter;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Eduardokum\LaravelBoleto\Boleto\AbstractBoleto;
use Eduardokum\LaravelBoleto\Exception\ValidationException;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;

/**
 * Class Util
 *
 * @TODO validar tamanho nosso numero
 * @TODO validar processar
 * @TODO validar float nos numeros
 */
final class Util
{
    /**
     * @var string[]
     */
    public static $bancos = [
        '246' => 'Banco ABC Brasil S.A.',
        '025' => 'Banco Alfa S.A.',
        '641' => 'Banco Alvorada S.A.',
        '029' => 'Banco Banerj S.A.',
        '000' => 'Banco Bankpar S.A.',
        '740' => 'Banco Barclays S.A.',
        '107' => 'Banco BBM S.A.',
        '077' => 'Banco Inter S.A.',
        '031' => 'Banco Beg S.A.',
        '739' => 'Banco BGN S.A.',
        '096' => 'Banco BM&F de Serviços de Liquidação e Custódia S.A',
        '318' => 'Banco BMG S.A.',
        '752' => 'Banco BNP Paribas Brasil S.A.',
        '248' => 'Banco Boavista Interatlântico S.A.',
        '218' => 'Banco Bonsucesso S.A.',
        '065' => 'Banco Bracce S.A.',
        '036' => 'Banco Bradesco BBI S.A.',
        '204' => 'Banco Bradesco Cartões S.A.',
        '394' => 'Banco Bradesco Financiamentos S.A.',
        '237' => 'Banco Bradesco S.A.',
        '225' => 'Banco Brascan S.A.',
        '208' => 'Banco BTG Pactual S.A.',
        '044' => 'Banco BVA S.A.',
        '263' => 'Banco Cacique S.A.',
        '473' => 'Banco Caixa Geral - Brasil S.A.',
        '040' => 'Banco Cargill S.A.',
        '233' => 'Banco Cifra S.A.',
        '745' => 'Banco Citibank S.A.',
        'M08' => 'Banco Citicard S.A.',
        'M19' => 'Banco CNH Capital S.A.',
        '215' => 'Banco Comercial e de Investimento Sudameris S.A.',
        '756' => 'Banco Cooperativo do Brasil S.A. - BANCOOB',
        '748' => 'Banco Cooperativo Sicredi S.A.',
        '222' => 'Banco Credit Agricole Brasil S.A.',
        '505' => 'Banco Credit Suisse (Brasil) S.A.',
        '229' => 'Banco Cruzeiro do Sul S.A.',
        '003' => 'Banco da Amazônia S.A.',
        '083' => 'Banco da China Brasil S.A.',
        '707' => 'Banco Daycoval S.A.',
        'M06' => 'Banco de Lage Landen Brasil S.A.',
        '024' => 'Banco de Pernambuco S.A. - BANDEPE',
        '456' => 'Banco de Tokyo-Mitsubishi UFJ Brasil S.A.',
        '214' => 'Banco Dibens S.A.',
        '001' => 'Banco do Brasil S.A.',
        '047' => 'Banco do Estado de Sergipe S.A.',
        '037' => 'Banco do Estado do Pará S.A.',
        '041' => 'Banco do Estado do Rio Grande do Sul S.A.',
        '004' => 'Banco do Nordeste do Brasil S.A.',
        '265' => 'Banco Fator S.A.',
        'M03' => 'Banco Fiat S.A.',
        '224' => 'Banco Fibra S.A.',
        '626' => 'Banco Ficsa S.A.',
        'M18' => 'Banco Ford S.A.',
        'M07' => 'Banco GMAC S.A.',
        '612' => 'Banco Guanabara S.A.',
        'M22' => 'Banco Honda S.A.',
        '063' => 'Banco Ibi S.A. Banco Múltiplo',
        'M11' => 'Banco IBM S.A.',
        '604' => 'Banco Industrial do Brasil S.A.',
        '320' => 'Banco Industrial e Comercial S.A.',
        '653' => 'Banco Indusval S.A.',
        '249' => 'Banco Investcred Unibanco S.A.',
        '184' => 'Banco Itaú BBA S.A.',
        '479' => 'Banco ItaúBank S.A',
        'M09' => 'Banco Itaucred Financiamentos S.A.',
        '376' => 'Banco J. P. Morgan S.A.',
        '074' => 'Banco J. 074 S.A.',
        '217' => 'Banco John Deere S.A.',
        '600' => 'Banco Luso Brasileiro S.A.',
        '389' => 'Banco Mercantil do Brasil S.A.',
        '746' => 'Banco Modal S.A.',
        '045' => 'Banco Opportunity S.A.',
        '079' => 'Banco Original do Agronegócio S.A.',
        '623' => 'Banco Panamericano S.A.',
        '611' => 'Banco Paulista S.A.',
        '643' => 'Banco Pine S.A.',
        '638' => 'Banco Prosper S.A.',
        '747' => 'Banco Rabobank International Brasil S.A.',
        '356' => 'Banco Real S.A.',
        '633' => 'Banco Rendimento S.A.',
        'M16' => 'Banco Rodobens S.A.',
        '072' => 'Banco Rural Mais S.A.',
        '453' => 'Banco Rural S.A.',
        '422' => 'Banco 422 S.A.',
        '033' => 'Banco Santander (Brasil) S.A.',
        '749' => 'Banco Simples S.A.',
        '366' => 'Banco Société Générale Brasil S.A.',
        '637' => 'Banco Sofisa S.A.',
        '012' => 'Banco Standard de Investimentos S.A.',
        '464' => 'Banco Sumitomo Mitsui Brasileiro S.A.',
        '082' => 'Banco Topázio S.A.',
        'M20' => 'Banco Toyota do Brasil S.A.',
        '634' => 'Banco Triângulo S.A.',
        '136' => 'Banco Unicred do Brasil',
        'M14' => 'Banco Volkswagen S.A.',
        'M23' => 'Banco Volvo (Brasil) S.A.',
        '655' => 'Banco Votorantim S.A.',
        '610' => 'Banco VR S.A.',
        '119' => 'Banco Western Union do Brasil S.A.',
        '370' => 'Banco WestLB do Brasil S.A.',
        '021' => 'BANESTES S.A. Banco do Estado do Espírito Santo',
        '719' => 'Banif-Banco Internacional do Funchal (Brasil)S.A.',
        '755' => 'Bank of America Merrill Lynch Banco Múltiplo S.A.',
        '073' => 'BB Banco Popular do Brasil S.A.',
        '250' => 'BCV - Banco de Crédito e Varejo S.A.',
        '078' => 'BES Investimento do Brasil S.A.-Banco de Investimento',
        '069' => 'BPN Brasil Banco Múltiplo S.A.',
        '070' => 'BRB - Banco de Brasília S.A.',
        '104' => 'Caixa Econômica Federal',
        '477' => 'Citibank S.A.',
        '133' => 'Cresol',
        '081' => 'Concórdia Banco S.A.',
        '487' => 'Deutsche Bank S.A. - Banco Alemão',
        '064' => 'Goldman Sachs do Brasil Banco Múltiplo S.A.',
        '062' => 'Hipercard Banco Múltiplo S.A.',
        '399' => 'HSBC Bank Brasil S.A.',
        '492' => 'ING Bank N.V.',
        '652' => 'Itaú Unibanco Holding S.A.',
        '341' => 'Itaú Unibanco S.A.',
        '435' => 'Delcred SCD S.A',
        '488' => 'JPMorgan Chase Bank',
        '751' => 'Scotiabank Brasil S.A. Banco Múltiplo',
        '409' => 'UNIBANCO - União de Bancos Brasileiros S.A.',
        '230' => 'Unicard Banco Múltiplo S.A.',
        '712' => 'Banco Ourinvest',
        '085' => 'AILOS - Sistema de Cooperativa de Crédito',
        'XXX' => 'Desconhecido',
    ];

    /**
     * Retorna a String em MAIUSCULO
     *
     * @param string $string
     *
     * @return string
     */
    public static function upper($string)
    {
        return strtr(mb_strtoupper($string), 'àáâãäåæçèéêëìíîïðñòóôõö÷øùüúþÿ', 'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖ×ØÙÜÚÞß');
    }

    /**
     * Retorna a String em minusculo
     *
     * @param string $string
     *
     * @return string
     */
    public static function lower($string)
    {
        return strtr(mb_strtolower($string), 'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖ×ØÙÜÚÞß', 'àáâãäåæçèéêëìíîïðñòóôõö÷øùüúþÿ');
    }

    /**
     * Retorna a String em minusculo
     *
     * @param string $string
     *
     * @return string
     */
    public static function upFirst($string)
    {
        return ucfirst(self::lower($string));
    }

    /**
     * Retorna somente as letras da string
     *
     * @param string $string
     *
     * @return string
     */
    public static function lettersOnly($string)
    {
        return preg_replace('/[^[:alpha:]]/', '', $string);
    }

    /**
     * Retorna somente as letras da string
     *
     * @param string $string
     *
     * @return string
     */
    public static function onlyLetters($string)
    {
        return self::lettersOnly($string);
    }

    /**
     * Retorna somente as letras da string
     *
     * @param string $string
     *
     * @return string
     */
    public static function lettersNot($string)
    {
        return preg_replace('/[[:alpha:]]/', '', $string);
    }

    /**
     * Retorna somente as letras da string
     *
     * @param string $string
     *
     * @return string
     */
    public static function notLetters($string)
    {
        return self::lettersNot($string);
    }

    /**
     * Retorna somente os digitos da string
     *
     * @param string $string
     *
     * @return string
     */
    public static function numbersOnly($string)
    {
        return preg_replace('/[^[:digit:]]/', '', $string);
    }

    /**
     * Retorna somente os digitos da string
     *
     * @param string $string
     *
     * @return string
     */
    public static function onlyNumbers($string)
    {
        return self::numbersOnly($string);
    }

    /**
     * Retorna somente os digitos da string
     *
     * @param string $string
     *
     * @return string
     */
    public static function numbersNot($string)
    {
        return preg_replace('/[[:digit:]]/', '', $string);
    }

    /**
     * Retorna somente os digitos da string
     *
     * @param string $string
     *
     * @return string
     */
    public static function notNumbers($string)
    {
        return self::numbersNot($string);
    }

    /**
     * Retorna somente alfanumericos
     *
     * @param string $string
     *
     * @return string
     */
    public static function alphanumberOnly($string)
    {
        return preg_replace('/[^[:alnum:]]/', '', $string);
    }

    /**
     * Retorna somente alfanumericos
     *
     * @param string $string
     *
     * @return string
     */
    public static function onlyAlphanumber($string)
    {
        return self::alphanumberOnly($string);
    }

    /**
     * Função para limpar acentos de uma string
     *
     * @param string $string
     *
     * @return string
     */
    public static function normalizeChars($string)
    {
        $normalizeChars = [
            'Á' => 'A',
            'À' => 'A',
            'Â' => 'A',
            'Ã' => 'A',
            'Å' => 'A',
            'Ä' => 'A',
            'Æ' => 'AE',
            'Ç' => 'C',
            'É' => 'E',
            'È' => 'E',
            'Ê' => 'E',
            'Ë' => 'E',
            'Í' => 'I',
            'Ì' => 'I',
            'Î' => 'I',
            'Ï' => 'I',
            'Ð' => 'Eth',
            'Ñ' => 'N',
            'Ó' => 'O',
            'Ò' => 'O',
            'Ô' => 'O',
            'Õ' => 'O',
            'Ö' => 'O',
            'Ø' => 'O',
            'Ú' => 'U',
            'Ù' => 'U',
            'Û' => 'U',
            'Ü' => 'U',
            'Ý' => 'Y',
            'Ŕ' => 'R',

            'á' => 'a',
            'à' => 'a',
            'â' => 'a',
            'ã' => 'a',
            'å' => 'a',
            'ä' => 'a',
            'æ' => 'ae',
            'ç' => 'c',
            'é' => 'e',
            'è' => 'e',
            'ê' => 'e',
            'ë' => 'e',
            'í' => 'i',
            'ì' => 'i',
            'î' => 'i',
            'ï' => 'i',
            'ð' => 'eth',
            'ñ' => 'n',
            'ó' => 'o',
            'ò' => 'o',
            'ô' => 'o',
            'õ' => 'o',
            'ö' => 'o',
            'ø' => 'o',
            'ú' => 'u',
            'ù' => 'u',
            'û' => 'u',
            'ü' => 'u',
            'ý' => 'y',
            'ŕ' => 'r',
            'ÿ' => 'y',

            'ß' => 'sz',
            'þ' => 'thorn',
            'º' => '',
            'ª' => '',
            '°' => '',
        ];

        return preg_replace('/[^0-9a-zA-Z !+=*\-,.;:%@_]/', '', strtr($string, $normalizeChars));
    }

    /**
     * Mostra o Valor no float Formatado
     *
     * @param string $number
     * @param int $decimals
     * @param bool $showThousands
     * @return string
     */
    public static function nFloat($number, $decimals = 2, $showThousands = false)
    {
        if (is_null($number) || empty(self::onlyNumbers($number)) || floatval($number) == 0) {
            return 0;
        }
        $pontuacao = preg_replace('/[0-9]/', '', $number);
        $locale = (mb_substr($pontuacao, -1, 1) == ',') ? 'pt-BR' : 'en-US';
        $formater = new NumberFormatter($locale, NumberFormatter::DECIMAL);

        if ($decimals === false) {
            $decimals = 2;
            preg_match_all('/[0-9][^0-9]([0-9]+)/', $number, $matches);
            if (! empty($matches[1])) {
                $decimals = mb_strlen(rtrim($matches[1][0], 0));
            }
        }

        return number_format($formater->parse($number, NumberFormatter::TYPE_DOUBLE), $decimals, '.', ($showThousands ? ',' : ''));
    }

    /**
     * Mostra o Valor no real Formatado
     *
     * @param float $number
     * @param bool $fixed
     * @param bool $symbol
     * @param int $decimals
     * @return string
     */
    public static function nReal($number, $decimals = 2, $symbol = true, $fixed = true)
    {
        if (is_null($number) || empty(self::onlyNumbers($number))) {
            return '';
        }
        $formater = new NumberFormatter('pt-BR', NumberFormatter::CURRENCY);
        $formater->setAttribute(NumberFormatter::MIN_FRACTION_DIGITS, ($fixed ? $decimals : 1));
        if ($decimals === false) {
            $decimals = 2;
            preg_match_all('/[0-9][^0-9]([0-9]+)/', $number, $matches);
            if (! empty($matches[1])) {
                $decimals = mb_strlen(rtrim($matches[1][0], 0));
            }
        }
        $formater->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, $decimals);
        $pattern = substr($formater->getPattern(), strpos($formater->getPattern(), '#'));
        if ($symbol) {
            $pattern = '¤ ' . $pattern;
        }
        $formater->setPattern($pattern);

        return trim($formater->formatCurrency($number, $formater->getTextAttribute(NumberFormatter::CURRENCY_CODE)));
    }

    /**
     * Return percent x of y;
     *
     * @param $big
     * @param $small
     * @param int $defaultOnZero
     *
     * @return string
     */
    public static function percentOf($big, $small, $defaultOnZero = 0)
    {
        $result = $big > 0.01 ? (($small * 100) / $big) : $defaultOnZero;

        return self::nFloat($result);
    }

    /**
     * Return percentage of value;
     *
     * @param $big
     * @param $percent
     *
     * @return string
     */
    public static function percent($big, $percent)
    {
        if ($percent < 0.01) {
            return 0;
        }

        return self::nFloat($big * ($percent / 100));
    }

    /**
     * Função para mascarar uma string, mascara tipo ##-##-##
     *
     * @param string $val
     * @param string $mask
     *
     * @return string
     */
    public static function maskString($val, $mask)
    {
        if (empty($val)) {
            return $val;
        }
        $maskared = '';
        $k = 0;
        if (is_numeric($val)) {
            $val = sprintf('%0' . mb_strlen(preg_replace('/[^#]/', '', $mask)) . 's', $val);
        }
        for ($i = 0; $i <= mb_strlen($mask) - 1; $i++) {
            if ($mask[$i] == '#') {
                if (isset($val[$k])) {
                    $maskared .= $val[$k++];
                }
            } else {
                if (isset($mask[$i])) {
                    $maskared .= $mask[$i];
                }
            }
        }

        return $maskared;
    }

    /**
     * @param $n
     * @param int $loop
     * @param $insert
     *
     * @return string
     */
    public static function numberFormatGeral($n, $loop, $insert = 0)
    {
        // Removo os caracteras a mais do que o pad solicitado caso a string seja maior
        $n = mb_substr(self::onlyNumbers($n), 0, $loop);

        return str_pad($n, $loop, $insert, STR_PAD_LEFT);
    }

    /**
     * @param        $tipo
     * @param        $valor
     * @param int $tamanho
     * @param int $dec
     * @param string $sFill
     *
     * @return string
     * @throws ValidationException
     */
    public static function formatCnab($tipo, $valor, $tamanho, $dec = 0, $sFill = '')
    {
        $tipo = self::upper($tipo);
        $valor = self::upper(self::normalizeChars($valor));
        if (in_array($tipo, ['9', 9, 'N', '9L', 'NL'])) {
            if ($tipo == '9L' || $tipo == 'NL') {
                $valor = self::onlyNumbers($valor);
            }
            $left = '';
            $sFill = 0;
            $type = 's';
            $valor = ($dec > 0) ? sprintf("%.{$dec}f", $valor) : $valor;
            $valor = str_replace([',', '.'], '', $valor);
        } elseif (in_array($tipo, ['A', 'X', 'Z'])) { // Adiciona 'x' como uma condição válida
            $left = '-';
            $type = 's';
        } else {
            throw new ValidationException('Tipo inválido');
        }

        // Verifica se o tipo é 'x' minúsculo e então retorna a string em minúsculas
        if ($tipo === 'Z') {
            return strtolower(sprintf("%{$left}{$sFill}{$tamanho}{$type}", mb_substr($valor, 0, $tamanho)));
        } else {
            return sprintf("%{$left}{$sFill}{$tamanho}{$type}", mb_substr($valor, 0, $tamanho));
        }
    }

    /**
     * @param Carbon|string $date
     * @param string $format
     *
     * @return int
     */
    public static function fatorVencimento($date, $format = 'Y-m-d')
    {
        $date = ($date instanceof Carbon) ? $date : Carbon::createFromFormat($format, $date)->setTime(0, 0, 0);
        $fator = (new Carbon('1997-10-07'))->diffInDays($date);
        $limit = $fator % 9000;
        if ($limit >= 1000) {
            return $limit;
        }

        return $limit + 9000;
    }

    /**
     * @param        $date
     * @param string $format
     *
     * @return string
     */
    public static function dataJuliano($date, $format = 'Y-m-d')
    {
        $date = ($date instanceof Carbon) ? $date : Carbon::createFromFormat($format, $date);
        $dateDiff = $date->copy()->day(31)->month(12)->subYear()->diffInDays($date);

        return $dateDiff . mb_substr($date->year, -1);
    }

    /**
     * @param        $factor
     * @param string $format
     *
     * @return bool|string
     */
    public static function fatorVencimentoBack($factor, $format = 'Y-m-d')
    {
        $date = Carbon::create(1997, 10, 7, 0, 0, 0)->addDays((int) $factor);

        return $format ? $date->format($format) : $date;
    }

    /**
     * @param     $n
     * @param int $factor
     * @param int $base
     * @param int $x10
     * @param int $resto10
     *
     * @return int
     */
    public static function modulo11($n, $factor = 2, $base = 9, $x10 = 0, $resto10 = 0)
    {
        $sum = 0;
        for ($i = mb_strlen($n); $i > 0; $i--) {
            $sum += ((int) mb_substr($n, $i - 1, 1)) * $factor;
            if ($factor == $base) {
                $factor = 1;
            }
            $factor++;
        }

        if ($x10 == 0) {
            $sum *= 10;
            $digito = $sum % 11;
            if ($digito == 10) {
                $digito = $resto10;
            }

            return $digito;
        }

        return $sum % 11;
    }

    /**
     * @param $n
     *
     * @return int
     */
    public static function modulo10($n)
    {
        $chars = array_reverse(str_split($n, 1));
        $odd = array_intersect_key($chars, array_fill_keys(range(1, count($chars), 2), null));
        $even = array_intersect_key($chars, array_fill_keys(range(0, count($chars), 2), null));
        $even = array_map(function ($n) {
            return ($n >= 5) ? 2 * $n - 9 : 2 * $n;
        }, $even);
        $total = array_sum($odd) + array_sum($even);

        return ((floor($total / 10) + 1) * 10 - $total) % 10;
    }

    /**
     * @param array $a
     *
     * @return string
     * @throws ValidationException
     */
    public static function array2Controle(array $a)
    {
        if (preg_match('/[0-9]/', implode('', array_keys($a)))) {
            throw new ValidationException('Somente chave alfanumérica no array, para separar o controle pela chave');
        }

        $controle = '';
        foreach ($a as $key => $value) {
            $controle .= sprintf('%s%s', $key, $value);
        }

        if (mb_strlen($controle) > 25) {
            throw new ValidationException('Controle muito grande, máximo permitido de 25 caracteres');
        }

        return $controle;
    }

    /**
     * @param $controle
     *
     * @return array
     */
    public static function controle2array($controle)
    {
        $matches = [];
        $matches_founded = [];
        preg_match_all('/(([A-Za-zÀ-Úà-ú]+)([0-9]*))/', $controle, $matches, PREG_SET_ORDER);
        if ($matches) {
            foreach ($matches as $match) {
                $matches_founded[$match[2]] = (int) $match[3];
            }

            return $matches_founded;
        }

        return [$controle];
    }

    /**
     * Pela remessa cria um retorno fake para testes.
     *
     * @param $file
     * @param string $ocorrencia
     *
     * @return string
     * @throws ValidationException
     * @codeCoverageIgnore
     */
    public static function criarRetornoFake($file, $ocorrencia = '02')
    {
        $remessa = file($file);
        $banco = self::remove(77, 79, $remessa[0]);
        $retorno[0] = array_fill(0, 400, '0');

        // header
        self::adiciona($retorno[0], 1, 9, '02RETORNO');
        switch ($banco) {
            case BoletoContract::COD_BANCO_BB:
                self::adiciona($retorno[0], 27, 30, self::remove(27, 30, $remessa[0]));
                self::adiciona($retorno[0], 31, 31, self::remove(31, 31, $remessa[0]));
                self::adiciona($retorno[0], 32, 39, self::remove(32, 39, $remessa[0]));
                self::adiciona($retorno[0], 40, 40, self::remove(40, 40, $remessa[0]));
                self::adiciona($retorno[0], 150, 156, self::remove(130, 136, $remessa[0]));
                break;
            case BoletoContract::COD_BANCO_SANTANDER:
                self::adiciona($retorno[0], 27, 30, self::remove(27, 30, $remessa[0]));
                self::adiciona($retorno[0], 39, 46, '0' . self::remove(40, 46, $remessa[0]));
                break;
            case BoletoContract::COD_BANCO_CEF:
                self::adiciona($retorno[0], 27, 30, self::remove(27, 30, $remessa[0]));
                self::adiciona($retorno[0], 31, 36, self::remove(31, 36, $remessa[0]));
                break;
            case BoletoContract::COD_BANCO_BRADESCO:
            case BoletoContract::COD_BANCO_OURINVEST:
            case BoletoContract::COD_BANCO_CRESOL:
                self::adiciona($retorno[0], 27, 46, self::remove(27, 46, $remessa[0]));
                break;
            case BoletoContract::COD_BANCO_ITAU:
                // Header do retorno conforme especificação CNAB400 do Itaú
                self::adiciona($retorno[0], 1, 1, '0'); // Tipo de Registro
                self::adiciona($retorno[0], 2, 2, '2'); // Código de Retorno
                self::adiciona($retorno[0], 3, 9, 'RETORNO'); // Literal de Retorno
                self::adiciona($retorno[0], 10, 11, '01'); // Código do Serviço
                self::adiciona($retorno[0], 12, 26, str_pad('COBRANCA', 15, ' ', STR_PAD_RIGHT)); // Literal de Serviço
                self::adiciona($retorno[0], 27, 30, self::remove(27, 30, $remessa[0])); // Agência
                self::adiciona($retorno[0], 31, 32, '00'); // Zeros
                self::adiciona($retorno[0], 33, 37, self::remove(33, 37, $remessa[0])); // Conta
                self::adiciona($retorno[0], 38, 38, self::remove(38, 38, $remessa[0])); // DAC
                self::adiciona($retorno[0], 39, 46, str_repeat(' ', 8)); // Brancos
                self::adiciona($retorno[0], 47, 76, self::remove(47, 76, $remessa[0])); // Nome da Empresa
                self::adiciona($retorno[0], 77, 79, $banco); // Código do Banco
                self::adiciona($retorno[0], 80, 94, str_pad('BANCO ITAU SA', 15, ' ', STR_PAD_RIGHT)); // Nome do Banco
                self::adiciona($retorno[0], 95, 100, date('dmy')); // Data de Geração
                self::adiciona($retorno[0], 101, 105, str_repeat('0', 5)); // Densidade
                self::adiciona($retorno[0], 106, 108, 'BPI'); // Unidade de Densidade
                self::adiciona($retorno[0], 109, 113, str_repeat('0', 5)); // Nº Seq. Arquivo Ret.
                self::adiciona($retorno[0], 114, 119, date('dmy')); // Data de Crédito
                self::adiciona($retorno[0], 120, 394, str_repeat(' ', 275)); // Brancos
                self::adiciona($retorno[0], 395, 400, self::remove(395, 400, $remessa[0])); // Número Sequencial
                break;
            case BoletoContract::COD_BANCO_HSBC:
                self::adiciona($retorno[0], 28, 31, self::remove(28, 31, $remessa[0]));
                self::adiciona($retorno[0], 38, 43, self::remove(38, 43, $remessa[0]));
                self::adiciona($retorno[0], 44, 44, self::remove(44, 44, $remessa[0]));
                break;
            case BoletoContract::COD_BANCO_SICREDI:
                self::adiciona($retorno[0], 27, 31, self::remove(27, 31, $remessa[0]));
                self::adiciona($retorno[0], 32, 45, self::remove(32, 45, $remessa[0]));
                self::adiciona($retorno[0], 111, 117, self::remove(111, 117, $remessa[0]));
                break;
            case BoletoContract::COD_BANCO_BANRISUL:
                self::adiciona($retorno[0], 27, 39, self::remove(18, 30, $remessa[0]));
                self::adiciona($retorno[0], 47, 76, self::remove(47, 76, $remessa[0]));
                break;
            case BoletoContract::COD_BANCO_INTER:
                // dd(self::remove(30, 37, $remessa[1]));
                self::adiciona($retorno[0], 1, 1, '0'); // Identificação do registro
                self::adiciona($retorno[0], 2, 2, '2'); // Identificação do arquivo retorno  
                self::adiciona($retorno[0], 3, 9, 'RETORNO'); // Literal remessa
                self::adiciona($retorno[0], 10, 11, '01'); // Código de serviço
                self::adiciona($retorno[0], 12, 26, str_pad('COBRANCA', 15, ' ', STR_PAD_RIGHT)); // Literal serviço
                self::adiciona($retorno[0], 27, 36, str_repeat(' ', 10)); // Campo em branco
                self::adiciona($retorno[0], 37, 45, '006550547'); // Número da conta corrente
                self::adiciona($retorno[0], 46, 46, '6'); // Dígito verificador da conta corrente
                self::adiciona($retorno[0], 47, 76, self::remove(47, 76, $remessa[0])); // Nome da empresa
                self::adiciona($retorno[0], 77, 79, $banco); // Código do banco na compensação
                self::adiciona($retorno[0], 80, 94, str_pad('INTER', 15, ' ', STR_PAD_RIGHT)); // Nome do banco por extenso
                self::adiciona($retorno[0], 95, 100, date('dmy')); // Data da gravação do arquivo
                self::adiciona($retorno[0], 101, 394, str_repeat(' ', 294)); // Campo em branco
                self::adiciona($retorno[0], 395, 400, self::remove(395, 400, $remessa[0])); // Número sequencial do registro
                break;
            case BoletoContract::COD_BANCO_BANCOOB:
                self::adiciona($retorno[0], 1, 1, '0'); // Identificação do registro
                self::adiciona($retorno[0], 2, 2, '2'); // Identificação do arquivo retorno  
                self::adiciona($retorno[0], 3, 9, 'RETORNO'); // Literal remessa
                self::adiciona($retorno[0], 10, 11, '01'); // Código de serviço
                self::adiciona($retorno[0], 12, 26, str_pad('COBRANCA', 15, ' ', STR_PAD_RIGHT)); // Literal serviço
                self::adiciona($retorno[0], 27, 46, str_pad('3249905801', 20, ' ', STR_PAD_LEFT)); // Código da empresa
                self::adiciona($retorno[0], 47, 76, self::remove(47, 76, $remessa[0])); // Nome da empresa
                self::adiciona($retorno[0], 77, 79, $banco); // Código do banco na compensação
                self::adiciona($retorno[0], 80, 94, str_pad('BANCOOB', 15, ' ', STR_PAD_RIGHT)); // Nome do banco por extenso
                self::adiciona($retorno[0], 95, 100, date('dmy')); // Data da gravação do arquivo
                self::adiciona($retorno[0], 101, 104, '3249'); // Número sequencial do registro
                self::adiciona($retorno[0], 105, 105, '0'); // Dígito verificador do número sequencial do registro
                self::adiciona($retorno[0], 106, 112, str_pad('17678', 7, '0', STR_PAD_LEFT)); // Dígito verificador do número sequencial do registro
                self::adiciona($retorno[0], 113, 113, '8'); // Dígito verificador do número sequencial do registro
                // Continuar
                self::adiciona($retorno[0], 395, 400, self::remove(395, 400, $remessa[0])); // Número sequencial do registro
            default:
                throw new ValidationException("Banco: $banco, inválido");
        }

        // Para o Banco Inter, os campos já foram preenchidos no case específico
        if ($banco !== BoletoContract::COD_BANCO_INTER) {
            self::adiciona($retorno[0], 77, 79, $banco);
            self::adiciona($retorno[0], 95, 100, date('dmy'));
            self::adiciona($retorno[0], 395, 400, sprintf('%06s', count($retorno)));
        }

        array_shift($remessa); // removo o header
        array_pop($remessa); // remove o trailer

        foreach ($remessa as $detalhe) {
            if (! in_array(self::remove(1, 1, $detalhe), [0, 1, 9])) {
                continue;
            }
            $i = count($retorno);

            $retorno[$i] = array_fill(0, 400, '0');

            self::adiciona($retorno[$i], 395, 400, sprintf('%06s', count($retorno)));
            switch ($banco) {

                case BoletoContract::COD_BANCO_BB:
                    if (self::remove(1, 1, $detalhe) != 7) {
                        unset($retorno[$i]);
                        continue 2;
                    }
                    self::adiciona($retorno[$i], 1, 1, '7');
                    self::adiciona($retorno[$i], 64, 80, self::remove(64, 80, $detalhe));
                    break;
                case BoletoContract::COD_BANCO_SANTANDER:
                    self::adiciona($retorno[$i], 63, 71, self::remove(63, 71, $detalhe));
                    self::adiciona($retorno[$i], 384, 385, self::remove(384, 385, $detalhe));
                    break;
                case BoletoContract::COD_BANCO_CEF:
                    self::adiciona($retorno[$i], 57, 73, self::remove(57, 73, $detalhe));
                    break;
                case BoletoContract::COD_BANCO_BRADESCO:
                case BoletoContract::COD_BANCO_OURINVEST:
                case BoletoContract::COD_BANCO_CRESOL:
                    self::adiciona($retorno[$i], 25, 29, self::remove(25, 29, $detalhe));
                    self::adiciona($retorno[$i], 30, 36, self::remove(30, 36, $detalhe));
                    self::adiciona($retorno[$i], 37, 37, self::remove(37, 37, $detalhe));
                    self::adiciona($retorno[$i], 71, 82, self::remove(71, 82, $detalhe));
                    break;
                case BoletoContract::COD_BANCO_ITAU:
                    // Detalhe do retorno CNAB400 do Itaú conforme especificação
                    self::adiciona($retorno[$i], 1, 1, '1'); // Tipo de Registro
                    self::adiciona($retorno[$i], 2, 3, self::remove(2, 3, $detalhe)); // Código de Inscrição (da remessa)
                    self::adiciona($retorno[$i], 4, 17, self::remove(4, 17, $detalhe)); // Número de Inscrição (da remessa)
                    self::adiciona($retorno[$i], 18, 21, self::remove(18, 21, $detalhe)); // Agência (da remessa)
                    self::adiciona($retorno[$i], 22, 23, '00'); // Zeros
                    self::adiciona($retorno[$i], 24, 28, self::remove(24, 28, $detalhe)); // Conta (da remessa)
                    self::adiciona($retorno[$i], 29, 29, self::remove(29, 29, $detalhe)); // DAC (da remessa)
                    self::adiciona($retorno[$i], 30, 37, str_repeat(' ', 8)); // Brancos
                    self::adiciona($retorno[$i], 38, 62, self::remove(38, 62, $detalhe)); // Uso da Empresa (da remessa)
                    self::adiciona($retorno[$i], 63, 70, self::remove(63, 70, $detalhe)); // Nosso Número (da remessa)
                    self::adiciona($retorno[$i], 71, 82, str_repeat(' ', 12)); // Brancos
                    self::adiciona($retorno[$i], 83, 85, self::remove(84, 86, $detalhe)); // Carteira (Número da Carteira)
                    // Nosso Número completo com DAC (posições 86-94)
                    $nossoNumeroCompleto = self::remove(63, 70, $detalhe);
                    self::adiciona($retorno[$i], 86, 93, $nossoNumeroCompleto); // Nosso Número (confirmação)
                    // O DAC do Nosso Número é o último dígito do nosso número completo da remessa
                    $nossoNumeroRemessa = self::remove(63, 70, $detalhe);
                    self::adiciona($retorno[$i], 94, 94, strlen($nossoNumeroRemessa) > 0 ? substr($nossoNumeroRemessa, -1) : '0'); // DAC Nosso Número
                    self::adiciona($retorno[$i], 95, 107, str_repeat(' ', 13)); // Brancos
                    self::adiciona($retorno[$i], 108, 108, self::remove(108, 108, $detalhe) ?: 'I'); // Carteira (Código da Carteira) - padrão 'I' se não houver
                    self::adiciona($retorno[$i], 109, 110, $ocorrencia); // Código de Ocorrência
                    self::adiciona($retorno[$i], 111, 116, date('dmy')); // Data de Ocorrência
                    self::adiciona($retorno[$i], 117, 126, self::remove(111, 120, $detalhe)); // Nº do Documento (da remessa)
                    self::adiciona($retorno[$i], 127, 134, $nossoNumeroCompleto); // Nosso Número (confirmação)
                    self::adiciona($retorno[$i], 135, 146, str_repeat(' ', 12)); // Brancos
                    self::adiciona($retorno[$i], 147, 152, self::remove(121, 126, $detalhe)); // Vencimento (da remessa)
                    self::adiciona($retorno[$i], 153, 165, self::remove(127, 139, $detalhe)); // Valor do Título (da remessa)
                    self::adiciona($retorno[$i], 166, 168, $banco); // Código do Banco
                    self::adiciona($retorno[$i], 169, 172, str_repeat('0', 4)); // Agência Cobradora
                    self::adiciona($retorno[$i], 173, 173, '0'); // DAC Ag. Cobradora
                    self::adiciona($retorno[$i], 174, 175, self::remove(148, 149, $detalhe)); // Espécie (da remessa)
                    self::adiciona($retorno[$i], 176, 188, str_repeat('0', 13)); // Tarifa de Cobrança
                    self::adiciona($retorno[$i], 189, 214, str_repeat(' ', 26)); // Brancos
                    self::adiciona($retorno[$i], 215, 227, str_repeat('0', 13)); // Valor do IOF
                    self::adiciona($retorno[$i], 228, 240, str_repeat('0', 13)); // Valor Abatimento
                    self::adiciona($retorno[$i], 241, 253, str_repeat('0', 13)); // Descontos
                    self::adiciona($retorno[$i], 254, 266, self::remove(127, 139, $detalhe)); // Valor Principal (mesmo do título)
                    self::adiciona($retorno[$i], 267, 279, str_repeat('0', 13)); // Juros de Mora/Multa
                    self::adiciona($retorno[$i], 280, 292, str_repeat('0', 13)); // Outros Créditos
                    self::adiciona($retorno[$i], 293, 293, 'N'); // Boleto DDA (N=Não)
                    self::adiciona($retorno[$i], 294, 295, str_repeat(' ', 2)); // Brancos
                    self::adiciona($retorno[$i], 296, 301, date('dmy')); // Data Crédito
                    self::adiciona($retorno[$i], 302, 305, str_repeat('0', 4)); // Instrução Cancelada
                    self::adiciona($retorno[$i], 306, 311, str_repeat(' ', 6)); // Brancos
                    self::adiciona($retorno[$i], 312, 324, str_repeat('0', 13)); // Zeros
                    self::adiciona($retorno[$i], 325, 354, self::remove(235, 264, $detalhe)); // Nome do Pagador (da remessa)
                    self::adiciona($retorno[$i], 355, 377, str_repeat(' ', 23)); // Brancos
                    self::adiciona($retorno[$i], 378, 385, str_repeat(' ', 8)); // Erros
                    self::adiciona($retorno[$i], 386, 392, str_repeat(' ', 7)); // Brancos
                    self::adiciona($retorno[$i], 393, 394, '01'); // Cód. de Liquidação
                    // Número Sequencial já foi preenchido na linha 849
                    break;
                case BoletoContract::COD_BANCO_HSBC:
                    self::adiciona($retorno[$i], 63, 73, self::remove(63, 73, $detalhe));
                    break;
                case BoletoContract::COD_BANCO_SICREDI:
                    self::adiciona($retorno[$i], 48, 62, '00000' . self::remove(48, 56, $detalhe));
                    break;
                case BoletoContract::COD_BANCO_BANRISUL:
                    self::adiciona($retorno[$i], 38, 62, self::remove(38, 62, $detalhe));
                    self::adiciona($retorno[$i], 63, 72, self::remove(111, 120, $detalhe));
                    self::adiciona($retorno[$i], 18, 30, self::remove(18, 30, $detalhe));
                    break;
                case BoletoContract::COD_BANCO_INTER:
                    self::adiciona($retorno[$i], 1, 1, '1'); // Identificação do registro
                    self::adiciona($retorno[$i], 2, 3, '02'); // Tipo de inscrição empresa (01=CPF, 02=CNPJ)
                    self::adiciona($retorno[$i], 4, 17, str_pad('37456158000180', 14, '0', STR_PAD_RIGHT)); // Número inscrição da empresa
                    self::adiciona($retorno[$i], 18, 20, '000'); // Zeros
                    self::adiciona($retorno[$i], 21, 23, self::remove(21, 23, $detalhe)); // Carteira
                    self::adiciona($retorno[$i], 24, 27, '0001'); // Agência da empresa beneficiária no Inter
                    self::adiciona($retorno[$i], 28, 37, self::remove(28, 37, $detalhe)); // Número da conta corrente da empresa
                    self::adiciona($retorno[$i], 38, 62, str_pad(self::remove(38, 62, $detalhe), 25, ' ', STR_PAD_RIGHT)); // Número controle do participante
                    self::adiciona($retorno[$i], 63, 70, '00000000'); // Zeros
                    self::adiciona($retorno[$i], 71, 81, str_pad(self::remove(38, 62, $detalhe), 11, '0', STR_PAD_LEFT)); // Nosso número
                    self::adiciona($retorno[$i], 82, 86, str_repeat(' ', 5)); // Campo em branco
                    self::adiciona($retorno[$i], 87, 89, '112'); // Carteira
                    self::adiciona($retorno[$i], 90, 91, '02'); // Identificação de ocorrência
                    self::adiciona($retorno[$i], 92, 97, date('dmy')); // Data da ocorrência no banco
                    self::adiciona($retorno[$i], 98, 107, str_pad(self::remove(111, 120, $detalhe), 10, ' ', STR_PAD_RIGHT)); // Número do documento "Seu número"
                    self::adiciona($retorno[$i], 108, 118, str_pad(self::remove(38, 62, $detalhe), 11, '0', STR_PAD_LEFT)); // Nosso número no banco
                    self::adiciona($retorno[$i], 119, 124, self::remove(121, 126, $detalhe)); // Data do vencimento do título
                    self::adiciona($retorno[$i], 125, 137, self::remove(127, 139, $detalhe)); // Valor do título
                    self::adiciona($retorno[$i], 138, 140, '077'); // Código do banco na compensação
                    self::adiciona($retorno[$i], 141, 144, '0001'); // Agência da empresa beneficiária no Inter
                    self::adiciona($retorno[$i], 145, 146, '01'); // Espécie do título
                    self::adiciona($retorno[$i], 147, 159, '             '); // Campo em branco
                    self::adiciona($retorno[$i], 160, 172, str_repeat('0', 13)); // Valor pago (mesmo valor do título)
                    self::adiciona($retorno[$i], 173, 178, date('dmy')); // Data do crédito
                    self::adiciona($retorno[$i], 179, 181, str_repeat(' ', 3)); // Campo em branco
                    self::adiciona($retorno[$i], 182, 221, str_pad(self::remove(237, 271, $detalhe), 40, ' ', STR_PAD_RIGHT)); // Nome do pagador
                    self::adiciona($retorno[$i], 222, 226, str_repeat(' ', 5)); // Campo em branco
                    self::adiciona($retorno[$i], 227, 240, str_pad(self::remove(223, 236, $detalhe), 14, '0', STR_PAD_LEFT)); // CPF/CNPJ do pagador
                    self::adiciona($retorno[$i], 241, 380, str_repeat(' ', 140)); // Campo em branco
                    self::adiciona($retorno[$i], 381, 394, str_pad(self::remove(1, 1, $detalhe), 14, ' ', STR_PAD_LEFT)); // Campo em branco
                    self::adiciona($retorno[$i], 395, 400, str_pad(self::remove(395, 400, $detalhe), 5, ' ', STR_PAD_LEFT)); // Campo em branco
                    break;
                default:
                    throw new ValidationException("Banco: $banco, inválido");
            }
        }


        // Calcula totais dos detalhes para o trailer
        $qtdDetalhes = 0;
        $valorTotalInformado = 0;
        $qtdTitulosSimples = 0;
        $valorTotalSimples = 0;
        $qtdTitulosVinculada = 0;
        $valorTotalVinculada = 0;
        $qtdTitulosDireta = 0;
        $valorTotalDireta = 0;

        foreach ($retorno as $idx => $linha) {
            if ($idx == 0) continue; // Pula o header
            if (is_array($linha) && isset($linha[0]) && $linha[0] == '1') { // É um detalhe
                $qtdDetalhes++;
                // Extrai valor do título (posições 153-165) - formato CNAB: 13 dígitos (11 inteiros + 2 decimais)
                $valorTituloStr = '';
                for ($j = 152; $j < 165; $j++) { // Posições 153-165 (índice 0-based: 152-164)
                    $valorTituloStr .= isset($linha[$j]) ? $linha[$j] : '0';
                }
                $valorTituloStr = trim($valorTituloStr);
                $valorTitulo = $valorTituloStr ? (float)($valorTituloStr / 100) : 0;
                $valorTotalInformado += $valorTitulo;

                // Por padrão, considera como cobrança simples
                $qtdTitulosSimples++;
                $valorTotalSimples += $valorTitulo;
            }
        }

        if ($banco === BoletoContract::COD_BANCO_INTER) {
            $i = count($retorno);
            $retorno[$i] = array_fill(0, 400, ' '); // Preenche com espaços para o trailer
            self::adiciona($retorno[$i], 1, 1, '9'); // Identificação do registro
            self::adiciona($retorno[$i], 2, 2, '2'); // Identificação de retorno
            self::adiciona($retorno[$i], 3, 4, '01'); // Identificação tipo de registro
            self::adiciona($retorno[$i], 5, 7, '077'); // Código do banco na compensação
            self::adiciona($retorno[$i], 8, 17, str_repeat(' ', 10)); // Campo em branco
            self::adiciona($retorno[$i], 18, 25, str_pad($i - 1, 8, '0', STR_PAD_LEFT)); // Quantidade de registros Ocorrência 01
            self::adiciona($retorno[$i], 26, 57, str_repeat(' ', 32)); // Campo em branco
            self::adiciona($retorno[$i], 58, 62, str_pad($i - 1, 5, '0', STR_PAD_LEFT)); // Quantidade de registros Ocorrência 02
            self::adiciona($retorno[$i], 63, 74, str_pad(1800000, 12, '0', STR_PAD_LEFT)); // Valor de registros Ocorrência 02
            self::adiciona($retorno[$i], 75, 86, str_repeat(' ', 12)); // Campo em branco
            self::adiciona($retorno[$i], 87, 91, str_pad(0, 5, '0', STR_PAD_LEFT)); // Quantidade de registros Ocorrência 03
            self::adiciona($retorno[$i], 92, 115, str_repeat(' ', 24)); // Campo em branco
            self::adiciona($retorno[$i], 116, 120, str_pad(0, 5, '0', STR_PAD_LEFT)); // Quantidade de registros Ocorrência 04
            self::adiciona($retorno[$i], 121, 132, str_pad(0, 12, '0', STR_PAD_LEFT)); // Valor dos registros Ocorrência 04
            self::adiciona($retorno[$i], 133, 394, str_repeat(' ', 262)); // Campo em branco
            self::adiciona($retorno[$i], 395, 400, sprintf('%06d', count($retorno))); // Número sequencial de registros
        } elseif ($banco === BoletoContract::COD_BANCO_ITAU) {
            // Trailer do retorno CNAB400 do Itaú conforme especificação
            $i = count($retorno);
            $retorno[$i] = array_fill(0, 400, '0');

            // Número sequencial do arquivo retorno (do header, posições 109-113)
            $numeroSequencialArquivo = self::remove(109, 113, $retorno[0]);
            $numeroSequencialArquivo = str_pad($numeroSequencialArquivo ?: '00000', 5, '0', STR_PAD_LEFT);

            // Formata valores (remove vírgula decimal, multiplica por 100)
            $valorTotalSimplesFormatado = str_pad((int)($valorTotalSimples * 100), 14, '0', STR_PAD_LEFT);
            $valorTotalVinculadaFormatado = str_pad((int)($valorTotalVinculada * 100), 14, '0', STR_PAD_LEFT);
            $valorTotalDiretaFormatado = str_pad((int)($valorTotalDireta * 100), 14, '0', STR_PAD_LEFT);
            $valorTotalInformadoFormatado = str_pad((int)($valorTotalInformado * 100), 14, '0', STR_PAD_LEFT);

            self::adiciona($retorno[$i], 1, 1, '9'); // Tipo de Registro
            self::adiciona($retorno[$i], 2, 2, '2'); // Código de Retorno
            self::adiciona($retorno[$i], 3, 4, '01'); // Código de Serviço
            self::adiciona($retorno[$i], 5, 7, $banco); // Código do Banco
            self::adiciona($retorno[$i], 8, 17, str_repeat(' ', 10)); // Brancos
            self::adiciona($retorno[$i], 18, 25, str_pad($qtdTitulosSimples, 8, '0', STR_PAD_LEFT)); // Qtde. de Títulos - Cobrança Simples
            self::adiciona($retorno[$i], 26, 39, $valorTotalSimplesFormatado); // Valor Total - Cobrança Simples
            self::adiciona($retorno[$i], 40, 47, str_repeat(' ', 8)); // Aviso Bancário
            self::adiciona($retorno[$i], 48, 57, str_repeat(' ', 10)); // Brancos
            self::adiciona($retorno[$i], 58, 65, str_pad($qtdTitulosVinculada, 8, '0', STR_PAD_LEFT)); // Qtde. de Títulos - Cobrança/Vinculada
            self::adiciona($retorno[$i], 66, 79, $valorTotalVinculadaFormatado); // Valor Total - Cobrança/Vinculada
            self::adiciona($retorno[$i], 80, 87, str_repeat(' ', 8)); // Aviso Bancário
            self::adiciona($retorno[$i], 88, 177, str_repeat(' ', 90)); // Brancos
            self::adiciona($retorno[$i], 178, 185, str_pad($qtdTitulosDireta, 8, '0', STR_PAD_LEFT)); // Qtde. de Títulos - Cobrança Direta/Escritural
            self::adiciona($retorno[$i], 186, 199, $valorTotalDiretaFormatado); // Valor Total - Cobrança Direta/Escritural
            self::adiciona($retorno[$i], 200, 207, str_repeat(' ', 8)); // Aviso Bancário
            self::adiciona($retorno[$i], 208, 212, $numeroSequencialArquivo); // Controle do Arquivo
            self::adiciona($retorno[$i], 213, 220, str_pad($qtdDetalhes, 8, '0', STR_PAD_LEFT)); // Qtde de Detalhes
            self::adiciona($retorno[$i], 221, 234, $valorTotalInformadoFormatado); // Vlr Total Informado
            self::adiciona($retorno[$i], 235, 394, str_repeat(' ', 160)); // Brancos
            self::adiciona($retorno[$i], 395, 400, sprintf('%06s', count($retorno))); // Número Sequencial
        } else {
            $i = count($retorno);
            $retorno[$i] = array_fill(0, 400, '0');
            self::adiciona($retorno[$i], 1, 1, '9');
            self::adiciona($retorno[$i], 395, 400, sprintf('%06s', count($retorno)));
        }

        $retorno = array_map(function ($a) {
            return implode('', $a);
        }, $retorno);

        return implode("\r\n", $retorno);
    }

    /**
     * Remove trecho do array.
     *
     * @param $i
     * @param $f
     * @param $array
     *
     * @return string
     * @throws ValidationException
     */
    public static function remove($i, $f, &$array)
    {
        if (is_string($array)) {
            $array = preg_split('//u', rtrim($array, chr(10) . chr(13) . "\n" . "\r"), -1, PREG_SPLIT_NO_EMPTY);
        }

        $i--;

        if ($i > 398 || $f > 400) {
            throw new ValidationException('$ini ou $fim ultrapassam o limite máximo de 400');
        }

        if ($f < $i) {
            throw new ValidationException('$ini é maior que o $fim');
        }

        $t = $f - $i;

        $toSplice = $array;

        if ($toSplice != null) {
            return trim(implode('', array_splice($toSplice, $i, $t)));
        } else {
            return null;
        }
    }

    /**
     * Função para add valor a linha nas posições informadas.
     *
     * @param $line
     * @param int $i
     * @param int $f
     * @param $value
     *
     * @return array
     * @throws ValidationException
     */
    public static function adiciona(&$line, $i, $f, $value)
    {
        $iOriginal = $i;
        $i--;

        if (($i > 398 || $f > 400) && ($i != 401 && $f != 444)) {
            throw new ValidationException(sprintf('Posições inválidas: $ini=%d e $fim=%d ultrapassam o limite máximo de 400', $iOriginal, $f));
        }

        if ($f < $i) {
            throw new ValidationException(sprintf('Posições inválidas: $ini=%d é maior que $fim=%d', $iOriginal, $f));
        }

        $t = $f - $i;

        if (mb_strlen($value) > $t) {
            $valuePreview = mb_strlen($value) > 60 ? mb_substr($value, 0, 60) . '...' : $value;
            throw new ValidationException(sprintf(
                'Valor "%s" (%d caracteres) excede o tamanho do campo nas posições %d-%d (cabem %d caracteres). Verifique se o dado de entrada (agência, conta, DV, convênio, nome, etc.) está no formato esperado pelo banco.',
                $valuePreview,
                mb_strlen($value),
                $iOriginal,
                $f,
                $t
            ));
        }

        $value = sprintf("%{$t}s", $value);
        $value = preg_split('//u', $value, -1, PREG_SPLIT_NO_EMPTY) + array_fill(0, $t, '');

        return array_splice($line, $i, $t, $value);
    }

    /**
     * Validação para o tipo de cnab 240
     *
     * @param  $content
     * @return bool
     */
    public static function isCnab240($content)
    {
        $content = is_array($content) ? $content[0] : $content;

        return mb_strlen(rtrim($content, "\r\n")) == 240;
    }

    /**
     * Validação para o tipo de cnab 400
     *
     * @param  $content
     * @return bool
     */
    public static function isCnab400($content)
    {
        $content = is_array($content) ? $content[0] : $content;

        return mb_strlen(rtrim($content, "\r\n")) == 400;
    }

    /**
     * @param $file
     *
     * @return array|bool
     */
    public static function file2array($file)
    {
        $aFile = [];
        if ($file instanceof UploadedFile) {
            $aFile = file($file->getRealPath());
        } elseif (is_array($file) && isset($file[0]) && is_string($file[0])) {
            $aFile = $file;
        } elseif (is_string($file) && is_file($file) && file_exists($file)) {
            $aFile = file($file);
        } elseif (is_string($file) && strstr($file, PHP_EOL) !== false) {
            $file_content = explode(PHP_EOL, $file);
            if (empty(end($file_content))) {
                array_pop($file_content);
            }
            reset($file_content);
            $aFile = $file_content;
        }

        return array_map('\ForceUTF8\Encoding::toUTF8', $aFile);
    }

    /**
     * Valida se o header é de um arquivo retorno valido, 240 ou 400 posicoes
     *
     * @param $header
     *
     * @return bool
     */
    public static function isHeaderRetorno($header)
    {
        if (! self::isCnab240($header) && ! self::isCnab400($header)) {
            return false;
        }
        if (self::isCnab400($header) && mb_substr($header, 0, 9) != '02RETORNO') {
            return false;
        }
        if (self::isCnab240($header) && mb_substr($header, 142, 1) != '2') {
            return false;
        }

        return true;
    }

    /**
     * @param object $obj
     * @param array $params
     */
    public static function fillClass(&$obj, array $params)
    {
        foreach ($params as $param => $value) {
            $param = Str::camel($param);
            if (method_exists($obj, 'getProtectedFields') && in_array(lcfirst($param), $obj->getProtectedFields())) {
                continue;
            }
            if (method_exists($obj, 'set' . Str::camel($param))) {
                $obj->{'set' . Str::camel($param)}($value);
            }
        }
    }

    /**
     * @param $ipte
     *
     * @return string
     */
    public static function IPTE2CodigoBarras($ipte)
    {
        $ipte = self::onlyNumbers($ipte);

        $barras = substr($ipte, 0, 4);
        $barras .= substr($ipte, 32, 1);
        $barras .= substr($ipte, 33, 14);
        $barras .= substr($ipte, 4, 1);
        $barras .= substr($ipte, 5, 4);
        $barras .= substr($ipte, 10, 10);
        $barras .= substr($ipte, 21, 8);
        $barras .= substr($ipte, 29, 2);

        return $barras;
    }

    /**
     * @param $ipte
     *
     * @return array
     * @throws ValidationException
     */
    public static function IPTE2Variveis($ipte)
    {
        $barras = self::IPTE2CodigoBarras($ipte);

        $variaveis = [
            'barras'           => $barras,
            'banco'            => substr($barras, 0, 3),
            'moeda'            => substr($barras, 3, 1),
            'dv'               => substr($barras, 4, 1),
            'fator_vencimento' => substr($barras, 5, 4),
            'vencimento'       => self::fatorVencimentoBack(substr($barras, 5, 4), false),
            'valor'            => ((float) substr($barras, 9, 10)) / 100,
            'campo_livre'      => substr($barras, -25),
        ];

        $class = __NAMESPACE__ . '\\Boleto\\' . self::getBancoClass($variaveis['banco']);

        if (method_exists($class, 'parseCampoLivre')) {
            $variaveis['campo_livre_parsed'] = $class::parseCampoLivre($variaveis['campo_livre']);
        } else {
            $variaveis['campo_livre_parsed'] = false;
        }

        return $variaveis;
    }

    /**
     * @param $codigo
     * @return string
     */
    public static function codigoBarras2LinhaDigitavel($codigo)
    {
        $parte1 = substr($codigo, 0, 4) . substr($codigo, 19, 5);
        $parte1 .= Util::modulo10($parte1);

        $parte2 = substr($codigo, 24, 10);
        $parte2 .= Util::modulo10($parte2);

        $parte3 = substr($codigo, 34, 10);
        $parte3 .= Util::modulo10($parte3);

        $parte4 = substr($codigo, 4, 1);

        $parte5 = substr($codigo, 5, 14);

        return $parte1 . $parte2 . $parte3 . $parte4 . $parte5;
    }

    /**
     * @param $linhaDigitavel
     * @return string
     * @throws ValidationException
     */
    public static function formatLinhaDigitavel($linhaDigitavel)
    {
        // Remover espaços em branco
        $linhaDigitavel = Util::onlyNumbers($linhaDigitavel);

        // Verificar se a linha digitável possui 47 caracteres
        if (strlen($linhaDigitavel) != 47) {
            throw new ValidationException('A linha digitável deve ter 47 caracteres.');
        }

        return self::maskString($linhaDigitavel, '#####.##### #####.###### #####.###### # ##############');
    }

    /**
     * @param $banco
     *
     * @return string
     * @throws ValidationException
     */
    public static function getBancoClass($banco)
    {
        $aBancos = [

            BoletoContract::COD_BANCO_BB        => 'Banco\\Bb',
            BoletoContract::COD_BANCO_BNB       => 'Banco\\Bnb',
            BoletoContract::COD_BANCO_SANTANDER => 'Banco\\Santander',
            BoletoContract::COD_BANCO_BANRISUL  => 'Banco\\Banrisul',
            BoletoContract::COD_BANCO_INTER     => 'Banco\\Inter',
            BoletoContract::COD_BANCO_CEF       => 'Banco\\Caixa',
            BoletoContract::COD_BANCO_BTG       => 'Banco\\Btg',
            BoletoContract::COD_BANCO_UNICRED   => 'Banco\\Unicred',
            BoletoContract::COD_BANCO_BRADESCO  => 'Banco\\Bradesco',
            BoletoContract::COD_BANCO_FIBRA     => 'Banco\\Fibra',
            BoletoContract::COD_BANCO_ITAU      => 'Banco\\Itau',
            BoletoContract::COD_BANCO_HSBC      => 'Banco\\Hsbc',
            BoletoContract::COD_BANCO_DELCRED   => 'Banco\\Delbank',
            BoletoContract::COD_BANCO_PINE      => 'Banco\\Pine',
            BoletoContract::COD_BANCO_OURINVEST => 'Banco\\Ourinvest',
            BoletoContract::COD_BANCO_SICREDI   => 'Banco\\Sicredi',
            BoletoContract::COD_BANCO_BANCOOB   => 'Banco\\Bancoob',
            BoletoContract::COD_BANCO_CRESOL    => 'Banco\\Cresol',
            BoletoContract::COD_BANCO_AILOS     => 'Banco\\Ailos',
        ];

        if (array_key_exists($banco, $aBancos)) {
            return $aBancos[$banco];
        }

        throw new ValidationException("Banco: $banco, inválido");
    }

    /**
     * @param $property
     * @param $obj
     *
     * @return Pessoa
     * @throws ValidationException
     */
    public static function addPessoa(&$property, $obj)
    {
        if (is_subclass_of($obj, 'Eduardokum\\LaravelBoleto\\Contracts\\Pessoa')) {
            $property = $obj;

            return $obj;
        } elseif (is_array($obj)) {
            $obj = new Pessoa($obj);
            $property = $obj;

            return $obj;
        }
        throw new ValidationException('Objeto inválido, somente Pessoa e Array');
    }

    /**
     * @return string
     */
    public static function appendStrings()
    {
        $strings = func_get_args();
        $appended = null;
        foreach ($strings as $string) {
            $appended .= " $string";
        }

        return trim($appended);
    }

    /**
     * Return value or null.
     *
     * @param       $a
     * @param array $parms
     *
     * @return int
     * @internal param $key
     * @internal param int $default
     */
    public static function whichOne($a, ...$parms)
    {
        if (is_array($parms[0])) {
            $parms = $parms[0];
        }
        foreach ($parms as $parm) {
            if (is_object($a)) {
                if (isset($a->{$parm})) {
                    return $a->{$parm};
                }
            } else {
                if (array_key_exists($parm, $a)) {
                    return $a[$parm];
                }
            }
        }

        return null;
    }

    /**
     * @param $cpf
     * @return bool
     */
    public static function validarCpf($cpf)
    {
        $c = self::onlyNumbers($cpf);
        if (mb_strlen($c) != 11 || preg_match("/^{$c[0]}{11}$/", $c)) {
            return false;
        }
        for ($s = 10, $n = 0, $i = 0; $s >= 2; $n += $c[$i++] * $s--);
        if ($c[9] != ((($n %= 11) < 2) ? 0 : 11 - $n)) {

            return false;
        }
        for ($s = 11, $n = 0, $i = 0; $s >= 2; $n += $c[$i++] * $s--);
        if ($c[10] != ((($n %= 11) < 2) ? 0 : 11 - $n)) {

            return false;
        }

        return true;
    }

    /**
     * @param $cnpj
     * @return bool
     */
    public static function validarCnpj($cnpj)
    {
        $c = self::onlyNumbers($cnpj);
        $b = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        if (mb_strlen($c) != 14 || preg_match("/^{$c[0]}{14}$/", $c)) {
            return false;
        }
        for ($i = 0, $n = 0; $i < 12; $n += $c[$i] * $b[++$i]);
        if ($c[12] != ((($n %= 11) < 2) ? 0 : 11 - $n)) {

            return false;
        }
        for ($i = 0, $n = 0; $i <= 12; $n += $c[$i] * $b[$i++]);
        if ($c[13] != ((($n %= 11) < 2) ? 0 : 11 - $n)) {

            return false;
        }

        return true;
    }

    /**
     * @param $documento
     * @return bool
     */
    public static function validarCnpjCpf($documento)
    {
        $documento = Util::onlyNumbers($documento);
        if (strlen($documento) == 11) {
            return self::validarCpf($documento);
        } elseif (strlen($documento) == 14) {
            return self::validarCnpj($documento);
        }

        return false;
    }

    /**
     * @param $uuid
     * @return string
     */
    public static function formatarUUID($uuid)
    {
        $uuidNew = self::onlyNumbers($uuid);
        if (preg_match('/[a-zA-Z0-9]{32}/', $uuidNew)) {
            return Util::maskString($uuidNew, '########-####-####-####-############');
        }

        return $uuid;
    }

    /**
     * @param $pix
     * @param $valor
     * @param $id
     * @param Pessoa $beneficiario
     * @return string
     * @throws ValidationException
     */
    public static function gerarPixCopiaECola($pix, $valor, $id, Pessoa $beneficiario)
    {
        if ($id != Util::normalizeChars($id)) {
            throw new ValidationException('ID inválido, não pode possuir caracteres especiais');
        }

        $dinamico = false;
        if (filter_var($pix, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED) || filter_var('https://' . $pix, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED)) {
            $dinamico = true;
        }

        $crc16 = function ($payload) {
            $payload .= '6304';

            $polinomio = 0x1021;
            $resultado = 0xFFFF;
            if (($length = strlen($payload)) > 0) {
                for ($offset = 0; $offset < $length; $offset++) {
                    $resultado ^= (ord($payload[$offset]) << 8);
                    for ($bitwise = 0; $bitwise < 8; $bitwise++) {
                        if (($resultado <<= 1) & 0x10000) {
                            $resultado ^= $polinomio;
                        }
                        $resultado &= 0xFFFF;
                    }
                }
            }

            return '6304' . strtoupper(dechex($resultado));
        };

        $line = function ($id, $value) {
            $size = str_pad(mb_strlen($value), 2, '0', STR_PAD_LEFT);

            return $id . $size . $value;
        };

        $gui = $line('00', 'br.gov.bcb.pix');
        if ($dinamico) {
            $key = $line('25', preg_replace('/^https?:\/\//', '', $pix));
            $txId = $line('05', '***');
        } else {
            $key = $line('01', $pix);
            $txId = $line('05', $id);
        }
        $payload = $line('00', '01');
        if (! $dinamico) {
            $payload .= $line('01', '12');
        }
        $payload .= $line('26', $gui . $key);
        $payload .= $line('52', '0000');
        $payload .= $line('53', '986');
        if (! $dinamico) {
            $payload .= $line('54', $valor);
        }
        $payload .= $line('58', 'BR');
        $payload .= $line('59', Util::normalizeChars($beneficiario->getNome()));
        $payload .= $line('60', Util::normalizeChars($beneficiario->getCidade()));
        $payload .= $line('62', $txId);

        return $payload . $crc16($payload);
    }

    /**
     * @param $location
     * @return array
     */
    public static function fetchPixLocation($location)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_URL, $location);
        $data = curl_exec($curl);
        curl_close($curl);
        $datas = explode('.', $data);
        if (count($datas) !== 3) {
            return [];
        }

        return [
            'fetch'     => $data,
            'header'    => json_decode(base64_decode($datas[0]), true),
            'payload'   => json_decode(base64_decode($datas[1]), true),
            'signature' => $datas[2],
        ];
    }

    /**
     * @param $pixCopiaECola
     * @param null $parent
     * @return array|null
     */
    public static function decodePixCopiaECola($pixCopiaECola, $parent = null)
    {
        $structures = [
            '00' => [
                'type' => 'single',
                'name' => 'Payload Format Indicator',
            ],
            '01' => [
                'type' => 'single',
                'name' => 'Point of Initiation Method',
            ],
            '04' => [
                'type' => 'single',
                'name' => 'Merchant Account Information – Cartões',
            ],
            '26' => [
                'type'      => 'multiple',
                'name'      => 'Merchant Account Information',
                'multiples' => [
                    '00' => [
                        'type' => 'single',
                        'name' => 'Globally Unique Identifier',
                    ],
                    '01' => [
                        'type' => 'single',
                        'name' => 'Pix Key',
                    ],
                    '02' => [
                        'type' => 'single',
                        'name' => 'Payment Description',
                    ],
                    '25' => [
                        'type' => 'single',
                        'name' => 'Payment URL',
                    ],
                ],
            ],
            '52' => [
                'type' => 'single',
                'name' => 'Merchant Category Code',
            ],
            '53' => [
                'type' => 'single',
                'name' => 'Transaction Currency',
            ],
            '54' => [
                'type' => 'single',
                'name' => 'Transaction Amount',
            ],
            '58' => [
                'type' => 'single',
                'name' => 'Country Code',
            ],
            '59' => [
                'type' => 'single',
                'name' => 'Merchant Name',
            ],
            '60' => [
                'type' => 'single',
                'name' => 'Merchant City',
            ],
            '61' => [
                'type' => 'single',
                'name' => 'Postal Code',
            ],
            '62' => [
                'type'      => 'multiple',
                'name'      => 'Additional Data Field Template',
                'multiples' => [
                    '05' => [
                        'type' => 'single',
                        'name' => 'Reference Label',
                    ],
                ],
            ],
            '80' => [
                'type'      => 'multiple',
                'name'      => 'Unreserved Templates',
                'multiples' => [
                    '00' => [
                        'type' => 'single',
                        'name' => 'Globally Unique Identifier',
                    ],
                    '01' => [
                        'type' => 'single',
                        'name' => 'informação arbitrária do arranjo',
                    ],
                ],
            ],
            '63' => [
                'type' => 'single',
                'name' => 'CRC',
            ],
        ];

        if ($parent && ! ($structures = Arr::get($structures, "$parent.multiples"))) {
            return null;
        }

        $aPix = [];
        $i = 0;
        while ($i < strlen($pixCopiaECola)) {
            $code = $codeSearch = substr($pixCopiaECola, $i, 2);
            if ($code >= 26 && $code <= 51) {
                $codeSearch = 26;
            }
            if ($code >= 80 && $code <= 99) {
                $codeSearch = 80;
            }
            $i += 2;
            $size = intval(substr($pixCopiaECola, $i, 2));
            $i += 2;
            if ($structure = Arr::get($structures, $codeSearch)) {
                if ($structure['type'] == 'multiple') {
                    $aPix["$code"] = self::decodePixCopiaECola(substr($pixCopiaECola, $i, $size), $codeSearch);
                } else {
                    $aPix["$code"] = substr($pixCopiaECola, $i, $size);
                }
            }
            $i += $size;
        }

        return $aPix;
    }

    /**
     * @param $chave
     * @return string|null
     */
    public static function tipoChavePix($chave)
    {
        if (is_null($chave)) {
            return null;
        }

        $parametro = trim($chave);
        if (filter_var($parametro, FILTER_VALIDATE_EMAIL)) {

            return AbstractBoleto::TIPO_CHAVEPIX_EMAIL;
        }

        if (Util::validarCnpj($parametro)) {

            return AbstractBoleto::TIPO_CHAVEPIX_CNPJ;
        }

        if (Util::validarCpf($parametro)) {

            return AbstractBoleto::TIPO_CHAVEPIX_CPF;
        }

        // Verificar se é um telefone
        if (preg_match('/^(\+\d{2}\s?)?[-.\s]?\(?\d{2}\)?[-.\s]?(\d\s?)?\d{4}[-.\s]?\d{4}$/', $parametro)) {

            return AbstractBoleto::TIPO_CHAVEPIX_CELULAR;
        }

        $parametro = Util::onlyAlphanumber($parametro);
        // Verificar se é um UUID
        if (preg_match('/^[a-fA-F0-9]{32}$/', $parametro) && (ctype_xdigit($parametro))) {
            return AbstractBoleto::TIPO_CHAVEPIX_ALEATORIA;
        }

        return null;
    }

    /**
     * @param $str
     * @return bool
     */
    public static function isBase64($str)
    {
        try {
            $decoded = base64_decode($str, true);

            if (base64_encode($decoded) === $str) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }
}
