<?php
namespace Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab400\Banco;

use Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab400\AbstractRetorno;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;
use Eduardokum\LaravelBoleto\Contracts\Cnab\RetornoCnab400;
use Eduardokum\LaravelBoleto\Util;

class Hsbc extends AbstractRetorno implements RetornoCnab400
{
    /**
     * Código do banco
     *
     * @var string
     */
    protected $codigoBanco = BoletoContract::COD_BANCO_HSBC;

    /**
     * Array com as ocorrencias do banco;
     *
     * @var array
     */
    private $ocorrencias = [
        '02' => 'Entrada confirmada',
        '03' => 'Entrada rejeitada ou Instrução rejeitada',
        '06' => 'Liquidação normal em dinheiro',
        '07' => 'Liquidação por conta em dinheiro',
        '09' => 'Baixa automática',
        '10' => 'Baixado conforme instruções',
        '11' => 'Títulos em ser (Conciliação Mensal)',
        '12' => 'Abatimento concedido',
        '13' => 'Abatimento cancelado',
        '14' => 'Vencimento prorrogado',
        '15' => 'Liquidação em cartório em dinheiro',
        '16' => 'Liquidação - baixado/devolvido em data anterior dinheiro',
        '17' => 'Entregue em cartório em .../... protocolo ...........',
        '18' => 'Instrução automática de protesto',
        '21' => 'Instrução de alteração de mora',
        '22' => 'Instrução de protesto processada/re-emitida ',
        '23' => 'Cancelamento de protesto processado',
        '27' => 'Número do cedente ou controle do participante alterado.',
        '31' => 'Liquidação normal em cheque/compensação/banco correspondente',
        '32' => 'Liquidação em cartório em cheque',
        '33' => 'Liquidação por conta em cheque',
        '36' => 'Liquidação - baixado/devolvido em data anterior em cheque',
        '37' => 'Baixa de título protestado',
        '38' => 'Liquidação de título não registrado - em dinheiro (Cobrança Expressa ou Cobrança Diretiva)',
        '39' => 'Liquidação de título não registrado - em cheque (Cobrança Expressa ou Cobrança Diretiva)',
        '49' => 'Vencimento alterado para .../.../...',
        '51' => 'Título DDA aceito pelo sacado.',
        '52' => 'Título DDA não reconhecido pelo sacado.',
        '69' => 'Despesas/custas de cartório(complemento posições 176 a 188) ',
        '70' => 'Ressarcimento sobre títulos.',
        '71' => 'Ocorrência/Instrução não permitida para título em garantia de operação.',
        '72' => 'Concessão de Desconto Aceito.',
        '73' => 'Cancelamento Condição de Desconto Fixo Aceito',
        '74' => 'Cancelamento de Desconto Diário Aceito.',
    ];

    /**
     * Array com as possiveis rejeicoes do banco.
     *
     * @var array
     */
    private $rejeicoes = [
        '01' => 'Valor do desconto não informado/inválido.',
        '02' => 'Inexistência de agência do HSBC na praça do sacado. ',
        '03' => 'CEP do sacado incorreto ou inválido.',
        '04' => 'Cadastro do cedente não aceita banco correspondente. ',
        '05' => 'Tipo de moeda inválido.',
        '06' => 'Prazo de protesto indefinido (não informado)/inválido ou prazo de protesto inferior ao tempo decorrido da data de vencimento em relação ao envio da instrução de alteração de prazo.',
        '07' => 'Data do vencimento inválida.',
        '08' => 'Nosso número(número bancário) utilizado não possui vinculação com a conta cobrança.',
        '09' => 'Taxa mensal de mora acima do permitido (170%).',
        '10' => 'Taxa de multa acima do permitido (10% ao mês).',
        '11' => 'Data limite de desconto inválida.',
        '12' => 'CEP Inválido/Inexistência de Ag HSBC.',
        '13' => 'Valor/Taxa de multa inválida.',
        '14' => 'Valor diário da multa não informado.',
        '15' => 'Quantidade de dias após vencimento para incidência da multa não informada.',
        '16' => 'Outras irregularidades.',
        '17' => 'Data de início da multa inválida.',
        '18' => 'Nosso número (número bancário) já existente para outro título.',
        '19' => 'Valor do título inválido.',
        '20' => 'Ausência CEP/Endereço/CNPJ ou Sacador Avalista. ',
        '21' => 'Título sem borderô.',
        '22' => 'Número da conta do cedente não cadastrado.',
        '23' => 'Instrução não permitida para título em garantia de operação. ',
        '24' => 'Condição de desconto não permitida para titulo em garantia de Operação.',
        '25' => 'Utilizada mais de uma instrução de multa.',
        '26' => 'Ausência do endereço do sacado.',
        '27' => 'CEP inválido.do sacado.',
        '28' => 'Ausência do CPF/CNPJ do sacado em título com instrução de protesto.',
        '29' => 'Agência cedente informada inválida.',
        '30' => 'Número da conta do cedente inválido.',
        '31' => 'Contrato garantia não cadastrado/inválido.',
        '32' => 'Tipo de carteira inválido.',
        '33' => 'Conta corrente do cedente não compatível com o órgão do contratante.',
        '34' => 'Faixa de aplicação não cadastrada/inválida.',
        '35' => 'Nosso número (número bancário) inválido.',
        '36' => 'Data de emissão do título inválida.',
        '37' => 'Valor do título acima de R$ 5.000.000,00 (Cinco milhões de reais).',
        '38' => 'Data de desconto menor que data da emissão.',
        '39' => 'Espécie inválida.',
        '40' => 'Ausência no nome do sacador avalista.',
        '41' => 'Data de início de multa menor que data de emissão. ',
        '42' => 'Quantidade de moeda variável inválida.',
        '43' => 'Controle do participante inválido.',
        '44' => 'Nosso número (número bancário) duplicado no mesmo movimento.',
        '45' => 'Título não aceito para compor a carteira de garantias',
        '50' => 'Título liquidado em..../..../... .(Vide data nas posições 111 a 116). ',
        '51' => 'Data de emissão da ocorrência inválida',
        '52' => 'Nosso número (número bancário) duplicado.',
        '53' => 'Código de ocorrência comandada inválido.',
        '54' => 'Valor do desconto concedido inválido. (Vide valor nas posições 228 a 240).',
        '55' => 'Data de prorrogação de vencimento não informada.',
        '56' => 'Outras irregularidades.',
        '57' => 'Ocorrência não permitida para título em garantia de operações. ',
        '58' => 'Nosso número (número bancário) comandado na instrução/ocorrência não possui vinculação com a conta cobrança. ',
        '59' => 'Nosso número (número bancário) comandado na baixa não possui vinculação com a conta cobrança.',
        '60' => 'Valor do desconto igual ou maior que o valor do título.',
        '61' => 'Titulo com valor em moeda variável não permite condição de desconto.',
        '62' => 'Data do desconto informada não coincide com o registro do título. ',
        '63' => 'Titulo não possui condição de desconto diário.',
        '64' => 'Título baixado em...../...../.....(Vide data nas posições 111 a 116) ',
        '65' => 'Título devolvido em...../...../.....(Vide data nas posições 111 a 116) ',
        '66' => 'Valor do título não confere com o registrado.',
        '67' => 'Nosso número (número bancário) não informado.',
        '68' => 'Nosso número (número bancário) inválido.',
        '69' => 'Concessão de abatimento não é permitida para moeda diferente de Real.',
        '70' => 'Valor do abatimento concedido inválido. (Valor do abatimento zerado, maior ou igual ao valor do título).',
        '71' => 'Cancelamento comandado sobre título sem abatimento. ',
        '72' => 'Concessão de desconto não é permitida para moeda diferente de real.',
        '73' => 'Valor do desconto não informado.',
        '74' => 'Cancelamento comandado sobre título sem desconto.',
        '75' => 'Data de vencimento alterado inválida. (Vide data nas posições 111 a 116).',
        '76' => 'Data de prorrogação de vencimento inválida.',
        '77' => 'Data da instrução inválida.',
        '78' => 'Protesto comandado em duplicidade no mesmo dia.',
        '79' => 'Título não possui instrução de protesto ou está com entrada já confirmada em cartório.',
        '80' => 'Título não possui condição de desconto.',
        '81' => 'Título não possui instrução de abatimento.',
        '82' => 'Valor de juros inválido.',
        '83' => 'Nosso número (número bancário) inexistente. ',
        '84' => 'Baixa/liquidação por órgão não autorizado.',
        '85' => 'Instrução de protesto recusada/inválida.',
        '86' => 'Instrução não permitida para banco correspondente.',
        '87' => 'Valor da instrução inválido.',
        '88' => 'Instrução inválida para tipo de carteira.',
        '89' => 'Valor do desconto informado não coincide com o registro do título.',
    ];

    /**
     * Roda antes dos metodos de processar
     */
    protected function init()
    {
        $this->totais = [
            'valor_recebido' => 0,
            'liquidados' => 0,
            'entradas' => 0,
            'baixados' => 0,
            'protestados' => 0,
            'erros' => 0,
            'alterados' => 0,
        ];
    }

    protected function processarHeader(array $header)
    {
        $this->getHeader()
            ->setOperacaoCodigo($this->rem(2, 2, $header))
            ->setOperacao($this->rem(3, 9, $header))
            ->setServicoCodigo($this->rem(10, 11, $header))
            ->setServico($this->rem(12, 26, $header))
            ->setAgencia($this->rem(28, 31, $header))
            ->setConta($this->rem(38, 43, $header))
            ->setContaDv($this->rem(44, 44, $header))
            ->setData($this->rem(95, 100, $header));

        return true;
    }

    protected function processarDetalhe(array $detalhe)
    {
        $d = $this->detalheAtual();
        $d->setCarteira($this->rem(108, 108, $detalhe))
            ->setNossoNumero($this->rem(63, 73, $detalhe))
            ->setNumeroDocumento($this->rem(117, 126, $detalhe))
            ->setNumeroControle($this->rem(38, 62, $detalhe))
            ->setOcorrencia($this->rem(109, 110, $detalhe))
            ->setOcorrenciaDescricao(array_get($this->ocorrencias, $d->getOcorrencia(), 'Desconhecida'))
            ->setDataOcorrencia($this->rem(111, 116, $detalhe))
            ->setDataVencimento($this->rem(147, 152, $detalhe))
            ->setValor(Util::nFloat($this->rem(153, 165, $detalhe)/100, 2, false))
            ->setValorTarifa(Util::nFloat($this->rem(176, 188, $detalhe)/100, 2, false))
            ->setValorAbatimento(Util::nFloat($this->rem(228, 240, $detalhe)/100, 2, false))
            ->setValorDesconto(Util::nFloat($this->rem(241, 253, $detalhe)/100, 2, false))
            ->setValorRecebido(Util::nFloat($this->rem(254, 266, $detalhe)/100, 2, false))
            ->setValorMora(Util::nFloat($this->rem(267, 279, $detalhe)/100, 2, false));

        $this->totais['valor_recebido'] += $d->getValorRecebido();

        if ($d->hasOcorrencia('06', '07', '15', '16', '31', '32', '33', '36', '38', '39')) {
            $this->totais['liquidados']++;
            $d->setOcorrenciaTipo($d::OCORRENCIA_LIQUIDADA);
        } elseif ($d->hasOcorrencia('02')) {
            $this->totais['entradas']++;
            $d->setOcorrenciaTipo($d::OCORRENCIA_ENTRADA);
        } elseif ($d->hasOcorrencia('09', '10', '16')) {
            $this->totais['baixados']++;
            $d->setOcorrenciaTipo($d::OCORRENCIA_BAIXADA);
        } elseif ($d->hasOcorrencia('37')) {
            $this->totais['protestados']++;
            $d->setOcorrenciaTipo($d::OCORRENCIA_PROTESTADA);
        } elseif ($d->hasOcorrencia('14', '49')) {
            $this->totais['alterados']++;
            $d->setOcorrenciaTipo($d::OCORRENCIA_ALTERACAO);
        } elseif ($d->hasOcorrencia('03')) {
            $this->totais['erros']++;
            $d->setError(array_get($this->rejeicoes, $this->rem(302, 303, $detalhe), 'Consulte seu Internet Banking'));
        } else {
            $d->setOcorrenciaTipo($d::OCORRENCIA_OUTROS);
        }

        return true;
    }

    protected function processarTrailer(array $trailer)
    {
        $this->getTrailer()
            ->setQuantidadeTitulos((int) $this->count())
            ->setValorTitulos((float) Util::nFloat($this->totais['valor_recebido'], 2, false))
            ->setQuantidadeErros((int) $this->totais['erros'])
            ->setQuantidadeEntradas((int) $this->totais['entradas'])
            ->setQuantidadeLiquidados((int) $this->totais['liquidados'])
            ->setQuantidadeBaixados((int) $this->totais['baixados'])
            ->setQuantidadeAlterados((int) $this->totais['alterados']);

        return true;
    }
}
