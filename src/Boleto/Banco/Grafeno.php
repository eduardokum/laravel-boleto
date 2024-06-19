<?php

namespace Eduardokum\LaravelBoleto\Boleto\Banco;

use Eduardokum\LaravelBoleto\Util;
use Eduardokum\LaravelBoleto\CalculoDV;
use Eduardokum\LaravelBoleto\Boleto\AbstractBoleto;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto;
use Eduardokum\LaravelBoleto\Exception\ValidationException;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;

class Grafeno extends AbstractBoleto implements BoletoContract
{
    /**
     * Código do banco
     *
     * @var string
     */
    protected $codigoBanco = Boleto::COD_BANCO_GRAFENO;

    /**
     * Define as carteiras disponíveis para este banco
     * '1' => Com registro | '2' => Simples | '3' => Escritural
     *
     * @var array
     */
    protected $carteiras = [1, 2, 3];

    /**
     * Espécie do documento, coódigo para remessa
     *
     * @var string
     */
    protected $especiesCodigo = [
        'DM'  => '01', // Duplicata Mercantil
        'NP'  => '02', // Nota Promissória
        'NS'  => '03', // Nota de Seguro
        'CS'  => '04', // Cobrança Seriada
        'RC'  => '05', // Recibo
        'LC'  => '10', // Letra de Câmbio
        'ND'  => '11', // Nota de Débito
        'DS'  => '12', // Duplicata de Serviço
        'CC'  => '31', // Cartão de Crédito
        'BDP' => '32', // Boleto de Proposta
        'O'   => '99', // Outros
    ];

    /**
     * Código de range de composição do nosso número.
     *
     * @var int
     */
    protected $range = 0;

    /**
     * @return int
     */
    public function getRange()
    {
        return $this->range;
    }

    /**
     * @param int $range
     *
     * @return Grafeno
     */
    public function setRange($range)
    {
        $this->range = (int) $range;

        return $this;
    }

    /**
     * Retorna o número definido pelo cliente para compor o nosso número
     *
     * @return int
     */
    public function getNumero()
    {
        return $this->numero < $this->getRange() ? $this->getRange() + $this->numero : $this->numero;
    }

    /**
     * Gera o Nosso Número.
     *
     * @return string
     */
    protected function gerarNossoNumero()
    {
        $nn = $this->getNumero();
        $dv = CalculoDV::grafenoNossoNumero($this->getCarteira(), $nn);

        return Util::numberFormatGeral($nn, 11) . $dv;
    }

    /**
     * Seta dia para baixa automática
     *
     * @param int $baixaAutomatica
     *
     * @return Grafeno
     * @throws ValidationException
     */
    public function setDiasBaixaAutomatica($baixaAutomatica)
    {
        if ($this->getDiasProtesto() > 0) {
            throw new ValidationException('Você deve usar dias de protesto ou dias de baixa, nunca os 2');
        }
        $baixaAutomatica = (int) $baixaAutomatica;
        $this->diasBaixaAutomatica = $baixaAutomatica > 0 ? $baixaAutomatica : 0;

        return $this;
    }

    /**
     * Método que retorna o nosso número usado no boleto. Alguns bancos possuem algumas diferenças.
     *
     * @return string
     */
    public function getNossoNumeroBoleto()
    {
        return substr_replace($this->getNossoNumero(), '-', -1, 0);
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
        $campoLivre .= Util::numberFormatGeral($this->getNumero(), 11);
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
            'agenciaDv'       => null,
            'contaCorrenteDv' => null,
            'agencia'         => substr($campoLivre, 0, 4),
            'carteira'        => substr($campoLivre, 4, 2),
            'nossoNumero'     => substr($campoLivre, 6, 11),
            'nossoNumeroDv'   => null,
            'nossoNumeroFull' => substr($campoLivre, 6, 11),
            'contaCorrente'   => substr($campoLivre, 17, 7),
        ];
    }
}
