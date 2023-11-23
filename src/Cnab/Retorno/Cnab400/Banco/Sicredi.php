<?php

namespace Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab400\Banco;

use Illuminate\Support\Arr;
use Eduardokum\LaravelBoleto\Util;
use Eduardokum\LaravelBoleto\Contracts\Cnab\RetornoCnab400;
use Eduardokum\LaravelBoleto\Exception\ValidationException;
use Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab400\AbstractRetorno;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;

class Sicredi extends AbstractRetorno implements RetornoCnab400
{
    /**
     * Código do banco
     *
     * @var string
     */
    protected $codigoBanco = BoletoContract::COD_BANCO_SICREDI;

    /**
     * Array com as ocorrencias do banco;
     *
     * @var array
     */
    private $ocorrencias = [
        '02' => 'Entrada confirmada',
        '03' => 'Entrada rejeitada',
        '06' => 'Liquidação normal',
        '09' => 'Baixado automaticamente via arquivo',
        '10' => 'Baixado conforme instruções da cooperativa de crédito',
        '12' => 'Abatimento concedido',
        '13' => 'Abatimento cancelado',
        '14' => 'Vencimento alterado',
        '15' => 'Liquidação em cartório',
        '17' => 'Liquidação após baixa',
        '19' => 'Confirmação de recebimento de instrução de protesto',
        '20' => 'Confirmação de recebimento de instrução de sustação de protesto',
        '23' => 'Entrada de título em cartório',
        '24' => 'Entrada rejeitada por CEP irregular',
        '27' => 'Baixa rejeitada',
        '28' => 'Tarifa',
        '29' => 'Rejeição do pagador',
        '30' => 'Alteração rejeitada',
        '32' => 'Instrução rejeitada',
        '33' => 'Confirmação de pedido de alteração de outros dados',
        '34' => 'Retirado de cartório e manutenção em carteira',
        '35' => 'Aceite do pagador',
    ];

    /**
     * Array com as possiveis rejeicoes do banco.
     *
     * @var array
     */
    private $rejeicoes = [
        '0A' => 'Aceito',
        '0D' => 'Desprezado',
        '01' => 'Código do banco inválido',
        '02' => 'Código do registro detalhe inválido',
        '03' => 'Código da ocorrência inválido',
        '04' => 'Código de ocorrência não permitida para a carteira',
        '05' => 'Código de ocorrência não numérico',
        '07' => 'Cooperativa/agência/conta/dígito inválidos',
        '08' => 'Nosso número inválido',
        '09' => 'Nosso número duplicado',
        '10' => 'Carteira inválida',
        '15' => 'Cooperativa/carteira/agência/conta/nosso número inválidos',
        '16' => 'Data de vencimento inválida',
        '17' => 'Data de vencimento anterior à data de emissão',
        '18' => 'Vencimento fora do prazo de operação',
        '20' => 'Valor do título inválido',
        '21' => 'Espécie do título inválida',
        '22' => 'Espécie não permitida para a carteira',
        '24' => 'Data de emissão inválida',
        '29' => 'Valor do desconto maior/igual ao valor do título',
        '31' => 'Concessão de desconto - existe desconto anterior',
        '33' => 'Valor do abatimento inválido',
        '34' => 'Valor do abatimento maior/igual ao valor do título',
        '36' => 'Concessão de abatimento - existe abatimento anterior',
        '38' => 'Prazo para protesto inválido',
        '39' => 'Pedido para protesto não permitido para o título',
        '40' => 'Título com ordem de protesto emitida',
        '41' => 'Pedido cancelamento/sustação sem instrução de protesto',
        '44' => 'Cooperativa de crédito/agência beneficiária não prevista',
        '45' => 'Nome do pagador inválido',
        '46' => 'Tipo/número de inscrição do pagador inválidos',
        '47' => 'Endereço do pagador não informado',
        '48' => 'CEP irregular',
        '49' => 'Número de Inscrição do pagador/avalista inválido',
        '50' => 'Pagador/avalista não informado',
        '60' => 'Movimento para título não cadastrado',
        '63' => 'Entrada para título já cadastrado',
        'A1' => 'Praça do pagador não cadastrada.',
        'A2' => 'Tipo de cobrança do título divergente com a praça do pagador.',
        'A3' => 'Cooperativa/agência depositária divergente: atualiza o cadastro de praças da Coop./agência beneficiária',
        'A4' => 'Beneficiário não cadastrado ou possui CGC/CIC inválido',
        'A5' => 'Pagador não cadastrado',
        'A6' => 'Data da instrução/ocorrência inválida',
        'A7' => 'Ocorrência não pode ser comandada',
        'B4' => 'Tipo de moeda inválido',
        'B5' => 'Tipo de desconto/juros inválido',
        'B6' => 'Mensagem padrão não cadastrada',
        'B7' => 'Seu número inválido',
        'B8' => 'Percentual de multa inválido',
        'B9' => 'Valor ou percentual de juros inválido',
        'C1' => 'Data limite para concessão de desconto inválida',
        'C2' => 'Aceite do título inválido',
        'C3' => 'Campo alterado na instrução “31 – alteração de outros dados” inválido',
        'C4' => 'Título ainda não foi confirmado pela centralizadora',
        'C5' => 'Título rejeitado pela centralizadora',
        'C6' => 'Título já liquidado',
        'C7' => 'Título já baixado',
        'C8' => 'Existe mesma instrução pendente de confirmação para este título',
        'C9' => 'Instrução prévia de concessão de abatimento não existe ou não confirmada',
        'D1' => 'Título dentro do prazo de vencimento (em dia)',
        'D2' => 'Espécie de documento não permite protesto de título',
        'D3' => 'Título possui instrução de baixa pendente de confirmação',
        'D4' => 'Quantidade de mensagens padrão excede o limite permitido',
        'D5' => 'Quantidade inválida no pedido de boletos pré-impressos da cobrança sem registro',
        'D6' => 'Tipo de impressão inválida para cobrança sem registro',
        'D7' => 'Cidade ou Estado do pagador não informado',
        'D8' => 'Seqüência para composição do nosso número do ano atual esgotada',
        'D9' => 'Registro mensagem para título não cadastrado',
        'E2' => 'Registro complementar ao cadastro do título da cobrança com e sem registro não cadastrado',
        'E3' => 'Tipo de postagem inválido, diferente de S, N e branco',
        'E4' => 'Pedido de boletos pré-impressos',
        'E5' => 'Confirmação/rejeição para pedidos de boletos não cadastrado',
        'E6' => 'Pagador/avalista não cadastrado',
        'E7' => 'Informação para atualização do valor do título para protesto inválido',
        'E8' => 'Tipo de impressão inválido, diferente de A, B e branco',
        'E9' => 'Código do pagador do título divergente com o código da cooperativa de crédito',
        'F1' => 'Liquidado no sistema do cliente',
        'F2' => 'Baixado no sistema do cliente',
        'F3' => 'Instrução inválida, este título está caucionado/descontado',
        'F4' => 'Instrução fixa com caracteres inválidos',
        'F6' => 'Nosso número / número da parcela fora de seqüência – total de parcelas inválido',
        'F7' => 'Falta de comprovante de prestação de serviço',
        'F8' => 'Nome do beneficiário incompleto / incorreto.',
        'F9' => 'CNPJ / CPF incompatível com o nome do pagador / Sacador Avalista',
        'G1' => 'CNPJ / CPF do pagador Incompatível com a espécie',
        'G2' => 'Título aceito: sem a assinatura do pagador',
        'G3' => 'Título aceito: rasurado ou rasgado',
        'G4' => 'Título aceito: falta título (cooperativa/ag. beneficiária deverá enviá-lo)',
        'G5' => 'Praça de pagamento incompatível com o endereço',
        'G6' => 'Título aceito: sem endosso ou beneficiário irregular',
        'G7' => 'Título aceito: valor por extenso diferente do valor numérico',
        'G8' => 'Saldo maior que o valor do título',
        'G9' => 'Tipo de endosso inválido',
        'H1' => 'Nome do pagador incompleto / Incorreto',
        'H2' => 'Sustação judicial',
        'H3' => 'Pagador não encontrado',
        'H4' => 'Alteração de carteira',
        'H7' => 'Espécie de documento necessita beneficiário ou avalista PJ',
        'H9' => 'Dados do título não conferem com disquete',
        'I1' => 'Pagador e Sacador Avalista são a mesma pessoa',
        'I2' => 'Aguardar um dia útil após o vencimento para protestar',
        'I3' => 'Data do vencimento rasurada',
        'I4' => 'Vencimento – extenso não confere com número',
        'I5' => 'Falta data de vencimento no título',
        'I6' => 'DM/DMI sem comprovante autenticado ou declaração',
        'I7' => 'Comprovante ilegível para conferência e microfilmagem',
        'I8' => 'Nome solicitado não confere com emitente ou pagador',
        'I9' => 'Confirmar se são 2 emitentes. Se sim, indicar os dados dos 2',
        'J1' => 'Endereço do pagador igual ao do pagador ou do portador',
        'J2' => 'Endereço do apresentante incompleto ou não informado',
        'J3' => 'Rua/número inexistente no endereço',
        'J4' => 'Falta endosso do favorecido para o apresentante',
        'J5' => 'Data da emissão rasurada',
        'J6' => 'Falta assinatura do pagador no título',
        'J7' => 'Nome do apresentante não informado/incompleto/incorreto',
        'J8' => 'Erro de preenchimento do titulo',
        'J9' => 'Titulo com direito de regresso vencido',
        'K1' => 'Titulo apresentado em duplicidade',
        'K2' => 'Titulo já protestado',
        'K3' => 'Letra de cambio vencida – falta aceite do pagador',
        'K4' => 'Falta declaração de saldo assinada no título',
        'K5' => 'Contrato de cambio – Falta conta gráfica',
        'K6' => 'Ausência do documento físico',
        'K7' => 'Pagador falecido',
        'K8' => 'Pagador apresentou quitação do título',
        'K9' => 'Título de outra jurisdição territorial',
        'L1' => 'Título com emissão anterior a concordata do pagador',
        'L2' => 'Pagador consta na lista de falência',
        'L3' => 'Apresentante não aceita publicação de edital',
        'L4' => 'Dados do Pagador em Branco ou inválido',
        'L5' => 'Código do Pagador na agência beneficiária está duplicado',
        'M2' => 'Não reconhecimento da dívida pelo pagador',
    ];

    /**
     * Array com as possiveis ocorrências da Tabela de Motivos das Ocorrências para Tarifas - “28 – Tarifas” Maio 2020 v1.6
     *
     * @var array
     */
    private $ocorrenciasTarifas = [
        '03' => 'Tarifa de sustação',
        '04' => 'Tarifa de protesto',
        '08' => 'Tarifa de custas de protesto',
        'A9' => 'Tarifa de manutenção de título vencido',
        'B1' => 'Tarifa de baixa da carteira',
        'B3' => 'Tarifa de registro de entrada do título',
        'F5' => 'Tarifa de entrada na rede Sicredi',
        'S4' => 'Tarifa de Inclusão Negativação',
        'S5' => 'Tarifa de Exclusão Negativação',
    ];

    /**
     * Roda antes dos metodos de processar
     */
    protected function init()
    {
        $this->totais = [
            'valor_recebido' => 0,
            'liquidados'     => 0,
            'entradas'       => 0,
            'baixados'       => 0,
            'protestados'    => 0,
            'erros'          => 0,
            'alterados'      => 0,
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
            ->setConta($this->rem(27, 31, $header))
            ->setCodigoCliente($this->rem(32, 45, $header))
            ->setData($this->rem(95, 102, $header), 'Ymd');

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

        $d->setNossoNumero($this->rem(48, 62, $detalhe))
            ->setNumeroControle($this->rem(117, 126, $detalhe))
            ->setNumeroDocumento($this->rem(117, 126, $detalhe))
            ->setOcorrencia($this->rem(109, 110, $detalhe))
            ->setOcorrenciaDescricao(Arr::get($this->ocorrencias, $d->getOcorrencia(), 'Desconhecida'))
            ->setDataOcorrencia($this->rem(111, 116, $detalhe))
            ->setDataVencimento($this->rem(147, 152, $detalhe))
            ->setValor(Util::nFloat($this->rem(153, 165, $detalhe) / 100, 2, false))
            ->setValorTarifa(Util::nFloat($this->rem(176, 188, $detalhe) / 100, 2, false))
            ->setValorOutrasDespesas(Util::nFloat($this->rem(189, 201, $detalhe) / 100, 2, false))
            ->setValorAbatimento(Util::nFloat($this->rem(228, 240, $detalhe) / 100, 2, false))
            ->setValorDesconto(Util::nFloat($this->rem(241, 253, $detalhe) / 100, 2, false))
            ->setValorRecebido(Util::nFloat($this->rem(254, 266, $detalhe) / 100, 2, false))
            ->setValorMora(Util::nFloat($this->rem(267, 279, $detalhe) / 100, 2, false))
            ->setValorMulta(Util::nFloat($this->rem(280, 292, $detalhe) / 100, 2, false))
            ->setDataCredito($this->rem(329, 336, $detalhe), 'Ymd');

        if ($d->hasOcorrencia('06', '15', '16')) {
            $this->totais['valor_recebido'] += $d->getValorRecebido();
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
        } elseif ($d->hasOcorrencia('33')) {
            $this->totais['alterados']++;
            $d->setOcorrenciaTipo($d::OCORRENCIA_ALTERACAO);
        } elseif ($d->hasOcorrencia('03', '24', '27', '30', '32')) {
            $this->totais['erros']++;
            if ($d->hasOcorrencia('03')) {
                if (isset($this->rejeicoes[$this->rem(319, 320, $detalhe)])) {
                    $d->setRejeicao($this->rejeicoes[$this->rem(319, 320, $detalhe)]);
                }
            }
            $d->setOcorrenciaTipo($d::OCORRENCIA_ERRO);
        } else {
            $d->setOcorrenciaTipo($d::OCORRENCIA_OUTROS);
        }

        $msgAdicional = sprintf('%010s', $this->rem(319, 328, $detalhe));
        $msgAdicRetorno = str_split($msgAdicional, 2) + array_fill(0, 5, '') + array_fill(0, 5, '');

        if (trim($msgAdicional, '0') != '') {
            //Caso seja detalhe de Tarifa ('28' => 'Tarifa') Buscar as mensagens especificas e não classificar como erro
            if ($d->hasOcorrencia('28')) {
                $motivo = [];
                $motivo[] = Arr::get($this->ocorrenciasTarifas, $msgAdicRetorno[0], '');
                $motivo[] = Arr::get($this->ocorrenciasTarifas, $msgAdicRetorno[1], '');
                $motivo[] = Arr::get($this->ocorrenciasTarifas, $msgAdicRetorno[2], '');
                $motivo[] = Arr::get($this->ocorrenciasTarifas, $msgAdicRetorno[3], '');
                $motivo[] = Arr::get($this->ocorrenciasTarifas, $msgAdicRetorno[4], '');

                $motivo = array_filter($motivo);

                if (count($motivo) > 0) {
                    $d->setRejeicao(implode(PHP_EOL, $motivo));
                }
            } elseif ($d->getOcorrenciaTipo() != $d::OCORRENCIA_ERRO) {
                $ocorrencia = Util::appendStrings($d->getOcorrenciaDescricao(), Arr::get($this->rejeicoes, $msgAdicRetorno[0], ''), Arr::get($this->rejeicoes, $msgAdicRetorno[1], ''), Arr::get($this->rejeicoes, $msgAdicRetorno[2], ''), Arr::get($this->rejeicoes, $msgAdicRetorno[3], ''), Arr::get($this->rejeicoes, $msgAdicRetorno[4], ''));
                $d->setOcorrenciaDescricao($ocorrencia);
            } else {
                $error = [];
                $error[] = Arr::get($this->rejeicoes, $msgAdicRetorno[0], '');
                $error[] = Arr::get($this->rejeicoes, $msgAdicRetorno[1], '');
                $error[] = Arr::get($this->rejeicoes, $msgAdicRetorno[2], '');
                $error[] = Arr::get($this->rejeicoes, $msgAdicRetorno[3], '');
                $error[] = Arr::get($this->rejeicoes, $msgAdicRetorno[4], '');

                $error = array_filter($error);

                if (count($error) > 0) {
                    $d->setError(implode(PHP_EOL, $error));
                }
            }
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
