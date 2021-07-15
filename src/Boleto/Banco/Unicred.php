<?php

namespace Eduardokum\LaravelBoleto\Boleto\Banco;


use Eduardokum\LaravelBoleto\Boleto\AbstractBoleto;
use Eduardokum\LaravelBoleto\CalculoDV;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;
use Eduardokum\LaravelBoleto\Util;


class Unicred extends AbstractBoleto implements BoletoContract
{

    protected $codigoBanco = Boleto::COD_BANCO_UNICRED;

    /**
     * Trata-se de código utilizado para identificar mensagens especificas ao cedente, sendo
     * que o mesmo consta no cadastro do Banco, quando não houver código cadastrado preencher
     * com zeros "000".
     *
     * @var int
     */
    protected $cip = '000';

    /**
     * Local de pagamento
     *
     * @var string
     */
    protected $localPagamento = 'Pagável Preferencialmente na Unicred';

    /**
     * Define as carteiras disponíveis para este banco
     *
     * @var array
     */
    protected $carteiras = [ '21' ];

    /**
     * Define a espécie do documento
     * @var string
     */
    protected $especieDoc = "DM";

    /**
     * Método onde o Boleto deverá gerar o Nosso Número.
     *
     * @return string
     */
    protected function gerarNossoNumero()
    {
        $quantidadeCaracteresNossoNumero = 11;
        $digitoVerificador = CalculoDV::unicredNossoNumero($this->numeroDocumento);
        return Util::numberFormatGeral($this->numeroDocumento . $digitoVerificador, $quantidadeCaracteresNossoNumero);
    }

    /**
     * Método que retorna o nosso numero usado no boleto. alguns bancos possuem algumas diferenças.
     *
     * @return string
     */
    public function getNossoNumeroBoleto()
    {
        return Util::maskString($this->getNossoNumero(), '##########-#');
    }

    /**
     * Método onde qualquer boleto deve extender para gerar o código da posição de 20 a 44
     *
     * @return string
     */
    public function getCampoLivre()
    {

        if ($this->campoLivre) {
            return $this->campoLivre;
        }

        $campoLivre = Util::numberFormatGeral($this->agencia, 4);
        $campoLivre .= Util::numberFormatGeral($this->conta . $this->contaDv, 10);
        $campoLivre .= $this->getNossoNumero();

        return $this->campoLivre = $campoLivre;
    }

    /**
     * Método onde qualquer boleto deve extender para gerar o código da posição de 20 a 44
     *
     * @param $campoLivre
     *
     * @return array
     */
    static public function parseCampoLivre($campoLivre)
    {
        return [
            'convenio' => null,
            'agenciaDv' => null,
            'codigoCliente' => null,
            'carteira' => null,
            'nossoNumero' => substr($campoLivre, 14, 10),
            'nossoNumeroDv' => substr($campoLivre, 24, 1),
            'nossoNumeroFull' => substr($campoLivre, 14),
            'agencia' => substr($campoLivre, 0, 4),
            'contaCorrente' => substr($campoLivre, 4, 10),
            'contaCorrenteDv' => null
        ];
    }

    /**
     * Retorna o codigo de barras
     *
     * @return string
     * @throws \Exception
     */
    public function getCodigoBarras()
    {
        if (!empty($this->campoCodigoBarras)) {
            return $this->campoCodigoBarras;
        }

        if (!$this->isValid($messages)) {
            throw new \Exception('Campos requeridos pelo banco, aparentam estar ausentes ' . $messages);
        }

        $codigo = Util::numberFormatGeral($this->getCodigoBanco(), 3)
            . $this->getMoeda()
            . Util::fatorVencimento($this->getDataVencimento())
            . Util::numberFormatGeral($this->getValor(), 10)
            . $this->getCampoLivre();

        $dv = CalculoDV::unicredCodigoBarra($codigo);
        return $this->campoCodigoBarras = substr($codigo, 0, 4) . $dv . substr($codigo, 4);
    }

    /**
     * @return int
     */
    public function getCip()
    {
        return $this->cip;
    }

    /**
     * @param int $cip
     */
    public function setCip($cip)
    {
        $this->cip = $cip;
    }
}