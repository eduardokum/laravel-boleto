<?php
namespace Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab400\Banco;

use Eduardokum\LaravelBoleto\Util;
use Eduardokum\LaravelBoleto\Contracts\Cnab\RetornoCnab400;
use Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab400\AbstractRetorno;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;

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
        '03' => 'Entrada rejeitada',
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
        '15' => 'Baixas rejeitadas',
        '16' => 'Instruções rejeitadas',
        '17' => 'Alteração de dados rejeitados',
        '18' => 'Cobrança contratual - instruções/alterações rejeitadas/pendentes',
        '19' => 'Confirma recebimento de instrução de protesto',
        '20' => 'Confirma recebimento de instrução de sustação de protesto /tarifa',
        '21' => 'Confirma recebimento de instrução de não protestar',
        '23' => 'Título enviado a cartório/tarifa',
        '24' => 'Instrução de protesto rejeitada / sustada / pendente',
        '25' => 'Alegações do pagador',
        '26' => 'Tarifa de aviso de cobrança',
        '27' => 'Tarifa de extrato posição (B40X)',
        '28' => 'Tarifa de relação das liquidações',
        '29' => 'Tarifa de manutenção de títulos vencidos',
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
        '57' => 'Instrução cancelada',
        '59' => 'Baixa por crédito em c/c através do sispag',
        '60' => 'Entrada rejeitada carnê',
        '61' => 'Tarifa emissão aviso de movimentação de títulos (2154)',
        '62' => 'Débito mensal de tarifa - aviso de movimentação de títulos (2154)',
        '63' => 'Título sustado judicialmente',
        '64' => 'Entrada confirmada com rateio de crédito',
        '65' => 'Pagamento com cheque – aguardando compensação',
        '69' => 'Cheque devolvido',
        '71' => 'Entrada registrada, aguardando avaliação',
        '72' => 'Baixa por crédito em c/c através do sispag sem título correspondente',
        '73' => 'Confirmação de entrada na cobrança simples – entrada não aceita na cobrança contratual',
        '74' => 'Instrução de negativação expressa rejeitada',
        '75' => 'Confirmação de recebimento de instrução de entrada em negativação expressa',
        '76' => 'Cheque compensado',
        '77' => 'Confirmação de recebimento de instrução de exclusão de entrada em negativação expressa',
        '78' => 'Confirmação de recebimento de instrução de cancelamento de negativação expressa',
        '79' => 'Negativação expressa informacional',
        '80' => 'Confirmação de entrada em negativação expressa – tarifa',
        '82' => 'Confirmação do cancelamento de negativação expressa – tarifa',
        '83' => 'Confirmação de exclusão de entrada em negativação expressa por liquidação – tarifa',
        '85' => 'Tarifa por boleto (até 03 envios) cobrança ativa eletrônica',
        '86' => 'Tarifa email cobrança ativa eletrônica',
        '87' => 'Tarifa SMS cobrança ativa eletrônica',
        '88' => 'Tarifa mensal por boleto (até 03 envios) cobrança ativa eletrônica',
        '89' => 'Tarifa mensal email cobrança ativa eletrônica',
        '90' => 'Tarifa mensal SMS cobrança ativa eletrônica',
        '91' => 'Tarifa mensal de exclusão de entrada de negativação expressa',
        '92' => 'Tarifa mensal de cancelamento de negativação expressa',
        '93' => 'Tarifa mensal de exclusão de negativação expressa por liquidação',
    ];

    /**
     * Array com as possiveis rejeicoes do banco.
     *
     * @var array
     */
    private $rejeicoes = [
        '03' => 'Ag. Cobradora - Cep sem atendimento de protesto no momento',
        '04' => 'Sigla do estado inválida',
        '05' => 'Data Vencimento - Prazo da operação menor que prazo mínimo ou maior que o máximo',
        '07' => 'Valor do título maior que 10.000.000,00',
        '08' => 'Nome do Pagador - Não informado ou deslocado',
        '09' => 'Agência/Conta - Agência encerrada',
        '10' => 'Logradouro - Não informado ou deslocado',
        '11' => 'Cep não numérico ou cep inválido',
        '12' => 'Sacador/Avalista - Nome não informado ou deslocado (bancos correspondentes)',
        '13' => 'Estado/Cep - Cep incompatível com a sigla do estado',
        '14' => 'Nosso número já registrado no cadastro do banco ou fora da faixa',
        '15' => 'Nosso número em duplicidade no mesmo movimento',
        '18' => 'Data de entrada inválida para operar com esta carteira',
        '19' => 'Ocorrência inválida',
        '21' => 'Ag. Cobradora - Carteira não aceita depositária correspondente estado da agência diferente do estado do pagador ag. cobradora não consta no cadastro ou encerrando',
        '22' => 'Carteira - Não permitida (necessário cadastrar faixa livre)',
        '26' => 'Agência/Conta não liberada para operar com cobrança',
        '27' => 'Cnpj do beneficiário inapto devolução de título em garantia',
        '29' => 'Código empresa - Categoria da conta inválida',
        '30' => 'Entradas bloqueadas, conta suspensa em cobrança',
        '31' => 'Agência/Conta - Conta não tem permissão para protestar (contate seu gerente)',
        '35' => 'Iof maior que 5%',
        '36' => 'Quantidade de moeda incompatível com valor do título',
        '37' => 'Cnpj/Cpf do pagador não numérico ou igual a zeros',
        '42' => 'Nosso número fora de faixa',
        '52' => 'Ag. cobradora empresa não aceita banco correspondente',
        '53' => 'Ag. cobradora empresa não aceita banco correspondente - cobrança mensagem',
        '54' => 'Data de vencimento banco correspondente - título com vencimento inferior a 15 dias',
        '55' => 'Cep não pertence à depositária informada',
        '56' => 'Data Vencimento superior a 180 dias da data de entrada',
        '57' => 'Cep só depositária bco do brasil com vencimento inferior a 8 dias',
        '60' => 'Valor do abatimento inválido',
        '61' => 'Juros de mora maior que o permitido',
        '62' => 'Valor do desconto maior que valor do título',
        '63' => 'Valor da importância por dia de desconto (idd) não permitido',
        '64' => 'Data de emissão do título inválida',
        '65' => 'Taxa financiamento inválida (vendor)',
        '66' => 'Data de vencimento invalida/fora de prazo de operação (mínimo ou máximo)',
        '67' => 'Valor do título/quantidade de moeda inválido',
        '68' => 'Carteira inválida ou não cadastrada no intercâmbio da cobrança',
        '69' => 'Carteira inválida para títulos com rateio de crédito',
        '70' => 'Agência/Conta beneficiário não cadastrado para fazer rateio de crédito',
        '78' => 'Agência/Conta duplicidade de agência/conta beneficiária do rateio de crédito',
        '80' => 'Agência/Conta quantidade de contas beneficiárias do rateio maior do que o permitido (máximo de 30 contas por título)',
        '81' => 'Agência/Conta para rateio de crédito inválida / não pertence ao itaú',
        '82' => 'Desconto/Abatimento não permitido para títulos com rateio de crédito',
        '83' => 'Valor do título menor que a soma dos valores estipulados para rateio',
        '84' => 'Agência/Conta beneficiária do rateio é a centralizadora de crédito do beneficiário',
        '85' => 'Agência/Conta do beneficiário é contratual / rateio de crédito não permitido',
        '86' => 'Código do tipo de valor inválido / não previsto para títulos com rateio de crédito',
        '87' => 'Agência/Conta registro tipo 4 sem informação de agências/contas beneficiárias do rateio',
        '90' => 'Número da linha cobrança mensagem - número da linha da mensagem inválido ou quantidade de linhas excedidas',
        '97' => 'Sem mensagem (só de campos fixos), porém com registro do tipo 7 ou 8',
        '98' => 'Registro mensagem sem flash cadastrado ou flash informado diferente do cadastrado',
        '99' => 'Conta de cobrança com flash cadastrado e sem registro de mensagem correspondente',
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
            ->setAgencia($this->rem(27, 30, $header))
            ->setConta($this->rem(33, 37, $header))
            ->setContaDv($this->rem(38, 38, $header))
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
        $d = $this->detalheAtual();

        $d->setCarteira($this->rem(83, 85, $detalhe))
            ->setNossoNumero($this->rem(86, 94, $detalhe))
            ->setNumeroDocumento($this->rem(117, 126, $detalhe))
            ->setNumeroControle($this->rem(38, 62, $detalhe))
            ->setOcorrencia($this->rem(109, 110, $detalhe))
            ->setOcorrenciaDescricao(array_get($this->ocorrencias, $d->getOcorrencia(), 'Desconhecida'))
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

        $msgAdicional = str_split(sprintf('%08s', $this->rem(378, 385, $detalhe)), 2) + array_fill(0, 4, '');
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
        } elseif ($d->hasOcorrencia('03', '15', '16', '17', '18', '60')) {
            $this->totais['erros']++;
            $error = Util::appendStrings(
                array_get($this->rejeicoes, $msgAdicional[0], ''),
                array_get($this->rejeicoes, $msgAdicional[1], ''),
                array_get($this->rejeicoes, $msgAdicional[2], ''),
                array_get($this->rejeicoes, $msgAdicional[3], '')
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
     * @throws \Exception
     */
    protected function processarTrailer(array $trailer)
    {
        $this->getTrailer()
            ->setQuantidadeTitulos((int) $this->rem(18, 25, $trailer) + (int) $this->rem(58, 65, $trailer) + (int) $this->rem(178, 185, $trailer))
            ->setValorTitulos((float) Util::nFloat($this->rem(221, 234, $trailer) / 100, 2, false))
            ->setQuantidadeErros((int) $this->totais['erros'])
            ->setQuantidadeEntradas((int) $this->totais['entradas'])
            ->setQuantidadeLiquidados((int) $this->totais['liquidados'])
            ->setQuantidadeBaixados((int) $this->totais['baixados'])
            ->setQuantidadeAlterados((int) $this->totais['alterados']);

        return true;
    }
}
