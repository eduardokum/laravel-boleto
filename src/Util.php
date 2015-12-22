<?php
namespace Eduardokum\LaravelBoleto;

use Carbon\Carbon;

final class Util
{

    /**
     * Retorna a String em MAIUSCULO
     *
     * @param String $string
     *
     * @return String
     */
    public static function upper($string)
    {
        return strtr(strtoupper($string), "àáâãäåæçèéêëìíîïðñòóôõö÷øùüúþÿ", "ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖ×ØÙÜÚÞß");
    }

    /**
     * Retorna a String em minusculo
     *
     * @param String $string
     *
     * @return String
     */
    public static function lower($string)
    {
        return strtr(strtolower($string), "ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖ×ØÙÜÚÞß", "àáâãäåæçèéêëìíîïðñòóôõö÷øùüúþÿ");
    }

    /**
     * Retorna a String em minusculo
     *
     * @param String $string
     *
     * @return String
     */
    public static function upFirst($string)
    {
        return ucfirst(self::lower($string));
    }

    /**
     * Retorna somente as letras da string
     *
     * @param String $string
     *
     * @return String
     */
    public static function lettersOnly($string)
    {
        return preg_replace('/[^[:alpha:]]/', '', $string);
    }

    /**
     * Retorna somente as letras da string
     *
     * @param String $string
     *
     * @return String
     */
    public static function onlyLetters($string)
    {
        return self::lettersOnly($string);
    }

    /**
     * Retorna somente as letras da string
     *
     * @param String $string
     *
     * @return String
     */
    public static function lettersNot($string)
    {
        return preg_replace('/[[:alpha:]]/', '', $string);
    }

    /**
     * Retorna somente as letras da string
     *
     * @param String $string
     *
     * @return String
     */
    public static function notLetters($string)
    {
        return self::lettersNot($string);
    }

    /**
     * Retorna somente os digitos da string
     *
     * @param String $string
     *
     * @return String
     */
    public static function numbersOnly($string)
    {
        return preg_replace('/[^[:digit:]]/', '', $string);
    }

    /**
     * Retorna somente os digitos da string
     *
     * @param String $string
     *
     * @return String
     */
    public static function onlyNumbers($string)
    {
        return self::numbersOnly($string);
    }

    /**
     * Retorna somente os digitos da string
     *
     * @param String $string
     *
     * @return String
     */
    public static function numbersNot($string)
    {
        return preg_replace('/[[:digit:]]/', '', $string);
    }

    /**
     * Retorna somente os digitos da string
     *
     * @param String $string
     *
     * @return String
     */
    public static function notNumbers($string)
    {
        return self::numbersNot($string);
    }

    /**
     * Retorna somente alfanumericos
     *
     * @param String $string
     *
     * @return String
     */
    public static function alphanumberOnly($string)
    {
        return preg_replace('/[^[:alnum:]]/', '', $string);
    }

    /**
     * Retorna somente alfanumericos
     *
     * @param String $string
     *
     * @return String
     */
    public static function onlyAlphanumber($string)
    {
        return self::alphanumberOnly($string);
    }

    /**
     * Função para limpar acentos de uma string
     *
     * @param string $string
     * @return string
     */
    public static function normalizeChars($string) {

        $normalizeChars = array(
            'Á'=>'A', 'À'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Å'=>'A', 'Ä'=>'A', 'Æ'=>'AE', 'Ç'=>'C',
            'É'=>'E', 'È'=>'E', 'Ê'=>'E', 'Ë'=>'E', 'Í'=>'I', 'Ì'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ð'=>'Eth',
            'Ñ'=>'N', 'Ó'=>'O', 'Ò'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O',
            'Ú'=>'U', 'Ù'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Ŕ'=>'R',

            'á'=>'a', 'à'=>'a', 'â'=>'a', 'ã'=>'a', 'å'=>'a', 'ä'=>'a', 'æ'=>'ae', 'ç'=>'c',
            'é'=>'e', 'è'=>'e', 'ê'=>'e', 'ë'=>'e', 'í'=>'i', 'ì'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'eth',
            'ñ'=>'n', 'ó'=>'o', 'ò'=>'o', 'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o',
            'ú'=>'u', 'ù'=>'u', 'û'=>'u', 'ü'=>'u', 'ý'=>'y', 'ŕ'=>'r', 'ÿ'=>'y',

            'ß'=>'sz', 'þ'=>'thorn'
        );
        return strtr($string,$normalizeChars);
    }

    /**
     * Mostra o Valor no float Formatado
     * @param float $number
     * @param integer $decimals
     * @param boolean $showThousands
     * @return string
     */
    public static function nFloat($number, $decimals = 2, $showThousands = false)
    {
        if(is_null($number) || !is_numeric($number)) {return '';}
        $pontuacao = preg_replace('/[0-9]/', '', $number);
        $locale = (substr($pontuacao, -1, 1) == ',')?"pt-BR":"en-US";
        $formater = new \NumberFormatter($locale, \NumberFormatter::DECIMAL);

        if( $decimals === false )
        {
            $decimals = 2;
            preg_match_all('/[0-9][^0-9]([0-9]+)/', $number, $matches);
            if( !empty($matches[1]) )
            {
                $decimals = strlen(rtrim($matches[1][0], 0));
            }
        }

        return number_format($formater->parse( $number, \NumberFormatter::TYPE_DOUBLE), $decimals, '.', ($showThousands)?',':'');
    }

    /**
     * Mostra o Valor no real Formatado
     * @param float $number
     * @param boolean $fixed
     * @param boolean $symbol
     * @param integer $decimals
     * @return string
     */
    public static function nReal($number, $decimals = 2, $symbol = true, $fixed = true)
    {
        if(is_null($number) || !is_numeric($number)) {return '';}
        $formater = new \NumberFormatter("pt-BR", \NumberFormatter::CURRENCY);
        $formater->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, ($fixed?$decimals:1));
        if( $decimals === false )
        {
            $decimals = 2;
            preg_match_all('/[0-9][^0-9]([0-9]+)/', $number, $matches);
            if( !empty($matches[1]) )
            {
                $decimals = strlen(rtrim($matches[1][0], 0));
            }
        }
        $formater->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, $decimals);
        if(!$symbol) {
            $pattern = preg_replace("/[¤]/", '', $formater->getPattern());
            $formater->setPattern($pattern);
        } else {
            // ESPAÇO DEPOIS DO SIMBOLO
            $pattern = str_replace("¤", "¤ ", $formater->getPattern());
            $formater->setPattern($pattern);
        }
        return $formater->formatCurrency($number, $formater->getTextAttribute(\NumberFormatter::CURRENCY_CODE));
    }

    /**
     * Mostra um numero por extenso.
     *
     * @param $value
     *
     * @param $uppercase 1 - UPPER; 2 - Upper; false - tudo minusculo;
     * @return string
     */
    public static function nRealExtenso($value, $uppercase)
    {
        $value = self::nFloat($value, 2);

        $singular = ["centavo", "real", "mil", "milhão", "bilhão", "trilhão", "quatrilhão"];
        $plural = ["centavos", "reais", "mil", "milhões", "bilhões", "trilhões", "quatrilhões"];

        $c = ["", "cem", "duzentos", "trezentos", "quatrocentos", "quinhentos", "seiscentos", "setecentos", "oitocentos", "novecentos"];
        $d = ["", "dez", "vinte", "trinta", "quarenta", "cinquenta", "sessenta", "setenta", "oitenta", "noventa"];
        $d10 = ["dez", "onze", "doze", "treze", "quatorze", "quinze", "dezesseis", "dezesete", "dezoito", "dezenove"];
        $u = ["", "um", "dois", "três", "quatro", "cinco", "seis", "sete", "oito", "nove"];

        $z = 0;

        $value = number_format($value, 2, ".", ".");
        $integer = explode(".", $value);
        $cont = count($integer);
        for ($i = 0; $i < $cont; $i++)
            for ($ii = strlen($integer[$i]); $ii < 3; $ii++)
                $integer[$i] = "0" . $integer[$i];

        $fim = $cont - ($integer[$cont - 1] > 0 ? 1 : 2);
        $rt = '';
        for ($i = 0; $i < $cont; $i++) {
            $value = $integer[$i];
            $rc = (($value > 100) && ($value < 200)) ? "cento" : $c[$value[0]];
            $rd = ($value[1] < 2) ? "" : $d[$value[1]];
            $ru = ($value > 0) ? (($value[1] == 1) ? $d10[$value[2]] : $u[$value[2]]) : "";

            $r = $rc . (($rc && ($rd || $ru)) ? " e " : "") . $rd . (($rd &&
                    $ru) ? " e " : "") . $ru;
            $t = $cont - 1 - $i;
            $r .= $r ? " " . ($value > 1 ? $plural[$t] : $singular[$t]) : "";
            if ($value == "000"
            )
                $z++;
            elseif ($z > 0)
                $z--;
            if (($t == 1) && ($z > 0) && ($integer[0] > 0))
                $r .= ( ($z > 1) ? " de " : "") . $plural[$t];
            if ($r)
                $rt = $rt . ((($i > 0) && ($i <= $fim) &&
                        ($integer[0] > 0) && ($z < 1)) ? ( ($i < $fim) ? ", " : " e ") : " ") . $r;
        }

        if (!$uppercase) {
            return trim($rt ? $rt : "zero");
        } elseif ($uppercase == "2") {
            return trim(strtoupper($rt) ? strtoupper(strtoupper($rt)) : "Zero");
        } else {
            return trim(ucwords($rt) ? ucwords($rt) : "Zero");
        }
    }

    /**
     * Return percent x of y;
     *
     * @param     $big
     * @param     $small
     * @param int $defaultOnZero
     *
     * @return string
     */
    public static function percentOf($big, $small, $defaultOnZero = 0)
    {
        $result = $big > 0.01 ? (($small*100)/$big) : $defaultOnZero;
        return self::nFloat($result);
    }

    /**
     * Return percentage of value;
     *
     * @param     $big
     * @param     $percent
     *
     * @return string
     */
    public static function percent($big, $percent)
    {
        if( $percent < 0.01 )
        {
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
        if(empty($val))
        {
            return $val;
        }
        $maskared = '';
        $k = 0;
        if (is_numeric($val))
        {
            $val = sprintf('%0' . strlen(preg_replace('/[^#]/', '', $mask)) . 's', $val);
        }
        for ($i = 0; $i <= strlen($mask) - 1; $i ++)
        {
            if ($mask[$i] == '#')
            {
                if (isset($val[$k]))
                {
                    $maskared .= $val[$k ++];
                }
            } else
            {
                if (isset($mask[$i]))
                {
                    $maskared .= $mask[$i];
                }
            }
        }

        return $maskared;
    }

    /**
     *
     * @param string $date
     *
     * @return string
     */
    public static function dateBrToUs($date)
    {
        if (empty($date))
        {
            return false;
        }
        $numbersOnly = self::numbersOnly($date);
        if (self::maskString($numbersOnly, '##/##/####') == $date)
        {
            $date_time = \DateTime::createFromFormat('d/m/Y', $date);
        } elseif (self::maskString($numbersOnly, '##/##/##') == $date)
        {
            $date_time = \DateTime::createFromFormat('d/m/y', $date);
        } else
        {
            return false;
        }

        return $date_time->format('Y-m-d');
    }

    /**
     * @param $n
     * @param $loop
     * @param $insert
     *
     * @return string
     */
    public static function numberFormatGeral($n, $loop, $insert) {
        return str_pad(self::onlyNumbers($n), $loop, $insert, STR_PAD_LEFT);
    }

    /**
     * @param $n
     * @param $loop
     * @param $insert
     *
     * @return string
     */
    public static function numberFormatValue($n, $loop, $insert) {
        return str_pad(self::onlyNumbers(number_format((float)$n, '2', ',', '.')), $loop, $insert, STR_PAD_LEFT);
    }

    /**
     * @param $n
     * @param $loop
     * @param $insert
     *
     * @return string
     */
    public static function numberFormatConvenio($n, $loop, $insert) {
        return str_pad(self::onlyNumbers($n), $loop, $insert, STR_PAD_RIGHT);
    }

    /**
     * @param        $tipo
     * @param        $valor
     * @param        $tamanho
     * @param int    $dec
     * @param string $sFill
     *
     * @return string
     * @throws \Exception
     */
    public static function formatCnab($tipo, $valor, $tamanho, $dec = 0, $sFill = '')
    {
        $string = $valor;
        if (in_array(strtoupper($tipo), array('9', 9, 'N', '9L', 'NL'))) {
            if (strtoupper($tipo) == '9L' || strtoupper($tipo) == 'NL') {
                $string = self::onlyNumbers($string);
            }
            $left = '';
            $sFill = 0;
            $type = 's';
            $string = ($dec > 0) ? sprintf("%.{$dec}f", $string) : $string;
            $string = str_replace(array(',', '.'), '', $string);
        } else if (in_array(strtoupper($tipo), array('A', 'X'))) {
            $left = '-';
            $type = 's';
            $string = strtoupper(self::normalizeChars($string));
        } else if (in_array(strtoupper($tipo), array('AM', 'XM'))) {
            $left = '-';
            $type = 's';
            $string = (self::normalizeChars($string));
        } else if (strtoupper($tipo) == 'L') {
            $left = '-';
            $type = 's';
            $string = self::normalizeChars($string);
        } else if (strtoupper($tipo) == 'D') {
            $tamanho = 6;
            $left = '-';
            $type = 's';
            $string = $string->format('dmy');
        } else {
            throw new \Exception('Tipo inválido');
        }
        $string = substr($string, 0, $tamanho);
        return sprintf("%{$left}{$sFill}{$tamanho}{$type}", $string);
    }

    /**
     * @param        $date
     * @param string $format
     *
     * @return float
     */
    public static function fatorVencimento($date, $format = 'Y-m-d') {
        $date = ($date instanceof Carbon) ? $date :  Carbon::createFromFormat($format, $date)->setTime(0,0,0);
        return round(($date->timestamp-mktime(0,0,0,10,07,1997))/86400);
    }

    /**
     * @param        $factor
     * @param string $format
     *
     * @return bool|string
     */
    public static function fatorVencimentoBack($factor, $format = 'Y-m-d') {
        return Carbon::create(1997, 10, 7, 0, 0, 0)->addDay($factor)->format($format);
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
        $dateDiff = Carbon::create(null, 12, 31)->subYear(1)->diffInDays($date);
        return $dateDiff . substr($date->year, -1);
    }


    /**
     * @param     $n
     * @param int $factor
     * @param int $base
     * @param int $rest
     * @param int $whenTen
     *
     * @return int
     */
    public static function modulo11($n, $factor=2, $base=9, $rest=0, $whenTen=0) {
        $sum = 0;
        for ($i = strlen($n); $i > 0; $i--) {
            $ns[$i] = substr($n, $i - 1, 1);
            $partial[$i] = $ns[$i] * $factor;
            $sum += $partial[$i];
            if ($factor == $base) {
                $factor = 1;
            }
            $factor++;
        }

        if ($rest == 0) {
            $sum *= 10;
            $digito = $sum % 11;
            if ($digito == 10) {
                $digito = $whenTen;
            }
            return $digito;
        }
        return $sum % 11;
    }

    /**
     * @param     $n
     * @param int $earlyFactor
     * @param int $lastFactor
     *
     * @return int
     */
    public static function modulo11Reverso($n, $earlyFactor = 2, $lastFactor = 9) {
        $factor = $lastFactor;
        $sum = 0;
        for ($i = strlen($n); $i > 0; $i--) {
            $sum += substr($n, $i - 1, 1) * $factor;
            if (--$factor < $earlyFactor)
                $factor = $lastFactor;
        }

        $module = $sum % 11;
        if ($module > 9)
        {
            return 0;
        }

        return $module;
    }

    /**
     * @param $n
     *
     * @return int
     */
    public static function modulo10($n) {
        $chars = array_reverse(str_split($n, 1));
        $odd = array_intersect_key($chars, array_fill_keys(range(1, count($chars), 2), null));
        $even = array_intersect_key($chars, array_fill_keys(range(0, count($chars), 2), null));
        $even = array_map(function($n) { return ($n >= 5)?2 * $n - 9:2 * $n; }, $even);
        $total = array_sum($odd) + array_sum($even);
        return ((floor($total / 10) + 1) * 10 - $total) % 10;
    }

    /**
     * @param array $a
     *
     * @return string
     * @throws \Exception
     */
    public static function array2Controle(array $a)
    {
        if(preg_match('/[0-9]/', array_keys($a)))
        {
            throw new \Exception('Somente chave alfanumérica no array, para separar o controle pela chave');
        }

        $controle = '';
        foreach($a as $key => $value)
        {
            $controle .= sprintf('%s%s', $key, $value);
        }

        if(strlen($controle) > 25)
        {
            throw new \Exception('Controle muito grande, máximo permitido de 25 caracteres');
        }

        return $controle;
    }

    /**
     * @param $controle
     *
     * @return null|string
     */
    public static function controle2array($controle)
    {
        $matches = '';
        $matches_founded = '';
        preg_match_all('/(([A-Za-zÀ-Úà-ú]{1,1})([0-9]*))/', $controle, $matches, PREG_SET_ORDER);
        if ($matches) {
            foreach ($matches as $match) {
                $matches_founded[$match[2]] = $match[3];
            }
            return $matches_founded;
        }
        return [$controle];
    }

}