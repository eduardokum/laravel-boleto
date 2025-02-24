<?php

namespace Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400\Banco;

use Eduardokum\LaravelBoleto\Util;
use Eduardokum\LaravelBoleto\CalculoDV;
use Eduardokum\LaravelBoleto\Exception\ValidationException;
use Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400\AbstractRemessa;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;
use Eduardokum\LaravelBoleto\Contracts\Cnab\Remessa as RemessaContract;

class Ourinvest extends AbstractRemessa implements RemessaContract
{
    const ESPECIE_DUPLICATA = '01';
    const ESPECIE_NOTA_PROMISSORIA = '02';
    const ESPECIE_DUPLICATA_SERVICO = '12';
    const ESPECIE_OUTROS = '99';
    const OCORRENCIA_REMESSA = '01';
    const OCORRENCIA_PEDIDO_BAIXA = '02';
    const OCORRENCIA_CONCESSAO_ABATIMENTO = '04';
    const OCORRENCIA_CANC_ABATIMENTO_CONCEDIDO = '05';
    const OCORRENCIA_ALT_VENCIMENTO = '06';
    const OCORRENCIA_PEDIDO_PROTESTO = '09';
    const OCORRENCIA_SUSTAR_PROTESTO_BAIXAR_TITULO = '18';
    const OCORRENCIA_SUSTAR_PROTESTO_MANTER_TITULO = '19';
    const OCORRENCIA_ALT_OUTROS_DADOS = '31';
    const INSTRUCAO_SEM = '00';

    public function __construct(array $params = [])
    {
        parent::__construct($params);
        $this->addCampoObrigatorio('idremessa');
    }

    /**
     * Código do banco
     *
     * @var string
     */
    protected $codigoBanco = BoletoContract::COD_BANCO_OURINVEST;

    /**
     * Define as carteiras disponíveis para cada banco
     *
     * @var array
     */
    protected $carteiras = false;

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
     * @return Ourinvest
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
        $this->add(27, 46, Util::formatCnab('9', $this->getConta(), 20));
        $this->add(47, 76, Util::formatCnab('X', $this->getBeneficiario()->getNome(), 30));
        $this->add(77, 79, $this->getCodigoBanco());
        $this->add(80, 94, Util::formatCnab('X', 'Banco Ourinvest', 15));
        $this->add(95, 100, $this->getDataRemessa('dmy'));
        $this->add(101, 108, '');
        $this->add(109, 110, 'MX');
        $this->add(111, 117, Util::formatCnab('9', $this->getIdremessa(), 7));
        $this->add(118, 394, '');
        $this->add(395, 400, Util::formatCnab('9', 1, 6));

        return $this;
    }

    /**
     * @param \Eduardokum\LaravelBoleto\Boleto\Banco\Bradesco $boleto
     *
     * @return Ourinvest
     * @throws ValidationException
     */
    public function addBoleto(BoletoContract $boleto)
    {
        $this->boletos[] = $boleto;
        $this->iniciaDetalhe();

        $this->add(1, 1, '1');
        $this->add(2, 6, '');
        $this->add(7, 7, '');
        $this->add(8, 12, '');
        $this->add(13, 19, '');
        $this->add(20, 20, '');
        $this->add(21, 21, '0');
        $this->add(22, 24, Util::formatCnab('9', $this->getCarteira(), 3));
        $this->add(25, 29, Util::formatCnab('9', $this->getAgencia(), 5));
        $this->add(30, 36, Util::formatCnab('9', $this->getConta(), 7));
        $this->add(37, 37, ! is_null($this->getContaDv()) ? $this->getContaDv() : CalculoDV::ourinvestConta($this->getConta()));
        $this->add(38, 62, Util::formatCnab('X', $boleto->getNumeroControle(), 25)); // numero de controle
        $this->add(63, 65, '000');
        $this->add(66, 66, $boleto->getMulta() > 0 ? '2' : '0');
        $this->add(67, 70, Util::formatCnab('9', $boleto->getMulta() > 0 ? $boleto->getMulta() : '0', 4, 2));
        $this->add(71, 82, Util::formatCnab('9', $boleto->getNossoNumero(), 12));
        $this->add(83, 92, Util::formatCnab('9', 0, 10, 2));
        $this->add(93, 93, $boleto->isEmissaoPropria() ? '2' : '1'); // 1 = Banco emite e Processa o registro. 2 = Cliente emite e o Banco somente processa o registro
        $this->add(94, 94, '');
        $this->add(95, 104, '');
        $this->add(105, 108, '');
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
        $this->add(127, 139, Util::formatCnab('9', $boleto->getValor(), 13, 2));
        $this->add(140, 142, '000');
        $this->add(143, 147, '00000');
        $this->add(148, 149, $boleto->getEspecieDocCodigo());
        $this->add(150, 150, 'N');
        $this->add(151, 156, $boleto->getDataDocumento()->format('dmy'));
        $this->add(157, 158, self::INSTRUCAO_SEM);
        $this->add(159, 160, self::INSTRUCAO_SEM);
        $this->add(161, 173, Util::formatCnab('9', $boleto->getMoraDia(), 13, 2));
        $this->add(174, 179, $boleto->getDesconto() > 0 ? $boleto->getDataDesconto()->format('dmy') : '000000');
        $this->add(180, 192, Util::formatCnab('9', $boleto->getDesconto(), 13, 2));
        $this->add(193, 205, Util::formatCnab('9', 0, 13, 2));
        $this->add(206, 218, Util::formatCnab('9', 0, 13, 2));
        $this->add(219, 220, strlen(Util::onlyNumbers($boleto->getPagador()->getDocumento())) == 14 ? '02' : '01');
        $this->add(221, 234, Util::formatCnab('9', Util::onlyNumbers($boleto->getPagador()->getDocumento()), 14));
        $this->add(235, 274, Util::formatCnab('X', $boleto->getPagador()->getNome(), 40));
        $this->add(275, 312, Util::formatCnab('X', $boleto->getPagador()->getEndereco(), 38));
        $this->add(313, 324, Util::formatCnab('X', $boleto->getPagador()->getCidade(), 12));
        $this->add(325, 326, Util::formatCnab('X', $boleto->getPagador()->getUf(), 2));
        $this->add(327, 334, Util::formatCnab('9', Util::onlyNumbers($boleto->getPagador()->getCep()), 8));
        $this->add(335, 394, Util::formatCnab('X', '', 60));
        if ($boleto->getSacadorAvalista()) {
            $this->add(335, 348, Util::formatCnab('9', Util::onlyNumbers($boleto->getSacadorAvalista()->getDocumento()), 14));
            $this->add(349, 350, Util::formatCnab('X', '', 2));
            $this->add(351, 394, Util::formatCnab('X', $boleto->getSacadorAvalista()->getNome(), 44));
        }
        $this->add(395, 400, Util::formatCnab('9', $this->iRegistros + 1, 6));
        if ($chaveNfe = $boleto->getChaveNfe()) {
            $this->iniciaDetalhe();
            $this->add(1, 1, '2');
            $this->add(82, 125, Util::formatCnab('9', $chaveNfe, 44));
            $this->add(242, 321, Util::formatCnab('X', $boleto->getPagador()->getEmail(), 80));
            $this->add(395, 400, Util::formatCnab('9', $this->iRegistros + 1, 6));
        }

        return $this;
    }

    /**
     * @return Ourinvest
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

    public function nomeSugerido()
    {
        //CBDDMM??.REM
        //CB – Cobrança Ourinvest
        //DD – O Dia geração do arquivo
        //MM – O Mês da geração do Arquivo
        //?? - variáveis alfanumérico-Numéricas Ex.:
        //01, AB, A1 etc.
        //.Rem – Extensão do arquivo.
        return sprintf('CB%02s%02s01.REM', date('d'), date('m'));
    }

    public function save($path, $suggestName = true)
    {
        return parent::save($path, $suggestName);
    }
}
