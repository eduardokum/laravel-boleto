<?php
namespace Eduardokum\LaravelBoleto\Boleto\Banco;

use Eduardokum\LaravelBoleto\Boleto\AbstractBoleto;
use Eduardokum\LaravelBoleto\CalculoDV;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;
use Eduardokum\LaravelBoleto\Util;

class Santander  extends AbstractBoleto implements BoletoContract
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
     * Espécie do documento, coódigo para remessa
     *
     * @var string
     */
    protected $especiesCodigo = [
        'DM' => '02',
        'DS' => '04',
        'LC' => '07',
        'NP' => '12',
        'NR' => '13',
        'RC' => '17',
        'AP' => '20',
        'BCC'=> '31',
        'BDP'=> '32',
        'CH' => '97',
        'ND' => '98'
    ];
    /**
     * Define os nomes das carteiras para exibição no boleto
     *
     * @var array
     */
    protected $carteirasNomes = [
        '101' => 'Cobrança Simples ECR',
        '102' => 'Cobrança Simples CSR',
        '201' => 'Penhor'
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
        $agencia = $this->getAgenciaDv() !== null ? $this->getAgencia() . '-' . $this->getAgenciaDv() : $this->getAgencia();
        $codigoCliente = $this->getCodigoCliente();

        return $agencia . ' / ' . $codigoCliente;
    }

    /**
     * Retorna o código da carteira
     * @return string
     */
    public function getCarteiraNumero(){
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
     * @param  string $carteira
     * @return AbstractBoleto
     * @throws \Exception
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
     * Seta dias para baixa automática
     *
     * @param int $baixaAutomatica
     *
     * @return $this
     * @throws \Exception
     */
    public function setDiasBaixaAutomatica($baixaAutomatica)
    {
        if ($this->getDiasProtesto() > 0) {
            throw new \Exception('Você deve usar dias de protesto ou dias de baixa, nunca os 2');
        }
        if (!in_array($baixaAutomatica, [15, 30])) {
            throw new \Exception('O Banco Santander so aceita 15 ou 30 dias após o vencimento para baixa automática');
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
        $numero_boleto = $this->getNumero();
        return Util::numberFormatGeral($numero_boleto, 12)
            . CalculoDV::santanderNossoNumero($numero_boleto);
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
    public static function parseCampoLivre($campoLivre) {
        return [
            'convenio' => null,
            'agencia' => null,
            'agenciaDv' => null,
            'contaCorrente' => null,
            'contaCorrenteDv' => null,
            'codigoCliente' => substr($campoLivre, 1, 7),
            'nossoNumero' => substr($campoLivre, 8, 12),
            'nossoNumeroDv' => substr($campoLivre, 20, 1),
            'nossoNumeroFull' => substr($campoLivre, 8, 13),
            'carteira' => substr($campoLivre, 22, 3),
        ];
    }
}
