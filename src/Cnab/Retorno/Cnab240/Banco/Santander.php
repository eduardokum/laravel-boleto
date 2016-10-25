<?php

namespace Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab240\Banco;

use Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab240\AbstractRetorno;
use Eduardokum\LaravelBoleto\Contracts\Cnab\RetornoCnab240;
use Eduardokum\LaravelBoleto\Util;

class Santander extends AbstractRetorno implements RetornoCnab240
{
    /**
     * Código do banco
     * @var string
     */
    protected $codigoBanco = self::COD_BANCO_SANTANDER;

    /**
     * Array com as ocorrencias do banco;
     *
     * @var array
     */
    private $ocorrencias = [
        '02' => 'Título não existe',
        '03' => 'Entrada rejeitada',
        '04' => 'transferência de carteira/entrada',
        '05' => 'transferência de carteira/baixa',
        '06' => 'Liquidação',
        '09' => 'Baixa',
        '11' => 'títulos em carteira',
        '12' => 'confirmação recebimento instrução de abatimento',
        '13' => 'confirmação recebimento instrução de cancelamento abatimento',
        '14' => 'confirmação recebimento instrução alteração de vencimento',
        '17' => 'liquidação após baixa ou liquidação título não registrado',
        '19' => 'confirmação recebimento instrução de protesto',
        '20' => 'confirmação recebimento instrução de sustação/Não Protestar',
        '23' => 'remessa a cartorio ( aponte em cartorio)',
        '24' => 'retirada de cartorio e manutenção em carteira',
        '25' => 'protestado e baixado ( baixa por ter sido protestado)',
        '26' => 'instrução rejeitada',
        '27' => 'confirmação do pedido de alteração de outros dado',
        '28' => 'debito de tarifas/custas',
        '29' => 'ocorrências do sacado',
        '30' => 'alteração de dados rejeitada',
        '51' => 'Título DDA reconhecido pelo sacado',
        '52' => 'Título DDA não reconhecido pelo sacado',
        '53' => 'Título DDA recusado pela CIP',
    ];

    /**
     * Array com as possiveis rejeicoes do banco.
     *
     * @var array
     */
    private $rejeicoes = [
        '01' => 'código do banco invalido',
        '02' => 'código do registro detalhe inválido',
        '03' => 'código do segmento invalido',
        '04' => 'código do movimento não permitido para carteira',
        '05' => 'código de movimento invalido',
        '06' => 'tipo/numero de inscrição do cedente inválidos',
        '07' => 'agencia/conta/DV invalido',
        '08' => 'nosso numero invalido',
        '09' => 'nosso numero duplicado',
        '10' => 'carteira invalida',
        '11' => 'forma de cadastramento do titulo invalida',
        '12' => 'tipo de documento invalido',
        '13' => 'identificação da emissão do Boleto invalida',
        '14' => 'identificação da distribuição do Boleto invalida',
        '15' => 'características da cobrança incompatíveis',
        '16' => 'data de vencimento invalida',
        '17' => 'data de vencimento anterior a data de emissão',
        '18' => 'vencimento fora do prazo de operação',
        '19' => 'titulo a cargo de bancos correspondentes com vencimento inferior a xx dias',
        '20' => 'valor do título invalido',
        '21' => 'espécie do titulo invalida',
        '22' => 'espécie não permitida para a carteira',
        '23' => 'aceite invalido',
        '24' => 'Data de emissão inválida',
        '25' => 'Data de emissão posterior a data de entrada',
        '26' => 'Código de juros de mora inválido',
        '27' => 'Valor/Taxa de juros de mora inválido',
        '28' => 'Código de desconto inválido',
        '29' => 'Valor do desconto maior ou igual ao valor do título',
        '30' => 'Desconto a conceder não confere',
        '31' => 'Concessão de desconto - já existe desconto anterior',
        '32' => 'Valor do IOF',
        '33' => 'Valor do abatimento inválido',
        '34' => 'Valor do abatimento maior ou igual ao valor do título',
        '35' => 'Abatimento a conceder não confere',
        '36' => 'Concessão de abatimento - já existe abatimento anterior',
        '37' => 'Código para protesto inválido',
        '38' => 'Prazo para protesto inválido',
        '39' => 'Pedido de protesto não permitido para o título',
        '40' => 'Título com ordem de protesto emitida',
        '41' => 'Pedido de cancelamento/sustação para títulos sem instrução de protesto',
        '42' => 'Código para baixa/devolução inválido',
        '43' => 'Prazo para baixa/devolução inválido',
        '44' => 'Código de moeda inválido',
        '45' => 'Nome do sacados não informado',
        '46' => 'Tipo /Número de inscrição do sacado inválidos',
        '47' => 'Endereço do sacado não informado',
        '48' => 'CEP inválido',
        '49' => 'CEP sem praça de cobrança (não localizado)',
        '50' => 'CEP referente a um Banco Correspondente',
        '51' => 'CEP incompatível com a unidade de federação',
        '52' => 'Unidade de federação inválida',
        '53' => 'Tipo/Número de inscrição do sacador/avalista inválidos',
        '54' => 'Sacador/Avalista não informado',
        '55' => 'Nosso número no Banco Correspondente não informado',
        '56' => 'Código do Banco Correspondente não informado',
        '57' => 'Código da multa inválido',
        '58' => 'Data da multa inválida',
        '59' => 'Valor/Percentual da multa inválido',
        '60' => 'Movimento para título não cadastrado',
        '61' => 'Alteração de agência cobradora/dv inválida',
        '62' => 'Tipo de impressão inválido',
        '63' => 'Entrada para título já cadastrado',
        '64' => 'Número da linha inválido'
    ];

    /**
     * Roda antes dos metodos de processar
     */
    protected function init()
    {
        $this->totais = [
            'valor_recebido' => 0,
            'liquidados' => 0,
            'entradas' => 0,
            'baixados' => 0,
            'protestados' => 0,
            'erros' => 0,
            'alterados' => 0,
        ];
    }

    /**
     * @param array $header
     *
     * @return boolean
     */
    protected function processarHeader(array $header)
    {
        $this->getHeader()
            ->setCodBanco($this->rem(1, 3, $header))
            ->setLoteServico($this->rem(4, 7, $header))
            ->setTipoRegistro($this->rem(8, 8, $header))
            ->setTipoInscricao($this->rem(17, 17, $header))
            ->setNumeroInscricao($this->rem(18, 32, $header))
            ->setAgencia($this->rem(33, 36, $header))
            ->setAgenciaDigito($this->rem(37, 37, $header))
            ->setConta($this->rem(38, 46, $header))
            ->setContaDigito($this->rem(47, 47, $header))
            ->setCodigoCedente($this->rem(53, 61, $header))
            ->setNomeEmpresa($this->rem(73, 102, $header))
            ->setNomeBanco($this->rem(103, 132, $header))
            ->setCodigoRemessaRetorno($this->rem(143, 143, $header))
            ->setData($this->convertDate($this->rem(144, 151, $header)))
            ->setNumeroSequencialArquivo($this->rem(158, 163, $header))
            ->setVersaoLayoutArquivo($this->rem(164, 166, $header));

        return true;
    }

    /**
     * @param array $headerLote
     *
     * @return boolean
     */
    protected function processarHeaderLote(array $headerLote)
    {
        $this->getHeaderLote()
            ->setCodBanco($this->rem(1, 3, $headerLote))
            ->setNumeroLoteRetorno($this->rem(4, 7, $headerLote))
            ->setTipoRegistro($this->rem(8, 8, $headerLote))
            ->setTipoOperacao($this->rem(9, 9, $headerLote))
            ->setTipoServico($this->rem(10, 11, $headerLote))
            ->setVersaoLayoutLote($this->rem(14, 16, $headerLote))
            ->setNumeroInscricao($this->rem(19, 33, $headerLote))
            ->setAgencia($this->rem(54, 57, $headerLote))
            ->setAgenciaDigito($this->rem(58, 58, $headerLote))
            ->setConta($this->rem(59, 67, $headerLote))
            ->setContaDigito($this->rem(68, 68, $headerLote))
            ->setCodigoCedente($this->rem(34, 42, $headerLote))
            ->setNomeEmpresa($this->rem(74, 103, $headerLote))
            ->setNumeroRetorno($this->rem(184, 191, $headerLote))
            ->setDataGravacao($this->convertDate($this->rem(192, 199, $headerLote)));

        return true;
    }

    /**
     * @param array $detalhe
     *
     * @return boolean
     */
    protected function processarDetalhe(array $detalhe)
    {
        if ($this->getServiceType($detalhe) == '3') {

            if ($this->getSegmentType($detalhe) == 'T') {

                $d = $this->detalheAtual()
                    ->setOcorrencia($this->rem(16, 17, $detalhe));

                $d = $this->detalheAtual()
                    ->getSegmentoT()
                    ->setCodigoBancoCompensacao($this->rem(1, 3, $detalhe))
                    ->setNumeroLoteRetorno($this->rem(4, 7, $detalhe))
                    ->setTipoRegistro($this->rem(8, 8, $detalhe))
                    ->setNumeroSequencialRegistroLote($this->rem(9, 13, $detalhe))
                    ->setCodigoSegmentoRegistroDetalhe($this->rem(14, 14, $detalhe))
                    ->setAgenciaCedente($this->rem(18, 21, $detalhe))
                    ->setAgenciaCedenteDigito($this->rem(22, 22, $detalhe))
                    ->setContaCorrente($this->rem(23, 31, $detalhe))
                    ->setContaDigito($this->rem(32, 32, $detalhe))
                    ->setNossoNumero($this->rem(41, 53, $detalhe))
                    ->setCodigoCarteira($this->rem(54, 54, $detalhe))
                    ->setSeuNumero($this->rem(55, 69, $detalhe))
                    ->setDataVencimento($this->convertDate($this->rem(70, 77, $detalhe)))
                    ->setValorTitulo(Util::nFloat($this->rem(78, 92, $detalhe)) / 100, 2, false)
                    ->setNumeroBancoCobradorRecebedor($this->rem(93, 95, $detalhe))
                    ->setAgenciaCobradoraRecebedora($this->rem(96, 99, $detalhe))
                    ->setDigitoAgenciaCedente($this->rem(100, 100, $detalhe))
                    ->setIdentificador($this->rem(101, 125, $detalhe))
                    ->setCodigoMoeda($this->rem(126, 127, $detalhe))
                    ->setTipoInscriçãoSacado($this->rem(128, 128, $detalhe))
                    ->setNumeroInscricaoSacado($this->rem(129, 143, $detalhe))
                    ->setNomeSacado($this->rem(144, 183, $detalhe))
                    ->setContaCobranca($this->rem(184, 193, $detalhe))
                    ->setValorTarifa(Util::nFloat($this->rem(194, 208, $detalhe)) / 100, 2, false)
                    ->setIdentificacaoRejeicao($this->rem(209, 218, $detalhe))
                    ->setNumeroDocumento($this->rem(55, 69, $detalhe));

                //ocorrencias

                $d = $this->detalheAtual();
                $this->totais['valor_recebido'] += $d->getValorPagoSacado();

                if ($d->hasOcorrencia('06', '09', '17')) {
                    $this->totais['liquidados']++;
                    $d->setOcorrenciaTipo($d::OCORRENCIA_LIQUIDADA);
                } elseif ($d->hasOcorrencia('02')) {
                    $this->totais['entradas']++;
                    $d->setOcorrenciaTipo($d::OCORRENCIA_ENTRADA);
                } elseif ($d->hasOcorrencia('09')) {
                    $this->totais['baixados']++;
                    $d->setOcorrenciaTipo($d::OCORRENCIA_BAIXADA);
                } elseif ($d->hasOcorrencia('19')) {
                    $this->totais['protestados']++;
                    $d->setOcorrenciaTipo($d::OCORRENCIA_PROTESTADA);
                } elseif ($d->hasOcorrencia('27', '30')) {
                    $this->totais['alterados']++;
                    $d->setOcorrenciaTipo($d::OCORRENCIA_ALTERACAO);
                } elseif ($d->hasOcorrencia('03', '26', '30')) {

                    $this->totais['erros']++;
                    $errorsRetorno = str_split(sprintf('%09s', $this->rem(209, 218, $detalhe)), 3);
                    $error = array_get($this->rejeicoes, $errorsRetorno[0], '');
                    $error .= array_get($this->rejeicoes, $errorsRetorno[1], '');
                    $error .= array_get($this->rejeicoes, $errorsRetorno[2], '');

                    $d->setError($error);

                } else {
                    $d->setOcorrenciaTipo($d::OCORRENCIA_OUTROS);
                }

            } elseif ($this->getSegmentType($detalhe) == 'U') {

                $d = $this->detalheAtual()
                    ->getSegmentoU()
                    ->setCodigoBancoCompensacao($this->rem(1, 3, $detalhe))
                    ->setLoteServico($this->rem(4, 7, $detalhe))
                    ->setTipoRegistro($this->rem(8, 8, $detalhe))
                    ->setNumeroSequencialRegistroLote($this->rem(9, 13, $detalhe))
                    ->setCodigoSegmentoRegistroDetalhe($this->rem(14, 14, $detalhe))
                    ->setJurosMultaEncargos(Util::nFloat($this->rem(18, 32, $detalhe)) / 100, 2, false)
                    ->setValorDescontoConcedido(Util::nFloat($this->rem(33, 47, $detalhe)) / 100, 2, false)
                    ->setValorAbatimentoConcedidoCancelado(Util::nFloat($this->rem(48, 62, $detalhe)) / 100, 2, false)
                    ->setValorIOF(Util::nFloat($this->rem(63, 77, $detalhe)) / 100, 2, false)
                    ->setValorPagoSacado(Util::nFloat($this->rem(78, 92, $detalhe)) / 100, 2, false)
                    ->setValorLiquidoCreditado(Util::nFloat($this->rem(93, 107, $detalhe)) / 100, 2, false)
                    ->setValorOutrasDespesas(Util::nFloat($this->rem(108, 122, $detalhe)) / 100, 2, false)
                    ->setValorOutrosCreditos(Util::nFloat($this->rem(123, 137, $detalhe)) / 100, 2, false)
                    ->setDataOcorrencia($this->convertDate($this->rem(138, 145, $detalhe)))
                    ->setDataCredito($this->convertDate($this->rem(146, 153, $detalhe)))
                    ->setCodigoOcorrenciaSacado($this->rem(154, 157, $detalhe))
                    ->setDataOcorrenciaSacado($this->convertDate($this->rem(158, 165, $detalhe)))
                    ->setValorOcorrenciaSacado(Util::nFloat($this->rem(166, 180, $detalhe)) / 100, 2, false)
                    ->setComplementoOcorrenciaSacado($this->rem(181, 210, $detalhe))
                    ->setCodigoBancoCorrespondenteCompensacao($this->rem(211, 213, $detalhe));

            } elseif ($this->getSegmentType($detalhe) == 'Y') {

                $d = $this->detalheAtual()
                    ->getSegmentoY()
                    ->setCodigoBancoCompensacao($this->rem(1, 3, $detalhe))
                    ->setLoteServico($this->rem(4, 7, $detalhe))
                    ->setTipoRegistro($this->rem(8, 8, $detalhe))
                    ->setNumeroSequencialRegistroLote($this->rem(9, 13, $detalhe))
                    ->setCodigoSegmentoRegistroDetalhe($this->rem(14, 14, $detalhe))
                    ->setCodigoOcorrencia($this->rem(16, 17, $detalhe))
                    ->setIdentificacaoRegistroOpcional($this->rem(18, 19, $detalhe))
                    ->setIdentificacaoCheque(array(
                        '1' => $this->rem(20, 53, $detalhe),
                        '2' => $this->rem(44, 87, $detalhe),
                        '3' => $this->rem(88, 121, $detalhe),
                        '4' => $this->rem(122, 155, $detalhe),
                        '5' => $this->rem(156, 189, $detalhe),
                        '6' => $this->rem(190, 223, $detalhe),
                    ));

            }

            return true;

        }

        return false;

    }

    /**
     * @param array $trailerLote
     *
     * @return boolean
     */
    protected function processarTrailerLote(array $trailer)
    {
        $this->getTrailerLote()
            ->setLoteServico($this->rem(4, 7, $trailer))
            ->setTipoRegistro($this->rem(8, 8, $trailer))
            ->setQtdRegistroLote((int)$this->rem(18, 23, $trailer))
            ->setQtdTitulosCobrancaSimples((int)$this->rem(24, 29, $trailer))
            ->setValorTotalTitulosCobrancaSimples(Util::nFloat($this->rem(30, 46, $trailer)) / 100, 2, false)
            ->setQtdTitulosCobrancaVinculada((int)$this->rem(47, 52, $trailer))
            ->setValorTotalTitulosCobrancaVinculada(Util::nFloat($this->rem(53, 69, $trailer)) / 100, 2, false)
            ->setQtdTitulosCobrancaCaucionada((int)$this->rem(70, 75, $trailer))
            ->setValorTotalTitulosCobrancaCaucionada(Util::nFloat($this->rem(76, 92, $trailer)) / 100, 2, false)
            ->setQtdTitulosCobrancaDescontada((int)$this->rem(93, 98, $trailer))
            ->setValorTotalTitulosCobrancaDescontada(Util::nFloat($this->rem(99, 115, $trailer)) / 100, 2, false)
            ->setNumeroAvisoLancamento($this->rem(116, 123, $trailer));

        return true;

    }

    /**
     * @param array $trailerArquivo
     *
     * @return boolean
     */
    protected function processarTrailerArquivo(array $trailer)
    {
        $this->getTrailerArquivo()
            ->setNumeroLoteRemessa($this->rem(4, 7, $trailer))
            ->setTipoRegistro($this->rem(8, 8, $trailer))
            ->setQtdLotesArquivo((int)$this->rem(18, 23, $trailer))
            ->setQtdRegistroArquivo((int)$this->rem(24, 29, $trailer));

        return true;
    }
}