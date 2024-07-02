<?php

namespace Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab400\Banco;

use Illuminate\Support\Arr;
use Eduardokum\LaravelBoleto\Util;
use Eduardokum\LaravelBoleto\Contracts\Cnab\RetornoCnab400;
use Eduardokum\LaravelBoleto\Exception\ValidationException;
use Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab400\AbstractRetorno;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;

class Abc extends AbstractRetorno implements RetornoCnab400
{
    /**
     * Código do banco
     *
     * @var string
     */
    protected $codigoBanco = BoletoContract::COD_BANCO_ABC;

    /**
     * Array com as ocorrencias do banco;
     *
     * @var array
     */
    private $ocorrencias = [
        '01' => 'Confirma Entrada Título na CIP',
        '02' => 'Entrada Confirmada',
        '03' => '(*) Entrada Rejeitada',
        '05' => 'Campo Livre Alterado',
        '06' => 'Liquidação Normal',
        '08' => 'Liquidação em Cartório',
        '09' => 'Baixa Automática',
        '10' => 'Baixa por ter sido liquidado',
        '12' => 'Confirma Abatimento',
        '13' => 'Abatimento Cancelado',
        '14' => 'Vencimento Alterado',
        '15' => '(*) Baixa Rejeitada',
        '16' => '(*) Instrução Rejeitada',
        '19' => 'Confirma Recebimento de Ordem de Protesto',
        '20' => 'Confirma Recebimento de Ordem de Sustação',
        '22' => 'Seu número alterado',
        '23' => 'Título enviado para cartório',
        '24' => 'Confirma recebimento de ordem de não protestar',
        '28' => 'Débito de Tarifas/Custas – Correspondentes',
        '40' => 'Tarifa de Entrada (debitada na Liquidação)',
        '43' => 'Baixado por ter sido protestado',
        '96' => 'Tarifa Sobre Instruções – Mês anterior',
        '97' => 'Tarifa Sobre Baixas – Mês Anterior',
        '98' => 'Tarifa Sobre Entradas – Mês Anterior',
        '99' => 'Tarifa Sobre Instruções de Protesto/Sustação – Mês Anterior',
    ];

    /**
     * Array com as possiveis rejeicoes do banco.
     *
     * @var array
     */
    private $rejeicoes = [
        '03' => [
            '00' => 'Sem definição',
            '01' => 'Banco inválido',
            '02' => 'Código do registro detalhe inválido',
            '03' => 'Código do segmento inválido',
            '04' => 'Código do movimento não permitido p/ a carteira',
            '05' => 'Código do movimento inválido',
            '06' => 'Tipo ou nº inscrição do cedente/sacador inválidos',
            '07' => 'Agência, conta ou dígito verificador inválidos',
            '08' => '"Nosso número" ou dígito verificador inválidos',
            '09' => '"Nosso número" duplicado',
            '10' => 'Tipo de carteira inválida',
            '11' => 'Forma de cadastro do título inválida',
            '12' => 'Tipo de documento inválido',
            '13' => 'Identificação da emissão do título inválida',
            '14' => 'Identificação da distribuição do título inválida',
            '15' => 'Características da cobrança incompatíveis',
            '16' => 'Data de vencimento inválida ou igual à data atual',
            '17' => 'Data de vencimento inválida',
            '18' => 'Vencimento do título fora do prazo da operação',
            '19' => 'Título a cargo de bancos - vencimento < XX dias',
            '20' => 'Título com valor inválido',
            '21' => 'Espécie do título inválida',
            '22' => 'Espécie não permitida para a carteira',
            '23' => 'Título com aceite inválido',
            '24' => 'Data de emissão inválida',
            '25' => 'Data de emissão posterior à de entrada do título',
            '26' => 'Código de juros de mora inválido',
            '27' => 'Valor ou taxa de juros de mora inválidos',
            '28' => 'Código do desconto inválido',
            '29' => 'Desconto maior ou igual ao valor do título',
            '30' => 'O desconto a conceder não confere',
            '31' => 'Desconto já concedido',
            '32' => 'IOF com valor inválido',
            '33' => 'Abatimento com valor inválido',
            '34' => 'Abatimento maior ou igual ao valor do título',
            '35' => 'Abatimento a conceder não confere',
            '36' => 'Título já possui abatimento vigente',
            '37' => 'Código para protesto do título inválido',
            '38' => 'Prazo para protesto do título inválido',
            '39' => 'Pedido de protesto não permitido para o título',
            '40' => 'Ordem de protesto emitida para o título',
            '41' => 'Pedido de cancelamento/sustação não aplicável',
            '42' => 'Código para baixa ou devolução do título inválido',
            '43' => 'Prazo para baixa ou devolução do título inválido',
            '44' => 'Código da moeda inválido',
            '45' => 'Nome do sacado não informado',
            '46' => 'Tipo ou nº do CNPJ do sacado inválidos',
            '47' => 'Endereço do sacado não informado (em branco)',
            '48' => 'CEP inválido',
            '49' => 'CEP sem praça de cobrança (não localizado)',
            '50' => 'CEP refere-se a um banco correspondente',
            '51' => 'CEP incompatível com a UF (Unidade da Federação)',
            '52' => 'UF (Unidade da Federação) inválida',
            '53' => 'Tipo ou nº inscrição do sacador/avalista inválidos',
            '54' => 'Sacador/avalista não informado',
            '55' => '"Nosso número" banco correspondente: não informado',
            '56' => 'Código do banco correspondente inválido',
            '57' => 'Código da multa inválido',
            '58' => 'Data da multa inválida',
            '59' => 'Valor ou percentual da multa inválidos',
            '61' => 'Ag. cobradora/díg. verificador: alteração inválida',
            '62' => 'Tipo de impressão inválido',
            '63' => 'Solicitação de entrada: título já cadastrado',
            '64' => 'Número da linha inválido',
            '65' => 'Código do banco para débito inválido',
            '66' => 'Agência, conta ou díg. verif. p/ débito inválidos',
            '67' => 'Dados do débito incompatíveis com emissão do bloq.',
            '68' => 'Débito automático agendado',
            '69' => 'Débito não agendado: erro nos dados da remessa',
            '70' => 'Sacado não consta no cadastro do autorizante',
            '71' => 'Cedente não autorizado pelo sacado',
            '72' => 'Cedente não dispõe da modalidade débito automático',
            '73' => 'Código da moeda diferente de Real',
            '74' => 'Data de vencimento inválida',
            '75' => 'Confira seu pedido. Título não registrado.',
            '76' => 'Tipo ou número de inscrição do debitado é inválido',
            '78' => 'Déb. autom.: data inferior ou igual ao vencimento',
            '79' => 'Data de juros de mora inválida',
            '80' => 'Data de desconto inválida',
            '81' => 'Tentativas de débito esgotadas - título baixado',
            '82' => 'Tentativas de débito esgotadas - título pendente',
            '83' => 'Limite excedido',
            '84' => 'Número de autorização inexistente',
            '85' => 'Título com pagamento vinculado',
            '86' => '"Seu número" inválido (em branco)',
            '87' => 'Prazo entre emissão e vencimento ultrapassado',
            '88' => 'Sacador não aceito - pessoa física',
            '89' => 'Situação do título não permite instrução',
            '90' => 'Desinteresse do banco ABC em relação ao sacado',
            '91' => 'Desinteresse do banco ABC em relação ao sacador',
            'AA' => 'Serviço de cobrança inválido',
            'AB' => 'Conflito entre classificação e banco cobrador',
            'AC' => 'Desconto + abatimento maior que o valor do título',
            'AD' => 'Título pago, baixado ou protestado',
            'AE' => 'Título não tem abatimento',
            'AF' => 'Movimento não permitido para carteira de desconto',
            'AG' => 'Movimentação não permitida para título à vista',
            'AH' => 'Cancelamento de valores inválidos',
            'AI' => '"Nossa carteira" inválida',
            'AJ' => 'Modalidade com bancos correspondentes inválida',
            'AK' => 'Título pertence a outro cliente',
            'AL' => 'Sacado impedido de entrar no ABC Brasil',
            'AM' => 'Sacado isento de protesto ou tentativa de protesto',
            'AN' => 'Sacado inválido, mas aceito com restrições',
            'AO' => '"Nosso número" bancos corresp. fora da faixa/vazio',
            'AP' => 'Título deve estar em aberto e sem protesto',
            'AQ' => 'Entrada rejeitada por represamento reprovado',
            'AR' => 'Instrução não permitida',
            'AS' => '"Nosso número" fora da faixa cedida ao cliente',
            'AT' => 'Valor pago inválido',
            'AU' => 'Data da ocorrência inválida',
            'AV' => 'Valor da tarifa de cobrança inválido',
            'AX' => 'Título em pagamento parcial',
            'AY' => 'Título em aberto e vencido para atender protesto',
            'AZ' => '"Seu número" está duplicado',
            'BJ' => 'Código do movimento inválido',
            'BK' => '"Seu número" inválido (em branco)',
            'BL' => 'Data de vencimento inválida ou igual à data atual',
            'BM' => 'Data inválida de emissão',
            'BN' => 'Data de vencimento inválida',
            'BO' => 'Título com valor inválido',
            'BP' => 'Data de emissão posterior à data de entrada',
            'BQ' => 'Aceite do título inválido',
            'BR' => 'Valor ou taxa de juros de mora inválidos',
            'BS' => 'Abatimento com valor inválido',
            'BT' => 'Abatimento maior ou igual ao valor do título',
            'BU' => 'Desconto a conceder não confere',
            'BV' => 'Desconto + abatimento maior que o valor do título',
            'BW' => 'Tipo ou nº de inscrição do sacado inválidos',
            'BX' => 'IOF com valor inválido',
            'BY' => 'Nome do sacado não informado',
            'BZ' => 'Endereço do sacado não informado (em branco)',
            'C1' => 'Título com saldo inválido',
            'C2' => 'Custas do cartório com valor inválido',
            'C3' => 'Despesas com valor inválido',
            'C4' => 'Data de ocorrência maior que a de processamento',
            'C5' => 'Data da ocorrência inválida',
            'CA' => 'CEP inválido',
            'CB' => 'Título com protesto efetivado ou a efetivar hoje',
            'CC' => 'Valor de IOF incompatível com o tipo de documento',
            'CD' => 'Efetivação de protesto sem agenda válida',
            'CE' => 'Título não aceito - pessoa física',
            'CF' => 'Prazo de vencimento inferior ao permitido',
            'CG' => 'Contate a gerência: título não aceito na rede',
            'CH' => 'Prazo de vencimento menor que o aceito na rede',
            'CI' => 'Nosso número inexistente para o banco correspondente',
            'CJ' => 'Mora diária para títulos emitidos após 07/1997',
            'CK' => 'Valor do IOF maior que o permitido',
            'CL' => 'Carteira inválida',
            'CM' => 'Forma de pagamento inválida',
            'CN' => 'Quantidade de mensagens inválida',
            'CO' => 'Prazo de vencimento inválido',
            'CP' => 'Falta ou inconsistência na identificação do sacado',
            'CQ' => 'Instrução não permite protesto',
            'CR' => 'Título em poder do sacado',
            'CS' => 'Título com exigência de aceite ou reconhecimento',
            'CT' => 'Número da agência/conta/dígito inválido',
            'CU' => 'Nosso número repetido (remessa duplicada)',
            'CV' => 'Nome do sacado igual ao do cedente/avalista',
            'CW' => 'Movimento para título não cadastrado',
            'CX' => 'Multa diária para títulos vencidos após 10/1997',
            'CY' => 'Juros de mora para títulos vencidos após 10/1997',
            'CZ' => 'Quantidade de moeda inválida',
            'D1' => 'Solicitação sem transmissão de instrução',
            'D2' => 'Identificação da empresa inválida',
            'D3' => 'Débito recusado sem informação adicional',
            'D4' => 'Título já se encontra baixado',
            'D5' => 'Boleto já pago e conciliado no ABC Brasil',
            'D6' => 'Valor do abatimento inválido',
            'D7' => 'Valor do IOF inválido',
            'D8' => 'Inscrição do sacador/avalista inválida',
            'D9' => 'Número do documento inválido',
            'DA' => 'Número da parcela inválido',
            'DB' => 'Endereço não informado ou incompleto',
            'DC' => 'Número do documento com caracteres inválidos',
            'DD' => 'Nosso número inválido para a carteira 11',
            'DE' => 'Sacado com nome idêntico ao do sacador',
            'DF' => 'Título com "não aceite" do sacado',
            'DG' => 'Número do documento do sacado inválido',
            'DH' => 'Espécie do documento inválida para protesto',
            'DI' => 'Prazo de protesto/serasa inválido',
            'DJ' => 'Título com "vencimento" inferior ao protesto',
            'DK' => 'Data de cadastro da entrada do título inválida',
            'DL' => 'Tipo ou número do documento do sacador inválido',
            'DM' => 'Abatimento inválido para a carteira 11',
            'DN' => 'Código do vencimento inválido',
            'DO' => 'Data de vencimento não informada ou inválida',
            'DP' => 'Código do desconto inválido',
            'DQ' => 'Nosso número inválido para a carteira 17',
            'DR' => 'Comando não aceito - não existe autorização',
            'DS' => 'Identificação da empresa remetente inválida',
            'DT' => 'Mensagem não registrada',
            'DU' => 'Mensagem sem vínculo para a entrada',
            'DV' => 'Entrada não permitida para a carteira 17',
            'DW' => 'Modalidade inválida para o protesto',
            'DX' => 'Instrução cancelada e rejeitada pela agência',
            'DY' => 'Espécie de título não permitida para protesto',
            'DZ' => 'Movimento sem retorno',
            'E1' => 'Instrução não aceita, motivo 5',
            'E2' => 'Dias para cálculo inválido',
            'E3' => 'Dias para cálculo menor que 15 dias',
            'E4' => 'Dias para cálculo maior que o aceito na rede',
            'E5' => 'Agência/conta/dígito não conferem cedente',
            'E6' => 'Agência/conta/dígito não conferem sacado',
            'E7' => 'Agência/conta/dígito não conferem sacador/avalista',
            'E8' => 'Aceite do título não efetuado - erro de transmissão',
            'E9' => 'Identificação do contrato inválida',
            'EA' => 'Nosso número inválido para a carteira 17',
            'EB' => 'Nosso número já existente na cobrança',
            'EC' => 'Cliente não cadastrado para o produto',
            'ED' => 'Instrução para protesto não permitida para a carteira',
            'EE' => 'Recebimento de instrução não permitido',
            'EF' => 'Abatimento não permitido para o produto',
            'EG' => 'Título vencido fora do prazo para protesto',
            'EH' => 'Título já se encontra vencido',
            'EI' => 'Código da moeda incompatível com o código da carteira',
            'EJ' => 'Produto não permite emissão de título',
            'EK' => 'Duplicidade de transmissão de remessa de título',
            'EL' => 'Tipo de título inválido',
            'EM' => 'Produto não permite baixa / liquidação antecipada',
            'EN' => 'Produto não permite baixa com pagamento',
            'EO' => 'Data da emissão anterior à data de entrada',
            'EP' => 'Data da emissão posterior à data de vencimento',
            'EQ' => 'Código de juros inválido',
            'ER' => 'Juros de mora já informados',
            'ES' => 'Valor do abatimento inválido para cancelamento',
            'ET' => 'Motivo de baixa inválido para cancelamento',
            'EU' => 'CEP do sacado não encontrado',
            'EV' => 'Cadastro do cedente não encontrado',
            'EW' => 'Confira seu pedido. Título não registrado.',
            'EX' => 'CEP base sem praça de cobrança (não localizado)',
            'EY' => 'CEP referente a banco correspondente',
            'EZ' => 'CEP incompatível com a UF (Unidade da Federação)',
            'F1' => 'CEP não numericamente sequencial',
            'F2' => 'UF (Unidade da Federação) inválida',
            'F3' => 'Pessoa física sem registro de sacador avalista',
            'F4' => 'Não autorização para protesto a emissão de bloquetos',
            'F5' => 'Cliente sem crédito no ABC Brasil',
            'F6' => 'Baixa manual efetivada',
            'F7' => 'Bloquetos liquidados nos últimos 24 horas',
            'F8' => 'Identificação do título diferente do documento',
            'F9' => 'Quantidade de caracteres do campo da mensagem',
            'FA' => 'Juros mora incompatível com data de vencimento',
            'FB' => 'Documento informado existente em aberto',
            'FC' => 'Solicitação fora de prazo',
            'FD' => 'Banco e Ag. favorecido não cadastrado',
            'FE' => 'Título sem contrato de cobrança/conta corrente',
            'FF' => 'Identificação de carne de parcelamento',
            'FG' => 'Carteira de concessão inválida',
            'FH' => 'Recusa do cedente para aceite eletrônico',
            'FI' => 'Código de vencimento com prazo de 365 dias',
            'FJ' => 'Beneficiário original do título não localizado',
            'FK' => 'Título sem previsão de liquidação',
            'FL' => 'Exclusão por beneficiário original',
        ],
        '15' => [
            '05' => 'Solicitação de baixa para título já baixado ou liquidado',
            '06' => 'Solicitação de baixa para título não registrado no sistema',
            '08' => 'Solicitação de baixa para título em float',
        ],
        '16' => [
            '04' => 'Data de vencimento não numérica ou inválida',
            '05' => 'Data de Vencimento inválida ou fora do prazo mínimo',
            '14' => 'Registro em duplicidade',
            '19' => 'Data de desconto inválida ou maior que a data de vencimento',
            '20' => 'Campo livre não informado',
            '21' => 'Título não registrado no sistema',
            '22' => 'Título baixado ou liquidado',
            '26' => 'Espécie de documento inválida',
            '27' => 'Instrução não aceita, por não ter sido emitida ordem de protesto ao cartório',
            '28' => 'Título tem instrução de cartório ativa',
            '29' => 'Título não tem instrução de carteira ativa',
            '30' => 'Existe instrução de não protestar, ativa para o título',
            '36' => 'Valor de permanência (mora) não numérico',
            '37' => 'Título Descontado – Instrução não permitida para a carteira',
            '38' => 'Valor do abatimento não numérico ou maior que a soma do valor do título + permanência + multa',
            '39' => 'Título em cartório',
            '40' => 'Instrução recusada – Reprovado no Represamento para Análise',
            '44' => 'Título zerado ou em branco; ou não numérico na remessa',
            '51' => 'Tipo/Número de Inscrição Sacador/Avalista Inválido',
            '53' => 'Prazo de vencimento do título excede ao da contratação',
            '57' => 'Remessa contendo duas instruções incompatíveis – não protestar e dias de protesto ou prazo para protesto inválido',
            'AA' => 'Serviço de cobrança inválido',
            'AE' => 'Título não possui abatimento',
            'AG' => 'Movimento não permitido – Título à vista ou contra apresentação',
            'AH' => 'Cancelamento de valores inválidos',
            'AI' => 'Nossa carteira inválida',
            'AK' => 'Título pertence a outro cliente',
            'AU' => 'Data da ocorrência inválida',
            'AY' => 'Título deve estar em aberto e vencido para acatar protesto',
            'BA' => 'Banco Correspondente Recebedor não é o Cobrador Atual',
            'BB' => 'Título deve estar em cartório para baixar',
            'CB' => 'Título possui protesto efetivado/a efetivar hoje',
            'CT' => 'Título já baixado',
            'CW' => 'Título já transferido',
            'DO' => 'Título em Prejuízo',
            'IX' => 'Título de Cartão de Crédito não aceita instruções',
            'JK' => 'Produto não permite alteração de valor de título',
            'JQ' => 'Título em Correspondente – Não alterar Valor',
            'JS' => 'Título possui Descontos/Abto/Mora/Multa',
            'JT' => 'Título possui Agenda de Protesto/Devolução',
            '99' => 'Ocorrência desconhecida na remessa',
        ],
    ];

    /**
     * Roda antes dos metodos de processar
     */
    protected function init()
    {
        $this->totais = [
            'liquidados'  => 0,
            'entradas'    => 0,
            'baixados'    => 0,
            'protestados' => 0,
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
            ->setOperacaoCodigo($this->rem(2, 2, $header))
            ->setOperacao($this->rem(3, 9, $header))
            ->setServicoCodigo($this->rem(10, 11, $header))
            ->setServico($this->rem(12, 26, $header))
            ->setCodigoCliente($this->rem(27, 46, $header))
            ->setData($this->rem(95, 100, $header));

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
        $d = $this->detalheAtual();
        $d->setCarteira($this->rem(108, 108, $detalhe))
            ->setNossoNumero($this->rem(63, 73, $detalhe))
            ->setNumeroDocumento($this->rem(117, 126, $detalhe))
            ->setNumeroControle($this->rem(38, 62, $detalhe))
            ->setOcorrencia($this->rem(109, 110, $detalhe))
            ->setOcorrenciaDescricao(Arr::get($this->ocorrencias, $d->getOcorrencia(), 'Desconhecida'))
            ->setDataOcorrencia($this->rem(111, 116, $detalhe))
            ->setDataVencimento($this->rem(147, 152, $detalhe))
            ->setValor(Util::nFloat($this->rem(153, 165, $detalhe) / 100, 2, false))
            ->setValorTarifa(Util::nFloat($this->rem(176, 188, $detalhe) / 100, 2, false))
            ->setValorIOF(Util::nFloat($this->rem(215, 227, $detalhe) / 100, 2, false))
            ->setValorAbatimento(Util::nFloat($this->rem(228, 240, $detalhe) / 100, 2, false))
            ->setValorDesconto(Util::nFloat($this->rem(241, 253, $detalhe) / 100, 2, false))
            ->setValorRecebido(Util::nFloat($this->rem(254, 266, $detalhe) / 100, 2, false))
            ->setValorMora(Util::nFloat($this->rem(267, 279, $detalhe) / 100, 2, false));

        $msgAdicional = str_split(sprintf('%08s', $this->rem(378, 385, $detalhe)), 2) + array_fill(0, 4, '');
        if ($d->hasOcorrencia('06', '08', '10')) {
            $this->totais['liquidados']++;
            $d->setOcorrenciaTipo($d::OCORRENCIA_LIQUIDADA);
        } elseif ($d->hasOcorrencia('01', '02')) {
            $this->totais['entradas']++;
            $d->setOcorrenciaTipo($d::OCORRENCIA_ENTRADA);
        } elseif ($d->hasOcorrencia('09')) {
            $this->totais['baixados']++;
            $d->setOcorrenciaTipo($d::OCORRENCIA_BAIXADA);
        } elseif ($d->hasOcorrencia('43')) {
            $this->totais['protestados']++;
            $d->setOcorrenciaTipo($d::OCORRENCIA_PROTESTADA);
        } elseif ($d->hasOcorrencia('05', '14', '22')) {
            $this->totais['alterados']++;
            $d->setOcorrenciaTipo($d::OCORRENCIA_ALTERACAO);
        } elseif ($d->hasOcorrencia('03', '15', '16')) {
            $this->totais['erros']++;
            $error = Util::appendStrings(Arr::get($this->rejeicoes[sprintf('%02s', $d->getOcorrencia())], $msgAdicional[0], ''), Arr::get($this->rejeicoes[sprintf('%02s', $d->getOcorrencia())], $msgAdicional[1], ''), Arr::get($this->rejeicoes[sprintf('%02s', $d->getOcorrencia())], $msgAdicional[2], ''), Arr::get($this->rejeicoes[sprintf('%02s', $d->getOcorrencia())], $msgAdicional[3], ''));
            $d->setError($error);
        } else {
            $d->setOcorrenciaTipo($d::OCORRENCIA_OUTROS);
        }

        return true;
    }

    /**
     * @param array $trailer
     *
     * @return bool
     */
    protected function processarTrailer(array $trailer)
    {
        $this->getTrailer()
            ->setQuantidadeTitulos($this->count())
            ->setQuantidadeEntradas((int) $this->totais['entradas'])
            ->setQuantidadeLiquidados((int) $this->totais['liquidados'])
            ->setQuantidadeBaixados((int) $this->totais['baixados'])
            ->setQuantidadeAlterados((int) $this->totais['alterados']);

        return true;
    }
}
