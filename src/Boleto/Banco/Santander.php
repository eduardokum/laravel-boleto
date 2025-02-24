<?php

namespace Eduardokum\LaravelBoleto\Boleto\Banco;

use Eduardokum\LaravelBoleto\Util;
use Eduardokum\LaravelBoleto\CalculoDV;
use Eduardokum\LaravelBoleto\Boleto\AbstractBoleto;
use Eduardokum\LaravelBoleto\Exception\ValidationException;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;

class Santander extends AbstractBoleto implements BoletoContract
{
    public function __construct(array $params = [])
    {
        parent::__construct($params);
        $this->setCamposObrigatorios('numero', 'codigoCliente', 'carteira');
    }

    /**
     * Código do banco
     *
     * @var string
     */
    protected $codigoBanco = self::COD_BANCO_SANTANDER;

    /**
     * Define as carteiras disponíveis para este banco
     *
     * @var array
     */
    protected $carteiras = ['101', '201'];

    /**
     * Espécie do documento, código para remessa 240
     *
     * @var string
     */
    protected $especiesCodigo240 = [
        'DM'  => '02',
        'DS'  => '04',
        'LC'  => '07',
        'NP'  => '12',
        'NR'  => '13',
        'RC'  => '17',
        'AP'  => '20',
        'BCC' => '31',
        'BDP' => '32',
        'CH'  => '97',
        'ND'  => '98',
    ];

    /**
     * Espécie do documento, código para remessa 400
     *
     * @var string
     */
    protected $especiesCodigo400 = [
        'DM'  => '01',
        'NP'  => '02',
        'AP'  => '03',
        'RC'  => '05',
        'DP'  => '06',
        'LC'  => '07',
        'BDP' => '08',
        'BCC' => '19',
    ];

    /**
     * Mostrar o endereço do beneficiário abaixo da razão e CNPJ na ficha de compensação
     *
     * @var bool
     */
    protected $mostrarEnderecoFichaCompensacao = true;

    /**
     * Define os nomes das carteiras para exibição no boleto
     *
     * @var array
     */
    protected $carteirasNomes = [
        '101' => 'Cobrança Simples ECR',
        '102' => 'Cobrança Simples CSR',
        '201' => 'Penhor',
    ];

    /**
     * Define o valor do IOS - Seguradoras (Se 7% informar 7. Limitado a 9%) - Demais clientes usar 0 (zero)
     *
     * @var int
     */
    protected $ios = 0;

    /**
     * Variaveis adicionais.
     *
     * @var array
     */
    public $variaveis_adicionais = [
        'esconde_uso_banco' => true,
    ];

    /**
     * Código do cliente.
     *
     * @var int
     */
    protected $codigoCliente;

    /**
     * Retorna o campo Agência/Beneficiário do boleto
     *
     * @return string
     */
    public function getAgenciaCodigoBeneficiario()
    {
        $agencia = rtrim(sprintf('%s-%s', $this->getAgencia(), $this->getAgenciaDv()), '-');

        return sprintf('%s / %s', $agencia, $this->getCodigoCliente());
    }

    /**
     * Retorna o código da carteira
     * @return string
     */
    public function getCarteiraNumero()
    {
        switch ($this->carteira) {
            case '101':
                $carteira = '5';
                break;
            case '201':
                $carteira = '1';
                break;
            default:
                $carteira = $this->carteira;
                break;
        }

        return $carteira;
    }

    /**
     * Retorna o código do cliente.
     *
     * @return int
     */
    public function getCodigoCliente()
    {
        return $this->codigoCliente;
    }

    /**
     * Define o código do cliente.
     *
     * @param int $codigoCliente
     *
     * @return AbstractBoleto
     */
    public function setCodigoCliente($codigoCliente)
    {
        $this->codigoCliente = $codigoCliente;

        return $this;
    }

    /**
     * Define o código da carteira (Com ou sem registro)
     *
     * @param string $carteira
     * @return AbstractBoleto
     * @throws ValidationException
     */
    public function setCarteira($carteira)
    {
        switch ($carteira) {
            case '1':
            case '5':
                $carteira = '101';
                break;
            case '4':
                $carteira = '102';
                break;
        }

        return parent::setCarteira($carteira);
    }

    /**
     * Define o valor do IOS
     *
     * @param int $ios
     */
    public function setIos($ios)
    {
        $this->ios = $ios;
    }

    /**
     * Retorna o atual valor do IOS
     *
     * @return int
     */
    public function getIos()
    {
        return $this->ios;
    }

    /**
     * Seta dia para baixa automática
     *
     * @param int $baixaAutomatica
     *
     * @return Santander
     * @throws ValidationException
     */
    public function setDiasBaixaAutomatica($baixaAutomatica)
    {
        if ($this->getDiasProtesto() > 0) {
            throw new ValidationException('Você deve usar dias de protesto ou dias de baixa, nunca os 2');
        }
        if (! in_array($baixaAutomatica, [15, 30])) {
            throw new ValidationException('O Banco Santander so aceita 15 ou 30 dias após o vencimento para baixa automática');
        }
        $baixaAutomatica = (int) $baixaAutomatica;
        $this->diasBaixaAutomatica = $baixaAutomatica > 0 ? $baixaAutomatica : 0;

        return $this;
    }

    /**
     * Gera o Nosso Número.
     *
     * @return string
     */
    protected function gerarNossoNumero()
    {
        return Util::numberFormatGeral($this->getNumero(), 12)
            . CalculoDV::santanderNossoNumero($this->getNumero());
    }

    /**
     * Método que retorna o nosso numero usado no boleto. alguns bancos possuem algumas diferenças.
     *
     * @return string
     */
    public function getNossoNumeroBoleto()
    {
        return substr($this->getNossoNumero(), 0, -1) . '-' . substr($this->getNossoNumero(), -1);
    }

    /**
     * @param $id
     * @return string
     * @throws ValidationException
     */
    protected function validateId($id)
    {
        if (! preg_match('/^[a-zA-Z0-9]{25,36}$/', $id)) {
            throw new ValidationException('ID/TXID do boleto é inválido, Os caracteres aceitos neste contexto são: A-Z, a-z, 0-9, não pode conter brancos e nulos, com o mínimo de 26 caracteres e no máximo 35 caracteres');
        }

        return $id;
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

        return $this->campoLivre = '9' . Util::numberFormatGeral($this->getCodigoCliente(), 7)
            . Util::numberFormatGeral($this->getNossoNumero(), 13)
            . Util::numberFormatGeral($this->getIos(), 1)
            . Util::numberFormatGeral($this->getCarteira(), 3);
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
            'agencia'         => null,
            'agenciaDv'       => null,
            'contaCorrente'   => null,
            'contaCorrenteDv' => null,
            'codigoCliente'   => substr($campoLivre, 1, 7),
            'nossoNumero'     => substr($campoLivre, 8, 12),
            'nossoNumeroDv'   => substr($campoLivre, 20, 1),
            'nossoNumeroFull' => substr($campoLivre, 8, 13),
            'carteira'        => substr($campoLivre, 22, 3),
        ];
    }
}
