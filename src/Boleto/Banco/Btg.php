<?php

namespace Eduardokum\LaravelBoleto\Boleto\Banco;

use Eduardokum\LaravelBoleto\Util;
use Eduardokum\LaravelBoleto\CalculoDV;
use Eduardokum\LaravelBoleto\Boleto\AbstractBoleto;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;

class Btg extends AbstractBoleto implements BoletoContract
{
    /**
     * Local de pagamento
     *
     * @var string
     */
    protected $localPagamento = 'Pagável em canais eletrônicos, agências ou correspondentes';

    /**
     * Código do banco
     *
     * @var string
     */
    protected $codigoBanco = self::COD_BANCO_BTG;

    /**
     * Define as carteiras disponíveis para este banco
     * '1' = Cobrança Simples
     * '2' = Cobrança Vinculada
     * '3' = Cobrança Caucionada
     * '4' = Cobrança Descontada
     * '5' = Cobrança Vendor
     * '6' = Cobrança Cessão
     *
     * @var array
     */
    protected $carteiras = [1, 2, 3, 4, 5, 6];

    /**
     * Espécie do documento, coódigo para remessa
     *
     * @var string
     */
    protected $especiesCodigo = [
        'DM'  => '01',
        'DS'  => '02',
        'NP'  => '03',
        'NS'  => '04',
        'REC' => '05',
        'LC'  => '06',
        'FC'  => '07',
        'CAR' => '08',
        'CT'  => '09',
        'ME'  => '12',
        'ND'  => '13',
        'CDA' => '15',
        'EC'  => '16',
        'CPS' => '17',
        'FT'  => '31',
        'BA'  => '33',
        'O'   => '99',
    ];

    /**
     * Codigo do cliente junto ao banco.
     *
     * @var string
     */
    protected $codigoCliente;

    /**
     * Gera o Nosso Número.
     *
     * @return string
     */
    protected function gerarNossoNumero()
    {
        return Util::numberFormatGeral($this->getNumero(), 11) . CalculoDV::btgNossoNumero($this->getCarteira(), $this->getNumero());
    }

    /**
     * Método que retorna o nosso número usado no boleto. alguns bancos possuem algumas diferenças.
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
        $campoLivre .= Util::numberFormatGeral(substr($this->getNossoNumero(), -11), 11);
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
            'codigoCliente'   => substr($campoLivre, 0, 12),
            'carteira'        => substr($campoLivre, 22, 2),
            'nossoNumero'     => substr($campoLivre, 12, 10),
            'nossoNumeroDv'   => null,
            'nossoNumeroFull' => substr($campoLivre, 12, 10),
            'agencia'         => null,
            'contaCorrente'   => null,
            'contaCorrenteDv' => null,
        ];
    }

    /**
     * Retorna o codigo do cliente.
     *
     * @return mixed
     */
    public function getCodigoCliente()
    {
        return $this->codigoCliente;
    }

    /**
     * Seta o codigo do cliente.
     *
     * @param mixed $codigoCliente
     *
     * @return Btg
     */
    public function setCodigoCliente($codigoCliente)
    {
        $this->codigoCliente = $codigoCliente;

        return $this;
    }

    /**
     * @return string
     */
    public function getAgenciaCodigoBeneficiario()
    {
        return sprintf('%04s/%012s', $this->getAgencia() ?: 1, $this->getCodigoCliente());
    }
}
