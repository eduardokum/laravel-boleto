<?php

namespace Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab240\Banco;

use Illuminate\Support\Arr;
use Eduardokum\LaravelBoleto\Util;
use Eduardokum\LaravelBoleto\Contracts\Cnab\RetornoCnab240;
use Eduardokum\LaravelBoleto\Exception\ValidationException;
use Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab240\AbstractRetorno;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;

/**
 * Retorno CNAB 240 - Banrisul
 *
 * Implementação baseada no manual oficial:
 *   "Cobrança Banrisul - Leiaute Padrão Febraban CNAB 240 Posições - Versão 10.3"
 *   Atualizado em 08/04/2025.
 */
class Banrisul extends AbstractRetorno implements RetornoCnab240
{
    /**
     * Código do banco
     *
     * @var string
     */
    protected $codigoBanco = BoletoContract::COD_BANCO_BANRISUL;

    /**
     * Tabela completa de movimentos retorno (campo C044 do manual).
     *
     * @var array
     */
    private $ocorrencias = [
        '02' => 'Entrada Confirmada',
        '03' => 'Entrada Rejeitada',
        '04' => 'Transferência de Carteira/Entrada',
        '05' => 'Transferência de Carteira/Baixa',
        '06' => 'Liquidação',
        '07' => 'Confirmação do Recebimento da Instrução de Desconto',
        '08' => 'Confirmação do Recebimento do Cancelamento do Desconto',
        '09' => 'Baixa',
        '11' => 'Títulos em Carteira (Em Ser)',
        '12' => 'Confirmação Recebimento Instrução de Abatimento',
        '13' => 'Confirmação Recebimento Instrução de Cancelamento Abatimento',
        '14' => 'Confirmação Recebimento Instrução Alteração de Vencimento',
        '15' => 'Franco de Pagamento',
        '17' => 'Liquidação Após Baixa ou Liquidação Título Não Registrado',
        '19' => 'Confirmação Recebimento Instrução de Protesto',
        '20' => 'Confirmação Recebimento Instrução de Sustação/Cancelamento de Protesto',
        '23' => 'Remessa a Cartório (Confirmação da Entrada em Cartório)',
        '24' => 'Retirada de Cartório e Manutenção em Carteira',
        '25' => 'Protestado e Baixado (Baixa por Ter Sido Protestado)',
        '26' => 'Instrução Rejeitada',
        '27' => 'Confirmação do Pedido de Alteração de Outros Dados',
        '28' => 'Débito de Tarifas/Custas',
        '29' => 'Ocorrências do Pagador',
        '30' => 'Alteração de Dados Rejeitada',
        '33' => 'Confirmação da Alteração dos Dados do Rateio de Crédito',
        '34' => 'Confirmação do Cancelamento dos Dados do Rateio de Crédito',
        '35' => 'Confirmação do Desagendamento do Débito Automático',
        '36' => 'Confirmação de envio de e-mail/SMS',
        '37' => 'Envio de e-mail/SMS rejeitado',
        '38' => 'Confirmação de alteração do Prazo Limite de Recebimento',
        '39' => 'Confirmação de Dispensa de Prazo Limite de Recebimento',
        '40' => 'Confirmação da alteração do número do título dado pelo Beneficiário',
        '41' => 'Confirmação da alteração do número controle do Participante',
        '42' => 'Confirmação da alteração dos dados do Pagador',
        '43' => 'Confirmação da alteração dos dados do Sacador/Avalista',
        '44' => 'Título pago com cheque devolvido',
        '45' => 'Título pago com cheque compensado',
        '46' => 'Instrução para cancelar protesto confirmada',
        '47' => 'Instrução para protesto para fins falimentares confirmada',
        '48' => 'Confirmação de instrução de transferência de carteira/modalidade de cobrança',
        '49' => 'Alteração de contrato de cobrança',
        '50' => 'Título pago com cheque pendente de liquidação',
        '51' => 'Título DDA reconhecido pelo Pagador',
        '52' => 'Título DDA não reconhecido pelo Pagador',
        '53' => 'Título DDA recusado pela CIP',
        '54' => 'Confirmação da Instrução de Baixa de Título Negativado sem Protesto',
        '55' => 'Confirmação de Pedido de Dispensa de Multa',
        '56' => 'Confirmação do Pedido de Cobrança de Multa',
        '57' => 'Confirmação do Pedido de Alteração de Cobrança de Juros',
        '58' => 'Confirmação do Pedido de Alteração do Valor/Data de Desconto',
        '59' => 'Confirmação do Pedido de Alteração do Beneficiário do Título',
        '60' => 'Confirmação do Pedido de Dispensa de Juros de Mora',
        '61' => 'Confirmação de Alteração do Valor Nominal do Título',
        '63' => 'Título Sustado Judicialmente',
        '64' => 'Confirmação de alteração do valor mínimo/percentual',
        '65' => 'Confirmação de alteração do valor máximo/percentual',
        'AB' => 'Cobrança a Creditar (em trânsito)',
        'AC' => 'Situação do Título – Cartório',
        'RI' => 'Retorno Intradia (pagamento sujeito à confirmação na compensação noturna)',
    ];

    /**
     * Códigos de baixa/liquidação (campo C047-C do manual).
     *
     * @var array
     */
    private $baixa_liquidacao = [
        '01' => 'Por Saldo',
        '02' => 'Por Conta',
        '03' => 'Liquidação no Guichê de Caixa em Dinheiro',
        '04' => 'Compensação Eletrônica',
        '05' => 'Compensação Convencional',
        '06' => 'Por Meio Eletrônico',
        '07' => 'Após Feriado Local',
        '08' => 'Em Cartório',
        '09' => 'Comandada Banco',
        '10' => 'Comandada Cliente Arquivo',
        '11' => 'Comandada Cliente On-line',
        '12' => 'Decurso Prazo - Cliente',
        '13' => 'Decurso Prazo - Banco',
        '14' => 'Protestado',
        '15' => 'Título Excluído',
        '30' => 'Liquidação no Guichê de Caixa em Cheque',
        '31' => 'Liquidação em Banco Correspondente',
        '32' => 'Liquidação Terminal de Auto-Atendimento',
        '33' => 'Liquidação na Internet (Home Banking)',
        '34' => 'Liquidado Office Banking',
        '35' => 'Liquidado Correspondente em Dinheiro',
        '36' => 'Liquidado Correspondente em Cheque',
        '37' => 'Liquidado por meio de Central de Atendimento (Telefone)',
        '61' => 'Liquidado via Pix',
    ];

    /**
     * Códigos de cartório (campo C047-E do manual - movimento AC).
     *
     * @var array
     */
    private $cartorio = [
        '70' => 'Título não selecionado por erro no CNPJ/CPF ou endereço',
        '76' => 'Banco aguarda cópia autenticada do documento',
        '77' => 'Título selecionado falta seu número',
        '78' => 'Título rejeitado pelo cartório por estar irregular',
        '79' => 'Título não selecionado - praça não atendida',
        '80' => 'Cartório aguarda autorização para protestar por edital',
        '90' => 'Protesto sustado por solicitação do Beneficiário',
        '91' => 'Protesto sustado por alteração no vencimento',
        '92' => 'Aponte cobrado de título sustado',
        '93' => 'Protesto sustado por alteração no prazo do protesto',
        '95' => 'Entidade Pública',
        '97' => 'Título em cartório',
    ];

    /**
     * Códigos de tarifas / custas (campo C047-B do manual - movimento 28).
     *
     * @var array
     */
    private $tarifas = [
        '01' => 'Tarifa de Extrato de Posição',
        '02' => 'Tarifa de Manutenção de Título Vencido',
        '03' => 'Tarifa de Sustação',
        '04' => 'Tarifa de Protesto',
        '05' => 'Tarifa de Outras Instruções',
        '06' => 'Tarifa de Outras Ocorrências',
        '07' => 'Tarifa de Envio de Duplicata ao Pagador',
        '08' => 'Custas de Protesto',
        '09' => 'Custas de Sustação de Protesto',
        '10' => 'Custas de Cartório Distribuidor',
        '11' => 'Custas de Edital',
        '12' => 'Tarifa Sobre Devolução de Título Vencido',
        '13' => 'Tarifa Sobre Registro Cobrada na Baixa/Liquidação',
        '14' => 'Tarifa Sobre Reapresentação Automática',
        '15' => 'Tarifa Sobre Rateio de Crédito',
        '16' => 'Tarifa Sobre Informações Via Fax',
        '17' => 'Tarifa Sobre Prorrogação de Vencimento',
        '18' => 'Tarifa Sobre Alteração de Abatimento/Desconto',
        '19' => 'Tarifa Sobre Arquivo mensal (Em Ser)',
        '20' => 'Tarifa Sobre Emissão de Boleto de Pagamento Pré-Emitido pelo Banco',
    ];

    /**
     * Tabela completa de rejeições (campo C047-A do manual).
     *
     * @var array
     */
    private $rejeicoes = [
        '01' => 'Código do Banco Inválido',
        '02' => 'Código do Registro Detalhe Inválido',
        '03' => 'Código do Segmento Inválido',
        '04' => 'Código de Movimento Não Permitido para Carteira',
        '05' => 'Código de Movimento Inválido',
        '06' => 'Tipo/Número de Inscrição do Beneficiário Inválidos',
        '07' => 'Agência/Conta/DV Inválido',
        '08' => 'Nosso Número Inválido',
        '09' => 'Nosso Número Duplicado',
        '10' => 'Carteira Inválida',
        '11' => 'Forma de Cadastramento do Título Inválido',
        '12' => 'Tipo de Documento Inválido',
        '13' => 'Identificação da Emissão do Boleto de Pagamento Inválida',
        '14' => 'Identificação da Distribuição do Boleto de Pagamento Inválida',
        '15' => 'Características da Cobrança Incompatíveis',
        '16' => 'Data de Vencimento Inválida',
        '17' => 'Data de Vencimento Anterior a Data de Emissão',
        '18' => 'Vencimento Fora do Prazo de Operação',
        '19' => 'Título a Cargo de Bancos Correspondentes com Vencimento Inferior a XX Dias',
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
        '35' => 'Valor a Conceder Não Confere',
        '36' => 'Concessão de Abatimento - Já Existe Abatimento Anterior',
        '37' => 'Código para Protesto Inválido',
        '38' => 'Prazo para Protesto Inválido',
        '39' => 'Pedido de Protesto Não Permitido para o Título',
        '40' => 'Título com Ordem de Protesto Emitida',
        '41' => 'Pedido de Cancelamento/Sustação para Títulos sem Instrução de Protesto',
        '42' => 'Código para Baixa/Devolução Inválido',
        '43' => 'Prazo para Baixa/Devolução Inválido',
        '44' => 'Código da Moeda Inválido',
        '45' => 'Nome do Pagador Não Informado',
        '46' => 'Tipo/Número de Inscrição do Pagador Inválidos',
        '47' => 'Endereço do Pagador Não Informado',
        '48' => 'CEP Inválido',
        '49' => 'CEP Sem Praça de Cobrança (Não Localizado)',
        '50' => 'CEP Referente a um Banco Correspondente',
        '51' => 'CEP Incompatível com a Unidade da Federação',
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
        '64' => 'Número da Linha Inválido',
        '65' => 'Código do Banco para Débito Inválido',
        '66' => 'Agência/Conta/DV para Débito Inválido',
        '67' => 'Dados para Débito incompatível com a Identificação da Emissão do Boleto de Pagamento',
        '68' => 'Débito Automático Agendado',
        '69' => 'Débito Não Agendado - Erro nos Dados da Remessa',
        '70' => 'Débito Não Agendado - Pagador Não Consta do Cadastro de Autorizante',
        '71' => 'Débito Não Agendado - Beneficiário Não Autorizado pelo Pagador',
        '72' => 'Débito Não Agendado - Beneficiário Não Participa da Modalidade Débito Automático',
        '73' => 'Débito Não Agendado - Código de Moeda Diferente de Real (R$)',
        '74' => 'Débito Não Agendado - Data Vencimento Inválida',
        '75' => 'Débito Não Agendado, Conforme seu Pedido, Título Não Registrado',
        '76' => 'Débito Não Agendado, Tipo/Num. Inscrição do Debitado, Inválido',
        '77' => 'Transferência para Desconto Não Permitida para a Carteira do Título',
        '78' => 'Data Inferior ou Igual ao Vencimento para Débito Automático',
        '79' => 'Data Juros de Mora Inválido',
        '80' => 'Data do Desconto Inválida',
        '81' => 'Tentativas de Débito Esgotadas - Baixado',
        '82' => 'Tentativas de Débito Esgotadas - Pendente',
        '83' => 'Limite Excedido',
        '84' => 'Número Autorização Inexistente',
        '85' => 'Título com Pagamento Vinculado',
        '86' => 'Seu Número Inválido',
        '87' => 'E-mail/SMS enviado',
        '88' => 'E-mail Lido',
        '89' => 'E-mail/SMS devolvido - e-mail ou celular incorreto',
        '90' => 'E-mail devolvido - caixa postal cheia',
        '91' => 'E-mail/número do celular do Pagador não informado',
        '92' => 'Pagador optante por Boleto de Pagamento Eletrônico - e-mail não enviado',
        '93' => 'Código para emissão de Boleto de Pagamento não permite envio de e-mail',
        '94' => 'Código da Carteira inválido para envio e-mail',
        '95' => 'Contrato não permite o envio de e-mail',
        '96' => 'Número de contrato inválido',
        '97' => 'Rejeição da alteração do prazo limite de recebimento',
        '98' => 'Rejeição de dispensa de prazo limite de recebimento',
        '99' => 'Rejeição da alteração do número do título dado pelo Beneficiário',
        'A1' => 'Rejeição da alteração do número controle do participante',
        'A2' => 'Rejeição da alteração dos dados do Pagador',
        'A3' => 'Rejeição da alteração dos dados do Sacador/avalista',
        'A4' => 'Pagador DDA',
        'A5' => 'Registro Rejeitado – Título já Liquidado',
        'A6' => 'Código do Convenente Inválido ou Encerrado',
        'A7' => 'Título já se encontra na situação Pretendida',
        'A8' => 'Valor do Abatimento inválido para cancelamento',
        'A9' => 'Não autoriza pagamento parcial',
        'B1' => 'Autoriza recebimento parcial',
        'B2' => 'Valor Nominal do Título Conflitante',
        'B3' => 'Tipo de Pagamento Inválido',
        'B4' => 'Valor Máximo/Percentual Inválido',
        'B5' => 'Valor Mínimo/Percentual Inválido',
        'P1' => 'Registrado com QR Code PIX',
        'P2' => 'Registrado sem QR Code PIX',
        'P4' => 'Chave PIX – sem cadastro na DICT',
    ];

    /**
     * Converte um valor monetário lido do CNAB (string) em float seguro.
     * Trata cenários onde o campo vem em branco.
     *
     * @param string|null $value
     * @return float
     */
    protected function moneyFromCnab($value)
    {
        $value = preg_replace('/\D/', '', (string) $value);

        return $value === '' ? 0.0 : Util::nFloat(((int) $value) / 100, 2, false);
    }

    /**
     * Roda antes dos métodos de processar.
     */
    protected function init()
    {
        $this->totais = [
            'liquidados'  => 0,
            'entradas'    => 0,
            'baixados'    => 0,
            'protestados' => 0,
            'erros'       => 0,
            'alterados'   => 0,
        ];
    }

    /**
     * @param array $header
     *
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
            // Manual Banrisul CNAB 240, campo 07.0: numérico, usar as 13 primeiras posições do campo (20 chars).
            ->setCodigoCedente($this->rem(33, 45, $header))
            ->setAgencia($this->rem(53, 57, $header))
            ->setAgenciaDv($this->rem(58, 58, $header))
            ->setConta($this->rem(59, 70, $header))
            ->setContaDv($this->rem(71, 71, $header))
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
            ->setVersaoLayoutLote($this->rem(14, 16, $headerLote))
            ->setTipoInscricao($this->rem(18, 18, $headerLote))
            ->setNumeroInscricao($this->rem(19, 33, $headerLote))
            // Manual Banrisul CNAB 240, campo 11.1: numérico, usar as 13 primeiras posições do campo (20 chars).
            ->setCodigoCedente($this->rem(34, 46, $headerLote))
            ->setAgencia($this->rem(54, 58, $headerLote))
            ->setAgenciaDv($this->rem(59, 59, $headerLote))
            ->setConta($this->rem(60, 71, $headerLote))
            ->setContaDv($this->rem(72, 72, $headerLote))
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
     * @throws ValidationException
     */
    protected function processarDetalhe(array $detalhe)
    {
        $segmento = $this->getSegmentType($detalhe);

        switch ($segmento) {
            case 'T':
                $this->processarSegmentoT($detalhe);
                break;
            case 'U':
                $this->processarSegmentoU($detalhe);
                break;
            case 'Y':
                $this->processarSegmentoY($detalhe);
                break;
        }

        return true;
    }

    /**
     * Segmento T - Dados básicos do título (manual seção 3.12).
     *
     * @param array $detalhe
     * @throws ValidationException
     */
    protected function processarSegmentoT(array $detalhe)
    {
        $d = $this->detalheAtual();
        $movimento = $this->rem(16, 17, $detalhe);

        $d->setOcorrencia($movimento)
            ->setOcorrenciaDescricao(Arr::get($this->ocorrencias, $movimento, 'Desconhecida'))
            ->setNossoNumero($this->rem(38, 57, $detalhe))
            ->setCarteira($this->rem(58, 58, $detalhe))
            ->setNumeroDocumento(trim($this->rem(59, 73, $detalhe)))
            ->setDataVencimento($this->rem(74, 81, $detalhe))
            ->setValor($this->moneyFromCnab($this->rem(82, 96, $detalhe)))
            ->setNumeroControle(trim($this->rem(106, 130, $detalhe)))
            ->setPagador([
                'nome'      => trim($this->rem(149, 188, $detalhe)),
                'documento' => trim($this->rem(134, 148, $detalhe)),
            ])
            ->setValorTarifa($this->moneyFromCnab($this->rem(199, 213, $detalhe)));

        // Motivo da ocorrência (10 alfa = 5 pares de 2 chars).
        $msg = $this->extrairMotivos($this->rem(214, 223, $detalhe));

        $this->classificarOcorrencia($d, $movimento, $msg);
    }

    /**
     * Segmento U - Valores do título (manual seção 3.13).
     *
     * Valores conforme tipo 1 (padrão Banrisul):
     *   Valor pago    = Valor do título - Desconto - Abatimento + Outros + IOF
     *   Valor líquido = Valor pago + Juros - Despesas de Cobrança - Outras Despesas
     *
     * @param array $detalhe
     * @throws ValidationException
     */
    protected function processarSegmentoU(array $detalhe)
    {
        $d = $this->detalheAtual();

        $d->setValorMora($this->moneyFromCnab($this->rem(18, 32, $detalhe))) // Acréscimos (juros + multa + encargos)
            ->setValorDesconto($this->moneyFromCnab($this->rem(33, 47, $detalhe)))
            ->setValorAbatimento($this->moneyFromCnab($this->rem(48, 62, $detalhe)))
            ->setValorIOF($this->moneyFromCnab($this->rem(63, 77, $detalhe)))
            ->setValorRecebido($this->moneyFromCnab($this->rem(78, 92, $detalhe)))
            ->setValorOutrasDespesas($this->moneyFromCnab($this->rem(108, 122, $detalhe)))
            ->setValorOutrosCreditos($this->moneyFromCnab($this->rem(123, 137, $detalhe)))
            ->setDataOcorrencia($this->rem(138, 145, $detalhe))
            ->setDataCredito($this->rem(146, 153, $detalhe));
    }

    /**
     * Segmento Y - Registros opcionais. Hoje só Y-04 (PIX) é tratado, demais
     * são ignorados (Y-01, Y-50, Y-52, Y-53). Manual seção 3.7-3.11.
     *
     * @param array $detalhe
     * @throws ValidationException
     */
    protected function processarSegmentoY(array $detalhe)
    {
        $codigoOpcional = $this->rem(18, 19, $detalhe);

        if ($codigoOpcional === '04') {
            $d = $this->detalheAtual();

            // Posições conforme manual seção 3.8 (segmento Y-04).
            $url = trim($this->rem(82, 158, $detalhe));
            $txid = trim($this->rem(159, 193, $detalhe));

            if ($url !== '') {
                $d->setPixLocation($url);
            }
            if ($txid !== '') {
                $d->setId($txid);
            }
        }
    }

    /**
     * Segmenta a string de motivos (10 alfa) em até 5 pares.
     *
     * @param string $raw
     * @return array
     */
    protected function extrairMotivos($raw)
    {
        $raw = sprintf('%-10s', $raw);
        $partes = str_split($raw, 2);

        return array_pad(array_map('trim', $partes), 5, '');
    }

    /**
     * Classifica a ocorrência do detalhe e popula totais e descrições.
     *
     * @param mixed  $d
     * @param string $movimento
     * @param array  $motivos
     */
    protected function classificarOcorrencia($d, $movimento, array $motivos)
    {
        if ($d->hasOcorrencia('06', '17')) {
            $this->totais['liquidados']++;
            $d->setOcorrenciaTipo($d::OCORRENCIA_LIQUIDADA);
            $d->setOcorrenciaDescricao(
                Util::appendStrings(
                    $d->getOcorrenciaDescricao(),
                    ...$this->traduzirMotivos($motivos, $this->baixa_liquidacao)
                )
            );

            return;
        }

        if ($d->hasOcorrencia('02')) {
            $this->totais['entradas']++;
            $d->setOcorrenciaTipo($d::OCORRENCIA_ENTRADA);

            // Pagador DDA (motivo A4)
            if (in_array('A4', array_map('strtoupper', $motivos), true)) {
                if ($d->getPagador()) {
                    $d->getPagador()->setDda(true);
                }
            }

            return;
        }

        if ($d->hasOcorrencia('09', '25')) {
            // 09 = Baixa, 25 = Protestado e Baixado
            if ($movimento === '25') {
                $this->totais['protestados']++;
                $d->setOcorrenciaTipo($d::OCORRENCIA_PROTESTADA);
            } else {
                $this->totais['baixados']++;
                $d->setOcorrenciaTipo($d::OCORRENCIA_BAIXADA);
            }
            $d->setOcorrenciaDescricao(
                Util::appendStrings(
                    $d->getOcorrenciaDescricao(),
                    ...$this->traduzirMotivos($motivos, $this->baixa_liquidacao)
                )
            );

            return;
        }

        if ($d->hasOcorrencia('27', '14', '33', '34', '35', '38', '39', '40', '41', '42', '43', '46', '48', '49', '54', '55', '56', '57', '58', '59', '60', '61', '64', '65')) {
            $this->totais['alterados']++;
            $d->setOcorrenciaTipo($d::OCORRENCIA_ALTERACAO);

            return;
        }

        if ($d->hasOcorrencia('03', '26', '30')) {
            $this->totais['erros']++;
            $d->setError(Util::appendStrings(...$this->traduzirMotivos($motivos, $this->rejeicoes)));

            return;
        }

        if ($d->hasOcorrencia('28')) {
            // Débito de tarifas/custas - decoração com a tabela de tarifas
            $d->setOcorrenciaTipo($d::OCORRENCIA_OUTROS);
            $d->setOcorrenciaDescricao(
                Util::appendStrings(
                    $d->getOcorrenciaDescricao(),
                    ...$this->traduzirMotivos($motivos, $this->tarifas)
                )
            );

            return;
        }

        if ($d->hasOcorrencia('AC')) {
            // Situação do título - cartório
            $d->setOcorrenciaTipo($d::OCORRENCIA_OUTROS);
            $d->setOcorrenciaDescricao(
                Util::appendStrings(
                    $d->getOcorrenciaDescricao(),
                    ...$this->traduzirMotivos($motivos, $this->cartorio)
                )
            );

            return;
        }

        $d->setOcorrenciaTipo($d::OCORRENCIA_OUTROS);
    }

    /**
     * Traduz uma lista de códigos para suas descrições, ignorando vazios.
     *
     * @param array $motivos
     * @param array $tabela
     * @return array
     */
    protected function traduzirMotivos(array $motivos, array $tabela)
    {
        $out = [];
        foreach ($motivos as $cod) {
            if ($cod === '' || $cod === null) {
                continue;
            }
            $descricao = Arr::get($tabela, strtoupper($cod), '');
            if ($descricao !== '') {
                $out[] = $descricao;
            }
        }

        return $out;
    }

    /**
     * @param array $trailer
     *
     * @return bool
     * @throws ValidationException
     */
    protected function processarTrailerLote(array $trailer)
    {
        $this->getTrailerLote()
            ->setLoteServico($this->rem(4, 7, $trailer))
            ->setTipoRegistro($this->rem(8, 8, $trailer))
            ->setQtdRegistroLote((int) $this->rem(18, 23, $trailer))
            ->setQtdTitulosCobrancaSimples((int) $this->rem(24, 29, $trailer))
            ->setValorTotalTitulosCobrancaSimples($this->moneyFromCnab($this->rem(30, 46, $trailer)))
            ->setQtdTitulosCobrancaVinculada((int) $this->rem(47, 52, $trailer))
            ->setValorTotalTitulosCobrancaVinculada($this->moneyFromCnab($this->rem(53, 69, $trailer)))
            ->setQtdTitulosCobrancaCaucionada((int) $this->rem(70, 75, $trailer))
            ->setValorTotalTitulosCobrancaCaucionada($this->moneyFromCnab($this->rem(76, 92, $trailer)))
            ->setQtdTitulosCobrancaDescontada((int) $this->rem(93, 98, $trailer))
            ->setValorTotalTitulosCobrancaDescontada($this->moneyFromCnab($this->rem(99, 115, $trailer)));

        return true;
    }

    /**
     * @param array $trailer
     *
     * @return bool
     * @throws ValidationException
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
