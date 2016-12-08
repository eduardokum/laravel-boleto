<?php
namespace Eduardokum\LaravelBoleto\Boleto\Banco;

use Eduardokum\LaravelBoleto\Boleto\AbstractBoleto;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;
use Eduardokum\LaravelBoleto\Util;

class Itau extends AbstractBoleto implements BoletoContract
{
    /**
     * Código do banco
     * @var string
     */
    protected $codigoBanco = self::COD_BANCO_ITAU;
    /**
     * Variáveis adicionais.
     * @var array
     */
    public $variaveis_adicionais = [
        'carteira' => '',
    ];
    /**
     * Define as carteiras disponíveis para este banco
     * @var array
     */
    protected $carteiras = ['112', '115', '188', '109', '121', '180', '175'];
    /**
     * Espécie do documento, coódigo para remessa
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
     * @var int
     */
    protected $codigoCliente;
    /**
     * Dígito verificador da carteira/nosso número para impressão no boleto
     * @var int
     */
    protected $carteiraDv;
    /**
     * Define o código do cliente
     *
     * @param int $codigoCliente
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
        $this->diasProtesto = $baixaAutomatica > 0 ? $baixaAutomatica : 0;
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
        if ((in_array($this->getCarteira(), ['107', '122', '142', '143', '196', '198']) && $this->codigoCliente == '') || !parent::isValid())
        {
            return false;
        }
        return true;
    }
    /**
     * Gera o Nosso Número.
     *
     * @return string
     */
    protected function gerarNossoNumero()
    {
        $this->getCampoLivre(); // Força o calculo do DV.
        return Util::numberFormatGeral($this->getNumeroDocumento(), 8) . $this->carteiraDv;
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

        $numero = Util::numberFormatGeral($this->getNumeroDocumento(), 8);
        $carteira = Util::numberFormatGeral($this->getCarteira(), 3);
        $agencia = Util::numberFormatGeral($this->getAgencia(), 4);
        $conta = Util::numberFormatGeral($this->getConta(), 5);
        // Carteira 198 - (Nosso Número com 15 posições) - Anexo 5 do manual
        if (in_array($this->getCarteira(), ['107', '122', '142', '143', '196', '198'])) {
            $codigo = $carteira . $numero .
                Util::numberFormatGeral($this->getNumeroDocumento(), 7) .
                Util::numberFormatGeral($this->getCodigoCliente(), 5);
            // Define o DV da carteira para a view
            $this->carteiraDv = $modulo = Util::modulo10($codigo);
            return $this->campoLivre = $codigo . $modulo . '0';
        }
        // Geração do DAC - Anexo 4 do manual
        if (!in_array($this->getCarteira(), ['126', '131', '146', '150', '168'])) {
            // Define o DV da carteira para a view
            $this->carteiraDv = $dvAgContaCarteira = Util::modulo10($agencia . $conta . $carteira . $numero);
        } else {
            // Define o DV da carteira para a view
            $this->carteiraDv = $dvAgContaCarteira = Util::modulo10($carteira . $numero);
        }
        // Módulo 10 Agência/Conta
        $dvAgConta = Util::modulo10($agencia . $conta);
        return $this->campoLivre = $carteira . $numero . $dvAgContaCarteira . $agencia . $conta . $dvAgConta . '000';
    }
}