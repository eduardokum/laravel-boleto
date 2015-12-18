<?php
namespace Eduardokum\LaravelBoleto\Cnab\Retorno\Banco;

use Carbon\Carbon;
use Eduardokum\LaravelBoleto\Cnab\Contracts\Retorno;
use Eduardokum\LaravelBoleto\Cnab\Retorno\AbstractCnab;
use Eduardokum\LaravelBoleto\Cnab\Retorno\Detalhe;
use Eduardokum\LaravelBoleto\Util;

class Bb extends AbstractCnab implements Retorno
{

    public $agencia;
    public $conta;

    private $ocorrencias = [
        '02' => 'Confirmação de Entrada de Título',
        '03' => 'Comando recusado (Motivo indicado na posição 087/088)',
        '05' => 'Liquidado sem registro (carteira 17-tipo4)',
        '06' => 'Liquidação Normal',
        '07' => 'Liquidação por Conta',
        '08' => 'Liquidação por Saldo',
        '09' => 'Baixa de Titulo',
        '10' => 'Baixa Solicitada',
        '11' => 'Títulos em Ser (constara somente do arquivo de existência de cobrança, fornecido mediante solicitação do cliente)',
        '12' => 'Abatimento Concedido',
        '13' => 'Abatimento Cancelado',
        '14' => 'Alteração de Vencimento do título',
        '15' => 'Liquidação em Cartório',
        '16' => 'Confirmação de alteração de juros de mora',
        '19' => 'Confirmação de recebimento de instruções para protesto',
        '20' => 'Debito em Conta',
        '21' => 'Alteração do Nome do Sacado',
        '22' => 'Alteração do Endereço do Sacado',
        '23' => 'Indicação de encaminhamento a cartório',
        '24' => 'Sustar Protesto',
        '25' => 'Dispensar Juros de mora',
        '26' => 'Alteração do número do título dado pelo Cedente (Seu número) – 10 e 15 posições',
        '28' => 'Manutenção de titulo vencido',
        '31' => 'Conceder desconto',
        '32' => 'Não conceder desconto',
        '33' => 'Retificar desconto',
        '34' => 'Alterar data para desconto',
        '35' => 'Cobrar Multa',
        '36' => 'Dispensar Multa',
        '37' => 'Dispensar Indexador',
        '38' => 'Dispensar prazo limite para recebimento',
        '39' => 'Alterar prazo limite para recebimento',
        '41' => 'Alteração do número do controle do participante (25 posições)',
        '42' => 'Alteração do número do documento do sacado (CNPJ/CPF)',
        '44' => 'Título pago com cheque devolvido',
        '46' => 'Título pago com cheque, aguardando compensação',
        '72' => 'Alteração de tipo de cobrança (específico para títulos das carteiras 11 e 17)',
        '96' => 'Despesas de Protesto',
        '97' => 'Despesas de Sustação de Protesto',
        '98' => 'Debito de Custas Antecipadas',
        'XX' => 'Desconhecido',
    ];

    private $especies = [
        '00' => 'informado nos registros com comando',
        '01' => 'duplicata mercantil',
        '02' => 'nota promissória',
        '03' => 'nota de seguro',
        '05' => 'recibo',
        '08' => 'letra de cambio',
        '09' => 'warrant',
        '10' => 'cheque',
        '12' => 'duplicata de serviço',
        '13' => 'nota de debito',
        '15' => 'apólice de seguro',
        '25' => 'divida ativa da União',
        '26' => 'divida ativa de Estado',
        '27' => 'divida ativa de Município',
        '97' => 'Despesas de Sustação de Protesto nas posições 109/110 desde que o titulo não conste mais da existência',
        'XX' => 'Desconhecido',
    ];

    private $origensPagamento = [
        '00' => 'Não é Sacado Eletrônico no DDA',
        '01' => 'terminal de auto-atendimento',
        '02' => 'internet',
        '03' => 'central de atendimento (URA)',
        '04' => 'gerenciador financeiro',
        '05' => 'central de atendimento',
        '06' => 'outro canal de auto-atendimento',
        '07' => 'correspondente bancário',
        '08' => 'guichê de caixa',
        '09' => 'arquivo-eletrônico',
        '10' => 'compensação',
        '11' => 'outro canal eletrônico',
        '50' => 'Sacado eletrônico no DDA',
        'XX' => 'Desconhecido',
    ];

    private $tiposCobranca = array(
        '0' => 'Caso não haja alteração de tipo de cobrança',
        '1' => 'Simples',
        '2' => 'Vinculada',
        '4' => 'Descontada',
        '7' => 'Cobrança Simples Carteira 17',
        '8' => 'Vendor'
    );

    private $indicativosCredito = array(
        '0' => 'sem lançamento',
        '1' => 'débito',
        '2' => 'crédito'
    );

    private $rejeicoes = array(
        '01' => 'identificação inválida',
        '02' => 'variação da carteira inválida',
        '03' => 'valor dos juros por um dia inválido',
        '04' => 'valor do desconto inválido',
        '05' => 'espécie de título inválida para carteira/variação',
        '06' => 'espécie de valor invariável inválido',
        '07' => 'prefixo da agência usuária inválido',
        '08' => 'valor do título/apólice inválido',
        '09' => 'data de vencimento inválida',
        '10' => 'fora do prazo/só admissível na carteira',
        '11' => 'inexistência de margem para desconto',
        '12' => 'o banco não tem agência na praça do sacado',
        '13' => 'razões cadastrais',
        '14' => 'sacado interligado com o sacador (só admissível em cobrança simples- cart. 11 e 17)',
        '15' => 'Titulo sacado contra órgão do Poder Público (só admissível na carteira 11 e sem ordem de protesto)',
        '16' => 'Titulo preenchido de forma irregular',
        '17' => 'Titulo rasurado',
        '18' => 'Endereço do sacado não localizado ou incompleto',
        '19' => 'Código do cedente inválido',
        '20' => 'Nome/endereço do cliente não informado (ECT)',
        '21' => 'Carteira inválida',
        '22' => 'Quantidade de valor variável inválida',
        '23' => 'Faixa nosso-numero excedida',
        '24' => 'Valor do abatimento inválido',
        '25' => 'Novo número do título dado pelo cedente inválido (Seu número)',
        '26' => 'Valor do IOF de seguro inválido',
        '27' => 'Nome do sacado/cedente inválido',
        '28' => 'Data do novo vencimento inválida',
        '29' => 'Endereço não informado',
        '30' => 'Registro de título já liquidado (carteira 17-tipo 4)',
        '31' => 'Numero do borderô inválido',
        '32' => 'Nome da pessoa autorizada inválido',
        '33' => 'Nosso número já existente',
        '34' => 'Numero da prestação do contrato inválido',
        '35' => 'percentual de desconto inválido',
        '36' => 'Dias para fichamento de protesto inválido',
        '37' => 'Data de emissão do título inválida',
        '38' => 'Data do vencimento anterior à data da emissão do título',
        '39' => 'Comando de alteração indevido para a carteira',
        '40' => 'Tipo de moeda inválido',
        '41' => 'Abatimento não permitido',
        '42' => 'CEP/UF inválido/não compatíveis (ECT)',
        '43' => 'Código de unidade variável incompatível com a data de emissão do título',
        '44' => 'Dados para debito ao sacado inválidos',
        '45' => 'Carteira/variação encerrada',
        '46' => 'Convenio encerrado',
        '47' => 'Titulo tem valor diverso do informado',
        '48' => 'Motivo de baixa invalido para a carteira',
        '49' => 'Abatimento a cancelar não consta do título',
        '50' => 'Comando incompatível com a carteira',
        '51' => 'Código do convenente invalido',
        '52' => 'Abatimento igual ou maior que o valor do titulo',
        '53' => 'Titulo já se encontra na situação pretendida',
        '54' => 'Titulo fora do prazo admitido para a conta 1',
        '55' => 'Novo vencimento fora dos limites da carteira',
        '56' => 'Titulo não pertence ao convenente',
        '57' => 'Variação incompatível com a carteira',
        '58' => 'Impossível a variação única para a carteira indicada',
        '59' => 'Titulo vencido em transferência para a carteira 51',
        '60' => 'Titulo com prazo superior a 179 dias em variação única para carteira 51',
        '61' => 'Titulo já foi fichado para protesto',
        '62' => 'Alteração da situação de debito inválida para o código de responsabilidade',
        '63' => 'DV do nosso número inválido',
        '64' => 'Titulo não passível de débito/baixa – situação anormal',
        '65' => 'Titulo com ordem de não protestar – não pode ser encaminhado a cartório',
        '66' => 'Número do documento do sacado (CNPJ/CPF) inválido',
        '67' => 'Titulo/carne rejeitado',
        '68' => 'Código/Data/Percentual de multa inválido',
        '69' => 'Valor/Percentual de Juros Inválido',
        '70' => 'Título já se encontra isento de juros',
        '71' => 'Código de Juros Inválido',
        '72' => 'Prefixo da Ag. cobradora inválido',
        '73' => 'Numero do controle do participante inválido',
        '74' => 'Cliente não cadastrado no CIOPE (Desconto/Vendor)',
        '75' => 'Qtde. de dias do prazo limite p/ recebimento de título vencido inválido',
        '76' => 'Titulo excluído automaticamente por decurso de prazo CIOPE (Desconto/Vendor)',
        '77' => 'Titulo vencido transferido para a conta 1 – Carteira vinculada',
        '80' => 'Nosso numero inválido',
        '81' => 'Data para concessão do desconto inválida. Gerada nos seguintes casos: 11 - erro na data do desconto; 12 - data do desconto anterior à data de emissão',
        '82' => 'CEP do sacado inválido',
        '83' => 'Carteira/variação não localizada no cedente',
        '84' => 'Título não localizado na existência/Baixado por protesto',
        '84' => 'Titulo não localizado na existência',
        '99' => 'Outros motivos',
        'XX' => 'Desconhecido',
    );

    public function __construct($file)
    {
        parent::__construct($file);

        $this->banco = self::COD_BANCO_BB;
        $this->agencia = (int) substr($this->file[0], 26, 4);
        $this->conta = (int) substr($this->file[0], 31, 8);
    }

    protected function processarHeader(array $header)
    {
        $this->header->operacaoCodigo = $this->rem(2, 2, $header);
        $this->header->operacao = $this->rem(3, 9, $header);
        $this->header->servicoCodigo = $this->rem(10, 11, $header);
        $this->header->servico = $this->rem(12, 19, $header);
        $this->header->agencia = $this->rem(27, 30, $header);
        $this->header->agenciaDigito = $this->rem(31, 31, $header);
        $this->header->conta = $this->rem(32, 39, $header);
        $this->header->contaDigito = $this->rem(40, 40, $header);
        $this->header->cedenteNome = $this->rem(47, 76, $header);
        $this->header->data = $this->rem(95, 100, $header);
        $this->header->convenio = $this->rem(150, 156, $header);

        $this->header->data = trim($this->header->data, '0 ') == "" ? null : Carbon::createFromFormat('dmy', $this->header->data)->setTime(0, 0, 0);
    }

    protected function processarDetalhe(array $detalhe)
    {
        $i = $this->i;

        if($this->rem(64, 80, $detalhe) == '5')
        {
            return;
        }

        $this->detalhe[$i] = new Detalhe($detalhe);
        $this->detalhe[$i]->numeroControle = Util::controle2array($this->rem(39, 63, $detalhe));
        $this->detalhe[$i]->nossoNumero = $this->rem(64, 80, $detalhe);
        $this->detalhe[$i]->numeroDocumento = (int)$this->rem(117, 126, $detalhe);
        $this->detalhe[$i]->tipoCobranca = $this->rem(81, 81, $detalhe);
        $this->detalhe[$i]->tipoCobranca72 = $this->rem(82, 82, $detalhe);
        $this->detalhe[$i]->diasCalculo = (int)$this->rem(83, 86, $detalhe);
        $this->detalhe[$i]->naturezaRec = $this->rem(87, 88, $detalhe);
        $this->detalhe[$i]->contaCaucao = $this->rem(95, 95, $detalhe);
        $this->detalhe[$i]->ocorrencia = $this->rem(109, 110, $detalhe);
        $this->detalhe[$i]->dataOcorrencia = $this->rem(111, 116, $detalhe);
        $this->detalhe[$i]->dataVencimento = $this->rem(147, 152, $detalhe);
        $this->detalhe[$i]->dataCredito = $this->rem(176, 181, $detalhe);
        $this->detalhe[$i]->bancoCobrador = $this->rem(166, 168, $detalhe);
        $this->detalhe[$i]->agenciaCobradora = $this->rem(169, 172, $detalhe);
        $this->detalhe[$i]->agenciaCobradoraDigito = $this->rem(173, 173, $detalhe);
        $this->detalhe[$i]->especie = $this->rem(174, 175, $detalhe);
        $this->detalhe[$i]->taxaDesconto = Util::nFloat($this->rem(96, 100, $detalhe));
        $this->detalhe[$i]->taxaIOF = Util::nFloat($this->rem(101, 105, $detalhe));
        $this->detalhe[$i]->valorTarifa = Util::nFloat($this->rem(182, 188, $detalhe)/100);
        $this->detalhe[$i]->valor = Util::nFloat($this->rem(153, 165, $detalhe)/100);
        $this->detalhe[$i]->valorOutros = Util::nFloat($this->rem(189, 201, $detalhe)/100);
        $this->detalhe[$i]->valorJurosDesconto = Util::nFloat($this->rem(202, 214, $detalhe)/100);
        $this->detalhe[$i]->valorIOFDesconto = Util::nFloat($this->rem(215, 227, $detalhe)/100);
        $this->detalhe[$i]->valorAbatimento = Util::nFloat($this->rem(228, 240, $detalhe)/100);
        $this->detalhe[$i]->valorDesconto = Util::nFloat($this->rem(241, 253, $detalhe)/100);
        $this->detalhe[$i]->valorRecebido = Util::nFloat($this->rem(254, 266, $detalhe)/100);
        $this->detalhe[$i]->valorMora = Util::nFloat($this->rem(267, 279, $detalhe)/100);
        $this->detalhe[$i]->valorOutrosCreditos = Util::nFloat($this->rem(280, 292, $detalhe)/100);
        $this->detalhe[$i]->valorAbatidosNaoAprovados = Util::nFloat($this->rem(293, 305, $detalhe)/100);
        $this->detalhe[$i]->valorLacamento = Util::nFloat($this->rem(306, 318, $detalhe)/100);
        $this->detalhe[$i]->valorAjuste = Util::nFloat($this->rem(321, 332, $detalhe)/100);
        $this->detalhe[$i]->indicativoCredito = $this->rem(319, 319, $detalhe);
        $this->detalhe[$i]->indicativoValor = $this->rem(320, 320, $detalhe);
        $this->detalhe[$i]->origemPagamento = $this->rem(393, 394, $detalhe);

        $this->detalhe[$i]->tipoCobrancaNome = $this->tiposCobranca[$this->detalhe[$i]->get('tipoCobranca', $this->detalhe[$i]->get('tipoCobranca72'))];
        $this->detalhe[$i]->ocorrenciaNome = $this->ocorrencias[$this->detalhe[$i]->get('ocorrencia', 'XX', true)];
        $this->detalhe[$i]->bancoCobradorNome = $this->bancos[$this->detalhe[$i]->get('bancoCobrador', 'XXX', true)];
        $this->detalhe[$i]->especieNome = $this->especies[$this->detalhe[$i]->get('especie', 'XX', true)];
        $this->detalhe[$i]->indicativoCreditoNome = $this->indicativosCredito[$this->detalhe[$i]->get('indicativoCredito')];
        $this->detalhe[$i]->origemPagamentoNome = $this->origensPagamento[$this->detalhe[$i]->get('origemPagamento', 'XX', true)];

        $this->detalhe[$i]->dataOcorrencia = $this->detalhe[$i]->get('dataOcorrencia', false, true) ? Carbon::createFromFormat('dmy', $this->detalhe[$i]->get('dataOcorrencia'))->setTime(0, 0, 0) : null;
        $this->detalhe[$i]->dataVencimento = $this->detalhe[$i]->get('dataVencimento', false, true) ? Carbon::createFromFormat('dmy', $this->detalhe[$i]->get('dataVencimento'))->setTime(0, 0, 0) : null;
        $this->detalhe[$i]->dataCredito = $this->detalhe[$i]->get('dataCredito', false, true) ? Carbon::createFromFormat('dmy', $this->detalhe[$i]->get('dataCredito'))->setTime(0, 0, 0) : null;

        if(in_array($this->detalhe[$i]->get('ocorrencia'), ['05','06','07','08','15']))
        {
            $this->totais['liquidados']++;
            $this->detalhe[$i]->setTipoOcorrencia(Detalhe::OCORRENCIA_LIQUIDADA);
        }
        elseif(in_array($this->detalhe[$i]->get('ocorrencia'), ['02']))
        {
            $this->totais['entradas']++;
            $this->detalhe[$i]->setTipoOcorrencia(Detalhe::OCORRENCIA_ENTRADA);
        }
        elseif(in_array($this->detalhe[$i]->get('ocorrencia'), ['09','10']))
        {
            $this->totais['baixados']++;
            $this->detalhe[$i]->setTipoOcorrencia(Detalhe::OCORRENCIA_BAIXADA);
        }
        elseif(in_array($this->detalhe[$i]->get('ocorrencia'), ['03']))
        {
            $this->totais['erros']++;
            $this->detalhe[$i]->setErro($this->rejeicoes[$this->detalhe[$i]->get('ocorrencia', 'XX', true)]);
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
        $this->trailer->quantidadeTitulos = (int)$this->rem(18, 25, $trailer);
        $this->trailer->valorTitulos = Util::nFloat($this->rem(26, 39, $trailer)/100);
        $this->trailer->avisos = Util::nFloat($this->rem(40, 47, $trailer));
        $this->trailer->quantidadeErros = (int)$this->totais['erros'];
        $this->trailer->quantidadeEntradas = (int)$this->totais['entradas'];
        $this->trailer->quantidadeLiquidados = (int)$this->totais['liquidados'];
        $this->trailer->quantidadeBaixados = (int)$this->totais['baixados'];
        $this->trailer->quantidadeAlterados = (int)$this->totais['alterados'];
    }

}