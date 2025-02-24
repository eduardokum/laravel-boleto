<?php

namespace Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab400\Banco;

use Illuminate\Support\Arr;
use Eduardokum\LaravelBoleto\Util;
use Eduardokum\LaravelBoleto\Contracts\Cnab\RetornoCnab400;
use Eduardokum\LaravelBoleto\Exception\ValidationException;
use Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab400\AbstractRetorno;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;

class C6 extends AbstractRetorno implements RetornoCnab400
{
    /**
     * Código do banco
     *
     * @var string
     */
    protected $codigoBanco = BoletoContract::COD_BANCO_C6;

    /**
     * Array com as ocorrencias do banco;
     *
     * @var array
     */
    private $ocorrencias = [
        '02' => 'Entrada confirmada',
        '03' => 'Entrada Rejeitada',
        '04' => 'Alteração de Dados (Entrada)',
        '05' => 'Alteração de Dados (Baixa)',
        '06' => 'Liquidação Normal',
        '07' => 'Liquidação após Baixa',
        '08' => 'Liquidação em Cartório',
        '09' => 'Baixa Simples',
        '10' => 'Baixa comandada do cliente arquivo',
        '12' => 'Abatimento concedido',
        '13' => 'Abatimento cancelado',
        '14' => 'Vencimento alterado',
        '15' => 'Baixa rejeitada',
        '16' => 'Instrução rejeitada',
        '17' => 'Alterações de dados rejeitados',
        '19' => 'Confirma instrução de protesto',
        '20' => 'Confirma instrução de sustação de protesto',
        '21' => 'Confirma instrução de não protestar',
        '23' => 'Protesto enviado a cartório',
        '29' => 'Sacado nao retirou boleto eletronicamente,enviado para correio',
        '32' => 'Baixa por ter sido protestado',
        '35' => 'Alegações do sacado',
        '36' => 'Custas de Edital',
        '37' => 'Custas de sustação judicial',
        '38' => 'Título sustado judicialmente',
        '65' => 'Pagamento com Cheque - Aguardando compensação',
        '69' => 'Cancelamento de Liquidação por Cheque Devolvido',
        '71' => 'Protesto cancelado pelo Cartório',
        '72' => 'Baixa Operacional',
        '74' => 'Cancelamento da Baixa Operacional',
        '75' => 'Pagamento Parcial',
        '90' => 'Instrução de Protesto Rejeitada',
        '95' => 'Troca Uso Empresa',
        '96' => 'Emissão Extrato Mov. Carteira',
        '97' => 'Tarifa de sustação de protesto',
        '98' => 'Tarifa de protesto',
        '99' => 'Custas de protesto',
    ];

    /**
     * Array com as possiveis rejeicoes do banco.
     *
     * @var array
     */
    private $rejeicoes = [
        '1818' => 'boleto não retirado pelo sacado. reenviado pelo correio para carteiras com emissão pelo banco',
        '9000' => 'data vencimento menor que o prazo de aceitação do título',
        '9001' => 'sacado bloqueado por atraso',
        '9002' => 'registro opcional inválido',
        '9003' => 'cep sem praça de cobrança',
        '9004' => 'prazo insuficiente para cobrança do título',
        '9005' => 'Campo numérico inválido',
        '9006' => 'Campo texto inválido',
        '9007' => 'Campo tipo data inválido',
        '9008' => 'Caractere inválido',
        '9009' => 'cpf/cnpj do sacado e emitente devem ser diferentes',
        '9010' => 'data vencimento menor que a data de emissão',
        '9011' => 'data emissão maior que a data atual',
        '9012' => 'uf sacado inválido',
        '9013' => 'uf emitente inválido',
        '9014' => 'Campo obrigatório não preenchido',
        '9015' => 'cpf do sacado inválido',
        '9016' => 'cnpj do sacado inválido',
        '9017' => 'o nome do sacado enviado não confere com o nome do sacado cadastrado no sistema para este cnpj/cpf',
        '9018' => 'tipo do sacado inválido',
        '9019' => 'o sacado está bloqueado',
        '9020' => 'o endereço do sacado esta com o  tamanho esta maior que o permitido',
        '9021' => 'digito do nosso numero inválido',
        '9022' => 'não existe faixa cadastrada para o banco e a conta',
        '9023' => 'o nosso numero esta fora da faixa cadastrada para o cedente',
        '9024' => 'identificação do título inválida',
        '9025' => 'ocorrência não permitida pois o título esta baixado',
        '9026' => 'ocorrência não permitida pois o título esta liquidado',
        '9027' => 'ocorrência não permitida pois o título esta em protesto',
        '9028' => 'não é permitida alteração de vencimento para carteira de desconto',
        '9029' => 'situação do título inválida',
        '9030' => 'não foi possível conceder o abatimento',
        '9031' => 'não existe abatimento a ser cancelado',
        '9032' => 'não foi possível prorrogar a data de vencimento do título',
        '9033' => 'evento não permitido para situação do título',
        '9034' => 'evento não permitido para cheques',
        '9035' => 'o código do registro esta diferente de 1',
        '9036' => 'Agência inválida',
        '9037' => 'Número da Conta Corrente para depósito Inválido',
        '9038' => 'o cnpj do cedente passado para o arquivo não confere com o cnpj do cedente cadastrado para o arquivo',
        '9040' => 'cnpj do cedente não encontrado no cadastro',
        '9041' => 'tipo do emitente inválido',
        '9042' => 'cnpj do emitente inválido',
        '9045' => 'campo nosso numero deve ter um valor de, no máximo , 10 digitos quando a carteira de cobrança não é direta',
        '9046' => 'no campo nosso número a identificação do título esta inválida',
        '9047' => 'banco e conta de cobrança direta não informados',
        '9049' => 'campo aceite enviado com valor nulo ou inválido',
        '9050' => 'data de emisão inválida',
        '9051' => 'data de vencimento inválida',
        '9052' => 'Data de desconto 2 inválida',
        '9053' => 'especie de titulo invalida',
        '9054' => 'especie de titulo não encontrada',
        '9055' => 'valor de título inválido',
        '9056' => 'prazo de cartorio invalido',
        '9057' => 'valor de abatimento inválido',
        '9058' => 'valor de desconto inválido',
        '9059' => 'código de ocorrência inválida ou inexistente',
        '9060' => 'tipo de mora inválido',
        '9062' => 'valor de juros ao dia inválido',
        '9063' => 'a data de juros mora é anterior à data de vencimento. favor verificar estes campos.',
        '9064' => 'a data de juros mora inválida',
        '9065' => 'número da sequência diferente do esperado',
        '9066' => 'numero de sequencia inválido',
        '9067' => 'registro inválido',
        '9068' => 'cpf do emitente inválido',
        '9070' => 'nome do emitente inválido',
        '9071' => 'endereço do emitente inválido',
        '9072' => 'cidade do emitente inválida',
        '9073' => 'cep do emitente inválido',
        '9074' => 'este contrato não está cadastrado para o cedente',
        '9075' => 'não é permitida a entrada de títulos vencidos',
        '9078' => 'não existe endereço, uf e cidade para o título',
        '9079' => 'nosso número inválido',
        '9081' => 'prazo insuficiente para cobrança do título neste cep',
        '9083' => 'o cedente não pode enviar esse tipo de título com esta carteira',
        '9084' => 'seu número do registro opcional diferente da linha do registro do título',
        '9085' => 'data de vencimento do registro opcional diferente da linha do registro do título.',
        '9086' => 'valor do título no vencimento do registro opcional diferente da linha do registro do título.',
        '9087' => 'os títulos de carteira de cobrança direta só podem ser enviados para contas de cobrnaça direta. acao: confira a carteira e a conta cobrança que está sendo enviada/atribuida ao título.',
        '9089' => 'código cmc7 invalido',
        '9090' => 'entrada - nosso número já está sendo utilizado para mesmo banco / conta',
        '9091' => 'cep do sacado não pertence ao estado da federação (uf) informado',
        '9092' => 'tipo de multa inválido',
        '9093' => 'registro opcional de emitente inválido',
        '9097' => 'O campo Nosso Número não foi informado ou não foi possivel identificar o titulo.',
        '9098' => 'foi encontrado mais de um título para esse nosso número.',
        '9099' => 'preencha o campo de "conta de cobrança" no cadastro de carteira por cedente.',
        '9100' => 'título possui registro opcional de emitente e a sua configuração não permite envio de títulos de terceiros.',
        '9101' => 'título possui emitente, porém seus dados não foram informados.',
        '9103' => 'ja existe titulo em aberto cadastrado para este cedente/seu numero/data vencimento/valor e emitente',
        '9104' => 'impedido pela legislação vigente',
        '9105' => 'crédito retido',
        '9106' => 'nosso numero nao informado',
        '9107' => 'tamanho máximo do nosso número para cobrança direta é 10 posições + digito(layout padrao matera/bradesco).',
        '9108' => 'título pertence a uma espécie que não pode ser protestada.',
        '9109' => 'protesto não permitido para título com moeda diferente de real.',
        '9110' => 'cep do sacado não atendido pelos cartórios cadastrados.',
        '9113' => 'Não permitimos troca de carteira no evento de Alteração de Outros Dados.',
        '9114' => 'Não permitimos troca de tipo titulo no evento de Alteração de Outros Dados.',
        '9201' => 'liquidação em cartório',
        '9202' => 'baixa decurso prazo - banco',
        '9203' => 'baixa protestado',
        '9204' => 'Tarifa de Sustacao de protesto',
        '9205' => 'tarifa de protesto',
        '9206' => 'custas de protesto',
        '9207' => 'custas de edital',
        '9208' => 'custas de sustação de protesto título sustado judicialmente',
        '9210' => 'liquidação em cheque',
        '9213' => 'tarifa de manutenção de título vencido',
        '9216' => 'liquidação no guichê de caixa em dinheiro',
        '9217' => 'liquidação em banco correspondente',
        '9218' => 'liquidação por compensação eletrônica',
        '9219' => 'liquidação por conta',
        '9222' => 'emissão extrato mov. carteira',
        '9223' => 'Liquidação por STR',
        '9224' => 'Carteira do Tipo G não pode inserir titulos.',
        '9230' => 'Valor desconto 2 inválido',
        '9232' => 'Sacado pertence a empresa do grupo (coligada)',
        '9233' => 'Por solicitação da diretoria de crédito/comercial',
        '9234' => 'Inexistência de relação com o cedente',
        '9235' => 'Outros',
        '9236' => 'Recusado - Outros Motivos',
        '9237' => 'Baixa por Outros Motivos',
        '9238' => 'Pagador Rejeita Boleto',
        '9239' => 'Pagador Aceita Boleto',
        '9240' => 'Data multa menor que data de vencimento do título',
        '9242' => 'Baixa Integral Interbancária',
        '9243' => 'Baixa Integral Intrabancária',
        '9244' => 'Baixa Parcial Intrabancária',
        '9245' => 'Baixa Parcial Interbancária',
        '9250' => 'Tipo de autorização para recebimento de valor divergente inválido',
        '9251' => 'Indicativo Tipo de valor ou percentual inválido',
        '9252' => 'Quantidade de pagamento parcial inválido',
        '9253' => 'Quantidade de pagamento parcial inválido, somente é permitido um valor maior ou igual a quantidade de pagamentos já recebido',
        '9254' => 'Mínimo não aceito para o título',
        '9255' => 'Máximo não aceito para o título',
        '9258' => 'Data de desconto 3 inválida',
        '9259' => 'Valor desconto 3 inválido',
        '9260' => 'Mínimo é obrigatório quando informado o tipo valor ou percentual',
        '9261' => 'Tipo de autorização de recebimento de valor divergente não permitio para tipo de título 31',
        '9262' => 'Para especie de título diferente de fatura de cartão de crédito não é possível informar o tipo aceita qualquer valor com range mínimo e máximo  preenchido',
        '9263' => 'Mínimo e Máximo tem que ser informado para o tipo de autorização de valor divergente igual a 2',
        '9264' => 'Mínimo e Máximo não devem ser informados para o tipo de autorização de valor divergente igual a 3',
        '9265' => 'Mínimo deve ser informado e Máximo não pode ser informado para o tipo de autorização de valor divergente igual a 4',
        '9266' => 'Valor não permitido para tipo de título fatura de cartão de crédito',
        '9267' => 'Não é permitido ter juros, multa, abatimento, desconto ou protesto tipo de título fatura de cartão de crédito',
        '9999' => 'cep do sacado inválido',
    ];

    /**
     * Campos com possíveis erros
     * @var string[]
     */
    private $campoInvalido = [
        '004' => 'CNPJ da Empresa',
        '018' => 'Código da Empresa',
        '063' => 'Nosso Número',
        '074' => 'Digito Verificador nosso número',
        '107' => 'Código da Carteira',
        '109' => 'Código Ocorrência Remessa',
        '111' => 'Seu Número',
        '121' => 'Data Vencimento',
        '127' => 'Valor Título',
        '148' => 'Espécie do Título',
        '150' => 'Aceite',
        '151' => 'Data Emissão Título',
        '157' => 'Instrução 1',
        '159' => 'Instrução 2',
        '161' => 'Juros ao Dia',
        '174' => 'Data Desconto ',
        '180' => 'Valor Desconto ',
        '193' => 'Data Multa',
        '206' => 'Valor Abatimento',
        '219' => 'Tipo Sacado',
        '221' => 'CNPJ / CPF Sacado',
        '235' => 'Nome do Sacado',
        '275' => 'Endereço Sacado',
        '315' => 'Bairro Sacado',
        '327' => 'CEP Sacado',
        '335' => 'Cidade Sacado',
        '350' => 'UF Sacado',
        '352' => 'Sacador',
        '382' => 'Tipo de Multa',
        '383' => 'Percentual de Multa',
        '386' => 'Data Juros Mora',
        '392' => 'Prazo dias para Cartório',
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
            ->setServico($this->rem(12, 19, $header))
            ->setConta($this->rem(27, 38, $header))
            ->setCodigoCliente($this->rem(39, 50, $header))
            ->setData($this->rem(125, 130, $header));

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
        $d->setCarteira($this->rem(107, 108, $detalhe))
            ->setNossoNumero($this->rem(63, 73, $detalhe))
            ->setNumeroDocumento($this->rem(117, 126, $detalhe))
            ->setNumeroControle($this->rem(38, 62, $detalhe))
            ->setOcorrencia($this->rem(109, 110, $detalhe))
            ->setOcorrenciaDescricao(Arr::get($this->ocorrencias, $d->getOcorrencia(), 'Desconhecida'))
            ->setDataOcorrencia($this->rem(111, 116, $detalhe))
            ->setDataVencimento($this->rem(147, 152, $detalhe))
            ->setDataCredito($this->rem(296, 301, $detalhe))
            ->setValor(Util::nFloat($this->rem(153, 165, $detalhe) / 100, 2, false))
            ->setValorTarifa(Util::nFloat($this->rem(176, 188, $detalhe) / 100, 2, false))
            ->setValorAbatimento(Util::nFloat($this->rem(228, 240, $detalhe) / 100, 2, false))
            ->setValorDesconto(Util::nFloat($this->rem(241, 253, $detalhe) / 100, 2, false))
            ->setValorRecebido(Util::nFloat($this->rem(254, 266, $detalhe) / 100, 2, false))
            ->setValorMora(Util::nFloat($this->rem(267, 279, $detalhe) / 100, 2, false))
            ->setValorMulta(Util::nFloat($this->rem(280, 292, $detalhe) / 100, 2, false));

        $codErro = str_split(sprintf('%016s', $this->rem(378, 393, $detalhe)), 4) + array_fill(0, 4, '');

        if ($d->hasOcorrencia('06', '08')) {
            $this->totais['liquidados']++;
            $d->setOcorrenciaTipo($d::OCORRENCIA_LIQUIDADA);
        } elseif ($d->hasOcorrencia('02')) {
            $this->totais['entradas']++;
            $d->setOcorrenciaTipo($d::OCORRENCIA_ENTRADA);
        } elseif ($d->hasOcorrencia('07', '09', '10', '32', '72')) {
            $this->totais['baixados']++;
            $d->setOcorrenciaTipo($d::OCORRENCIA_BAIXADA);
        } elseif ($d->hasOcorrencia('23')) {
            $this->totais['protestados']++;
            $d->setOcorrenciaTipo($d::OCORRENCIA_PROTESTADA);
        } elseif ($d->hasOcorrencia('04', '05', '14')) {
            $this->totais['alterados']++;
            $d->setOcorrenciaTipo($d::OCORRENCIA_ALTERACAO);
        } elseif ($d->hasOcorrencia('03', '15', '16', '17', '90')) {
            $this->totais['erros']++;
            $error = Util::appendStrings(
                Arr::get($this->rejeicoes, $codErro[0], ''),
                Arr::get($this->rejeicoes, $codErro[1], ''),
                Arr::get($this->rejeicoes, $codErro[2], ''),
                Arr::get($this->rejeicoes, $codErro[3], '')
            );
            if (in_array($codErro[0], ['9005', '9006', '9007', '9008'])
                || in_array($codErro[1], ['9005', '9006', '9007', '9008'])
                || in_array($codErro[2], ['9005', '9006', '9007', '9008'])
                || in_array($codErro[3], ['9005', '9006', '9007', '9008'])) {
                $posicaoInvalida = str_split(sprintf('%012s', $this->rem(366, 377, $detalhe)), 3) + array_fill(0, 4, '');
                $error .= Util::appendStrings(
                    Arr::get($this->campoInvalido, $posicaoInvalida[0], ''),
                    Arr::get($this->campoInvalido, $posicaoInvalida[1], ''),
                    Arr::get($this->campoInvalido, $posicaoInvalida[2], ''),
                    Arr::get($this->campoInvalido, $posicaoInvalida[3], '')
                );
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
            ->setQuantidadeTitulos($this->rem(17, 22, $trailer))
            ->setValorTitulos(Util::nFloat($this->rem(3, 16, $trailer) / 100, 2, false))
            ->setQuantidadeErros((int) $this->totais['erros'])
            ->setQuantidadeEntradas((int) $this->totais['entradas'])
            ->setQuantidadeLiquidados((int) $this->rem(37, 42, $trailer))
            ->setQuantidadeBaixados((int) $this->totais['baixados'])
            ->setQuantidadeAlterados((int) $this->totais['alterados']);

        return true;
    }
}
