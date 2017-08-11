<?php
namespace Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab400\Banco;

use Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab400\AbstractRetorno;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;
use Eduardokum\LaravelBoleto\Contracts\Cnab\RetornoCnab400;
use Eduardokum\LaravelBoleto\Util;

class Bancoob extends AbstractRetorno implements RetornoCnab400
{
    /**
     * Código do banco
     *
     * @var string
     */
    protected $codigoBanco = BoletoContract::COD_BANCO_BANCOOB;

    /**
     * Array com as ocorrencias do banco;
     *
     * @var array
     */

    private $ocorrencias = [
        '02' => 'Confirmação Entrada Título',
        '05' => 'Liquidação Sem Registro: Identifica a liquidação de título da modalidade ""SEM REGISTRO""',
        '06' => 'Liquidação Normal: Identificar a liquidação de título de modalidade ""REGISTRADA"", com exceção dos títulos que forem liquidados em cartório (Cód. de movimento 15=Liquidação em Cartório)',
        '09' => 'Baixa de Titulo: Identificar as baixas de títulos, com exceção da baixa realizada com o cód. de movimento 10 (Baixa - Pedido Beneficiário)',
        '10' => 'Baixa Solicitada (Baixa - Pedido Beneficiário): Identificar as baixas de títulos comandadas a pedido do Beneficiário',
        '11' => 'Títulos em Ser: Identifica os títulos em carteira, que estiverem com a situação ""em abarto"" (vencidos e a vencer).',
        '14' => 'Alteração de Vencimento',
        '15' => 'Liquidação em Cartório: Identifica as liquidações dos títulos ocorridas em cartórios de protesto',
        '23' => 'Encaminhado a Protesto: Identifica o recebimento da instrução de protesto',
        '27' => 'Confirmação Alteração Dados.',
        '48' => 'Confirmação de instrução de transferência de carteira/modalidade de cobrança"'
    ];

    /**
     * Roda antes dos metodos de processar
     */
    protected function init()
    {
        $this->totais = [
            'liquidados' => 0,
            'entradas' => 0,
            'baixados' => 0,
            'protestados' => 0,
            'alterados' => 0,
        ];
    }

    protected function processarHeader(array $header)
    {
        $this->getHeader()
            ->setOperacaoCodigo($this->rem(2, 2, $header))
            ->setOperacao($this->rem(3, 9, $header))
            ->setServicoCodigo($this->rem(10, 11, $header))
            ->setServico($this->rem(12, 19, $header))
            ->setAgencia($this->rem(27, 30, $header))
            ->setAgenciaDv($this->rem(31, 31, $header))
            ->setConta($this->rem(32, 39, $header))
            ->setContaDv($this->rem(40, 40, $header))
            ->setConvenio($this->rem(41, 46, $header))
            ->setData($this->rem(95, 100, $header));

        return true;
    }

    protected function processarDetalhe(array $detalhe)
    {
        $d = $this->detalheAtual();

        $d->setNossoNumero($this->rem(63, 73, $detalhe))
            ->setNumeroDocumento($this->rem(117, 126, $detalhe))
            ->setNumeroControle($this->rem(38, 62, $detalhe))
            ->setOcorrencia($this->rem(109, 110, $detalhe))
            ->setOcorrenciaDescricao(array_get($this->ocorrencias, $d->getOcorrencia(), 'Desconhecida'))
            ->setDataOcorrencia($this->rem(111, 116, $detalhe))
            ->setDataVencimento($this->rem(147, 152, $detalhe))
            ->setDataCredito($this->rem(176, 181, $detalhe))
            ->setValor(Util::nFloat($this->rem(153, 165, $detalhe)/100, 2, false))
            ->setValorTarifa(Util::nFloat($this->rem(182, 188, $detalhe)/100, 2, false))
            ->setValorIOF(Util::nFloat($this->rem(215, 227, $detalhe)/100, 2, false))
            ->setValorAbatimento(Util::nFloat($this->rem(228, 240, $detalhe)/100, 2, false))
            ->setValorDesconto(Util::nFloat($this->rem(241, 253, $detalhe)/100, 2, false))
            ->setValorRecebido(Util::nFloat($this->rem(254, 266, $detalhe)/100, 2, false))
            ->setValorMora(Util::nFloat($this->rem(267, 279, $detalhe)/100, 2, false))
            ->setValorMulta(Util::nFloat($this->rem(280, 292, $detalhe)/100, 2, false));

        if ($d->hasOcorrencia('05', '06')) {
            $this->totais['liquidados']++;
            $d->setOcorrenciaTipo($d::OCORRENCIA_LIQUIDADA);
        } elseif ($d->hasOcorrencia('02')) {
            $this->totais['entradas']++;
            $d->setOcorrenciaTipo($d::OCORRENCIA_ENTRADA);
        } elseif ($d->hasOcorrencia('09')) {
            $this->totais['baixados']++;
            $d->setOcorrenciaTipo($d::OCORRENCIA_BAIXADA);
        } elseif ($d->hasOcorrencia('23')) {
            $this->totais['protestados']++;
            $d->setOcorrenciaTipo($d::OCORRENCIA_PROTESTADA);
        } elseif ($d->hasOcorrencia('14')) {
            $this->totais['alterados']++;
            $d->setOcorrenciaTipo($d::OCORRENCIA_ALTERACAO);
        } else {
            $d->setOcorrenciaTipo($d::OCORRENCIA_OUTROS);
        }

        return true;
    }

    protected function processarTrailer(array $trailer)
    {
        $this->getTrailer()
            ->setQuantidadeTitulos((int) $this->rem(164, 171, $trailer))
            ->setQuantidadeEntradas((int) $this->totais['entradas'])
            ->setQuantidadeLiquidados((int) $this->totais['liquidados'])
            ->setQuantidadeBaixados((int) $this->totais['baixados'])
            ->setQuantidadeAlterados((int) $this->totais['alterados']);

        return true;
    }
}
