<?php

namespace Eduardokum\LaravelBoleto\Boleto\Banco;

use Eduardokum\LaravelBoleto\Boleto\AbstractBoleto;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;
use Eduardokum\LaravelBoleto\Exception\ValidationException;
use Eduardokum\LaravelBoleto\Util;

class Ailos extends AbstractBoleto implements BoletoContract
{
    public function __construct(array $params = [])
    {
        parent::__construct($params);
        $this->addCampoObrigatorio('convenio', 'contaDv', 'agenciaDv');
    }

    /**
     * Local de pagamento
     *
     * @var string
     */
    protected $localPagamento = 'Pagável preferencialmente nas cooperativas do Sistema AILOS';
    /**
     * Código do banco
     *
     * @var string
     */
    protected $codigoBanco = self::COD_BANCO_AILOS;
    /**
     * Define as carteiras disponíveis para este banco
     *
     * @var array
     */
    protected $carteiras = ['1'];
    /**
     * Espécie do documento, coódigo para remessa
     *
     * @var string
     */
    protected $especiesCodigo = [
        'DM' => '02', // Duplicata Mercantil por Indicação
        'DS' => '04', // Duplicata Serviço por Indicação
    ];
    /**
     * Se possui registro o boleto (tipo = 1 com registro e 3 sem registro)
     *
     * @var bool
     */
    protected $registro = true;

    /**
     * Define o número do convênio (6 dígitos. Iniciar na primeira posição e deixar as demais em branco).
     *
     * @var string
     */
    protected $convenio;

    /**
     * Define se possui ou não registro
     *
     * @param  bool $registro
     * @return Ailos
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
     * Seta o codigo do cliente.
     *
     * @param mixed $convenio
     *
     * @return Ailos
     */
    public function setConvenio($convenio)
    {
        $this->convenio = $convenio;

        return $this;
    }
    /**
     * Retorna o codigo do cliente.
     *
     * @return string
     */
    public function getConvenio()
    {
        return $this->convenio;
    }
    /**
     * Retorna o campo Agência/Beneficiário do boleto
     *
     * @return string
     */
    public function getAgenciaCodigoBeneficiario()
    {
        $conta = $this->getConta();
        $contaDV = $this->getContaDv();

        if (!empty($contaDV) || $contaDV == '0') {
            $conta .= '-' . $contaDV;
        }

        $agenciaDV = $this->getAgencia() . '-' . $this->getAgenciaDv();

        return $agenciaDV . '/' . $conta;
    }
    /**
     * Gera o Nosso Número.
     *
     * @return string
     */
    protected function gerarNossoNumero()
    {
        $conta = Util::numberFormatGeral($this->getConta() . $this->getContaDv(), 8);
        $numero_boleto = Util::numberFormatGeral($this->getNumero(), 9);
        $nossoNumero = $conta . $numero_boleto;
        return $nossoNumero;
    }
    /**
     * Método que retorna o nosso numero usado no boleto. alguns bancos possuem algumas diferenças.
     *
     * @return string
     */
    public function getNossoNumeroBoleto()
    {
        return $this->getNossoNumero();
    }
    /**
     * Método para gerar o código da posição de 20 a 44
     *
     * @return string
     * @throws ValidationException
     */
    protected function getCampoLivre()
    {
        if ($this->campoLivre) {
            return $this->campoLivre;
        }

        $campoLivre = Util::numberFormatGeral($this->getConvenio(), 6);
        $campoLivre .= $this->getNossoNumero();
        $campoLivre .= Util::numberFormatGeral($this->getCarteira(), 2);

        return $this->campoLivre .= $campoLivre;
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
            'agenciaDv' => null,
            'contaCorrenteDv' => null,
            'convenio' => substr($campoLivre, 20, 6),
            'carteira' => substr($campoLivre, -2),
            'nossoNumero' => substr($campoLivre, 26, 17),
            'nossoNumeroDv' => null,
            'nossoNumeroFull' => substr($campoLivre, 26, 17),
            'agencia' => null,
            'contaCorrente' => substr($campoLivre, 26, 8),
        ];
    }
}
