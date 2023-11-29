<?php
namespace Eduardokum\LaravelBoleto\Boleto\Banco;

use Eduardokum\LaravelBoleto\Boleto\AbstractBoleto;
use Eduardokum\LaravelBoleto\CalculoDV;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;
use Eduardokum\LaravelBoleto\Util;

class Cresol extends AbstractBoleto implements BoletoContract
{
    public function __construct(array $params = [])
    {
        parent::__construct($params);
        $this->setCamposObrigatorios('numero', 'convenio', 'carteira');
    }

    /**
     * Local de pagamento
     *
     * @var string
     */
    protected $localPagamento = 'Pagar preferencialmente na Rede Cresol';
    /**
     * Código do banco
     *
     * @var string
     */
    protected $codigoBanco = self::COD_BANCO_CRESOL;
    /**
     * Define as carteiras disponíveis para este banco
     *
     * @var array
     */
    protected $carteiras = ['09'];
    /**
     * Espécie do documento, coódigo para remessa
     *
     * @var string
     */
    protected $especiesCodigo240 = [
        'DM' => '02', // Duplicata Mercantil	        'DM' => '02', // Duplicata Mercantil
        'DS' => '04', // Duplicata de Serviço	        'DS' => '04', // Duplicata de Serviço
    ];
    /**
     * Espécie do documento, coódigo para remessa
     *
     * @var string
     */
    protected $especiesCodigo400 = [
        'DM' => '02', // Duplicata Mercantil	        'DM' => '02', // Duplicata Mercantil
        'DS' => '04', // Duplicata de Serviço	        'DS' => '04', // Duplicata de Serviço
    ];
    /**
     * Se possui registro o boleto (tipo = 1 com registro e 3 sem registro)
     *
     * @var bool
     */
    protected $registro = true;
    /**
     * Código do posto do cliente no banco.
     *
     * @var int
     */
    protected $posto;
    /**
     * Byte que compoe o nosso número.
     *
     * @var int
     */
    protected $byte = 2;
    /**
     * Código do cliente (é código do cedente, também chamado de código do beneficiário) é o código do emissor junto ao banco, geralmente é o próprio número da conta sem o dígito verificador. 
     * O código do cliente/cedente/beneficiário será diferente desse padrão em casos como quando um cliente bancário faz a migração da sua conta entre agências.
     *
     * @var string
     */
    protected $codigoCliente;

    /**
     * Define se possui ou não registro
     *
     * @param  bool $registro
     * @return $this
     */
    public function setComRegistro($registro)
    {
        $this->registro = $registro;
        return $this;
    }
    /**
     * Retorna se é com registro.
     *
     * @return bool
     */
    public function isComRegistro()
    {
        return $this->registro;
    }
    /**
     * Define o posto do cliente
     *
     * @param  int $posto
     * @return $this
     */
    public function setPosto($posto)
    {
        $this->posto = $posto;
        return $this;
    }
  
     /**
     * Seta o codigo do cliente.
     *
     * @param mixed $codigoCliente
     *
     * @return $this
     */
    public function setCodigoCliente($codigoCliente)
    {
        $this->codigoCliente = $codigoCliente;

        return $this;
    }
  
    /**
     * Retorna o codigo do cliente.
     *
     * @return string
     */
    public function getCodigoCliente()
    {
        return $this->codigoCliente;
    }
    /**
     * Retorna o campo Agência/Beneficiário do boleto
     *
     * @return string
     */
    public function getAgenciaCodigoBeneficiario()
    {
        $agencia = $this->getAgencia() . '-' . CalculoDV::bbAgencia($this->getAgencia());
        $codigoCliente = $this->getConvenio();

        return $agencia . ' / ' . $codigoCliente;
    }

    /**
     * Define o número do convênio. Sempre use string pois a quantidade de caracteres é validada.
     *
     * @param  string $convenio
     * @return Bb
     */
    public function setConvenio($convenio)
    {
        $this->convenio = $convenio;
        return $this;
    }
    /**
     * Retorna o número do convênio
     *
     * @return string
     */
    public function getConvenio()
    {
        return $this->convenio;
    }
    /**
     * Gera o Nosso Número.
     *
     * @return string
     */
    protected function gerarNossoNumero()
    {
        $numero_boleto = Util::numberFormatGeral($this->getNumero(), 11);
        $nossoNumero = $this->getCarteira() . $numero_boleto
            . CalculoDV::cresolNossoNumero($this->getCarteira(), $numero_boleto);
        return $nossoNumero;
    }
    /**
     * Método que retorna o nosso numero usado no boleto. alguns bancos possuem algumas diferenças.
     *
     * @return string
     */
    public function getNossoNumeroBoleto()
    {
        return Util::maskString($this->getNossoNumero(), '#############-#');
    }
    /**
     * Método para gerar o código da posição de 20 a 44
     *
     * @return string
     * @throws \Exception
     */
    protected function getCampoLivre()
    {
        if ($this->campoLivre) {
            return $this->campoLivre;
        }

        $campoLivre = $this->isComRegistro() ? '1' : '3';
        $campoLivre .= Util::numberFormatGeral($this->getCarteira(), 1);
        $campoLivre .= $this->getNossoNumero();
        $campoLivre .= Util::numberFormatGeral($this->getAgencia(), 4);
        $campoLivre .= Util::numberFormatGeral($this->getCodigoCliente(), 5);
        $campoLivre .= '10';
        $campoLivre .= Util::modulo11($campoLivre);

        return $this->campoLivre .= $campoLivre;
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
            'agenciaDv' => null,
            'contaCorrenteDv' => null,
            'codigoCliente' => substr($campoLivre, 17, 5),
            'carteira' => substr($campoLivre, 1, 1),
            'nossoNumero' => substr($campoLivre, 2, 8),
            'nossoNumeroDv' => substr($campoLivre, 10, 1),
            'nossoNumeroFull' => substr($campoLivre, 2, 9),
            'agencia' => substr($campoLivre, 11, 4),
            //'contaCorrente' => substr($campoLivre, 17, 5),
        ];
    }
}
