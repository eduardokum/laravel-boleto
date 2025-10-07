<?php

namespace Eduardokum\LaravelBoleto\Cnab\Pagamento\Retorno\Cnab240\Banco;

use Illuminate\Support\Arr;
use Eduardokum\LaravelBoleto\Util;
use Eduardokum\LaravelBoleto\Exception\ValidationException;
use Eduardokum\LaravelBoleto\Cnab\Pagamento\Retorno\Cnab240\AbstractRetorno;

class Bancoob extends AbstractRetorno
{
    /**
     * Código do banco
     * @var string
     */
    protected $codigoBanco = '756';

    /**
     * Array com as ocorrências do banco
     * @var array
     */
    private $ocorrencias = [
        '00' => 'Crédito ou Débito Efetivado',
        'AJ' => 'Tipo de Movimento Inválido',
        'BD' => 'Inclusão Efetuada com Sucesso',
        'PD' => 'Transação Pendente de Assinatura',
        'BF' => 'Transação Rejeitada',
    ];

    /**
     * Array com as possíveis rejeições do banco
     * @var array
     */
    private $rejeicoes = [
        '01' => 'Identificação inválida',
        '02' => 'Variação da Carteira inválida',
        '04' => 'Código de Ocorrência não numérico',
        '05' => 'Código de Ocorrência não previsto',
        '08' => 'Nosso número não numérico',
        '15' => 'Característica da cobrança incompatível',
        '16' => 'Data de Vencimento inválida',
        '17' => 'Data de Vencimento anterior a Data de Emissão',
        '18' => 'Vencimento fora do prazo de operação',
        '20' => 'Valor do Título inválido',
        '21' => 'Espécie do Título inválida',
        '22' => 'Espécie não permitida para a Carteira',
        '24' => 'Data de Emissão inválida',
        '26' => 'Código de Juros de Mora inválido',
        '27' => 'Valor/Taxa de Juros de Mora inválido',
        '28' => 'Código de Desconto inválido',
        '29' => 'Valor do Desconto maior ou igual ao Valor do Título',
        '30' => 'Desconto a conceder não confere',
        '31' => 'Concessão de Desconto já existente',
        '33' => 'Valor do Abatimento inválido',
        '34' => 'Valor do Abatimento maior ou igual ao Valor do Título',
        '36' => 'Concessão de Abatimento já existente',
        '38' => 'Prazo para Protesto inválido',
        '39' => 'Pedido para protesto não permitido para o Título',
        '40' => 'Título com ordem de Protesto emitida',
        '42' => 'Código para Baixa/Devolução inválido',
        '45' => 'Nome do Pagador não informado',
        '46' => 'Tipo/Número de Inscrição do Pagador inválidos',
        '47' => 'Endereço do Pagador não informado',
        '48' => 'CEP inválido',
        '50' => 'CEP irregular - Banco Correspondente',
        '53' => 'Tipo de Inscrição do Sacador/Avalista inválidos',
        '60' => 'Movimento para Título não cadastrado',
        '63' => 'Entrada para Título já cadastrado',
        '77' => 'Transferência para Desconto não permitido para a Carteira',
        '85' => 'Título com Pagamento Vinculado',
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
            ->setConvenio($this->rem(33, 52, $header))
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
            ->setConvenio($this->rem(33, 52, $headerLote))
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
            $ocorrencia = trim($this->rem(231, 240, $detalhe));
            $seuNumero = trim($this->rem(74, 93, $detalhe));

            // Separa número do documento e número de controle (formato: DOCUMENTO-CONTROLE)
            $partes = explode('-', $seuNumero);
            $numeroDocumento = $partes[0] ?? $seuNumero;
            $numeroControle = $partes[1] ?? '';

            $d->setOcorrencia($ocorrencia)
                ->setOcorrenciaDescricao(Arr::get($this->ocorrencias, $ocorrencia, 'Desconhecida'))
                ->setCodigoBancoFavorecido($this->rem(21, 23, $detalhe))
                ->setAgenciaFavorecido($this->rem(24, 28, $detalhe))
                ->setContaFavorecido($this->rem(30, 41, $detalhe))
                ->setSeuNumero($seuNumero)
                ->setNumeroDocumento($numeroDocumento)
                ->setNumeroControle($numeroControle)
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
            } elseif ($formaLancamento == '01') {
                $tipoPagamento = 'DOC';
            } elseif ($formaLancamento == '41') {
                $tipoPagamento = 'TED - Mesma Titularidade';
            } elseif ($formaLancamento == '43') {
                $tipoPagamento = 'TED - Outra Titularidade';
            }
            $d->setTipoPagamento($tipoPagamento);

            // Processa ocorrências
            if (in_array($ocorrencia, ['00', 'BD', 'BE', 'BH', 'BI'])) {
                // Pagamento efetivado com sucesso
                $this->totais['pagos']++;
                $this->totais['valor_total'] += $d->getValor();
                $d->setOcorrenciaTipo($d::OCORRENCIA_PAGO);
            } elseif (substr($ocorrencia, 0, 1) == 'A' || in_array($ocorrencia, ['HA', 'HB', 'HC', 'HD', 'HE', 'HF', 'HG', 'HH', 'HI', 'HJ', 'HK', 'HL', 'ZC', 'CA', 'CB', 'CC', 'CD', 'CE', 'CF', 'BJ', 'BK', 'BF'])) {
                // Pagamento rejeitado ou com erro
                $this->totais['rejeitados']++;
                $d->setOcorrenciaTipo($d::OCORRENCIA_REJEITADO);
            } elseif ($ocorrencia == '02') {
                // Pagamento cancelado
                $this->totais['cancelados']++;
                $d->setOcorrenciaTipo($d::OCORRENCIA_CANCELADO);
            } elseif (in_array($ocorrencia, ['BL', 'BG'])) {
                // Pagamento agendado/pendente
                $this->totais['pendentes']++;
                $d->setOcorrenciaTipo($d::OCORRENCIA_PENDENTE);
            } else {
                $d->setOcorrenciaTipo($d::OCORRENCIA_OUTROS);
            }
        }

        if ($segmentType == 'B') {
            // Segmento B - Dados complementares do favorecido
            $tipoInscricao = $this->rem(18, 18, $detalhe);
            $documento = $this->rem(19, 32, $detalhe);

            $d->setFavorecido([
                'tipo_documento' => $tipoInscricao == '1' ? 'CPF' : 'CNPJ',
                'documento' => $documento,
                'nome' => trim($this->rem(44, 73, $detalhe)),
                'endereco' => trim($this->rem(33, 62, $detalhe)),
                'bairro' => trim($this->rem(83, 97, $detalhe)),
                'cidade' => trim($this->rem(98, 117, $detalhe)),
                'uf' => trim($this->rem(126, 127, $detalhe)),
                'cep' => $this->rem(118, 122, $detalhe) . $this->rem(123, 125, $detalhe),
            ]);

            // Verifica códigos de ocorrência/rejeição no Segmento B
            $codigosOcorrencia = trim($this->rem(231, 240, $detalhe));
            if (!empty($codigosOcorrencia) && !in_array($codigosOcorrencia, ['00', 'BD', 'BE', 'BH', 'BI', ''])) {
                // Processa múltiplos códigos (cada código tem 2 caracteres)
                $codigos = str_split(str_pad($codigosOcorrencia, 10, ' ', STR_PAD_RIGHT), 2);
                foreach ($codigos as $codigo) {
                    $codigo = trim($codigo);
                    if (!empty($codigo) && !in_array($codigo, ['00', 'BD', 'BE', 'BH', 'BI'])) {
                        // Busca primeiro nas rejeições, depois nas ocorrências
                        $descricao = Arr::get($this->rejeicoes, $codigo, Arr::get($this->ocorrencias, $codigo, 'Código Desconhecido: ' . $codigo));
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
