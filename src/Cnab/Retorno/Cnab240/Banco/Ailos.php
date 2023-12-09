<?php

namespace Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab240\Banco;

use Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab240\AbstractRetorno;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;
use Eduardokum\LaravelBoleto\Contracts\Cnab\RetornoCnab240;
use Eduardokum\LaravelBoleto\Exception\ValidationException;
use Eduardokum\LaravelBoleto\Util;
use Illuminate\Support\Arr;

class Ailos extends AbstractRetorno implements RetornoCnab240
{
    /**
     * Código do banco
     *
     * @var string
     */
    protected $codigoBanco = BoletoContract::COD_BANCO_AILOS;

    /**
     * Array com as ocorrencias do banco;
     *
     * @var array
     */
    private $ocorrencias = [
        '02' => 'Entrada Confirmada',
        '03' => 'Entrada Rejeitada',
        '06' => 'Liquidação',
        '07' => 'Confirmação do Recebimento da Instrução de Desconto',
        '08' => 'Confirmação do Recebimento do Cancelamento do Desconto',
        '09' => 'Baixa',
        '12' => 'Confirmação Recebimento Instrução de Abatimento',
        '13' => 'Confirmação Recebimento Instrução de Cancelamento Abatimento',
        '14' => 'Confirmação Recebimento Instrução Alteração de Vencimento',
        '17' => 'Liquidação Após Baixa ou Liquidação Título Não Registrado',
        '19' => 'Confirmação Recebimento Instrução de Protesto',
        '20' => 'Confirmação Recebimento Instrução de Sustação/Cancelamento de Protesto',
        '22' => 'Título Enviado ao Cartório',
        '23' => 'Remessa a Cartório (Aponte em Cartório)',
        '24' => 'Retirada de Cartório e Manutenção em Carteira',
        '25' => 'Protestado e Baixado (Baixa por Ter Sido Protestado) ',
        '26' => 'Instrução Rejeitada',
        '27' => 'Confirmação do Pedido de Alteração de Outros Dados ',
        '28' => 'Débito de Tarifas/Custas',
        '36' => 'Confirmação de envio de e-mail e SMS',
        '37' => 'Envio de e-mail/SMS rejeitado',
        '42' => 'Confirmação da alteração dos dados do Sacado',
        '46' => 'Instrução para cancelar protesto confirmada',
        '51' => 'Título DDA reconhecido pelo Pagador (quando o pagador aceitar o boleto depois de ter recusado)',
        '52' => 'Título DDA não reconhecido pelo Pagador',
        '76' => 'Liquidação CEE (boleto emitido na modalidade Cooperativa Emite e Expede)',
        '77' => 'Liquidação após Baixa ou Liquidação Título Não Registrado CEE (boleto emitido na modalidade Cooperativa Emite e Expede)',
        '89' => 'Rejeição cartorária (Visualizar motivo na última página deste manual)',
        '91' => 'Título em aberto não enviado ao pagador',
        '92' => 'Inconsistência Negativação Serasa',
        '93' => 'Incluir Serasa',
        '94' => 'Excluir Serasa',
        '95' => 'Instrução de SMS',
        '96' => 'Cancelamento Instrução SMS',
        '97' => 'Confirmação de instrução automática de protesto',
        '98' => 'Excluir Protesto com carta de anuência',
    ];

    /**
     * Array com as possiveis rejeicoes do banco.
     *
     * @var array
     */
    private $rejeicoes = [
        '01' => "Código do Banco Inválido",
        '02' => "Código do Registro Detalhe Inválido",
        '03' => "Código do Segmento Inválido",
        '04' => "Código de Movimento Não Permitido para Carteira",
        '05' => "Código de Movimento Inválido",
        '06' => "Tipo/Número de Inscrição do Cedente Inválidos",
        '07' => "Agência/Conta/DV Inválido",
        '08' => "Nosso Número Inválido",
        '09' => "Nosso Número Duplicado",
        '10' => "Carteira Inválida",
        '11' => "Forma de Cadastramento do Título Inválido",
        '12' => "Tipo de Documento Inválido",
        '13' => "Identificação da Emissão do Boleto Inválida",
        '14' => "Identificação da Distribuição do Boleto Inválida",
        '15' => "Características da Cobrança Incompatíveis",
        '16' => "Data de Vencimento Inválida",
        '17' => "Data de Vencimento Anterior à Data de Emissão",
        '18' => "Vencimento Fora do Prazo de Operação",
        '19' => "Título a Cargo de Bancos Correspondentes com Vencimento Inferior a XX Dias",
        '20' => "Valor do Título Inválido",
        '21' => "Espécie do Título Inválida",
        '22' => "Espécie do Título Não Permitida para a Carteira",
        '23' => "Aceite Inválido",
        '24' => "Data da Emissão Inválida",
        '25' => "Data da Emissão Posterior a Data de Entrada",
        '26' => "Código de Juros de Mora Inválido",
        '27' => "Vlr/Taxa de Juros de Mora Inválido",
        '28' => "Código do Desconto Inválido",
        '29' => "Valor do Desconto Maior ou Igual ao Valor do Título",
        '30' => "Desconto a Conceder Não Confere",
        '31' => "Concessão de Desconto - Já Existe Desconto Anterior",
        '33' => "Valor do Abatimento Inválido",
        '34' => "Valor do Abatimento Maior ou Igual ao Valor do Título",
        '35' => "Valor a Conceder Não Confere",
        '36' => "Concessão de Abatimento - Já Existe Abatimento Anterior",
        '37' => "Código para Protesto Inválido",
        '38' => "Prazo para Protesto Inválido",
        '39' => "Pedido de Protesto Não Permitido para o Título",
        '40' => "Título com Ordem de Protesto Emitida",
        '41' => "Pedido de Cancelamento/Sustação para Títulos sem Instrução de Protesto",
        '42' => "Código para Baixa/Devolução Inválido",
        '43' => "Prazo para Baixa/Devolução Inválido",
        '44' => "Código da Moeda Inválido",
        '45' => "Nome do Sacado Não Informado",
        '46' => "Tipo/Número de Inscrição do Sacado Inválidos",
        '47' => "Endereço do Sacado Não Informado",
        '48' => "CEP Inválido",
        '49' => "CEP Sem Praça de Cobrança (Não Localizado)",
        '50' => "CEP Referente a um Banco Correspondente",
        '51' => "CEP incompatível com a Unidade da Federação",
        '52' => "Unidade da Federação Inválida",
        '53' => "Tipo/Número de Inscrição do Sacador/Avalista Inválidos",
        '54' => "Sacador/Avalista Não Informado",
        '55' => "Nosso número no Banco Correspondente Não Informado",
        '56' => "Código do Banco Correspondente Não Informado",
        '57' => "Código da Multa Inválido",
        '58' => "Data da Multa Inválida",
        '59' => "Valor/Percentual da Multa Inválido",
        '60' => "Movimento para Título Não Cadastrado",
        '61' => "Alteração da Agência Cobradora/DV Inválida",
        '62' => "Tipo de Impressão Inválido",
        '63' => "Entrada para Título já cadastrado",
        '64' => "Número da Linha Inválido",
        '65' => "Código do Banco para Débito Inválido",
        '66' => "Agência/Conta/DV para Débito Inválido",
        '79' => "Data Juros de Mora Inválido",
        '80' => "Data do Desconto Inválida",
        '86' => "Seu Número Inválido",
        '89' => "E-mail/SMS devolvido - endereço de e-mail ou número do celular incorreto",
        '91' => "E-mail/número do celular do sacado não informado",
        '96' => "Número do Convenio invalido",
        '97' => "Rejeição da alteração do prazo limite de recebimento",
        '98' => "Rejeição de dispensa de prazo limite de recebimento",
        '99' => "Rejeição da alteração do número do título dado pelo cedente",
        'A2' => "Rejeição da alteração dos dados do sacado",
        'A4' => "Sacado DDA",
        'A3' => "Rejeição da alteração dos dados do sacador/avalista",
        'A4R1' => "Sacado DDA / Registro online",
        'A4P1' => "Sacado DDA / Envio Cooperativa E/E PJ",
        'A5' => "Registro Rejeitado - Título já Liquidado",
        'A7' => "Título já se encontra na situação Pretendida",
        'A8' => "Título pendente na CIP (Existe uma instrução pendente de processamento na CIP)",
        'A9' => "Título pendente na CIP (título é DDA e não possui número de identificação na CIP)",
        'BI' => "Beneficiário Divergente",
        'B3' => "Tipo de Pagamento Invalido",
        'B4' => "Vlr Max/Perc Invalido",
        'B5' => "Vlr Min/Perc Invalido",
        'B6' => "Parâmetro Pag. Divergente não habilitado no convênio de cobrança",
        'H3' => "Dias para prot. Inv. Min/máx (Número de dias de protesto está fora do limite mínimo e máximo parametrizados)",
        'H4' => "Prazo prot. Inv. (Prazo para protesto inválido)",
        'H6' => "Prot. não habilitado (Cooperado não está habilitado a protestar)",
        'NP' => "Boleto não protestado devido ao CEP do pagador incorreto",
        'P1' => "Enviado Cooperativa Emite e Expede",
        'PC' => "Boleto PCR (pagador não possui DDA ativo)",
        'R1' => "Registro Online",
        'S1' => "Cancelamento de Instrução de negativação não processada",
        'XA' => "Título em processo de registro",
        'XW' => "Serviço de SMS não contratado",
    ];

    /**
     * Roda antes dos metodos de processar
     */
    protected function init()
    {
        $this->totais = [
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
                    'nome' => $this->rem(149, 188, $detalhe),
                    'documento' => $this->rem(134, 148, $detalhe),
                ])
                ->setValorTarifa(Util::nFloat($this->rem(199, 213, $detalhe) / 100, 2, false));

            /**
             * ocorrencias
             */
            $msgAdicional = str_split(sprintf('%08s', $this->rem(214, 223, $detalhe)), 2) + array_fill(0, 5, '');
            if ($d->hasOcorrencia('06', '17')) {
                $this->totais['liquidados']++;
                $d->setOcorrenciaTipo($d::OCORRENCIA_LIQUIDADA);
            } elseif ($d->hasOcorrencia('02')) {
                $this->totais['entradas']++;
                if (array_search('a4', array_map('strtolower', $msgAdicional)) !== false) {
                    $d->getPagador()->setDda(true);
                }
                $d->setOcorrenciaTipo($d::OCORRENCIA_ENTRADA);
            } elseif ($d->hasOcorrencia('09')) {
                $this->totais['baixados']++;
                $d->setOcorrenciaTipo($d::OCORRENCIA_BAIXADA);
            } elseif ($d->hasOcorrencia('25')) {
                $this->totais['protestados']++;
                $d->setOcorrenciaTipo($d::OCORRENCIA_PROTESTADA);
            } elseif ($d->hasOcorrencia('27', '14')) {
                $this->totais['alterados']++;
                $d->setOcorrenciaTipo($d::OCORRENCIA_ALTERACAO);
            } elseif ($d->hasOcorrencia('03', '26', '30', '36')) {
                $this->totais['erros']++;
                $error = Util::appendStrings(
                    Arr::get($this->rejeicoes, $msgAdicional[0], ''),
                    Arr::get($this->rejeicoes, $msgAdicional[1], ''),
                    Arr::get($this->rejeicoes, $msgAdicional[2], ''),
                    Arr::get($this->rejeicoes, $msgAdicional[3], ''),
                    Arr::get($this->rejeicoes, $msgAdicional[4], '')
                );
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
