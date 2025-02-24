<?php

namespace Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab400\Banco;

use Illuminate\Support\Arr;
use Eduardokum\LaravelBoleto\Util;
use Eduardokum\LaravelBoleto\Contracts\Cnab\RetornoCnab400;
use Eduardokum\LaravelBoleto\Exception\ValidationException;
use Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab400\AbstractRetorno;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;

class Bnb extends AbstractRetorno implements RetornoCnab400
{
    /**
     * Código do banco
     *
     * @var string
     */
    protected $codigoBanco = BoletoContract::COD_BANCO_BNB;

    /**
     * Array com as ocorrencias do banco;
     *
     * @var array
     */
    private $ocorrencias = [
        '02' => 'Entrada confirmada',
        '04' => 'Alteração',
        '06' => 'Liquidação normal',
        '07' => 'Pagamento por conta',
        '08' => 'Pagamento por cartório',
        '09' => 'Baixa simples',
        '10' => 'Devolvido - Protestado',
        '11' => 'Em ser',
        '12' => 'Abatimento concedido',
        '13' => 'Abatimento cancelado',
        '14' => 'Vencimento alterado',
        '15' => 'Baixa automática',
        '18' => 'Alteração depositária',
        '19' => 'Confirma recebimento de instrução de protesto',
        '20' => 'Confirma recebimento de instrução de sustação de protesto',
        '21' => 'Alteração de informações de controle da empresa',
        '22' => 'Alteração do "seu número"',
        '51' => 'Entrada rejeitada',
        '52' => 'Erro ocorrencia 02',
        '54' => 'Erro ocorrencia 04',
        '56' => 'Erro ocorrencia 06',
        '57' => 'Erro ocorrencia 07',
        '58' => 'Erro ocorrencia 08',
        '59' => 'Erro ocorrencia 09',
        '60' => 'Erro ocorrencia 10',
        '61' => 'Erro ocorrencia 11',
        '62' => 'Erro ocorrencia 12',
        '63' => 'Erro ocorrencia 13',
        '64' => 'Erro ocorrencia 14',
        '65' => 'Erro ocorrencia 15',
        '68' => 'Erro ocorrencia 18',
        '69' => 'Erro ocorrencia 19',
        '70' => 'Erro ocorrencia 20',
        '71' => 'Erro ocorrencia 21',
        '72' => 'Erro ocorrencia 22',
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
            ->setAgencia($this->rem(27, 30, $header))
            ->setConta($this->rem(33, 39, $header))
            ->setContaDv($this->rem(40, 40, $header))
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
        // Verifica data de crédotp (não vem no retorno mas uso pra saber se foi liquidado)
        if ($this->rem(254, 266, $detalhe) == '0000000000000') {
            $dataCredito = '';
        } else {
            $dataCredito = $this->rem(111, 116, $detalhe);
        }
        $d->setCarteira($this->rem(108, 108, $detalhe))
            ->setNossoNumero($this->rem(63, 70, $detalhe)) // Nosso número + digito (no retorno bnb são separados em campos diferentes)
            ->setNumeroDocumento($this->rem(117, 126, $detalhe))
            ->setNumeroControle($this->rem(38, 62, $detalhe))
            ->setOcorrencia($this->rem(109, 110, $detalhe))
            ->setOcorrenciaDescricao(Arr::get($this->ocorrencias, $d->getOcorrencia(), 'Desconhecida'))
            ->setDataOcorrencia($this->rem(111, 116, $detalhe))
            ->setDataVencimento($this->rem(147, 152, $detalhe))
            ->setDataCredito($dataCredito)
            ->setValor(Util::nFloat($this->rem(153, 165, $detalhe) / 100, 2, false))
            ->setValorTarifa(Util::nFloat($this->rem(176, 188, $detalhe) / 100, 2, false))
            ->setValorIOF(Util::nFloat(0.00, 2, false))
            ->setValorAbatimento(Util::nFloat($this->rem(228, 240, $detalhe) / 100, 2, false))
            ->setValorDesconto(Util::nFloat($this->rem(241, 253, $detalhe) / 100, 2, false))
            ->setValorRecebido(Util::nFloat($this->rem(254, 266, $detalhe) / 100, 2, false))
            ->setValorMora(Util::nFloat($this->rem(267, 279, $detalhe) / 100, 2, false))
            ->setValorMulta(Util::nFloat(0.00, 2, false));

        if ($d->hasOcorrencia('06', '07', '08')) {
            $this->totais['liquidados']++;
            $d->setOcorrenciaTipo($d::OCORRENCIA_LIQUIDADA);
        } elseif ($d->hasOcorrencia('02')) {
            $this->totais['entradas']++;
            $d->setOcorrenciaTipo($d::OCORRENCIA_ENTRADA);
        } elseif ($d->hasOcorrencia('09', '15')) {
            $this->totais['baixados']++;
            $d->setOcorrenciaTipo($d::OCORRENCIA_BAIXADA);
        } elseif ($d->hasOcorrencia('10')) {
            $this->totais['protestados']++;
            $d->setOcorrenciaTipo($d::OCORRENCIA_PROTESTADA);
        } elseif ($d->hasOcorrencia('04', '14', '18', '21', '22')) {
            $this->totais['alterados']++;
            $d->setOcorrenciaTipo($d::OCORRENCIA_ALTERACAO);
        } elseif ($d->hasOcorrencia('51', '52', '54', '56', '57', '58', '59', '60', '61', '62', '63', '64', '65', '68', '69', '70', '71', '72')) {
            $this->totais['erros']++;
            $d->setError('Consulte seu Internet Banking');
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
            ->setQuantidadeTitulos((int) $this->rem(18, 25, $trailer))
            ->setValorTitulos((float) Util::nFloat($this->rem(26, 39, $trailer) / 100, 2, false))
            ->setQuantidadeErros((int) $this->totais['erros'])
            ->setQuantidadeEntradas((int) $this->totais['entradas'])
            ->setQuantidadeLiquidados((int) $this->totais['liquidados'])
            ->setQuantidadeBaixados((int) $this->totais['baixados'])
            ->setQuantidadeAlterados((int) $this->totais['alterados']);

        return true;
    }
}
