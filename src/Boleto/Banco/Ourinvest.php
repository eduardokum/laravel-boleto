<?php

namespace Eduardokum\LaravelBoleto\Boleto\Banco;

use Eduardokum\LaravelBoleto\Util;
use Eduardokum\LaravelBoleto\CalculoDV;
use Eduardokum\LaravelBoleto\Boleto\AbstractBoleto;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;

class Ourinvest extends AbstractBoleto implements BoletoContract
{
    /**
     * Código do banco
     * @var string
     */
    protected $codigoBanco = self::COD_BANCO_OURINVEST;

    /**
     * Define as carteiras disponíveis para este banco
     * @var array
     */
    protected $carteiras = false;

    /**
     * Espécie do documento, código para remessa do CNAB240
     * @var string
     */
    protected $especiesCodigo = [
        'DM' => '01', //Duplicata Mercantil
        'NP' => '02', //Nota Promissória
        'DS' => '12', //Duplicata de Serviço
        'O'  => '99',  //Outros,
    ];

    /**
     * Emissão do boleto por conta do beneficiário (true) por conta do banco (false)
     * @var bool
     */
    protected $emissaoPropria = true;

    /**
     * Linha de local de pagamento
     *
     * @var string
     */
    protected $localPagamento = 'Canais eletrônicos, agências ou correspondentes bancários de todo o BRASIL';

    /**
     * @return bool
     */
    public function isEmissaoPropria()
    {
        return $this->emissaoPropria;
    }

    /**
     * @param $emissaoPropria
     *
     * @return Ourinvest
     */
    public function setEmissaoPropria($emissaoPropria)
    {
        $this->emissaoPropria = $emissaoPropria;

        return $this;
    }

    /**
     * Gera o Nosso Número.
     *
     * @return string
     */
    protected function gerarNossoNumero()
    {
        return $this->isEmissaoPropria()
            ? Util::numberFormatGeral($this->getNumero(), 11) . CalculoDV::ourinvestNossoNumero($this->getCarteira(), $this->getNumero())
            : Util::numberFormatGeral(0, 12);
    }

    /**
     * Método para gerar o código da posição de 20 a 44
     *
     * @return string
     */
    protected function getCampoLivre()
    {
        if ($this->campoLivre) {
            return $this->campoLivre;
        }

        $campoLivre = Util::numberFormatGeral($this->getAgencia(), 4);
        $campoLivre .= Util::numberFormatGeral($this->getCarteira(), 2);
        $campoLivre .= Util::numberFormatGeral($this->getNossoNumero(), 11);
        $campoLivre .= Util::numberFormatGeral($this->getConta(), 7);
        $campoLivre .= '0';

        return $this->campoLivre = $campoLivre;
    }

    /**
     * Método onde qualquer boleto deve extender para gerar o código da posição de 20 a 44
     *
     * @param $campoLivre
     *
     * @return array
     */
    public static function parseCampoLivre($campoLivre)
    {
        return [
            'convenio'        => null,
            'parcela'         => null,
            'agenciaDv'       => null,
            'contaCorrente'   => substr($campoLivre, 16, 7),
            'modalidade'      => null,
            'contaCorrenteDv' => null,
            'nossoNumeroDv'   => substr($campoLivre, 15, 1),
            'agencia'         => substr($campoLivre, 0, 4),
            'nossa_carteira'  => substr($campoLivre, 4, 2),
            'codigoCliente'   => null,
            'nossoNumero'     => substr($campoLivre, 6, 10),
            'nossoNumeroFull' => substr($campoLivre, 6, 11),
        ];
    }

    /**
     * @return string
     */
    public function getAgenciaCodigoBeneficiario()
    {
        return sprintf(
            '%04s-%s / %07s-%s',
            $this->getAgencia(),
            ! is_null($this->getAgenciaDv()) ? $this->getAgenciaDv() : CalculoDV::ourinvestAgencia($this->getAgencia()),
            $this->getConta(),
            ! is_null($this->getContaDv()) ? $this->getContaDv() : CalculoDV::ourinvestConta($this->getConta()));
    }

    /**
     * @return bool
     */
    public function imprimeBoleto()
    {
        return $this->isEmissaoPropria();
    }
}
