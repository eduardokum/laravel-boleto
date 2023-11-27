<?php

namespace Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400\Banco;

use Eduardokum\LaravelBoleto\Util;
use Eduardokum\LaravelBoleto\CalculoDV;
use Eduardokum\LaravelBoleto\Exception\ValidationException;
use Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400\AbstractRemessa;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;
use Eduardokum\LaravelBoleto\Contracts\Cnab\Remessa as RemessaContract;

class Itau extends AbstractRemessa implements RemessaContract
{
    const ESPECIE_DUPLICATA = '01';
    const ESPECIE_NOTA_PROMISSORIA = '02';
    const ESPECIE_NOTA_SEGURO = '03';
    const ESPECIE_MENSALIDADE_ESCOLAR = '04';
    const ESPECIE_RECIBO = '05';
    const ESPECIE_CONTRATO = '06';
    const ESPECIE_COSSEGUROS = '07';
    const ESPECIE_DUPLICATA_SERVICO = '08';
    const ESPECIE_LETRA_CAMBIO = '09';
    const ESPECIE_NOTA_DEBITOS = '13';
    const ESPECIE_DOCUMENTO_DIVIDA = '15';
    const ESPECIE_ENCARGOS_CONDOMINIAIS = '16';
    const ESPECIE_NOTA_SERVICOS = '17';
    const ESPECIE_DIVERSOS = '99';
    const OCORRENCIA_REMESSA = '01';
    const OCORRENCIA_PEDIDO_BAIXA = '02';
    const OCORRENCIA_CONCESSAO_ABATIMENTO = '04';
    const OCORRENCIA_CANC_ABATIMENTO = '05';
    const OCORRENCIA_ALT_VENCIMENTO = '06';
    const OCORRENCIA_ALT_CONTROLE_PARTICIPANTE = '07';
    const OCORRENCIA_ALT_SEUNUMERO = '08';
    const OCORRENCIA_PROTESTAR = '09';
    const OCORRENCIA_NAO_PROTESTAR = '10';
    const OCORRENCIA_PROTESTO_FALIMENTARES = '11';
    const OCORRENCIA_SUSTAR_PROTESTO = '18';
    const OCORRENCIA_EXCL_AVALISTA = '30';
    const OCORRENCIA_ALT_OUTROS_DADOS = '31';
    const OCORRENCIA_BAIXA_PAGO_DIRETAMENTE = '34';
    const OCORRENCIA_CANC_INSTRUCAO = '35';
    const OCORRENCIA_ALT_VENC_SUSTAR_PROTESTO = '37';
    const OCORRENCIA_NAO_CONCORDA_SACADO = '38';
    const OCORRENCIA_DISPENSA_JUROS = '47';
    const OCORRENCIA_REMESSA_PIX = '71';
    const INSTRUCAO_SEM = '00';
    const INSTRUCAO_DEVOL_VENC_5 = '02';
    const INSTRUCAO_DEVOL_VENC_30 = '03';
    const INSTRUCAO_RECEBER_CONFORME_TITULO = '05';
    const INSTRUCAO_DEVOL_VENC_10 = '06';
    const INSTRUCAO_DEVOL_VENC_15 = '07';
    const INSTRUCAO_DEVOL_VENC_20 = '08';
    const INSTRUCAO_PROTESTAR_VENC_XX = '09';
    const INSTRUCAO_NAO_PROTESTAR = '10';
    const INSTRUCAO_DEVOL_VENC_25 = '11';
    const INSTRUCAO_DEVOL_VENC_35 = '12';
    const INSTRUCAO_DEVOL_VENC_40 = '13';
    const INSTRUCAO_DEVOL_VENC_45 = '14';
    const INSTRUCAO_DEVOL_VENC_50 = '15';
    const INSTRUCAO_DEVOL_VENC_55 = '16';
    const INSTRUCAO_DEVOL_VENC_60 = '17';
    const INSTRUCAO_DEVOL_VENC_90 = '18';
    const INSTRUCAO_NAO_RECEBER_VENC_05 = '19';
    const INSTRUCAO_NAO_RECEBER_VENC_10 = '20';
    const INSTRUCAO_NAO_RECEBER_VENC_15 = '21';
    const INSTRUCAO_NAO_RECEBER_VENC_20 = '22';
    const INSTRUCAO_NAO_RECEBER_VENC_25 = '23';
    const INSTRUCAO_NAO_RECEBER_VENC_30 = '24';
    const INSTRUCAO_NAO_RECEBER_VENC_35 = '25';
    const INSTRUCAO_NAO_RECEBER_VENC_40 = '26';
    const INSTRUCAO_NAO_RECEBER_VENC_45 = '27';
    const INSTRUCAO_NAO_RECEBER_VENC_50 = '28';
    const INSTRUCAO_NAO_RECEBER_VENC_55 = '29';
    const INSTRUCAO_DESCONTO_DIA = '30';
    const INSTRUCAO_NAO_RECEBER_VENC_60 = '31';
    const INSTRUCAO_NAO_RECEBER_VENC_90 = '32';
    const INSTRUCAO_CONCEDER_ABATIMENTO_VENCIDO = '33';
    const INSTRUCAO_PROTESTAR_VENC_XX_S_AVISO = '34';
    const INSTRUCAO_PROTESTAR_VENC_XX_UTEIS_S_AVISO = '35';
    const INSTRUCAO_RECEBER_ULT_DIA_MES_VENC = '37';
    const INSTRUCAO_CONCEDER_DESC_VENC = '38';
    const INSTRUCAO_NAO_RECEBER_VENC = '39';
    const INSTRUCAO_CONCEDER_DESC_NOTA_CRED = '40';
    const INSTRUCAO_PROTESTO_FALIMENTARES = '42';
    const INSTRUCAO_SUJEITO_PROTESTO_NAO_VENC = '43';
    const INSTRUCAO_PAGTO_ATRASO_APOS_DDMMAA = '44';
    const INSTRUCAO_DIA_GRACAO = '45';
    const INSTRUCAO_DISPENSAR_JUROS = '47';
    const INSTRUCAO_RECEBER_ANT_QUITADA = '51';
    const INSTRUCAO_PAGTO_SOMENTE_BOLETO_BANCO = '52';
    const INSTRUCAO_VENC_PAGTO_EMPRESA = '54';
    const INSTRUCAO_VALOR_SOMA_MORA = '57';
    const INSTRUCAO_DEVOL_VENC_365 = '58';
    const INSTRUCAO_PAGTO_BANCO = '59';
    const INSTRUCAO_ENTREGUE_PENHOR = '61';
    const INSTRUCAO_TRANSFERIDO = '62';
    const INSTRUCAO_VALOR_PRORATA_10 = '78';
    const INSTRUCAO_JUROS_VENC_15 = '79';
    const INSTRUCAO_PAGTO_CHEQUE = '80';
    const INSTRUCAO_OPERACAO_VENDOR = '83';
    const INSTRUCAO_AG_CEDENTE_APOS_VENC = '84';
    const INSTRUCAO_ANTES_VENC_APOS_15_SEDE = '86';
    const INSTRUCAO_NAO_RECEBER_ANTES_VENC = '88';
    const INSTRUCAO_VENC_QLQ_AG = '90';
    const INSTRUCAO_NAO_RECEBER_VENC_XX = '91';
    const INSTRUCAO_DEVOL_VENC_XX = '92';
    const INSTRUCAO_MSG_30_POS = '93';
    const INSTRUCAO_MSG_40_POS = '94';

    /**
     * Código do banco
     *
     * @var string
     */
    protected $codigoBanco = BoletoContract::COD_BANCO_ITAU;

    /**
     * Define as carteiras disponíveis para cada banco
     *
     * @var array
     */
    protected $carteiras = ['112', '115', '188', '109', '121', '175'];

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
     * @return Itau
     * @throws ValidationException
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
        $this->add(33, 37, Util::formatCnab('9', $this->getConta(), 5));
        $this->add(38, 38, ! is_null($this->getContaDv()) ? $this->getContaDv() : CalculoDV::itauContaCorrente($this->getAgencia(), $this->getConta()));
        $this->add(39, 46, '');
        $this->add(47, 76, Util::formatCnab('X', $this->getBeneficiario()->getNome(), 30));
        $this->add(77, 79, $this->getCodigoBanco());
        $this->add(80, 94, Util::formatCnab('X', 'BANCO ITAU SA', 15));
        $this->add(95, 100, $this->getDataRemessa('dmy'));
        $this->add(101, 394, '');
        $this->add(395, 400, Util::formatCnab('9', 1, 6));

        return $this;
    }

    /**
     * @param \Eduardokum\LaravelBoleto\Boleto\Banco\Itau $boleto
     *
     * @return Itau
     * @throws ValidationException
     */
    public function addBoleto(BoletoContract $boleto)
    {
        $this->boletos[] = $boleto;
        if ($chaveNfe = $boleto->getChaveNfe()) {
            $this->iniciaDetalheExtendido();
        } else {
            $this->iniciaDetalhe();
        }

        $pix = $boleto->validarPix();

        $this->add(1, 1, '1');
        $this->add(2, 3, strlen(Util::onlyNumbers($this->getBeneficiario()->getDocumento())) == 14 ? '02' : '01');
        $this->add(4, 17, Util::formatCnab('9', Util::onlyNumbers($this->getBeneficiario()->getDocumento()), 14));
        $this->add(18, 21, Util::formatCnab('9', $this->getAgencia(), 4));
        $this->add(22, 23, '00');
        $this->add(24, 28, Util::formatCnab('9', $this->getConta(), 5));
        $this->add(29, 29, ! is_null($this->getContaDv()) ? $this->getContaDv() : CalculoDV::itauContaCorrente($this->getAgencia(), $this->getConta()));
        $this->add(30, 33, '');
        $this->add(34, 37, '0000');
        $this->add(38, 62, Util::formatCnab('X', $boleto->getNumeroControle(), 25)); // numero de controle
        $this->add(63, 70, Util::formatCnab('9', substr($boleto->getNossoNumero(), 0, -1), 8));
        $this->add(71, 83, Util::formatCnab('9', '0', 13, 2));
        $this->add(84, 86, Util::formatCnab('9', $this->getCarteiraNumero(), 3));
        $this->add(87, 107, '');
        $this->add(108, 108, 'I');
        $this->add(109, 110, $pix ? self::OCORRENCIA_REMESSA_PIX : self::OCORRENCIA_REMESSA); // REGISTRO ou REGISTRO PIX
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
        $this->add(127, 139, Util::formatCnab('9', $boleto->getValor(), 13, 2));
        $this->add(140, 142, $this->getCodigoBanco());
        $this->add(143, 147, '00000');
        $this->add(148, 149, $boleto->getEspecieDocCodigo());
        $this->add(150, 150, $boleto->getAceite());
        $this->add(151, 156, $boleto->getDataDocumento()->format('dmy'));
        $this->add(157, 158, self::INSTRUCAO_SEM);
        $this->add(159, 160, self::INSTRUCAO_VALOR_SOMA_MORA);
        if ($boleto->getDiasProtesto() > 0) {
            $this->add(157, 158, self::INSTRUCAO_PROTESTAR_VENC_XX);
        } elseif ($boleto->getDiasBaixaAutomatica() > 0) {
            $this->add(157, 158, self::INSTRUCAO_DEVOL_VENC_XX);
        }
        $this->add(161, 173, Util::formatCnab('9', $boleto->getMoraDia(), 13, 2));
        $this->add(174, 179, $boleto->getDesconto() > 0 ? $boleto->getDataDesconto()->format('dmy') : '000000');
        $this->add(180, 192, Util::formatCnab('9', $boleto->getDesconto(), 13, 2));
        $this->add(193, 205, Util::formatCnab('9', 0, 13, 2));
        $this->add(206, 218, Util::formatCnab('9', 0, 13, 2));
        $this->add(219, 220, strlen(Util::onlyNumbers($boleto->getPagador()->getDocumento())) == 14 ? '02' : '01');
        $this->add(221, 234, Util::formatCnab('9', Util::onlyNumbers($boleto->getPagador()->getDocumento()), 14));
        $this->add(235, 264, Util::formatCnab('X', $boleto->getPagador()->getNome(), 30));
        $this->add(265, 274, '');
        $this->add(275, 314, Util::formatCnab('X', $boleto->getPagador()->getEndereco(), 40));
        $this->add(315, 326, Util::formatCnab('X', $boleto->getPagador()->getBairro(), 12));
        $this->add(327, 334, Util::formatCnab('9', Util::onlyNumbers($boleto->getPagador()->getCep()), 8));
        $this->add(335, 349, Util::formatCnab('X', $boleto->getPagador()->getCidade(), 15));
        $this->add(350, 351, Util::formatCnab('X', $boleto->getPagador()->getUf(), 2));
        $this->add(352, 381, Util::formatCnab('X', $boleto->getSacadorAvalista() ? $boleto->getSacadorAvalista()->getNome() : '', 30));
        $this->add(382, 385, '');
        $this->add(386, 391, $boleto->getJurosApos() === false ? '000000' : $boleto->getDataVencimento()->copy()->addDays($boleto->getJurosApos())->format('dmy'));
        $this->add(392, 393, Util::formatCnab('9', $boleto->getDiasProtesto($boleto->getDiasBaixaAutomatica()), 2));
        $this->add(394, 394, '');
        $this->add(395, 400, Util::formatCnab('9', $this->iRegistros + 1, 6));
        if ($chaveNfe) {
            $this->add(401, 444, Util::formatCnab('9', $chaveNfe, 44));
        }

        // Verifica multa
        if ($boleto->getMulta() > 0) {
            // Inicia uma nova linha de detalhe e marca com a atual de edição
            $this->iniciaDetalhe();
            // Campo adicional para a multa
            $this->add(1, 1, 2); // Adicional Multa
            $this->add(2, 2, 2); // Cód 2 = Informa Valor em percentual
            $this->add(3, 10, $boleto->getDataVencimento()->format('dmY')); // Data da multa
            $this->add(11, 23, Util::formatCnab('9', Util::nFloat($boleto->getMulta(), 2), 13));
            $this->add(24, 394, '');
            $this->add(395, 400, Util::formatCnab('9', $this->iRegistros + 1, 6));
        }

        if ($pix) {
            $this->iniciaDetalhe();
            $this->add(1, 1, '3');
            $this->add(2, 78, Util::formatCnab('X', $boleto->getPixChave(), 77));
            $this->add(79, 142, Util::formatCnab('X', $boleto->getID(), 35));
            $this->add(143, 394, '');
            $this->add(395, 400, Util::formatCnab('9', $this->iRegistros + 1, 6));
        }

        return $this;
    }

    /**
     * @return Itau
     * @throws ValidationException
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
