<?php

namespace Eduardokum\LaravelBoleto\Boleto\Render;

use FPDF;

abstract class AbstractPdf extends FPDF
{
    // INCLUDE JS
    protected $javascript;

    protected $n_js;

    protected $angle = 0;

    // PAGE GROUP
    protected $NewPageGroup; // variable indicating whether a new group was requested

    protected $PageGroups = []; // variable containing the number of pages of the groups

    protected $CurrPageGroup; // variable containing the alias of the current page group

    protected $encrypted = false;  //whether document is protected

    protected $Uvalue;             //U entry in pdf document

    protected $Ovalue;             //O entry in pdf document

    protected $Pvalue;             //P entry in pdf document

    protected $enc_obj_id;         //encryption object id

    private $last_key;

    private $last_state;

    public function __construct($orientation = 'P', $unit = 'mm', $size = 'A4')
    {
        parent::__construct($orientation, $unit, $size);
        $this->AliasNbPages('{1}');
    }

    public function Footer()
    {
        $this->SetY(-8);
        if (count($this->PageGroups)) {
            $this->Cell(0, 6, 'Boleto ' . $this->GroupPageNo() . '/' . $this->PageGroupAlias(), 0, 0, 'C');
        }
    }

    public function StartPageGroup()
    {
        $this->NewPageGroup = true;
    }

    public function GroupPageNo()
    {
        return $this->PageGroups[$this->CurrPageGroup];
    }

    public function PageGroupAlias()
    {
        return $this->CurrPageGroup;
    }

    public function i25($xpos, $ypos, $code, $basewidth = 1, $height = 10)
    {
        $code = (strlen($code) % 2 != 0 ? '0' : '') . $code;
        $wide = $basewidth;
        $narrow = $basewidth / 3;

        $barChar = [];
        // wide/narrow codes for the digits
        $barChar['0'] = 'nnwwn';
        $barChar['1'] = 'wnnnw';
        $barChar['2'] = 'nwnnw';
        $barChar['3'] = 'wwnnn';
        $barChar['4'] = 'nnwnw';
        $barChar['5'] = 'wnwnn';
        $barChar['6'] = 'nwwnn';
        $barChar['7'] = 'nnnww';
        $barChar['8'] = 'wnnwn';
        $barChar['9'] = 'nwnwn';
        $barChar['A'] = 'nn';
        $barChar['Z'] = 'wn';

        $this->SetFont('Arial', '', 10);
        $this->SetFillColor(0);

        // add start and stop codes
        $code = 'AA' . strtolower($code) . 'ZA';

        for ($i = 0; $i < strlen($code); $i = $i + 2) {
            // choose next pair of digits
            $charBar = $code[$i];
            $charSpace = $code[$i + 1];
            // check whether it is a valid digit
            if (! isset($barChar[$charBar])) {
                $this->Error('Invalid character in barcode: ' . $charBar);
            }
            if (! isset($barChar[$charSpace])) {
                $this->Error('Invalid character in barcode: ' . $charSpace);
            }
            // create a wide/narrow-sequence (first digit=bars, second digit=spaces)
            $seq = '';
            for ($s = 0; $s < strlen($barChar[$charBar]); $s++) {
                $seq .= $barChar[$charBar][$s] . $barChar[$charSpace][$s];
            }
            for ($bar = 0; $bar < strlen($seq); $bar++) {
                // set lineWidth depending on value
                if ($seq[$bar] == 'n') {
                    $lineWidth = $narrow;
                } else {
                    $lineWidth = $wide;
                }
                // draw every second value, because the second digit of the pair is represented by the spaces
                if ($bar % 2 == 0) {
                    $this->Rect($xpos, $ypos, $lineWidth, $height, 'F');
                }
                $xpos += $lineWidth;
            }
        }
    }

    public function SetProtection($user_pass = '', $permissions = [], $owner_pass = null)
    {
        $options = ['print' => 4, 'modify' => 8, 'copy' => 16, 'annot-forms' => 32];
        $protection = 192;
        foreach ($permissions as $permission) {
            if (! isset($options[$permission])) {
                $this->Error('Incorrect permission: ' . $permission);
            }
            $protection += $options[$permission];
        }
        if ($owner_pass === null) {
            $owner_pass = uniqid(rand());
        }
        $this->encrypted = true;
        $this->padding = "\x28\xBF\x4E\x5E\x4E\x75\x8A\x41\x64\x00\x4E\x56\xFF\xFA\x01\x08" .
            "\x2E\x2E\x00\xB6\xD0\x68\x3E\x80\x2F\x0C\xA9\xFE\x64\x53\x69\x7A";
        $this->_generateencryptionkey($user_pass, $owner_pass, $protection);
    }

    /**
     * @param string $name
     * @param string $dest
     * I: send the file inline to the browser.<br>
     * D: send to the browser and force download.<br>
     * F: save to a local<br>
     * S: return as a string. name is ignored.
     * @return string
     */
    public function Output($name = '', $dest = 'I', $isUTF8 = false)
    {
        return parent::Output($name, $dest, $isUTF8);
    }

    protected function IncludeJS($script)
    {
        $this->javascript = $script;
    }

    protected function _putresources()
    {
        parent::_putresources();
        if (! empty($this->javascript)) {
            $this->_putjavascript();
        }

        if ($this->encrypted) {
            $this->_newobj();
            $this->enc_obj_id = $this->n;
            $this->_put('<<');
            $this->_putencryption();
            $this->_put('>>');
            $this->_put('endobj');
        }
    }

    protected function _putjavascript()
    {
        $this->_newobj();
        $this->n_js = $this->n;
        $this->_put('<<');
        $this->_put('/Names [(EmbeddedJS) ' . ($this->n + 1) . ' 0 R]');
        $this->_put('>>');
        $this->_put('endobj');
        $this->_newobj();
        $this->_put('<<');
        $this->_put('/S /JavaScript');
        $this->_put('/JS ' . $this->_textstring($this->javascript));
        $this->_put('>>');
        $this->_put('endobj');
    }

    protected function _putcatalog()
    {
        parent::_putcatalog();
        if (! empty($this->javascript)) {
            $this->_put('/Names <</JavaScript ' . ($this->n_js) . ' 0 R>>');
        }
    }

    protected function _putencryption()
    {
        $this->_put('/Filter /Standard');
        $this->_put('/V 1');
        $this->_put('/R 2');
        $this->_put('/O (' . $this->_escape($this->Ovalue) . ')');
        $this->_put('/U (' . $this->_escape($this->Uvalue) . ')');
        $this->_put('/P ' . $this->Pvalue);
    }

    protected function _beginpage($orientation, $size, $rotation)
    {
        parent::_beginpage($orientation, $size, $rotation);
        if ($this->NewPageGroup) {
            // start a new group
            if (! is_array($this->PageGroups)) {
                $this->PageGroups = [];
            }
            $n = sizeof($this->PageGroups) + 1;
            $alias = '{' . $n . '}';
            $this->PageGroups[$alias] = 1;
            $this->CurrPageGroup = $alias;
            $this->NewPageGroup = false;
        } elseif ($this->CurrPageGroup) {
            $this->PageGroups[$this->CurrPageGroup]++;
        }
    }

    protected function _putpages()
    {
        $nb = $this->page;
        if (! empty($this->PageGroups)) {
            // do page number replacement
            foreach ($this->PageGroups as $k => $v) {
                for ($n = 1; $n <= $nb; $n++) {
                    $this->pages[$n] = str_replace($k, $v, $this->pages[$n]);
                }
            }
        }
        parent::_putpages();
    }

    protected function _putstream($s)
    {
        if ($this->encrypted) {
            $s = $this->RC4($this->_objectkey($this->n), $s);
        }
        parent::_putstream($s);
    }

    protected function _puttrailer()
    {
        parent::_puttrailer();
        if ($this->encrypted) {
            $this->_put('/Encrypt ' . $this->enc_obj_id . ' 0 R');
            $this->_put('/ID [()()]');
        }
    }

    protected function _textstring($s)
    {
        if (! $this->_isascii($s)) {
            $s = $this->_UTF8toUTF16($s);
        }
        if ($this->encrypted) {
            $s = $this->RC4($this->_objectkey($this->n), $s);
        }

        return '(' . $this->_escape($s) . ')';
    }

    protected function _()
    {
        $args = func_get_args();
        $var = utf8_decode(array_shift($args));
        $s = vsprintf($var, $args);

        return $s;
    }

    protected function textFitCell($w, $h, $txt, $border, $ln, $align, $dec = 0.1)
    {
        $fsize = $this->FontSizePt;
        $size = $fsize;
        while ($this->GetStringWidth($txt) > ($w - 2)) {
            $this->SetFontSize($size -= $dec);
        }
        $this->Cell($w, $h, $txt, $border, $ln, $align);
        $this->SetFontSize($fsize);
    }

    protected function _generateencryptionkey($user_pass, $owner_pass, $protection)
    {
        // Pad passwords
        $user_pass = substr($user_pass . $this->padding, 0, 32);
        $owner_pass = substr($owner_pass . $this->padding, 0, 32);
        // Compute O value
        $this->Ovalue = $this->_Ovalue($user_pass, $owner_pass);
        // Compute encyption key
        $tmp = $this->_md5_16($user_pass . $this->Ovalue . chr($protection) . "\xFF\xFF\xFF");
        $this->encryption_key = substr($tmp, 0, 5);
        // Compute U value
        $this->Uvalue = $this->_Uvalue();
        // Compute P value
        $this->Pvalue = -(($protection ^ 255) + 1);
    }

    protected function _Ovalue($user_pass, $owner_pass)
    {
        $tmp = $this->_md5_16($owner_pass);
        $owner_RC4_key = substr($tmp, 0, 5);

        return $this->RC4($owner_RC4_key, $user_pass);
    }

    protected function _Uvalue()
    {
        return $this->RC4($this->encryption_key, $this->padding);
    }

    protected function _md5_16($string)
    {
        return pack('H*', md5($string));
    }

    protected function _objectkey($n)
    {
        return substr($this->_md5_16($this->encryption_key . pack('VXxx', $n)), 0, 10);
    }

    protected function RC4($key, $data)
    {
        if (function_exists('mcrypt_encrypt')) {
            return mcrypt_encrypt(MCRYPT_ARCFOUR, $key, $data, MCRYPT_MODE_STREAM, '');
        }

        if ($key != $this->last_key) {
            $k = str_repeat($key, 256 / strlen($key) + 1);
            $state = range(0, 255);
            $j = 0;
            for ($i = 0; $i < 256; $i++) {
                $t = $state[$i];
                $j = ($j + $t + ord($k[$i])) % 256;
                $state[$i] = $state[$j];
                $state[$j] = $t;
            }
            $this->last_key = $key;
            $this->last_state = $state;
        } else {
            $state = $this->last_state;
        }

        $len = strlen($data);
        $a = 0;
        $b = 0;
        $out = '';
        for ($i = 0; $i < $len; $i++) {
            $a = ($a + 1) % 256;
            $t = $state[$a];
            $b = ($b + $t) % 256;
            $state[$a] = $state[$b];
            $state[$b] = $t;
            $k = $state[($state[$a] + $state[$b]) % 256];
            $out .= chr(ord($data[$i]) ^ $k);
        }

        return $out;
    }
}
