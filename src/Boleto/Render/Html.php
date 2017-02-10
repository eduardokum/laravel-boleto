<?php
namespace Eduardokum\LaravelBoleto\Boleto\Render;

use \Eduardokum\LaravelBoleto\Contracts\Boleto\Render\Html as HtmlContract;

class Html implements HtmlContract
{
    /**
     * Dados para passar pra view.
     *
     * @var array
     */
    protected $dados = [];

    /**
     * Html constructor.
     *
     * @param array $dados
     */
    public function __construct(array $dados)
    {
        $this->dados = $dados;
        $this->dados['css'] = $this->writeCss();
        $this->dados['codigo_barras'] = $this->getImagemCodigoDeBarras($this->dados['codigo_barras']);
    }


    /**
     * Escreve o codigo css na página
     */
    public function writeCss()
    {
        return "@media print{.noprint{display:none}}body{background-color:#fff;margin-right:0}.table-boleto{font:9px Arial;width:666px}.table-boleto td.top-2{border-top-width: 2px;}.table-boleto td{border-left:1px solid #000;border-top:1px solid #000;padding:1px 4px}.table-boleto td:last-child{border-right:1px solid #000}.table-boleto .titulo{color:#003}.linha-pontilhada{color:#003;font:9px Arial;width:100%;border-bottom:1px dashed #000;text-align:right;margin-bottom:10px}.table-boleto .conteudo{font:700 10px Arial;height:13px}.table-boleto .sacador{display:inline;margin-left:5px}.table-boleto .noleftborder{border-left:none!important}.table-boleto .notopborder{border-top:none!important}.table-boleto .norightborder{border-right:none!important}.table-boleto .noborder{border:none!important}.table-boleto .bottomborder{border-bottom:1px solid #000!important}.table-boleto .rtl{text-align:right}.table-boleto .logobanco{display:inline-block;max-width:150px}.table-boleto .logocontainer{width:257px;display:inline-block}.table-boleto .logobanco img{margin-bottom:-5px}.table-boleto .codbanco{font:700 20px Arial;padding:1px 5px;display:inline;border-left:2px solid #000;border-right:2px solid #000;width:51px;margin-left:0px}.table-boleto .linha-digitavel{font:700 14px Arial;display:inline-block;width:406px;text-align:right}.table-boleto .nopadding{padding:0!important}.table-boleto .caixa-gray-bg{font-weight:700;background:#ccc}.info,.info-empresa{font:11px Arial}.header{font:700 13px Arial;display:block;margin:4px}.barcode{height:50px}.barcode div{display:inline-block;height:100%}.barcode .black{border-color:#000;border-left-style:solid;width:0}.barcode .white{background:#fff}.barcode .thin.black{border-left-width:1px}.barcode .large.black{border-left-width:3px}.barcode .thin.white{width:1px}.barcode .large.white{width:3px}";
    }

    /**
     * Retorna a string contendo as imagens do código de barras, segundo o padrão Febraban
     *
     * @param $codigo_barras
     *
     * @return string
     */
    public function getImagemCodigoDeBarras($codigo_barras)
    {
        $codigo_barras = (strlen($codigo_barras)%2 != 0 ? '0' : '') . $codigo_barras;
        $barcodes = ['00110', '10001', '01001', '11000', '00101', '10100', '01100', '00011', '10010', '01010'];
        for ($f1 = 9; $f1 >= 0; $f1--) {
            for ($f2 = 9; $f2 >= 0; $f2--) {
                $f = ($f1*10) + $f2;
                $texto = "";
                for ($i = 1; $i < 6; $i++) {
                    $texto .= substr($barcodes[$f1], ($i - 1), 1) . substr($barcodes[$f2], ($i - 1), 1);
                }
                $barcodes[$f] = $texto;
            }
        }
        
        // Guarda inicial
        $retorno = '<div class="barcode">' .
            '<div class="black thin"></div>' .
            '<div class="white thin"></div>' .
            '<div class="black thin"></div>' .
            '<div class="white thin"></div>';

        // Draw dos dados
        while (strlen($codigo_barras) > 0) {
            $i = round(substr($codigo_barras, 0, 2));
            $codigo_barras = substr($codigo_barras, strlen($codigo_barras) - (strlen($codigo_barras) - 2), strlen($codigo_barras) - 2);
            ;
            $f = $barcodes[$i];
            for ($i = 1; $i < 11; $i += 2) {
                if (substr($f, ($i - 1), 1) == "0") {
                    $f1 = 'thin';
                } else {
                    $f1 = 'large';
                }
                $retorno .= "<div class='black {$f1}'></div>";
                if (substr($f, $i, 1) == "0") {
                    $f2 = 'thin';
                } else {
                    $f2 = 'large';
                }
                $retorno .= "<div class='white {$f2}'></div>";
            }
        }

        // Final
        return $retorno . '<div class="black large"></div>' .
        '<div class="white thin"></div>' .
        '<div class="black thin"></div>' .
        '</div>';
    }

    /**
     * função para gerar o boleto
     *
     * @param bool $print
     *
     * @return string
     * @throws \Throwable
     */
    public function gerarBoleto($print = false)
    {
        $this->dados['imprimir_carregamento'] = (bool) $print;
        view()->addNamespace('BoletoHtmlRender', realpath(__DIR__ . '/view/'));
        return view('BoletoHtmlRender::boleto', $this->dados)->render();
    }
}
