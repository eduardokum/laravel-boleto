<?php

namespace Eduardokum\LaravelBoleto\Boleto\Render;

use Eduardokum\LaravelBoleto\Blade;
use Illuminate\Container\Container;
use Illuminate\Contracts\View\Factory;
use Eduardokum\LaravelBoleto\Exception\ValidationException;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Render\Html as HtmlContract;

class Html implements HtmlContract
{
    const PIX_INSTRUCAO = 'instrucao';
    const PIX_COD_BARRAS = 'barras';

    /**
     * @var BoletoContract[]
     */
    private $boleto = [];

    /**
     * @var bool
     */
    private $print = false;

    /**
     * @var bool
     */
    private $showInstrucoes = true;

    /**
     * @var \Illuminate\View\Factory
     */
    private $blade = null;

    /**
     * @var string
     */
    protected $localizacao_pix = self::PIX_INSTRUCAO;

    /**
     * @param $localizacao
     * @return Html
     * @throws ValidationException
     */
    public function setLocalizacaoPix($localizacao)
    {
        if (! in_array($localizacao, [self::PIX_COD_BARRAS, self::PIX_INSTRUCAO])) {
            throw new ValidationException('Pix deve ser Pdf::PIX_COD_BARRAS ou Pdf::PIX_INSTRUCAO');
        }

        $this->localizacao_pix = $localizacao;

        return $this;
    }

    /**
     * @return \Illuminate\View\Factory
     * @throws ValidationException
     */
    private function getBlade()
    {
        if (! is_null($this->blade)) {
            return $this->blade;
        }
        $instance = Container::getInstance();
        if (! is_null($instance) && $instance->resolved(Factory::class)) {
            view()->addNamespace('BoletoHtmlRender', realpath(__DIR__ . '/view/'));
            $this->blade = view();
        } else {
            $blade = new Blade(realpath(__DIR__ . '/view/'), realpath(__DIR__ . '/cache/'));
            $blade->view()->addNamespace('BoletoHtmlRender', realpath(__DIR__ . '/view/'));
            $this->blade = $blade->view();
        }
        $blade = $this->blade->getEngineResolver()->resolve('blade')->getCompiler();
        $blade->directive('php', function ($expression) {
            return $expression ? "<?php {$expression}; ?>" : '<?php ';
        });
        $blade->directive('endphp', function ($expression) {
            return ' ?>';
        });

        return $this->blade;
    }

    /**
     * Adiciona o boletos
     *
     * @param array $boletos
     *
     * @return Html
     * @throws ValidationException
     */
    public function addBoletos(array $boletos)
    {
        foreach ($boletos as $boleto) {
            $this->addBoleto($boleto);
        }

        return $this;
    }

    /**
     * Adiciona o boleto
     *
     * @param BoletoContract $boleto
     *
     * @return Html
     * @throws ValidationException
     */
    public function addBoleto(BoletoContract $boleto)
    {
        if (! $boleto->imprimeBoleto()) {
            throw new ValidationException('Boleto com modalidade/carteira não disponível para impressão');
        }
        $dados = $boleto->toArray();
        $dados['codigo_barras'] = $this->getImagemCodigoDeBarras($dados['codigo_barras']);
        $this->boleto[] = $dados;

        return $this;
    }

    /**
     * @return Html
     */
    public function hideInstrucoes()
    {
        $this->showInstrucoes = false;

        return $this;
    }

    /**
     * @return Html
     */
    public function showPrint()
    {
        $this->print = true;

        return $this;
    }

    /**
     * Escreve o codigo css na página
     */
    protected function writeCss()
    {
        $black = $this->localizacao_pix == self::PIX_COD_BARRAS ? 2 : 3;

        return "@media print{.noprint{display:none}}body{background-color:#fff;margin-right:0}.wrapper{width:666px;}.table-boleto{font:9px Arial;width:666px}.table-boleto td.top-2{border-top-width: 2px;}.table-boleto td{border-left:1px solid #000;border-top:1px solid #000;padding:1px 4px}.table-boleto td:last-child{border-right:1px solid #000}.table-boleto .titulo{color:#003}.linha-pontilhada{height:15px;color:#003;font:9px Arial;width:100%;border-bottom:1px dashed #000;text-align:right;margin-bottom:10px}.table-boleto .conteudo{font:700 10px Arial;height:13px}.table-boleto .sacador{display:inline;margin-left:5px}.table-boleto .noleftborder{border-left:none!important}.table-boleto .notopborder{border-top:none!important}.table-boleto .norightborder{border-right:none!important}.table-boleto .noborder{border:none!important}.table-boleto .bottomborder{border-bottom:1px solid #000!important}.table-boleto .rtl{text-align:right}.table-boleto .logobanco{display:inline-block;max-width:150px}.table-boleto .logocontainer{width:257px;display:inline-block}.table-boleto .logobanco img{margin-bottom:-5px}.table-boleto .codbanco{font:700 20px Arial;padding:1px 5px;display:inline;border-left:2px solid #000;border-right:2px solid #000;width:51px;margin-left:0px}.table-boleto .linha-digitavel{font:700 14px Arial;display:inline-block;width:406px;text-align:right}.table-boleto .nopadding{padding:0!important}.table-boleto .caixa-gray-bg{font-weight:700;background:#ccc}.info,.info-empresa{font:11px Arial}.header{font:700 13px Arial;display:block;margin:4px}.barcode{height:50px; display:inline-block}.barcode div{display:inline-block;height:100%}.barcode .black{border-color:#000;border-left-style:solid;width:0}.barcode .white{background:#fff}.barcode .thin.black{border-left-width:1px}.barcode .large.black{border-left-width:{$black}px}.barcode .thin.white{width:1px}.barcode .large.white{width:3px}.table-boleto tr.duas-linhas{vertical-align:top}";
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
        $codigo_barras = (strlen($codigo_barras) % 2 != 0 ? '0' : '') . $codigo_barras;
        $barcodes = ['00110', '10001', '01001', '11000', '00101', '10100', '01100', '00011', '10010', '01010'];
        for ($f1 = 9; $f1 >= 0; $f1--) {
            for ($f2 = 9; $f2 >= 0; $f2--) {
                $f = ($f1 * 10) + $f2;
                $texto = '';
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
            $f = $barcodes[$i];
            for ($i = 1; $i < 11; $i += 2) {
                if (substr($f, ($i - 1), 1) == '0') {
                    $f1 = 'thin';
                } else {
                    $f1 = 'large';
                }
                $retorno .= "<div class='black {$f1}'></div>";
                if (substr($f, $i, 1) == '0') {
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
     * @return string
     * @throws ValidationException
     */
    public function gerarBoleto()
    {
        if (count($this->boleto) == 0) {
            throw new ValidationException('Nenhum Boleto adicionado');
        }

        return $this->getBlade()->make('BoletoHtmlRender::boleto', [
            'localizacao_pix'       => $this->localizacao_pix,
            'boletos'               => $this->boleto,
            'css'                   => $this->writeCss(),
            'imprimir_carregamento' => (bool) $this->print,
            'mostrar_instrucoes'    => (bool) $this->showInstrucoes,
        ])->render();
    }

    /**
     * função para gerar o carne
     *
     * @return string
     * @throws ValidationException
     */
    public function gerarCarne()
    {
        if (count($this->boleto) == 0) {
            throw new ValidationException('Nenhum Boleto adicionado');
        }

        return $this->getBlade()->make('BoletoHtmlRender::carne', [
            'localizacao_pix'       => $this->localizacao_pix,
            'boletos'               => $this->boleto,
            'css'                   => $this->writeCss(),
            'imprimir_carregamento' => (bool) $this->print,
            'mostrar_instrucoes'    => (bool) $this->showInstrucoes,
        ])->render();
    }
}
