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
        '01' => 'Insuficiência de Fundos - Débito Não Efetuado',
        '02' => 'Crédito ou Débito Cancelado pelo Pagador',
        '03' => 'Débito Autorizado pela Agência - Efetuado',
        'AA' => 'Controle Inválido',
        'AB' => 'Tipo de Operação Inválido',
        'AC' => 'Tipo de Serviço Inválido',
        'AD' => 'Forma de Lançamento Inválida',
        'AE' => 'Tipo/Número de Inscrição Inválido',
        'AF' => 'Código de Convênio Inválido',
        'AG' => 'Agência/Conta Corrente/DV Inválido',
        'AH' => 'Número Sequencial do Registro no Lote Inválido',
        'AI' => 'Código de Segmento de Detalhe Inválido',
        'AJ' => 'Tipo de Movimento Inválido',
        'AK' => 'Código da Câmara de Compensação do Banco Favorecido/Depositário Inválido',
        'AL' => 'Código do Banco Favorecido, Instituição de Pagamento ou Depositário Inválido',
        'AM' => 'Agência Mantenedora da Conta Corrente do Favorecido Inválida',
        'AN' => 'Conta Corrente/DV/Conta de Pagamento do Favorecido Inválido',
        'AO' => 'Nome do Favorecido Não Informado',
        'AP' => 'Data de Lançamento Inválido',
        'AQ' => 'Tipo/Quantidade da Moeda Inválido',
        'AR' => 'Valor do Lançamento Inválido',
        'AS' => 'Aviso ao Favorecido - Identificação Inválida',
        'AT' => 'Tipo/Número de Inscrição do Favorecido Inválido',
        'AU' => 'Logradouro do Favorecido Não Informado',
        'AV' => 'Número do Local do Favorecido Não Informado',
        'AW' => 'Cidade do Favorecido Não Informada',
        'AX' => 'CEP/Complemento do Favorecido Inválido',
        'AY' => 'Sigla do Estado do Favorecido Inválida',
        'AZ' => 'Código/Nome do Banco Depositário Inválido',
        'BA' => 'Código/Nome da Agência Depositária Não Informado',
        'BB' => 'Seu Número Inválido',
        'BC' => 'Nosso Número Inválido',
        'BD' => 'Inclusão Efetuada com Sucesso',
        'BE' => 'Alteração Efetuada com Sucesso',
        'BF' => 'Exclusão Efetuada com Sucesso',
        'BG' => 'Agendamento Efetuado com Sucesso',
        'BH' => 'Pagamento Efetuado com Sucesso',
        'BI' => 'Transferência Efetuada com Sucesso',
        'BJ' => 'Valor Fora do Limite Permitido',
        'BK' => 'Tipo de Pagamento Incompatível',
        'BL' => 'Pagamento Agendado',
        'CA' => 'Código de Barras - Código do Banco Inválido',
        'CB' => 'Código de Barras - Código da Moeda Inválido',
        'CC' => 'Código de Barras - Dígito Verificador Geral Inválido',
        'CD' => 'Código de Barras - Valor do Título Inválido',
        'CE' => 'Código de Barras - Campo Livre Inválido',
        'CF' => 'Valor do Documento Inválido',
        'HA' => 'Lote Não Aceito',
        'HB' => 'Inscrição da Empresa Inválida para o Contrato',
        'HC' => 'Convênio com a Empresa Inexistente/Inválido para o Contrato',
        'HD' => 'Agência/Conta Corrente da Empresa Inexistente/Inválido para o Contrato',
        'HE' => 'Tipo de Serviço Inválido para o Contrato',
        'HF' => 'Conta Corrente da Empresa com Saldo Insuficiente',
        'HG' => 'Lote de Serviço Fora de Sequência',
        'HH' => 'Lote de Serviço Inválido',
        'HI' => 'Arquivo não aceito',
        'HJ' => 'Tipo de Registro Inválido',
        'HK' => 'Código Remessa / Retorno Inválido',
        'HL' => 'Versão de Layout Inválida',
        'HM' => 'Mutuário não identificado',
        'HN' => 'Tipo do benefício não permite empréstimo',
        'HO' => 'Benefício cessado/suspenso',
        'HP' => 'Benefício possui representante legal',
        'HQ' => 'Benefício é do tipo PA (Pensão alimentícia)',
        'HR' => 'Quantidade de contratos permitida excedida',
        'HS' => 'Benefício não pertence ao Banco informado',
        'HT' => 'Início do desconto informado já ultrapassado',
        'HU' => 'Número da parcela inválida',
        'HV' => 'Quantidade de parcelas inválida',
        'HW' => 'Margem consignável excedida para o mutuário dentro do prazo do contrato',
        'HX' => 'Empréstimo já cadastrado',
        'HY' => 'Empréstimo inexistente',
        'HZ' => 'Empréstimo já encerrado',
        'IA' => 'Arquivo de Retorno sem Registros',
        'IB' => 'Confirmação de Operação de Crédito',
        'ZA' => 'Registro Aceito',
        'ZB' => 'Registro Aceito com Alteração',
        'ZC' => 'Registro Rejeitado',
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
            $ocorrencias = trim($this->rem(231, 240, $detalhe));

            $d->setOcorrencia($ocorrencias)
                ->setOcorrenciaDescricao(Arr::get($this->ocorrencias, $ocorrencias, 'Desconhecida'))
                ->setCodigoBancoFavorecido($this->rem(21, 23, $detalhe))
                ->setAgenciaFavorecido($this->rem(24, 28, $detalhe))
                ->setContaFavorecido($this->rem(30, 41, $detalhe))
                ->setSeuNumero($this->rem(74, 93, $detalhe))
                ->setDataPagamento($this->rem(94, 101, $detalhe))
                ->setValor(Util::nFloat($this->rem(120, 134, $detalhe) / 100, 2, false))
                ->setNossoNumero($this->rem(135, 154, $detalhe))
                ->setDataEfetivacao($this->rem(155, 162, $detalhe))
                ->setValorRealEfetivado(Util::nFloat($this->rem(163, 177, $detalhe) / 100, 2, false));

            // Define tipo de pagamento
            $d->setTipoPagamento('TED'); // Padrão para Bancoob

            // Processa ocorrências
            if (in_array($ocorrencias, ['00', 'BD', 'BH', 'BI'])) {
                // Pagamento efetivado
                $this->totais['pagos']++;
                $this->totais['valor_total'] += $d->getValor();
                $d->setOcorrenciaTipo($d::OCORRENCIA_PAGO);
            } elseif (substr($ocorrencias, 0, 1) == 'A' || in_array($ocorrencias, ['HA', 'HI', 'ZC'])) {
                // Pagamento rejeitado
                $this->totais['rejeitados']++;
                $d->setOcorrenciaTipo($d::OCORRENCIA_REJEITADO);
            } elseif ($ocorrencias == '02') {
                // Pagamento cancelado
                $this->totais['cancelados']++;
                $d->setOcorrenciaTipo($d::OCORRENCIA_CANCELADO);
            } elseif (in_array($ocorrencias, ['BL', 'BG'])) {
                // Pagamento agendado/pendente
                $this->totais['pendentes']++;
                $d->setOcorrenciaTipo($d::OCORRENCIA_PENDENTE);
            } else {
                $d->setOcorrenciaTipo($d::OCORRENCIA_OUTROS);
            }
        }

        if ($segmentType == 'B') {
            // Segmento B - Dados complementares do favorecido
            $d->setFavorecido([
                'documento' => $this->rem(19, 32, $detalhe),
                'nome' => $this->rem(44, 73, $detalhe),
                'endereco' => $this->rem(33, 62, $detalhe),
                'bairro' => $this->rem(83, 97, $detalhe),
                'cidade' => $this->rem(98, 117, $detalhe),
                'uf' => $this->rem(126, 127, $detalhe),
                'cep' => $this->rem(118, 125, $detalhe),
            ]);

            // Verifica códigos de ocorrência/rejeição
            $codigosOcorrencia = trim($this->rem(231, 240, $detalhe));
            if (!empty($codigosOcorrencia) && $codigosOcorrencia != '00') {
                // Processa múltiplos códigos (se houver)
                $codigos = str_split($codigosOcorrencia, 2);
                foreach ($codigos as $codigo) {
                    $codigo = trim($codigo);
                    if (!empty($codigo) && $codigo != '00') {
                        $descricao = Arr::get($this->rejeicoes, $codigo, Arr::get($this->ocorrencias, $codigo, 'Código Desconhecido'));
                        $d->addRejeicao($codigo, $descricao);
                    }
                }
            }

            // Se tem rejeições, marca como erro
            if (count($d->getRejeicoes()) > 0) {
                $this->totais['erros']++;
                $error = implode('; ', $d->getRejeicoes());
                $d->setError($error);
                if ($d->getOcorrenciaTipo() != $d::OCORRENCIA_REJEITADO) {
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
