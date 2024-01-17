<?php

namespace Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab400\Banco;

use Illuminate\Support\Arr;
use Eduardokum\LaravelBoleto\Util;
use Eduardokum\LaravelBoleto\Contracts\Cnab\RetornoCnab400;
use Eduardokum\LaravelBoleto\Exception\ValidationException;
use Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab400\AbstractRetorno;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;

class Bradesco extends AbstractRetorno implements RetornoCnab400
{
    /**
     * Código do banco
     *
     * @var string
     */
    protected $codigoBanco = BoletoContract::COD_BANCO_BRADESCO;

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
        '11' => 'Em Ser - Arquivo de Títulos pendentes (sem motivo)',
        '12' => 'Abatimento Concedido (sem motivo)',
        '13' => 'Abatimento Cancelado (sem motivo)',
        '14' => 'Vencimento Alterado (sem motivo)',
        '15' => 'Liquidação em Cartório (sem motivo)',
        '16' => 'Título Pago em Cheque - Vinculado',
        '17' => 'Liquidação após baixa ou Título não registrado (sem motivo)',
        '18' => 'Acerto de Depositária (sem motivo)',
        '19' => 'Confirmação Receb. Inst. de Protesto',
        '20' => 'Confirmação Recebimento Instrução Sustação de Protesto (sem motivo)',
        '21' => 'Acerto do Controle do Participante (sem motivo)',
        '22' => 'Título Com Pagamento Cancelado',
        '23' => 'Entrada do Título em Cartório (sem motivo)',
        '24' => 'Entrada rejeitada por CEP Irregular',
        '27' => 'Baixa Rejeitada',
        '28' => 'Débito de tarifas/custas',
        '30' => 'Alteração de Outros Dados Rejeitados',
        '32' => 'Instrução Rejeitada',
        '33' => 'Confirmação Pedido Alteração Outros Dados (sem motivo)',
        '34' => 'Retirado de Cartório e Manutenção Carteira (sem motivo)',
        '35' => 'Desagendamento do débito automático',
        '40' => 'Estorno de pagamento (Novo)',
        '55' => 'Sustado judicial (Novo)',
        '68' => 'Acerto dos dados do rateio de Crédito',
        '69' => 'Cancelamento dos dados do rateio',
    ];

    /**
     * Array com as possiveis rejeicoes do banco.
     *
     * @var array
     */
    private $rejeicoes = [
        '02' => 'Código do registro detalhe inválido',
        '03' => 'Código da ocorrência inválida',
        '04' => 'Código de ocorrência não permitida para a carteira',
        '05' => 'Código de ocorrência não numérico',
        '07' => 'Agência/conta/Digito - |Inválido',
        '08' => 'Nosso número inválido',
        '09' => 'Nosso número duplicado',
        '10' => 'Carteira inválida',
        '13' => 'Identificação da emissão do bloqueto inválida',
        '16' => 'Data de vencimento inválida',
        '18' => 'Vencimento fora do prazo de operação',
        '20' => 'Valor do Título inválido',
        '21' => 'Espécie do Título inválida',
        '22' => 'Espécie não permitida para a carteira',
        '24' => 'Data de emissão inválida',
        '28' => 'Código do desconto inválido',
        '38' => 'Prazo para protesto/ Negativação inválido (ALTERADO)',
        '44' => 'Agência Beneficiário não prevista',
        '45' => 'Nome do pagador não informado',
        '46' => 'Tipo/número de inscrição do pagador inválidos',
        '47' => 'Endereço do pagador não informado',
        '48' => 'CEP Inválido',
        '50' => 'CEP irregular - Banco Correspondente',
        '63' => 'Entrada para Título já cadastrado',
        '65' => 'Limite excedido',
        '66' => 'Número autorização inexistente',
        '68' => 'Débito não agendado - erro nos dados de remessa',
        '69' => 'Débito não agendado - Pagador não consta no cadastro de autorizante',
        '70' => 'Débito não agendado - Beneficiário não autorizado pelo Pagador',
        '71' => 'Débito não agendado - Beneficiário não participa do débito Automático',
        '72' => 'Débito não agendado - Código de moeda diferente de R$',
        '73' => 'Débito não agendado - Data de vencimento inválida',
        '74' => 'Débito não agendado - Conforme seu pedido, Título não registrado',
        '75' => 'Débito não agendado – Tipo de número de inscrição do debitado inválido',
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
        if ($this->count() == 1) {
            $this->getHeader()
                ->setAgencia($this->rem(25, 29, $detalhe))
                ->setConta($this->rem(30, 36, $detalhe))
                ->setContaDv($this->rem(37, 37, $detalhe));
        }

        if ($this->rem(1, 1, $detalhe) == 4) {
            return $this->processarPix($detalhe);
        }

        $d = $this->detalheAtual();
        $d->setCarteira($this->rem(108, 108, $detalhe))
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
            ->setValorIOF(Util::nFloat($this->rem(215, 227, $detalhe) / 100, 2, false))
            ->setValorAbatimento(Util::nFloat($this->rem(228, 240, $detalhe) / 100, 2, false))
            ->setValorDesconto(Util::nFloat($this->rem(241, 253, $detalhe) / 100, 2, false))
            ->setValorRecebido(Util::nFloat($this->rem(254, 266, $detalhe) / 100, 2, false))
            ->setValorMora(Util::nFloat($this->rem(267, 279, $detalhe) / 100, 2, false))
            ->setValorMulta(Util::nFloat($this->rem(280, 292, $detalhe) / 100, 2, false));

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
            $error = Util::appendStrings(Arr::get($this->rejeicoes, $msgAdicional[0], ''), Arr::get($this->rejeicoes, $msgAdicional[1], ''), Arr::get($this->rejeicoes, $msgAdicional[2], ''), Arr::get($this->rejeicoes, $msgAdicional[3], ''), Arr::get($this->rejeicoes, $msgAdicional[4], ''));
            if ($d->hasOcorrencia('03')) {
                if (isset($this->rejeicoes[$this->rem(319, 320, $detalhe)])) {
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

    /**
     * @param array $detalhe
     * @return bool
     * @throws ValidationException
     */
    private function processarPix(array $detalhe)
    {
        $d = $this->getDetalhe($this->increment - 1);
        $d->setPixLocation($this->rem(29, 105, $detalhe));
        $d->setId($this->rem(106, 140, $detalhe));

        return false;
    }
}
