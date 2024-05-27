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
            'Á' => 'A', 'À' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Å' => 'A', 'Ä' => 'A', 'Æ' => 'AE', 'Ç' => 'C',
            'É' => 'E', 'È' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Í' => 'I', 'Ì' => 'I', 'Î' => 'I', 'Ï' => 'I', 'Ð' => 'Eth',
            'Ñ' => 'N', 'Ó' => 'O', 'Ò' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O',
            'Ú' => 'U', 'Ù' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ý' => 'Y', 'Ŕ' => 'R',

            'á' => 'a', 'à' => 'a', 'â' => 'a', 'ã' => 'a', 'å' => 'a', 'ä' => 'a', 'æ' => 'ae', 'ç' => 'c',
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e', 'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i', 'ð' => 'eth',
            'ñ' => 'n', 'ó' => 'o', 'ò' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ø' => 'o',
            'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u', 'ý' => 'y', 'ŕ' => 'r', 'ÿ' => 'y',

            'ß' => 'sz', 'þ' => 'thorn', 'º' => '', 'ª' => '', '°' => '',
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
                self::adiciona($retorno[0], 27, 30, self::remove(27, 30, $remessa[0]));
                self::adiciona($retorno[0], 33, 37, self::remove(33, 37, $remessa[0]));
                self::adiciona($retorno[0], 38, 38, self::remove(38, 38, $remessa[0]));
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
            default:
                throw new ValidationException("Banco: $banco, inválido");
        }
        self::adiciona($retorno[0], 77, 79, $banco);
        self::adiciona($retorno[0], 95, 100, date('dmy'));
        self::adiciona($retorno[0], 395, 400, sprintf('%06s', count($retorno)));

        array_shift($remessa); // removo o header
        array_pop($remessa); // remove o trailer

        foreach ($remessa as $detalhe) {
            if (! in_array(self::remove(1, 1, $detalhe), [0, 1, 9])) {
                continue;
            }
            $i = count($retorno);
            $retorno[$i] = array_fill(0, 400, '0');
            self::adiciona($retorno[$i], 1, 1, '1');
            self::adiciona($retorno[$i], 109, 110, sprintf('%02s', $ocorrencia));
            self::adiciona($retorno[$i], 111, 116, date('dmy'));
            self::adiciona($retorno[$i], 153, 165, self::remove(127, 139, $detalhe));
            self::adiciona($retorno[$i], 254, 266, self::remove(127, 139, $detalhe));
            self::adiciona($retorno[$i], 147, 152, self::remove(121, 126, $detalhe));
            self::adiciona($retorno[$i], 117, 126, self::remove(111, 120, $detalhe));
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
                    self::adiciona($retorno[$i], 86, 94, self::remove(63, 70, $detalhe));
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
                default:
                    throw new ValidationException("Banco: $banco, inválido");
            }
        }

        $i = count($retorno);
        $retorno[$i] = array_fill(0, 400, '0');
        self::adiciona($retorno[$i], 1, 1, '9');
        self::adiciona($retorno[$i], 395, 400, sprintf('%06s', count($retorno)));

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
        $i--;

        if (($i > 398 || $f > 400) && ($i != 401 && $f != 444)) {
            throw new ValidationException('$ini ou $fim ultrapassam o limite máximo de 400');
        }

        if ($f < $i) {
            throw new ValidationException('$ini é maior que o $fim');
        }

        $t = $f - $i;

        if (mb_strlen($value) > $t) {
            throw new ValidationException(sprintf('String $valor maior que o tamanho definido em $ini e $fim: $valor=%s e tamanho é de: %s', mb_strlen($value), $t));
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
