<?php
namespace Eduardokum\LaravelBoleto\Boleto\Banco;

use Eduardokum\LaravelBoleto\Boleto\AbstractBoleto;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;
use Eduardokum\LaravelBoleto\Util;

class Bancoob extends AbstractBoleto implements BoletoContract
{
    const BANCOBB_CONST_NOSSO_NUMERO = "3197";

    public function __construct(array $params = [])
    {
        parent::__construct($params);
        $this->addCampoObrigatorio('convenio');
    }

    /**
     * Código do banco
     * @var string
     */
    protected $codigoBanco = self::COD_BANCO_BANCOOB;
    /**
     * Define as carteiras disponíveis para este banco
     * @var array
     */
    protected $carteiras = ['1','3'];
    /**
     * Espécie do documento, coódigo para remessa
     * @var string
     */
    protected $especiesCodigo = [
        'DM' => '01',
        'NP' => '02',
        'DS' => '12',
    ];
    /**
     * Define o número do convênio (4, 6 ou 7 caracteres)
     *
     * @var string
     */
    protected $convenio;
    /**
     * Define o número do convênio. Sempre use string pois a quantidade de caracteres é validada.
     *
     * @param  string $convenio
     * @return Bancoob
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
     * @throws \Exception
     * @return string
     */
    protected function gerarNossoNumero()
    {
        $agencia = $this->getAgencia();
        $convenio = $this->getConvenio();
        $numero_boleto = $this->getNumero();

        $numero = Util::numberFormatGeral($agencia, 4).Util::numberFormatGeral($convenio, 10).Util::numberFormatGeral($numero_boleto, 7);

        $chars = str_split($numero, 1);
        $sums = str_split('3197319731973197319731973197', 1);
        $sum = 0;
        foreach ($chars as $i => $char) {
            $sum += $char*$sums[$i];
        }
        $resto = $sum % 11;
        $digito_verificador = 0;

        if (($resto != 0) && ($resto != 1)) {
            $digito_verificador = 11 - $resto;
        }

        return $numero_boleto . $digito_verificador;
    }
    /**
     * Método que retorna o nosso numero usado no boleto. alguns bancos possuem algumas diferenças.
     *
     * @return string
     */
    public function getNossoNumeroBoleto()
    {
        $nn = $this->getNossoNumero();
        return substr($nn, 0, -1) . '-' . substr($nn, -1);
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

        $nossoNumero = $this->getNossoNumero();

        $campoLivre = Util::numberFormatGeral($this->getCarteira(), 1);
        $campoLivre .= Util::numberFormatGeral($this->getAgencia(), 4);
        $campoLivre .= Util::numberFormatGeral($this->getCarteira(), 2);
        $campoLivre .= Util::numberFormatGeral($this->getConvenio(), 7);
        $campoLivre .= Util::numberFormatGeral($nossoNumero, 8);
        $campoLivre .= Util::numberFormatGeral(1, 3); //Numero da parcela - Não implementado

        return $this->campoLivre = $campoLivre;
    }
}
