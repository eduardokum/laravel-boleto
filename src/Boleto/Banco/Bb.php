<?php
namespace Eduardokum\LaravelBoleto\Boleto\Banco;

use Eduardokum\LaravelBoleto\Boleto\AbstractBoleto;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;
use Eduardokum\LaravelBoleto\Util;

class Bb extends AbstractBoleto implements BoletoContract
{
    public function __construct(array $params = [])
    {
        parent::__construct($params);
        $this->setCamposObrigatorios('numero', 'convenio', 'carteira');
    }
    /**
     * Código do banco
     *
     * @var string
     */
    protected $codigoBanco = self::COD_BANCO_BB;
    /**
     * Define as carteiras disponíveis para este banco
     *
     * @var array
     */
    protected $carteiras = array('11', '12', '15', '16', '17', '18', '31', '51');
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
        'LC' => '08',
        'W' => '09',
        'CH' => '10',
        'DS' => '12',
        'ND' => '13',
    ];
    /**
     * Define o número do convênio (4, 6 ou 7 caracteres)
     *
     * @var string
     */
    protected $convenio;
    /**
     * Defgine o numero da variação da carteira.
     *
     * @var string
     */
    protected $variacao_carteira;
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
     * Define o número da variação da carteira, para saber quando utilizar o nosso numero de 17 posições.
     *
     * @param  string $variacao_carteira
     * @return Bb
     */
    public function setVariacaoCarteira($variacao_carteira)
    {
        $this->variacao_carteira = $variacao_carteira;
        return $this;
    }
    /**
     * Retorna o número da variacao de carteira
     *
     * @return string
     */
    public function getVariacaoCarteira()
    {
        return $this->variacao_carteira;
    }
    /**
     * Gera o Nosso Número.
     *
     * @throws \Exception
     * @return string
     */
    protected function gerarNossoNumero()
    {
        $convenio = $this->getConvenio();
        $numero_boleto = $this->getNumero();
        switch (strlen($convenio)) {
        case 4:
            $numero = Util::numberFormatGeral($convenio, 4) . Util::numberFormatGeral($numero_boleto, 7);
            break;
        case 6:
            if (in_array($this->getCarteira(), ['16', '18']) && $this->getVariacaoCarteira() == 17) {
                $numero = Util::numberFormatGeral($numero_boleto, 17);
            } else {
                $numero = Util::numberFormatGeral($convenio, 6) . Util::numberFormatGeral($numero_boleto, 5);
            }
            break;
        case 7:
            $numero = Util::numberFormatGeral($convenio, 7) . Util::numberFormatGeral($numero_boleto, 10);
            break;
        default:
            throw new \Exception('O código do convênio precisa ter 4, 6 ou 7 dígitos!');
        }
        return $numero;
    }
    /**
     * Método que retorna o nosso numero usado no boleto. alguns bancos possuem algumas diferenças.
     *
     * @return string
     */
    public function getNossoNumeroBoleto()
    {
        $nn = $this->getNossoNumero();
        return strlen($nn) < 17 ? $nn . '-' . Util::modulo11($nn) : $nn;
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
        $length = strlen($this->getConvenio());
        $nossoNumero = $this->gerarNossoNumero();
        if (strlen($this->getNumero()) > 10) {
            if ($length == 6 && in_array($this->getCarteira(), ['16', '18']) && Util::numberFormatGeral($this->getVariacaoCarteira(), 3) == '017') {
                return $this->campoLivre = Util::numberFormatGeral($this->getConvenio(), 6) . $nossoNumero . '21';
            } else {
                throw new \Exception('Só é possível criar um boleto com mais de 10 dígitos no nosso número quando a carteira é 21 e o convênio possuir 6 dígitos.');
            }
        }
        switch ($length) {
        case 4:
        case 6:
            return $this->campoLivre = $nossoNumero . Util::numberFormatGeral($this->getAgencia(), 4) . Util::numberFormatGeral($this->getConta(), 8) . Util::numberFormatGeral($this->getCarteira(), 2);
        case 7:
            return $this->campoLivre = '000000' . $nossoNumero . Util::numberFormatGeral($this->getCarteira(), 2);
        }
        throw new \Exception('O código do convênio precisa ter 4, 6 ou 7 dígitos!');
    }
}
