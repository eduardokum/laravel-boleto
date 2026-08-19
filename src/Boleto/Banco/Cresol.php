<?php

namespace Eduardokum\LaravelBoleto\Boleto\Banco;

use Eduardokum\LaravelBoleto\Util;
use Eduardokum\LaravelBoleto\CalculoDV;
use Eduardokum\LaravelBoleto\Boleto\AbstractBoleto;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;

class Cresol extends AbstractBoleto implements BoletoContract
{
    /**
     * Código do banco
     *
     * @var string
     */
    protected $codigoBanco = Boleto::COD_BANCO_CRESOL;

    /**
     * Define as carteiras disponíveis para este banco
     *
     * @var array
     */
    protected $carteiras = ['09'];

    /**
     * Trata-se de código utilizado para identificar mensagens especificas ao cedente, sendo
     * que o mesmo consta no cadastro do Banco, quando não houver código cadastrado preencher
     * com zeros "000".
     *
     * @var int
     */
    protected $cip = '000';

    /**
     * Variaveis adicionais..
     *
     * @var array
     */
    public $variaveis_adicionais = [
        'cip'        => '000',
        'mostra_cip' => true,
    ];

    /**
     * Espécie do documento, código para remessa. A Cresol usa a mesma tabela no CNAB 240
     * (segmento P, pos. 107-108) e no CNAB 400 (detalhe, pos. 148-149).
     *
     * @var array
     */
    protected $especiesCodigo = [
        'CH'  => '01', // Cheque
        'DM'  => '02', // Duplicata mercantil
        'DS'  => '04', // Duplicata de serviço
        'DR'  => '06', // Duplicata rural
        'LC'  => '07', // Letra de câmbio
        'NP'  => '12', // Nota promissória
        'RC'  => '17', // Recibo
        'ND'  => '19', // Nota de débito
        'W'   => '26', // Warrant
        'DAE' => '27', // Dívida ativa de estado
        'DAM' => '28', // Dívida ativa de município
        'DAU' => '29', // Dívida ativa da união
        'EC'  => '30', // Encargos condominiais
        'O'   => '99', // Outros
    ];

    /**
     * Conta do cedente no sistema Cresol, usada no campo livre do código de barras
     * (pos. 37-43). Não é necessariamente a conta corrente impressa no boleto, por isso
     * é modelada como campo próprio; quando não informada, assume a conta corrente.
     *
     * @var string|null
     */
    protected $codigoCedente = null;

    /**
     * Mostrar o endereço do beneficiário abaixo da razão e CNPJ na ficha de compensação
     *
     * @var bool
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
            . CalculoDV::cresolNossoNumero($this->getCarteira(), $this->getNumero());
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
        return Util::numberFormatGeral($this->getCarteira(), 2) . ' / ' . substr_replace($this->getNossoNumero(), '-', -1, 0);
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
        $campoLivre .= Util::numberFormatGeral($this->getCodigoCedente(), 7);
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
    public static function parseCampoLivre($campoLivre)
    {
        return [
            'convenio'        => null,
            'agenciaDv'       => null,
            'contaCorrenteDv' => null,
            'agencia'         => substr($campoLivre, 0, 4),
            'carteira'        => substr($campoLivre, 4, 2),
            'nossoNumero'     => substr($campoLivre, 6, 11),
            'nossoNumeroDv'   => null,
            'nossoNumeroFull' => substr($campoLivre, 6, 11),
            'contaCorrente'   => substr($campoLivre, 17, 7),
            'codigoCedente'   => substr($campoLivre, 17, 7),
        ];
    }

    /**
     * Define a conta do cedente no sistema Cresol usada no campo livre
     *
     * @param  string $codigoCedente
     * @return Cresol
     */
    public function setCodigoCedente($codigoCedente)
    {
        $this->codigoCedente = $codigoCedente;

        return $this;
    }

    /**
     * Retorna a conta do cedente no sistema Cresol, com fallback para a conta corrente
     *
     * @return string
     */
    public function getCodigoCedente()
    {
        return $this->codigoCedente !== null ? $this->codigoCedente : $this->getConta();
    }

    /**
     * Retorna apenas o dígito verificador do nosso número. Pode ser a letra "P" quando o
     * resto da divisão por 11 é 1, conforme item 3 das especificações técnicas Cresol.
     *
     * @return string
     */
    public function getNossoNumeroDv()
    {
        return substr($this->getNossoNumero(), -1);
    }

    /**
     * Informa se o dígito do nosso número é a letra "P". O CNAB 400 aceita o valor por ser
     * campo alfanumérico (pos. 82), mas o CNAB 240 declara o dígito como numérico
     * (segmento P, pos. 57), então esses números devem ser pulados no gerador do 240.
     *
     * @return bool
     */
    public function nossoNumeroDvEhLetra()
    {
        return ! is_numeric($this->getNossoNumeroDv());
    }

    /**
     * Define o campo CIP do boleto
     *
     * @param  int $cip
     * @return Cresol
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
