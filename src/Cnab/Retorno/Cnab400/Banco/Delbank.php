<?php

namespace Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab400\Banco;

use Illuminate\Support\Arr;
use Eduardokum\LaravelBoleto\Util;
use Eduardokum\LaravelBoleto\Contracts\Cnab\RetornoCnab400;
use Eduardokum\LaravelBoleto\Exception\ValidationException;
use Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab400\AbstractRetorno;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;

class Delbank extends AbstractRetorno implements RetornoCnab400
{
    /**
     * Código do banco
     *
     * @var string
     */
    protected $codigoBanco = BoletoContract::COD_BANCO_DELCRED;

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
        '99' => 'Tarifa Sobre Instruções de Protesto/Sustação – Mês Anterio',
    ];

    /**
     * Array com as possiveis rejeicoes do banco.
     *
     * @var array
     */
    private $rejeicoes = [
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
        '36' => 'Valor de permanência (mora) não numérico ',
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

        $d = $this->detalheAtual();
        $d->setCarteira($this->rem(83, 85, $detalhe))
            ->setNossoNumero($this->rem(63, 73, $detalhe))
            ->setNumeroDocumento($this->rem(117, 126, $detalhe))
            ->setNumeroControle($this->rem(38, 62, $detalhe))
            ->setOcorrencia($this->rem(109, 110, $detalhe))
            ->setOcorrenciaDescricao(Arr::get($this->ocorrencias, $d->getOcorrencia(), 'Desconhecida'))
            ->setDataOcorrencia($this->rem(111, 116, $detalhe))
            ->setDataVencimento($this->rem(147, 152, $detalhe))
            ->setDataCredito($this->rem(386, 391, $detalhe))
            ->setValor(Util::nFloat($this->rem(153, 165, $detalhe) / 100, 2, false))
            ->setValorTarifa(Util::nFloat($this->rem(176, 188, $detalhe) / 100, 2, false))
            ->setValorIOF(Util::nFloat($this->rem(215, 227, $detalhe) / 100, 2, false))
            ->setValorAbatimento(Util::nFloat($this->rem(228, 240, $detalhe) / 100, 2, false))
            ->setValorDesconto(Util::nFloat($this->rem(241, 253, $detalhe) / 100, 2, false))
            ->setValorRecebido(Util::nFloat($this->rem(254, 266, $detalhe) / 100, 2, false))
            ->setValorMora(Util::nFloat($this->rem(267, 279, $detalhe) / 100, 2, false))
            ->setValorMulta(Util::nFloat($this->rem(280, 292, $detalhe) / 100, 2, false));

        $msgAdicional = str_split(sprintf('%08s', $this->rem(319, 328, $detalhe)), 2) + array_fill(0, 5, '');
        if ($d->hasOcorrencia('06', '08')) {
            $this->totais['liquidados']++;
            $d->setOcorrenciaTipo($d::OCORRENCIA_LIQUIDADA);
        } elseif ($d->hasOcorrencia('02')) {
            $this->totais['entradas']++;
            $d->setOcorrenciaTipo($d::OCORRENCIA_ENTRADA);
        } elseif ($d->hasOcorrencia('09', '10', '43')) {
            $this->totais['baixados']++;
            $d->setOcorrenciaTipo($d::OCORRENCIA_BAIXADA);
        } elseif ($d->hasOcorrencia('19')) {
            $this->totais['protestados']++;
            $d->setOcorrenciaTipo($d::OCORRENCIA_PROTESTADA);
        } elseif ($d->hasOcorrencia('05', '14', '22')) {
            $this->totais['alterados']++;
            $d->setOcorrenciaTipo($d::OCORRENCIA_ALTERACAO);
        } elseif ($d->hasOcorrencia('03', '15', '16')) {
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
}
