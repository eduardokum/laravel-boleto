<?php

namespace Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab400\Banco;

use Illuminate\Support\Arr;
use Eduardokum\LaravelBoleto\Util;
use Eduardokum\LaravelBoleto\Contracts\Cnab\RetornoCnab400;
use Eduardokum\LaravelBoleto\Exception\ValidationException;
use Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab400\AbstractRetorno;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;

class Rendimento extends AbstractRetorno implements RetornoCnab400
{
    /**
     * Código do banco
     *
     * @var string
     */
    protected $codigoBanco = BoletoContract::COD_BANCO_RENDIMENTO;

    /**
     * Array com as ocorrencias do banco;
     *
     * @var array
     */
    private $ocorrencias = [
        '01' => 'Confirma Entrada Título na CIP',
        '02' => 'Entrada Confirmada',
        '03' => 'Entrada Rejeitada',
        '05' => 'Campo Livre Alterado',
        '06' => 'Liquidação Normal',
        '08' => 'Liquidação em Cartório',
        '09' => 'Baixa Automática',
        '10' => 'Baixa por ter sido liquidado',
        '12' => 'Confirma Abatimento',
        '13' => 'Abatimento Cancelado',
        '14' => 'Vencimento Alterado',
        '15' => 'Baixa Rejeitada',
        '16' => 'Instrução Rejeitada',
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
            '03' => 'CEP inválido – Não temos cobrador – Cobrador não Localizado',
            '04' => 'Sigla do Estado inválida',
            '05' => 'Data de Vencimento inválida ou fora do prazo mínimo',
            '06' => 'Código do Banco inválido',
            '08' => 'Nome do sacado não informado',
            '10' => 'Logradouro não informado',
            '14' => 'Registro em duplicidade',
            '19' => 'Data de desconto inválida ou maior que a data de vencimento',
            '20' => 'Valor de IOF não numérico',
            '21' => 'Movimento para título não cadastrado no sistema',
            '22' => 'Valor de desconto + abatimento maior que o valor do título',
            '25' => 'CNPJ ou CPF do sacado inválido (aceito com restrições)',
            '26' => 'Espécie de documento inválida',
            '27' => 'Data de emissão do título inválida',
            '28' => 'Seu número não informado',
            '29' => 'CEP é igual a espaço ou zeros; ou não numérico',
            '30' => 'Valor do título não numérico ou inválido',
            '36' => 'Valor de permanência (mora) não numérico',
            '37' => 'Valor de permanência inconsistente, pois, dentro de um mês, será maior que o valor do título',
            '38' => 'Valor de desconto/abatimento não numérico ou inválido',
            '39' => 'Valor de abatimento não numérico',
            '42' => 'Título já existente em nossos registros. Nosso número não aceito',
            '43' => 'Título enviado em duplicidade nesse movimento',
            '44' => 'Título zerado ou em branco; ou não numérico na remessa',
            '46' => 'Título enviado fora da faixa de Nosso Número, estipulada para o cliente.',
            '51' => 'Tipo/Número de Inscrição Sacador/Avalista Inválido',
            '52' => 'Sacador/Avalista não informado',
            '53' => 'Prazo de vencimento do título excede ao da contratação',
            '54' => 'Banco informado não é nosso correspondente 140-142',
            '55' => 'Banco correspondente informado não cobra este CEP ou não possui faixas de CEP cadastradas',
            '56' => 'Nosso número no correspondente não foi informado',
            '57' => 'Remessa contendo duas instruções incompatíveis – não protestar e dias de protesto ou prazo para protesto inválido.',
            '58' => 'Entradas Rejeitadas – Reprovado no Represamento para Análise',
            '60' => 'CNPJ/CPF do sacado inválido – título recusado',
            '87' => 'Excede Prazo máximo entre emissão e vencimento',
            'AA' => 'Serviço de cobrança inválido',
            'AB' => 'Serviço de "0" ou "5" e banco cobrador <> zeros',
            'AE' => 'Título não possui abatimento',
            'AI' => 'Nossa carteira inválida',
            'AJ' => 'Modalidade com bancos correspondentes inválida',
            'AL' => 'Sacado impedido de entrar nesta cobrança',
            'AU' => 'Data da ocorrência inválida',
            'AV' => 'Valor da tarifa de cobrança inválida',
            'AX' => 'Título em pagamento parcial',
            'BC' => 'Análise gerencial-sacado inválido p/operação crédito',
            'BD' => 'Análise gerencial-sacado inadimplente',
            'BE' => 'Análise gerencial-sacado difere do exigido',
            'BF' => 'Análise gerencial-vencto excede vencto da operação de crédito',
            'BG' => 'Análise gerencial-sacado com baixa liquidez',
            'BH' => 'Análise gerencial-sacado excede concentração',
            'CC' => 'Valor de iof incompatível com a espécie documento',
            'CD' => 'Efetivação de protesto sem agenda válida',
            'CE' => 'Título não aceito - pessoa física',
            'CF' => 'Excede prazo máximo da entrada ao vencimento',
            'CG' => 'Título não aceito – por análise gerencial',
            'CH' => 'Título em espera – em análise pelo banco',
            'CJ' => 'Análise gerencial-vencto do titulo abaixo przcurto',
            'CK' => 'Análise gerencial-vencto do titulo abaixo przlongo',
            'CS' => 'Título rejeitado pela checagem de duplicatas',
            'DA' => 'Análise gerencial – Entrada de Título Descontado com limite cancelado',
            'DB' => 'Análise gerencial – Entrada de Título Descontado com limite vencido',
            'DC' => 'Análise gerencial - cedente com limite cancelado',
            'DD' => 'Análise gerencial – cedente é sacado e teve seu limite cancelado',
            'DE' => 'Análise gerencial - apontamento no Serasa',
            'DG' => 'Endereço sacador/avalista não informado',
            'DH' => 'Cep do sacador/avalista não informado',
            'DI' => 'Cidade do sacador/avalista não informado',
            'DJ' => 'Estado do sacador/avalista inválido ou n informado',
            'DM' => 'Cliente sem Código de Flash cadastrado no cobrador',
            'DN' => 'Título Descontado com Prazo ZERO – Recusado',
            'DP' => 'Data de Referência menor que a Data de Emissão do Título',
            'DT' => 'Nosso Número do Correspondente não deve ser informado',
            'EB' => 'HSBC não aceita endereço de sacado com mais de 38 caracteres',
            'G1' => 'Endereço do sacador incompleto ( lei 12.039)',
            'G2' => 'Sacador impedido de movimentar',
            'G3' => 'Concentração de cep não permitida',
            'G4' => 'Valor do título não permitido',
            'HA' => 'Serviço e Modalidade Incompatíveis',
            'HB' => 'Inconsistências entre Registros Título e Sacador',
            'HC' => 'Ocorrência não disponível',
            'HD' => 'Título com Aceite',
            'HF' => 'Baixa Liquidez do Sacado',
            'HG' => 'Sacado Informou que não paga Boletos',
            'HH' => 'Sacado não confirmou a Nota Fiscal',
            'HI' => 'Checagem Prévia não Efetuada',
            'HJ' => 'Sacado desconhece compra e Nota Fiscal',
            'HK' => 'Compra e Nota Fiscal canceladas pelo sacado',
            'HL' => 'Concentração além do permitido pela área de Crédito',
            'HM' => 'Vencimento acima do permitido pelo área de Crédito',
            'HN' => 'Excede o prazo limite da operação',
            'IX' => 'Título de Cartão de Crédito não aceita instruções',
            'JB' => 'Título de Cartão de Crédito inválido para o Produto',
            'JC' => 'Produto somente para Cartão de Crédito',
            'JH' => 'CB Direta com operação de Desconto Automático',
            'JI' => 'Espécie de Documento incompatível para produto de Cartão de Crédito',
            'JK' => 'Produto não permite alterar Valor e Vencimento',
            'JQ' => 'Título em Correspondente – Alteração não permitida',
            'JS' => 'Título possui Desc/Abatim/Mora/Multa',
            'JT' => 'Título possui Agenda',
            'KC' => 'Título já Sustado',
            'KD' => 'Serviço de Cobrança não permitido para carteira',
            'KE' => 'Título possui caracteres não permitidos.',
            'KF' => 'Operação fechada para novas entradas',
            'KG' => 'Nosso número bancos duplicado.',
            'ZQ' => 'Sem informação da Nota Fiscal Eletrônica',
            'ZR' => 'Chave de Acesso NF Rejeitada',
            'ZS' => 'Chave de Acesso NF Duplicada',
            'ZT' => 'Quantidade NF excede a quantidade permitida (30)',
            'ZU' => 'Chave de Acesso NF inválida',
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
            '57' => 'Remessa contendo duas instruções incompatíveis – não protestar e dias de protesto ou prazo para protesto inválido.',
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

        $msgAdicional = str_split(sprintf('%08s', $this->rem(378, 385, $detalhe)), 2) + array_fill(0, 5, '');
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
            $error = Util::appendStrings(Arr::get($this->rejeicoes[sprintf('%02s', $d->getOcorrencia())], $msgAdicional[0], ''), Arr::get($this->rejeicoes[sprintf('%02s', $d->getOcorrencia())], $msgAdicional[1], ''), Arr::get($this->rejeicoes[sprintf('%02s', $d->getOcorrencia())], $msgAdicional[2], ''), Arr::get($this->rejeicoes[sprintf('%02s', $d->getOcorrencia())], $msgAdicional[3], ''), Arr::get($this->rejeicoes[sprintf('%02s', $d->getOcorrencia())], $msgAdicional[4], ''));
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
