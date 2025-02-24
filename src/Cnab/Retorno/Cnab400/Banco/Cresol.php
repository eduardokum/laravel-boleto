<?php

namespace Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab400\Banco;

use Illuminate\Support\Arr;
use Eduardokum\LaravelBoleto\Util;
use Eduardokum\LaravelBoleto\Contracts\Cnab\RetornoCnab400;
use Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab400\AbstractRetorno;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;

class Cresol extends AbstractRetorno implements RetornoCnab400
{
    /**
     * Código do banco
     *
     * @var string
     */
    protected $codigoBanco = BoletoContract::COD_BANCO_CRESOL;

    /**
     * Array com as ocorrencias do banco;
     *
     * @var array
     */
    private $ocorrencias = [
        '02' => 'Entrada Confirmada',
        '03' => 'Entrada Rejeitada',
        '04' => 'Transferência de carteira/entrada',
        '05' => 'Transferência de carteira/baixa',
        '06' => 'Liquidação',
        '07' => 'Confirmação do Recebimento da Instrução de Desconto',
        '08' => 'Confirmação do Recebimento do Cancelamento do Desconto',
        '09' => 'Baixa',
        '11' => 'Em Ser - Títulos em carteira',
        '12' => 'Confirmação recebimento instrução de abatimento',
        '13' => 'Confirmação recebimento instrução de cancelamento de abatimento',
        '14' => 'Confirmação recebimento instrução alteração de vencimento',
        '15' => 'Franco de pagamento',
        '17' => 'Liquidação após baixa ou liquidação de título não registrado',
        '19' => 'Confirmação Receb. Inst. de Protesto',
        '20' => 'Confirmação recebimento instrução de sustação/cancelamento de protesto',
        '23' => 'Remessa a cartório (aponte em cartório)',
        '24' => 'Retirada de cartório e manutenção em carteira',
        '25' => 'Protestado e baixado (baixa por ter sido protestado)',
        '26' => 'Instrução rejeitada',
        '27' => 'Confirmação do pedido de alteração de outros dados',
        '28' => 'Débito de tarifas/custas',
        '29' => 'Ocorrências do pagador',
        '30' => 'Alteração de Outros Dados Rejeitados',
        '33' => 'Confirmação da Alteração dos Dados do Rateio de Crédito',
        '34' => 'Confirmação do Cancelamento dos Dados do Rateio de Crédito',
        '35' => 'Confirmação do Desagendamento do Débito Automático',
        '36' => 'Confirmação de envio de e-mail/SMS',
        '37' => 'Envio de e-mail/SMS rejeitado',
        '38' => 'Confirmação de alteração do Prazo Limite de Recebimento',
        '39' => 'Confirmação de Dispensa de Prazo Limite de Recebimento',
        '40' => 'Confirmação da alteração do número do título dado pelo cedente',
        '41' => 'Confirmação da alteração do número controle do Participante',
        '42' => 'Confirmação da alteração dos dados do Sacado',
        '43' => 'Confirmação da alteração dos dados do Sacador/Avalista',
        '44' => 'Título pago com cheque devolvido',
        '45' => 'Título pago com cheque compensado',
        '46' => 'Instrução para cancelar protesto confirmada',
        '47' => 'Instrução para protesto para fins falimentares confirmada',
        '48' => 'Confirmação de instrução de transferência de carteira/modalidade de cobrança',
        '49' => 'Alteração de contrato de cobrança',
        '50' => 'Título pago com cheque pendente de liquidação',
        '51' => 'Título DDA reconhecido pelo sacado',
        '52' => 'Título DDA não reconhecido pelo sacado',
        '53' => 'Título DDA recusado pela CIP',
        '54' => 'Confirmação da Instrução de Baixa de Título Negativado sem Protesto',
        '55' => 'Confirmação de Pedido de Dispensa de Multa',
        '56' => 'Confirmação do Pedido de Cobrança de Multa',
        '57' => 'Confirmação do Pedido de Alteração de Cobrança de Juros',
        '58' => 'Confirmação do Pedido de Alteração do Valor/Data de Desconto',
        '59' => 'Confirmação do Pedido de Alteração do Cedente do Título',
        '60' => 'Confirmação do Pedido de Dispensa de Juros de Mora',
    ];

    /**
     * Array com as possiveis rejeicoes do banco.
     *
     * @var array
     */
    private $rejeicoes = [
        '1'  => 'Código do Banco Inválido',
        '2'  => 'Código do Registro Detalhe Inválido',
        '3'  => 'Código do Segmento Inválido',
        '4'  => 'Código de Movimento Não Permitido para Carteira',
        '5'  => 'Código de Movimento Inválido',
        '6'  => 'Tipo/Número de Inscrição do Cedente Inválidos',
        '7'  => 'Agência/Conta/DV Inválido',
        '8'  => 'Nosso Número Inválido',
        '9'  => 'Nosso Número Duplicado',
        '10' => 'Carteira Inválida',
        '11' => 'Forma de Cadastramento do Título Inválido',
        '12' => 'Tipo de Documento Inválido',
        '13' => 'Identificação de Emissão do Bloqueto Inválida',
        '14' => 'Identificação da Distribuição do Bloqueto Inválida',
        '15' => 'Características da Cobrança Incompatíveis',
        '16' => 'Data de Vencimento Inválida',
        '17' => 'Data de Vencimento Anterior a Data de Emissão',
        '18' => 'Vencimento Fora do Prazo de Operação',
        '19' => 'Título a Cargo de Bancos Correspondentes com Vencimento Inferior a XX Dias',
        '20' => 'Valor do Título Inválido',
        '21' => 'Espécie do Título Inválida',
        '22' => 'Espécie do Título Não Permitida para a Carteira',
        '23' => 'Aceite Inválido',
        '24' => 'Data de Emissão Inválida',
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
        '43' => 'Prazo para Baixa/Devolução Inválida',
        '44' => 'Código da Moeda Inválido',
        '45' => 'Nome do Sacado Não Informado',
        '46' => 'Tipo/Número de Inscrição do Sacado Inválido',
        '47' => 'Endereço do Sacado Não Informado',
        '48' => 'CEP Inválido',
        '49' => 'CEP Sem Praça de Cobrança (Não Localizado)',
        '50' => 'CEP Referente a um Banco Correspondente',
        '51' => 'CEP imcompatível com a Unidade da Federação',
        '52' => 'Registro de Título já liquidado Cart. 17',
        '53' => 'Tipo/Número de Inscrição do Sacador/Avalista Inválidos',
        '54' => 'Sacador/Avalista Não Informado',
        '55' => 'Nosso Número no Banco Correspondente Não Informado',
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
        '67' => 'Dados para Débito incompatível com a Identificação da Emissão do Bloqueto',
        '68' => 'Débito Automático Agendado',
        '69' => 'Débito Não Agendado - Erro nos Dados da Remessa',
        '70' => 'Débito Não Agendado - Sacado Não Consta do Cadastro de Autorizante',
        '71' => 'Débito Não Agendado - Cedente Não Autorizado pelo Sacado',
        '72' => 'Débito Não Agendado - Cedente Não Participa da Modalidade Débito Automático',
        '73' => 'Débito Não Agendado - Código de Moeda Diferente de Reao (R$)',
        '74' => 'Débito Não Agendado - Data Vencimento Inválida',
        '75' => 'Débito Não Agendado, Conforme seu Pedido, Título Não Registrado',
        '76' => 'Débito Não Agendado, Tipo/Num. Inscrição do Debitado, Inválido',
        '77' => 'Transferência para Desconto Não Permitida para a Cateira do Título',
        '78' => 'Data Inferior ou Igual ao Vencimento para Débito Automático',
        '79' => 'Data Juros de Mora Inválido',
        '80' => 'Data do Desconto Inválida',
        '81' => 'Tentativas de Débito Esgotadas - Baixado',
        '82' => 'Tentativas de Débito Esgotadas - Pendente',
        '83' => 'Limite Excedido',
        '84' => 'Número Autorização Inexistente',
        '85' => 'Título com Pagamento Vinculado',
        '86' => 'Seu Número Inválido',
        '87' => 'e-mail/SMS enviado',
        '88' => 'e-mail Lido',
        '89' => 'e-mail/SMS devolvido - endereço de e-mail ou número do celular incorreto',
        '90' => 'e-mail devolvido - caixa postal cheia',
        '91' => 'e-mail/número do celular do sacado não informado',
        '92' => 'Sacado optante por Bloqueto Eletrônico - e-mail não enviado',
        '93' => 'Código para emissão de bloqueto não permite envio de e-mail',
        '94' => 'Código da Carteira inválido para envio e-mail',
        '95' => 'Contrato não permite o envio de e-mail',
        '96' => 'Número de contrato inválido',
        '97' => 'Rejeição da alteração do prazo limite de recebimento',
        '98' => 'Rejeição de dispensa de prazo limite de recebimento',
        '99' => 'Rejeição da alteração do número do título dado pelo cedente',
        'A1' => 'Rejeição da alteração do número controle do participante',
        'A2' => 'Rejeição da alteração dos dados do sacado',
        'A3' => 'Rejeição da alteração dos dados do sacador/avalista',
        'A4' => 'Sacado DDA',
        'A5' => 'Registro Rejeitado - Título já Liquidado',
        'A6' => 'Código do Convenente Inválido ou Encerrado',
        'A7' => 'Título se já encontra na situação Pretendida',
        'A8' => 'Valor do Abatimento inválido para cancelamento',
        'A9' => 'Não autoriza pagamento parcial',
        'B1' => 'Autoriza recebimento parcial',
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
     * @throws \Exception
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
     * @throws \Exception
     */
    protected function processarDetalhe(array $detalhe)
    {
        if ($this->count() == 1) {
            $this->getHeader()
                ->setAgencia($this->rem(25, 29, $detalhe))
                ->setConta($this->rem(30, 36, $detalhe))
                ->setContaDv($this->rem(37, 37, $detalhe));
        }

        $d = $this->detalheAtual();
        $d->setCarteira($this->rem(108, 108, $detalhe))
            ->setNossoNumero($this->rem(71, 82, $detalhe))
            //71 - 81 Identificação do Título no Banco / 82 Digito N/N
            ->setNumeroDocumento($this->rem(117, 126, $detalhe))
            ->setNumeroControle($this->rem(38, 62, $detalhe))
            ->setOcorrencia($this->rem(109, 110, $detalhe))
            ->setOcorrenciaDescricao(Arr::get($this->ocorrencias, $d->getOcorrencia(), 'Desconhecida'))
            ->setDataOcorrencia($this->rem(111, 116, $detalhe))
            ->setDataVencimento($this->rem(147, 152, $detalhe))
            ->setDataCredito($this->rem(296, 301, $detalhe))
            ->setValor(Util::nFloat($this->rem(153, 165, $detalhe) / 100, 2, false))
            ->setValorTarifa(Util::nFloat($this->rem(176, 188, $detalhe) / 100, 2, false))
            ->setValorIOF(Util::nFloat($this->rem(215, 227, $detalhe) / 100, 2, false))
            ->setValorAbatimento(Util::nFloat($this->rem(228, 240, $detalhe) / 100, 2, false))
            ->setValorDesconto(Util::nFloat($this->rem(241, 253, $detalhe) / 100, 2, false))
            ->setValorRecebido(Util::nFloat($this->rem(254, 266, $detalhe) / 100, 2, false))
            ->setValorMora(Util::nFloat($this->rem(267, 279, $detalhe) / 100, 2, false))
            ->setValorMulta(Util::nFloat($this->rem(280, 292, $detalhe) / 100, 2, false)); //outros creditos

        $msgAdicional = str_split(sprintf('%08s', $this->rem(319, 328, $detalhe)), 2) + array_fill(0, 5, '');
        if ($d->hasOcorrencia('06', '15', '17')) {
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
        } elseif ($d->hasOcorrencia('14')) {
            $this->totais['alterados']++;
            $d->setOcorrenciaTipo($d::OCORRENCIA_ALTERACAO);
        } elseif ($d->hasOcorrencia('03', '24', '27', '30', '32')) {
            $this->totais['erros']++;
            $error = Util::appendStrings(
                Arr::get($this->rejeicoes, $msgAdicional[0], ''),
                Arr::get($this->rejeicoes, $msgAdicional[1], ''),
                Arr::get($this->rejeicoes, $msgAdicional[2], ''),
                Arr::get($this->rejeicoes, $msgAdicional[3], ''),
                Arr::get($this->rejeicoes, $msgAdicional[4], '')
            );
            if ($d->hasOcorrencia('03')) {
               if (isset($this->rejeicoes[$this->rem(319, 320, $detalhe)])){
                  $d->setRejeicao($this->rejeicoes[$this->rem(319, 320, $detalhe)]);
               }
            }
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
     * @throws \Exception
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
