<?php
namespace Eduardokum\LaravelBoleto\Boleto\Banco;

use Eduardokum\LaravelBoleto\Boleto\AbstractBoleto;
use Eduardokum\LaravelBoleto\CalculoDV;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;
use Eduardokum\LaravelBoleto\Util;

class Bnb extends AbstractBoleto implements BoletoContract
{
    /**
     * Local de pagamento
     *
     * @var string
     */
    protected $localPagamento = 'PAGÁVEL EM QUALQUER AGÊNCIA BANCÁRIA ATÉ O VENCIMENTO';

    /**
     * Código do banco
     *
     * @var string
     */
    protected $codigoBanco = self::COD_BANCO_BNB;
    /**
     * Variáveis adicionais.
     *
     * @var array
     */
    public $variaveis_adicionais = [
        'carteira_nome' => '',
    ];
    /**
     * Define as carteiras disponíveis para este banco
     *
     * @var array
     */
    protected $carteiras = ['21'];
    /**
     * Espécie do documento, coódigo para remessa
     *
     * @var string
     */
    protected $especiesCodigo = [
        'DM' => '01',
        'NP' => '02',
        'CH' => '03',
        'CN' => '04',
        'RC' => '05'
    ];
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
        $baixaAutomatica = (int) $baixaAutomatica;
        $this->diasBaixaAutomatica = $baixaAutomatica > 0 ? $baixaAutomatica : 0;
        return $this;
    }

    /**
     * Gera o Nosso Número.
     *
     * @return string
     * @throws \Exception
     */
    protected function gerarNossoNumero()
    {
        $numero_boleto = $this->getNumero();
        return Util::numberFormatGeral($numero_boleto, 7) . CalculoDV::bnbNossoNumero($this->getNumero());
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
     * @throws \Exception
     */
    protected function getCampoLivre()
    {
        if ($this->campoLivre) {
            return $this->campoLivre;
        }
        $nosso_numero = $this->getNossoNumero();
        $carteira = Util::numberFormatGeral($this->getCarteira(), 2);
        $agencia = Util::numberFormatGeral($this->getAgencia(), 4);
        $conta = Util::numberFormatGeral($this->getConta(), 7);
        $dvContaCedente = $this->getContaDv() ?: CalculoDV::bnbContaCorrente($this->getAgencia(), $this->getConta());

        return $this->campoLivre = $agencia . $conta . $dvContaCedente . $nosso_numero . $carteira . '000';
    }
}
