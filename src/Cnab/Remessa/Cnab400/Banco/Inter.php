<?php

namespace Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400\Banco;

use Eduardokum\LaravelBoleto\Util;
use Eduardokum\LaravelBoleto\Exception\ValidationException;
use Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400\AbstractRemessa;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;
use Eduardokum\LaravelBoleto\Contracts\Cnab\Remessa as RemessaContract;

class Inter extends AbstractRemessa implements RemessaContract
{
    const OCORRENCIA_REMESSA = '01';

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
    protected $codigoBanco = BoletoContract::COD_BANCO_INTER;

    /**
     * Define as carteiras disponíveis para cada banco
     *
     * @var array
     */
    protected $carteiras = ['112'];

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
     * @return Inter
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
        $this->add(80, 94, Util::formatCnab('X', 'Inter', 15));
        $this->add(95, 100, $this->getDataRemessa('dmy'));
        $this->add(101, 108, '');
        $this->add(109, 110, 'MX');
        $this->add(111, 117, Util::formatCnab('9', $this->getIdremessa(), 7));
        $this->add(118, 394, '');
        $this->add(395, 400, Util::formatCnab('9', 1, 6));

        return $this;
    }

    /**
     * @param \Eduardokum\LaravelBoleto\Boleto\Banco\Inter $boleto
     *
     * @return Inter
     * @throws ValidationException
     */
    public function addBoleto(BoletoContract $boleto)
    {
        $this->boletos[] = $boleto;
        $this->iniciaDetalhe();

        $demonstrativo = array_filter($boleto->getDescricaoDemonstrativo());

        $this->add(1, 1, '1');
        $this->add(2, 20, '');
        $this->add(21, 37, '1120001' . Util::formatCnab('9', $this->getConta(), 10));
        $this->add(38, 62, Util::formatCnab('X', $boleto->getNumeroControle(), 25)); // numero de controle
        $this->add(63, 65, '000');
        $this->add(66, 66, $boleto->getMulta() > 0 ? '2' : '0');
        $this->add(67, 79, Util::formatCnab('9', 0, 13, 2));
        $this->add(80, 83, Util::formatCnab('9', $boleto->getMulta(), 4, 2));
        $this->add(84, 89, $boleto->getMulta() > 0 ? ($boleto->getDataVencimento()->copy())->addDay()->format('dmy') : '000000');
        $this->add(90, 100, Util::formatCnab('9', '0', 11));
        $this->add(101, 108, '');
        $this->add(109, 110, self::OCORRENCIA_REMESSA); // REGISTRO
        $this->add(111, 120, Util::formatCnab('X', $boleto->getNumeroDocumento(), 10));
        $this->add(121, 126, $boleto->getDataVencimento()->format('dmy'));
        $this->add(127, 139, Util::formatCnab('9', $boleto->getValor(), 13, 2));
        $this->add(140, 141, 60); // Informar “0”, "30" ou "60". Esses são os dias decorridos da data de vencimento do título em que ainda será possível o pagamento
        $this->add(142, 147, '');
        $this->add(148, 149, $boleto->getEspecieDocCodigo());
        $this->add(150, 150, 'N');
        $this->add(151, 156, $boleto->getDataDocumento()->format('dmy'));
        $this->add(157, 159, '');
        $this->add(160, 160, $boleto->getJuros() > 0 ? 2 : 0);
        $this->add(161, 173, Util::formatCnab('9', 0, 13, 2));
        $this->add(174, 177, Util::formatCnab('9', $boleto->getJuros(), 4, 2));
        $this->add(178, 183, $boleto->getJuros() > 0 ? ($boleto->getDataVencimento()->copy())->addDays($boleto->getJurosApos() > 0 ? $boleto->getJurosApos() : 1)->format('dmy') : '000000');
        $this->add(184, 184, $boleto->getDesconto() > 0 ? 1 : 0);
        $this->add(185, 197, Util::formatCnab('9', $boleto->getDesconto() > 0 ? $boleto->getDesconto() : 0, 13, 2));
        $this->add(198, 201, '0000');
        $this->add(202, 207, $boleto->getDesconto() > 0 ? $boleto->getDataDesconto()->format('dmy') : '000000');
        $this->add(208, 220, Util::formatCnab('9', 0, 13, 2));
        $this->add(221, 222, strlen(Util::onlyNumbers($boleto->getPagador()->getDocumento())) == 14 ? '02' : '01');
        $this->add(223, 236, Util::formatCnab('9', Util::onlyNumbers($boleto->getPagador()->getDocumento()), 14));
        $this->add(237, 276, Util::formatCnab('X', $boleto->getPagador()->getNome(), 40));
        $this->add(277, 316, Util::formatCnab('X', $boleto->getPagador()->getEndereco(), 40));
        $this->add(317, 324, Util::formatCnab('9', Util::onlyNumbers($boleto->getPagador()->getCep()), 8));
        $this->add(325, 394, Util::formatCnab('X', array_key_exists(0, $demonstrativo) ? $demonstrativo[0] : '', 70));
        $this->add(395, 400, Util::formatCnab('9', $this->iRegistros + 1, 6));

        if (count($demonstrativo) > 1) {
            $this->iniciaDetalhe();
            $this->add(1, 1, '2');
            $this->add(2, 79, Util::formatCnab('X', array_key_exists(1, $demonstrativo) ? $demonstrativo[1] : '', 78));
            $this->add(80, 157, Util::formatCnab('X', array_key_exists(2, $demonstrativo) ? $demonstrativo[2] : '', 78));
            $this->add(158, 235, Util::formatCnab('X', array_key_exists(3, $demonstrativo) ? $demonstrativo[3] : '', 78));
            $this->add(236, 313, Util::formatCnab('X', array_key_exists(4, $demonstrativo) ? $demonstrativo[4] : '', 78));
            $this->add(314, 336, Util::formatCnab('9', 0, 23));
            $this->add(337, 346, '');
            $this->add(347, 369, Util::formatCnab('9', 0, 23));
            $this->add(370, 379, '');
            $this->add(380, 390, Util::formatCnab('9', 0, 11));
            $this->add(391, 394, '');
            $this->add(395, 400, Util::formatCnab('9', $this->iRegistros + 1, 6));
        }

        return $this;
    }

    /**
     * @return Inter
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
        //          CI400_001_???????.REM
        //          CI400 - Cobrança Inter
        //          001 - Versão do layout
        //          ??????? - Número sequencial de remessa com sete caracteres (o mesmo número inserido no campo 111 a 117 do header do arquivo remesssa)
        //          .REM - Extensão do arquivo remessa
        return sprintf('CI400_001_%07s.REM', $this->getIdremessa());
    }

    public function save($path, $suggestName = true)
    {
        return parent::save($path, $suggestName);
    }
}
