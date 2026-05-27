<?php

namespace Eduardokum\LaravelBoleto\Cnab\Pagamento\Retorno\Cnab240\Banco;

use Illuminate\Support\Arr;
use Eduardokum\LaravelBoleto\Util;
use Eduardokum\LaravelBoleto\Exception\ValidationException;
use Eduardokum\LaravelBoleto\Cnab\Pagamento\Retorno\Cnab240\Detalhe;
use Eduardokum\LaravelBoleto\Cnab\Pagamento\Retorno\Cnab240\AbstractRetorno;

/**
 * Class Ailos
 *
 * Retorno CNAB 240 do Sistema Ailos (banco 085) para pagamento de títulos de
 * cobrança. Manual "Pagamento por Arquivo - Layout 240 Posições" (versão 10,
 * mai/2025), seções 5.1.3 (Segmento J), 5.1.4 (J-52) e 5.1.6 (J-99).
 *
 * @package Eduardokum\LaravelBoleto\Cnab\Pagamento\Retorno\Cnab240\Banco
 */
class Ailos extends AbstractRetorno
{
    protected $codigoBanco = '085';

    /**
     * Códigos de ocorrência (G059) conforme manual seção "Códigos de Ocorrências".
     * Campo 231-240 do segmento J permite até 5 códigos concatenados (2 chars cada).
     *
     * @var array
     */
    private $ocorrencias = [
        '00' => 'Débito Efetivado',
        '01' => 'Insuficiência de Fundos (Débito não efetuado)',
        '99' => 'Erro ao cadastrar agendamento',
        'AG' => 'Agência/Conta Corrente/DV inválido',
        'AH' => 'Nº Sequencial do Registro no Lote Inválido',
        'AI' => 'Código de Segmento de Detalhe Inválido',
        'AJ' => 'Tipo de Movimento Inválido',
        'AP' => 'Data Lançamento Inválido',
        'AR' => 'Valor do Lançamento Inválido',
        'BD' => 'Inclusão Efetuada com Sucesso',
        'BF' => 'Exclusão Efetuada com Sucesso',
        'BI' => 'Beneficiário Divergente',
        'CA' => 'Código de Barras - Código do Banco Inválido',
        'CB' => 'Código de Barras - Código da Moeda Inválido',
        'CC' => 'Código de Barras - Dígito Verificador Geral Inválido',
        'CD' => 'Código de Barras - Valor do Título Inválido',
        'CE' => 'Código de Barras - Campo Livre Inválido',
        'CF' => 'Valor do Documento Inválido',
        'CG' => 'Valor do Abatimento Inválido',
        'CH' => 'Valor do Desconto Inválido',
        'CI' => 'Valor da Mora Inválido',
        'CJ' => 'Valor da Multa Inválido',
        'HI' => 'Arquivo não Aceito',
        'HJ' => 'Tipo de Registro Inválido',
        'HK' => 'Código Remessa/Retorno Inválido',
        'OD' => 'Agendamento não permitido após Vencimento',
        'OE' => 'Somatória dos valores incorreta',
        'OF' => 'Agendamento já existe',
        'OG' => 'Banco não Encontrado',
        'YA' => 'Título Não Encontrado',
        'YD' => 'Código de Ocorrência Inválido',
    ];

    /**
     * Mapeamento código → status conceitual do Detalhe.
     * Códigos não listados são interpretados como rejeição.
     */
    private $statusOcorrencias = [
        '00' => Detalhe::OCORRENCIA_PAGO,
        'BD' => Detalhe::OCORRENCIA_PENDENTE,  // agendamento aceito, aguardando débito
        'BF' => Detalhe::OCORRENCIA_CANCELADO, // exclusão de agendamento aceita
    ];

    private $incrementoInicioLote = 0;

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
    }

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

    protected function processarHeaderLote(array $headerLote)
    {
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
            ->setContaDv($this->rem(71, 71, $headerLote))
            ->setNomeEmpresa($this->rem(73, 102, $headerLote));

        return true;
    }

    protected function processarDetalhe(array $detalhe)
    {
        $d = $this->detalheAtual();
        $segmentType = $this->getSegmentType($detalhe);

        if ($segmentType == 'J') {
            $registroOpcional = $this->rem(18, 19, $detalhe);

            // J-99 = registro de retorno com totais consolidados (manual seção 5.1.6)
            if ($registroOpcional === '99') {
                return $this->processarSegmentoJ99($detalhe, $d);
            }

            // J-52 = identificação sacado/cedente/sacador
            if ($registroOpcional === '52') {
                return $this->processarSegmentoJ52($detalhe, $d);
            }

            // J = liquidação propriamente dita
            return $this->processarSegmentoJ($detalhe, $d);
        }

        return true;
    }

    /**
     * Processa o segmento J - dados principais da liquidação de boleto.
     */
    protected function processarSegmentoJ(array $detalhe, $d)
    {
        $codigos = $this->parseCodigosOcorrencia($this->rem(231, 240, $detalhe));
        $primary = $codigos[0] ?? '';

        // Reconstrói o código de barras (44 dígitos) a partir das posições 018-061.
        $codigoBarras = $this->rem(18, 20, $detalhe)
            . $this->rem(21, 21, $detalhe)
            . $this->rem(22, 22, $detalhe)
            . $this->rem(23, 26, $detalhe)
            . $this->rem(27, 36, $detalhe)
            . $this->rem(37, 61, $detalhe);

        // "Seu Número" (pos 183-202) é gravado pelo admin no formato "<batch_id>-<control_number>".
        // Quebra para alimentar getNumeroDocumento (batch_id) e getNumeroControle (control_number).
        $seuNumero = trim($this->rem(183, 202, $detalhe));
        $partes = explode('-', $seuNumero);

        $d->setCodigoBarras($codigoBarras)
            ->setCodigoBancoFavorecido(substr($codigoBarras, 0, 3))
            ->setOcorrencia($primary)
            ->setOcorrenciaDescricao(Arr::get($this->ocorrencias, $primary, 'Desconhecida'))
            ->setTipoMovimento(trim($this->rem(15, 17, $detalhe)))
            ->setTipoPagamento('BOLETO')
            ->setNomeFavorecido(trim($this->rem(62, 91, $detalhe)))
            ->setSeuNumero($seuNumero)
            ->setNumeroDocumento($partes[0] ?? '')
            ->setNumeroControle($partes[1] ?? '')
            ->setNossoNumero(trim($this->rem(203, 222, $detalhe)))
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
     * Processa o segmento J-52 - sacado / cedente / sacador avalista.
     */
    protected function processarSegmentoJ52(array $detalhe, $d)
    {
        $tipoInscSacado = $this->rem(20, 20, $detalhe);
        $docSacado      = $this->extrairDocumentoJ52($this->rem(21, 35, $detalhe), $tipoInscSacado);
        $nomeSacado     = trim($this->rem(36, 75, $detalhe));

        if ($docSacado !== '' || $nomeSacado !== '') {
            $d->setSacado([
                'nome'          => $nomeSacado,
                'documento'     => $docSacado,
                'tipoDocumento' => $tipoInscSacado === '1' ? 'CPF' : 'CNPJ',
            ]);
        }

        $tipoInscCedente = $this->rem(76, 76, $detalhe);
        $docCedente      = $this->extrairDocumentoJ52($this->rem(77, 91, $detalhe), $tipoInscCedente);
        $nomeCedente     = trim($this->rem(92, 131, $detalhe));

        if ($docCedente !== '' || $nomeCedente !== '') {
            $d->setCedente([
                'nome'          => $nomeCedente,
                'documento'     => $docCedente,
                'tipoDocumento' => $tipoInscCedente === '1' ? 'CPF' : 'CNPJ',
            ]);

            if (! $d->getFavorecido()) {
                $d->setFavorecido([
                    'nome'          => $nomeCedente,
                    'documento'     => $docCedente,
                    'tipoDocumento' => $tipoInscCedente === '1' ? 'CPF' : 'CNPJ',
                ]);
            }
        }

        $tipoInscSacador = $this->rem(132, 132, $detalhe);
        $docSacador      = $this->extrairDocumentoJ52($this->rem(133, 147, $detalhe), $tipoInscSacador);
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
     * Processa o segmento J-99 - retorno consolidado (manual seção 5.1.6).
     * Geralmente carrega códigos adicionais de ocorrência ou totais finais.
     * Campos exatos variam; mantemos o registro sem alterar o status (já
     * definido pelo segmento J anterior).
     */
    protected function processarSegmentoJ99(array $detalhe, $d)
    {
        $codigos = $this->parseCodigosOcorrencia($this->rem(231, 240, $detalhe));

        // Acumula códigos extras como rejeições adicionais (sem reclassificar status).
        foreach ($codigos as $codigo) {
            if (! isset($this->statusOcorrencias[$codigo])) {
                $descricao = Arr::get($this->ocorrencias, $codigo, 'Código Desconhecido');
                $d->addRejeicao($codigo, $descricao);
            }
        }

        return true;
    }

    /**
     * Documento do J-52 vem pad-zerado em 15 chars. Recorta pelo tipo de inscrição
     * (1=CPF=11 dígitos, 2=CNPJ=14) preservando zeros legítimos. Tipo inválido ou
     * documento todo zerado retorna '' — sinaliza ausência de Pessoa no segmento.
     */
    protected function extrairDocumentoJ52($raw, $tipoInsc)
    {
        $digitos = preg_replace('/\D/', '', (string) $raw);

        if ($digitos === '' || ltrim($digitos, '0') === '') {
            return '';
        }

        if ((string) $tipoInsc === '1') {
            return substr($digitos, -11);
        }

        if ((string) $tipoInsc === '2') {
            return substr($digitos, -14);
        }

        return '';
    }

    /**
     * Extrai até 5 códigos de 2 caracteres do campo X(10) de ocorrências.
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
     * Classifica a ocorrência (código primário define status; secundários
     * viram rejeições adicionais como metadado).
     */
    protected function classificarOcorrencia($d, array $codigos)
    {
        $primary = $codigos[0] ?? '';

        if ($primary === '') {
            $d->setOcorrenciaTipo($d::OCORRENCIA_OUTROS);
            return;
        }

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

        foreach (array_slice($codigos, 1) as $codigo) {
            if (! isset($this->statusOcorrencias[$codigo])) {
                $descricao = Arr::get($this->ocorrencias, $codigo, 'Código Desconhecido');
                $d->addRejeicao($codigo, $descricao);
            }
        }
    }

    protected function processarTrailerLote(array $trailerLote)
    {
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
