<?php
/**
 *   Copyright (c) 2016 Eduardo Gusmão
 *
 *   Permission is hereby granted, free of charge, to any person obtaining a
 *   copy of this software and associated documentation files (the "Software"),
 *   to deal in the Software without restriction, including without limitation
 *   the rights to use, copy, modify, merge, publish, distribute, sublicense,
 *   and/or sell copies of the Software, and to permit persons to whom the
 *   Software is furnished to do so, subject to the following conditions:
 *
 *   The above copyright notice and this permission notice shall be included in all
 *   copies or substantial portions of the Software.
 *
 *   THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 *   INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR
 *   PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 *   COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 *   WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR
 *   IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace Eduardokum\LaravelBoleto\Boleto\Render;

use fpdf\FPDF;

abstract class AbstractPdf extends FPDF
{
    // INCLUDE JS
    protected $javascript;
    protected $n_js;
    protected $angle=0;

    protected function IncludeJS($script)
    {
        $this->javascript = $script;
    }

    protected function _putjavascript()
    {
        $this->_newobj();
        $this->n_js = $this->n;
        $this->_out('<<');
        $this->_out('/Names [(EmbeddedJS) ' . ($this->n + 1) . ' 0 R]');
        $this->_out('>>');
        $this->_out('endobj');
        $this->_newobj();
        $this->_out('<<');
        $this->_out('/S /JavaScript');
        $this->_out('/JS ' . $this->_textstring($this->javascript));
        $this->_out('>>');
        $this->_out('endobj');
    }

    public function _putresources()
    {
        parent::_putresources();
        if (!empty($this->javascript)) {
            $this->_putjavascript();
        }
    }

    public function _putcatalog()
    {
        parent::_putcatalog();
        if (!empty($this->javascript)) {
            $this->_out('/Names <</JavaScript ' . ($this->n_js) . ' 0 R>>');
        }
    }

    // PAGE GROUP
    protected $NewPageGroup;   // variable indicating whether a new group was requested
    protected $PageGroups;     // variable containing the number of pages of the groups
    protected $CurrPageGroup;  // variable containing the alias of the current page group

    // create a new page group; call this before calling AddPage()
    public function StartPageGroup()
    {
        $this->NewPageGroup=true;
    }

    // current page in the group
    public function GroupPageNo()
    {
        return $this->PageGroups[$this->CurrPageGroup];
    }

    // alias of the current page group -- will be replaced by the total number of pages in this group
    public function PageGroupAlias()
    {
        return $this->CurrPageGroup;
    }

    public function _beginpage($orientation, $size)
    {
        parent::_beginpage($orientation, $size);
        if($this->NewPageGroup)
        {
            // start a new group
            $n = sizeof($this->PageGroups)+1;
            $alias = '{'.$n.'}';
            $this->PageGroups[$alias] = 1;
            $this->CurrPageGroup = $alias;
            $this->NewPageGroup=false;
        }
        elseif($this->CurrPageGroup)
            $this->PageGroups[$this->CurrPageGroup]++;
    }

    public function _putpages()
    {
        $nb = $this->page;
        if (!empty($this->PageGroups))
        {
            // do page number replacement
            foreach ($this->PageGroups as $k => $v)
            {
                for ($n = 1; $n <= $nb; $n++)
                {
                    $this->pages[$n]=str_replace($k, $v, $this->pages[$n]);
                }
            }
        }
        parent::_putpages();
    }

    protected function _() {
        $args = func_get_args();
        $var  = utf8_decode(array_shift($args));
        $s    = vsprintf($var, $args);
        return $s;
    }

    /**
     * @param $w
     * @param $h
     * @param $txt
     * @param $border
     * @param $ln
     * @param $align
     * @param float $dec
     */
    protected function textFitCell($w, $h, $txt, $border, $ln, $align,$dec = 0.1 ) {
        $fsize = $this->FontSizePt;
        $size = $fsize;
        while($this->GetStringWidth($txt) > ($w-2)) {
            $this->SetFontSize($size -= $dec);
        }
        $this->Cell($w, $h, $txt, $border, $ln, $align);
        $this->SetFontSize($fsize);
    }

    /**
     * @param $angle
     * @param int $x
     * @param int $y
     */
    protected function rotate($angle, $x = -1, $y = -1) {
        if ($x == -1)
            $x = $this->x;
        if ($y == -1)
            $y = $this->y;
        if ($this->angle != 0)
            $this->_out('Q');
        if ($angle != 0) {
            $angle*=M_PI / 180;
            $c = cos($angle);
            $s = sin($angle);
            $cx = $x * $this->k;
            $cy = ($this->h - $y) * $this->k;

            $this->_out(sprintf('q %.5f %.5f %.5f %.5f %.2f %.2f cm 1 0 0 1 %.2f %.2f cm', $c, $s, -$s, $c, $cx, $cy, -$cx, -$cy));
        }
    }

    /**
     * @param $width
     * @param $height
     * @param $maxwidth
     * @param $maxheight
     * @return array
     */
    protected function calculateDimensions($width,$height,$maxwidth,$maxheight)
    {
        if($width != $height)
        {
            if($width > $height)
            {
                $t_width = $maxwidth;
                $t_height = (($t_width * $height)/$width);
                //fix height
                if($t_height > $maxheight)
                {
                    $t_height = $maxheight;
                    $t_width = (($width * $t_height)/$height);
                }
            }
            else
            {
                $t_height = $maxheight;
                $t_width = (($width * $t_height)/$height);
                //fix width
                if($t_width > $maxwidth)
                {
                    $t_width = $maxwidth;
                    $t_height = (($t_width * $height)/$width);
                }
            }
        }
        else
            $t_width = $t_height = min($maxheight,$maxwidth);

        return array('width'=>(int)$t_width,'w'=>(int)$t_width,'height'=>(int)$t_height, 'h'=>(int)$t_height);
    }

    /**
     * @param $pt
     * @return float
     */
    protected function point2px($pt)
    {
        return ceil($pt * 96 / 72);
    }

    /**
     * BarCode
     *
     * @param     $xpos
     * @param     $ypos
     * @param     $code
     * @param int $basewidth
     * @param int $height
     */
    public function i25($xpos, $ypos, $code, $basewidth = 1, $height = 10)
    {

        $wide = $basewidth;
        $narrow = $basewidth / 3;

        $barChar = array();
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

        // add leading zero if code-length is odd
        if (strlen($code) % 2 != 0) {
            $code = '0' . $code;
        }

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
            for ($s = 0; $s < strlen($barChar[$charBar]); $s ++) {
                $seq .= $barChar[$charBar][$s] . $barChar[$charSpace][$s];
            }
            for ($bar = 0; $bar < strlen($seq); $bar ++) {
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


    public function __construct($orientation = 'P', $unit = 'mm', $size = 'A4')
    {
        parent::__construct($orientation, $unit, $size);
        $this->SetCreator($this->_('Intrasis Desenvolvimento de Sistemas'));
        $this->SetAuthor($this->_('Intrasis Desenvolvimento de Sistemas'));
        $this->SetSubject($this->_('Visualização gerada pelo Sismanager'));
        $this->SetKeywords($this->_('Visualização visualização Visualizacao visualizacao sismanger Sismanger Intrasis intrasis'));
        $this->AliasNbPages('{1}');
    }

    /**
     * @param string $name
     * @param string $dest
     * I: send the file inline to the browser.<br>
     * D: send to the browser and force download.<br>
     * F: save to a local<br>
     * S: return as a string. name is ignored.
     * @param bool $print 1 imprime 0 nao imprime
     * @return string|void
     */
    public function Output($name = '', $dest = 'I', $print = false)
    {
        if ($print) {
            $this->IncludeJS("print('true');");
        }
        return parent::Output($name, $dest);
    }
}