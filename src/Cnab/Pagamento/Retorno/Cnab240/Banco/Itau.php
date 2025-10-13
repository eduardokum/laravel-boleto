<?php

namespace Eduardokum\LaravelBoleto\Cnab\Pagamento\Retorno\Cnab240\Banco;

use Illuminate\Support\Arr;
use Eduardokum\LaravelBoleto\Util;
use Eduardokum\LaravelBoleto\Exception\ValidationException;
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
     * Array com as ocorrências do banco
     * @var array
     */
    private $ocorrencias = [
        // Ocorrências gerais
        '00' => 'Crédito ou Débito Efetivado',
        '01' => 'Insuficiência de Fundos - Débito Não Efetuado',
        '02' => 'Crédito ou Débito Cancelado pelo Pagador/Credor',
        '03' => 'Débito Autorizado pela Agência - Efetuado',
        'BD' => 'Inclusão Efetuada com Sucesso',
        'BE' => 'Alteração Efetuada com Sucesso',
        'BF' => 'Exclusão Efetuada com Sucesso',
        'BI' => 'Transferência Efetuada com Sucesso',
        'BJ' => 'Pagamento Efetuado com Sucesso',

        // Ocorrências de erro/rejeição
        'AA' => 'Controle Inválido',
        'AB' => 'Tipo de Operação Inválido',
        'AC' => 'Tipo de Serviço Inválido',
        'AD' => 'Forma de Lançamento Inválida',
        'AE' => 'Tipo/Número de Inscrição Inválido',
        'AG' => 'Agência/Conta Corrente/DV Inválido',
        'AH' => 'Número Sequencial do Registro no Lote Inválido',
        'AI' => 'Código de Segmento de Detalhe Inválido',
        'AJ' => 'Tipo de Movimento Inválido',
        'AL' => 'Código do Banco Favorecido, Instituição de Pagamento ou Depositário Inválido',
        'AM' => 'Agência Mantenedora da Conta Corrente do Favorecido Inválida',
        'AN' => 'Conta Corrente/DV/Conta de Pagamento do Favorecido Inválido',
        'AO' => 'Nome do Favorecido Não Informado',
        'AP' => 'Data de Lançamento Inválida',
        'AQ' => 'Tipo/Quantidade da Moeda Inválido',
        'AR' => 'Valor do Lançamento Inválido',
        'AS' => 'Aviso ao Favorecido - Identificação Inválida',
        'AT' => 'Tipo/Número de Inscrição do Favorecido Inválido',
        'AV' => 'Número do Local do Favorecido Não Informado',
        'AW' => 'Cidade do Favorecido Não Informada',
        'AX' => 'CEP/Complemento do Favorecido Inválido',
        'AY' => 'Sigla do Estado do Favorecido Inválida',
        'BB' => 'Seu Número Inválido',
        'BC' => 'Nosso Número Inválido',
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
        'HK' => 'Código Remessa/Retorno Inválido',
        'HL' => 'Versão de Layout Inválida',
        'IA' => 'Arquivo de Retorno sem Registros',
        'ZA' => 'Registro Aceito',
        'ZB' => 'Registro Aceito com Alteração',
        'ZC' => 'Registro Rejeitado',
        'ZI' => 'Beneficiário divergente',

        // Ocorrências PIX específicas
        'PA' => 'PIX não efetivado',
        'PB' => 'Transação interrompida devido a erro no PSP do Recebedor',
        'PC' => 'QR Code inválido/vencido',
        'PD' => 'Tipo incorreto para a conta transacional especificada',
        'PE' => 'Tipo de transação não é suportado/autorizado',
        'PF' => 'CPF/CNPJ do usuário recebedor incorreto',
        'PG' => 'Chave PIX não cadastrada',
        'PH' => 'Chave PIX inválida',
        'PI' => 'Chave PIX não informada',
        'PJ' => 'Valor do PIX excede limite permitido',
        'PK' => 'Conta do favorecido não permite PIX',
        'PL' => 'Erro no processamento do PIX',

        // Ocorrências adicionais da documentação Itaú (NOTA 8)
        'AE' => 'Data de Pagamento Alterada',
        'AK' => 'Código da Câmara de Compensação do Banco Favorecido/Depositário Inválido',
        'AU' => 'Logradouro do Favorecido Não Informado',
        'AZ' => 'Código/Nome do Banco Depositário Inválido',
        'BA' => 'Código/Nome da Agência Depositária Não Informado',
        'BG' => 'Pagamento Agendado',
        'BH' => 'Pagamento Efetuado com Sucesso',
        'BK' => 'Tipo de Pagamento Incompatível',
        'BL' => 'Pagamento Agendado',
        'CA' => 'Código de Barras - Código do Banco Inválido',
        'CB' => 'Código de Barras - Código da Moeda Inválido',
        'CC' => 'Código de Barras - Dígito Verificador Geral Inválido',
        'CD' => 'Código de Barras - Valor do Título Inválido',
        'CE' => 'Código de Barras - Campo Livre Inválido',
        'CF' => 'Valor do Documento Inválido',
        'CG' => 'Valor do Abatimento Inválido',
        'CH' => 'Valor do Desconto Inválido',
        'CI' => 'CNPJ / CPF / Identificador / Inscrição Estadual / Inscrição no CAD / ICMS Inválido',
        'CJ' => 'Valor da Multa Inválido',
        'CK' => 'Tipo de Inscrição Inválida',
        'CL' => 'Valor do INSS Inválido',
        'CM' => 'Valor do COFINS Inválido',
        'CN' => 'Conta Não Cadastrada',
        'CO' => 'Valor de Outras Entidades Inválido',
        'CQ' => 'Soma das Faturas Difere do Pagamento',
        'CR' => 'Valor do CSLL Inválido',
        'CS' => 'Data de Vencimento da Fatura Inválida',
        'DA' => 'Número de Depend. Salário Família Inválido',
        'DB' => 'Número de Horas Semanais Inválido',
        'DC' => 'Salário de Contribuição INSS Inválido',
        'DD' => 'Salário de Contribuição FGTS Inválido',
        'DE' => 'Valor Total dos Proventos Inválido',
        'DF' => 'Valor Total dos Descontos Inválido',
        'DG' => 'Valor Líquido Não Numérico',
        'DH' => 'Valor Líq. Informado Difere do Calculado',
        'DI' => 'Valor do Salário-Base Inválido',
        'DJ' => 'Base de Cálculo IRRF Inválida',
        'DK' => 'Base de Cálculo FGTS Inválida',
        'DL' => 'Forma de Pagamento Incompatível com Holerite',
        'DM' => 'E-Mail do Favorecido Inválido',
        'DV' => 'DOC / TED Devolvido pelo Banco Favorecido',
        'DU' => 'Finalidade de Holerite Inválida',
        'D1' => 'Mês de Competência do Holerite Inválida',
        'D2' => 'Dia da Competência do Holerite Inválida',
        'D3' => 'Centro de Custo Inválido',
        'D4' => 'Campo Numérico da Funcional Inválido',
        'D5' => 'Data Início de Férias Não Numérica',
        'D6' => 'Data Início de Férias Inconsistente',
        'D7' => 'Data Fim de Férias Não Numérico',
        'D8' => 'Data Fim de Férias Inconsistente',
        'D9' => 'Número de Dependentes IR Inválido',
        'EM' => 'Confirmação de OP Emitida',
        'EX' => 'Devolução de OP Não Sacada pelo Favorecido',
        'E0' => 'Tipo de Movimento Holerite Inválido',
        'E1' => 'Valor 01 do Holerite / Informe Inválido',
        'E2' => 'Valor 02 do Holerite / Informe Inválido',
        'E3' => 'Valor 03 do Holerite / Informe Inválido',
        'E4' => 'Valor 04 do Holerite / Informe Inválido',
        'FC' => 'Pagamento Efetuado Através de Financiamento Compror',
        'FD' => 'Pagamento Efetuado Através de Financiamento Descompror',
        'HM' => 'Erro no Registro Header de Arquivo',
        'IB' => 'Valor do Documento Inválido',
        'IC' => 'Valor do Abatimento Inválido',
        'ID' => 'Valor do Desconto Inválido',
        'IE' => 'Valor da Mora Inválido',
        'IF' => 'Valor da Multa Inválido',
        'IG' => 'Valor da Dedução Inválido',
        'IH' => 'Valor do Acréscimo Inválido',
        'II' => 'Data de Pagamento Inválida / QR Code Expirado',
        'IJ' => 'Competência / Período Referência / Parcela Inválida',
        'IK' => 'Tributo Não Liquidado na SISPAG ou Não Conveniado com Itaú',
        'IL' => 'Código de Pagamento / Empresa /Receita Inválido',
        'IM' => 'Tipo x Forma Não Compatível',
        'IN' => 'Banco/Agência Não Cadastrados',
        'IO' => 'DAC / Valor / Competência / Identificador do Lacre Inválido / Identificador QR Code Inválido',
        'IP' => 'DAC do Código de Barras Inválido / Erro na Validação do QR Code',
        'IQ' => 'Dívida Ativa ou Número de Etiqueta Inválido',
        'IR' => 'Pagamento Alterado',
        'IS' => 'Concessionária Não Conveniada com Itaú',
        'IT' => 'Valor do Tributo Inválido',
        'IU' => 'Valor da Receita Bruta Acumulada Inválido',
        'IV' => 'Número do Documento Origem / Referência Inválido',
        'IX' => 'Código do Produto Inválido',
        'LA' => 'Data de Pagamento de um Lote Alterada',
        'LC' => 'Lote de Pagamentos Cancelado',
        'NA' => 'Pagamento Cancelado por Falta de Autorização',
        'NB' => 'Identificação do Tributo Inválida',
        'NC' => 'Exercício (Ano Base) Inválido',
        'ND' => 'Código Renavam Não Encontrado/Inválido',
        'NE' => 'UF Inválida',
        'NF' => 'Código do Município Inválido',
        'NG' => 'Placa Inválida',
        'NH' => 'Opção Parcela de Pagamento Inválida',
        'NI' => 'Tributo Já Foi Pago ou Está Vencido',
        'NR' => 'Operação Não Realizada',
        'PD' => 'Aquisição Confirmada (Equivale a Ocorrência 02 no Layout de Risco Sacado)',
        'RJ' => 'Registro Rejeitado',
        'RS' => 'Pagamento Disponível para Antecipação no Risco Sacado - Modalidade Risco Sacado Pós Autorizado',
        'SS' => 'Pagamento Cancelado por Insuficiência de Saldo / Limite Diário de Pagto Excedido',
        'TA' => 'Lote Não Aceito - Totais do Lote com Diferença',
        'TI' => 'Titularidade Inválida',
        'X1' => 'Forma Incompatível com Layout 010',
        'X2' => 'Número da Nota Fiscal Inválido',
        'X3' => 'Identificador de NF/CNPJ Inválido',
        'X4' => 'Forma 32 Inválida',
    ];

    /**
     * Array com as possíveis rejeições do banco (espelhamento das ocorrências de erro)
     * @var array
     */
    private $rejeicoes = [
        '01' => 'Insuficiência de Fundos',
        '02' => 'Crédito ou Débito Cancelado',
        'AA' => 'Controle Inválido',
        'AB' => 'Tipo de Operação Inválido',
        'AC' => 'Tipo de Serviço Inválido',
        'AD' => 'Forma de Lançamento Inválida',
        'AE' => 'Tipo/Número de Inscrição Inválido',
        'AG' => 'Agência/Conta Corrente/DV Inválido',
        'AH' => 'Número Sequencial do Registro no Lote Inválido',
        'AI' => 'Código de Segmento de Detalhe Inválido',
        'AJ' => 'Tipo de Movimento Inválido',
        'AK' => 'Código da Câmara de Compensação Inválido',
        'AL' => 'Código do Banco Favorecido Inválido',
        'AM' => 'Agência do Favorecido Inválida',
        'AN' => 'Conta Corrente do Favorecido Inválida',
        'AO' => 'Nome do Favorecido Não Informado',
        'AP' => 'Data de Lançamento Inválida',
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
        'BK' => 'Tipo de Pagamento Incompatível',
        'CA' => 'Código de Barras - Código do Banco Inválido',
        'CB' => 'Código de Barras - Código da Moeda Inválido',
        'CC' => 'Código de Barras - DV Geral Inválido',
        'CD' => 'Código de Barras - Valor do Título Inválido',
        'CE' => 'Código de Barras - Campo Livre Inválido',
        'CF' => 'Valor do Documento Inválido',
        'CG' => 'Valor do Abatimento Inválido',
        'CH' => 'Valor do Desconto Inválido',
        'CI' => 'CNPJ/CPF/Identificador Inválido',
        'CJ' => 'Valor da Multa Inválido',
        'CK' => 'Tipo de Inscrição Inválida',
        'CL' => 'Valor do INSS Inválido',
        'CM' => 'Valor do COFINS Inválido',
        'CN' => 'Conta Não Cadastrada',
        'CO' => 'Valor de Outras Entidades Inválido',
        'CQ' => 'Soma das Faturas Difere do Pagamento',
        'CR' => 'Valor do CSLL Inválido',
        'CS' => 'Data de Vencimento da Fatura Inválida',
        'DL' => 'Forma de Pagamento Incompatível com Holerite',
        'DM' => 'E-Mail do Favorecido Inválido',
        'DV' => 'DOC/TED Devolvido pelo Banco Favorecido',
        'HA' => 'Lote Não Aceito',
        'HB' => 'Inscrição da Empresa Inválida',
        'HC' => 'Convênio Inexistente/Inválido',
        'HD' => 'Agência/Conta Corrente da Empresa Inválida',
        'HE' => 'Tipo de Serviço Inválido para o Contrato',
        'HF' => 'Saldo Insuficiente',
        'HG' => 'Lote Fora de Sequência',
        'HH' => 'Lote Inválido',
        'HI' => 'Arquivo não aceito',
        'HJ' => 'Tipo de Registro Inválido',
        'HK' => 'Código Remessa/Retorno Inválido',
        'HL' => 'Versão de Layout Inválida',
        'HM' => 'Erro no Registro Header de Arquivo',
        'IB' => 'Valor do Documento Inválido',
        'IC' => 'Valor do Abatimento Inválido',
        'ID' => 'Valor do Desconto Inválido',
        'IE' => 'Valor da Mora Inválido',
        'IF' => 'Valor da Multa Inválido',
        'IG' => 'Valor da Dedução Inválido',
        'IH' => 'Valor do Acréscimo Inválido',
        'II' => 'Data de Pagamento Inválida/QR Code Expirado',
        'IJ' => 'Competência/Período/Parcela Inválida',
        'IK' => 'Tributo Não Conveniado com Itaú',
        'IL' => 'Código de Pagamento/Empresa/Receita Inválido',
        'IM' => 'Tipo x Forma Não Compatível',
        'IN' => 'Banco/Agência Não Cadastrados',
        'IO' => 'DAC/Valor/Competência/Lacre/QR Code Inválido',
        'IP' => 'DAC Código de Barras/QR Code Inválido',
        'IQ' => 'Dívida Ativa/Etiqueta Inválido',
        'IR' => 'Pagamento Alterado',
        'IS' => 'Concessionária Não Conveniada',
        'IT' => 'Valor do Tributo Inválido',
        'IU' => 'Valor Receita Bruta Acumulada Inválido',
        'IV' => 'Número Documento Origem/Referência Inválido',
        'IX' => 'Código do Produto Inválido',
        'LA' => 'Data de Pagamento de um Lote Alterada',
        'LC' => 'Lote de Pagamentos Cancelado',
        'NA' => 'Pagamento Cancelado por Falta de Autorização',
        'NB' => 'Identificação do Tributo Inválida',
        'NC' => 'Exercício (Ano Base) Inválido',
        'ND' => 'Código Renavam Inválido',
        'NE' => 'UF Inválida',
        'NF' => 'Código do Município Inválido',
        'NG' => 'Placa Inválida',
        'NH' => 'Opção Parcela de Pagamento Inválida',
        'NI' => 'Tributo Já Pago ou Vencido',
        'NR' => 'Operação Não Realizada',
        'RJ' => 'Registro Rejeitado',
        'SS' => 'Pagamento Cancelado por Insuficiência de Saldo',
        'TA' => 'Lote Não Aceito - Totais com Diferença',
        'TI' => 'Titularidade Inválida',
        'X1' => 'Forma Incompatível com Layout 010',
        'X2' => 'Número da Nota Fiscal Inválido',
        'X3' => 'Identificador de NF/CNPJ Inválido',
        'X4' => 'Forma 32 Inválida',
        'ZC' => 'Registro Rejeitado',
        'ZI' => 'Beneficiário divergente',
        // PIX
        'PA' => 'PIX não efetivado',
        'PB' => 'Erro no PSP do Recebedor',
        'PC' => 'QR Code inválido/vencido',
        'PD' => 'Tipo de conta incorreto',
        'PE' => 'Transação não suportada',
        'PF' => 'CPF/CNPJ incorreto',
        'PG' => 'Chave PIX não cadastrada',
        'PH' => 'Chave PIX inválida',
        'PI' => 'Chave PIX não informada',
        'PJ' => 'Valor excede limite',
        'PK' => 'Conta não permite PIX',
        'PL' => 'Erro no processamento PIX',
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
                ->setNumeroDocumento(explode('-', trim($this->rem(74, 93, $detalhe)))[0] ?? '')
                ->setNumeroControle(explode('-', trim($this->rem(74, 93, $detalhe)))[1] ?? '')
                ->setDataPagamento($this->rem(94, 101, $detalhe))
                ->setValor(Util::nFloat($this->rem(120, 134, $detalhe) / 100, 2, false))
                ->setNossoNumero($this->rem(135, 149, $detalhe))
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
            }
            $d->setTipoPagamento($tipoPagamento);

            // Processa ocorrências
            if (in_array($ocorrencias, ['00', 'BD', 'BI', 'BJ'])) {
                // Pagamento efetivado
                $this->totais['pagos']++;
                $this->totais['valor_total'] += $d->getValor();
                $d->setOcorrenciaTipo($d::OCORRENCIA_PAGO);
            } elseif (in_array($ocorrencias, ['02'])) {
                // Pagamento cancelado
                $this->totais['cancelados']++;
                $d->setOcorrenciaTipo($d::OCORRENCIA_CANCELADO);
            } elseif (substr($ocorrencias, 0, 1) == 'A' || substr($ocorrencias, 0, 1) == 'H' || substr($ocorrencias, 0, 1) == 'P' || in_array($ocorrencias, ['ZC', 'ZI'])) {
                // Pagamento rejeitado ou com erro
                $this->totais['rejeitados']++;
                $d->setOcorrenciaTipo($d::OCORRENCIA_REJEITADO);
            } elseif (in_array($ocorrencias, ['01'])) {
                // Insuficiência de fundos
                $this->totais['rejeitados']++;
                $d->setOcorrenciaTipo($d::OCORRENCIA_REJEITADO);
            } else {
                $d->setOcorrenciaTipo($d::OCORRENCIA_OUTROS);
            }
        }

        if ($segmentType == 'B') {
            // Segmento B - Dados complementares
            // Verifica se é PIX ou TED/DOC pelo campo de tipo de chave (posição 15-16)
            $tipoChave = trim($this->rem(15, 16, $detalhe));

            if (!empty($tipoChave) && in_array($tipoChave, ['01', '02', '03', '04', '05'])) {
                // É PIX - Segmento B específico para PIX
                $d->setFavorecido([
                    'documento' => $this->rem(19, 32, $detalhe),
                    'tipo_chave_pix' => $tipoChave,
                    'chave_pix' => trim($this->rem(128, 227, $detalhe)),
                    'info_entre_usuarios' => trim($this->rem(63, 127, $detalhe)),
                ]);
            } else {
                // É TED/DOC - Segmento B com endereço completo
                $d->setFavorecido([
                    'documento' => $this->rem(19, 32, $detalhe),
                    'endereco' => $this->rem(33, 62, $detalhe),
                    'numero' => $this->rem(63, 67, $detalhe),
                    'complemento' => $this->rem(68, 82, $detalhe),
                    'bairro' => $this->rem(83, 97, $detalhe),
                    'cidade' => $this->rem(98, 117, $detalhe),
                    'cep' => $this->rem(118, 125, $detalhe),
                    'uf' => $this->rem(126, 127, $detalhe),
                    'email' => trim($this->rem(128, 227, $detalhe)),
                ]);
            }

            // Verifica códigos de ocorrência/rejeição no Segmento B
            $codigosOcorrencia = trim($this->rem(231, 240, $detalhe));
            if (!empty($codigosOcorrencia) && !in_array($codigosOcorrencia, ['00', 'BD', 'BI', 'BJ', ''])) {
                // Processa múltiplos códigos (se houver)
                $codigos = str_split($codigosOcorrencia, 2);
                foreach ($codigos as $codigo) {
                    $codigo = trim($codigo);
                    if (!empty($codigo) && !in_array($codigo, ['00', ''])) {
                        $descricao = Arr::get($this->rejeicoes, $codigo, Arr::get($this->ocorrencias, $codigo, 'Código Desconhecido'));
                        $d->addRejeicao($codigo, $descricao);
                    }
                }
            }

            // Se tem rejeições E não foi marcado como pago, marca como erro
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
