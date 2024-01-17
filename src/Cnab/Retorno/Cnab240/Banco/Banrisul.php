<?php

namespace Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab240\Banco;

use Illuminate\Support\Arr;
use Eduardokum\LaravelBoleto\Util;
use Eduardokum\LaravelBoleto\Contracts\Cnab\RetornoCnab240;
use Eduardokum\LaravelBoleto\Exception\ValidationException;
use Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab240\AbstractRetorno;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;

class Banrisul extends AbstractRetorno implements RetornoCnab240
{
    /**
     * Código do banco
     *
     * @var string
     */
    protected $codigoBanco = BoletoContract::COD_BANCO_BANRISUL;

    /**
     * Array com as ocorrencias do banco;
     *
     * @var array
     */
    private $ocorrencias = [
        '02' => 'Entrada Confirmada',
        '03' => 'Entrada Rejeitada',
        '04' => 'Reembolso e Transf. (Desconto-Vendor) ou Transf. de Carteira (Garantia)',
        '05' => 'Reembolso e Devolução Desconto e Vendor',
        '06' => 'Liquidação',
        '09' => 'Baixa',
        '11' => 'Título em carteira (em ser) - Para este código de ocorrência, o campo data da ocorrência no banco (posição 138-145 do segmento “U”), será a data do registro dos títulos',
        '12' => 'Confirmação recebimento instrução abatimento',
        '13' => 'Confirmação recebimento instrução de cancelamento abatimento',
        '14' => 'Confirmação instrução alteração de vencimento',
        '15' => 'Confirmação de Protesto Imediato por Falência',
        '17' => 'Liquidação após baixa ou liquidação título não registrado',
        '19' => 'Confirmação Recebimento Instrução Protesto',
        '20' => 'Confirmação Recebimento Instrução de Sustação/Cancelamento de Protesto',
        '23' => 'Remessa a Cartório (aponte em cartório) - A data da Entrega em cartório é informada nas posições 138 a 145 do segmento U',
        '24' => 'Reservado',
        '25' => 'Protestado e baixado (baixa por ter sido protestado)',
        '26' => 'Instrução Rejeitada',
        '27' => 'Confirmação do pedido de alteração de outros dados',
        '28' => 'Débito de tarifas/custo',
        '30' => 'Alteração de Dados rejeitado',
        'AA' => 'Devolução, Liquidado Anteriormente (CCB) - A informação da Data da Liquidação está nas posições 138 a 145 do segmento U',
        'AB' => 'Cobrança a Creditar (em trânsito)*',
        'AC' => 'Situação do Título – Cartório',
    ];

    /**
     * Array com as possiveis descricoes de baixa e liquidacao.
     *
     * @var array
     */
    private $baixa_liquidacao = [
        '01' => 'Por saldo – Reservado',
        '02' => 'Por conta (Parcial)',
        '03' => 'No próprio Banco',
        '04' => 'Compensação Eletrônica',
        '05' => 'Compensação Convencional',
        '06' => 'Por meio Eletrônico',
        '07' => 'Reservado',
        '08' => 'Em cartório',
        '09' => 'Comandado Banco',
        '10' => 'Comandado cliente Arquivo',
        '11' => 'Comandado cliente On-Line',
        '12' => 'Decurso prazo – cliente',
        'AA' => 'Baixa por Pagamento',
    ];

    /**
     * Array com as possiveis rejeicoes do banco.
     *
     * @var array
     */
    private $rejeicoes = [
        '01' => 'Código do Banco inválido',
        '02' => 'Código de registro detalhe inválido',
        '03' => 'Código do Segmento inválido',
        '04' => 'Código do movimento não permitido para a carteira',
        '05' => 'Código do movimento inválido',
        '06' => 'Tipo/Número de inscrição do Beneficiário inválido',
        '07' => 'Agência/conta/DV inválido',
        '08' => 'Nosso Número inválido',
        '09' => 'Nosso número duplicado',
        '10' => 'Carteira inválida',
        '11' => 'Forma de cadastramento do título inválido',
        '12' => 'Tipo de documento inválido',
        '13' => 'Identificação da emissão do bloqueto inválido',
        '14' => 'Identificação da distribuição do bloqueto inválido',
        '15' => 'Características da cobrança incompatíveis - se a carteira e a moeda forem válidas e não existir espécie',
        '16' => 'Data de vencimento inválida',
        '17' => 'Data de vencimento anterior a data de emissão',
        '18' => 'Vencimento fora do prazo de operação',
        '19' => 'Título a cargo de Bancos Correspondentes com vencimento inferior a XX dias',
        '20' => 'Valor do título inválido (não numérico)',
        '21' => 'Espécie do título inválida (arquivo de registro)',
        '22' => 'Espécie não permetida para a carteira',
        '23' => 'Aceite inválido - verifica conteúdo válido',
        '24' => 'Data de emissão inválida - verifica se a data é numérica e se está no formato válido',
        '25' => 'Data de emissão posterior a data de processamento',
        '26' => 'Código de juros de mora inválido',
        '27' => 'Valor/taxa de juros de mora inválido',
        '28' => 'Código do desconto inválido',
        '29' => 'Valor do desconto maior ou igual ao valor do título',
        '30' => 'Desconto a conceder não confere',
        '32' => 'Valor do IOF inválido',
        '33' => 'Valor do abatimento inválido - para registro de título verifica se o campo é numérico e para concessão/cancelamento de abatimento',
        '34' => 'Valor do abatimento maior ou igual ao valor do título',
        '35' => 'Abatimento a conceder não confere',
        '36' => 'Concessão de abatimento - já existe abatimento anterior',
        '37' => 'Código para protesto inválido - rejeita o título se o campo for diferente de branco, 0, 1 ou 3',
        '38' => 'Prazo para protesto inválido - se o código for 1 verifica se o campo é numérico',
        '39' => 'Pedido de protesto não permitido para o título - não permite protesto para as carteiras R, S e N',
        '40' => 'Título com ordem de protesto emitida (para retorno de alteração)',
        '41' => 'Pedido de cancelamento/sustação de protesto inválido',
        '42' => 'Código para baixa/devolução ou instrução inválido - verifica se o código é branco, 0, 1 ou 2',
        '43' => 'Prazo para baixa/devolução inválido - se o código é 1 verifica se o campo prazo é numérico',
        '44' => 'Código da moeda inválido',
        '45' => 'Nome do Pagador inválido ou alteração do Pagador não permitida',
        '46' => 'Tipo/número de inscrição do Pagador inválido',
        '47' => 'Endereço não informado ou alteração de endereço não permitida',
        '48' => 'CEP inválido ou alteração de CEP não permitida',
        '49' => 'CEP sem praça de cobrança ou alteração de cidade não permitida',
        '50' => 'CEP referente a um Banco Correspondente',
        '51' => 'CEP incompatível com a unidade da federação',
        '52' => 'Unidade de Federação inválida ou alteração de UF não permitida',
        '53' => 'Tipo/Número de inscrição do Sacador/Avalista inválido',
        '54' => 'Sacador/Avalista não informado - para espécie AD o nome do Sacador é obrigatório',
        '57' => 'Código da multa inválido',
        '58' => 'Data da multa inválida',
        '59' => 'Valor/percentual da multa inválido',
        '60' => 'Movimento para título não cadastrado - alteração ou devolução',
        '62' => 'Tipo de impressão inválido - Segmento 3S',
        '63' => 'Entrada para título já cadastrado',
        '79' => 'Data de juros de mora inválido - valida data ou prazo na instrução de juros',
        '80' => 'Data do desconto inválida - valida data ou prazo da instrução de desconto',
        '81' => 'CEP inválido do Sacador',
        '83' => 'Tipo/Número de inscrição do Sacador inválido',
        '84' => 'Sacador não informado',
        '86' => 'Seu número inválido (para retorno de alteração).',
    ];

    /**
     * Roda antes dos metodos de processar
     */
    protected function init()
    {
        $this->totais = [
            'liquidados'  => 0,
            'entradas'    => 0,
            'baixados'    => 0,
            'protestados' => 0,
            'erros'       => 0,
            'alterados'   => 0,
        ];
    }

    /**
     * @param array $header
     *
     * @return bool
     * @throws ValidationException
     */
    protected function processarHeader(array $header)
    {
        $this->getHeader()
            ->setCodBanco($this->rem(1, 3, $header))
            ->setLoteServico($this->rem(4, 7, $header))
            ->setTipoRegistro($this->rem(8, 8, $header))
            ->setTipoInscricao($this->rem(18, 18, $header))
            ->setNumeroInscricao($this->rem(19, 32, $header))
            ->setCodigoCedente($this->rem(33, 52, $header))
            ->setAgencia($this->rem(53, 57, $header))
            ->setAgenciaDv($this->rem(58, 58, $header))
            ->setConta($this->rem(59, 70, $header))
            ->setContaDv($this->rem(71, 71, $header))
            ->setNomeEmpresa($this->rem(73, 102, $header))
            ->setNomeBanco($this->rem(103, 132, $header))
            ->setCodigoRemessaRetorno($this->rem(143, 143, $header))
            ->setData($this->rem(144, 151, $header))
            ->setNumeroSequencialArquivo($this->rem(158, 163, $header))
            ->setVersaoLayoutArquivo($this->rem(164, 166, $header));

        return true;
    }

    /**
     * @param array $headerLote
     *
     * @return bool
     * @throws ValidationException
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
            ->setTipoInscricao($this->rem(18, 18, $headerLote))
            ->setNumeroInscricao($this->rem(19, 33, $headerLote))
            ->setCodigoCedente($this->rem(34, 53, $headerLote))
            ->setAgencia($this->rem(54, 58, $headerLote))
            ->setAgenciaDv($this->rem(59, 59, $headerLote))
            ->setConta($this->rem(60, 71, $headerLote))
            ->setContaDv($this->rem(72, 72, $headerLote))
            ->setNomeEmpresa($this->rem(74, 103, $headerLote))
            ->setNumeroRetorno($this->rem(184, 191, $headerLote))
            ->setDataGravacao($this->rem(192, 199, $headerLote))
            ->setDataCredito($this->rem(200, 207, $headerLote));

        return true;
    }

    /**
     * @param array $detalhe
     *
     * @return bool
     * @throws ValidationException
     */
    protected function processarDetalhe(array $detalhe)
    {
        $d = $this->detalheAtual();

        if ($this->getSegmentType($detalhe) == 'T') {
            $d->setOcorrencia($this->rem(16, 17, $detalhe))
                ->setOcorrenciaDescricao(Arr::get($this->ocorrencias, $this->detalheAtual()->getOcorrencia(), 'Desconhecida'))
                ->setNossoNumero($this->rem(38, 57, $detalhe))
                ->setCarteira($this->rem(58, 58, $detalhe))
                ->setNumeroDocumento($this->rem(59, 73, $detalhe))
                ->setDataVencimento($this->rem(74, 81, $detalhe))
                ->setValor(Util::nFloat($this->rem(82, 96, $detalhe) / 100, 2, false))
                ->setNumeroControle($this->rem(106, 130, $detalhe))
                ->setPagador([
                    'nome'      => $this->rem(149, 188, $detalhe),
                    'documento' => $this->rem(134, 148, $detalhe),
                ])
                ->setValorTarifa(Util::nFloat($this->rem(199, 213, $detalhe) / 100, 2, false));

            /**
             * ocorrencias
             */
            $msgAdicional = str_split(sprintf('%08s', $this->rem(214, 223, $detalhe)), 2) + array_fill(0, 5, '');
            if ($d->hasOcorrencia('06', '17')) {
                $this->totais['liquidados']++;
                $ocorrencia = Util::appendStrings($d->getOcorrenciaDescricao(), Arr::get($this->baixa_liquidacao, $msgAdicional[0], ''), Arr::get($this->baixa_liquidacao, $msgAdicional[1], ''), Arr::get($this->baixa_liquidacao, $msgAdicional[2], ''), Arr::get($this->baixa_liquidacao, $msgAdicional[3], ''), Arr::get($this->baixa_liquidacao, $msgAdicional[4], ''));
                $d->setOcorrenciaDescricao($ocorrencia);
                $d->setOcorrenciaTipo($d::OCORRENCIA_LIQUIDADA);
            } elseif ($d->hasOcorrencia('02')) {
                $this->totais['entradas']++;
                if (array_search('a4', array_map('strtolower', $msgAdicional)) !== false) {
                    $d->getPagador()->setDda(true);
                }
                $d->setOcorrenciaTipo($d::OCORRENCIA_ENTRADA);
            } elseif ($d->hasOcorrencia('09')) {
                $this->totais['baixados']++;
                $ocorrencia = Util::appendStrings($d->getOcorrenciaDescricao(), Arr::get($this->baixa_liquidacao, $msgAdicional[0], ''), Arr::get($this->baixa_liquidacao, $msgAdicional[1], ''), Arr::get($this->baixa_liquidacao, $msgAdicional[2], ''), Arr::get($this->baixa_liquidacao, $msgAdicional[3], ''), Arr::get($this->baixa_liquidacao, $msgAdicional[4], ''));
                $d->setOcorrenciaDescricao($ocorrencia);
                $d->setOcorrenciaTipo($d::OCORRENCIA_BAIXADA);
            } elseif ($d->hasOcorrencia('25')) {
                $this->totais['protestados']++;
                $d->setOcorrenciaTipo($d::OCORRENCIA_PROTESTADA);
            } elseif ($d->hasOcorrencia('27', '14')) {
                $this->totais['alterados']++;
                $d->setOcorrenciaTipo($d::OCORRENCIA_ALTERACAO);
            } elseif ($d->hasOcorrencia('03', '26', '30')) {
                $this->totais['erros']++;
                $error = Util::appendStrings(Arr::get($this->rejeicoes, $msgAdicional[0], ''), Arr::get($this->rejeicoes, $msgAdicional[1], ''), Arr::get($this->rejeicoes, $msgAdicional[2], ''), Arr::get($this->rejeicoes, $msgAdicional[3], ''), Arr::get($this->rejeicoes, $msgAdicional[4], ''));
                $d->setError($error);
            } else {
                $d->setOcorrenciaTipo($d::OCORRENCIA_OUTROS);
            }
        }

        if ($this->getSegmentType($detalhe) == 'U') {
            $d->setValorMulta(Util::nFloat($this->rem(18, 32, $detalhe) / 100, 2, false))
                ->setValorDesconto(Util::nFloat($this->rem(33, 47, $detalhe) / 100, 2, false))
                ->setValorAbatimento(Util::nFloat($this->rem(48, 62, $detalhe) / 100, 2, false))
                ->setValorIOF(Util::nFloat($this->rem(63, 77, $detalhe) / 100, 2, false))
                ->setValorRecebido(Util::nFloat($this->rem(78, 92, $detalhe) / 100, 2, false))
                ->setValorTarifa($d->getValorRecebido() - Util::nFloat($this->rem(93, 107, $detalhe) / 100, 2, false))
                ->setDataOcorrencia($this->rem(138, 145, $detalhe))
                ->setDataCredito($this->rem(146, 153, $detalhe));
        }

        return true;
    }

    /**
     * @param array $trailer
     *
     * @return bool
     * @throws ValidationException
     */
    protected function processarTrailerLote(array $trailer)
    {
        $this->getTrailerLote()
            ->setLoteServico($this->rem(4, 7, $trailer))
            ->setTipoRegistro($this->rem(8, 8, $trailer))
            ->setQtdRegistroLote((int) $this->rem(18, 23, $trailer))
            ->setQtdTitulosCobrancaSimples((int) $this->rem(24, 29, $trailer))
            ->setValorTotalTitulosCobrancaSimples(Util::nFloat($this->rem(30, 46, $trailer) / 100, 2, false))
            ->setQtdTitulosCobrancaVinculada((int) $this->rem(47, 52, $trailer))
            ->setValorTotalTitulosCobrancaVinculada(Util::nFloat($this->rem(53, 69, $trailer) / 100, 2, false))
            ->setQtdTitulosCobrancaCaucionada((int) $this->rem(70, 75, $trailer))
            ->setValorTotalTitulosCobrancaCaucionada(Util::nFloat($this->rem(76, 92, $trailer) / 100, 2, false))
            ->setQtdTitulosCobrancaDescontada((int) $this->rem(93, 98, $trailer))
            ->setValorTotalTitulosCobrancaDescontada(Util::nFloat($this->rem(99, 115, $trailer) / 100, 2, false));

        return true;
    }

    /**
     * @param array $trailer
     *
     * @return bool
     * @throws ValidationException
     */
    protected function processarTrailer(array $trailer)
    {
        $this->getTrailer()
            ->setNumeroLote($this->rem(4, 7, $trailer))
            ->setTipoRegistro($this->rem(8, 8, $trailer))
            ->setQtdLotesArquivo((int) $this->rem(18, 23, $trailer))
            ->setQtdRegistroArquivo((int) $this->rem(24, 29, $trailer));

        return true;
    }
}
