<?php

namespace Eduardokum\LaravelBoleto\Boleto\Banco;

use Eduardokum\LaravelBoleto\Util;
use Eduardokum\LaravelBoleto\CalculoDV;
use Eduardokum\LaravelBoleto\Boleto\AbstractBoleto;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;

class Abc extends AbstractBoleto implements BoletoContract
{
    public function __construct(array $params = [])
    {
        parent::__construct($params);
        $this->setCamposObrigatorios('numero', 'agencia', 'carteira', 'operacao');
    }

    /**
     * Código do banco
     *
     * @var string
     */
    protected $codigoBanco = Boleto::COD_BANCO_ABC;

    /**
     * Define as carteiras disponíveis para este banco
     * 1 - Cobrança Normal com emissão de bloquetes, pelo banco. Ordem para Classificação: Banco preferencial do Cedente, nosso banco e nossos correspondentes, segundo ordem de escolha, ditada pelo nosso banco.
     * 2 - Ordem para Classificação: Banco preferencial do Cedente, nossos correspondentes, nosso banco.
     * 3 - Cobrança com determinação do Cobrador nas posições 140 – 142, (que não é o Nosso Banco).
     * 4 - O código do Banco cobrador (que não é o Nosso Banco) deve vir nas posições 140-142;. Nosso Número do Banco cobrador deve vir nas posições 74 a 86. Nesta carteira, o cliente envia o título para o Nosso Banco, já com o Nosso Número do correspondente e seu DV calculado (Exceto Banco Itaú, cujo layout exige Nosso Número sem DV na remessa). O campo deve ocupar as 13 posições, com zeros à esquerda.
     * 5 - Cobrança exclusivamente para o próprio banco. As posições 140 a 142 devem estar preenchidas com o código do nosso banco.
     * 6 - Cobrança Expressa (sem emissão de bloquetes)
     * 7 - O código do Banco cobrador (que não é o Nosso Banco) deve vir nas posições 140-142. Após a aceitação do título pelo Nosso Banco, o Nosso Número do correspondente será gerado pelo sistema.
     * @var array
     */
    protected $carteiras = [1, 2, 3, 4, 5, 6, 7];

    /**
     * Espécie do documento, coódigo para remessa
     *
     * @var string
     */
    protected $especiesCodigo = [
        'DM' => '01', // Duplicata Mercantil
        'NP' => '02', // Nota Promissória
        'CH' => '03', // Cheque
        'LC' => '04', // Letra de Câmbio
        'RC' => '05', // Recibo
        'AS' => '08', // Apólice de Seguro
        'DS' => '12', // Duplicata de Serviço
        'CC' => '31', // Cartão de Crédito
        'O'  => '99', // Outros
    ];

    /**
     * Moeda
     *
     * @var int
     */
    protected $moeda = 9;

    /**
     * @var string
     */
    protected $operacao;

    /**
     * Código de range de composição do nosso número.
     *
     * @var int
     */
    protected $range = 0;

    /**
     * @return int
     */
    public function getRange()
    {
        return $this->range;
    }

    /**
     * @param int $range
     *
     * @return Abc
     */
    public function setRange($range)
    {
        $this->range = (int) $range;

        return $this;
    }

    /**
     * @return string
     */
    public function getOperacao()
    {
        return $this->operacao;
    }

    /**
     * @param $operacao
     *
     * @return Abc
     */
    public function setOperacao($operacao)
    {
        $this->operacao = $operacao;

        return $this;
    }

    /**
     * Retorna o número definido pelo cliente para compor o nosso número
     *
     * @return int
     */
    public function getNumero()
    {
        return $this->numero < $this->getRange() ? $this->getRange() + $this->numero : $this->numero;
    }

    /**
     * Retorna o código da carteira (Com ou sem registro)
     *
     * @return string
     */
    public function getCarteira()
    {
        return $this->carteira == 6 ? 121 : 110;
    }

    /**
     * Gera o Nosso Número.
     *
     * @return string
     */
    protected function gerarNossoNumero()
    {
        if ($this->getCarteira() != 121) {
            return Util::numberFormatGeral(0, 11);
        }

        /**                                                 6 = 121
         *
         * Para o cliente enviar um arquivo remessa para o banco, no padrão CNAB400, referente aos
         * títulos de cobrança expressa, a carteira deve ser 6 (corresponde a 121, utilizada cálculo do N/N e
         * boleto)
         */
        $nn = $this->getNumero();
        $dv = CalculoDV::abcNossoNumero($this->getAgencia(), $this->getCarteira(), $nn);

        return Util::numberFormatGeral($nn, 10) . $dv;
    }

    /**
     * Método que retorna o nosso número usado no boleto. Alguns bancos possuem algumas diferenças.
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
        $campoLivre .= Util::numberFormatGeral($this->getCarteira(), 3);
        $campoLivre .= Util::numberFormatGeral($this->getOperacao(), 7);
        $campoLivre .= Util::numberFormatGeral($this->getNossoNumero(), 11);

        return $this->campoLivre = $campoLivre;
    }

    /**
     * @return bool
     */
    public function imprimeBoleto()
    {
        return $this->getCarteira() == 121;
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
            'contaCorrenteDv' => null,
            'agencia'         => substr($campoLivre, 0, 4),
            'carteira'        => substr($campoLivre, 4, 3),
            'operacao'        => substr($campoLivre, 7, 7),
            'nossoNumero'     => substr($campoLivre, 14, 10),
            'nossoNumeroDv'   => substr($campoLivre, 24, 1),
            'nossoNumeroFull' => substr($campoLivre, 14, 11),
        ];
    }
}
