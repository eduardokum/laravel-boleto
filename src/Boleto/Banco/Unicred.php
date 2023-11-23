<?php

namespace Eduardokum\LaravelBoleto\Boleto\Banco;

use Eduardokum\LaravelBoleto\Util;
use Eduardokum\LaravelBoleto\CalculoDV;
use Eduardokum\LaravelBoleto\Boleto\AbstractBoleto;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;

class Unicred extends AbstractBoleto implements BoletoContract
{
    /**
     * Código do banco
     * @var string
     */
    protected $codigoBanco = self::COD_BANCO_UNICRED;

    /**
     * Define as carteiras disponíveis para este banco
     * @var array
     */
    protected $carteiras = ['21'];

    /**
     * Linha de local de pagamento
     *
     * @var string
     */
    protected $localPagamento = 'PAGÁVEL EM QUALQUER AGÊNCIA BANCÁRIA/CORRESPONDENTE BANCÁRIO';

    /**
     * ESPÉCIE DO DOCUMENTO: de acordo com o ramo de atividade
     * @var string
     */
    protected $especiesCodigo = [
        'DM'     => 'DM', //'Duplicata Mercantil',
        'NP'     => 'NP', //'Nota Promissória',
        'NS'     => 'NS', //'Nota de Seguro',
        'CS'     => 'CS', //'Cobrança Seriada',
        'REC'    => 'REC', //'Recibo',
        'LC'     => 'LC', //'Letras de Câmbio',
        'ND'     => 'ND', //'Nota de Débito',
        'DS'     => 'DS', //'Duplicata de Serviços',
        'Outros' => 'Outros',
    ];

    /**
     * Trata-se de código utilizado para identificar mensagens especificas ao cedente, sendo
     * que o mesmo consta no cadastro do Banco, quando não houver código cadastrado preencher
     * com zeros "000".
     *
     * @var int
     */
    protected $cip = '000';

    /**
     * Variaveis adicionais.
     *
     * @var array
     */
    public $variaveis_adicionais = [
        'cip'        => '000',
        'mostra_cip' => true,
    ];

    /**
     * Código do cliente (é código do cedente, também chamado de código do beneficiário) é o código do emissor junto ao banco e precisa ser buscado junto ao gerente de contas essa informação
     *
     * @var string
     */
    protected $codigoCliente;

    /**
     * Gera o Nosso Número. Formado com 11(onze) caracteres, sendo 10 dígitos
     * para o nosso número é um digito para o digito verificador. Ex.: 9999999999-D.
     * Obs.: O Nosso Número é um identificador do boleto, devendo ser atribuído
     * Nosso Número diferenciado para cada um.
     *
     * @return string
     */
    protected function gerarNossoNumero()
    {
        return Util::numberFormatGeral($this->getNumero(), 10) . CalculoDV::unicredNossoNumero($this->getNumero());
    }

    /**
     * Método que retorna o nosso numero usado no boleto, formato XXXXXXXXXX-D. alguns bancos possuem algumas diferenças.
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

        $nossoNumero = $this->getNossoNumero();

        $campoLivre = Util::numberFormatGeral($this->getAgencia(), 4); //Agência BENEFICIÁRIO (Sem o dígito verificador, completar com zeros à esquerda quando necessário)
        $campoLivre .= Util::numberFormatGeral($this->getConta() . $this->getContaDv(), 10); //Conta do BENEFICIÁRIO (Com o dígito verificador - Completar com zeros à esquerda quando necessário)
        $campoLivre .= Util::numberFormatGeral($nossoNumero, 11); //Nosso Número (Com o dígito verificador)

        return $this->campoLivre = $campoLivre;
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
            // 'convenio' => null,
            'agenciaDv'       => null,
            'contaCorrenteDv' => null,
            'agencia'         => substr($campoLivre, 0, 4),
            'nossoNumero'     => substr($campoLivre, 14, 10),
            'nossoNumeroDv'   => substr($campoLivre, 24, 1),
            'nossoNumeroFull' => substr($campoLivre, 14, 11),
            'contaCorrente'   => substr($campoLivre, 4, 10),
        ];
    }

    /**
     * AGÊNCIA / CÓDIGO DO BENEFICIÁRIO: deverá ser preenchido com o código da agência,
     * contendo 4 (quatro) caracteres / Conta Corrente com 10 (dez) caracteres. Ex.
     * 9999/999999999-9. Obs.: Preencher com zeros à direita quando necessário.
     * @return string
     */
    public function getAgenciaCodigoBeneficiario()
    {
        return $this->getAgencia() . ' / ' . Util::numberFormatGeral($this->getConta(), 9) . '-' . $this->getContaDv();
    }

    /**
     * Define o campo CIP do boleto
     *
     * @param int $cip
     * @return Unicred
     */
    public function setCip($cip)
    {
        $this->cip = $cip;
        $this->variaveis_adicionais['cip'] = $this->getCip();

        return $this;
    }

    /**
     * Retorna o campo CIP do boleto
     *
     * @return string
     */
    public function getCip()
    {
        return Util::numberFormatGeral($this->cip, 3);
    }

    /**
     * Seta o código do cliente.
     *
     * @param mixed $codigoCliente
     *
     * @return Unicred
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
}
