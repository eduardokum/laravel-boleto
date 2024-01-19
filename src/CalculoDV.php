<?php

namespace Eduardokum\LaravelBoleto;

class CalculoDV
{
    /*
    |--------------------------------------------------------------------------
    | 001 - Banco do Brasil
    |--------------------------------------------------------------------------
    */
    public static function bbAgencia($agencia)
    {
        return Util::modulo11($agencia, 2, 9, 0, 'X');
    }

    public static function bbContaCorrente($conta)
    {
        return Util::modulo11($conta, 2, 9, 0, 'X');
    }

    public static function bbNossoNumero($nossoNumero)
    {
        return strlen($nossoNumero) >= 17 ? null : Util::modulo11($nossoNumero);
    }

    /*
    |--------------------------------------------------------------------------
    | 004 - Banco do Nordeste
    |--------------------------------------------------------------------------
    */
    public static function bnbAgencia($agencia)
    {
        $dv = Util::modulo11($agencia, 2, 9, 0);

        return $dv == 1 ? 'X' : $dv;
    }

    public static function bnbContaCorrente($agencia, $conta)
    {
        $conta = sprintf('%03s%09s', self::bnbAgenciaReal($agencia), $conta);
        $dv = Util::modulo11($conta, 2, 9, 1);
        if ($dv > 1) {
            return 11 - $dv;
        }

        return 0;
    }

    public static function bnbNossoNumero($nossoNumero)
    {
        return Util::modulo11(Util::numberFormatGeral($nossoNumero, 7));
    }

    private static function bnbAgenciaReal($agencia)
    {
        $agenciaAntiga = [
            '1'   => '99', '2' => '44', '3' => '74', '4' => '73', '5' => '81', '6' => '1',
            '7'   => '2', '8' => '53', '9' => '46', '10' => '20', '11' => '82', '12' => '47',
            '13'  => '9', '14' => '10', '15' => '54', '16' => '0', '17' => '55', '18' => '83',
            '19'  => '11', '20' => '48', '21' => '25', '22' => '12', '23' => '49', '24' => '26',
            '25'  => '40', '26' => '75', '27' => '16', '28' => '50', '29' => '27', '30' => '29',
            '31'  => '3', '32' => '4', '33' => '76', '34' => '41', '35' => '77', '36' => '30',
            '37'  => '67', '38' => '68', '39' => '78', '40' => '58', '41' => '59', '42' => '42',
            '43'  => '31', '44' => '60', '45' => '62', '46' => '17', '47' => '79', '48' => '32',
            '49'  => '70', '50' => '63', '51' => '86', '52' => '33', '53' => '52', '54' => '64',
            '55'  => '34', '56' => '71', '57' => '72', '58' => '14', '59' => '38', '60' => '43',
            '61'  => '80', '62' => '21', '63' => '57', '64' => '91', '66' => '6', '67' => '51',
            '68'  => '66', '69' => '85', '70' => '39', '71' => '92', '72' => '28', '73' => '19',
            '74'  => '87', '75' => '18', '76' => '61', '77' => '88', '78' => '89', '80' => '5',
            '81'  => '90', '82' => '15', '83' => '7', '84' => '13', '85' => '93', '86' => '69',
            '87'  => '94', '88' => '101', '89' => '107', '90' => '95', '91' => '45', '92' => '8',
            '93'  => '35', '95' => '106', '96' => '103', '97' => '117', '98' => '118', '99' => '104',
            '100' => '108', '101' => '102', '102' => '112', '103' => '113', '104' => '115', '105' => '105',
            '106' => '96', '107' => '97', '108' => '24', '109' => '111', '110' => '119', '111' => '84',
            '112' => '36', '113' => '37', '114' => '114', '115' => '100', '116' => '116', '117' => '56',
            '118' => '65', '119' => '109',
        ];

        return array_key_exists($agencia, $agenciaAntiga) ? $agenciaAntiga[$agencia] : $agencia;
    }

    /*
    |--------------------------------------------------------------------------
    | 033 - Santander
    |--------------------------------------------------------------------------
    */
    public static function santanderContaCorrente($agencia, $conta)
    {
        $n = Util::numberFormatGeral($agencia, 4)
            . '00'
            . Util::numberFormatGeral($conta, 8);
        $chars = array_reverse(str_split($n, 1));
        $sums = array_reverse(str_split('97310097131973', 1));
        $sum = 0;
        foreach ($chars as $i => $char) {
            $sum += (int) substr($char * $sums[$i], -1);
        }
        $unidade = substr($sum, -1);

        return $unidade == 0 ? $unidade : 10 - $unidade;
    }

    public static function santanderNossoNumero($nossoNumero)
    {
        return Util::modulo11($nossoNumero);
    }

    /*
    |--------------------------------------------------------------------------
    | 041 - Banrisul
    |--------------------------------------------------------------------------
    */
    public static function banrisulAgencia($agencia)
    {
        $newDv1 = $dv1 = Util::modulo10($agencia);
        $dv2 = Util::modulo11($agencia . $dv1, 2, 7);

        if ($dv2 == 1 && $dv1 != 9) {
            $newDv1 = 1;
        }
        if ($dv2 == 1 && $dv1 == 9) {
            $newDv1 = 0;
        }

        if ($dv1 != $newDv1) {
            $dv1 = $newDv1;
            $dv2 = Util::modulo11($agencia . $dv1, 2, 7);
        }

        return $dv1 . $dv2;
    }

    public static function banrisulContaCorrente($conta)
    {
        $chars = array_reverse(str_split($conta, 1));
        $sums = str_split('234567423', 1);

        $sum = 0;
        foreach ($chars as $i => $char) {
            $sum += $char * $sums[$i];
        }

        $resto = $sum % 11;

        if ($resto == 0) {
            return $resto;
        }

        if ($resto == 1) {
            return 6;
        }

        return 11 - $resto;
    }

    public static function banrisulNossoNumero($nossoNumero)
    {
        return self::banrisulDuploDigito($nossoNumero);
    }

    public static function banrisulDuploDigito($campo)
    {
        $dv1 = Util::modulo10($campo);
        $dv2 = Util::modulo11($campo . $dv1, 2, 7, 1, 10);

        if ($dv2 == 1) {
            if ($dv1 == 9) {
                $dv1 = 0;
            } else {
                $dv1++;
            }

            $dv2 = Util::modulo11($campo . $dv1, 2, 7, 0, 10);
        } elseif ($dv2 != 0) {
            $dv2 = (11 - $dv2);
        }

        return $dv1 . $dv2;
    }

    /*
    |--------------------------------------------------------------------------
    | 104 - Caixa Econômica Federal
    |--------------------------------------------------------------------------
    */
    public static function cefAgencia($agencia)
    {
        return Util::modulo11(Util::numberFormatGeral($agencia, 5));
    }

    public static function cefContaCorrente($agencia, $conta)
    {
        $n = Util::numberFormatGeral($agencia, 5)
            . Util::numberFormatGeral($conta, 11);

        return Util::modulo11($n);
    }

    public static function cefNossoNumero($nossoNumero)
    {
        return Util::modulo11($nossoNumero);
    }

    /*
    |--------------------------------------------------------------------------
    | 133 - Cresol
    |--------------------------------------------------------------------------
    */
    public static function cresolContaCorrente($conta)
    {
        return Util::modulo11($conta, 2, 9, 0, 'P');
    }

    public static function cresolNossoNumero($carteira, $nossoNumero)
    {
        return Util::modulo11($carteira . Util::numberFormatGeral($nossoNumero, 11), 2, 7, 0, 'P');
    }

    /*
    |--------------------------------------------------------------------------
    | 136 - Unicred
    |--------------------------------------------------------------------------
    */
    public static function unicredAgencia($agencia)
    {
        $dv = Util::modulo11($agencia);

        return $dv == 11 ? 0 : $dv;
    }

    public static function unicredContaCorrente($conta)
    {
        return Util::modulo11($conta);
    }

    public static function unicredNossoNumero($nossoNumero)
    {
        return Util::modulo11(Util::numberFormatGeral($nossoNumero, 10));
    }

    /*
    |--------------------------------------------------------------------------
    | 208 - BTG
    |--------------------------------------------------------------------------
    */

    public static function btgNossoNumero($carteira, $numero_boleto)
    {
        if (strlen($numero_boleto) < 11) {
            $numero_boleto = Util::numberFormatGeral($numero_boleto, 11);
        }
        $n = '0' . Util::numberFormatGeral($carteira, 2) . $numero_boleto;

        return Util::modulo11($n, 2, 7, 0, 'P');
    }

    public static function btgAgencia($agencia)
    {
        return Util::modulo11($agencia);
    }

    public static function btgContaCorrente($conta)
    {
        return Util::modulo11($conta);
    }

    /*
    |--------------------------------------------------------------------------
    | 237 - Bradesco
    |--------------------------------------------------------------------------
    */
    public static function bradescoAgencia($agencia)
    {
        $dv = Util::modulo11($agencia, 2, 9, 0, 'P');

        return $dv == 11 ? 0 : $dv;
    }

    public static function bradescoContaCorrente($conta)
    {
        return Util::modulo11($conta, 2, 9, 0, 'P');
    }

    public static function bradescoNossoNumero($carteira, $nossoNumero)
    {
        return Util::modulo11($carteira . Util::numberFormatGeral($nossoNumero, 11), 2, 7, 0, 'P');
    }

    /*
    |--------------------------------------------------------------------------
    | 224 - Fibra
    |--------------------------------------------------------------------------
    */
    public static function fibraAgencia($agencia)
    {
        return Util::modulo11($agencia);
    }

    public static function fibraConta($conta)
    {
        return Util::modulo11($conta);
    }

    public static function fibraNossoNumero($agencia, $nossaCarteira, $numero_boleto)
    {
        $n = Util::numberFormatGeral($agencia, 4)
            . Util::numberFormatGeral($nossaCarteira, 3)
            . Util::numberFormatGeral($numero_boleto, 10);

        $n = strrev($n);
        $factor = 2;
        $sum = 0;
        for ($i = mb_strlen($n); $i > 0; $i--) {
            $parcial = ((int) mb_substr($n, $i - 1, 1)) * $factor;
            if ($parcial > 9) {
                $parcial = (int) mb_substr($parcial, 0, 1) + (int) mb_substr($parcial, 1, 1);
            }
            $sum += $parcial;
            if ($factor == 2) {
                $factor = 0;
            }
            $factor++;
        }

        return 10 - $sum % 10;
    }

    /*
    |--------------------------------------------------------------------------
    | 435 - Delcred (Delbank)
    |--------------------------------------------------------------------------
    */

    public static function delcredNossoNumero($carteira, $nossoNumero)
    {
        $agencia = '0019';
        $numeroFormatado = Util::numberFormatGeral($nossoNumero, 10);

        return Util::modulo10($agencia . $carteira . $numeroFormatado);
    }

    /*
    |--------------------------------------------------------------------------
    | 336 - C6
    |--------------------------------------------------------------------------
    */

    public static function c6NossoNumero($carteira, $numero_boleto)
    {
        $n = '0' . Util::numberFormatGeral($carteira, 2) . Util::numberFormatGeral($numero_boleto, 10);

        return Util::modulo11($n, 2, 7, 0, 'P');
    }

    /*
    |--------------------------------------------------------------------------
    | 341 - Itau
    |--------------------------------------------------------------------------
    */
    public static function itauContaCorrente($agencia, $conta)
    {
        $n = Util::numberFormatGeral($agencia, 4)
            . Util::numberFormatGeral($conta, 5);

        return Util::modulo10($n);
    }

    public static function itauNossoNumero($agencia, $conta, $carteira, $numero_boleto)
    {
        $n = Util::numberFormatGeral($agencia, 4)
            . Util::numberFormatGeral($conta, 5)
            . Util::numberFormatGeral($carteira, 3)
            . Util::numberFormatGeral($numero_boleto, 8);

        return Util::modulo10($n);
    }

    /*
    |--------------------------------------------------------------------------
    | 633 - Rendimento
    |--------------------------------------------------------------------------
    */
    public static function rendimentoAgencia($agencia)
    {
        return Util::modulo11($agencia);
    }

    public static function rendimentoConta($conta)
    {
        return Util::modulo11($conta);
    }

    public static function rendimentoNossoNumero($agencia, $nossaCarteira, $numero_boleto)
    {
        $n = Util::numberFormatGeral($agencia, 4)
            . Util::numberFormatGeral($nossaCarteira, 3)
            . Util::numberFormatGeral($numero_boleto, 10);

        $n = strrev($n);
        $factor = 2;
        $sum = 0;

        for ($i = mb_strlen($n); $i > 0; $i--) {
            $x = ((int) mb_substr($n, $i - 1, 1));
            $parcial = $x * $factor;
            if ($parcial > 9) {
                $parcial = (int) mb_substr($parcial, 0, 1) + (int) mb_substr($parcial, 1, 1);
            }
            $sum += $parcial;
            if ($factor == 2) {
                $factor = 0;
            }
            $factor++;
        }

        return 10 - $sum % 10;
    }

    /*
    |--------------------------------------------------------------------------
    | 643 - Pine
    |--------------------------------------------------------------------------
    */
    public static function pineAgencia($agencia)
    {
        return Util::modulo11($agencia);
    }

    public static function pineConta($conta)
    {
        return Util::modulo11($conta);
    }

    public static function pineNossoNumero($agencia, $nossaCarteira, $numero_boleto)
    {
        $n = Util::numberFormatGeral($agencia, 4)
            . Util::numberFormatGeral($nossaCarteira, 3)
            . Util::numberFormatGeral($numero_boleto, 10);

        $n = strrev($n);
        $factor = 2;
        $sum = 0;
        for ($i = mb_strlen($n); $i > 0; $i--) {
            $parcial = ((int) mb_substr($n, $i - 1, 1)) * $factor;
            if ($parcial > 9) {
                $parcial = (int) mb_substr($parcial, 0, 1) + (int) mb_substr($parcial, 1, 1);
            }
            $sum += $parcial;
            if ($factor == 2) {
                $factor = 0;
            }
            $factor++;
        }

        return 10 - $sum % 10;
    }

    /*
    |--------------------------------------------------------------------------
    | 748 - Sicredi - Falta o calculo agencia e conta
    |--------------------------------------------------------------------------
    */
    public static function sicrediNossoNumero($agencia, $posto, $codigoCliente, $ano, $byte, $numero_boleto)
    {
        $n = Util::numberFormatGeral($agencia, 4)
            . Util::numberFormatGeral($posto, 2)
            . Util::numberFormatGeral($codigoCliente, 5)
            . Util::numberFormatGeral($ano, 2)
            . Util::numberFormatGeral($byte, 1)
            . Util::numberFormatGeral($numero_boleto, 5);

        return Util::modulo11($n);
    }

    /*
    |--------------------------------------------------------------------------
    | 712 - Ourinvest
    |--------------------------------------------------------------------------
    */

    public static function ourinvestNossoNumero($carteira, $nossoNumero)
    {
        return Util::modulo11(Util::numberFormatGeral($carteira, 2) . Util::numberFormatGeral($nossoNumero, 11), 2, 7, 0, 'P');
    }

    public static function ourinvestAgencia($agencia)
    {
        return null;
    }

    public static function ourinvestConta($conta, $agencia = '0001')
    {
        return Util::modulo10(Util::numberFormatGeral($agencia, 4) . Util::numberFormatGeral($conta, 7));
    }

    /*
    |--------------------------------------------------------------------------
    | 756 - Bancoob - Falta o calculo conta e confirmar agencia
    |--------------------------------------------------------------------------
    */
    public static function bancoobAgencia($agencia)
    {
        return Util::modulo11($agencia);
    }

    public static function bancoobContaCorrente($conta)
    {
        return Util::modulo11($conta);
    }

    public static function bancoobNossoNumero($agencia, $convenio, $numero_boleto)
    {
        $n = Util::numberFormatGeral($agencia, 4)
            . Util::numberFormatGeral($convenio, 10)
            . Util::numberFormatGeral($numero_boleto, 7);

        $chars = str_split($n, 1);
        $sums = str_split('3197319731973197319731973197', 1);
        $sum = 0;
        foreach ($chars as $i => $char) {
            $sum += $char * $sums[$i];
        }
        $resto = $sum % 11;
        $dv = 0;

        if (($resto != 0) && ($resto != 1)) {
            $dv = 11 - $resto;
        }

        return $dv;
    }
}
