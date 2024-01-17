<?php

namespace Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab400\Banco;

use Illuminate\Support\Arr;
use Eduardokum\LaravelBoleto\Util;
use Eduardokum\LaravelBoleto\Contracts\Cnab\RetornoCnab400;
use Eduardokum\LaravelBoleto\Exception\ValidationException;
use Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab400\AbstractRetorno;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;

class Unicred extends AbstractRetorno implements RetornoCnab400
{
    /**
     * Código do banco
     *
     * @var string
     */
    protected $codigoBanco = BoletoContract::COD_BANCO_UNICRED;

    /**
     * Array com as ocorrencias do banco;
     *
     * @var array
     */
    private $ocorrencias = [
        '01' => 'Pago (Título protestado pago em cartório)',
        '02' => 'Instrução Confirmada',
        '03' => 'Instrução Rejeitada',
        '04' => 'Sustado Judicial (Título protestado sustado judicialmente)',
        '06' => 'Liquidação Normal',
        '07' => 'Liquidação em Condicional (Título liquidado em cartório com cheque do próprio devedor)',
        '08' => 'Sustado Definitivo (Título protestado sustado judicialmente)',
        '09' => 'Liquidação de Título Descontado',
        '10' => 'Protesto solicitado',
        '11' => 'Protesto Em cartório',
        '12' => 'Sustação solicitada',
        '13' => 'Títulos Descontado (título utilizado como garantia em operação de desconto)',
        '14' => 'Títulos Descontável (título com desistência de garantia em operação de desconto)',
    ];

    /**
     * Array com as possiveis rejeicoes do banco.
     *
     * @var array
     */
    private $rejeicoes = [
        '00'  => 'Sem Complemento a informar',
        '01'  => 'Código do Banco Inválido',
        '04'  => 'Código de Movimento não permitido para a carteira',
        '05'  => 'Código de Movimento Inválido',
        '06'  => 'Número de Inscrição do Beneficiário Inválido',
        '07'  => 'Agência - Conta Inválida',
        '08'  => 'Nosso Número Inválido',
        '09'  => 'Nosso Número Duplicado',
        '10'  => 'Carteira inválida',
        '12'  => 'Tipo de Documento Inválido',
        '15'  => 'Data de Vencimento inferior a 5 dias uteis para remessa gráfica',
        '16'  => 'Data de Vencimento Inválida',
        '17'  => 'Data de Vencimento Anterior à Data de Emissão',
        '18'  => 'Vencimento fora do Prazo de Operação',
        '20'  => 'Valor do Título Inválido',
        '24'  => 'Data de Emissão Inválida',
        '25'  => 'Data de Emissão Posterior à data de Entrega',
        '26'  => 'Código de juros inválido',
        '27'  => 'Valor de juros inválido',
        '28'  => 'Código de Desconto inválido',
        '29'  => 'Valor de Desconto inválido',
        '30'  => 'Alteração de Dados Rejeitada',
        '33'  => 'Valor de Abatimento Inválido',
        '34'  => 'Valor do Abatimento Maior ou Igual ao Valor do título',
        '37'  => 'Código para Protesto Inválido; (Protesto via SGR, não é CRA)',
        '38'  => 'Prazo para Protesto Inválido; (Protesto via SGR, não é CRA)',
        '39'  => 'Pedido de Protesto Não Permitido para o Título',
        '40'  => 'Título com Ordem de Protesto Emitida',
        '41'  => 'Pedido de Cancelamento/Sustação para Títulos sem Instrução de Protesto ou Instrução de Protesto não confirmada pelo cartório',
        '45'  => 'Nome do Pagador não informado',
        '46'  => 'Número de Inscrição do Pagador Inválido',
        '47'  => 'Endereço do Pagador Não Informado',
        '48'  => 'CEP Inválido',
        '52'  => 'Unidade Federativa Inválida',
        '57'  => 'Código de Multa inválido',
        '58'  => 'Data de Multa inválido',
        '59'  => 'Valor / percentual de Multa inválido',
        '60'  => 'Movimento para Título não Cadastrado',
        '63'  => 'Entrada para Título já cadastrado',
        '79'  => 'Data de Juros inválida',
        '80'  => 'Data de Desconto inválida',
        '86'  => 'Seu Número Inválido',
        'A5'  => 'Título Liquidado',
        'A8'  => 'Valor do Abatimento Inválido para Cancelamento',
        'C0'  => 'Sistema Intermitente – Entre em contato com sua Cooperativa',
        'C1'  => 'Situação do título Aberto',
        'C3'  => 'Status do Borderô Inválido',
        'C4'  => 'Nome do Beneficiário Inválido',
        'C5'  => 'Documento Inválido',
        'C6'  => 'Instrução não Atualiza Cadastro do Título',
        'C7'  => 'Título não registrado na CIP',
        'C8'  => 'Situação do Borderô inválida',
        'C9'  => 'Título inválido conforme situação CIP',
        'C10' => 'Protesto: Título precisa estar em Aberto',
        'D0'  => 'Beneficiário não autorizado a operar com produto Desconto',
        'D1'  => 'Alteração de status de desconto não permitido para título',
        'D2'  => 'Operação de desconto não permitida para título vencido',
        'D3'  => 'Alteração de status de desconto não permitido para situação do título',
        'E0'  => 'CEP indicado para o endereço do Pagador não compatível com os Correios',
        'E1'  => 'Logradouro para o endereço do Pagador não compatível com os Correios, para o CEP indicado',
        'E2'  => 'Tipo de logradouro para o endereço do Pagador não compatível com os Correios, para o CEP indicado',
        'E3'  => 'Bairro para o endereço do Pagador não compatível com os Correios, para o CEP indicado',
        'E4'  => 'Cidade para o endereço do Pagador não compatível com os Correios, para o CEP indicado',
        'E5'  => 'UF para o endereço do Pagador não compatível com os Correios, para o CEP indicado',
        'E6'  => 'Dados do segmento/registro opcional de endereço do pagador, incompletos no arquivo remessa',
        'E7'  => 'Beneficiário não autorizado a enviar boleto por e-mail',
        'E8'  => 'Indicativo para pagador receber boleto por e-mail sinalizado, porém sem o endereço do e-mail',
        'E9'  => 'Beneficiário não autorizado a enviar títulos para protesto',
        'E10' => 'Instrução ‘09 – Protestar’, usada erroneamente para título a vencer ou ainda dentro do período de Carência de ‘1 dia’ do vencimento, referente a liquidação por Compensação',
        'E11' => 'Instrução ‘26 – Protesto Automático’, usada erroneamente para título vencido',
        'E12' => 'Cancelamento de protesto automático não permitido, título não possui configuração de protesto automático',
        'E13' => 'Configuração de Número de Dias para Protesto, foi informado para cancelamento de protesto automático',
        'E14' => 'Configuração de Número de Dias para Protesto, não foi informado para protesto automático',
        'E15' => 'Cancelamento de protesto automático não permitido, para protesto já enviado a cartório',
        'E16' => 'Código para Protesto inválido',
        'E17' => 'Instrução não permitida para título descontado',
        'E18' => 'Configuração de Número de Dias para Protesto, foi informado para opção de não protestar',
        'E19' => 'Baixa por decurso de prazo foi encaminhada em duplicidade pela CIP',
        'E20' => 'Títulos com múltiplos pagamentos devem ter permissão para receber qualquer valor de pagamento',
        'E21' => 'Instrução não permitida para títulos com múltiplos pagamentos',
        'E22' => 'Funcionalidade para títulos com múltiplos pagamentos não está habilitada',
        'E23' => 'Quantidade de pagamentos parciais, deve ser 99',
        'E24' => 'Quantidade de pagamentos parciais não deve ser informado',
        'E25' => 'Modelo de calculo invalido para titulo com pagamentos parciais',
        'I0'  => 'Título possui baixa operacional ativa na cip',
        /**
         * Array com os códigos de Complemento do Movimento, relacionados a Protesto de título:
         */
        '101' => 'Data da apresentação inferior à data de vencimento',
        '102' => 'Falta de comprovante da prestação de serviço',
        '103' => 'Nome do sacado incompleto/incorreto',
        '104' => 'Nome do cedente incompleto/incorreto',
        '105' => 'Nome do sacador incompleto/incorreto',
        '106' => 'Endereço do sacado insuficiente',
        '107' => 'CNPJ/CPF do sacado inválido/incorreto',
        '108' => 'CNPJ/CPF incompatível c/ o nome do sacado/sacador/avalista',
        '109' => 'CNPJ/CPF do sacado incompatível com o tipo de documento',
        '110' => 'CNPJ/CPF do sacador incompatível com a espécie',
        '111' => 'Título aceito sem a assinatura do sacado',
        '112' => 'Título aceito rasurado ou rasgado',
        '113' => 'Título aceito – falta título (ag ced: enviar)',
        '114' => 'CEP incorreto',
        '115' => 'Praça de pagamento incompatível com endereço',
        '116' => 'Falta número do título',
        '117' => 'Título sem endosso do cedente ou irregular',
        '118' => 'Falta data de emissão do título',
        '119' => 'Título aceito: valor por extenso diferente do valor por numérico',
        '120' => 'Data de emissão posterior ao vencimento',
        '121' => 'Espécie inválida para protesto',
        '122' => 'CEP do sacado incompatível com a praça de protesto',
        '123' => 'Falta espécie do título',
        '124' => 'Saldo maior que o valor do título',
        '125' => 'Tipo de endosso inválido',
        '126' => 'Devolvido por ordem judicial',
        '127' => 'Dados do título não conferem com disquete',
        '128' => 'Sacado e Sacador/Avalista são a mesma pessoa',
        '129' => 'Corrigir a espécie do título',
        '130' => 'Aguardar um dia útil após o vencimento para protestar',
        '131' => 'Data do vencimento rasurada',
        '132' => 'Vencimento – extenso não confere com número',
        '133' => 'Falta data de vencimento no título',
        '134' => 'DM/DMI sem comprovante autenticado ou declaração',
        '135' => 'Comprovante ilegível para conferência e microfilmagem',
        '136' => 'Nome solicitado não confere com emitente ou sacado',
        '137' => 'Confirmar se são 2 emitentes. Se sim, indicar os dados dos 2',
        '138' => 'Endereço do sacado igual ao do sacador ou do portador',
        '139' => 'Endereço do apresentante incompleto ou não informado',
        '140' => 'Rua / Número inexistente no endereço',
        '141' => 'Informar a qualidade do endosso (M ou T)',
        '142' => 'Falta endosso do favorecido para o apresentante',
        '143' => 'Data da emissão rasurada',
        '144' => 'Protesto de cheque proibido – motivo 20/25/28/30 ou 35',
        '145' => 'Falta assinatura do emitente no cheque',
        '146' => 'Endereço do emitente no cheque igual ao do banco sacado',
        '147' => 'Falta o motivo da devolução no cheque ou motivo ilegível',
        '148' => 'Falta assinatura do sacador no título',
        '149' => 'Nome do apresentante não informado/incompleto/incorreto',
        '150' => 'Erro de preenchimento do título',
        '151' => 'Título com direito de regresso vencido',
        '152' => 'Título apresentado em duplicidade',
        '153' => 'Título já protestado',
        '154' => 'Letra de Câmbio vencida – falta aceite do sacado',
        '155' => 'Título – falta tradução por tradutor público',
        '156' => 'Falta declaração de saldo assinada no título',
        '157' => 'Contrato de Câmbio – falta conta gráfica',
        '158' => 'Ausência do Documento Físico',
        '159' => 'Sacado Falecido',
        '160' => 'Sacado Apresentou Quitação do Título',
        '161' => 'Título de outra jurisdição territorial',
        '162' => 'Título com emissão anterior à concordata do sacado',
        '163' => 'Sacado consta na lista de falência',
        '164' => 'Apresentante não aceita publicação de edital',
        '165' => 'Dados do sacador em branco ou inválido',
        '166' => 'Título sem autorização para protesto por edital',
        '167' => 'Valor divergente entre título e comprovante',
        '168' => 'Condomínio não pode ser protestado para fins falimentares',
        '169' => 'Vedada a intimação por edital para protesto falimentar',
        '170' => 'Dados do Cedente em branco ou inválido',
    ];

    /**
     * Array com os Códigos de Tipo de Instrução Origem (Posições 327 a 328 do retorno)
     *
     * @var array
     */
    private $codigos_tipo_instrucao_origem = [
        '00' => 'Sem Tipo de Instrução Origem a informar – usado para Código de Movimento 01; 06; 07; 09; 13 e 14',
        '01' => 'Remessa',
        '02' => 'Pedido de Baixa',
        '04' => 'Concessão de Abatimento',
        '05' => 'Cancelamento de Abatimento',
        '06' => 'Alteração de vencimento',
        '09' => 'Protestar',
        '10' => 'Baixa por Decurso de Prazo – Solicitação CIP',
        '11' => 'Sustar Protesto e Manter em Carteira',
        '22' => 'Alteracao do Seu Numero',
        '23' => 'Alteracao de dados do Pagador',
        '25' => 'Sustar Protesto e Baixar Título',
        '26' => 'Protesto automático',
        '40' => 'Alteracao de Status Desconto',
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
            ->setCodigoCliente($this->rem(27, 46, $header))
            ->setData($this->rem(95, 100, $header));

        $this->dataGeracaoArquivo = $this->rem(95, 100, $header);

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
                ->setAgencia($this->rem(18, 21, $detalhe))
                ->setConta($this->rem(23, 30, $detalhe))
                ->setContaDv($this->rem(31, 31, $detalhe));
        }
        /**
         * Retorno não possui informação de data de ocorrencia, apenas de data de quitação.
         * Portanto, quando não refere-se a quitação de valores esse campo vem preenchido com '000000', e nesse caso
         * será utilizada então a data de geração do arquivo como data de ocorrência
         */
        $this->dataOcorrencia = ((! empty($this->rem(111, 116, $detalhe)) && ($this->rem(111, 116, $detalhe) != '000000')) ? $this->rem(111, 116, $detalhe) : $this->dataGeracaoArquivo);

        $d = $this->detalheAtual();

        //NossoNúmero UNICRED só tem 11 dígitos, por isso da pra pegar apartir do campo 52 em vez de começar do 46
        //pois o resto dos campos será preenchido com valor '0'
        $d->setNossoNumero($this->rem(52, 62, $detalhe))
            ->setNumeroDocumento($this->rem(280, 305, $detalhe))  //SEU NUMERO
            // ->setNumeroControle($this->rem(38, 62, $detalhe))
            ->setOcorrencia($this->rem(109, 110, $detalhe)) //MOVIMENTO
            ->setOcorrenciaDescricao(Arr::get($this->ocorrencias, $d->getOcorrencia(), 'Desconhecida'))
            ->setDataOcorrencia($this->dataOcorrencia)//Data de geração do arquivo de remessa ou data de quitação do registro
            ->setDataVencimento($this->rem(147, 152, $detalhe))
            ->setDataCredito($this->rem(176, 181, $detalhe))
            ->setValor(Util::nFloat($this->rem(153, 165, $detalhe) / 100, 2, false))
            ->setValorTarifa(Util::nFloat($this->rem(182, 188, $detalhe) / 100, 2, false))
            // ->setValorIOF(Util::nFloat($this->rem(215, 227, $detalhe)/100, 2, false))
            ->setValorAbatimento(Util::nFloat($this->rem(228, 240, $detalhe) / 100, 2, false))
            ->setValorDesconto(Util::nFloat($this->rem(241, 253, $detalhe) / 100, 2, false))
            ->setValorRecebido(Util::nFloat($this->rem(254, 266, $detalhe) / 100, 2, false))
            ->setValorMora(Util::nFloat($this->rem(267, 279, $detalhe) / 100, 2, false));
        // ->setValorMulta(Util::nFloat($this->rem(280, 292, $detalhe)/100, 2, false));

        //Adicionar array_fill para garantir que array tenha 5 casas
        $msgAdicional = str_split($this->rem(319, 326, $detalhe), 2) + array_fill(0, 5, '');

        if ($d->hasOcorrencia('01', '06', '09')) { //'07'
            $this->totais['valor_recebido'] += $d->getValorRecebido();
            $this->totais['liquidados']++;
            $d->setOcorrenciaTipo($d::OCORRENCIA_LIQUIDADA);
        } elseif ($d->hasOcorrencia('02')) {
            $this->totais['entradas']++;
            $d->setOcorrenciaTipo($d::OCORRENCIA_ENTRADA);
        } elseif ($d->hasOcorrencia('09', '10')) {
            $this->totais['baixados']++;
            $d->setOcorrenciaTipo($d::OCORRENCIA_BAIXADA);
        } elseif ($d->hasOcorrencia('10', '11')) {
            $this->totais['protestados']++;
            $d->setOcorrenciaTipo($d::OCORRENCIA_PROTESTADA);
        } elseif ($d->hasOcorrencia('03')) {
            $this->totais['erros']++;
            $error = Util::appendStrings(Arr::get($this->rejeicoes, $msgAdicional[0], ''), Arr::get($this->rejeicoes, $msgAdicional[1], ''), Arr::get($this->rejeicoes, $msgAdicional[2], ''), Arr::get($this->rejeicoes, $msgAdicional[3], ''), Arr::get($this->rejeicoes, $msgAdicional[4], ''));

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
