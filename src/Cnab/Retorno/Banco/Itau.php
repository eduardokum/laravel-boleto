<?php
/**
 *   Copyright (c) 2016 Eduardo Gusmão
 *
 *   Permission is hereby granted, free of charge, to any person obtaining a
 *   copy of this software and associated documentation files (the "Software"),
 *   to deal in the Software without restriction, including without limitation
 *   the rights to use, copy, modify, merge, publish, distribute, sublicense,
 *   and/or sell copies of the Software, and to permit persons to whom the
 *   Software is furnished to do so, subject to the following conditions:
 *
 *   The above copyright notice and this permission notice shall be included in all
 *   copies or substantial portions of the Software.
 *
 *   THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 *   INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR
 *   PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 *   COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 *   WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR
 *   IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace Eduardokum\LaravelBoleto\Cnab\Retorno\Banco;

use Eduardokum\LaravelBoleto\Cnab\Retorno\AbstractRetorno;
use Eduardokum\LaravelBoleto\Contracts\Cnab\Retorno;
use Eduardokum\LaravelBoleto\Util;

class Itau extends AbstractRetorno implements Retorno
{
    /**
     * Código do banco
     * @var string
     */
    protected $codigoBanco = self::COD_BANCO_ITAU;

    /**
     * Array com as ocorrencias do banco;
     *
     * @var array
     */
    private $ocorrencias = [
        '02' => 'entrada confirmada',
        '03' => 'entrada rejeitada (nota 20 - tabela 1)',
        '04' => 'alteração de dados - nova entrada',
        '05' => 'alteração de dados – baixa',
        '06' => 'liquidação normal',
        '07' => 'liquidação parcial – cobrança inteligente (b2b)',
        '08' => 'liquidação em cartório',
        '09' => 'baixa simples',
        '10' => 'baixa por ter sido liquidado',
        '11' => 'em ser (só no retorno mensal)',
        '12' => 'abatimento concedido',
        '13' => 'abatimento cancelado',
        '14' => 'vencimento alterado',
        '15' => 'baixas rejeitadas (nota 20 - tabela 4)',
        '16' => 'instruções rejeitadas (nota 20 - tabela 3)',
        '17' => 'alteração de dados rejeitados (nota 20 - tabela 2)',
        '18' => 'cobrança contratual - instruções/alterações rejeitadas/pendentes (nota 20 - tabela 5)',
        '19' => 'confirma recebimento de instrução de protesto',
        '20' => 'confirma recebimento de instrução de sustação de protesto /tarifa',
        '21' => 'confirma recebimento de instrução de não protestar',
        '23' => 'título enviado a cartório/tarifa',
        '30' => 'débito mensal de tarifas (para entradas e baixas)',
        '32' => 'baixa por ter sido protestado',
        '33' => 'custas de protesto',
        '34' => 'custas de sustação',
        '35' => 'custas de cartório distribuidor',
        '36' => 'custas de edital',
        '37' => 'tarifa de emissão de boleto/tarifa de envio de duplicata',
        '38' => 'tarifa de instrução',
        '39' => 'tarifa de ocorrências',
        '40' => 'tarifa mensal de emissão de boleto/tarifa mensal de envio de duplicata',
        '41' => 'débito mensal de tarifas – extrato de posição (b4ep/b4ox)',
        '42' => 'débito mensal de tarifas – outras instruções',
        '43' => 'débito mensal de tarifas – manutenção de títulos vencidos',
        '44' => 'débito mensal de tarifas – outras ocorrências',
        '45' => 'débito mensal de tarifas – protesto',
        '46' => 'débito mensal de tarifas – sustação de protesto',
        '47' => 'baixa com transferência para desconto',
        '48' => 'custas de sustação judicial',
        '51' => 'tarifa mensal ref a entradas bancos correspondentes na carteira',
        '52' => 'tarifa mensal baixas na carteira',
        '53' => 'tarifa mensal baixas em bancos correspondentes na carteira',
        '54' => 'tarifa mensal de liquidações na carteira',
        '55' => 'tarifa mensal de liquidações em bancos correspondentes na carteira',
        '56' => 'custas de irregularidade',
        '57' => 'instrução cancelada (nota 20 – tabela 8)',
        '59' => 'baixa por crédito em c/c através do sispag',
        '60' => 'entrada rejeitada carnê (nota 20 – tabela 1)',
        '61' => 'tarifa emissão aviso de movimentação de títulos (2154)',
        '62' => 'débito mensal de tarifa - aviso de movimentação de títulos (2154)',
        '63' => 'título sustado judicialmente',
        '64' => 'entrada confirmada com rateio de crédito',
        '69' => 'cheque devolvido (nota 20 - tabela 9)',
        '71' => 'entrada registrada, aguardando avaliação',
        '72' => 'baixa por crédito em c/c através do sispag sem título correspondente',
        '73' => 'confirmação de entrada na cobrança simples – entrada não aceita na cobrança contratual',
        '76' => 'cheque compensado',
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
            ->setContaDigito($this->rem(38, 38, $header))
            ->setData($this->rem(95, 100, $header));

        return true;
    }

    protected function processarDetalhe(array $detalhe)
    {

        $d = $this->detalheAtual();

        $d->setNossoNumero($this->rem(86, 94, $detalhe))
            ->setNumeroDocumento($this->rem(117, 126, $detalhe))
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

        if($d->hasOcorrencia('06','07','08','10'))
        {
            $this->totais['liquidados']++;
            $d->setOcorrenciaTipo($d::OCORRENCIA_LIQUIDADA);
        }
        elseif($d->hasOcorrencia('02','64','71','73'))
        {
            $this->totais['entradas']++;
            $d->setOcorrenciaTipo($d::OCORRENCIA_ENTRADA);
        }
        elseif($d->hasOcorrencia('05','09','32','47','59','72'))
        {
            $this->totais['baixados']++;
            $d->setOcorrenciaTipo($d::OCORRENCIA_BAIXADA);
        }
        elseif($d->hasOcorrencia('03','15','16','60','03'))
        {
            $this->totais['erros']++;
            $d->setError('Desconhecido');
        }
        else
        {
            $this->totais['alterados']++;
            $d->setOcorrenciaTipo($d::OCORRENCIA_ALTERACAO);
        }

        return true;
    }

    protected function processarTrailer(array $trailer)
    {

        $this->getTrailer()
            ->setQuantidadeTitulos((int)$this->rem(18, 25, $trailer) + (int)$this->rem(58, 65, $trailer) + (int)$this->rem(178, 185, $trailer))
            ->setValorTitulos((float) Util::nFloat($this->rem(221, 234, $trailer)/100, 2, false))
            ->setQuantidadeErros((int) $this->totais['erros'])
            ->setQuantidadeEntradas((int) $this->totais['entradas'])
            ->setQuantidadeLiquidados((int) $this->totais['liquidados'])
            ->setQuantidadeBaixados((int) $this->totais['baixados'])
            ->setQuantidadeAlterados((int) $this->totais['alterados']);

        return true;
    }
}