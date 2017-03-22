<?php
namespace Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab400\Banco;

use Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab400\AbstractRetorno;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;
use Eduardokum\LaravelBoleto\Contracts\Cnab\RetornoCnab400;
use Eduardokum\LaravelBoleto\Util;

class Itau extends AbstractRetorno implements RetornoCnab400
{
    /**
     * Código do banco
     *
     * @var string
     */
    protected $codigoBanco = BoletoContract::COD_BANCO_ITAU;

    /**
     * Array com as ocorrencias do banco;
     *
     * @var array
     */
    private $ocorrencias = [
        '02' => 'Entrada confirmada',
        '03' => 'Entrada rejeitada (nota 20 - tabela 1)',
        '04' => 'Alteração de dados - nova entrada',
        '05' => 'Alteração de dados – baixa',
        '06' => 'Liquidação normal',
        '07' => 'Liquidação parcial – cobrança inteligente (b2b)',
        '08' => 'Liquidação em cartório',
        '09' => 'Baixa simples',
        '10' => 'Baixa por ter sido liquidado',
        '11' => 'Em ser (só no retorno mensal)',
        '12' => 'Abatimento concedido',
        '13' => 'Abatimento cancelado',
        '14' => 'Vencimento alterado',
        '15' => 'Baixas rejeitadas (nota 20 - tabela 4)',
        '16' => 'Instruções rejeitadas (nota 20 - tabela 3)',
        '17' => 'Alteração de dados rejeitados (nota 20 - tabela 2)',
        '18' => 'Cobrança contratual - instruções/alterações rejeitadas/pendentes (nota 20 - tabela 5)',
        '19' => 'Confirma recebimento de instrução de protesto',
        '20' => 'Confirma recebimento de instrução de sustação de protesto /tarifa',
        '21' => 'Confirma recebimento de instrução de não protestar',
        '23' => 'Título enviado a cartório/tarifa',
        '30' => 'Débito mensal de tarifas (para entradas e baixas)',
        '32' => 'Baixa por ter sido protestado',
        '33' => 'Custas de protesto',
        '34' => 'Custas de sustação',
        '35' => 'Custas de cartório distribuidor',
        '36' => 'Custas de edital',
        '37' => 'Tarifa de emissão de boleto/tarifa de envio de duplicata',
        '38' => 'Tarifa de instrução',
        '39' => 'Tarifa de ocorrências',
        '40' => 'Tarifa mensal de emissão de boleto/tarifa mensal de envio de duplicata',
        '41' => 'Débito mensal de tarifas – extrato de posição (b4ep/b4ox)',
        '42' => 'Débito mensal de tarifas – outras instruções',
        '43' => 'Débito mensal de tarifas – manutenção de títulos vencidos',
        '44' => 'Débito mensal de tarifas – outras ocorrências',
        '45' => 'Débito mensal de tarifas – protesto',
        '46' => 'Débito mensal de tarifas – sustação de protesto',
        '47' => 'Baixa com transferência para desconto',
        '48' => 'Custas de sustação judicial',
        '51' => 'Tarifa mensal ref a entradas bancos correspondentes na carteira',
        '52' => 'Tarifa mensal baixas na carteira',
        '53' => 'Tarifa mensal baixas em bancos correspondentes na carteira',
        '54' => 'Tarifa mensal de liquidações na carteira',
        '55' => 'Tarifa mensal de liquidações em bancos correspondentes na carteira',
        '56' => 'Custas de irregularidade',
        '57' => 'Instrução cancelada (nota 20 – tabela 8)',
        '59' => 'Baixa por crédito em c/c através do sispag',
        '60' => 'Entrada rejeitada carnê (nota 20 – tabela 1)',
        '61' => 'Tarifa emissão aviso de movimentação de títulos (2154)',
        '62' => 'Débito mensal de tarifa - aviso de movimentação de títulos (2154)',
        '63' => 'Título sustado judicialmente',
        '64' => 'Entrada confirmada com rateio de crédito',
        '69' => 'Cheque devolvido (nota 20 - tabela 9)',
        '71' => 'Entrada registrada, aguardando avaliação',
        '72' => 'Baixa por crédito em c/c através do sispag sem título correspondente',
        '73' => 'Confirmação de entrada na cobrança simples – entrada não aceita na cobrança contratual',
        '76' => 'Cheque compensado',
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
            ->setAgencia($this->rem(27, 30, $header))
            ->setConta($this->rem(33, 37, $header))
            ->setContaDv($this->rem(38, 38, $header))
            ->setData($this->rem(95, 100, $header));

        return true;
    }

    protected function processarDetalhe(array $detalhe)
    {
        $d = $this->detalheAtual();

        $d->setNossoNumero($this->rem(86, 94, $detalhe))
            ->setNumeroDocumento($this->rem(117, 126, $detalhe))
            ->setNumeroControle($this->rem(38, 62, $detalhe))
            ->setOcorrencia($this->rem(109, 110, $detalhe))
            ->setOcorrenciaDescricao(array_get($this->ocorrencias, $d->getOcorrencia(), 'Desconhecida'))
            ->setDataOcorrencia($this->rem(111, 116, $detalhe))
            ->setDataVencimento($this->rem(147, 152, $detalhe))
            ->setDataCredito($this->rem(296, 301, $detalhe))
            ->setValor(Util::nFloat($this->rem(153, 165, $detalhe)/100, 2, false))
            ->setValorTarifa(Util::nFloat($this->rem(176, 188, $detalhe)/100, 2, false))
            ->setValorIOF(Util::nFloat($this->rem(215, 227, $detalhe)/100, 2, false))
            ->setValorAbatimento(Util::nFloat($this->rem(228, 240, $detalhe)/100, 2, false))
            ->setValorDesconto(Util::nFloat($this->rem(241, 253, $detalhe)/100, 2, false))
            ->setValorRecebido(Util::nFloat($this->rem(254, 266, $detalhe)/100, 2, false))
            ->setValorMora(Util::nFloat($this->rem(267, 279, $detalhe)/100, 2, false))
            ->setValorMulta(Util::nFloat($this->rem(280, 292, $detalhe)/100, 2, false));

        if ($d->hasOcorrencia('06', '07', '08', '10', '59')) {
            $this->totais['liquidados']++;
            $d->setOcorrenciaTipo($d::OCORRENCIA_LIQUIDADA);
        } elseif ($d->hasOcorrencia('02', '64', '71', '73')) {
            $this->totais['entradas']++;
            $d->setOcorrenciaTipo($d::OCORRENCIA_ENTRADA);
        } elseif ($d->hasOcorrencia('05', '09', '47', '72')) {
            $this->totais['baixados']++;
            $d->setOcorrenciaTipo($d::OCORRENCIA_BAIXADA);
        } elseif ($d->hasOcorrencia('32')) {
            $this->totais['protestados']++;
            $d->setOcorrenciaTipo($d::OCORRENCIA_PROTESTADA);
        } elseif ($d->hasOcorrencia('14')) {
            $this->totais['alterados']++;
            $d->setOcorrenciaTipo($d::OCORRENCIA_ALTERACAO);
        } elseif ($d->hasOcorrencia('03', '15', '16', '60', '03')) {
            $this->totais['erros']++;
            $d->setError('Consulte seu Internet Banking');
        } else {
            $d->setOcorrenciaTipo($d::OCORRENCIA_OUTROS);
        }

        return true;
    }

    protected function processarTrailer(array $trailer)
    {
        $this->getTrailer()
            ->setQuantidadeTitulos((int) $this->rem(18, 25, $trailer) + (int) $this->rem(58, 65, $trailer) + (int) $this->rem(178, 185, $trailer))
            ->setValorTitulos((float) Util::nFloat($this->rem(221, 234, $trailer)/100, 2, false))
            ->setQuantidadeErros((int) $this->totais['erros'])
            ->setQuantidadeEntradas((int) $this->totais['entradas'])
            ->setQuantidadeLiquidados((int) $this->totais['liquidados'])
            ->setQuantidadeBaixados((int) $this->totais['baixados'])
            ->setQuantidadeAlterados((int) $this->totais['alterados']);

        return true;
    }
}
