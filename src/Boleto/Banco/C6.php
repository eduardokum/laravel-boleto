<?php

namespace Eduardokum\LaravelBoleto\Boleto\Banco;

use Eduardokum\LaravelBoleto\Util;
use Eduardokum\LaravelBoleto\CalculoDV;
use Eduardokum\LaravelBoleto\Boleto\AbstractBoleto;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;

// C6 Bank — Layout CNAB 400 Cobrança Bancária, v2.7, jul/2025 (manuais/C6/)
class C6 extends AbstractBoleto implements BoletoContract
{
    public function __construct(array $params = [])
    {
        parent::__construct($params);
        $this->setCamposObrigatorios('codigoCliente');
    }

    /**
     * Local de pagamento
     *
     * @var string
     */
    protected $localPagamento = 'Pagável em canais eletrônicos, agências ou correspondentes';

    /**
     * Código do banco
     *
     * @var string
     */
    protected $codigoBanco = self::COD_BANCO_C6;

    /**
     * Define as carteiras disponíveis para este banco
     *
     * @var array
     */
    protected $carteiras = ['10', '20', '30', '40', '60'];

    /**
     * Espécie do documento, coódigo para remessa
     *
     * @var string
     */
    protected $especiesCodigo = [
        'DM'  => '01',
        'DS'  => '02',
        'NP'  => '03',
        'NS'  => '04',
        'REC' => '05',
        'LC'  => '06',
        'FC'  => '07',
        'CAR' => '08',
        'CT'  => '09',
        'ME'  => '12',
        'ND'  => '13',
        'CDA' => '15',
        'EC'  => '16',
        'CPS' => '17',
        'FT'  => '31',
        'BA'  => '33',
        'O'   => '99',
    ];

    /**
     * Codigo do cliente junto ao banco.
     *
     * @var string
     */
    protected $codigoCliente;

    /**
     * Carteira 10 (Emissão Banco): o C6 atribui o nosso número, campo fica em branco na remessa —
     * sem limite de dígitos aqui para o consumidor.
     * Carteira 20 (Emissão Cliente): o cliente atribui, 10 dígitos úteis nas posições 63-73 do
     * detalhe de remessa, formato "0NNNNNNNNNN" (manual, seção 5.2 e "Cálculo do Dígito do Nosso Número").
     * Demais carteiras (30/40/60) não documentadas no manual atual — tratadas como Carteira 20.
     *
     * @return int|null
     */
    public function getNossoNumeroMaxLength()
    {
        return $this->getCarteira() == '10' ? null : 10;
    }

    /**
     * Gera o Nosso Número.
     *
     * Carteira 10: o C6 atribui o nosso número — não temos como calculá-lo aqui, então o campo
     * fica vazio até vir do retorno (ver setNossoNumero abaixo, espelhando o Inter). O manual (item
     * 5.2, campo 24) diz para "deixar o campo em BRANCO" na remessa de registro; a remessa grava
     * esse vazio como zeros (Util::formatCnab tipo "9"), igual ao Inter — assumido por analogia de
     * formatação de campo numérico da lib, não confirmado com o C6. Repactuar se o C6 rejeitar.
     *
     * @return string
     */
    protected function gerarNossoNumero()
    {
        if ($this->getCarteira() == '10') {
            return '';
        }

        return Util::numberFormatGeral($this->getNumero(), 11) . CalculoDV::c6NossoNumero($this->getCarteira(), $this->getNumero());
    }

    /**
     * Só é preciso na Carteira 10 (banco atribui o nosso número): o valor devolvido pelo C6 no
     * arquivo de retorno é gravado aqui para as remessas seguintes que instruem sobre o mesmo
     * título (baixa, alteração de vencimento etc.), já que gerarNossoNumero() não tem como
     * recalculá-lo. Trunca a 11 dígitos (posições 63-73 do retorno, campo "Nosso Número do
     * Título") espelhando o padrão já usado pelo pipeline do admin para banco que atribui o
     * número (Inter — TicketBatchImportService/TicketBatchService::applyBankAssignedNumber já
     * chamam setNossoNumero() com o valor de 11 dígitos do retorno, sem o dígito verificador do
     * campo 118, que hoje não é capturado pelo parser). Invalida os campos derivados (campo
     * livre, código de barras, linha digitável), calculados a partir do nosso número anterior
     * (vazio).
     *
     * ATENÇÃO: getCampoLivre()/getNossoNumeroBoleto() assumem o formato "0" + 10 dígitos usado
     * na Carteira 20 (cliente emite). Não há confirmação de que o valor devolvido pelo C6 para a
     * Carteira 10 (banco emite) siga essa mesma estrutura — nem confirmação de que precisamos
     * renderizar boleto próprio nesse caso (o nome da carteira sugere que o C6 pode emitir o
     * boleto). Validar contra um retorno real antes de confiar na impressão do boleto para
     * títulos de Carteira 10.
     *
     * @param string $nossoNumero
     *
     * @return C6
     */
    public function setNossoNumero($nossoNumero)
    {
        $this->campoNossoNumero = substr(Util::onlyNumbers($nossoNumero), -11);

        $this->campoLivre = null;
        $this->campoCodigoBarras = null;
        $this->campoLinhaDigitavel = null;

        return $this;
    }

    /**
     * Método que retorna o nosso número usado no boleto. alguns bancos possuem algumas diferenças.
     *
     * @return string
     */
    public function getNossoNumeroBoleto()
    {
        return substr($this->getNossoNumero(), 1, 10);
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

        $campoLivre = Util::numberFormatGeral($this->getCodigoCliente(), 12);
        $campoLivre .= Util::numberFormatGeral(substr($this->getNossoNumero(), 1, 10), 10);
        $campoLivre .= Util::numberFormatGeral($this->getCarteira(), 2);
        $campoLivre .= in_array(((int) $this->getCarteira()), [10, 30, 60]) ? 3 : 4;

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
            'codigoCliente'   => substr($campoLivre, 0, 12),
            'carteira'        => substr($campoLivre, 22, 2),
            'nossoNumero'     => substr($campoLivre, 12, 10),
            'nossoNumeroDv'   => null,
            'nossoNumeroFull' => substr($campoLivre, 12, 10),
            'agencia'         => null,
            'contaCorrente'   => null,
            'contaCorrenteDv' => null,
        ];
    }

    /**
     * Retorna o codigo do cliente.
     *
     * @return mixed
     */
    public function getCodigoCliente()
    {
        return $this->codigoCliente;
    }

    /**
     * Seta o codigo do cliente.
     *
     * @param mixed $codigoCliente
     *
     * @return C6
     */
    public function setCodigoCliente($codigoCliente)
    {
        $this->codigoCliente = $codigoCliente;

        return $this;
    }

    /**
     * @return string
     */
    public function getAgenciaCodigoBeneficiario()
    {
        return sprintf('%04s/%012s', $this->getAgencia() ?: 1, $this->getCodigoCliente());
    }
}
