<?php
namespace Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400\Banco;

use Eduardokum\LaravelBoleto\CalculoDV;
use Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400\AbstractRemessa;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;
use Eduardokum\LaravelBoleto\Contracts\Cnab\Remessa as RemessaContract;
use Eduardokum\LaravelBoleto\Util;

class Bnb extends AbstractRemessa implements RemessaContract
{
    const ESPECIE_DUPLICATA = '01';
    const ESPECIE_NOTA_PROMISSORIA = '02';
    const ESPECIE_CHEQUE = '03';
    const ESPECIE_CARNE = '04';
    const ESPECIE_RECIBO = '05';
    const ESPECIE_OUTROS = '19';

    const OCORRENCIA_REMESSA = '01';
    const OCORRENCIA_PEDIDO_BAIXA = '02';
    const OCORRENCIA_CONCESSAO_ABATIMENTO = '04';
    const OCORRENCIA_ALT_VENCIMENTO = '06';
    const OCORRENCIA_ALT_SEUNUMERO = '08';
    const OCORRENCIA_PROTESTAR = '09';
    const OCORRENCIA_NAO_PROTESTAR = '10';
    const OCORRENCIA_INCLUSAO_OCORRENCIA = '12';
    const OCORRENCIA_EXCLUSAO_OCORRENCIA = '13';
    const OCORRENCIA_ALT_OUTROS_DADOS = '31';
    const OCORRENCIA_PEDIDO_DEVOLUCAO = '32';
    const OCORRENCIA_PEDIDO_DEVOLUCAO_ENTREGUE_SACADO = '33';
    const OCORRENCIA_PEDIDO_DOS_TITULOS_EM_ABERTO = '99';

    const INSTRUCAO_SEM = '00';
    const INSTRUCAO_ACATAR_INSTRUCOES_TITULO = '05';
    const INSTRUCAO_NAO_COBRAR_ENCARGOS = '08';
    const INSTRUCAO_NAO_RECEBER_APOS_VENCIMENTO = '12';
    const INSTRUCAO_APOS_VENCIMENTO_COBRAR_COMISSAO_PERMANENCIA = '15';

    /**
     * Código do banco
     *
     * @var string
     */
    protected $codigoBanco = BoletoContract::COD_BANCO_BNB;

    /**
     * Define as carteiras disponíveis para cada banco
     *
     * @var array
     */
    protected $carteiras = ['21'];

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
     * Retorna o numero da carteira, deve ser override em casos de carteira de letras
     *
     * @return string
     */
    public function getCarteiraNumero()
    {
        if ($this->getCarteira() == '21') {
            return '4';
        }
        return '1';
    }

    /**
     * @return $this
     * @throws \Exception
     */
    protected function header()
    {
        $this->iniciaHeader();

        $this->add(1, 1, '0');
        $this->add(2, 2, '1');
        $this->add(3, 9, 'REMESSA');
        $this->add(10, 11, '01');
        $this->add(12, 26, Util::formatCnab('X', 'COBRANCA', 15));
        $this->add(27, 30, Util::formatCnab('9', $this->getAgencia(), 4));
        $this->add(31, 32, '00');
        $this->add(33, 39, Util::formatCnab('9', $this->getConta(), 7));
        $this->add(40, 40, $this->getContaDv() ?: CalculoDV::bnbContaCorrente($this->getAgencia(), $this->getConta()));
        $this->add(41, 46, '');
        $this->add(47, 76, Util::formatCnab('X', $this->getBeneficiario()->getNome(), 30));
        $this->add(77, 79, $this->getCodigoBanco());
        $this->add(80, 94, Util::formatCnab('X', 'B.DO NORDESTE', 15));
        $this->add(95, 100, $this->getDataRemessa('dmy'));
        $this->add(101, 394, '');
        $this->add(395, 400, Util::formatCnab('9', 1, 6));

        return $this;
    }

    /**
     * @param BoletoContract $boleto
     *
     * @return $this
     * @throws \Exception
     */
    public function addBoleto(BoletoContract $boleto)
    {
        $this->boletos[] = $boleto;
        $this->iniciaDetalhe();
        $this->add(1, 1, '1');
        $this->add(2, 17, '');
        $this->add(18, 21, Util::formatCnab('9', $this->getAgencia(), 4));
        $this->add(22, 23, '00');
        $this->add(24, 30, Util::formatCnab('9', $this->getConta(), 7));
        $this->add(31, 31, $this->getContaDv() ?: CalculoDV::bnbContaCorrente($this->getAgencia(), $this->getConta()));
        $this->add(32, 33, Util::formatCnab('9', round($boleto->getMulta()), 2)); // Só aceita números inteiros
        $this->add(34, 37, '');
        $this->add(38, 62, Util::formatCnab('X', $boleto->getNumeroControle(), 25)); // Numero de controle
        $this->add(63, 70, Util::formatCnab('9', $boleto->getNossoNumero(), 8));
        $this->add(71, 80, '0000000000');
        $this->add(81, 86, '000000'); // Data segundo desconto
        $this->add(87, 99, Util::formatCnab('9', '0', 13)); // Segundo desconto
        $this->add(100, 107, '');
        $this->add(108, 108, Util::formatCnab('9', $this->getCarteiraNumero(), 1));
        $this->add(109, 110, self::OCORRENCIA_REMESSA); // REGISTRO
        if ($boleto->getStatus() == $boleto::STATUS_BAIXA) {
            $this->add(109, 110, self::OCORRENCIA_PEDIDO_BAIXA); // BAIXA
        }
        if ($boleto->getStatus() == $boleto::STATUS_ALTERACAO) {
            $this->add(109, 110, self::OCORRENCIA_ALT_VENCIMENTO); // ALTERAR VENCIMENTO
        }
        if ($boleto->getStatus() == $boleto::STATUS_ALTERACAO_DATA) {
            $this->add(109, 110, self::OCORRENCIA_ALT_VENCIMENTO);
        }
        if ($boleto->getStatus() == $boleto::STATUS_CUSTOM) {
            $this->add(109, 110, sprintf('%2.02s', $boleto->getComando()));
        }
        $this->add(111, 120, Util::formatCnab('X', $boleto->getNumeroDocumento(), 10));
        $this->add(121, 126, $boleto->getDataVencimento()->format('dmy'));
        $this->add(127, 139, Util::formatCnab('9', $boleto->getValor(), 13));
        $this->add(140, 142, $this->getCodigoBanco());
        $this->add(143, 146, '0000');
        $this->add(147, 147, '');
        $this->add(148, 149, $boleto->getEspecieDocCodigo());
        $this->add(150, 150, $boleto->getAceite());
        $this->add(151, 156, $boleto->getDataDocumento()->format('dmy'));
        $this->add(157, 160, Util::formatCnab('9', self::INSTRUCAO_SEM, 4));
        $this->add(161, 173, Util::formatCnab('9', $boleto->getMoraDia(), 13, 2));
        $this->add(174, 179, $boleto->getDesconto() > 0 ? $boleto->getDataDesconto()->format('dmy') : '000000');
        $this->add(180, 192, Util::formatCnab('9', $boleto->getDesconto(), 13, 2));
        $this->add(193, 205, Util::formatCnab('9', 0, 13, 2));
        $this->add(206, 218, Util::formatCnab('9', 0, 13, 2));
        $this->add(219, 220, strlen(Util::onlyNumbers($boleto->getPagador()->getDocumento())) == 14 ? '02' : '01');
        $this->add(221, 234, Util::formatCnab('9L', $boleto->getPagador()->getDocumento(), 14));
        $this->add(235, 274, Util::formatCnab('X', $boleto->getPagador()->getNome(), 40));
        $this->add(275, 314, Util::formatCnab('X', $boleto->getPagador()->getEndereco(), 40));
        $this->add(315, 326, Util::formatCnab('X', $boleto->getPagador()->getBairro(), 12));
        $this->add(327, 331, Util::formatCnab('9', substr(Util::numbersOnly($boleto->getPagador()->getCep()), 0, 5), 5));
        $this->add(332, 334, Util::formatCnab('9', substr(Util::numbersOnly($boleto->getPagador()->getCep()), 5, 3), 3));
        $this->add(335, 349, Util::formatCnab('X', $boleto->getPagador()->getCidade(), 15));
        $this->add(350, 351, Util::formatCnab('X', $boleto->getPagador()->getUf(), 2));
        $this->add(352, 391, Util::formatCnab('X', $boleto->getSacadorAvalista() ? $boleto->getSacadorAvalista()->getNome() : '', 40));
        $this->add(392, 393, Util::formatCnab('9', '99', 2));
        if ($boleto->getDiasProtesto() > 0) {
            $this->add(392, 393, Util::formatCnab('9', $boleto->getDiasProtesto(), 2));
        }
        $this->add(394, 394, '0');
        $this->add(395, 400, Util::formatCnab('9', $this->iRegistros + 1, 6));

        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    protected function trailer()
    {
        $this->iniciaTrailer();

        $this->add(1, 1, '9');
        $this->add(2, 394, '');
        $this->add(395, 400, Util::formatCnab('9', $this->getCount(), 6));

        return $this;
    }
}
