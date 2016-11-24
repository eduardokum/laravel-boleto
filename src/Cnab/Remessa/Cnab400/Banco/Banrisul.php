<?php


namespace Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400\Banco;

use Eduardokum\LaravelBoleto\Cnab\Remessa\AbstractRemessa;
use Eduardokum\LaravelBoleto\Contracts\Cnab\Remessa as RemessaContract;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;
use Eduardokum\LaravelBoleto\Util;

class Banrisul extends AbstractRemessa implements RemessaContract
{
    const TIPO_DOC_COBRANCA_DIRETA = '04';
    const TIPO_DOC_COBRANCA_ESCRITURAL = '06';
    const TIPO_DOC_COBRANCA_CREDENCIADA = '08';
    const TIPO_DOC_TITULO_TERCEIROS = '09';

    /**
     * Código do banco
     * @var string
     */
    protected $codigoBanco = BoletoContract::COD_BANCO_BANRISUL;

    /**
     * Define as carteiras disponíveis para este banco
     * 1 -> Cobrança Simples
     * 3 -> Cobrança Caucionada
     * 4 -> Cobrança em IGPM
     * 5 -> Cobrança Caucionada CGB Especial
     * 6 -> Cobrança Simples Seguradora
     * 7 -> Cobrança em UFIR
     * 8 -> Cobrança em IDTR
     * C -> Cobrança Vinculada
     * D -> Cobrança CSB
     * E -> Cobrança Caucionada Câmbio
     * F -> Cobrança Vendor
     * H -> Cobrança Caucionada Dólar
     * I -> Cobrança Caucionada Compror
     * K -> Cobrança Simples INCC-M
     * M -> Cobrança Partilhada
     * N -> Capital de Giro CGB ICM
     * R -> Desconto de Duplicata
     * S -> Vendor Eletrônico – Valor Final (Corrigido)
     * X -> Vendor BDL – Valor Inicial (Valor da NF)
     *
     * @var array
     */
    protected $carteiras = ['1','2','3','4','5','6','7','8','C','D','E','F','H','I','K','M','N','R','S','X'];

    /**
     * Caracter de fim de linha
     *
     * @var string
     */
    protected $fimLinha = "\r\n";

    /**
     * Caracter de fim de arquivo
     *
     * @var null
     */
    protected $fimArquivo = "\r\n";

    /**
     * Convenio com o banco
     *
     * @var string
     */
    protected $convenio;

    /**
     * Convenio lider com o banco
     *
     * @var string
     */
    protected $convenioLider;

    /**
     * Variação da carteira
     *
     * @var string
     */
    protected $variacaoCarteira;

    /**
     * @return $this
     */
    protected function header()
    {
        $this->iniciaHeader();

        $this->add(1, 9, '01REMESSA');
        $this->add(10, 26, '');
        $this->add(27, 39, $this->getConta());
        $this->add(40, 46, '');
        $this->add(47, 76, Util::formatCnab('A', $this->getBeneficiario()->getNome(), 30));
        $this->add(77, 87, '041BANRISUL');
        $this->add(88, 94, '');
        $this->add(95, 100, date('dmy'));
        $this->add(101, 109, '');
        $this->add(110, 113, '');
        $this->add(114, 114, '');
        $this->add(115, 115, '');
        $this->add(116, 116, '');
        $this->add(117, 126, '');
        $this->add(127, 394, '');
        $this->add(395, 400, '000001');

        return $this;
    }

    /**
     * @param BoletoContract $boleto
     * @return bool
     */
    public function addBoleto(BoletoContract $boleto)
    {

        $this->iniciaDetalhe();

        $this->add(1, 1, 1);
        $this->add(2, 17, '');
        $this->add(18, 30, Util::numberFormatGeral($this->getConta(), 13, '0'));
        $this->add(31, 37, '');
        $this->add(38, 62, '');
        $this->add(63, 72, Util::formatCnab('9L', $boleto->getNossoNumero(), 10));
        $this->add(73, 104, '');
        $this->add(105, 107, '');
        $this->add(108, 108, '1');
        $this->add(109, 110, '01'); // REGISTRO
        if($boleto->getStatus() == $boleto::STATUS_BAIXA)
        {
            $this->add(109, 110, '02'); // BAIXA
        }

        $this->add(111, 120, Util::formatCnab('X', $boleto->getNumeroDocumento(), 10));
        $this->add(121, 126, $boleto->getDataVencimento()->format('dmy'));
        $this->add(127, 139, Util::formatCnab('9', $boleto->getValor(), 13, 2));
        $this->add(140, 142, $this->getCodigoBanco());
        $this->add(143, 147, '');
        $this->add(148, 149, $boleto->getEspecieDocCodigo());
        $this->add(150, 150, $boleto->getAceite());
        $this->add(151, 156, $boleto->getDataDocumento()->format('dmy'));

        $this->add(157, 158, '23');
        $this->add(159, 160, '00');

        $juros = 0;

        if($boleto->getJuros() !== false)
        {
            $juros = Util::percent($boleto->getValor(), $boleto->getJuros())/30;
        }

        $this->add(161, 173, Util::formatCnab('9', $juros, 13, 2));
        $this->add(174, 179, '000000');
        $this->add(180, 192, Util::formatCnab('9', 0, 13, 2));
        $this->add(193, 205, Util::formatCnab('9', 0, 13, 2));
        $this->add(206, 218, Util::formatCnab('9', $boleto->getDescontosAbatimentos(), 13, 2));
        $this->add(219, 220, strlen(Util::onlyNumbers($boleto->getPagador()->getDocumento())) == 14 ? '02' : '01');
        $this->add(221, 234, Util::formatCnab('9L', $boleto->getPagador()->getDocumento(), 14));
        $this->add(235, 271, Util::formatCnab('X', $boleto->getPagador()->getNome(), 37));
        $this->add(272, 274, Util::formatCnab('N', '', 3));
        $this->add(275, 314, Util::formatCnab('X', $boleto->getPagador()->getEndereco(), 40));
        $this->add(315, 326, Util::formatCnab('X', '', 12));
        $this->add(327, 334, Util::formatCnab('9L', $boleto->getPagador()->getCep(), 8));
        $this->add(335, 349, Util::formatCnab('X', $boleto->getPagador()->getCidade(), 15));
        $this->add(350, 351, Util::formatCnab('X', $boleto->getPagador()->getUf(), 2));
        $this->add(352, 391, Util::formatCnab('X', $boleto->getSacadorAvalista() ? $boleto->getSacadorAvalista()->getNome() : '', 40));
        $this->add(392, 393, '00');
        $this->add(394, 394, '');
        $this->add(395, 400, Util::formatCnab('N', $this->iRegistros+1, 6));

        if($boleto->getMulta() !== false)
        {
            $this->iniciaDetalhe();

            $this->add(1, 1, 5);
            $this->add(2, 3, 99);
            $this->add(4, 4, 2);
            $this->add(5, 10, $boleto->getDataVencimento()->copy()->addDays($boleto->getJurosApos(0))->format('dmy'));
            $this->add(11, 22, Util::formatCnab('9', $boleto->getMulta(), 7, 2));
            $this->add(23, 394, '');
            $this->add(23, 394, '');
            $this->add(395, 400, Util::formatCnab('N', $this->iRegistros+1, 6));
        }

        return true;
    }

    /**
     * @return $this
     */
    protected function trailer()
    {
        $this->iniciaTrailer();

        $this->add(1, 1, '9');
        $this->add(2, 394, '');
        $this->add(395, 400, Util::formatCnab('N', $this->getCount(), 6));

        return $this;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        if(!parent::isValid())
        {
            return false;
        }

        return true;
    }

}