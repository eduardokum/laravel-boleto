<?php

namespace Eduardokum\LaravelBoleto\Boleto\Banco;

use Eduardokum\LaravelBoleto\Util;
use Eduardokum\LaravelBoleto\CalculoDV;
use Eduardokum\LaravelBoleto\Boleto\AbstractBoleto;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;

class C6 extends AbstractBoleto implements BoletoContract
{
    public function __construct(array $params = [])
    {
        parent::__construct($params);
        $this->setCamposObrigatorios('codigoCliente');
    }

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
    protected $codigoBanco = self::COD_BANCO_C6;

    /**
     * Define as carteiras disponíveis para este banco
     *
     * @var array
     */
    protected $carteiras = ['10', '20', '30', '40', '60'];

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
        return Util::numberFormatGeral($this->getNumero(), 11) . CalculoDV::c6NossoNumero($this->getCarteira(), $this->getNumero());
    }

    /**
     * Método que retorna o nosso número usado no boleto. alguns bancos possuem algumas diferenças.
     *
     * @return string
     */
    public function getNossoNumeroBoleto()
    {
        return substr($this->getNossoNumero(), 1, 10);
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

        $campoLivre = Util::numberFormatGeral($this->getCodigoCliente(), 12);
        $campoLivre .= Util::numberFormatGeral(substr($this->getNossoNumero(), 1, 10), 10);
        $campoLivre .= Util::numberFormatGeral($this->getCarteira(), 2);
        $campoLivre .= in_array(((int) $this->getCarteira()), [10, 30, 60]) ? 3 : 4;

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
     * @return C6
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
