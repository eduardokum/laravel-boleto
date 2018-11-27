<?php
namespace Eduardokum\LaravelBoleto\Boleto\Banco;

use Eduardokum\LaravelBoleto\Boleto\AbstractBoleto;
use Eduardokum\LaravelBoleto\CalculoDV;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto;
use Eduardokum\LaravelBoleto\Util;

class Bradesco  extends AbstractBoleto implements BoletoContract
{
    /**
     * Código do banco
     *
     * @var string
     */
    protected $codigoBanco = Boleto::COD_BANCO_BRADESCO;
    /**
     * Define as carteiras disponíveis para este banco
     * '09' => Com registro | '06' => Sem Registro | '21' => Com Registro - Pagável somente no Bradesco | '22' => Sem Registro - Pagável somente no Bradesco | '25' => Sem Registro - Emissão na Internet | '26' => Com Registro - Emissão na Internet
     *
     * @var array
     */
    protected $carteiras = ['04', '09', '21', '26'];
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
        'cip' => '000',
        'mostra_cip' => true,
    ];
    /**
     * Espécie do documento, coódigo para remessa
     *
     * @var string
     */
    protected $especiesCodigo = [
        'CH'  => '01', //Cheque
        'DM'  => '02', //Duplicata Mercantil
        'DMI' => '03', //Duplicata Mercantil p/ Indicação
        'DS'  => '04', //Duplicata de Serviço
        'DSI' => '05', //Duplicata de Serviço p/ Indicação
        'DR'  => '06', //Duplicata Rural
        'LC'  => '07', //Letra de Câmbio
        'NCC' => '08', //Nota de Crédito Comercial
        'NCE' => '09', //Nota de Crédito a Exportação
        'NCI' => '10', //Nota de Crédito Industrial
        'NCR' => '11', //Nota de Crédito Rural
        'NP'  => '12', //Nota Promissória
        'NPR' => '13', //Nota Promissória Rural
        'TM'  => '14', //Triplicata Mercantil
        'TS'  => '15', //Triplicata de Serviço
        'NS'  => '16', //Nota de Seguro
        'RC'  => '17', //Recibo
        'FAT' => '18', //Fatura
        'ND'  => '19', //Nota de Débito
        'AP'  => '20', //Apólice de Seguro
        'ME'  => '21', //Mensalidade Escolar
        'PC'  => '22', //Parcela de Consórcio
        'NF'  => '23', //Nota Fiscal
        'DD'  => '24', //Documento de Dívida
        'CPR' => '25', //Cédula de Produto Rural,
        'WAR' => '26', //Warrant
        'DAE' => '27', //Dívida Ativa do Estado
        'DAM' => '28', //Dívida Ativa do Município
        'DAU' => '29', //Dívida Ativa da União
        'EC'  => '30', //Encargos condominiais
        'CC'  => '31', //CC Cartão de Crédito,
        'BDP' => '32', //BDP - Boleto de Proposta
        'O'   => '99', //Outros,
    ];
    /**
     * Mostrar o endereço do beneficiário abaixo da razão e CNPJ na ficha de compensação
     *
     * @var boolean
     */
    protected $mostrarEnderecoFichaCompensacao = true;
    /**
     * Gera o Nosso Número.
     *
     * @return string
     */
    protected function gerarNossoNumero()
    {
        return Util::numberFormatGeral($this->getNumero(), 11)
            . CalculoDV::bradescoNossoNumero($this->getCarteira(), $this->getNumero());
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
        if ($this->getDiasProtesto() > 0) {
            throw new \Exception('Você deve usar dias de protesto ou dias de baixa, nunca os 2');
        }
        $baixaAutomatica = (int) $baixaAutomatica;
        $this->diasBaixaAutomatica = $baixaAutomatica > 0 ? $baixaAutomatica : 0;
        return $this;
    }

    /**
     * Método que retorna o nosso numero usado no boleto. alguns bancos possuem algumas diferenças.
     *
     * @return string
     */
    public function getNossoNumeroBoleto()
    {
        return Util::numberFormatGeral($this->getCarteira(), 2) . ' / ' .  substr_replace($this->getNossoNumero(), '-', -1, 0);
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
        $campoLivre .= Util::numberFormatGeral($this->getCarteira(), 2);
        $campoLivre .= Util::numberFormatGeral($this->getNumero(), 11);
        $campoLivre .= Util::numberFormatGeral($this->getConta(), 7);
        $campoLivre .= '0';

        return $this->campoLivre = $campoLivre;
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
            'agencia' => substr($campoLivre, 0, 4),
            'carteira' => substr($campoLivre, 4, 2),
            'nossoNumero' => substr($campoLivre, 6, 11),
            'nossoNumeroDv' => null,
            'nossoNumeroFull' => substr($campoLivre, 6, 11),
            'contaCorrente' => substr($campoLivre, 17, 7),
        ];
    }

    /**
     * Define o campo CIP do boleto
     *
     * @param  int $cip
     * @return Bradesco
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
}
