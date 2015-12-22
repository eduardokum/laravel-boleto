<?php
namespace Eduardokum\LaravelBoleto\Cnab\Retorno\Banco;

use Carbon\Carbon;
use Eduardokum\LaravelBoleto\Cnab\Contracts\Retorno;
use Eduardokum\LaravelBoleto\Cnab\Retorno\AbstractCnab;
use Eduardokum\LaravelBoleto\Cnab\Retorno\Detalhe;
use Eduardokum\LaravelBoleto\Util;

class Itau extends AbstractCnab implements Retorno
{

    public $agencia;
    public $conta;

    private $ocorrencias = array(
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
        'XX' => 'Desconhecido',
    );

    private $especies = array(
        '01' => 'duplicata mercantil',
        '02' => 'nota promissória',
        '03' => 'nota de seguro',
        '04' => 'mensalidade escolar',
        '05' => 'recibo',
        '06' => 'contrato',
        '07' => 'cosseguros',
        '08' => 'duplicata de serviço',
        '09' => 'letra de câmbio',
        '13' => 'nota de débitos',
        '15' => 'documento de dívida',
        '16' => 'encargos condominiais',
        '17' => 'conta de prestação de serviços',
        '99' => 'diversos',
        'XX' => 'Desconhecido',
    );
    private $liquidacoes = array(
        'AA' => 'caixa eletrônico banco itaú - disponível',
        'AC' => 'pagamento em cartório automatizado - a compensar',
        'BC' => 'bancos correspondentes - disponível',
        'BF' => 'itaú bankfone - disponível',
        'BL' => 'itaú bankline - disponível',
        'B0' => 'outros bancos - recebimento off-line - a compensar',
        'B1' => 'outros bancos - pelo código de barras - a compensar',
        'B2' => 'outros bancos - pela linha digitável - a compensar',
        'B3' => 'outros bancos - pelo auto atendimento - a compensar',
        'B4' => 'outros bancos - recebimento em casa lotérica - a compensar',
        'B5' => 'outros bancos - correspondente - a compensar',
        'B6' => 'outros bancos - telefone - a compensar',
        'B7' => 'outros bancos - arquivo eletrônico (pagamento efetuado por meio de troca de arquivos) - a compensar',
        'CC' => 'agência itaú - com cheque de outro banco ou (cheque itaú)* - a compensar',
        'CI' => 'correspondente itaú - disponível',
        'CK' => 'sispag - sistema de contas a pagar itaú - disponível',
        'CP' => 'agência itaú - por débito em conta corrente, cheque itaú* ou dinheiro - disponível',
        'DG' => 'agência itaú - capturado em off-line - disponível',
        'LC' => 'pagamento em cartório de protesto com cheque - a compensar',
        'Q0' => 'agendamento - pagamento agendado via bankline ou outro canal eletrônico e liquidado na data indicada - disponível',
        'XX' => 'Desconhecido',
    );

    public function __construct($file)
    {
        parent::__construct($file);

        $this->banco = self::COD_BANCO_ITAU;
        $this->agencia = (int)substr($this->file[0], 26, 4);
        $this->conta = (int)substr($this->file[0], 32, 5);
    }

    protected function processarHeader(array $header)
    {
        $this->header->operacaoCodigo = $this->rem(2, 2, $header);
        $this->header->operacao = $this->rem(3, 9, $header);
        $this->header->servicoCodigo = $this->rem(10, 11, $header);
        $this->header->servico = $this->rem(12, 26, $header);
        $this->header->agencia = $this->rem(27, 30, $header);
        $this->header->conta = $this->rem(33, 37, $header);
        $this->header->contaDigito = $this->rem(38, 38, $header);
        $this->header->cedenteNome = $this->rem(47, 76, $header);
        $this->header->data = $this->rem(95, 100, $header);
        $this->header->dataCredito = $this->rem(114, 119, $header);

        $this->header->data = trim($this->header->data, '0 ') == "" ? null : Carbon::createFromFormat('dmy', $this->header->data)->setTime(0, 0, 0);
        $this->header->dataCredito = $this->header->get('dataCredito', false, true) ?  Carbon::createFromFormat('dmy', $this->header->get('dataCredito'))->setTime(0, 0, 0) : null;
    }

    protected function processarDetalhe(array $detalhe)
    {
        $i = $this->i;

        $this->detalhe[$i] = new Detalhe($detalhe);
        $this->detalhe[$i]->numeroControle = Util::controle2array($this->rem(38, 62, $detalhe));
        $this->detalhe[$i]->numero = $this->rem(63, 70, $detalhe);
        $this->detalhe[$i]->nossoNumero = $this->rem(86, 93, $detalhe);
        $this->detalhe[$i]->nossoNumeroDigito = $this->rem(94, 94, $detalhe);
        $this->detalhe[$i]->numeroDocumento = $this->rem(117, 126, $detalhe);
        $this->detalhe[$i]->ocorrencia = $this->rem(109, 110, $detalhe);
        $this->detalhe[$i]->dataOcorrencia = $this->rem(111, 116, $detalhe);
        $this->detalhe[$i]->dataCredito = $this->rem(296, 301, $detalhe);
        $this->detalhe[$i]->dataVencimento = $this->rem(147, 152, $detalhe);
        $this->detalhe[$i]->confTituloBanco = $this->rem(127, 134, $detalhe);
        $this->detalhe[$i]->bancoCobrador = $this->rem(166, 168, $detalhe);
        $this->detalhe[$i]->agenciaCobradora = $this->rem(169, 172, $detalhe);
        $this->detalhe[$i]->agenciaCobradoraDigito = $this->rem(173, 173, $detalhe);
        $this->detalhe[$i]->especie = $this->rem(174, 175, $detalhe);
        $this->detalhe[$i]->valor = Util::nFloat($this->rem(153, 165, $detalhe)/100);
        $this->detalhe[$i]->valorTarifa = Util::nFloat($this->rem(176, 188, $detalhe)/100);
        $this->detalhe[$i]->valorIOF = Util::nFloat($this->rem(215, 227, $detalhe)/100);
        $this->detalhe[$i]->valorAbatimento = Util::nFloat($this->rem(228, 240, $detalhe)/100);
        $this->detalhe[$i]->valorDesconto = Util::nFloat($this->rem(241, 253, $detalhe)/100);
        $this->detalhe[$i]->valorRecebido = Util::nFloat($this->rem(254, 266, $detalhe)/100);
        $this->detalhe[$i]->valorMora = Util::nFloat($this->rem(267, 279, $detalhe)/100);
        $this->detalhe[$i]->valorOutrosCreditos = Util::nFloat($this->rem(280, 292, $detalhe)/100);
        $this->detalhe[$i]->valorComplementar = Util::nFloat($this->rem(312, 324, $detalhe)/100);
        $this->detalhe[$i]->dda = $this->rem(293, 293, $detalhe);
        $this->detalhe[$i]->instrucaoCancelada = $this->rem(302, 305, $detalhe);
        $this->detalhe[$i]->dataComplementar = $this->rem(306, 311, $detalhe);
        $this->detalhe[$i]->sacadoNome = $this->rem(325, 354, $detalhe);
        $this->detalhe[$i]->motivosRejeicao = str_split($this->rem(378, 385, $detalhe), 2);
        $this->detalhe[$i]->liquidacaoCodigo = $this->rem(393, 394, $detalhe);

        $this->detalhe[$i]->liquidacaoNome = 'Desconhecido';
        $this->detalhe[$i]->ocorrenciaNome = $this->ocorrencias[$this->detalhe[$i]->get('ocorrencia', 'XX', true)];
        $this->detalhe[$i]->especieNome = $this->especies[$this->detalhe[$i]->get('especie', 'XX', true)];
        $this->detalhe[$i]->bancoCobradorNome = $this->bancos[$this->detalhe[$i]->get('bancoCobrador', 'XXX', true)];

        $this->detalhe[$i]->dataOcorrencia = $this->detalhe[$i]->get('dataOcorrencia', false, true) ? Carbon::createFromFormat('dmy', $this->detalhe[$i]->get('dataOcorrencia'))->setTime(0, 0, 0) : null;
        $this->detalhe[$i]->dataVencimento = $this->detalhe[$i]->get('dataVencimento', false, true) ? Carbon::createFromFormat('dmy', $this->detalhe[$i]->get('dataVencimento'))->setTime(0, 0, 0) : null;
        $this->detalhe[$i]->dataCredito = $this->detalhe[$i]->get('dataCredito', false, true) ? Carbon::createFromFormat('dmy', $this->detalhe[$i]->get('dataCredito'))->setTime(0, 0, 0) : null;
        $this->detalhe[$i]->dataComplementar = $this->detalhe[$i]->get('dataComplementar', false, true) ? Carbon::createFromFormat('dmy', $this->detalhe[$i]->get('dataComplementar'))->setTime(0, 0, 0) : null;

        if(in_array($this->detalhe[$i]->get('ocorrencia'), ['06','07','08','10']))
        {
            $this->detalhe[$i]->liquidacaoNome = $this->liquidacoes[$this->detalhe[$i]->get('liquidacaoCodigo', 'XX', true)];
            $this->totais['liquidados']++;
            $this->detalhe[$i]->setTipoOcorrencia(Detalhe::OCORRENCIA_LIQUIDADA);
        }
        elseif(in_array($this->detalhe[$i]->get('ocorrencia'), ['02','64','71','73']))
        {
            $this->totais['entradas']++;
            $this->detalhe[$i]->setTipoOcorrencia(Detalhe::OCORRENCIA_ENTRADA);
        }
        elseif(in_array($this->detalhe[$i]->get('ocorrencia'), ['05','09','32','47','59','72']))
        {
            $this->totais['baixados']++;
            $this->detalhe[$i]->setTipoOcorrencia(Detalhe::OCORRENCIA_BAIXADA);
        }
        elseif(in_array($this->detalhe[$i]->get('ocorrencia'), ['03','15','16','60','03',]))
        {
            $this->totais['erros']++;
            $this->detalhe[$i]->setErro('Desconhecido');
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
        $this->trailer->quantidadeTitulos = (int)$this->rem(18, 25, $trailer) + (int)$this->rem(58, 65, $trailer) + (int)$this->rem(178, 185, $trailer);
        $this->trailer->valorTitulos = Util::nFloat($this->rem(221, 234, $trailer)/100);
        $this->trailer->quantidadeErros = (int)$this->totais['erros'];
        $this->trailer->quantidadeEntradas = (int)$this->totais['entradas'];
        $this->trailer->quantidadeLiquidados = (int)$this->totais['liquidados'];
        $this->trailer->quantidadeBaixados = (int)$this->totais['baixados'];
        $this->trailer->quantidadeAlterados = (int)$this->totais['alterados'];
    }
}