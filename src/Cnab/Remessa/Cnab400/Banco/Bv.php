<?php

namespace Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400\Banco;

use Eduardokum\LaravelBoleto\Util;
use Eduardokum\LaravelBoleto\Pessoa;
use Eduardokum\LaravelBoleto\Exception\ValidationException;
use Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400\AbstractRemessa;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;
use Eduardokum\LaravelBoleto\Contracts\Cnab\Remessa as RemessaContract;

class Bv extends AbstractRemessa implements RemessaContract
{
    // Espécies
    const ESPECIE_DUPLICATA = '01';
    const ESPECIE_DUPLICATA_SERVICO = '08';
    const ESPECIE_CARTAO = '31';
    const ESPECIE_DIV_ATV_MUNICIPIO = '27';

    // Ocorrências
    const OCORRENCIA_REMESSA = '48';
    const OCORRENCIA_REMESSA_ESCRITURAL = '01';
    const OCORRENCIA_PEDIDO_BAIXA = '02';
    const OCORRENCIA_CONCESSAO_ABATIMENTO = '04';
    const OCORRENCIA_PRORROGAR_VENCIMENTO = '06';
    const OCORRENCIA_ALT_VALOR = '07';
    const OCORRENCIA_ALT_VENCIMENTO = '08';
    const OCORRENCIA_NAO_BAIXAR_AUTOMATICO = '09';
    const OCORRENCIA_NAO_PROTESTAR = '10';
    const OCORRENCIA_ALT_DIAS_BAIXA = '11';
    const OCORRENCIA_ALT_QTDE_PARCELAS = '15';
    const OCORRENCIA_SUSTAR_PROTESTO = '18';
    const OCORRENCIA_PEDIDO_PROTESTO = '36';

    // Instruções
    const INSTRUCAO_SEM = '00';
    const INSTRUCAO_PROTESTAR = '81';
    const INSTRUCAO_NAO_PROTESTAR = '84';
    const INSTRUCAO_BAIXAR = '92';
    const INSTRUCAO_NAO_BAIXAR = '93';
    const INSTRUCAO_NAO_COBRAR_JUROS = '94';

    public function __construct(array $params = [])
    {
        parent::__construct($params);
        $this->addCampoObrigatorio('convenio');
    }

    /**
     * Código do banco
     *
     * @var string
     */
    protected $codigoBanco = BoletoContract::COD_BANCO_BV;

    /**
     * Define as carteiras disponíveis para cada banco
     *
     * @var array
     */
    protected $carteiras = [1, 200, 300, 400, 500];

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
     * @return mixed
     */
    public function getConvenio()
    {
        return $this->convenio;
    }

    /**
     * @param mixed $convenio
     *
     * @return Bv
     */
    public function setConvenio($convenio)
    {
        $this->convenio = ltrim($convenio, 0);

        return $this;
    }

    /**
     * @return Bv
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
        $this->add(27, 46, '');
        $this->add(47, 76, Util::formatCnab('X', $this->getBeneficiario()->getNome(), 30));
        $this->add(77, 79, $this->getCodigoBanco());
        $this->add(80, 99, 'BANCO VOTORANTIM S/A');
        $this->add(100, 105, $this->getDataRemessa('dmy'));
        $this->add(106, 389, '');
        $this->add(390, 394, Util::formatCnab('X', 'CL001', 5));
        $this->add(395, 400, Util::formatCnab('9', 1, 6));

        return $this;
    }

    /**
     * @param \Eduardokum\LaravelBoleto\Boleto\Banco\Bb $boleto
     *
     * @return Bv
     * @throws ValidationException
     */
    public function addBoleto(BoletoContract $boleto)
    {
        $this->boletos[] = $boleto;
        $this->iniciaDetalhe();

        $this->add(1, 1, 1);
        if ($this->getBeneficiario()->getTipo() == Pessoa::TIPO_BENEFICIARIO) {
            $this->add(2, 3, Util::isCnpj($this->getBeneficiario()->getDocumento()) ? '02' : '01');
        } elseif ($this->getBeneficiario()->getTipo() == Pessoa::TIPO_SACADOR) {
            $this->add(2, 3, Util::isCnpj($this->getBeneficiario()->getDocumento()) ? '04' : '03');
        } else {
            throw new ValidationException('Tipo de beneficiário inválido');
        }
        $this->add(4, 17, Util::formatCnabDocumento($this->getBeneficiario()->getDocumento(), 14));
        $this->add(18, 19, '00');
        $this->add(20, 29, Util::formatCnab('9', $this->getConvenio(), 10));
        $this->add(30, 37, ''); // Número do Contrato Externo
        $this->add(38, 62, Util::formatCnab('X', $boleto->getNumeroControle(), 25)); // numero de controle
        $this->add(63, 72, $boleto->getNossoNumero());
        $this->add(73, 75, $this->getCarteira());
        $this->add(76, 77, self::OCORRENCIA_REMESSA); // REGISTRO
        if ($boleto->getStatus() == $boleto::STATUS_BAIXA) {
            $this->add(76, 77, self::OCORRENCIA_PEDIDO_BAIXA); // BAIXA
        }
        if ($boleto->getStatus() == $boleto::STATUS_ALTERACAO) {
            $this->add(76, 77, self::OCORRENCIA_ALT_VENCIMENTO); // ALTERAR VENCIMENTO
        }
        if ($boleto->getStatus() == $boleto::STATUS_ALTERACAO_DATA) {
            $this->add(76, 77, self::OCORRENCIA_ALT_VENCIMENTO);
        }
        if ($boleto->getStatus() == $boleto::STATUS_CUSTOM) {
            $this->add(76, 77, sprintf('%2.02s', $boleto->getComando()));
        }
        $this->add(78, 87, Util::formatCnab('X', $boleto->getNumeroDocumento(), 10));
        $this->add(88, 93, $boleto->getDataVencimento()->format('dmy'));
        $this->add(94, 106, Util::formatCnab('9', $boleto->getValor(), 13, 2));
        $this->add(107, 109, $this->getCodigoBanco());
        $this->add(110, 114, '00001');
        $this->add(115, 116, $boleto->getEspecieDocCodigo('01', 400));
        $this->add(117, 117, $boleto->getAceite());
        $this->add(118, 123, $boleto->getDataDocumento()->format('dmy'));
        $this->add(124, 125, self::INSTRUCAO_SEM);
        $diasAux = '00';
        if ($boleto->getDiasProtesto() > 0) {
            $this->add(124, 125, self::INSTRUCAO_PROTESTAR);
            $diasAux = Util::formatCnab('9', $boleto->getDiasProtesto(), 2, 0);
        } elseif ($boleto->getDiasBaixaAutomatica() > 0) {
            $this->add(124, 125, self::INSTRUCAO_BAIXAR);
            $diasAux = Util::formatCnab('9', $boleto->getDiasProtesto(), 2, 0);
        }
        $this->add(126, 127, self::INSTRUCAO_SEM);
        $this->add(128, 136, '');
        $this->add(137, 137, ''); // Branco Percentual por dia ou Acatar parâmetro do convênio, 0 Acatar parâmetro do convênio, 1 Percentual por dia, 2 Percentual Mensal, 3 Isento, 4 Valor ao Dia, 5 Valor ao Mês
        $this->add(138, 150, Util::formatCnab('9', $boleto->getMoraDia(), 13, 2));
        $this->add(151, 156, $boleto->getDesconto() > 0 ? $boleto->getDataDesconto()->format('dmy') : '000000');
        $this->add(157, 169, Util::formatCnab('9', $boleto->getDesconto(), 13, 2));
        $this->add(170, 170, '0'); // Desconto -  0 Percentual, 1 Valor
        $this->add(171, 182, Util::formatCnab('9', 0, 12, 2));
        $this->add(183, 195, Util::formatCnab('9', 0, 13, 2));
        $this->add(196, 197, Util::isCnpj($boleto->getPagador()->getDocumento()) ? '02' : '01');
        $this->add(198, 211, Util::formatCnabDocumento($boleto->getPagador()->getDocumento(), 14));
        $this->add(212, 251, Util::formatCnab('X', $boleto->getPagador()->getNome(), 40));
        $this->add(252, 288, Util::formatCnab('X', $boleto->getPagador()->getEndereco(), 37));
        $this->add(289, 291, '');
        $this->add(292, 303, Util::formatCnab('X', $boleto->getPagador()->getBairro(), 12));
        $this->add(304, 311, Util::formatCnab('9L', $boleto->getPagador()->getCep(), 8));
        $this->add(312, 326, Util::formatCnab('X', $boleto->getPagador()->getCidade(), 15));
        $this->add(327, 328, Util::formatCnab('X', $boleto->getPagador()->getUf(), 2));
        $this->add(329, 368, Util::formatCnab('X', $boleto->getSacadorAvalista() ? $boleto->getSacadorAvalista()->getNome() : '', 40));
        $this->add(369, 374, $boleto->getMultaApos() === false ? $boleto->getDataVencimento()->copy()->format('dmy') : $boleto->getDataVencimento()->copy()->addDays($boleto->getMultaApos())->format('dmy'));
        $this->add(375, 376, $diasAux);
        $this->add(377, 377, '0');
        $this->add(378, 395, '');
        $this->add(395, 400, Util::formatCnab('9', $this->iRegistros + 1, 6));

        if ($boleto->getMulta() > 0) {
            $this->iniciaDetalhe();

            $this->add(1, 1, 2);

            // Feito ate /\
            $this->add(2, 2, '2'); // 0 Não Registra a Multa, 2 Percentual, 3 Acatar parâmetro do convênio, 4 Valor da Multa
            $this->add(3, 10, $boleto->getMultaApos() === false ? $boleto->getDataVencimento()->copy()->format('dmy') : $boleto->getDataVencimento()->copy()->addDays($boleto->getMultaApos())->format('dmy'));
            $this->add(11, 23, Util::formatCnab('9', $boleto->getMulta(), 13, 2));
            $this->add(24, 24, '');
            $this->add(25, 25, '0'); // A 0 Acatar parâmetro do convênio, 1 Qualquer Valor , 2 Mínimo e Máximo, 3 Não Aceita Divergente, 4 Somente Valor Mínimo
            $this->add(26, 37, '');
            $this->add(38, 38, '0');
            $this->add(39, 50, '');
            $this->add(51, 51, '0');
            $this->add(52, 53, '00'); // Ao informar “00” será acatado o parâmetro do convênio
            $this->add(54, 394, '');
            $this->add(395, 400, Util::formatCnab('9', $this->iRegistros + 1, 6));
        }

        return $this;
    }

    /**
     * @return Bv
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
