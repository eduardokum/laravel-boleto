<?php
namespace Eduardokum\LaravelBoleto\Boleto\Banco;

use Eduardokum\LaravelBoleto\Boleto\AbstractBoleto;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;
use Eduardokum\LaravelBoleto\Util;

class Hsbc  extends AbstractBoleto implements BoletoContract
{
    public function __construct(array $params = [])
    {
        parent::__construct($params);
        $this->addCampoObrigatorio('range', 'contaDv');
    }

    /**
     * Código do banco
     *
     * @var string
     */
    protected $codigoBanco = self::COD_BANCO_HSBC;
    /**
     * Define as carteiras disponíveis para este banco
     *
     * @var array
     */
    protected $carteiras = ['CSB'];
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
        'CE' => '09',
        'DS' => '10',
        'PD' => '98',
    ];
    /**
     * Código de range de composição do nosso numero.
     *
     * @var string
     */
    protected $range;
    /**
     * Espécie do documento, geralmente DM (Duplicata Mercantil)
     *
     * @var string
     */
    protected $especieDoc = 'PD';
    /**
     * @return string
     */
    public function getRange()
    {
        return $this->range;
    }
    /**
     * @param string $range
     *
     * @return Hsbc
     */
    public function setRange($range)
    {
        $this->range = $range;

        return $this;
    }
    /**
     * Define o campo Espécie Doc, HSBC sempre PD
     *
     * @param  string $especieDoc
     * @return AbstractBoleto
     */
    public function setEspecieDoc($especieDoc)
    {
        $this->especieDoc = 'PD';
        return $this;
    }
    /**
     * Retorna o campo Agência/Beneficiário do boleto
     *
     * @return string
     */
    public function getAgenciaCodigoBeneficiario()
    {
        $agencia = $this->getAgenciaDv() !== null ? $this->getAgencia() . '-' . $this->getAgenciaDv() : $this->getAgencia();

        if ($this->getContaDv() !== null && strlen($this->getContaDv()) == 1) {
            $conta = substr($this->getConta(), 0, -1) . '-' .substr($this->getConta(), -1).$this->getContaDv();
        } elseif ($this->getContaDv() !== null && strlen($this->getContaDv()) == 2) {
            $conta = substr($this->getConta(), 0, -1) . '-' .substr($this->getConta(), -1).$this->getContaDv();
        } else {
            $conta = $this->getConta();
        }

        return $agencia . ' / ' . $conta;
    }
    /**
     * Gera o Nosso Número.
     *
     * @return string
     */
    protected function gerarNossoNumero()
    {
        $range = Util::numberFormatGeral($this->getRange(), 5);
        $numero_boleto = Util::numberFormatGeral($this->getNumero(), 5);
        $dv = Util::modulo11($range . $numero_boleto, 2, 7);
        return $range . $numero_boleto . $dv;
    }
    /**
     * Método que retorna o nosso numero usado no boleto. alguns bancos possuem algumas diferenças.
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

        $ag = Util::numberFormatGeral($this->getAgencia(), 4);
        $cc = Util::numberFormatGeral($this->getConta(), 6);
        $agCc = $ag . $cc . ($this->getContaDv() ? $this->getContaDv() : Util::modulo11($ag . $cc));

        return $this->campoLivre = $this->getNossoNumero() .
            $agCc .
            '00' . // Codigo da carteira
            '1'; // Codigo do aplicativo
    }
}
