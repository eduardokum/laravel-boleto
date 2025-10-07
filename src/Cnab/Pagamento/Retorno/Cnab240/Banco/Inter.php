<?php

namespace Eduardokum\LaravelBoleto\Cnab\Pagamento\Retorno\Cnab240\Banco;

use Illuminate\Support\Arr;
use Eduardokum\LaravelBoleto\Util;
use Eduardokum\LaravelBoleto\Exception\ValidationException;
use Eduardokum\LaravelBoleto\Cnab\Pagamento\Retorno\Cnab240\AbstractRetorno;

class Inter extends AbstractRetorno
{
    /**
     * Código do banco
     * @var string
     */
    protected $codigoBanco = '077';

    /**
     * Array com as ocorrências do banco
     * @var array
     */
    private $ocorrencias = [
        // Ocorrências gerais
        '00' => 'Crédito ou Débito Efetivado',
        '02' => 'Crédito ou Débito Cancelado pelo Pagador/Credor',
        'BD' => 'Inclusão Efetuada com Sucesso',
        'HA' => 'Lote Não Aceito',

        // Ocorrências TED/DOC
        'AR' => 'Valor do Lançamento Inválido',
        'AG' => 'Agência/Conta Corrente/DV Inválido',
        'ZI' => 'Beneficiário divergente',
        'AP' => 'Data Lançamento Inválido',
        'HF' => 'Conta Corrente da Empresa com Saldo Insuficiente',
        'AB' => 'Tipo de Operação Inválido',
        'AC' => 'Tipo de Serviço Inválido',
        'AL' => 'Código do Banco Favorecido, Instituição de Pagamento ou Depositário Inválido',
        'AS' => 'Aviso ao Favorecido - Identificação Inválida',
        'HE' => 'Tipo de Serviço Inválido para o Contrato',

        // Ocorrências PIX
        'PA' => 'Pix não efetivado',
        'PJ' => 'Chave não cadastrada no DICT',
        'PM' => 'Chave de pagamento inválida',
        'PN' => 'Chave de pagamento não informada',
        'PC' => 'QR Code inválido/vencido',
        'PB' => 'Transação interrompida devido a erro no PSP do Recebedor',
        'PD' => 'Tipo incorreto para a conta transacional especificada',
        'PP' => 'Tipo de transação não é suportado/autorizado na conta transacional especificada',
        'PH' => 'Ordem rejeitada pelo PSP do Recebedor',
        'PG' => 'CPF/CNPJ do usuário recebedor incorreto',
        'PI' => 'ISPB do PSP do Pagador Inválido ou inexistente',
    ];

    /**
     * Array com as possíveis rejeições do banco
     * @var array
     */
    private $rejeicoes = [
        // Rejeições gerais
        'AR' => 'Valor do Lançamento Inválido',
        'AG' => 'Agência/Conta Corrente/DV Inválido',
        'ZI' => 'Beneficiário divergente',
        'AP' => 'Data Lançamento Inválido',
        'HF' => 'Conta Corrente da Empresa com Saldo Insuficiente',
        'AB' => 'Tipo de Operação Inválido',
        'AC' => 'Tipo de Serviço Inválido',
        'HA' => 'Lote Não Aceito',
        'AL' => 'Código do Banco Favorecido, Instituição de Pagamento ou Depositário Inválido',
        'AS' => 'Aviso ao Favorecido - Identificação Inválida',
        'HE' => 'Tipo de Serviço Inválido para o Contrato',

        // Rejeições PIX específicas
        'PA' => 'Pix não efetivado',
        'PJ' => 'Chave não cadastrada no DICT',
        'PM' => 'Chave de pagamento inválida',
        'PN' => 'Chave de pagamento não informada',
        'PC' => 'QR Code inválido/vencido',
        'PB' => 'Transação interrompida devido a erro no PSP do Recebedor',
        'PD' => 'Tipo incorreto para a conta transacional especificada',
        'PP' => 'Tipo de transação não é suportado/autorizado na conta transacional especificada',
        'PH' => 'Ordem rejeitada pelo PSP do Recebedor',
        'PG' => 'CPF/CNPJ do usuário recebedor incorreto',
        'PI' => 'ISPB do PSP do Pagador Inválido ou inexistente',
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
            $d->setOcorrencia($this->rem(231, 240, $detalhe))
                ->setOcorrenciaDescricao(Arr::get($this->ocorrencias, trim($this->rem(231, 240, $detalhe)), 'Desconhecida'))
                ->setCodigoBancoFavorecido($this->rem(21, 23, $detalhe))
                ->setAgenciaFavorecido($this->rem(24, 28, $detalhe))
                ->setContaFavorecido($this->rem(30, 41, $detalhe))
                ->setSeuNumero($this->rem(74, 93, $detalhe))
                ->setNumeroDocumento(explode('-', trim($this->rem(74, 93, $detalhe)))[0])
                ->setNumeroControle(explode('-', trim($this->rem(74, 93, $detalhe)))[1])
                ->setDataPagamento($this->rem(94, 101, $detalhe))
                ->setValor(Util::nFloat($this->rem(120, 134, $detalhe) / 100, 2, false))
                ->setNossoNumero($this->rem(135, 154, $detalhe))
                ->setDataEfetivacao($this->rem(155, 162, $detalhe))
                ->setValorRealEfetivado(Util::nFloat($this->rem(163, 177, $detalhe) / 100, 2, false));

            // Determina o tipo de pagamento pela forma de lançamento do lote
            $formaLancamento = $this->getHeaderLote()->getFormaLancamento();
            $tipoPagamento = 'TED'; // Padrão
            if ($formaLancamento == '45') {
                $tipoPagamento = 'PIX';
            } elseif ($formaLancamento == '03') {
                $tipoPagamento = 'TED';
            }
            $d->setTipoPagamento($tipoPagamento);

            // Processa ocorrências
            $ocorrencia = trim($this->rem(231, 240, $detalhe));
            if (in_array($ocorrencia, ['00', 'BD'])) {
                // Pagamento efetivado
                $this->totais['pagos']++;
                $this->totais['valor_total'] += $d->getValor();
                $d->setOcorrenciaTipo($d::OCORRENCIA_PAGO);
            } elseif (in_array($ocorrencia, ['HA', 'AR', 'AG', 'AP', 'HF', 'AB', 'AC', 'AL', 'AS', 'HE', 'ZI', 'PA', 'PJ', 'PM', 'PN', 'PC', 'PB', 'PD', 'PP', 'PH', 'PG', 'PI'])) {
                // Pagamento rejeitado ou com erro
                $this->totais['rejeitados']++;
                $d->setOcorrenciaTipo($d::OCORRENCIA_REJEITADO);
            } elseif ($ocorrencia == '02') {
                // Pagamento cancelado
                $this->totais['cancelados']++;
                $d->setOcorrenciaTipo($d::OCORRENCIA_CANCELADO);
            } else {
                $d->setOcorrenciaTipo($d::OCORRENCIA_OUTROS);
            }
        }

        if ($segmentType == 'B') {
            // Segmento B - Dados complementares
            $d->setFavorecido([
                'documento' => $this->rem(19, 32, $detalhe),
                'nome' => $this->rem(44, 73, $detalhe),
                'endereco' => $this->rem(33, 67, $detalhe),
                'bairro' => '',
                'cidade' => '',
                'uf' => '',
                'cep' => $this->rem(118, 125, $detalhe),
            ]);

            // Verifica se há códigos de rejeição (posições podem variar)
            $codigosRejeicao = str_split($this->rem(214, 223, $detalhe), 2);
            foreach ($codigosRejeicao as $codigo) {
                $codigo = trim($codigo);
                if (!empty($codigo) && $codigo != '00' && $codigo != 'BD') {
                    $descricao = Arr::get($this->rejeicoes, $codigo, 'Rejeição Desconhecida');
                    $d->addRejeicao($codigo, $descricao);
                }
            }

            // Se tem rejeições E não foi marcado como pago, marca como erro
            // Não sobrescreve se já foi definido como PAGO no Segmento A
            if (count($d->getRejeicoes()) > 0 && $d->getOcorrenciaTipo() != $d::OCORRENCIA_PAGO) {
                $this->totais['erros']++;
                $error = implode('; ', $d->getRejeicoes());
                $d->setError($error);
                $d->setOcorrenciaTipo($d::OCORRENCIA_ERRO);
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
