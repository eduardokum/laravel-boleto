<?php

namespace Eduardokum\LaravelBoleto\Boleto\Banco;


use Eduardokum\LaravelBoleto\Boleto\AbstractBoleto;
use Eduardokum\LaravelBoleto\CalculoDV;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;
use Eduardokum\LaravelBoleto\Util;


class Unicred extends AbstractBoleto implements BoletoContract
{

    protected $codigoBanco = self::COD_BANCO_UNICRED;


    /**
     * Local de pagamento
     *
     * @var string
     */
    protected $localPagamento = 'Pagável Preferencialmente na Unicred';

    /**
     * Variáveis adicionais.
     *
     * @var array
     */
    public $variaveis_adicionais = [
        'carteira_nome' => '',
    ];
    /**
     * Define as carteiras disponíveis para este banco
     *
     * @var array
     */
    protected $carteiras = ['112', '115', '188', '109', '121', '180', '110', '111'];
    /**
     * Espécie do documento, coódigo para remessa
     *
     * @var string
     */
    protected $especiesCodigo = [
        "Duplicata Mercantil" => "DM"
    ];

    /**
     * Método onde o Boleto deverá gerar o Nosso Número.
     *
     * @return string
     */
    protected function gerarNossoNumero()
    {
        $quantidadeCaracteresNossoNumero = 11;
        $digitoVerificador = CalculoDV::unicredNossoNumero(Util::numberFormatGeral($this->numeroDocumento, 10));
        return Util::numberFormatGeral($this->numeroDocumento . $digitoVerificador, $quantidadeCaracteresNossoNumero);
    }

    /**
     * Método onde qualquer boleto deve extender para gerar o código da posição de 20 a 44
     *
     * @return string
     */
    protected function getCampoLivre()
    {

        if ($this->campoLivre) {
            return $this->campoLivre;
        }

        $campoLivre = Util::numberFormatGeral($this->agencia, 4);
        $campoLivre .= Util::numberFormatGeral($this->conta, 10);
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
            'nossoNumero' => substr($campoLivre, 15, 9),
            'nossoNumeroDv' => substr($campoLivre, 24, 1),
            'nossoNumeroFull' => substr($campoLivre, 15, 10),
            'agencia' => substr($campoLivre, 0, 4),
            'contaCorrente' => substr($campoLivre, 5, 10),
            'contaCorrenteDv' => null
        ];
    }
}