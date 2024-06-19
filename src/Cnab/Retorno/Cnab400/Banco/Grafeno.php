<?php

namespace Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab400\Banco;

use Illuminate\Support\Arr;
use Eduardokum\LaravelBoleto\Util;
use Eduardokum\LaravelBoleto\Contracts\Cnab\RetornoCnab400;
use Eduardokum\LaravelBoleto\Exception\ValidationException;
use Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab400\AbstractRetorno;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;

class Grafeno extends AbstractRetorno implements RetornoCnab400
{
    /**
     * Código do banco
     *
     * @var string
     */
    protected $codigoBanco = BoletoContract::COD_BANCO_GRAFENO;

    /**
     * Array com as ocorrencias do banco;
     *
     * @var array
     */
    private $ocorrencias = [
        '02' => 'Entrada Confirmada',
        '03' => 'Entrada Rejeitada',
        '06' => 'Liquidação normal (sem motivo)',
        '09' => 'Baixado Automat. via Arquivo',
        '10' => 'Baixado conforme instruções da Agência',
        '11' => 'Em Ser - Arquivo de Títulos pendentes',
        '12' => 'Abatimento Concedido',
        '13' => 'Abatimento Cancelado',
        '14' => 'Vencimento Alterado',
        '17' => 'Liquidação após baixa ou Título não registrado 18 - Acerto de Depositária',
        '21' => 'Acerto do Controle do Participante',
        '22' => 'Título Com Pagamento Cancelado',
        '24' => 'Entrada rejeitada por CEP Irregular',
        '27' => 'Baixa Rejeitada',
        '28' => 'Débito de tarifas/custas',
        '29' => 'Ocorrências do Pagador',
        '30' => 'Alteração de Outros Dados Rejeitados',
        '32' => 'Instrução Rejeitada',
        '33' => 'Confirmação Pedido Alteração Outros Dados',
        '40' => 'Estorno de pagamento',
        '77' => 'Grafeno Titularidades',
    ];

    /**
     * Array com as possiveis rejeicoes do banco.
     *
     * @var array
     */
    private $rejeicoes = [
        '03' => [
            '00' => 'Ocorrência Aceita',
            '02' => 'Código do registro detalhe inválido',
            '03' => 'Código da ocorrência inválida',
            '04' => 'Código de ocorrência não permitida para a carteira',
            '05' => 'Código de ocorrência não numérico',
            '07' => 'Agência/conta/Digito Inválido',
            '08' => 'Nosso número inválido',
            '09' => 'Nosso número duplicado',
            '10' => 'Carteira inválida',
            '13' => 'Identificação da emissão do bloqueto inválida',
            '16' => 'Data de vencimento inválida',
            '18' => 'Vencimento fora do prazo de operação',
            '20' => 'Valor do Título inválido',
            '21' => 'Espécie do Título inválida',
            '22' => 'Espécie não permitida para a carteira',
            '23' => 'Tipo Pagamento não contratado',
            '24' => 'Data de emissão inválida',
            '27' => 'Valor/Taxa de Juros Mora Invalido',
            '28' => 'Código do desconto inválido',
            '29' => 'Valor Desconto > ou = valor título',
            '34' => 'Valor do Abatimento Maior ou Igual ao Valor do Título',
            '44' => 'Código da Moeda Invalido',
            '45' => 'Nome do pagador não informado',
            '46' => 'Tipo/número de inscrição do pagador inválidos',
            '47' => 'Endereço do pagador não informado',
            '48' => 'CEP Inválido',
            '49' => 'CEP sem Praça de Cobrança',
            '50' => 'CEP irregular - Banco Correspondente',
            '59' => 'Valor/Percentual da Multa Inválido',
            '63' => 'Entrada para Título já cadastrado',
            '65' => 'Limite excedido',
            '79' => 'Data de Juros de Mora Invalida',
            '80' => 'Data do Desconto Invalida',
            '86' => 'Seu Número Invalido',
        ],
        '06' => [
            '00' => 'Credito Disponível',
            '15' => 'Crédito Indisponível',
            '18' => 'Pagamento Parcial',
        ],
        '09' => [
            '00' => 'Ocorrência Aceita',
            '10' => 'Baixa Comandada pelo cliente',
        ],
        '10' => [
            '00' => 'Baixado Conforme Instruções da Agência',
            '14' => 'Título Protestado',
            '16' => 'Título Baixado pelo Banco por decurso Prazo',
            '20' => 'Titulo Baixado e Transferido para Desconto',
        ],
        '17' => [
            '00' => 'Crédito Disponivel',
            '15' => 'Crédito Indisponivel',
        ],
        '24' => [
            '00' => 'Ocorrência Aceita',
            '48' => 'CEP inválido',
            '49' => 'CEP sem praça de Cobrança',
        ],
        '27' => [
            '00' => 'Ocorrência Aceita',
            '02' => 'Código do registro detalhe Invalido',
            '04' => 'Código de ocorrência não permitido para a carteira',
            '07' => 'Agência/Conta/dígito inválidos',
            '08' => 'Nosso número inválido',
            '09' => 'Nosso Número Duplicado',
            '10' => 'Carteira inválida',
            '15' => 'Carteira/Agência/Conta/nosso número inválidos',
            '16' => 'Data Vencimento Invalida',
            '18' => 'Vencimento Fora do Prazo de Operação',
            '20' => 'Valor Título Invalido',
            '40' => 'Título com ordem de protesto emitido',
            '42' => 'Código para baixa/devolução inválido',
            '45' => 'Nome do sacado não informado ou invalido',
            '46' => 'Tipo/Número de Inscrição do Sacado Invalido',
            '47' => 'Endereço do sacado não informado',
            '48' => 'CEP Invalido',
            '60' => 'Movimento para Título não cadastrado',
            '77' => 'Transferência para desconto não permitido para a carteira',
            '85' => 'Título com pagamento vinculado',
            '86' => 'Seu Número Invalido',
        ],
        '28' => [
            '02' => 'Tarifa de permanência título cadastrado (*)',
            '12' => 'Tarifa de registro (*)',
            '13' => 'Tarifa título pago (*)',
            '14' => 'Tarifa título pago compensação (*)',
            '15' => 'Tarifa título baixado não pago (*)',
            '16' => 'Tarifa alteração de vencimento (*)',
            '17' => 'Tarifa concessão abatimento (*)',
            '18' => 'Tarifa cancelamento de abatimento (*)',
            '19' => 'Tarifa concessão desconto (*)',
            '20' => 'Tarifa cancelamento desconto (*)',
            '40' => 'Baixa registro em duplicidade (*)',
            '41' => 'Tarifa título baixado decurso prazo',
            '43' => 'Tarifa título baixado via remessa',
            '45' => 'Tarifa título baixado conf. Pedido (*)',
            '99' => 'Tr.Tít. Baixado por decurso prazo (*)',
        ],
        '30' => [
            '00' => 'Ocorrência Aceita',
            '01' => 'Código do Banco inválido',
            '04' => 'Código de ocorrência não permitido para a carteira',
            '05' => 'Código da ocorrência não numérico',
            '08' => 'Nosso número inválido',
            '15' => 'Característica da cobrança incompatível',
            '16' => 'Data de vencimento inválido',
            '17' => 'Data de vencimento anterior a data de emissão',
            '18' => 'Vencimento fora do prazo de operação',
            '20' => 'Valor título invalido',
            '21' => 'Espécie título invalida',
            '22' => 'Espécie não permitida para a carteira',
            '23' => 'Tipo pagamento não contratado',
            '24' => 'Data de emissão Inválida',
            '26' => 'Código de juros de mora inválido (*)',
            '27' => 'Valor/taxa de juros de mora inválido',
            '28' => 'Código de desconto inválido',
            '29' => 'Valor do desconto maior/igual ao valor do Título',
            '30' => 'Desconto a conceder não confere',
            '31' => 'Concessão de desconto já existente ( Desconto anterior )',
            '33' => 'Valor do abatimento inválido',
            '34' => 'Valor do abatimento maior/igual ao valor do Título',
            '36' => 'Concessão Abatimento',
            '42' => 'Código para baixa/devolução inválido',
            '43' => 'Prazo para Baixa/Devolução Invalido',
            '46' => 'Tipo/número de inscrição do pagador inválidos',
            '48' => 'Cep Inválido',
            '53' => 'Tipo/Número de inscrição do pagador/avalista inválidos',
            '54' => 'Pagador/avalista não informado',
            '57' => 'Código da multa inválido',
            '58' => 'Data da multa inválida',
            '60' => 'Movimento para Título não cadastrado',
            '79' => 'Data de Juros de mora Inválida',
            '80' => 'Data do desconto inválida 85 - Título com Pagamento Vinculado.',
            '88' => 'E-mail Pagador não lido no prazo 5 dias',
            '91' => 'E-mail pagador não recebido',
        ],
        '32' => [
            '00' => 'Ocorrência Aceita',
            '01' => 'Código do Banco inválido',
            '02' => 'Código Registro Detalhe Invalido',
            '04' => 'Código de ocorrência não permitido para a carteira',
            '05' => 'Código de ocorrência não numérico',
            '08' => 'Nosso número inválido',
            '10' => 'Carteira inválida',
            '15' => 'Características da cobrança incompatíveis',
            '16' => 'Data de vencimento inválida',
            '17' => 'Data de vencimento anterior a data de emissão',
            '18' => 'Vencimento fora do prazo de operação',
            '20' => 'Valor do título inválido',
            '21' => 'Espécie do Título inválida',
            '22' => 'Espécie não permitida para a carteira',
            '23' => 'Tipo Pagamento não contratado',
            '24' => 'Data de emissão inválida',
            '26' => 'Código Juros Mora Invalido',
            '27' => 'Valor/Taxa Juros MoraInvalido',
            '28' => 'Código de desconto inválido',
            '29' => 'Valor do desconto maior/igual ao valor do Título',
            '30' => 'Desconto a conceder não confere',
            '31' => 'Concessão de desconto - Já existe desconto anterior',
            '33' => 'Valor do abatimento inválido',
            '34' => 'Valor do abatimento maior/igual ao valor do Título',
            '36' => 'Concessão abatimento - Já existe abatimento anterior',
            '45' => 'Nome do Pagador não informado',
            '46' => 'Tipo/número de inscrição do Pagador inválidos',
            '47' => 'Endereço do Pagador não informado',
            '48' => 'CEP Inválido',
            '50' => 'CEP referente a um Banco correspondente',
            '52' => 'Unidade da Federação Invalida',
            '53' => 'Tipo de inscrição do pagador avalista inválidos',
            '60' => 'Movimento para Título não cadastrado',
            '65' => 'Limite Excedido',
            '66' => 'Numero Autorização Inexistente',
            '85' => 'Título com pagamento vinculado',
            '86' => 'Seu número inválido',
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
//        if ($this->count() == 1) {
//            $this->getHeader()
//                ->setAgencia($this->rem(25, 29, $detalhe))
//                ->setConta($this->rem(30, 36, $detalhe))
//                ->setContaDv($this->rem(37, 37, $detalhe));
//        }

        $d = $this->detalheAtual();
        $d
//            ->setCarteira($this->rem(108, 108, $detalhe))
            ->setNossoNumero($this->rem(71, 82, $detalhe))
            ->setNumeroDocumento($this->rem(117, 126, $detalhe))
            ->setNumeroControle($this->rem(38, 62, $detalhe))
            ->setOcorrencia($this->rem(109, 110, $detalhe))
            ->setOcorrenciaDescricao(Arr::get($this->ocorrencias, $d->getOcorrencia(), 'Desconhecida'))
            ->setDataOcorrencia($this->rem(111, 116, $detalhe))
            ->setDataVencimento($this->rem(147, 152, $detalhe))
            ->setDataCredito($this->rem(296, 301, $detalhe))
            ->setValor(Util::nFloat($this->rem(153, 165, $detalhe) / 100, 2, false))
            ->setValorTarifa(Util::nFloat($this->rem(176, 188, $detalhe) / 100, 2, false))
//            ->setValorIOF(Util::nFloat($this->rem(215, 227, $detalhe) / 100, 2, false))
            ->setValorAbatimento(Util::nFloat($this->rem(228, 240, $detalhe) / 100, 2, false))
            ->setValorDesconto(Util::nFloat($this->rem(241, 253, $detalhe) / 100, 2, false))
            ->setValorRecebido(Util::nFloat($this->rem(254, 266, $detalhe) / 100, 2, false))
            ->setValorMora(Util::nFloat($this->rem(267, 279, $detalhe) / 100, 2, false))
            ->setValorMulta(Util::nFloat($this->rem(280, 292, $detalhe) / 100, 2, false));

        $msgAdicional = str_split(sprintf('%010s', $this->rem(319, 328, $detalhe)), 2) + array_fill(0, 5, '');
        if ($d->hasOcorrencia('06', '17')) {
            $this->totais['liquidados']++;
            $d->setOcorrenciaTipo($d::OCORRENCIA_LIQUIDADA);
        } elseif ($d->hasOcorrencia('02')) {
            $this->totais['entradas']++;
            $d->setOcorrenciaTipo($d::OCORRENCIA_ENTRADA);
        } elseif ($d->hasOcorrencia('09', '10')) {
            $this->totais['baixados']++;
            $d->setOcorrenciaTipo($d::OCORRENCIA_BAIXADA);
        } elseif ($d->hasOcorrencia('23')) {
            $this->totais['protestados']++;
            $d->setOcorrenciaTipo($d::OCORRENCIA_PROTESTADA);
        } elseif ($d->hasOcorrencia('14', '21', '33')) {
            $this->totais['alterados']++;
            $d->setOcorrenciaTipo($d::OCORRENCIA_ALTERACAO);
        } elseif ($d->hasOcorrencia('03', '24', '27', '30', '32')) {
            $this->totais['erros']++;
            $error = Util::appendStrings(
                Arr::get($this->rejeicoes[sprintf('%02s', $d->getOcorrencia())], $msgAdicional[0], ''),
                Arr::get($this->rejeicoes[sprintf('%02s', $d->getOcorrencia())], $msgAdicional[1], ''),
                Arr::get($this->rejeicoes[sprintf('%02s', $d->getOcorrencia())], $msgAdicional[2], ''),
                Arr::get($this->rejeicoes[sprintf('%02s', $d->getOcorrencia())], $msgAdicional[3], ''),
                Arr::get($this->rejeicoes[sprintf('%02s', $d->getOcorrencia())], $msgAdicional[4], '')
            );
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
     * @throws ValidationException
     */
    protected function processarTrailer(array $trailer)
    {
        $this->getTrailer()
            ->setQuantidadeTitulos($this->rem(18, 25, $trailer))
            ->setValorTitulos(Util::nFloat($this->rem(26, 39, $trailer) / 100, 2, false))
            ->setQuantidadeErros((int) $this->totais['erros'])
            ->setQuantidadeEntradas((int) $this->totais['entradas'])
            ->setQuantidadeLiquidados((int) $this->totais['liquidados'])
            ->setQuantidadeBaixados((int) $this->totais['baixados'])
            ->setQuantidadeAlterados((int) $this->totais['alterados']);

        return true;
    }
}
