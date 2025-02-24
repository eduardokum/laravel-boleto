<?php

namespace Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab240\Banco;

use Illuminate\Support\Arr;
use Eduardokum\LaravelBoleto\Util;
use Eduardokum\LaravelBoleto\Contracts\Cnab\RetornoCnab240;
use Eduardokum\LaravelBoleto\Exception\ValidationException;
use Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab240\AbstractRetorno;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;

class Itau extends AbstractRetorno implements RetornoCnab240
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
        '02' => 'Entrada confirmada (com possibilidade de mensagem – nota 23 – tabela 8)',
        '03' => 'Entrada rejeitada (nota 23 - tabela 1)',
        '04' => 'Alteração de dados – nova entrada ou alteração/exclusãoados acatada',
        '05' => 'Alteração de dados – baixa',
        '06' => 'Liquidação normal',
        '08' => 'Liquidação em cartório',
        '09' => 'Baixa simples',
        '10' => 'Baixa por ter sido liquidado',
        '11' => 'Em ser (só no retorno mensal)',
        '12' => 'Abatimento concedido',
        '13' => 'Abatimento cancelado',
        '14' => 'Vencimento alterado',
        '15' => 'Baixas rejeitadas (nota 23 - tabela 4)',
        '16' => 'Instruções rejeitadas (nota 23 – tabela 3)',
        '17' => 'Alteração/exclusão de dados rejeitada (nota 23 - tabela 2)',
        '18' => 'Cobrança contratual – instruções/alterações rejeitadas/pendentes (nota 23 - tabela 5)',
        '19' => 'Confirmação recebimento de instrução de protesto',
        '20' => 'Confirmação recebimento de instrução de sustação de protesto /tarifa',
        '21' => 'Confirmação recebimento de instrução de não protestar',
        '23' => 'Protesto enviado a cartório/tarifa',
        '24' => 'Instrução de protesto sustada (nota 23 - tabela 7)',
        '25' => 'Alegações do pagador (nota 23 - tabela 6)',
        '26' => 'Tarifa de aviso de cobrança',
        '27' => 'Tarifa de extrato posição (b40x)',
        '28' => 'Tarifa de relação das liquidações',
        '29' => 'Tarifa de manutenção de títulos vencidos',
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
        '51' => 'Tarifa mensal referente a entradas bancos correspondentes na carteira',
        '52' => 'Tarifa mensal baixas na carteira',
        '53' => 'Tarifa mensal baixas em bancos correspondentes na carteira',
        '54' => 'Tarifa mensal de liquidações na carteira',
        '55' => 'Tarifa mensal de liquidações em bancos correspondentes na carteira',
        '56' => 'Custas de irregularidade',
        '57' => 'Instrução cancelada (nota 23 – tabela 8)',
        '60' => 'Entrada rejeitada carnê (nota 20 – tabela 1)',
        '61' => 'Tarifa emissão aviso de movimentação de títulos (2154)',
        '62' => 'Débito mensal de tarifa – aviso de movimentação de títulos (2154)',
        '63' => 'Título sustado judicialmente',
        '74' => 'Instrução de negativação expressa rejeitada (nota 25 – tabela 3)',
        '75' => 'Confirma o recebimento de instrução de entrada em negativação expressa',
        '77' => 'Confirma o recebimento de instrução de exclusão de entrada em negativação expressa',
        '78' => 'Confirma o recebimento de instrução de cancelamento da negativação expressa',
        '79' => 'Negativação expressa informacional (nota 25 – tabela 12)',
        '80' => 'Confirmação de entrada em negativação expressa – tarifa',
        '82' => 'Confirmação o cancelamento de negativação expressa - tarifa',
        '83' => 'Confirmação da exclusão/cancelamento da negativação expressa por liquidação - tarifa',
        '85' => 'Tarifa por boleto (até 03 envios) cobrança ativa eletrônica',
        '86' => 'Tarifa email cobrança ativa eletrônica',
        '87' => 'Tarifa sms cobrança ativa eletrônica',
        '88' => 'Tarifa mensal por boleto (até 03 envios) cobrança ativa eletrônica',
        '89' => 'Tarifa mensal email cobrança ativa eletrônica',
        '90' => 'Tarifa mensal sms cobrança ativa eletrônica',
        '91' => 'Tarifa mensal de exclusão de entrada em negativação expressa',
        '92' => 'Tarifa mensal de cancelamento de negativação expressa',
        '93' => 'Tarifa mensal de exclusão/cancelamento de negativação expressa por liquidação',
        '94' => 'Confirma recebimento de instrução de não negativar',
    ];

    /**
     * Array com as possiveis rejeicoes do banco.
     *
     * @var array
     */
    private $rejeicoes = [
        '03' => [
            '03' => 'Ag. cobradora não foi possível atribuir a agência pelo cep ou cep inválido',
            '04' => 'Estado sigla do estado inválida',
            '05' => 'Data vencimento prazo da operação menor que prazo mínimo ou maior que o máximo',
            '08' => 'Nome do pagador não informado ou deslocado',
            '09' => 'Agência/conta agência encerrada',
            '10' => 'Logradouro não informado ou deslocado',
            '11' => 'Cep cep não numérico',
            '12' => 'Sacador avalista nome não informado ou deslocado (bancos correspondentes)',
            '13' => 'Estado/cep cep incompatível com a sigla do estado',
            '14' => 'Nosso número nosso número já registrado no cadastro do banco ou fora da faixa',
            '15' => 'Nosso número nosso número em duplicidade no mesmo movimento',
            '18' => 'Data de entrada data de entrada inválida para operar com esta carteira',
            '19' => 'Ocorrência ocorrência inválida',
            '21' => 'Ag. cobradora carteira não aceita depositária correspondente, estado da agência diferente do estado do pagador, ag. cobradora não consta no cadastro ou encerrando',
            '22' => 'Carteira carteira não permitida (necessário cadastrar faixa livre)',
            '27' => 'Cnpj inapto cnpj do beneficiário inapto devolução de título em garantia',
            '29' => 'Código empresa categoria da conta inválida',
            '31' => 'Agência/conta conta não tem permissão para protestar (contate seu gerente)',
            '35' => 'Valor do iof iof maior que 5%',
            '36' => 'Qtdade de moeda quantidade de moeda incompatível com valor do título',
            '37' => 'Cnpj/cpf do pagador não numérico ou igual a zeros',
            '42' => 'Nosso número nosso número fora de faixa',
            '52' => 'Ag. cobradora empresa não aceita banco correspondente',
            '53' => 'Ag. cobradora empresa não aceita banco correspondente - cobrança mensagem',
            '54' => 'Data de vencto banco correspondente – título com vencimento inferior a 15 dias',
            '55' => 'Dep./bco. corresp. cep não pertence a depositária informada',
            '56' => 'Dt. vcto./bco. coresp. vencto. superior a 180 dias da data de entrada',
            '57' => 'Data de vencimento cep só depositária bco. do brasil com vencto. inferior a 8 dias',
            '60' => 'Abatimento valor do abatimento inválido',
            '61' => 'Juros de mora juros de mora maior que o permitido',
            '62' => 'Desconto valor do desconto maior que o valor do título',
            '63' => 'Desconto de antecipação valor da importância por dia de desconto (idd) não permitido',
            '64' => 'Emissão do título data de emissão do título inválida (vendor)',
            '65' => 'Taxa financto. taxa inválida (vendor)',
            '66' => 'Data de vencto.. invalida/fora de prazo de operação (mínimo ou máximo)',
            '67' => 'Valor/qtidade. valor do título/quantidade de moeda inválido',
            '68' => 'Carteira carteira inválida ou não cadastrada no intercâmbio da cobrança',
            '98' => 'Flash inválido registro mensagem sem flash cadastrado ou flash informado diferente do cadastrado',
            '91' => 'Dac dac agência / conta corrente inválido',
            '92' => 'Dac dac agência/conta/carteira/nosso número inválido',
            '93' => 'Estado sigla estado inválida',
            '94' => 'Estado sigla estado incompatível com cep do pagador',
            '95' => 'Cep cep do pagador não numérico ou inválido',
            '96' => 'Endereço endereço / nome / cidade pagador inválido',
        ],
        '15' => [
            '04' => 'Nosso número em duplicidade num mesmo movimento',
            '05' => 'Solicitação de baixa para título já baixado ou liquidado',
            '06' => 'Solicitação de baixa para título não registrado no sistema',
            '07' => 'Cobrança prazo curto - solicitação de baixa p/ título não registrado no sistema',
            '08' => 'Solicitação de baixa para título em floating',
        ],
        '16' => [
            '01' => 'Instrução/ocorrência não existente',
            '03' => 'Conta não tem permissão para protestar (contate seu gerente)',
            '06' => 'Nosso número igual a zeros',
            '09' => 'Cnpj/cpf do sacador/avalista inválido',
            '14' => 'Registro em duplicidade',
            '15' => 'Cnpj/cpf informado sem nome do sacador/avalista',
            '19' => 'Valor do abatimento maior que 90% do valor do título',
            '20' => 'Existe sustacao de protesto pendente para o titulo',
            '21' => 'Título não registrado no sistema',
            '22' => 'Título baixado ou liquidado',
            '23' => 'Instrução não aceita',
            '24' => 'Instrução incompatível - existe instrução de protesto para o título',
            '25' => 'Instrução incompatível - não existe instrução de protesto para o título',
            '26' => 'Instrução não aceita por já ter sido emitida a ordem de protesto ao cartório',
            '27' => 'Instrução não aceita por não ter sido emitida a ordem de protesto ao cartório',
            '28' => 'Já existe uma mesma instrução cadastrada anteriormente para o título',
            '29' => 'Valor líquido + valor do abatimento diferente do valor do título registrado',
            '30' => 'Existe uma instrução de não protestar ativa para o título',
            '31' => 'Existe uma ocorrência do pagador que bloqueia a instrução',
            '32' => 'Depositária do título = 9999 ou carteira não aceita protesto',
            '33' => 'Alteração de vencimento igual à registrada no sistema ou que torna o título vencido',
            '34' => 'Instrução de emissão de aviso de cobrança para título vencido antes do vencimento',
            '35' => 'Solicitação de cancelamento de instrução inexistente',
            '36' => 'Título sofrendo alteração de controle (agência/conta/carteira/nosso número)',
            '37' => 'Instrução não permitida para a carteira',
            '40' => 'Instrução incompatível – não existe instrução de negativação expressa para o título',
            '41' => 'Instrução não permitida – título já enviado para negativação expressa',
            '42' => 'Instrução não permitida – título com negativação expressa concluída',
            '43' => 'Prazo inválido para negativação – mínimo: 02 dias corridos após o vencimento',
            '45' => 'Instrução incompatível para o mesmo título nesta data',
            '47' => 'Instrução não permitida – espécie inválida',
            '48' => 'Dados do pagador inválidos (cpf / cnpj / nome)',
            '49' => 'Dados do endereço do pagador inválidos',
            '50' => 'Data de emissão do título inválida',
            '51' => 'Instrução não permitida – título com negativação expressa agendada',
        ],
        '17' => [
            '02' => 'Agência cobradora inválida ou com o mesmo conteúdo',
            '04' => 'Sigla do estado inválida',
            '05' => 'Data de vencimento inválida ou com o mesmo conteúdo',
            '06' => 'Valor do título com outra alteração simultânea',
            '08' => 'Nome do pagador com o mesmo conteúdo',
            '11' => 'Cep inválido',
            '12' => 'Número inscrição inválido do sacador avalista',
            '13' => 'Seu número com o mesmo conteúdo',
            '21' => 'Agência cobradora não consta no cadastro de depositária ou em encerramento',
            '42' => 'Alteração inválida para título vencido',
            '43' => 'Alteração bloqueada – vencimento já alterado',
            '53' => 'Instrução com o mesmo conteúdo',
            '54' => 'Data vencimento para bancos correspondentes inferior ao aceito pelo banco',
            '55' => 'Alterações iguais para o mesmo controle (agência/conta/carteira/nosso número)',
            '60' => 'Valor de iof – alteração não permitida para carteiras de n.s. – moeda variável',
            '61' => 'Título já baixado ou liquidado ou não existe título correspondente no sistema',
            '66' => 'Alteração não permitida para carteiras de notas de seguros – moeda variável',
            '67' => 'Nome inválido do sacador avalista',
            '72' => 'Endereço inválido – sacador avalista',
            '73' => 'Bairro inválido – sacador avalista',
            '74' => 'Cidade inválida – sacador avalista',
            '75' => 'Sigla estado inválido – sacador avalista',
            '76' => 'Cep inválido – sacador avalista',
            '81' => 'Alteração bloqueada - título com negativação expressa ou protesto',
        ],
        '18' => [
            '16' => 'Abatimento/alteração do valor do título ou solicitação de baixa bloqueados',
            '40' => 'Não aprovada devido ao impacto na elegibilidade de garantias',
            '41' => 'Automaticamente rejeitada',
            '42' => 'Confirma recebimento de instrução – pendente de análise',
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
            ->setCodBanco($this->rem(1, 3, $header))
            ->setLoteServico($this->rem(4, 7, $header))
            ->setTipoRegistro($this->rem(8, 8, $header))
            ->setTipoInscricao($this->rem(18, 18, $header))
            ->setNumeroInscricao($this->rem(19, 32, $header))
            ->setAgencia($this->rem(54, 57, $header))
            ->setConta($this->rem(66, 70, $header))
            ->setContaDv($this->rem(72, 72, $header))
            ->setNomeEmpresa($this->rem(73, 102, $header))
            ->setNomeBanco($this->rem(103, 132, $header))
            ->setCodigoRemessaRetorno($this->rem(143, 143, $header))
            ->setData($this->rem(144, 151, $header))
            ->setNumeroSequencialArquivo($this->rem(158, 163, $header))
            ->setVersaoLayoutArquivo($this->rem(164, 166, $header));

        return true;
    }

    /**
     * @param array $headerLote
     *
     * @return bool
     * @throws ValidationException
     */
    protected function processarHeaderLote(array $headerLote)
    {
        $this->getHeaderLote()
            ->setCodBanco($this->rem(1, 3, $headerLote))
            ->setNumeroLoteRetorno($this->rem(4, 7, $headerLote))
            ->setTipoRegistro($this->rem(8, 8, $headerLote))
            ->setTipoOperacao($this->rem(9, 9, $headerLote))
            ->setTipoServico($this->rem(10, 11, $headerLote))
            ->setVersaoLayoutLote($this->rem(14, 16, $headerLote))
            ->setTipoInscricao($this->rem(18, 18, $headerLote))
            ->setNumeroInscricao($this->rem(19, 33, $headerLote))
            ->setAgencia($this->rem(55, 58, $headerLote))
            ->setConta($this->rem(67, 71, $headerLote))
            ->setContaDv($this->rem(73, 73, $headerLote))
            ->setNomeEmpresa($this->rem(74, 103, $headerLote))
            ->setNumeroRetorno($this->rem(184, 191, $headerLote))
            ->setDataGravacao($this->rem(192, 199, $headerLote))
            ->setDataCredito($this->rem(200, 207, $headerLote));

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

        if ($this->getSegmentType($detalhe) == 'T') {
            $d->setOcorrencia($this->rem(16, 17, $detalhe))
                ->setOcorrenciaDescricao(Arr::get($this->ocorrencias, $this->detalheAtual()->getOcorrencia(), 'Desconhecida'))
                ->setNossoNumero($this->rem(41, 48, $detalhe))
                ->setCarteira($this->rem(38, 40, $detalhe))
                ->setNumeroDocumento($this->rem(59, 68, $detalhe))
                ->setDataVencimento($this->rem(74, 81, $detalhe))
                ->setValor(Util::nFloat($this->rem(82, 96, $detalhe) / 100, 2, false))
                ->setNumeroControle($this->rem(106, 130, $detalhe))
                ->setPagador([
                    'nome'      => $this->rem(149, 188, $detalhe),
                    'documento' => $this->rem(134, 148, $detalhe),
                ])
                ->setValorTarifa(Util::nFloat($this->rem(199, 213, $detalhe) / 100, 2, false));

            /**
             * ocorrencias
             */
            $msgAdicional = str_split(sprintf('%08s', $this->rem(214, 221, $detalhe)), 2) + array_fill(0, 5, '');
            if ($d->hasOcorrencia('06', '08', '10')) {
                $this->totais['liquidados']++;
                $d->setOcorrenciaTipo($d::OCORRENCIA_LIQUIDADA);
            } elseif ($d->hasOcorrencia('02')) {
                $this->totais['entradas']++;
                if (array_search('a4', array_map('strtolower', $msgAdicional)) !== false) {
                    $d->getPagador()->setDda(true);
                }
                $d->setOcorrenciaTipo($d::OCORRENCIA_ENTRADA);
            } elseif ($d->hasOcorrencia('09')) {
                $this->totais['baixados']++;
                $d->setOcorrenciaTipo($d::OCORRENCIA_BAIXADA);
            } elseif ($d->hasOcorrencia('32')) {
                $this->totais['protestados']++;
                $d->setOcorrenciaTipo($d::OCORRENCIA_PROTESTADA);
            } elseif ($d->hasOcorrencia('04')) {
                $this->totais['alterados']++;
                $d->setOcorrenciaTipo($d::OCORRENCIA_ALTERACAO);
            } elseif ($d->hasOcorrencia('03', '15', '16', '17', '18')) {
                $this->totais['erros']++;
                $error = Util::appendStrings(Arr::get($this->rejeicoes, $msgAdicional[0], ''), Arr::get($this->rejeicoes, $msgAdicional[1], ''), Arr::get($this->rejeicoes, $msgAdicional[2], ''), Arr::get($this->rejeicoes, $msgAdicional[3], ''));
                $d->setError($error);
            } else {
                $d->setOcorrenciaTipo($d::OCORRENCIA_OUTROS);
            }
        }

        if ($this->getSegmentType($detalhe) == 'U') {
            $d->setValorMulta(Util::nFloat($this->rem(18, 32, $detalhe) / 100, 2, false))
                ->setValorDesconto(Util::nFloat($this->rem(33, 47, $detalhe) / 100, 2, false))
                ->setValorAbatimento(Util::nFloat($this->rem(48, 62, $detalhe) / 100, 2, false))
                ->setValorIOF(Util::nFloat($this->rem(63, 77, $detalhe) / 100, 2, false))
                ->setValorRecebido(Util::nFloat($this->rem(78, 92, $detalhe) / 100, 2, false))
                ->setValorTarifa($d->getValorRecebido() - Util::nFloat($this->rem(93, 107, $detalhe) / 100, 2, false))
                ->setDataOcorrencia($this->rem(138, 145, $detalhe))
                ->setDataCredito($this->rem(146, 153, $detalhe));
        }

        return true;
    }

    /**
     * @param array $trailer
     *
     * @return bool
     * @throws ValidationException
     */
    protected function processarTrailerLote(array $trailer)
    {
        $this->getTrailerLote()
            ->setLoteServico($this->rem(4, 7, $trailer))
            ->setTipoRegistro($this->rem(8, 8, $trailer))
            ->setQtdRegistroLote((int) $this->rem(18, 23, $trailer))
            ->setQtdTitulosCobrancaSimples((int) $this->rem(24, 29, $trailer))
            ->setValorTotalTitulosCobrancaSimples(Util::nFloat($this->rem(30, 46, $trailer) / 100, 2, false))
            ->setQtdTitulosCobrancaVinculada((int) $this->rem(47, 52, $trailer))
            ->setValorTotalTitulosCobrancaVinculada(Util::nFloat($this->rem(53, 69, $trailer) / 100, 2, false));

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
            ->setNumeroLote($this->rem(4, 7, $trailer))
            ->setTipoRegistro($this->rem(8, 8, $trailer))
            ->setQtdLotesArquivo((int) $this->rem(18, 23, $trailer))
            ->setQtdRegistroArquivo((int) $this->rem(24, 29, $trailer));

        return true;
    }
}
