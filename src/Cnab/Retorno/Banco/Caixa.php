<?php
namespace Eduardokum\LaravelBoleto\Cnab\Retorno\Banco;

use Carbon\Carbon;
use Eduardokum\LaravelBoleto\Cnab\Contracts\Retorno;
use Eduardokum\LaravelBoleto\Cnab\Retorno\AbstractCnab;
use Eduardokum\LaravelBoleto\Cnab\Retorno\Detalhe;
use Eduardokum\LaravelBoleto\Util;

class Caixa extends AbstractCnab implements Retorno
{

    public $codigoTransmissao;

    public function __construct($file)
    {
        parent::__construct($file);

        $this->banco = self::COD_BANCO_CEF;
        $this->codigoTransmissao = (int)substr($this->file[0], 26, 16);
    }

    private $ocorrencias = array(
        '01' => 'Entrada Confirmada',
        '02' => 'Baixa Confirmada',
        '03' => 'Abatimento Concedido',
        '04' => 'Abatimento Cancelado',
        '05' => 'Vencimento Alterado',
        '06' => 'Uso da Empresa Alterado',
        '07' => 'Prazo de Protesto Alterado',
        '08' => 'Prazo de Devolução Alterado',
        '09' => 'Alteração Confirmada',
        '10' => 'Alteração com Reemissão de Bloqueto Confirmada',
        '11' => 'Alteração da Opção de Protesto para Devolução',
        '12' => 'Alteração da Opção de Devolução para protesto',
        '20' => 'Em Ser',
        '21' => 'Liquidação',
        '22' => 'Liquidação em Cartório',
        '23' => 'Baixa por Devolução',
        '24' => 'Baixa por Franco Pagamento',
        '25' => 'Baixa por Protesto',
        '26' => 'Título enviado para Cartório',
        '27' => 'Sustação de Protesto',
        '28' => 'Estorno de Protesto',
        '29' => 'Estorno de Sustação de Protesto',
        '30' => 'Alteração de Título',
        '31' => 'Tarifa sobre Título Vencido',
        '32' => 'Outras Tarifas de Alteração',
        '33' => 'Estorno de Baixa/Liquidação',
        '34' => 'Transferência de Carteira/Entrada',
        '35' => 'Transferência de Carteira/Baixa',
        '99' => 'Rejeição do Título – Cód. Rejeição informado nas POS 80 a 82'
    );

    private $rejeicoes = array(
        '01' => 'Movimento sem Cedente Correspondente ',
        '02' => 'Movimento sem Título Correspondente',
        '08' => 'Movimento para Título já com Movimentação no dia ',
        '09' => 'Nosso Número não Pertence ao Cedente',
        '10' => 'Inclusão de Título já Existente',
        '12' => 'Movimento Duplicado',
        '13' => 'Entrada Inválida para Cobrança Caucionada (Cedente não possui conta Caução)',
        '20' => 'CEP do Sacado não Encontrado (Não foi possível a Determinação da Agência Cobradora para o Título) ',
        '21' => 'Agência Cobradora não Encontrada (Agência Designada para Cobradora não Cadastrada no Sistema) ',
        '22' => 'Agência Cedente não Encontrada (Agência do Cedente não Cadastrada no Sistema)',
        '45' => 'Data de Vencimento com prazo mais de 1 ano',
        '49' => 'Movimento Inválido para Título Baixado/Liquidado',
        '50' => 'Movimento Inválido para Título enviado ao Cartório',
        '54' => 'Faixa de CEP da Agência Cobradora não Abrange CEP do Sacado',
        '55' => 'Título já com Opção de Devolução',
        '56' => 'Processo de Protesto em Andamento',
        '57' => 'Título já com Opção de Protesto',
        '58' => 'Processo de Devolução em Andamento',
        '59' => 'Novo Prazo p/ Protesto/Devolução Inválido',
        '76' => 'Alteração de Prazo de Protesto Inválida',
        '77' => 'Alteração de Prazo de Devolução Inválida',
        '81' => 'CEP do Sacado Inválido',
        '82' => 'CGC/CPF do Sacado Inválido (Dígito não Confere)',
        '83' => 'Número do Documento (Seu Número) inválido',
        '84' => 'Protesto inválido para título sem Número do Documento (Seu Número)',
        'XX' => 'Desconhecida',
    );

    private $especies = [
        '01' => 'Duplicata Mercantil',
        '02' => 'Nota Promissória',
        '03' => 'Duplicata de Prestação de Serviços',
        '05' => 'Nota de Seguro',
        '06' => 'Letra de Câmbio',
        '09' => 'Outros',
        'XX' => 'Desconhecida',
    ];

    private $liquidacoes = [
        '620' => 'Correspondente bancários',
        '639' => 'Outros Canais',
        '647' => 'Lotéricos',
        '655' => 'Guichê CAIXA',
        '663' => 'Compensação',
        'XXX' => 'Desconhecida',
    ];

    private $formasPagamento = [
        '1' => 'Dinheiro',
        '2' => 'Cheque',
        'X' => 'Desconhecida',
    ];

    protected function processarHeader(array $header)
    {
        $this->header->operacaoCodigo = $this->rem(2, 2, $header);
        $this->header->operacao = $this->rem(3, 9, $header);
        $this->header->servicoCodigo = $this->rem(10, 11, $header);
        $this->header->servico = $this->rem(12, 26, $header);
        $this->header->cedenteCodigo = $this->rem(27, 42, $header);
        $this->header->cedenteNome = $this->rem(47, 76, $header);
        $this->header->data = $this->rem(95, 100, $header);
        $this->header->mensagem = $this->rem(101, 158, $header);

        $this->header->data = trim($this->header->data, '0 ') == "" ? null : Carbon::createFromFormat('dmy', $this->header->data)->setTime(0, 0, 0);
    }

    protected function processarDetalhe(array $detalhe)
    {
        $i = $this->i;
        $this->detalhe[$i] = new Detalhe($detalhe);
        $this->detalhe[$i]->numeroControle = Util::controle2array($this->rem(38, 62, $detalhe));
        $this->detalhe[$i]->nossoNumero = $this->rem(63, 73, $detalhe);
        $this->detalhe[$i]->numeroDocumento = $this->rem(117, 126, $detalhe);
        $this->detalhe[$i]->rejeicaoCodigo = $this->rem(80, 82, $detalhe);
        $this->detalhe[$i]->ocorrencia = $this->rem(109, 110, $detalhe);
        $this->detalhe[$i]->dataOcorrencia = $this->rem(111, 116, $detalhe);
        $this->detalhe[$i]->dataVencimento = $this->rem(147, 152, $detalhe);
        $this->detalhe[$i]->dataCredito = $this->rem(294, 299, $detalhe);
        $this->detalhe[$i]->dataDebitoTarifa = $this->rem(195, 200, $detalhe);
        $this->detalhe[$i]->bancoCobrador = $this->rem(166, 168, $detalhe);
        $this->detalhe[$i]->agenciaCobradora = $this->rem(169, 173, $detalhe);
        $this->detalhe[$i]->especie = $this->rem(174, 175, $detalhe);

        $this->detalhe[$i]->liquidacaoCodigo = $this->rem(189, 191, $detalhe);
        $this->detalhe[$i]->formaPagamento = $this->rem(192, 192, $detalhe);
        $this->detalhe[$i]->valor = Util::nFloat($this->rem(153, 165, $detalhe)/100);
        $this->detalhe[$i]->valorTarifa = Util::nFloat($this->rem(176, 188, $detalhe)/100);
        $this->detalhe[$i]->valorIOF = Util::nFloat($this->rem(215, 227, $detalhe)/100);
        $this->detalhe[$i]->valorAbatimento = Util::nFloat($this->rem(228, 240, $detalhe)/100);
        $this->detalhe[$i]->valorDesconto = Util::nFloat($this->rem(241, 253, $detalhe)/100);
        $this->detalhe[$i]->valorRecebido = Util::nFloat($this->rem(254, 266, $detalhe)/100);
        $this->detalhe[$i]->valorMora = Util::nFloat($this->rem(267, 279, $detalhe)/100);
        $this->detalhe[$i]->valorMulta = Util::nFloat($this->rem(280, 292, $detalhe)/100);

        $this->detalhe[$i]->liquidacaoNome = 'Desconhecido';
        $this->detalhe[$i]->formaPagamentoNome = $this->formasPagamento[$this->detalhe[$i]->get('formaPagamento', 'X', true)];
        $this->detalhe[$i]->ocorrenciaNome = $this->ocorrencias[$this->detalhe[$i]->get('ocorrencia', 'XX', true)];
        $this->detalhe[$i]->especieNome = $this->especies[$this->detalhe[$i]->get('especie', 'XX', true)];
        $this->detalhe[$i]->bancoCobradorNome = $this->bancos[$this->detalhe[$i]->get('bancoCobrador', 'XXX', true)];

        $this->detalhe[$i]->dataOcorrencia = $this->detalhe[$i]->get('dataOcorrencia', false, true) ? Carbon::createFromFormat('dmy', $this->detalhe[$i]->get('dataOcorrencia'))->setTime(0, 0, 0) : null;
        $this->detalhe[$i]->dataVencimento = $this->detalhe[$i]->get('dataVencimento', false, true) ? Carbon::createFromFormat('dmy', $this->detalhe[$i]->get('dataVencimento'))->setTime(0, 0, 0) : null;
        $this->detalhe[$i]->dataCredito = $this->detalhe[$i]->get('dataCredito', false, true) ? Carbon::createFromFormat('dmy', $this->detalhe[$i]->get('dataCredito'))->setTime(0, 0, 0) : null;
        $this->detalhe[$i]->dataDebitoTarifa = $this->detalhe[$i]->get('dataDebitoTarifa', false, true) ? Carbon::createFromFormat('dmy', $this->detalhe[$i]->get('dataDebitoTarifa'))->setTime(0, 0, 0) : null;

        if(in_array($this->detalhe[$i]->get('ocorrencia'), ['21','22']))
        {
            $this->detalhe[$i]->liquidacaoNome = $this->liquidacoes[$this->detalhe[$i]->get('liquidacaoCodigo', 'XXX', true)];
            $this->totais['liquidados']++;
            $this->detalhe[$i]->setTipoOcorrencia(Detalhe::OCORRENCIA_LIQUIDADA);
        }
        elseif(in_array($this->detalhe[$i]->get('ocorrencia'), ['01']))
        {
            $this->totais['entradas']++;
            $this->detalhe[$i]->setTipoOcorrencia(Detalhe::OCORRENCIA_ENTRADA);
        }
        elseif(in_array($this->detalhe[$i]->get('ocorrencia'), ['02','23','24','25']))
        {
            $this->totais['baixados']++;
            $this->detalhe[$i]->setTipoOcorrencia(Detalhe::OCORRENCIA_BAIXADA);
        }
        elseif(in_array($this->detalhe[$i]->get('ocorrencia'), ['99']))
        {
            $this->totais['erros']++;
            $this->detalhe[$i]->setErro($this->rejeicoes[$this->detalhe[$i]->get('rejeicaoCodigo', 'XX', true)]);
        }
        else
        {
            $this->totais['alterados']++;
            $this->detalhe[$i]->setTipoOcorrencia(Detalhe::OCORRENCIA_ALTERACAO);
        }

        $this->i++;
    }

    protected function processarTrailer(array $trailer)
    {
        $this->trailer->quantidadeTitulos = (int)$this->i;
        $this->trailer->valorTitulos = Util::nFloat(0);
        $this->trailer->quantidadeErros = (int)$this->totais['erros'];
        $this->trailer->quantidadeEntradas = (int)$this->totais['entradas'];
        $this->trailer->quantidadeLiquidados = (int)$this->totais['liquidados'];
        $this->trailer->quantidadeBaixados = (int)$this->totais['baixados'];
        $this->trailer->quantidadeAlterados = (int)$this->totais['alterados'];
    }
}