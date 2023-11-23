<?php

namespace Eduardokum\LaravelBoleto\Boleto\Banco;

use Eduardokum\LaravelBoleto\Util;
use Eduardokum\LaravelBoleto\CalculoDV;
use Eduardokum\LaravelBoleto\Boleto\AbstractBoleto;
use Eduardokum\LaravelBoleto\Exception\ValidationException;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;

class Banrisul extends AbstractBoleto implements BoletoContract
{
    /**
     * Código do banco
     *
     * @var string
     */
    protected $codigoBanco = self::COD_BANCO_BANRISUL;

    /**
     * Define as carteiras disponíveis para este banco
     * 1 -> Cobrança Simples
     * 2 -> Cobrança Vinculada
     * 3 -> Cobrança Caucionada
     * 4 -> Cobrança em IGPM
     * 5 -> Cobrança Caucionada CGB Especial
     * 6 -> Cobrança Simples Seguradora
     * 7 -> Cobrança em UFIR
     * 8 -> Cobrança em IDTR
     * B -> Cobrança Caucionada CGB Especial
     * C -> Cobrança Vinculada
     * D -> Cobrança CSB
     * E -> Cobrança Caucionada Câmbio
     * F -> Cobrança Vendor
     * G -> BBH
     * H -> Cobrança Caucionada Dólar
     * I -> Cobrança Caucionada Compror
     * J -> Cobrança Caucionada NPR
     * K -> Cobrança Simples INCC-M
     * M -> Cobrança Partilhada
     * N -> Capital de Giro CGB ICM
     * P -> Capital de Giro CGB ICM
     * R -> Desconto de Duplicata
     * S -> Vendor Eletrônico
     * T -> Leasing
     * U -> CSB e CCB sem registro
     * X -> Vendor BDL
     *
     * @var array
     */
    protected $carteiras = ['1', '2', '3', '4', '5', '6', '7', '8', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'M', 'N', 'P', 'R', 'S', 'T', 'U', 'X'];

    /**
     * Espécie do documento, código para remessa do CNAB240
     * @var string
     */
    protected $especiesCodigo = [
        'DM'  => '02', //Duplicata Mercantil – Banco emite bloqueto franqueado. Se a posição 61 for igual a 2 o Banco transformará “espécie do título” para AA
        'DS'  => '04', //Duplicata de Serviço
        'LC'  => '07', //Letra de Câmbio
        'NP'  => '12', //Nota Promissória
        'CCB' => 'AA', //O Banco não emite o bloqueto
        'CD'  => 'AB', //Cobrança Direta
        'CE'  => 'AC', //Cobrança Escritural
        'TT'  => 'AD', //Título de terceiros
    ];

    /**
     * Codigo do cliente junto ao banco.
     *
     * @var string
     */
    protected $codigoCliente;

    /**
     * Seta dia para baixa automática
     *
     * @param int $baixaAutomatica
     *
     * @return Banrisul
     * @throws ValidationException
     */
    public function setDiasBaixaAutomatica($baixaAutomatica)
    {
        if ($this->getDiasProtesto() > 0) {
            throw new ValidationException('Você deve usar dias de protesto ou dias de baixa, nunca os 2');
        }
        $baixaAutomatica = (int) $baixaAutomatica;
        $this->diasBaixaAutomatica = $baixaAutomatica > 0 ? $baixaAutomatica : 0;

        return $this;
    }

    /**
     * Gerar nosso número
     *
     * @return string
     */
    protected function gerarNossoNumero()
    {
        $numero_boleto = $this->getNumero();
        $nossoNumero = Util::numberFormatGeral($numero_boleto, 8)
            . CalculoDV::banrisulNossoNumero(Util::numberFormatGeral($numero_boleto, 8));

        return $nossoNumero;
    }

    /**
     * Método que retorna o nosso numero usado no boleto. alguns bancos possuem algumas diferenças.
     *
     * @return string
     */
    public function getNossoNumeroBoleto()
    {
        return substr_replace($this->getNossoNumero(), '-', -2, 0);
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

        $campoLivre = '2';
        $campoLivre .= '1';
        $campoLivre .= Util::numberFormatGeral($this->getCodigoCliente(), 11); //4 digitos da agencia + 7 primeiros digitos pois os ultimos 2 são digitos verificadores
        $campoLivre .= Util::numberFormatGeral($this->getNumero(), 8);
        $campoLivre .= '40';
        $campoLivre .= CalculoDV::banrisulDuploDigito(Util::onlyNumbers($campoLivre));

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
            'carteira'        => substr($campoLivre, 0, 1),
            'agencia'         => substr($campoLivre, 2, 4),
            'contaCorrente'   => substr($campoLivre, 6, 7),
            'nossoNumero'     => substr($campoLivre, 13, 8),
            'nossoNumeroDv'   => null,
            'nossoNumeroFull' => substr($campoLivre, 13, 8),
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
     * @return Banrisul
     */
    public function setCodigoCliente($codigoCliente)
    {
        $this->codigoCliente = $codigoCliente;

        return $this;
    }

    /**
     * Retorna o campo Agência/Beneficiário do boleto
     *
     * @return string
     */
    public function getAgenciaCodigoBeneficiario()
    {
        return $this->getCodigoCliente();
    }
}
