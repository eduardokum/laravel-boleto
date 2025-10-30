<?php

namespace Eduardokum\LaravelBoleto\Cnab\Pagamento\Retorno\Cnab240\Banco;

use Illuminate\Support\Arr;
use Eduardokum\LaravelBoleto\Util;
use Eduardokum\LaravelBoleto\Exception\ValidationException;
use Eduardokum\LaravelBoleto\Cnab\Pagamento\Retorno\Cnab240\AbstractRetorno;

/**
 * Class Sicredi
 * @package Eduardokum\LaravelBoleto\Cnab\Pagamento\Retorno\Cnab240\Banco
 */
class Sicredi extends AbstractRetorno
{
    /**
     * Código do banco
     * @var string
     */
    protected $codigoBanco = '748';

    /**
     * Array com as ocorrências do banco (G059)
     * @var array
     */
    private $ocorrencias = [
        // Ocorrências gerais - Sucesso
        '00' => 'Crédito ou débito efetivado',
        '03' => 'Débito autorizado pela agência – Efetuado',
        'BD' => 'Inclusão efetuada com sucesso',
        'BE' => 'Alteração efetuada com sucesso',
        'BF' => 'Exclusão efetuada com sucesso',
        'BI' => 'Transferência efetuada com sucesso',
        'BJ' => 'Pagamento efetuado com sucesso',
        'BN' => 'Operação de consignação incluída com sucesso',
        'BO' => 'Operação de consignação alterada com sucesso',
        'BP' => 'Operação de consignação excluída com sucesso',
        'BQ' => 'Operação de consignação liquidada com sucesso',
        'ZC' => 'Confirmação de Antecipação de Valor',
        'ZD' => 'Antecipação parcial de valor',

        // Ocorrências - Cancelamento
        '02' => 'Crédito ou débito cancelado pelo pagador/credor',

        // Ocorrências - Rejeições/Erros
        '01' => 'Insuficiência de fundos - Débito Não Efetuado',
        'AA' => 'Controle inválido',
        'AB' => 'Tipo de operação inválido',
        'AC' => 'Tipo de serviço inválido',
        'AD' => 'Forma de lançamento inválida',
        'AE' => 'Tipo/número de inscrição inválido',
        'AF' => 'Código de convênio inválido',
        'AG' => 'Agência/conta corrente/DV inválido',
        'AH' => 'Nº sequencial do registro no lote inválido',
        'AI' => 'Código de segmento de detalhe inválido',
        'AJ' => 'Tipo de movimento inválido',
        'AK' => 'Código da câmara de compensação do banco favorecido/depositário inválido',
        'AL' => 'Código do banco favorecido ou depositário inválido',
        'AM' => 'Agência mantenedora da conta corrente do favorecido inválida',
        'AN' => 'Conta corrente/DV do favorecido inválido',
        'AO' => 'Nome do favorecido não informado',
        'AP' => 'Data lançamento inválido',
        'AQ' => 'Tipo/quantidade da moeda inválido',
        'AR' => 'Valor do lançamento inválido',
        'AS' => 'Aviso ao favorecido - identificação inválida',
        'AT' => 'Tipo/número de inscrição do favorecido inválido',
        'AU' => 'Logradouro do favorecido não informado',
        'AV' => 'Nº do local do favorecido não informado',
        'AW' => 'Cidade do favorecido não informada',
        'AX' => 'CEP/complemento do favorecido inválido',
        'AY' => 'Sigla do estado do favorecido inválida',
        'AZ' => 'Código/nome do banco depositário inválido',
        'BA' => 'Código/nome da agência depositária não informado',
        'BB' => 'Seu número inválido',
        'BC' => 'Nosso número inválido',
        'BG' => 'Agência/conta impedida legalmente',
        'BH' => 'Empresa não pagou salário',
        'BJ' => 'Empresa não enviou remessa do mutuário',
        'BK' => 'Empresa não enviou remessa no vencimento',
        'BL' => 'Valor da parcela inválida',
        'BM' => 'Identificação do contrato inválida',
        'CA' => 'Código de barras - código do banco inválido',
        'CB' => 'Código de barras - código da moeda inválido',
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
        'HA' => 'Lote não aceito',
        'HB' => 'Inscrição da empresa inválida para o contrato',
        'HC' => 'Convênio com a empresa inexistente/inválido para o contrato',
        'HD' => 'Agência/conta corrente da empresa inexistente/inválido para o contrato',
        'HE' => 'Tipo de serviço inválido para o contrato',
        'HF' => 'Conta corrente da empresa com saldo insuficiente',
        'HG' => 'Lote de serviço fora de sequência',
        'HH' => 'Lote de serviço inválido',
        'HI' => 'Arquivo não aceito',
        'HJ' => 'Tipo de registro inválido',
        'HK' => 'Código remessa / retorno inválido',
        'HL' => 'Versão de leiaute inválida',
        'HM' => 'Mutuário não identificado',
        'HN' => 'Tipo do benefício não permite empréstimo',
        'HO' => 'Benefício cessado/suspenso',
        'HP' => 'Benefício possui representante legal',
        'HQ' => 'Benefício é do tipo PA (pensão alimentícia)',
        'HR' => 'Quantidade de contratos permitida excedida',
        'HS' => 'Benefício não pertence ao banco informado',
        'HT' => 'Início do desconto informado já ultrapassado',
        'HU' => 'Número da parcela inválida',
        'HV' => 'Quantidade de parcela inválida',
        'HW' => 'Margem consignável excedida para o mutuário dentro do prazo do contrato',
        'HX' => 'Empréstimo já cadastrado',
        'HY' => 'Empréstimo inexistente',
        'HZ' => 'Empréstimo já encerrado',
        'H1' => 'Arquivo sem trailer',
        'H2' => 'Mutuário sem crédito na competência',
        'H3' => 'Não descontado – outros motivos',
        'H4' => 'Retorno de crédito não pago',
        'H5' => 'Cancelamento de empréstimo retroativo',
        'H6' => 'Outros motivos de glosa',
        'H7' => 'Margem consignável excedida para o mutuário acima do prazo do contrato',
        'H8' => 'Mutuário desligado do empregador',
        'H9' => 'Mutuário afastado por licença',
        'TA' => 'Lote não aceito - totais do lote com diferença',
        'YA' => 'Título não encontrado',
        'YB' => 'Identificador registro opcional inválido',
        'YC' => 'Código padrão inválido',
        'YD' => 'Código de ocorrência inválido',
        'YE' => 'Complemento de ocorrência inválido',
        'YF' => 'Alegação já informada',
        'ZA' => 'Agência / Conta do Favorecido Substituída',
        'ZB' => 'Divergência entre o primeiro e último nome do beneficiário versus primeiro e último nome na Receita Federal',
        'ZE' => 'Título bloqueado na base',
        'ZF' => 'Sistema em contingência – título valor maior que referência',
        'ZG' => 'Sistema em contingência - Título vencido',
        'ZH' => 'Sistema em contingência - Título Indexado',
        'ZI' => 'Beneficiário divergente',
        'ZJ' => 'Limite de pagamento parciais excedido',
        'ZK' => 'Boleto já liquidado',
    ];

    /**
     * Array com as possíveis rejeições do banco
     * @var array
     */
    private $rejeicoes = [
        '01' => 'Insuficiência de fundos - Débito Não Efetuado',
        'AA' => 'Controle inválido',
        'AB' => 'Tipo de operação inválido',
        'AC' => 'Tipo de serviço inválido',
        'AD' => 'Forma de lançamento inválida',
        'AE' => 'Tipo/número de inscrição inválido',
        'AF' => 'Código de convênio inválido',
        'AG' => 'Agência/conta corrente/DV inválido',
        'AH' => 'Nº sequencial do registro no lote inválido',
        'AI' => 'Código de segmento de detalhe inválido',
        'AJ' => 'Tipo de movimento inválido',
        'AK' => 'Código da câmara de compensação do banco favorecido/depositário inválido',
        'AL' => 'Código do banco favorecido ou depositário inválido',
        'AM' => 'Agência mantenedora da conta corrente do favorecido inválida',
        'AN' => 'Conta corrente/DV do favorecido inválido',
        'AO' => 'Nome do favorecido não informado',
        'AP' => 'Data lançamento inválido',
        'AQ' => 'Tipo/quantidade da moeda inválido',
        'AR' => 'Valor do lançamento inválido',
        'AS' => 'Aviso ao favorecido - identificação inválida',
        'AT' => 'Tipo/número de inscrição do favorecido inválido',
        'AU' => 'Logradouro do favorecido não informado',
        'AV' => 'Nº do local do favorecido não informado',
        'AW' => 'Cidade do favorecido não informada',
        'AX' => 'CEP/complemento do favorecido inválido',
        'AY' => 'Sigla do estado do favorecido inválida',
        'AZ' => 'Código/nome do banco depositário inválido',
        'BA' => 'Código/nome da agência depositária não informado',
        'BB' => 'Seu número inválido',
        'BC' => 'Nosso número inválido',
        'BG' => 'Agência/conta impedida legalmente',
        'CA' => 'Código de barras - código do banco inválido',
        'CB' => 'Código de barras - código da moeda inválido',
        'CC' => 'Código de barras - dígito verificador geral inválido',
        'CD' => 'Código de barras - valor do título inválido',
        'CE' => 'Código de barras - campo livre inválido',
        'CF' => 'Valor do documento inválido',
        'CG' => 'Valor do abatimento inválido',
        'CH' => 'Valor do desconto inválido',
        'CI' => 'Valor de mora inválido',
        'CJ' => 'Valor da multa inválido',
        'HA' => 'Lote não aceito',
        'HB' => 'Inscrição da empresa inválida para o contrato',
        'HC' => 'Convênio com a empresa inexistente/inválido para o contrato',
        'HD' => 'Agência/conta corrente da empresa inexistente/inválido para o contrato',
        'HE' => 'Tipo de serviço inválido para o contrato',
        'HF' => 'Conta corrente da empresa com saldo insuficiente',
        'HG' => 'Lote de serviço fora de sequência',
        'HH' => 'Lote de serviço inválido',
        'HI' => 'Arquivo não aceito',
        'HJ' => 'Tipo de registro inválido',
        'HK' => 'Código remessa / retorno inválido',
        'HL' => 'Versão de leiaute inválida',
        'TA' => 'Lote não aceito - totais do lote com diferença',
        'YA' => 'Título não encontrado',
        'YB' => 'Identificador registro opcional inválido',
        'YC' => 'Código padrão inválido',
        'YD' => 'Código de ocorrência inválido',
        'YE' => 'Complemento de ocorrência inválido',
        'YF' => 'Alegação já informada',
        'ZB' => 'Divergência entre o primeiro e último nome do beneficiário versus primeiro e último nome na Receita Federal',
        'ZE' => 'Título bloqueado na base',
        'ZF' => 'Sistema em contingência – título valor maior que referência',
        'ZG' => 'Sistema em contingência - Título vencido',
        'ZH' => 'Sistema em contingência - Título Indexado',
        'ZI' => 'Beneficiário divergente',
        'ZJ' => 'Limite de pagamento parciais excedido',
        'ZK' => 'Boleto já liquidado',
    ];

    /**
     * Roda antes dos métodos de processar
     */
    protected function init()
    {
        $this->totais = [
            'pagos'      => 0,
            'rejeitados' => 0,
            'pendentes'  => 0,
            'cancelados' => 0,
            'erros'      => 0,
            'valor_total' => 0,
        ];
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
            // Segmento A - Dados principais do pagamento
            // G059 - Código das ocorrências para retorno (posições 231-240)
            $ocorrencias = trim($this->rem(231, 240, $detalhe));

            $d->setOcorrencia($ocorrencias)
                ->setOcorrenciaDescricao(Arr::get($this->ocorrencias, $ocorrencias, 'Desconhecida'))
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

            // Determina o tipo de pagamento pela forma de lançamento do lote
            $formaLancamento = $this->getHeaderLote()->getFormaLancamento();
            $tipoPagamento = 'TED'; // Padrão
            if ($formaLancamento == '03') {
                $tipoPagamento = 'DOC';
            } elseif ($formaLancamento == '41') {
                $tipoPagamento = 'TED';
            }
            $d->setTipoPagamento($tipoPagamento);

            // Processa ocorrências
            // Pode conter até 5 códigos de ocorrência (10 caracteres = 5 códigos de 2 dígitos)
            $codigos = str_split($ocorrencias, 2);
            $primeiraOcorrencia = trim($codigos[0] ?? '');

            if (in_array($primeiraOcorrencia, ['00', '03', 'BD', 'BE', 'BF', 'BI', 'BJ', 'BN', 'BO', 'BP', 'BQ', 'ZC', 'ZD'])) {
                // Pagamento efetivado/sucesso
                $this->totais['pagos']++;
                $this->totais['valor_total'] += $d->getValor();
                $d->setOcorrenciaTipo($d::OCORRENCIA_PAGO);
            } elseif ($primeiraOcorrencia == '02') {
                // Pagamento cancelado
                $this->totais['cancelados']++;
                $d->setOcorrenciaTipo($d::OCORRENCIA_CANCELADO);
            } elseif (
                $primeiraOcorrencia == '01' || substr($primeiraOcorrencia, 0, 1) == 'A' || substr($primeiraOcorrencia, 0, 1) == 'H' ||
                substr($primeiraOcorrencia, 0, 1) == 'C' || substr($primeiraOcorrencia, 0, 1) == 'Y' ||
                substr($primeiraOcorrencia, 0, 1) == 'Z' || in_array($primeiraOcorrencia, ['TA', 'YA', 'YB', 'YC', 'YD', 'YE', 'YF'])
            ) {
                // Pagamento rejeitado ou com erro
                $this->totais['rejeitados']++;
                $d->setOcorrenciaTipo($d::OCORRENCIA_REJEITADO);
            } else {
                $d->setOcorrenciaTipo($d::OCORRENCIA_OUTROS);
            }
        }

        if ($segmentType == 'B') {
            // Segmento B - Dados complementares do favorecido
            $d->setFavorecido([
                'documento' => $this->rem(19, 32, $detalhe),
                'nome' => '',
                'endereco' => $this->rem(33, 62, $detalhe),
                'numero' => $this->rem(63, 67, $detalhe),
                'complemento' => $this->rem(68, 82, $detalhe),
                'bairro' => $this->rem(83, 97, $detalhe),
                'cidade' => $this->rem(98, 117, $detalhe),
                'cep' => $this->rem(118, 125, $detalhe),
                'uf' => $this->rem(126, 127, $detalhe),
            ]);

            // Verifica se há códigos de ocorrência/rejeição no Segmento B (posições 231-240)
            $codigosOcorrencia = trim($this->rem(231, 240, $detalhe));
            if (!empty($codigosOcorrencia)) {
                // Processa múltiplos códigos (até 5 códigos de 2 dígitos)
                $codigos = str_split($codigosOcorrencia, 2);
                foreach ($codigos as $codigo) {
                    $codigo = trim($codigo);
                    if (!empty($codigo) && $codigo != '00' && !in_array($codigo, ['BD', 'BE', 'BF', 'BI', 'BJ', 'BN', 'BO', 'BP', 'BQ'])) {
                        $descricao = Arr::get($this->rejeicoes, $codigo, Arr::get($this->ocorrencias, $codigo, 'Rejeição Desconhecida'));
                        $d->addRejeicao($codigo, $descricao);
                    }
                }
            }

            // Se tem rejeições E não foi marcado como pago, marca como erro
            // Não sobrescreve se já foi definido como PAGO no Segmento A
            if (count($d->getRejeicoes()) > 0 && $d->getOcorrenciaTipo() != $d::OCORRENCIA_PAGO) {
                $this->totais['erros']++;
                $error = implode('; ', $d->getRejeicoes());
                $d->setError($error);
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
            ->setQtdPagamentos((int) $this->rem(18, 23, $trailerLote) - 2); // Subtrai header e trailer

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
