<?php

namespace Eduardokum\LaravelBoleto\Cnab\Pagamento\Retorno\Cnab240\Banco;

use Illuminate\Support\Arr;
use Eduardokum\LaravelBoleto\Util;
use Eduardokum\LaravelBoleto\Exception\ValidationException;
use Eduardokum\LaravelBoleto\Cnab\Pagamento\Retorno\Cnab240\AbstractRetorno;

class Caixa extends AbstractRetorno
{
    /**
     * Código do banco (Caixa)
     * @var string
     */
    protected $codigoBanco = '104';

    /**
     * Ocorrências G059 (tabela fornecida)
     * @var array
     */
    private $ocorrencias = [
        '00' => 'Crédito ou Débito Efetivado',
        '01' => 'Insuficiência de Fundos - Débito não efetuado',
        '02' => 'Crédito ou Débito Cancelado pelo Pagador/Credor',
        '03' => 'Débito Autorizado pela Agência - Efetuado',
        'HA' => 'Lote não aceito',
        'HB' => 'Inscrição da Empresa Inválida para o Contrato',
        'HC' => 'Convênio com a Empresa Inexistente/Inválido para o Contrato',
        'HD' => 'Agência/Conta Corrente da Empresa Inexistente/Inválido para o Contrato',
        'HE' => 'Tipo de Serviço Inválido para o Contrato',
        'HF' => 'Conta Corrente da Empresa com Saldo Insuficiente',
        'HG' => 'Lote de Serviço fora de Sequência',
        'HH' => 'Lote de serviço inválido',
        'HI' => 'Número da remessa inválido',
        'HJ' => 'Arquivo sem HEADER',
        'HK' => 'Código remessa/retorno inválido',
        'HL' => 'Versão de layout inválida',
        'HM' => 'Versão do arquivo inválido',
        'HO' => 'CPF/CNPJ do favorecido diferente do da base da conta',
        'HP' => 'Tipo da inscrição do favorecido inválido',
        'HV' => 'Quantidade de parcela inválida',
        'AA' => 'Controle inválido',
        'AB' => 'Tipo de operação inválido',
        'AC' => 'Tipo de serviço inválido',
        'AD' => 'Forma de Lançamento inválida',
        'AE' => 'Tipo/Número de inscrição inválido',
        'AF' => 'Código de convênio inválido',
        'AG' => 'Agência/Conta corrente/DV inválido',
        'AH' => 'Número sequencial do registro no lote inválido',
        'AI' => 'Código de segmento de detalhe inválido',
        'AJ' => 'Tipo de movimento inválido',
        'AK' => 'Código da câmara de compensação do banco favorecido/depositário inválido',
        'AL' => 'Código do banco favorecido ou depositário inválido',
        'AM' => 'Agência mantenedora da conta corrente do favorecido inválida',
        'AN' => 'Conta Corrente / DV do favorecido inválido',
        'AO' => 'Nome do favorecido não informado',
        'AP' => 'Data de lançamento inválido',
        'AQ' => 'Tipo/quantidade de moeda inválida',
        'AR' => 'Valor do lançamento inválido',
        'AS' => 'Aviso ao favorecido - identificação inválida',
        'AT' => 'Tipo/número de inscrição do favorecido inválido',
        'AU' => 'Logradouro do favorecido não informado',
        'AV' => 'Número do local do favorecido não informado',
        'AW' => 'Cidade do favorecido não informada',
        'AX' => 'CEP/complemento do favorecido inválido',
        'AY' => 'Sigla do Estado do Favorecido Inválido',
        'AZ' => 'Código/nome do banco depositário inválido',
        'BA' => 'Código/nome da agência depositária não informado',
        'BB' => 'Seu número inválido',
        'BC' => 'Nosso número inválido',
        'BD' => 'Inclusão efetuada com sucesso',
        'BE' => 'Alteração efetuada com sucesso',
        'BF' => 'Exclusão efetuada com sucesso',
        'BG' => 'Agência/conta impedida legalmente',
        'BK' => 'Documento vencido',
        'BL' => 'Valor da parcela inválido',
        'BV' => 'Tipo boleto não admite juros/multa/desc/abatimento',
        'BX' => 'Data limite para pagamento inválido',
        'BY' => 'Validação do título indisponível',
        'BZ' => 'Inclusão efetuada sem validação do título',
        'CA' => 'Código de barras - código do banco inválido',
        'CB' => 'Código de barras - código da moeda inválida',
        'CC' => 'Código de barras - dígito verificador geral inválido',
        'CD' => 'Código de barras - valor do título inválido',
        'CE' => 'Código de barras - campo livre inválido',
        'CF' => 'Valor do documento inválido',
        'CG' => 'Valor do abatimento inválido',
        'CH' => 'Valor do desconto inválido',
        'CI' => 'Valor de mora inválido',
        'CJ' => 'Valor da multa inválido',
        'CK' => 'Valor do IR inválido',
        'CL' => 'Valor do ISS inválido',
        'CM' => 'Valor do IOF inválido',
        'CN' => 'Valor de outras deduções inválido',
        'CO' => 'Valor de outros acréscimos inválido',
        'CP' => 'Valor do INSS inválido',
        'CQ' => 'Código de barras inválido',
        'CR' => 'Código de pagamento/receita inválida',
        'CS' => 'Identificação do tributo inválida',
        'CT' => 'Competência/referência/período apuração inválida',
        'CV' => 'Não é permitido agendamento de DOC',
        'CY' => 'Tipo de inscrição Pagador/Beneficiário inválido',
        'CZ' => 'Código de barras duplicado',
        'DA' => 'Beneficiário não cadastrado',
        'DB' => 'Situação do beneficiário não permite pagamento',
        'DC' => 'Não foi possível realizar a baixa do boleto. Reenvie o agendamento.',
        'DE' => 'ID NÃO tratado via SIACC',
        'DF' => 'ID com outras falhas',
        'DG' => 'Valor divergente do ID',
        'DH' => 'ID já se encontra pago',
        'DI' => 'ID vencido',
        'DJ' => 'ID Conta Bloqueada',
        'DL' => 'Valor recebido diferente do acordado/esperado',
        'DM' => 'Pagamento rejeitado pela instituição financeira do favorecido',
        'EA' => 'Excedeu o limite de horários para remessas – D0',
        'PA' => 'Com impedimentos para finalização do serviço - Pix',
        'PC' => 'Conta do favorecido bloqueada ou encerrada',
        'PD' => 'Tipo de conta do favorecido incorreto',
        'PE' => 'Tipo de transação não permitido para a conta do favorecido',
        'PG' => 'CPF/CNPJ inválido/não preenchido - Pix',
        'PH' => 'Verifique se o pagamento foi efetivado. Se não, reenvie - Pix',
        'PI' => 'QR Code diferente de dinâmico com vencimento – Pix',
        'PP' => 'Valor do pagamento inválido – Pix QR Code',
        'PX' => 'Falha técnica, não foi possível realizar o pagamento - Pix',
        'PZ' => 'Pix QR Code rejeitado pela instituição financeira do favorecido',
        'TA' => 'Lote não aceito - totais de lote com diferença',
        'TB' => 'Lote sem trailler',
        'TC' => 'Lote de Arquivo sem trailler',
        'WA' => 'Valor supera o limite permitido - Pix',
        'WC' => 'Inclusão rejeitada - contingência Pix',
        'WD' => 'Ultrapassou horário permitido para pagamento - Pix',
        'WE' => 'CPF/CNPJ do favorecido inconsistente com o da conta - Pix',
        'WF' => 'Agente não autorizado para realizar a operação',
        'WI' => 'Data e hora do envio da mensagem inválidas',
        'WJ' => 'ID inválido',
        'WL' => 'Tipo de chave Pix não preenchido',
        'WN' => 'Não foi informada chave Pix',
        'WM' => 'Chave/QR Code/Payload inválido - Pix',
        'WO' => 'Verifique se o pagamento foi efetivado. Se não, reenvie',
        'WP' => 'QR Code inválido ou já consta como pago no Bacen',
        'WK' => 'Tipo de chave Pix inválida',
        'WW' => 'Sacador/avalista divergente',
        'YA' => 'Título não encontrado – procure o beneficiário',
        'YB' => 'Identificador registro opcional inválido',
        'YC' => 'Código padrão inválido',
        'YD' => 'Código de ocorrência inválido',
        'YE' => 'Complemento de ocorrência inválido',
        'YF' => 'Alegação já informada',
        'YY' => 'Faltou Segmento J-52',
        'ZA' => 'Agência/conta do favorecido substituída',
        'ZB' => 'Segmento J53 obrigatório para agregador',
        'ZC' => 'CNPJ do agregador deve ser igual ao do compromisso',
        'ZE' => 'Título bloqueado na base',
        'ZJ' => 'Limite de pagamentos parciais excedidos',
        'ZK' => 'Pagamento Rejeitado - Boleto Já Liquidado',
        'ZQ' => 'Não permitida contratação de pagamento de Pix para convênio PF',
        'ZY' => 'Pagamento Rejeitado - Beneficiário Divergente',
        'ZW' => 'Dados do Pagador Incorretos',
    ];

    /**
     * Rejeições (espelho das ocorrências que não são sucesso/cancelamento)
     * @var array
     */
    private $rejeicoes = [];

    /**
     * Roda antes de processar o arquivo
     */
    protected function init()
    {
        $this->totais = [
            'pagos'       => 0,
            'rejeitados'  => 0,
            'pendentes'   => 0,
            'cancelados'  => 0,
            'erros'       => 0,
            'valor_total' => 0,
        ];

        // Usa o mesmo catálogo para rejeições, exceto os de sucesso/cancelamento
        $sucesso = ['00', '03', 'BD', 'BE', 'BF', 'BI', 'BJ'];
        $cancel = ['02'];
        foreach ($this->ocorrencias as $cod => $desc) {
            if (!in_array($cod, $sucesso) && !in_array($cod, $cancel)) {
                $this->rejeicoes[$cod] = $desc;
            }
        }
    }

    /**
     * @param array $header
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
            ->setAgencia($this->rem(53, 57, $header))
            ->setAgenciaDv($this->rem(58, 58, $header))
            ->setConta($this->rem(59, 70, $header))
            ->setContaDv($this->rem(71, 71, $header))
            ->setNomeEmpresa($this->rem(73, 102, $header))
            ->setNomeBanco($this->rem(103, 132, $header))
            ->setCodigoRemessaRetorno($this->rem(143, 143, $header))
            ->setData($this->rem(144, 151, $header))
            ->setHora($this->rem(152, 157, $header))
            ->setNumeroSequencialArquivo($this->rem(158, 163, $header))
            ->setVersaoLayoutArquivo($this->rem(164, 166, $header));

        return true;
    }

    /**
     * @param array $headerLote
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
            ->setFormaLancamento($this->rem(12, 13, $headerLote))
            ->setVersaoLayoutLote($this->rem(14, 16, $headerLote))
            ->setTipoInscricao($this->rem(18, 18, $headerLote))
            ->setNumeroInscricao($this->rem(19, 32, $headerLote))
            ->setAgencia($this->rem(53, 57, $headerLote))
            ->setAgenciaDv($this->rem(58, 58, $headerLote))
            ->setConta($this->rem(59, 70, $headerLote))
            ->setContaDv($this->rem(71, 71, $headerLote))
            ->setNomeEmpresa($this->rem(73, 102, $headerLote));

        return true;
    }

    /**
     * @param array $detalhe
     * @return bool
     * @throws ValidationException
     */
    protected function processarDetalhe(array $detalhe)
    {
        $d = $this->detalheAtual();
        $segmentType = $this->getSegmentType($detalhe);

        if ($segmentType == 'A') {
            $ocorrenciasCampo = trim($this->rem(231, 240, $detalhe));

            $d->setOcorrencia($ocorrenciasCampo)
                ->setOcorrenciaDescricao(Arr::get($this->ocorrencias, $ocorrenciasCampo, 'Desconhecida'))
                ->setCodigoBancoFavorecido($this->rem(21, 23, $detalhe))
                ->setAgenciaFavorecido($this->rem(24, 28, $detalhe))
                ->setContaFavorecido($this->rem(30, 41, $detalhe))
                ->setSeuNumero($this->rem(74, 93, $detalhe))
                ->setNumeroDocumento(explode('-', trim($this->rem(74, 93, $detalhe)))[0] ?? '')
                ->setNumeroControle(explode('-', trim($this->rem(74, 93, $detalhe)))[1] ?? '')
                ->setDataPagamento($this->rem(94, 101, $detalhe))
                ->setValor(Util::nFloat($this->rem(120, 134, $detalhe) / 100, 2, false))
                ->setNossoNumero($this->rem(135, 154, $detalhe))
                ->setDataEfetivacao($this->rem(155, 162, $detalhe))
                ->setValorRealEfetivado(Util::nFloat($this->rem(163, 177, $detalhe) / 100, 2, false));

            // Tipo de pagamento baseado na forma de lançamento do Lote
            $formaLancamento = $this->getHeaderLote()->getFormaLancamento();
            $tipoPagamento = 'TED';
            if ($formaLancamento == '45') {
                $tipoPagamento = 'PIX';
            } elseif ($formaLancamento == '03' || $formaLancamento == '41') {
                $tipoPagamento = 'TED';
            } elseif ($formaLancamento == '01' || $formaLancamento == '07') {
                $tipoPagamento = 'DOC';
            }
            $d->setTipoPagamento($tipoPagamento);

            // Classificação: usa o primeiro código (2 primeiros chars) para o status
            $codigos = str_split($ocorrenciasCampo, 2);
            $primeira = trim($codigos[0] ?? '');
            if (in_array($primeira, ['00', '03', 'BD', 'BE', 'BF', 'BI', 'BJ'])) {
                $this->totais['pagos']++;
                $this->totais['valor_total'] += $d->getValor();
                $d->setOcorrenciaTipo($d::OCORRENCIA_PAGO);
            } elseif ($primeira === '02') {
                $this->totais['cancelados']++;
                $d->setOcorrenciaTipo($d::OCORRENCIA_CANCELADO);
            } else {
                // Demais códigos tratamos como rejeição/erro (inclui PIX/Tributos/etc.)
                $this->totais['rejeitados']++;
                $d->setOcorrenciaTipo($d::OCORRENCIA_REJEITADO);
            }
        }

        if ($segmentType == 'B') {
            $d->setFavorecido([
                'documento' => $this->rem(19, 32, $detalhe),
                'endereco' => trim($this->rem(33, 62, $detalhe)),
                'numero' => trim($this->rem(63, 67, $detalhe)),
                'complemento' => trim($this->rem(68, 82, $detalhe)),
                'bairro' => trim($this->rem(83, 97, $detalhe)),
                'cidade' => trim($this->rem(98, 117, $detalhe)),
                'cep' => $this->rem(118, 125, $detalhe),
                'uf' => trim($this->rem(126, 127, $detalhe)),
            ]);

            // Ocorrências adicionais no Segmento B (231-240)
            $codigosOcorrencia = trim($this->rem(231, 240, $detalhe));
            if (!empty($codigosOcorrencia)) {
                $codigos = str_split($codigosOcorrencia, 2);
                foreach ($codigos as $codigo) {
                    $codigo = trim($codigo);
                    if (!empty($codigo) && !in_array($codigo, ['00', 'BD', 'BE', 'BF', 'BI', 'BJ'])) {
                        $descricao = Arr::get($this->rejeicoes, $codigo, Arr::get($this->ocorrencias, $codigo, 'Rejeição Desconhecida'));
                        $d->addRejeicao($codigo, $descricao);
                    }
                }
            }

            if (count($d->getRejeicoes()) > 0 && $d->getOcorrenciaTipo() != $d::OCORRENCIA_PAGO) {
                $this->totais['erros']++;
                $d->setError(implode('; ', $d->getRejeicoes()));
                if ($d->getOcorrenciaTipo() != $d::OCORRENCIA_PAGO) {
                    $d->setOcorrenciaTipo($d::OCORRENCIA_ERRO);
                }
            }
        }

        return true;
    }

    /**
     * @param array $trailerLote
     * @return bool
     * @throws ValidationException
     */
    protected function processarTrailerLote(array $trailerLote)
    {
        $this->getTrailerLote()
            ->setCodBanco($this->rem(1, 3, $trailerLote))
            ->setLoteServico($this->rem(4, 7, $trailerLote))
            ->setTipoRegistro($this->rem(8, 8, $trailerLote))
            ->setQtdRegistroLote((int) $this->rem(18, 23, $trailerLote))
            ->setValorTotalPagamentos(Util::nFloat($this->rem(24, 41, $trailerLote) / 100, 2, false))
            ->setQtdPagamentos((int) $this->rem(18, 23, $trailerLote) - 2);

        return true;
    }

    /**
     * @param array $trailer
     * @return bool
     * @throws ValidationException
     */
    protected function processarTrailer(array $trailer)
    {
        $this->getTrailer()
            ->setCodBanco($this->rem(1, 3, $trailer))
            ->setNumeroLote($this->rem(4, 7, $trailer))
            ->setTipoRegistro($this->rem(8, 8, $trailer))
            ->setQtdLotesArquivo((int) $this->rem(18, 23, $trailer))
            ->setQtdRegistroArquivo((int) $this->rem(24, 29, $trailer));

        return true;
    }
}
