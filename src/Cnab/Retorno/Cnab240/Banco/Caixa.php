<?php

namespace Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab240\Banco;

use Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab240\AbstractRetorno;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;
use Eduardokum\LaravelBoleto\Contracts\Cnab\RetornoCnab240;
use Eduardokum\LaravelBoleto\Util;

class Caixa extends AbstractRetorno implements RetornoCnab240
{
    /**
     * Código do banco
     *
     * @var string
     */
    protected $codigoBanco = BoletoContract::COD_BANCO_CEF;

    /**
     * Array com as ocorrencias do banco;
     *
     * @var array
     */
    private $ocorrencias = [
        '01' => 'Solicitação de Impressão de Títulos Confirmada',
        '02' => 'Entrada Confirmada',
        '03' => 'Entrada Rejeitada',
        '04' => 'Transferência de Carteira/Entrada',
        '05' => 'Transferência de Carteira/Baixa',
        '06' => 'Liquidação',
        '07' => 'Confirmação do Recebimento da Instrução de Desconto',
        '08' => 'Confirmação do Recebimento do Cancelamento do Desconto',
        '09' => 'Baixa',
        '12' => 'Confirmação Recebimento Instrução de Abatimento',
        '13' => 'Confirmação Recebimento Instrução de Cancelamento Abatimento',
        '14' => 'Confirmação Recebimento Instrução Alteração de Vencimento',
        '19' => 'Confirmação Recebimento Instrução de Protesto',
        '20' => 'Confirmação Recebimento Instrução de Sustação/Cancelamento de Protesto',
        '23' => 'Remessa a Cartório',
        '24' => 'Retirada de Cartório',
        '25' => 'Protestado e Baixado (Baixa por Ter Sido Protestado)',
        '26' => 'Instrução Rejeitada',
        '27' => 'Confirmação do Pedido de Alteração de Outros Dados',
        '28' => 'Débito de Tarifas/Custas',
        '30' => 'Alteração de Dados Rejeitada',
        '35' => 'Confirmação de Inclusão Banco de Pagador',
        '36' => 'Confirmação de Alteração Banco de Pagador',
        '37' => 'Confirmação de Exclusão Banco de Pagador',
        '38' => 'Emissão de Boletos de Banco de Pagador',
        '39' => 'Manutenção de Pagador Rejeitada',
        '40' => 'Entrada de Título via Banco de Pagador Rejeitada',
        '41' => 'Manutenção de Banco de Pagador Rejeitada',
        '44' => 'Estorno de Baixa / Liquidação',
        '45' => 'Alteração de Dados',
        '46' => 'Liquidação On-line',
        '47' => 'Estorno de Liquidação On-line',
        '51' => 'Título DDA reconhecido pelo pagador',
        '52' => 'Título DDA não reconhecido pelo pagador',
        '53' => 'Título DDA recusado pela CIP',
        '61' => 'Confirmação de alteração do valor nominal do título',
        '62' => 'Confirmação de alteração do valor/percentual mínimo/máximo',
    ];

    /**
     * Array com as possiveis descricoes de baixa e liquidacao.
     *
     * @var array
     */
    private $baixa_liquidacao = [
        '02' => 'Casa Lotérica',
        '03' => 'Agências CAIXA',
        '04' => 'Compensação Eletrônica',
        '05' => 'Compensação Convencional',
        '06' => 'Internet Banking',
        '07' => 'Correspondente Bancário',
        '08' => 'Em Cartório',
        '09' => 'Comandada Banco',
        '10' => 'Comandada Cliente via Arquivo',
        '11' => 'Comandada Cliente On-line',
        '12' => 'Decurso Prazo – Cliente',
        '13' => 'Decurso Prazo – Banco',
        '14' => 'Protestado',
    ];

    /**
     * Array com as possiveis rejeicoes do banco.
     *
     * @var array
     */
    private $rejeicoes = [
        'AA' => 'Cód Desconto Preenchido, Obrig Data e Valor/Perc',
        'AB' => 'Cod Desconto Obrigatório p/ Cód Mov = 7',
        'AC' => 'Forma de Cadastramento Inválida',
        'AD' => 'Data de Desconto deve estar em Ordem Crescente',
        'AE' => 'Data de Desconto é Posterior a Data de Vencimento',
        'AF' => 'Título não está com situação “Em Aberto”',
        'AG' => 'Título já está Vencido / Vencendo',
        'AH' => 'Não existe desconto a ser cancelado',
        'AI' => 'Data solicitada p/ Prot/Dev é anterior à data atual',
        'AJ' => 'Código do Pagador Inválido',
        'AK' => 'Número da Parcela Invalida ou Fora de Sequencia',
        'AL' => 'Estorno de Envio Não Permitido',
        'AM' => 'Nosso Numero Fora de Sequencia',
        'A4' => 'Pagador DDA',
        'B2' => 'Valor Nominal do Título Conflitante',
        'CA' => 'Autorização de pagamento parcial inválida',
        'CB' => 'Identificação do tipo de pagamento inválida',
        'CC' => 'Quantidade de pagamentos possíveis inválida',
        'CD' => 'Tipo de valor máximo inválido',
        'CE' => 'Valor/percentual máximo inválido',
        'CF' => 'Tipo de valor mínimo inválido',
        'CG' => 'Valor/percentual mínimo inválido',
        'CH' => 'Segmento Y53 não informado',
        'CI' => 'Alteração do valor/percentual mínimo/máximo inválida para o tipo de pagamento do título',
        'CJ' => 'Valor/percentual mínimo/máximo igual ao cadastrado',
        'CK' => 'Título autorizado para pagamentos parciais não pode ser desautorizado',
        'CL' => 'Quantidade de pagamentos possíveis menor que a quantidade de pagamentos realizados',
        'VA' => 'Arq.Ret.Inexis. P/ Redisp. Nesta Dt/Nro',
        'VB' => 'Registro Duplicado',
        'VC' => 'Beneficiário deve ser padrão CNAB240',
        'VD' => 'Ident. Banco Pagador Inválida',
        'VE' => 'Num Docto Cobr Inválido',
        'VF' => 'Vlr/Perc a ser concedido inválido',
        'VG' => 'Data de Inscrição Inválida',
        'VH' => 'Data Movto Inválida',
        'VI' => 'Data Inicial Inválida',
        'VJ' => 'Data Final Inválida',
        'VK' => 'Banco de Pagador já cadastrado',
        'VL' => 'Beneficiário não cadastrado',
        'VM' => 'Número de Lote Duplicado',
        'VN' => 'Forma de Emissão de Boleto Inválida',
        'VO' => 'Forma Entrega Boleto Inválida p/ Emissão via Banco',
        'VP' => 'Forma Entrega Boleto Invalida p/ Emissão via Beneficiário',
        'VQ' => 'Opção para Endosso Inválida',
        'VR' => 'Tipo de Juros ao Mês Inválido',
        'VS' => 'Percentual de Juros ao Mês Inválido',
        'VT' => 'Percentual / Valor de Desconto Inválido',
        'VU' => 'Prazo de Desconto Inválido',
        'VV' => 'Preencher Somente Percentual ou Valor',
        'VW' => 'Prazo de Multa Invalido',
        'VX' => 'Perc. Desconto tem que estar em ordem decrescente',
        'VY' => 'Valor Desconto tem que estar em ordem decrescente',
        'VZ' => 'Dias/Data desconto tem que estar em ordem decrescente',
        'WA' => 'Vlr Contr p/ aquisição de Bens Inválid',
        'WB' => 'Vlr Contr p/ Fundo de Reserva Inválid',
        'WC' => 'Vlr Rend. Aplicações Financ Inválido',
        'WD' => 'Valor Multa/Juros Monetários Inválido',
        'WE' => 'Valor Prêmios de Seguro Inválido',
        'WF' => 'Valor Custas Judiciais Inválido',
        'WG' => 'Valor Reembolso de Despesas Inválido',
        'WH' => 'Valor Outros Inválido',
        'WI' => 'Valor de Aquisição de Bens Inválido',
        'WJ' => 'Valor Devolvido ao Consorciado Inválido',
        'WK' => 'Vlr Desp. Registro de Contrato Inválido',
        'WL' => 'Valor de Rendimentos Pagos Inválido',
        'WM' => 'Data de Descrição Inválida',
        'WN' => 'Valor do Seguro Inválido',
        'WO' => 'Data de Vencimento Inválida',
        'WP' => 'Data de Nascimento Inválida',
        'WQ' => 'CPF/CNPJ do Aluno Inválido',
        'WR' => 'Data de Avaliação Inválida',
        'WS' => 'CPF/CNPJ do Locatário Inválido',
        'WT' => 'Literal da Remessa Inválida',
        'WU' => 'Tipo de Registro Inválido',
        'WV' => 'Modelo Inválido',
        'WW' => 'Código do Banco de Pagadores Inválido',
        'WX' => 'Banco de Pagadores não Cadastrado',
        'WY' => 'Qtde dias para Protesto tem que estar entre 2 e 90',
        'WZ' => 'Não existem Pagadores para este Banco',
        'XA' => 'Preço Unitário do Produto Inválido',
        'XB' => 'Preço Total do Produto Inválido',
        'XC' => 'Valor Atual do Bem Inválido',
        'XD' => 'Quantidade de Bens Entregues Inválido',
        'XE' => 'Quantidade de Bens Distribuídos Inválido',
        'XF' => 'Quantidade de Bens não Distribuídos Inválido',
        'XG' => 'Número da Próxima Assembleia Inválido',
        'XH' => 'Horário da Próxima Assembleia Inválido',
        'XI' => 'Data da Próxima Assembleia Inválida',
        'XJ' => 'Número de Ativos Inválido',
        'XK' => 'Número de Desistentes Excluídos Inválido',
        'XL' => 'Número de Quitados Inválido',
        'XM' => 'Número de Contemplados Inválido',
        'XN' => 'Número de não Contemplados Inválido',
        'XO' => 'Data da Última Assembleia Inválida',
        'XP' => 'Quantidade de Prestações Inválida',
        'XQ' => 'Data de Vencimento da Parcela Inválida',
        'XR' => 'Valor da Amortização Inválida',
        'XS' => 'Código do Personalizado Inválido',
        'XT' => 'Valor da Contribuição Inválida',
        'XU' => 'Percentual da Contribuição Inválido',
        'XV' => 'Valor do Fundo de Reserva Inválido',
        'XW' => 'Número Parcela Inválido ou Fora de Sequência',
        'XX' => 'Percentual Fundo de Reserva Inválido',
        'XY' => 'Prz Desc/Multa Preenchido, Obrigat.Perc. ou Valor',
        'XZ' => 'Valor Taxa de Administração Inválida',
        'YA' => 'Data de Juros Inválida ou Não Informada',
        'YB' => 'Data Desconto Inválida ou Não Informada',
        'YC' => 'E-mail Inválido',
        'YD' => 'Código de Ocorrência Inválido',
        'YE' => 'Pagador já Cadastrado (Banco de Pagadores)',
        'YF' => 'Pagador não Cadastrado (Banco de Pagadores)',
        'YG' => 'Remessa Sem Registro Tipo 9',
        'YH' => 'Identificação da Solicitação Inválida',
        'YI' => 'Quantidade Boletos Solicitada Inválida',
        'YJ' => 'Trailer do Arquivo não Encontrado',
        'YK' => 'Tipo Inscrição do Responsável Inválido',
        'YL' => 'Número Inscrição do Responsável Inválido',
        'YM' => 'Ajuste de Vencimento Inválido',
        'YN' => 'Ajuste de Emissão Inválido',
        'YO' => 'Código de Modelo Inválido',
        'YP' => 'Vía de Entrega Inválido',
        'YQ' => 'Espécie Banco de Pagador Inválido',
        'YR' => 'Aceite Banco de Pagador Inválido',
        'YS' => 'Pagador já Cadastrado',
        'YT' => 'Pagador não Cadastrado',
        'YU' => 'Número do Telefone Inválido',
        'YV' => 'CNPJ do Condomínio Inválido',
        'YW' => 'Indicador de Registro de Título Inválido',
        'YX' => 'Valor da Nota Inválido',
        'YY' => 'Qtde de dias para Devolução tem que estar entre 1 e 999',
        'YZ' => 'Quantidade de Produtos Inválida',
        'ZA' => 'Perc. Taxa de Administração Inválido',
        'ZB' => 'Valor do Seguro Inválido',
        'ZC' => 'Percentual do Seguro Inválido',
        'ZD' => 'Valor da Diferença da Parcela Inválido',
        'ZE' => 'Perc. Da Diferença da Parcela Inválido',
        'ZF' => 'Valor Reajuste do Saldo de Caixa Inválido',
        'ZG' => 'Perc. Reajuste do Saldo de Caixa Inválido',
        'ZH' => 'Valor Total a Pagar Inválido',
        'ZI' => 'Percentual ao Total a Pagar Inválido',
        'ZJ' => 'Valor de Outros Acréscimos Inválido',
        'ZK' => 'Perc. De Outros Acréscimos Inválido',
        'ZL' => 'Valor de Outras Deduções Inválido',
        'ZM' => 'Perc. De Outras Deduções Inválido',
        'ZN' => 'Valor da Contribuição Inválida',
        'ZO' => 'Percentual da Contribuição Inválida',
        'ZP' => 'Valor de Juros/Multa Inválido',
        'ZQ' => 'Percentual de Juros/Multa Inválido',
        'ZR' => 'Valor Cobrado Inválido',
        'ZS' => 'Percentual Cobrado Inválido',
        'ZT' => 'Valor Disponibilizado em Caixa Inválido',
        'ZU' => 'Valor Depósito Bancário Inválido',
        'ZV' => 'Valor Aplicações Financeiras Inválido',
        'ZW' => 'Data/Valor Preenchidos, Obrigatório Código Desconto',
        'ZX' => 'Valor Cheques em Cobrança Inválido',
        'ZY' => 'Desconto c/ valor Fixo, Obrigatório Valor do Título',
        'ZZ' => 'Código Movimento Inválido p/ Segmento Y8',
        '01' => 'Código do Banco Inválido',
        '02' => 'Código do Registro Inválido',
        '03' => 'Código do Segmento Inválido',
        '04' => 'Código do Movimento não Permitido p/ Carteira',
        '05' => 'Código do Movimento Inválido',
        '06' => 'Tipo Número Inscrição Beneficiário Inválido',
        '07' => 'Agencia/Conta/DV Inválidos',
        '08' => 'Nosso Número Inválido',
        '09' => 'Nosso Número Duplicado',
        '10' => 'Carteira Inválida',
        '11' => 'Data de Geração Inválida',
        '12' => 'Tipo de Documento Inválido',
        '13' => 'Identif. Da Emissão do Boleto Inválida',
        '14' => 'Identif. Da Distribuição do Boleto Inválida',
        '15' => 'Características Cobrança Incompatíveis',
        '16' => 'Data de Vencimento Inválida',
        '17' => 'Data de Vencimento Anterior à Data de Emissão',
        '18' => 'Vencimento fora do prazo de operação',
        '19' => 'Título a Cargo de Bco Correspondentes c/ Vencto Inferior a XX Dias',
        '20' => 'Valor do Título Inválido',
        '21' => 'Espécie do Título Inválida',
        '22' => 'Espécie do Título Não Permitida para a Carteira',
        '23' => 'Aceite Inválido',
        '24' => 'Data da Emissão Inválida',
        '25' => 'Data da Emissão Posterior a Data de Entrada',
        '26' => 'Código de Juros de Mora Inválido',
        '27' => 'Valor/Taxa de Juros de Mora Inválido',
        '28' => 'Código do Desconto Inválido',
        '29' => 'Valor do Desconto Maior ou Igual ao Valor do Título',
        '30' => 'Desconto a Conceder Não Confere',
        '31' => 'Concessão de Desconto - Já Existe Desconto Anterior',
        '32' => 'Valor do IOF Inválido',
        '33' => 'Valor do Abatimento Inválido',
        '34' => 'Valor do Abatimento Maior ou Igual ao Valor do Título',
        '35' => 'Valor Abatimento a Conceder Não Confere',
        '36' => 'Concessão de Abatimento - Já Existe Abatimento Anterior',
        '37' => 'Código para Protesto Inválido',
        '38' => 'Prazo para Protesto Inválido',
        '39' => 'Pedido de Protesto Não Permitido para o Título',
        '40' => 'Título com Ordem de Protesto Emitida',
        '41' => 'Pedido Cancelamento/Sustação p/ Títulos sem Instrução Protesto',
        '42' => 'Código para Baixa/Devolução Inválido',
        '43' => 'Prazo para Baixa/Devolução Inválido',
        '44' => 'Código da Moeda Inválido',
        '45' => 'Nome do Pagador Não Informado',
        '46' => 'Tipo/Número de Inscrição do Pagador Inválidos',
        '47' => 'Endereço do Pagador Não Informado',
        '48' => 'CEP Inválido',
        '49' => 'CEP Sem Praça de Cobrança (Não Localizado)',
        '50' => 'CEP Referente a um Banco Correspondente',
        '51' => 'CEP incompatível com a Unidade da Federação',
        '52' => 'Unidade da Federação Inválida',
        '53' => 'Tipo/Número de Inscrição do Sacador/Avalista Inválidos',
        '54' => 'Sacador/Avalista Não Informado',
        '55' => 'Nosso número no Banco Correspondente Não Informado',
        '56' => 'Código do Banco Correspondente Não Informado',
        '57' => 'Código da Multa Inválido',
        '58' => 'Data da Multa Inválida',
        '59' => 'Valor/Percentual da Multa Inválido',
        '60' => 'Movimento para Título Não Cadastrado',
        '61' => 'Alteração da Agência Cobradora/DV Inválida',
        '62' => 'Tipo de Impressão Inválido',
        '63' => 'Entrada para Título já Cadastrado',
        '64' => 'Entrada Inválida para Cobrança Caucionada',
        '65' => 'CEP do Pagador não encontrado',
        '66' => 'Agencia Cobradora não encontrada',
        '67' => 'Agencia Beneficiário não encontrada',
        '68' => 'Movimentação inválida para título',
        '69' => 'Alteração de dados inválida',
        '70' => 'Apelido do cliente não cadastrado',
        '71' => 'Erro na composição do arquivo',
        '72' => 'Lote de serviço inválido',
        '73' => 'Código do Beneficiário inválido',
        '74' => 'Beneficiário não pertencente a Cobrança Eletrônica',
        '75' => 'Nome da Empresa inválido',
        '76' => 'Nome do Banco inválido',
        '77' => 'Código da Remessa inválido',
        '78' => 'Data/Hora Geração do arquivo inválida',
        '79' => 'Número Sequencial do arquivo inválido',
        '80' => 'Versão do Lay out do arquivo inválido',
        '81' => 'Literal REMESSA-TESTE - Válido só p/ fase testes',
        '82' => 'Literal REMESSA-TESTE - Obrigatório p/ fase testes',
        '83' => 'Tp Número Inscrição Empresa inválido',
        '84' => 'Tipo de Operação inválido',
        '85' => 'Tipo de serviço inválido',
        '86' => 'Forma de lançamento inválido',
        '87' => 'Número da remessa inválido',
        '88' => 'Número da remessa menor/igual remessa anterior',
        '89' => 'Lote de serviço divergente',
        '90' => 'Número sequencial do registro inválido',
        '91' => 'Erro seq de segmento do registro detalhe',
        '92' => 'Cod movto divergente entre grupo de segm',
        '93' => 'Qtde registros no lote inválido',
        '94' => 'Qtde registros no lote divergente',
        '95' => 'Qtde lotes no arquivo inválido',
        '96' => 'Qtde lotes no arquivo divergente',
        '97' => 'Qtde registros no arquivo inválido',
        '98' => 'Qtde registros no arquivo divergente',
        '99' => 'Código de DDD inválido',
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
     * @throws \Exception
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
            ->setCodigoCedente($this->rem(59, 64, $header))
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
     * @throws \Exception
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
            ->setAgencia($this->rem(54, 58, $headerLote))
            ->setAgenciaDv($this->rem(59, 59, $headerLote))
            ->setCodigoCedente($this->rem(60, 65, $headerLote))
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
     * @throws \Exception
     */
    protected function processarDetalhe(array $detalhe)
    {
        $d = $this->detalheAtual();

        if ($this->getSegmentType($detalhe) == 'T') {
            $d->setOcorrencia($this->rem(16, 17, $detalhe))
                ->setOcorrenciaDescricao(array_get($this->ocorrencias, $this->detalheAtual()->getOcorrencia(), 'Desconhecida'))
                ->setNossoNumero($this->rem(39, 56, $detalhe))
                ->setCarteira($this->rem(58, 58, $detalhe))
                ->setNumeroDocumento($this->rem(59, 69, $detalhe))
                ->setDataVencimento($this->rem(74, 81, $detalhe))
                ->setValor(Util::nFloat($this->rem(82, 96, $detalhe)/100, 2, false))
                ->setNumeroControle($this->rem(106, 130, $detalhe))
                ->setPagador([
                    'nome' => $this->rem(149, 188, $detalhe),
                    'documento' => $this->rem(134, 148, $detalhe),
                ])
                ->setValorTarifa(Util::nFloat($this->rem(199, 213, $detalhe)/100, 2, false));

            /**
             * ocorrencias
            */
            $msgAdicional = str_split(sprintf('%08s', $this->rem(214, 223, $detalhe)), 2) + array_fill(0, 5, '');
            if ($d->hasOcorrencia('06', '46')) {
                $this->totais['liquidados']++;
                $ocorrencia = Util::appendStrings(
                    $d->getOcorrenciaDescricao(),
                    array_get($this->baixa_liquidacao, $msgAdicional[0], ''),
                    array_get($this->baixa_liquidacao, $msgAdicional[1], ''),
                    array_get($this->baixa_liquidacao, $msgAdicional[2], ''),
                    array_get($this->baixa_liquidacao, $msgAdicional[3], ''),
                    array_get($this->baixa_liquidacao, $msgAdicional[4], '')
                );
                $d->setOcorrenciaDescricao($ocorrencia);
                $d->setOcorrenciaTipo($d::OCORRENCIA_LIQUIDADA);
            } elseif ($d->hasOcorrencia('02')) {
                $this->totais['entradas']++;
                if(array_search('a4', array_map('strtolower', $msgAdicional)) !== false) {
                    $d->getPagador()->setDda(true);
                }
                $d->setOcorrenciaTipo($d::OCORRENCIA_ENTRADA);
            } elseif ($d->hasOcorrencia('09')) {
                $this->totais['baixados']++;
                $ocorrencia = Util::appendStrings(
                    $d->getOcorrenciaDescricao(),
                    array_get($this->baixa_liquidacao, $msgAdicional[0], ''),
                    array_get($this->baixa_liquidacao, $msgAdicional[1], ''),
                    array_get($this->baixa_liquidacao, $msgAdicional[2], ''),
                    array_get($this->baixa_liquidacao, $msgAdicional[3], ''),
                    array_get($this->baixa_liquidacao, $msgAdicional[4], '')
                );
                $d->setOcorrenciaDescricao($ocorrencia);
                $d->setOcorrenciaTipo($d::OCORRENCIA_BAIXADA);
            } elseif ($d->hasOcorrencia('25')) {
                $this->totais['protestados']++;
                $d->setOcorrenciaTipo($d::OCORRENCIA_PROTESTADA);
            } elseif ($d->hasOcorrencia('36', '45', '61', '62')) {
                $this->totais['alterados']++;
                $d->setOcorrenciaTipo($d::OCORRENCIA_ALTERACAO);
            } elseif ($d->hasOcorrencia('03', '26', '30', '39', '40', '41')) {
                $this->totais['erros']++;
                $error = Util::appendStrings(
                    array_get($this->rejeicoes, $msgAdicional[0], ''),
                    array_get($this->rejeicoes, $msgAdicional[1], ''),
                    array_get($this->rejeicoes, $msgAdicional[2], ''),
                    array_get($this->rejeicoes, $msgAdicional[3], ''),
                    array_get($this->rejeicoes, $msgAdicional[4], '')
                );
                $d->setError($error);
            } else {
                $d->setOcorrenciaTipo($d::OCORRENCIA_OUTROS);
            }
        }

        if ($this->getSegmentType($detalhe) == 'U') {
            $d->setValorMulta(Util::nFloat($this->rem(18, 32, $detalhe)/100, 2, false))
                ->setValorDesconto(Util::nFloat($this->rem(33, 47, $detalhe)/100, 2, false))
                ->setValorAbatimento(Util::nFloat($this->rem(48, 62, $detalhe)/100, 2, false))
                ->setValorIOF(Util::nFloat($this->rem(63, 77, $detalhe)/100, 2, false))
                ->setValorRecebido(Util::nFloat($this->rem(78, 92, $detalhe)/100, 2, false))
                ->setValorTarifa($d->getValorRecebido() - Util::nFloat($this->rem(93, 107, $detalhe)/100, 2, false))
                ->setDataOcorrencia($this->rem(138, 145, $detalhe))
                ->setDataCredito($this->rem(146, 153, $detalhe));
        }

        return true;
    }

    /**
     * @param array $trailer
     *
     * @return bool
     * @throws \Exception
     */
    protected function processarTrailerLote(array $trailer)
    {
        $this->getTrailerLote()
            ->setLoteServico($this->rem(4, 7, $trailer))
            ->setTipoRegistro($this->rem(8, 8, $trailer))
            ->setQtdRegistroLote((int) $this->rem(18, 23, $trailer));

        return true;
    }

    /**
     * @param array $trailer
     *
     * @return bool
     * @throws \Exception
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
