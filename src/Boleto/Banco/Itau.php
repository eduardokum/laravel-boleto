<?php
namespace Eduardokum\LaravelBoleto\Boleto\Banco;

use Eduardokum\LaravelBoleto\Boleto\AbstractBoleto;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;
use Eduardokum\LaravelBoleto\Util;

class Itau extends AbstractBoleto implements BoletoContract
{
    /**
     * Código do banco
     *
     * @var string
     */
    protected $codigoBanco = self::COD_BANCO_ITAU;
    /**
     * Variáveis adicionais.
     *
     * @var array
     */
    public $variaveis_adicionais = [
        'carteira' => '',
    ];
    /**
     * Define as carteiras disponíveis para este banco
     *
     * @var array
     */
    protected $carteiras = ['112', '115', '188', '109', '121', '180', '175'];
    /**
     * Espécie do documento, coódigo para remessa
     *
     * @var string
     */
    protected $especiesCodigo = [
        'DM' => '01',
        'NP' => '02',
        'NS' => '03',
        'REC' => '05',
        'CT' => '06',
        'CS' => '07',
        'DS' => '08',
        'LC' => '09',
        'ND' => '13',
        'CDA' => '15',
        'EC' => '16',
        'CPS' => '17',
    ];
    /**
     * Campo obrigatório para emissão de boletos com carteira 198 fornecido pelo Banco com 5 dígitos
     *
     * @var int
     */
    protected $codigoCliente;
    /**
     * Dígito verificador da carteira/nosso número para impressão no boleto
     *
     * @var int
     */
    protected $carteiraDv;
    /**
     * Define o código do cliente
     *
     * @param  int $codigoCliente
     * @return $this
     */
    public function setCodigoCliente($codigoCliente)
    {
        $this->codigoCliente = $codigoCliente;
        return $this;
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
        if($this->getDiasProtesto() > 0) {
            throw new \Exception('Você deve usar dias de protesto ou dias de baixa, nunca os 2');
        }
        $baixaAutomatica = (int) $baixaAutomatica;
        $this->diasBaixaAutomatica = $baixaAutomatica > 0 ? $baixaAutomatica : 0;
        return $this;
    }

    /**
     * Retorna o código do cliente
     *
     * @return int
     */
    public function getCodigoCliente()
    {
        return $this->codigoCliente;
    }
    /**
     * Método que valida se o banco tem todos os campos obrigadotorios preenchidos
     */
    public function isValid()
    {
        if ((in_array($this->getCarteira(), ['107', '122', '142', '143', '196', '198']) && $this->codigoCliente == '') || !parent::isValid()) {
            return false;
        }
        return true;
    }

    /**
     * Gera o Nosso Número.
     *
     * @return string
     * @throws \Exception
     */
    protected function gerarNossoNumero()
    {
        $this->getCampoLivre(); // Força o calculo do DV.
        return Util::numberFormatGeral($this->getNumero(), 8) . $this->carteiraDv;
    }
    /**
     * Método que retorna o nosso numero usado no boleto. alguns bancos possuem algumas diferenças.
     *
     * @return string
     */
    public function getNossoNumeroBoleto()
    {
        return $this->getCarteira() . '/' . substr_replace($this->getNossoNumero(), '-', -1, 0);
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
        $numero_boleto = Util::numberFormatGeral($this->getNumero(), 8);
        $carteira = Util::numberFormatGeral($this->getCarteira(), 3);
        $this->carteiraDv = $dvAgContaCarteira = Util::modulo10($carteira . $numero_boleto);
        $agencia = Util::numberFormatGeral($this->getAgencia(), 4);
        $conta = Util::numberFormatGeral($this->getConta(), 5);
        // Módulo 10 Agência/Conta
        $dvAgConta = Util::modulo10($agencia . $conta);
        return $this->campoLivre = $carteira . $numero_boleto . $dvAgContaCarteira . $agencia . $conta . $dvAgConta . '000';
    }
}