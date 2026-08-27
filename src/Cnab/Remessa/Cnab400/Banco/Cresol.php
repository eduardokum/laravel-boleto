<?php

namespace Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400\Banco;

use Eduardokum\LaravelBoleto\Util;
use Eduardokum\LaravelBoleto\CalculoDV;
use Eduardokum\LaravelBoleto\Exception\ValidationException;
use Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400\AbstractRemessa;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;
use Eduardokum\LaravelBoleto\Contracts\Cnab\Remessa as RemessaContract;
use Eduardokum\LaravelBoleto\Cnab\Remessa\Traits\ValidacoesCresol;

class Cresol extends AbstractRemessa implements RemessaContract
{
    use ValidacoesCresol;

    /**
     * Espécies de título aceitas no registro detalhe, pos. 148-149 do manual
     * "Padrão Remessa CNAB400 Cresol 133".
     */
    const ESPECIE_CHEQUE = '01';
    const ESPECIE_DUPLICATA_MERCANTIL = '02';
    const ESPECIE_DUPLICATA_SERVICO = '04';
    const ESPECIE_DUPLICATA_RURAL = '06';
    const ESPECIE_LETRAS_CAMBIO = '07';
    const ESPECIE_NOTA_PROMISSORIA = '12';
    const ESPECIE_RECIBO = '17';
    const ESPECIE_NOTA_DEBITO = '19';
    const ESPECIE_WARRANT = '26';
    const ESPECIE_DIVIDA_ATIVA_ESTADO = '27';
    const ESPECIE_DIVIDA_ATIVA_MUNICIPIO = '28';
    const ESPECIE_DIVIDA_ATIVA_UNIAO = '29';
    const ESPECIE_ENCARGOS_CONDOMINIAIS = '30';
    const ESPECIE_OUTROS = '99';

    /**
     * Identificação da ocorrência, pos. 109-110 do mesmo manual. A Cresol não
     * prevê os demais códigos do padrão FEBRABAN/Bradesco nesse layout.
     */
    const OCORRENCIA_REMESSA = '01';
    const OCORRENCIA_PEDIDO_BAIXA = '02';
    const OCORRENCIA_CONCESSAO_ABATIMENTO = '04';
    const OCORRENCIA_CANC_ABATIMENTO_CONCEDIDO = '05';
    const OCORRENCIA_ALT_VENCIMENTO = '06';
    const OCORRENCIA_PEDIDO_PROTESTO = '09';
    const OCORRENCIA_SUSTAR_PROTESTO_BAIXAR_TITULO = '10';
    const OCORRENCIA_SUSTAR_PROTESTO_MANTER_TITULO = '11';
    const OCORRENCIA_ALT_OUTROS_DADOS = '31';

    // As posições 157-158 e 159-160 (primeira e segunda instrução) são "Branco"
    // no manual da Cresol, por isso não há constantes de instrução aqui.

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
    protected $codigoBanco = BoletoContract::COD_BANCO_CRESOL;

    /**
     * Define as carteiras disponíveis para cada banco
     *
     * @var array
     */
    protected $carteiras = ['09'];

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
     * Codigo do cliente junto ao banco.
     *
     * @var string
     */
    protected $codigoCliente;

    /**
     * Retorna o codigo do cliente.
     *
     * @return mixed
     * @throws \Exception
     */
    public function getCodigoCliente()
    {
        if (empty($this->codigoCliente)) {
            $this->codigoCliente = Util::formatCnab('9', $this->getCarteiraNumero(), 4) .
            Util::formatCnab('9', $this->getAgencia(), 5) .
            Util::formatCnab('9', $this->getConta(), 7) .
            Util::formatCnab('9', $this->getContaDv() ?: CalculoDV::cresolContaCorrente($this->getConta()), 1);
        }

        return $this->codigoCliente;
    }

    /**
     * Seta o codigo do cliente.
     *
     * @param mixed $codigoCliente
     *
     * @return Cresol
     */
    public function setCodigoCliente($codigoCliente)
    {
        $this->codigoCliente = $codigoCliente;

        return $this;
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
        $this->add(27, 46, Util::formatCnab('9', $this->getCodigoCliente(), 20));
        // $this->add(47, 76, Util::formatCnab('X', '', 30));
        $this->add(47, 76, '');
        $this->add(77, 79, $this->getCodigoBanco());
        $this->add(80, 94, Util::formatCnab('X', 'Cresol', 15));
        $this->add(95, 100, '');
        $this->add(101, 108, '');
        $this->add(109, 110, '');
        $this->add(111, 117, '');
        $this->add(118, 394, '');
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
        // O CNAB 400 da Cresol não carrega instrução de protesto; ela só pode ser feita
        // pela tela do portal. Falhar aqui evita que a instrução seja perdida em silêncio.
        if ($boleto->getDiasProtesto() > 0) {
            throw new ValidationException('O CNAB 400 da Cresol não suporta instrução de protesto. Use o CNAB 240 (segmento P) ou registre o protesto pela tela do portal.');
        }

        $this->validaFaixaNossoNumero($boleto);
        $this->validaMultaPercentual($boleto);

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
        $this->add(37, 37, Util::formatCnab('9', $this->getContaDv() ?: CalculoDV::cresolContaCorrente($this->getConta()), 1));
        $this->add(38, 62, Util::formatCnab('X', $boleto->getNumeroControle(), 25)); // numero de controle
        $this->add(63, 65, '');
        $this->add(66, 66, $boleto->getMulta() > 0 ? '2' : '0');
        $this->add(67, 70, Util::formatCnab('9', $boleto->getMulta() > 0 ? $boleto->getMulta() : '0', 4, 2));
        // 71-81 é numérico e 82 é alfanumérico, pois o dígito pode ser a letra "P"
        $this->add(71, 81, Util::formatCnab('9', substr($boleto->getNossoNumero(), 0, 11), 11));
        $this->add(82, 82, Util::formatCnab('X', substr($boleto->getNossoNumero(), -1), 1));
        $this->add(83, 92, '');
        $this->add(93, 93, '2'); // 1 = Banco emite e Processa o registro. 2 = Cliente emite e o Banco somente processa o registro
        $this->add(94, 94, ''); // N= Não registra na cobrança. Diferente de N registra e emite Boleto.
        $this->add(95, 104, '');
        $this->add(105, 105, '');
        $this->add(106, 106, '');
        $this->add(107, 108, '');
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
        $this->add(140, 142, '');
        $this->add(143, 147, '');
        $this->add(148, 149, $boleto->getEspecieDocCodigo('99', 400));
        $this->add(150, 150, '');
        $this->add(151, 156, $boleto->getDataDocumento()->format('dmy'));
        $this->add(157, 158, '');
        $this->add(159, 160, '');
        $this->add(161, 173, Util::formatCnab('9', $boleto->getMoraDia(), 13, 2));
        $valorDescontoAbs = $this->resolveValorDescontoAbsoluto($boleto);
        $this->add(174, 179, $valorDescontoAbs > 0 && $boleto->getDataDesconto() ? $boleto->getDataDesconto()->format('dmy') : '000000');
        $this->add(180, 192, Util::formatCnab('9', $valorDescontoAbs, 13, 2));
        $this->add(193, 205, Util::formatCnab('9', 0, 13, 2));
        $this->add(206, 218, Util::formatCnab('9', 0, 13, 2));
        $this->add(219, 220, strlen(Util::onlyNumbers($boleto->getPagador()->getDocumento())) == 14 ? '02' : '01');
        $this->add(221, 234, Util::formatCnab('9', Util::onlyNumbers($boleto->getPagador()->getDocumento()), 14));
        $this->add(235, 274, Util::formatCnab('X', $boleto->getPagador()->getNome(), 40));
        $this->add(275, 314, Util::formatCnab('X', $boleto->getPagador()->getEndereco(), 40));
        $this->add(315, 326, '');
        $this->add(327, 334, Util::formatCnab('9', Util::onlyNumbers($boleto->getPagador()->getCep()), 8));
        $this->add(335, 394, '');
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
