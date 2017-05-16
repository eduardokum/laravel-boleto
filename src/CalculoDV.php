<?php
namespace Eduardokum\LaravelBoleto;


class CalculoDV
{

    /*
    |--------------------------------------------------------------------------
    | 001 - Banco do Brasil
    |--------------------------------------------------------------------------
    */
    public static function bbAgencia($agencia) {
        return Util::modulo11($agencia, 2, 9, 0, 'X');
    }

    public static function bbContaCorrente($contaCorrente) {
        return Util::modulo11($contaCorrente, 2, 9, 0, 'X');
    }

    public static function bbNossoNumero($nossoNumero) {
        return strlen($nossoNumero) >= 17 ? null : Util::modulo11($nossoNumero);
    }

    /*
    |--------------------------------------------------------------------------
    | 004 - Banco do Nordeste
    |--------------------------------------------------------------------------
    */
    public static function bnbAgencia($agencia) {
        return Util::modulo11($agencia, 2, 9, 0, 'X');
    }

    public static function bnbNossoNumero($nossoNumero) {
        return Util::modulo11(Util::numberFormatGeral($nossoNumero, 7));
    }

    /*
    |--------------------------------------------------------------------------
    | 033 - Santander
    |--------------------------------------------------------------------------
    */
    public static function santanderContaCorrente($agencia, $contaCorrente) {
        $n = Util::numberFormatGeral($agencia, 4)
            . '00'
            . Util::numberFormatGeral($contaCorrente, 8);
        $chars = array_reverse(str_split($n, 1));
        $sums = array_reverse(str_split('97310097131973', 1));
        $sum = 0;
        foreach ($chars as $i => $char) {
            $sum += substr($char*$sums[$i], -1);
        }
        $unidade = substr($sum, -1);
        return $unidade == 0 ? $unidade : 10 - $unidade;
    }

    public static function santanderNossoNumero($nossoNumero) {
        return Util::modulo11($nossoNumero);
    }

    /*
    |--------------------------------------------------------------------------
    | 041 - Banrisul
    |--------------------------------------------------------------------------
    */
    public static function banrisulAgencia($agencia) {
        $newDv1 = $dv1 = Util::modulo10($agencia);
        $dv2 = Util::modulo11($agencia . $dv1, 2, 7);

        if($dv2 == 1 && $dv1 != 9) {
            $newDv1 = 1;
        }
        if($dv2 == 1 && $dv1 == 9) {
            $newDv1 = 0;
        }

        if($dv1 != $newDv1) {
            $dv1 = $newDv1;
            $dv2 = Util::modulo11($agencia . $dv1, 2, 7);
        }

        return $dv1.$dv2;
    }

    public static function banrisulContaCorrente($contaCorrente) {
        $chars = array_reverse(str_split($contaCorrente, 1));
        $sums = str_split('234567423', 1);

        $sum = 0;
        foreach ($chars as $i => $char) {
            $sum += $char*$sums[$i];
        }

        $resto = $sum%11;

        if($resto == 0) {
            return $resto;
        }

        if($resto == 1) {
            return 6;
        }

        return 11 - $resto;
    }

    public static function banrisulNossoNumero($nossoNumero) {
        return self::banrisulDuploDigito($nossoNumero);
    }

    public static function banrisulDuploDigito($campo) {
        $dv1 = Util::modulo10($campo);
        $dv2 = Util::modulo11($campo . $dv1, 2, 7);
        if ($dv2 == 1) {
            $dv1++;
            $dv2 = Util::modulo11($campo . $dv1, 2, 7);
            if ($dv1 > 9) {
                $dv1 = 0;
                $dv2 = Util::modulo11($campo . $dv1, 2, 7);
            }
        }
        return $dv1 . $dv2;
    }

    /*
    |--------------------------------------------------------------------------
    | 104 - Caixa Econômica Federal
    |--------------------------------------------------------------------------
    */
    public static function cefContaCorrente($agencia, $contaCorrente) {
        $n = Util::numberFormatGeral($agencia, 4)
            . Util::numberFormatGeral($contaCorrente, 11);
        return Util::modulo11($n);
    }

    public static function cefNossoNumero($nossoNumero) {
        return Util::modulo11($nossoNumero);
    }

    /*
    |--------------------------------------------------------------------------
    | 237 - Bradesco
    |--------------------------------------------------------------------------
    */
    public static function bradescoAgencia($agencia) {
        $dv = Util::modulo11($agencia, 2, 9, 0, 'P');
        return $dv == 11 ? 0 : $dv;
    }
    
    public static function bradescoContaCorrente($contaCorrente) {
        return Util::modulo11($contaCorrente, 2, 9, 0, 'P');
    }

    public static function bradescoNossoNumero($carteira, $nossoNumero) {
        return Util::modulo11($carteira . $nossoNumero, 2, 7, 0, 'P');
    }

    /*
    |--------------------------------------------------------------------------
    | 341 - Itau
    |--------------------------------------------------------------------------
    */
    public static function itauContaCorrente($agencia, $contaCorrente) {
        $n = Util::numberFormatGeral($agencia, 4)
            . Util::numberFormatGeral($contaCorrente, 5);
        return Util::modulo10($n);
    }

    public static function itauNossoNumero($agencia, $conta, $carteira, $numero_boleto) {
        $n = Util::numberFormatGeral($agencia, 4)
            . Util::numberFormatGeral($conta, 5)
            . Util::numberFormatGeral($carteira, 3)
            . Util::numberFormatGeral($numero_boleto, 8);
        return Util::modulo10($n);
    }

    /*
    |--------------------------------------------------------------------------
    | 748 - Sicredi
    |--------------------------------------------------------------------------
    */
    public static function sicrediNossoNumero($agencia, $posto, $conta, $ano, $byte, $numero_boleto) {
        $n = Util::numberFormatGeral($agencia, 4)
            . Util::numberFormatGeral($posto, 2)
            . Util::numberFormatGeral($conta, 5)
            . Util::numberFormatGeral($ano, 2)
            . Util::numberFormatGeral($byte, 1)
            . Util::numberFormatGeral($byte, 5);
        return  Util::modulo11($n);
    }

    /*
    |--------------------------------------------------------------------------
    | 756 - Bancoob
    |--------------------------------------------------------------------------
    */
    public static function bancoobAgencia($agencia) {
        return Util::modulo11($agencia);
    }

    public static function bancoobNossoNumero($agencia, $convenio, $numero_boleto) {
        $n = Util::numberFormatGeral($agencia, 4)
            . Util::numberFormatGeral($convenio, 10)
            . Util::numberFormatGeral($numero_boleto, 7);

        $chars = str_split($n, 1);
        $sums = str_split('3197319731973197319731973197', 1);
        $sum = 0;
        foreach ($chars as $i => $char) {
            $sum += $char*$sums[$i];
        }
        $resto = $sum % 11;
        $dv = 0;

        if (($resto != 0) && ($resto != 1)) {
            $dv = 11 - $resto;
        }
        return  $dv;
    }
}