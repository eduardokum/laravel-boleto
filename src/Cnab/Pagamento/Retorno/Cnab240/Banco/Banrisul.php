<?php

namespace Eduardokum\LaravelBoleto\Cnab\Pagamento\Retorno\Cnab240\Banco;

use Illuminate\Support\Arr;
use Eduardokum\LaravelBoleto\Util;
use Eduardokum\LaravelBoleto\Exception\ValidationException;
use Eduardokum\LaravelBoleto\Cnab\Pagamento\Retorno\Cnab240\AbstractRetorno;

/**
 * Class Banrisul
 * Retorno de Pagamento CNAB 240 - Layout BRR Contas a Pagar
 *
 * @package Eduardokum\LaravelBoleto\Cnab\Pagamento\Retorno\Cnab240\Banco
 */
class Banrisul extends AbstractRetorno
{
    /**
     * Codigo do banco
     *
     * @var string
     */
    protected $codigoBanco = '041';

    /**
     * Tipos de Servico
     *
     * @var array
     */
    const TIPO_SERVICO = [
        '12' => 'TED',
        '13' => 'TED/STR006',
        '20' => 'PAGAMENTO FORNECEDOR',
        '22' => 'TRIBUTOS E IMPOSTOS SEM CODIGO DE BARRAS',
        '23' => 'INTEROPERABILIDADE CONTAS DE PAGAMENTO',
        '30' => 'PAGAMENTO SALARIOS',
        '90' => 'PAGAMENTO BENEFICIOS INSS',
        '91' => 'PAGAMENTO GA',
        '92' => 'PAGAMENTO GNRE',
        '93' => 'PAGAMENTO DARF',
        '94' => 'PAGAMENTO ARRECADACAO',
        '95' => 'TELECOMUNICACOES E NET',
        '96' => 'PAGAMENTO GPS PJ',
        '97' => 'SIMPLES NACIONAL DAS',
        '98' => 'PAGAMENTOS DIVERSOS',
    ];

    /**
     * Formas de Lancamento
     *
     * @var array
     */
    const FORMA_LANCAMENTO = [
        '01' => 'CREDITO EM C/C',
        '03' => 'DOC/TED',
        '10' => 'OP A DISPOSICAO',
        '16' => 'DARF NORMAL SEM CODIGO DE BARRAS',
        '18' => 'DARF SIMPLES SEM CODIGO DE BARRAS',
        '30' => 'LIQUIDACAO TITULOS BANRISUL',
        '31' => 'LIQUIDACAO TITULOS OUTROS BANCOS',
        '32' => 'PAGAMENTO INSS GPS PJ',
        '33' => 'PAGAMENTO ARRECADACOES DIVERSAS',
    ];

    /**
     * Codigos de ocorrencias de retorno (item 5.3 do layout)
     *
     * @var array
     */
    private $ocorrencias = [
        '00' => 'Credito efetuado',
        '01' => 'Insuficiencia de fundos',
        '02' => 'Credito cancelado pelo pagador/credor',
        '03' => 'Debito autorizado pela agencia - efetuado',
        'HA' => 'Lote nao aceito',
        'HB' => 'Inscricao da empresa invalida para o contrato',
        'HC' => 'Convenio com a empresa inexistente/invalido para o contrato',
        'HD' => 'Agencia/conta corrente da empresa inexistente/invalida para o contrato',
        'HE' => 'Tipo de servico invalido para o contrato',
        'HF' => 'Conta-Corrente da Empresa com saldo insuficiente',
        'H4' => 'Retorno de Credito nao Pago',
        'AA' => 'Controle invalido',
        'AB' => 'Tipo de operacao invalido',
        'AC' => 'Tipo de servico invalido',
        'AD' => 'Forma de lancamento invalida',
        'AE' => 'Tipo/numero de inscricao invalido',
        'AF' => 'Codigo do convenio invalido',
        'AG' => 'Agencia/conta corrente/Dv invalido',
        'AH' => 'Numero sequencial do registro do lote invalido',
        'AI' => 'Codigo do Segmento de Detalhe invalido',
        'AJ' => 'Tipo de movimento invalido',
        'AK' => 'Codigo da camara de compensacao do favorecido invalido',
        'AL' => 'Codigo do Banco Favorecido, Instituicao de Pagamento ou Depositario Invalido',
        'AM' => 'Agencia mantenedora da conta corrente do favorecido invalida',
        'AN' => 'Conta Corrente/DV/Conta de Pagamento do Favorecido Invalido',
        'AO' => 'Nome do favorecido nao informado',
        'AP' => 'Data do lancamento invalida',
        'AQ' => 'Tipo/quantidade de moeda invalido',
        'AR' => 'Valor do lancamento invalido',
        'AS' => 'Aviso ao favorecido - Identificacao invalida',
        'AT' => 'Tipo/numero de inscricao do favorecido invalido',
        'AU' => 'Logradouro do favorecido nao informado',
        'AV' => 'Numero do local do favorecido nao informado',
        'AW' => 'Cidade do favorecido nao informado',
        'AX' => 'Cep/complemento do favorecido invalido',
        'AY' => 'Sigla do estado do favorecido invalida',
        'AZ' => 'Codigo/nome do banco depositario invalido',
        'BA' => 'Codigo/nome da agencia depositaria nao informado',
        'BB' => 'Seu numero invalido',
        'BC' => 'Nosso numero invalido',
        'BD' => 'Confirmacao de pagamento agendado',
        'BE' => 'Codigo do pagamento invalido',
        'BF' => 'Periodo de competencia invalido',
        'BG' => 'Mes de competencia invalido',
        'BH' => 'Ano de competencia invalido',
        'BI' => 'Competencia 13 nao pode ser antecipada',
        'BJ' => 'Identificador de pagamento invalido',
        'BK' => 'Valor da multa invalido',
        'BL' => 'Valor minimo de GPS - R$10,00',
        'BM' => 'Codigo de Operacao para o sistema BLV invalido',
        'BN' => 'STR006 ou TED fora do horario',
        'BO' => 'Pagamento em agencia do mesmo estado do favorecido',
        'BP' => 'Erro na validacao do codigo de barras',
        'BQ' => 'Inconsistencia do codigo de barras da GPS',
        'CC' => 'Digito verificador geral invalido',
        'CF' => 'Valor do Documento Invalido',
        'CI' => 'Valor de Mora Invalido',
        'CJ' => 'Valor da Multa Invalido',
        'DD' => 'Duplicidade de DOC',
        'DT' => 'Duplicidade de Titulo',
        'TA' => 'Lote nao aceito - totais de lote com diferenca',
        'XA' => 'TED Agendada cancelada pelo Piloto',
        'XC' => 'TED cancelada pelo Piloto',
        'XD' => 'Devolucao do SPB',
        'XE' => 'Devolucao do SPB por erro',
        'XP' => 'Devolucao do SPB por situacao especial',
        'XR' => 'Movimento entre contas invalido',
        '57' => 'Divergencia na indicacao da agencia, conta corrente, nome ou CNPJ/CPF do favorecido',
        'ZA' => 'Agencia / Conta do Favorecido substituido',
    ];

    /**
     * Processa o header do arquivo
     *
     * @param array|string $header
     * @return bool
     */
    protected function processarHeader($header)
    {
        $this->getHeader()
            ->setCodBanco($this->rem(1, 3, $header))
            ->setLoteServico($this->rem(4, 7, $header))
            ->setTipoRegistro($this->rem(8, 8, $header))
            ->setTipoInscricao($this->rem(18, 18, $header))
            ->setNumeroInscricao($this->rem(19, 32, $header))
            ->setConvenio($this->rem(33, 37, $header))
            ->setAgencia($this->rem(53, 57, $header))
            ->setConta($this->rem(62, 71, $header))
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
     * Processa o header do lote
     *
     * @param array|string $headerLote
     * @return bool
     */
    protected function processarHeaderLote($headerLote)
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
            ->setConvenio($this->rem(33, 37, $headerLote))
            ->setAgencia($this->rem(53, 57, $headerLote))
            ->setConta($this->rem(62, 71, $headerLote))
            ->setNomeEmpresa($this->rem(73, 102, $headerLote));

        return true;
    }

    /**
     * Processa os registros de detalhe
     *
     * @param array|string $detalhe
     * @return bool
     */
    protected function processarDetalhe($detalhe)
    {
        $d = $this->detalheAtual();
        $segmento = $this->getSegmentType($detalhe);

        if ($segmento == 'A') {
            // Segmento A - Dados principais do pagamento
            $ocorrencias = trim($this->rem(231, 240, $detalhe));

            $d->setOcorrencia($ocorrencias)
                ->setOcorrenciaDescricao(Arr::get($this->ocorrencias, $ocorrencias, 'Desconhecida'))
                ->setCodigoBancoFavorecido($this->rem(21, 23, $detalhe))
                ->setAgenciaFavorecido($this->rem(24, 28, $detalhe))
                ->setContaFavorecido($this->rem(30, 42, $detalhe))
                ->setSeuNumero($this->rem(74, 88, $detalhe))
                ->setNumeroDocumento(trim($this->rem(74, 88, $detalhe)))
                ->setDataPagamento($this->rem(94, 101, $detalhe))
                ->setValor(Util::nFloat($this->rem(120, 134, $detalhe) / 100, 2, false))
                ->setNossoNumero($this->rem(135, 154, $detalhe))
                ->setDataEfetivacao($this->rem(155, 162, $detalhe))
                ->setValorRealEfetivado(Util::nFloat($this->rem(163, 177, $detalhe) / 100, 2, false));

            // Determina o tipo de pagamento pela forma de lancamento do lote
            $formaLancamento = $this->getHeaderLote()->getFormaLancamento();
            $tipoPagamento = 'TED';
            if ($formaLancamento == '01') {
                $tipoPagamento = 'CC';
            } elseif ($formaLancamento == '10') {
                $tipoPagamento = 'OP';
            }
            $d->setTipoPagamento($tipoPagamento);

            // Processa ocorrencias
            if (in_array($ocorrencias, ['00', '03', 'BD'])) {
                $d->setOcorrenciaTipo($d::OCORRENCIA_PAGO);
            } elseif (in_array($ocorrencias, ['02'])) {
                $d->setOcorrenciaTipo($d::OCORRENCIA_CANCELADO);
            } elseif (in_array($ocorrencias, ['01'])) {
                $d->setOcorrenciaTipo($d::OCORRENCIA_REJEITADO);
            } elseif (
                substr($ocorrencias, 0, 1) == 'A' ||
                substr($ocorrencias, 0, 1) == 'H' ||
                substr($ocorrencias, 0, 1) == 'X' ||
                in_array($ocorrencias, ['DD', 'DT', 'TA', 'BP', 'BQ', 'CC', 'CF', 'CI', 'CJ'])
            ) {
                $d->setOcorrenciaTipo($d::OCORRENCIA_REJEITADO);
            } else {
                $d->setOcorrenciaTipo($d::OCORRENCIA_OUTROS);
            }
        }

        if ($segmento == 'B') {
            // Segmento B - Dados complementares do favorecido
            $d->setFavorecido([
                'documento' => $this->rem(19, 32, $detalhe),
                'endereco' => $this->rem(33, 62, $detalhe),
                'numero' => $this->rem(63, 67, $detalhe),
                'complemento' => $this->rem(68, 82, $detalhe),
                'bairro' => $this->rem(83, 97, $detalhe),
                'cidade' => $this->rem(98, 117, $detalhe),
                'cep' => $this->rem(118, 125, $detalhe),
                'uf' => $this->rem(126, 127, $detalhe),
            ]);
        }

        return true;
    }

    /**
     * Processa o trailer do lote
     *
     * @param array|string $trailerLote
     * @return bool
     */
    protected function processarTrailerLote($trailerLote)
    {
        $this->getTrailerLote()
            ->setLoteServico($this->rem(4, 7, $trailerLote))
            ->setTipoRegistro($this->rem(8, 8, $trailerLote))
            ->setQtdRegistros($this->rem(18, 23, $trailerLote))
            ->setSomatorioValores(Util::nFloat($this->rem(24, 41, $trailerLote) / 100, 2, false))
            ->setOcorrencia($this->rem(231, 240, $trailerLote));

        return true;
    }

    /**
     * Processa o trailer do arquivo
     *
     * @param array|string $trailer
     * @return bool
     */
    protected function processarTrailer($trailer)
    {
        $this->getTrailer()
            ->setNumeroLote($this->rem(4, 7, $trailer))
            ->setTipoRegistro($this->rem(8, 8, $trailer))
            ->setQtdLotes($this->rem(18, 23, $trailer))
            ->setQtdRegistros($this->rem(24, 29, $trailer));

        return true;
    }
}
