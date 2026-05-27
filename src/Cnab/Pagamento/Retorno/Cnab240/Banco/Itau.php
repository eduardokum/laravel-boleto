<?php

namespace Eduardokum\LaravelBoleto\Cnab\Pagamento\Retorno\Cnab240\Banco;

use Illuminate\Support\Arr;
use Eduardokum\LaravelBoleto\Util;
use Eduardokum\LaravelBoleto\Exception\ValidationException;
use Eduardokum\LaravelBoleto\Cnab\Pagamento\Retorno\Cnab240\Detalhe;
use Eduardokum\LaravelBoleto\Cnab\Pagamento\Retorno\Cnab240\AbstractRetorno;

/**
 * Class Itau
 * @package Eduardokum\LaravelBoleto\Cnab\Pagamento\Retorno\Cnab240\Banco
 */
class Itau extends AbstractRetorno
{
    /**
     * Código do banco
     * @var string
     */
    protected $codigoBanco = '341';

    /**
     * Tipos de Pagamento (NOTA 4)
     * @var array
     */
    const TIPO_PAGAMENTO = [
        '10' => 'DIVIDENDOS',
        '15' => 'DEBÊNTURES',
        '20' => 'FORNECEDORES',
        '22' => 'TRIBUTOS',
        '30' => 'SALÁRIOS',
        '40' => 'FUNDOS DE INVESTIMENTOS',
        '50' => 'SINISTROS DE SEGUROS',
        '60' => 'DESPESAS VIAJANTE EM TRÂNSITO',
        '80' => 'REPRESENTANTES AUTORIZADOS',
        '90' => 'BENEFÍCIOS',
        '98' => 'DIVERSOS',
    ];

    /**
     * Formas de Pagamento (NOTA 5)
     * @var array
     */
    const FORMA_PAGAMENTO = [
        '01' => 'CRÉDITO EM CONTA CORRENTE NO ITAÚ',
        '02' => 'CHEQUE PAGAMENTO/ADMINISTRATIVO',
        '03' => 'DOC "C"',
        '05' => 'CRÉDITO EM CONTA POUPANÇA NO ITAÚ',
        '06' => 'CRÉDITO EM CONTA CORRENTE DE MESMA TITULARIDADE',
        '07' => 'DOC "D"',
        '10' => 'ORDEM DE PAGAMENTO À DISPOSIÇÃO',
        '13' => 'PAGAMENTO DE CONCESSIONÁRIAS',
        '16' => 'DARF NORMAL',
        '17' => 'GPS - GUIA DA PREVIDÊNCIA SOCIAL',
        '18' => 'DARF SIMPLES',
        '19' => 'IPTU/ISS/OUTROS TRIBUTOS MUNICIPAIS',
        '22' => 'GARE - SP ICMS',
        '25' => 'IPVA',
        '27' => 'DPVAT',
        '30' => 'PAGAMENTO DE TÍTULOS EM COBRANÇA NO ITAÚ',
        '31' => 'PAGAMENTO DE TÍTULOS EM COBRANÇA EM OUTROS BANCOS',
        '32' => 'NOTA FISCAL - LIQUIDAÇÃO ELETRÔNICA',
        '35' => 'FGTS',
        '41' => 'TED - OUTRO TITULAR',
        '43' => 'TED - MESMO TITULAR',
        '45' => 'PIX TRANSFERÊNCIA',
        '47' => 'PIX QR-CODE',
        '60' => 'CARTÃO SALÁRIO',
        '91' => 'GNRE E TRIBUTOS COM CÓDIGO DE BARRAS',
    ];

    /**
     * Mapa de código de ocorrência → descrição, conforme NOTA 8 do manual
     * SISPAG Itaú. O campo de ocorrências (posições 231-240) é X(10) e pode
     * conter até 5 códigos de 2 caracteres concatenados.
     *
     * @var array
     */
    private $ocorrencias = [
        '00' => 'Pagamento Efetuado',
        'AE' => 'Data de Pagamento Alterada',
        'AG' => 'Número do Lote Inválido',
        'AH' => 'Número Sequencial do Registro no Lote Inválido',
        'AI' => 'Produto Demonstrativo de Pagamento Não Contratado',
        'AJ' => 'Tipo de Movimento Inválido',
        'AL' => 'Código do Banco Favorecido Inválido',
        'AM' => 'Agência do Favorecido Inválida',
        'AN' => 'Conta Corrente do Favorecido Inválida',
        'AO' => 'Nome do Favorecido Inválido',
        'AP' => 'Data de Pagamento / Validade / Hora de Lançamento / Arrecadação / Apuração Inválida',
        'AQ' => 'Quantidade de Registros Maior que 999999',
        'AR' => 'Valor Arrecadado / Lançamento Inválido',
        'BC' => 'Nosso Número Inválido',
        'BD' => 'Pagamento Agendado',
        'BE' => 'Pagamento Agendado com Forma Alterada para OP',
        'BI' => 'CNPJ/CPF do Favorecido no Segmento J-52 ou B Inválido / Documento Favorecido Inválido PIX',
        'BL' => 'Valor da Parcela Inválido',
        'CD' => 'CNPJ/CPF Informado Divergente do Cadastrado',
        'CE' => 'Pagamento Cancelado',
        'CF' => 'Valor do Documento Inválido / Valor Divergente do QR Code',
        'CG' => 'Valor do Abatimento Inválido',
        'CH' => 'Valor do Desconto Inválido',
        'CI' => 'CNPJ/CPF/Identificador/Inscrição Estadual/CAD/ICMS Inválido',
        'CJ' => 'Valor da Multa Inválido',
        'CK' => 'Tipo de Inscrição Inválida',
        'CL' => 'Valor do INSS Inválido',
        'CM' => 'Valor do COFINS Inválido',
        'CN' => 'Conta Não Cadastrada',
        'CO' => 'Valor de Outras Entidades Inválido',
        'CP' => 'Confirmação de OP Cumprida',
        'CQ' => 'Soma das Faturas Difere do Pagamento',
        'CR' => 'Valor do CSLL Inválido',
        'CS' => 'Data de Vencimento da Fatura Inválida',
        'DA' => 'Número de Dependentes Salário Família Inválido',
        'DB' => 'Número de Horas Semanais Inválido',
        'DC' => 'Salário de Contribuição INSS Inválido',
        'DD' => 'Salário de Contribuição FGTS Inválido',
        'DE' => 'Valor Total dos Proventos Inválido',
        'DF' => 'Valor Total dos Descontos Inválido',
        'DG' => 'Valor Líquido Não Numérico',
        'DH' => 'Valor Líquido Informado Difere do Calculado',
        'DI' => 'Valor do Salário-Base Inválido',
        'DJ' => 'Base de Cálculo IRRF Inválida',
        'DK' => 'Base de Cálculo FGTS Inválida',
        'DL' => 'Forma de Pagamento Incompatível com Holerite',
        'DM' => 'E-mail do Favorecido Inválido',
        'DV' => 'DOC/TED Devolvido pelo Banco Favorecido',
        'D0' => 'Finalidade do Holerite Inválida',
        'D1' => 'Mês de Competência do Holerite Inválido',
        'D2' => 'Dia da Competência do Holerite Inválido',
        'D3' => 'Centro de Custo Inválido',
        'D4' => 'Campo Numérico da Funcional Inválido',
        'D5' => 'Data Início de Férias Não Numérica',
        'D6' => 'Data Início de Férias Inconsistente',
        'D7' => 'Data Fim de Férias Não Numérica',
        'D8' => 'Data Fim de Férias Inconsistente',
        'D9' => 'Número de Dependentes IR Inválido',
        'EM' => 'Confirmação de OP Emitida',
        'EX' => 'Devolução de OP Não Sacada pelo Favorecido',
        'E0' => 'Tipo de Movimento Holerite Inválido',
        'E1' => 'Valor 01 do Holerite/Informe Inválido',
        'E2' => 'Valor 02 do Holerite/Informe Inválido',
        'E3' => 'Valor 03 do Holerite/Informe Inválido',
        'E4' => 'Valor 04 do Holerite/Informe Inválido',
        'FC' => 'Pagamento Efetuado Através de Financiamento Compror',
        'FD' => 'Pagamento Efetuado Através de Financiamento Descompror',
        'HA' => 'Erro no Lote',
        'HM' => 'Erro no Registro Header de Arquivo',
        'IB' => 'Valor do Documento Inválido',
        'IC' => 'Valor do Abatimento Inválido',
        'ID' => 'Valor do Desconto Inválido',
        'IE' => 'Valor da Mora Inválido',
        'IF' => 'Valor da Multa Inválido',
        'IG' => 'Valor da Dedução Inválido',
        'IH' => 'Valor do Acréscimo Inválido',
        'II' => 'Data de Vencimento Inválida / QR Code Expirado',
        'IJ' => 'Competência/Período Referência/Parcela Inválida',
        'IK' => 'Tributo Não Liquidável via SISPAG ou Não Conveniado com Itaú',
        'IL' => 'Código de Pagamento/Empresa/Receita Inválido',
        'IM' => 'Tipo X Forma Não Compatível',
        'IN' => 'Banco/Agência Não Cadastrados',
        'IO' => 'DAC/Valor/Competência/Identificador do Lacre / QR Code Inválido',
        'IP' => 'DAC do Código de Barras Inválido / Erro na Validação do QR Code',
        'IQ' => 'Dívida Ativa ou Número de Etiqueta Inválido',
        'IR' => 'Pagamento Alterado',
        'IS' => 'Concessionária Não Conveniada com Itaú',
        'IT' => 'Valor do Tributo Inválido',
        'IU' => 'Valor da Receita Bruta Acumulada Inválido',
        'IV' => 'Número do Documento Origem/Referência Inválido',
        'IX' => 'Código do Produto Inválido',
        'LA' => 'Data de Pagamento de um Lote Alterada',
        'LC' => 'Lote de Pagamentos Cancelado',
        'NA' => 'Pagamento Cancelado por Falta de Autorização',
        'NB' => 'Identificação do Tributo Inválida',
        'NC' => 'Exercício (Ano Base) Inválido',
        'ND' => 'Código RENAVAM Não Encontrado/Inválido',
        'NE' => 'UF Inválida',
        'NF' => 'Código do Município Inválido',
        'NG' => 'Placa Inválida',
        'NH' => 'Opção/Parcela de Pagamento Inválida',
        'NI' => 'Tributo Já Foi Pago ou Está Vencido',
        'NR' => 'Operação Não Realizada',
        'PD' => 'Aquisição Confirmada (Risco Sacado)',
        'RJ' => 'Registro Rejeitado',
        'RS' => 'Pagamento Disponível para Antecipação no Risco Sacado',
        'SS' => 'Pagamento Cancelado por Insuficiência de Saldo / Limite Diário Excedido',
        'TA' => 'Lote Não Aceito - Totais do Lote com Diferença',
        'TI' => 'Titularidade Inválida',
        'X1' => 'Forma Incompatível com Layout 010',
        'X2' => 'Número da Nota Fiscal Inválido',
        'X3' => 'Identificador de NF/CNPJ Inválido',
        'X4' => 'Forma 32 Inválida',
    ];

    /**
     * Mapeia código de ocorrência (NOTA 8) para status conceitual do Detalhe.
     * Códigos ausentes deste mapa são interpretados como rejeição.
     *
     * @var array
     */
    private $statusOcorrencias = [
        // Pagos
        '00' => Detalhe::OCORRENCIA_PAGO,
        'CP' => Detalhe::OCORRENCIA_PAGO, // Confirmação de OP cumprida
        'EM' => Detalhe::OCORRENCIA_PAGO, // Confirmação de OP emitida
        'FC' => Detalhe::OCORRENCIA_PAGO, // Financiamento Compror
        'FD' => Detalhe::OCORRENCIA_PAGO, // Financiamento Descompror
        'PD' => Detalhe::OCORRENCIA_PAGO, // Aquisição confirmada (risco sacado)

        // Pendentes (agendados / alterados aguardando execução)
        'BD' => Detalhe::OCORRENCIA_PENDENTE, // Pagamento agendado
        'BE' => Detalhe::OCORRENCIA_PENDENTE, // Agendado com forma alterada para OP
        'AE' => Detalhe::OCORRENCIA_PENDENTE, // Data de pagamento alterada
        'LA' => Detalhe::OCORRENCIA_PENDENTE, // Data de pag. de um lote alterada
        'IR' => Detalhe::OCORRENCIA_PENDENTE, // Pagamento alterado
        'RS' => Detalhe::OCORRENCIA_PENDENTE, // Antecipação risco sacado

        // Cancelados / devolvidos
        'CE' => Detalhe::OCORRENCIA_CANCELADO, // Pagamento cancelado
        'LC' => Detalhe::OCORRENCIA_CANCELADO, // Lote cancelado
        'NA' => Detalhe::OCORRENCIA_CANCELADO, // Cancelado por falta de autorização
        'SS' => Detalhe::OCORRENCIA_CANCELADO, // Cancelado por insuficiência de saldo
        'EX' => Detalhe::OCORRENCIA_CANCELADO, // Devolução de OP não sacada
        'DV' => Detalhe::OCORRENCIA_CANCELADO, // DOC/TED devolvido pelo banco favorecido
    ];

    /**
     * Snapshot de $this->increment ao entrar em cada lote, para calcular a
     * quantidade real de pagamentos (A/J/N/O) no trailer do lote.
     *
     * @var int
     */
    private $incrementoInicioLote = 0;

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
            ->setConta($this->rem(59, 70, $header))
            ->setContaDv($this->rem(72, 72, $header))
            ->setNomeEmpresa($this->rem(73, 102, $header))
            ->setNomeBanco($this->rem(103, 132, $header))
            ->setCodigoRemessaRetorno($this->rem(143, 143, $header))
            ->setData($this->rem(144, 151, $header))
            ->setHora($this->rem(152, 157, $header))
            ->setNumeroSequencialArquivo($this->rem(158, 166, $header))
            ->setVersaoLayoutArquivo($this->rem(15, 17, $header));

        return true;
    }

    /**
     * @param array $headerLote
     * @return bool
     * @throws ValidationException
     */
    protected function processarHeaderLote(array $headerLote)
    {
        // Marca o início do lote para permitir contagem correta de
        // pagamentos no trailer (independente de quantos segmentos por
        // pagamento o layout contenha: A/B, J/J-52, etc.).
        $this->incrementoInicioLote = (int) $this->increment;

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
            ->setConta($this->rem(59, 70, $headerLote))
            ->setContaDv($this->rem(72, 72, $headerLote))
            ->setNomeEmpresa($this->rem(73, 102, $headerLote));

        return true;
    }

    /**
     * Mapeia a forma de lançamento (NOTA 5) para um "tipo de pagamento"
     * conceitual usado no Detalhe (TED, PIX, DOC, BOLETO, ...).
     *
     * @param string $formaLancamento
     * @return string
     */
    protected function resolveTipoPagamento($formaLancamento)
    {
        switch ($formaLancamento) {
            case '01':
            case '05':
            case '06':
                return 'TED'; // Crédito em conta / poupança / mesma titularidade
            case '02':
                return 'CHEQUE';
            case '03':
            case '07':
                return 'DOC';
            case '41':
            case '43':
                return 'TED';
            case '45':
                return 'PIX';
            case '47':
                return 'PIX_QRCODE';
            case '30':
            case '31':
                return 'BOLETO';
            case '32':
                return 'NF_LIQUIDACAO';
            default:
                return 'OUTROS';
        }
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

        // Segmentos J e J-52 (liquidação de boletos)
        if ($segmentType == 'J') {
            return $this->rem(18, 19, $detalhe) === '52'
                ? $this->processarSegmentoJ52($detalhe, $d)
                : $this->processarSegmentoJ($detalhe, $d);
        }

        // Segmento O - pagamento de concessionárias/tributos com código de
        // barras (manual SISPAG pág 34).
        if ($segmentType == 'O') {
            return $this->processarSegmentoO($detalhe, $d);
        }

        // Segmento Z (autenticação eletrônica do pagamento - opcional)
        if ($segmentType == 'Z') {
            $d->setAutenticacao(trim($this->rem(15, 78, $detalhe)));
            if (empty($d->getSeuNumero())) {
                $d->setSeuNumero(trim($this->rem(79, 98, $detalhe)));
            }
            if (empty($d->getNossoNumero())) {
                $d->setNossoNumero(trim($this->rem(104, 118, $detalhe)));
            }

            return true;
        }

        if ($segmentType == 'A') {
            // Segmento A - Dados principais do pagamento
            $codigos = $this->parseCodigosOcorrencia($this->rem(231, 240, $detalhe));
            $primary = $codigos[0] ?? '';

            $bancoFavorecido = $this->rem(21, 23, $detalhe);
            $identTransferencia = trim($this->rem(113, 114, $detalhe));
            $conta = $this->parseContaFavorecido($detalhe, $bancoFavorecido, $identTransferencia);

            $nomeFavorecido = trim($this->rem(44, 73, $detalhe));
            $documentoFavorecido = ltrim(trim($this->rem(204, 217, $detalhe)), '0');

            $d->setOcorrencia($primary)
                ->setOcorrenciaDescricao(Arr::get($this->ocorrencias, $primary, 'Desconhecida'))
                ->setTipoMovimento(trim($this->rem(15, 17, $detalhe)))
                ->setCamara(trim($this->rem(18, 20, $detalhe)))
                ->setCodigoBancoFavorecido($bancoFavorecido)
                ->setAgenciaFavorecido($conta['agencia'])
                ->setContaFavorecido($conta['conta'])
                ->setContaFavorecidoDv($conta['dv'])
                ->setNomeFavorecido($nomeFavorecido)
                ->setDocumentoFavorecido($documentoFavorecido)
                ->setSeuNumero($this->rem(74, 93, $detalhe))
                ->setNumeroDocumento(explode('-', trim($this->rem(74, 93, $detalhe)))[0] ?? '')
                ->setNumeroControle(explode('-', trim($this->rem(74, 93, $detalhe)))[1] ?? '')
                ->setDataPagamento($this->rem(94, 101, $detalhe))
                ->setCodigoIspb(trim($this->rem(105, 112, $detalhe)))
                ->setIdentificacaoTransferencia($identTransferencia)
                ->setValor(Util::nFloat($this->rem(120, 134, $detalhe) / 100, 2, false))
                ->setNossoNumero(trim($this->rem(135, 149, $detalhe)))
                ->setDataEfetivacao($this->rem(155, 162, $detalhe))
                ->setValorRealEfetivado(Util::nFloat($this->rem(163, 177, $detalhe) / 100, 2, false))
                ->setNumeroDocumentoBancario(ltrim(trim($this->rem(198, 203, $detalhe)), '0'))
                ->setFinalidadeTed(trim($this->rem(220, 224, $detalhe)));

            // Determina o tipo de pagamento pela forma de lançamento do lote
            $d->setTipoPagamento($this->resolveTipoPagamento($this->getHeaderLote()->getFormaLancamento()));

            // Processa ocorrências (código primário + secundários como metadados)
            $this->classificarOcorrencia($d, $codigos);
        }

        if ($segmentType == 'B') {
            // Segmento B - Dados complementares.
            // Detecção PIX via forma de lançamento do lote (45 = Transferência,
            // 47 = QR-Code). O campo "tipo chave" no próprio B pode vir em
            // branco em retornos de rejeição, portanto não serve como fonte.
            $formaLancamento = $this->getHeaderLote()->getFormaLancamento();
            $isPix = in_array($formaLancamento, ['45', '47'], true);

            // NOTA 15: se B traz CPF/CNPJ válido, ele vence; caso contrário,
            // mantém o que foi lido em A.
            $documentoB = ltrim(trim($this->rem(19, 32, $detalhe)), '0');
            $documento = $documentoB !== '' ? $documentoB : (string) $d->getDocumentoFavorecido();

            $pessoa = [
                'nome'      => $d->getNomeFavorecido(),
                'documento' => $documento,
            ];

            if ($isPix) {
                $tipoChave   = trim($this->rem(15, 16, $detalhe));
                $chavePix    = trim($this->rem(128, 227, $detalhe));
                $infoUsuario = trim($this->rem(63, 127, $detalhe));

                $d->addInformacaoAdicional('tipo_chave_pix', $tipoChave);
                $d->addInformacaoAdicional('chave_pix', $chavePix);
                if ($infoUsuario !== '') {
                    $d->addInformacaoAdicional('info_entre_usuarios', $infoUsuario);
                }
            } else {
                $pessoa = array_merge($pessoa, [
                    'endereco' => trim($this->rem(33, 62, $detalhe)),
                    'bairro'   => trim($this->rem(83, 97, $detalhe)),
                    'cidade'   => trim($this->rem(98, 117, $detalhe)),
                    'cep'      => trim($this->rem(118, 125, $detalhe)),
                    'uf'       => trim($this->rem(126, 127, $detalhe)),
                    'email'    => trim($this->rem(128, 227, $detalhe)),
                ]);

                // Campos não aceitos pela Pessoa viram informações adicionais.
                $numero      = trim($this->rem(63, 67, $detalhe));
                $complemento = trim($this->rem(68, 82, $detalhe));
                if (ltrim($numero, '0') !== '') {
                    $d->addInformacaoAdicional('numero_endereco', ltrim($numero, '0'));
                }
                if ($complemento !== '') {
                    $d->addInformacaoAdicional('complemento_endereco', $complemento);
                }
            }

            $d->setFavorecido($pessoa);

            // Códigos de ocorrência no Segmento B: adiciona como rejeições
            // apenas os não mapeados (i.e., códigos de erro da NOTA 8).
            foreach ($this->parseCodigosOcorrencia($this->rem(231, 240, $detalhe)) as $codigo) {
                if (! isset($this->statusOcorrencias[$codigo])) {
                    $descricao = Arr::get($this->ocorrencias, $codigo, 'Código Desconhecido');
                    $d->addRejeicao($codigo, $descricao);
                }
            }

            // Se surgiram rejeições adicionais no B e o status principal
            // não é terminal (pago / cancelado / pendente), promove para ERRO.
            $statusTerminais = [
                $d::OCORRENCIA_PAGO,
                $d::OCORRENCIA_CANCELADO,
                $d::OCORRENCIA_PENDENTE,
            ];
            if (count($d->getRejeicoes()) > 0 && ! in_array($d->getOcorrenciaTipo(), $statusTerminais)) {
                // Evita double-count: se já estava em rejeitados, recategoriza como erro.
                if ($d->getOcorrenciaTipo() === $d::OCORRENCIA_REJEITADO) {
                    $this->totais['rejeitados']--;
                }
                $this->totais['erros']++;
                $d->setError(implode('; ', $d->getRejeicoes()));
            }
        }

        return true;
    }

    /**
     * Processa o segmento J - dados principais da liquidação de boleto.
     *
     * @param array    $detalhe
     * @param \Eduardokum\LaravelBoleto\Cnab\Pagamento\Retorno\Cnab240\Detalhe $d
     * @return bool
     * @throws ValidationException
     */
    protected function processarSegmentoJ(array $detalhe, $d)
    {
        $codigos = $this->parseCodigosOcorrencia($this->rem(231, 240, $detalhe));
        $primary = $codigos[0] ?? '';

        // Reconstrói o código de barras (44 dígitos) a partir das posições 018-061.
        $codigoBarras = $this->rem(18, 20, $detalhe)   // Banco favorecido
            . $this->rem(21, 21, $detalhe)             // Moeda
            . $this->rem(22, 22, $detalhe)             // DV
            . $this->rem(23, 26, $detalhe)             // Fator de vencimento
            . $this->rem(27, 36, $detalhe)             // Valor
            . $this->rem(37, 61, $detalhe);            // Campo livre

        $d->setCodigoBarras($codigoBarras)
            ->setCodigoBancoFavorecido(substr($codigoBarras, 0, 3))
            ->setOcorrencia($primary)
            ->setOcorrenciaDescricao(Arr::get($this->ocorrencias, $primary, 'Desconhecida'))
            ->setTipoMovimento(trim($this->rem(15, 17, $detalhe)))
            ->setTipoPagamento($this->resolveTipoPagamento($this->getHeaderLote()->getFormaLancamento()))
            ->setNomeFavorecido(trim($this->rem(62, 91, $detalhe)))
            ->setSeuNumero(trim($this->rem(183, 202, $detalhe)))
            ->setNossoNumero(trim($this->rem(216, 230, $detalhe)))
            ->setDataVencimento($this->rem(92, 99, $detalhe))
            ->setValorTitulo(Util::nFloat($this->rem(100, 114, $detalhe) / 100, 2, false))
            ->setDesconto(Util::nFloat($this->rem(115, 129, $detalhe) / 100, 2, false))
            ->setAcrescimo(Util::nFloat($this->rem(130, 144, $detalhe) / 100, 2, false))
            ->setDataPagamento($this->rem(145, 152, $detalhe))
            ->setValor(Util::nFloat($this->rem(153, 167, $detalhe) / 100, 2, false))
            ->setValorRealEfetivado(Util::nFloat($this->rem(153, 167, $detalhe) / 100, 2, false));

        $this->classificarOcorrencia($d, $codigos);

        return true;
    }

    /**
     * Processa o segmento J-52 - identificação de sacado, cedente e sacador
     * avalista vinculados ao boleto liquidado.
     *
     * @param array    $detalhe
     * @param \Eduardokum\LaravelBoleto\Cnab\Pagamento\Retorno\Cnab240\Detalhe $d
     * @return bool
     * @throws ValidationException
     */
    protected function processarSegmentoJ52(array $detalhe, $d)
    {
        $tipoInscSacado = $this->rem(20, 20, $detalhe);
        $docSacado      = ltrim($this->rem(21, 35, $detalhe), '0');
        $nomeSacado     = trim($this->rem(36, 75, $detalhe));

        if ($docSacado !== '' || $nomeSacado !== '') {
            $d->setSacado([
                'nome'          => $nomeSacado,
                'documento'     => $docSacado,
                'tipoDocumento' => $tipoInscSacado === '1' ? 'CPF' : 'CNPJ',
            ]);
        }

        $tipoInscCedente = $this->rem(76, 76, $detalhe);
        $docCedente      = ltrim($this->rem(77, 91, $detalhe), '0');
        $nomeCedente     = trim($this->rem(92, 131, $detalhe));

        if ($docCedente !== '' || $nomeCedente !== '') {
            $d->setCedente([
                'nome'          => $nomeCedente,
                'documento'     => $docCedente,
                'tipoDocumento' => $tipoInscCedente === '1' ? 'CPF' : 'CNPJ',
            ]);

            // O cedente é o favorecido efetivo do pagamento do boleto.
            if (! $d->getFavorecido()) {
                $d->setFavorecido([
                    'nome'          => $nomeCedente,
                    'documento'     => $docCedente,
                    'tipoDocumento' => $tipoInscCedente === '1' ? 'CPF' : 'CNPJ',
                ]);
            }
        }

        $tipoInscSacador = $this->rem(132, 132, $detalhe);
        $docSacador      = ltrim($this->rem(133, 147, $detalhe), '0');
        $nomeSacador     = trim($this->rem(148, 187, $detalhe));

        if ($docSacador !== '' || $nomeSacador !== '') {
            $d->setSacadorAvalista([
                'nome'          => $nomeSacador,
                'documento'     => $docSacador,
                'tipoDocumento' => $tipoInscSacador === '1' ? 'CPF' : 'CNPJ',
            ]);
        }

        return true;
    }

    /**
     * Processa o segmento O - pagamento de contas de concessionárias e
     * tributos com código de barras (manual SISPAG Itaú v085 pág 34).
     *
     * Layout (retorno):
     *   018-065 Código de Barras X(48)        (44 dígitos significativos)
     *   066-095 Nome concessionária/contribuinte X(30)
     *   096-103 Data de vencimento (nominal) DDMMAAAA
     *   104-106 Tipo de moeda (REA)
     *   107-121 Quantidade de moeda 9(07)V9(08)
     *   122-136 Valor previsto do pagamento 9(13)V9(02)
     *   137-144 Data do pagamento DDMMAAAA
     *   145-159 Valor efetivado do pagamento 9(13)V9(02)  (somente retorno)
     *   163-171 Número da Nota Fiscal 9(09)               (GNRE-SP)
     *   175-194 Seu Número X(20)
     *   216-230 Nosso Número X(15)                        (somente retorno)
     *   231-240 Ocorrências X(10)                         (somente retorno)
     *
     * @param array    $detalhe
     * @param \Eduardokum\LaravelBoleto\Cnab\Pagamento\Retorno\Cnab240\Detalhe $d
     * @return bool
     * @throws ValidationException
     */
    protected function processarSegmentoO(array $detalhe, $d)
    {
        $codigos = $this->parseCodigosOcorrencia($this->rem(231, 240, $detalhe));
        $primary = $codigos[0] ?? '';

        // Barcode tem 44 dígitos no campo X(48); o restante são brancos à direita.
        $codigoBarras = trim($this->rem(18, 65, $detalhe));

        $nomeFavorecido = trim($this->rem(66, 95, $detalhe));
        $notaFiscal     = ltrim(trim($this->rem(163, 171, $detalhe)), '0');
        $seuNumero      = trim($this->rem(175, 194, $detalhe));
        $nossoNumero    = trim($this->rem(216, 230, $detalhe));

        // Quantidade de Moeda 9(07)V9(08) — usada quando moeda variável (tipo
        // valor 7/9 no barcode); zero para REA.
        $quantidadeMoeda = Util::nFloat($this->rem(107, 121, $detalhe) / 100000000, 8, false);

        $d->setCodigoBarras($codigoBarras)
            ->setOcorrencia($primary)
            ->setOcorrenciaDescricao(Arr::get($this->ocorrencias, $primary, 'Desconhecida'))
            ->setTipoMovimento(trim($this->rem(15, 17, $detalhe)))
            ->setTipoPagamento($this->resolveTipoPagamento($this->getHeaderLote()->getFormaLancamento()))
            ->setNomeFavorecido($nomeFavorecido)
            ->setSeuNumero($seuNumero)
            ->setNossoNumero($nossoNumero)
            ->setDataVencimento($this->rem(96, 103, $detalhe))
            ->setDataPagamento($this->rem(137, 144, $detalhe))
            ->setValor(Util::nFloat($this->rem(122, 136, $detalhe) / 100, 2, false))
            ->setValorRealEfetivado(Util::nFloat($this->rem(145, 159, $detalhe) / 100, 2, false));

        if ($notaFiscal !== '') {
            $d->addInformacaoAdicional('numero_nota_fiscal', $notaFiscal);
        }
        if ($quantidadeMoeda > 0) {
            $d->addInformacaoAdicional('quantidade_moeda', $quantidadeMoeda);
        }

        // Favorecido do segmento O — não há segmento B/J-52 vinculado, então
        // o próprio nome do contribuinte/concessionária é a única informação
        // disponível para compor a Pessoa.
        if ($nomeFavorecido !== '' && ! $d->getFavorecido()) {
            $d->setFavorecido(['nome' => $nomeFavorecido]);
        }

        $this->classificarOcorrencia($d, $codigos);

        return true;
    }

    /**
     * Extrai agência / conta / DAC do segmento A conforme NOTA 11 do manual.
     *
     * - Banco favorecido 341 ou 409: agência 25-28 (4), conta 36-41 (6), DAC 43.
     * - Outros bancos: agência 24-28 (5), conta 30-41 (12), DAC 42-43.
     * - Conta pagamento (identificação "PG"): conta ocupa 24-43 (20 dígitos),
     *   agência e DAC ficam vazios.
     *
     * @param array  $detalhe
     * @param string $bancoFavorecido
     * @param string $identTransferencia
     * @return array{agencia:string, conta:string, dv:string}
     */
    protected function parseContaFavorecido(array $detalhe, $bancoFavorecido, $identTransferencia = '')
    {
        if ($identTransferencia === 'PG') {
            return [
                'agencia' => '',
                'conta'   => ltrim($this->rem(24, 43, $detalhe), '0'),
                'dv'      => '',
            ];
        }

        if (in_array($bancoFavorecido, ['341', '409'], true)) {
            return [
                'agencia' => $this->rem(25, 28, $detalhe),
                'conta'   => $this->rem(36, 41, $detalhe),
                'dv'      => trim($this->rem(43, 43, $detalhe)),
            ];
        }

        return [
            'agencia' => $this->rem(24, 28, $detalhe),
            'conta'   => $this->rem(30, 41, $detalhe),
            'dv'      => trim($this->rem(42, 43, $detalhe)),
        ];
    }

    /**
     * Extrai os códigos de ocorrência (2 caracteres) do campo X(10) de
     * ocorrências. O manual permite até 5 códigos concatenados, em ordem de
     * precedência — o primeiro define o status principal do registro.
     *
     * @param string $raw
     * @return string[]
     */
    protected function parseCodigosOcorrencia($raw)
    {
        $padded = str_pad(substr((string) $raw, 0, 10), 10, ' ', STR_PAD_RIGHT);
        $codes = [];
        for ($i = 0; $i < 10; $i += 2) {
            $code = trim(substr($padded, $i, 2));
            if ($code !== '') {
                $codes[] = $code;
            }
        }

        return $codes;
    }

    /**
     * Classifica a ocorrência conforme a NOTA 8 do manual SISPAG:
     * o código primário determina o status (pago / pendente / cancelado /
     * rejeitado) e alimenta o totalizador correspondente. Códigos secundários
     * que representem rejeição são acumulados em $rejeicoes como metadados.
     *
     * @param \Eduardokum\LaravelBoleto\Cnab\Pagamento\Retorno\Cnab240\Detalhe $d
     * @param string[] $codigos
     * @return void
     */
    protected function classificarOcorrencia($d, array $codigos)
    {
        $primary = $codigos[0] ?? '';

        if ($primary === '') {
            $d->setOcorrenciaTipo($d::OCORRENCIA_OUTROS);

            return;
        }

        // Códigos ausentes no mapa são tratados como rejeição por default.
        $status = Arr::get($this->statusOcorrencias, $primary, $d::OCORRENCIA_REJEITADO);
        $d->setOcorrenciaTipo($status);

        switch ($status) {
            case $d::OCORRENCIA_PAGO:
                $this->totais['pagos']++;
                $this->totais['valor_total'] += $d->getValor();
                break;
            case $d::OCORRENCIA_PENDENTE:
                $this->totais['pendentes']++;
                break;
            case $d::OCORRENCIA_CANCELADO:
                $this->totais['cancelados']++;
                break;
            case $d::OCORRENCIA_REJEITADO:
                $this->totais['rejeitados']++;
                break;
        }

        // Códigos secundários de rejeição viram anotações no registro.
        foreach (array_slice($codigos, 1) as $codigo) {
            if (! isset($this->statusOcorrencias[$codigo])) {
                $descricao = Arr::get($this->ocorrencias, $codigo, 'Código Desconhecido');
                $d->addRejeicao($codigo, $descricao);
            }
        }
    }

    /**
     * @param array $trailerLote
     * @return bool
     * @throws ValidationException
     */
    protected function processarTrailerLote(array $trailerLote)
    {
        // Quantidade real de pagamentos no lote = detalhes iniciados
        // (segmentos A/J/N/O) desde o header deste lote. O campo 18-23 no
        // trailer conta TODOS os registros (tipos 1, 3, 5), incluindo os
        // segmentos complementares B, J-52, C, D, E, F, W e Z.
        $qtdPagamentos = (int) $this->increment - (int) $this->incrementoInicioLote;

        $this->getTrailerLote()
            ->setCodBanco($this->rem(1, 3, $trailerLote))
            ->setLoteServico($this->rem(4, 7, $trailerLote))
            ->setTipoRegistro($this->rem(8, 8, $trailerLote))
            ->setQtdRegistroLote((int) $this->rem(18, 23, $trailerLote))
            ->setValorTotalPagamentos(Util::nFloat($this->rem(24, 41, $trailerLote) / 100, 2, false))
            ->setQtdPagamentos($qtdPagamentos);

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
