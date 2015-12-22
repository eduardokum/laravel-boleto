<?php
namespace Eduardokum\LaravelBoleto\Cnab\Retorno\Banco;

use Carbon\Carbon;
use Eduardokum\LaravelBoleto\Cnab\Contracts\Retorno;
use Eduardokum\LaravelBoleto\Cnab\Retorno\AbstractCnab;
use Eduardokum\LaravelBoleto\Cnab\Retorno\Detalhe;
use Eduardokum\LaravelBoleto\Util;

class Bradesco extends AbstractCnab implements Retorno
{

    public $agencia;
    public $conta;

    private $motivosProtesto = array(
        "A" => "Aceito",
        "D" => "Desprezado",
        "X" => "",
    );

    private $ocorrencias = array(
        "02" => "Entrada Confirmada (verificar motivo na posição 319 a 328 )",
        "03" => "Entrada Rejeitada ( verificar motivo na posição 319 a 328)",
        "06" => "Liquidação normal (sem motivo)",
        "09" => "Baixado Automat. via Arquivo (verificar motivo posição 319 a 328) ",
        "10" => "Baixado conforme instruções da Agência(verificar motivo pos.319 a 328)",
        "11" => "Em Ser - Arquivo de Títulos pendentes (sem motivo)",
        "12" => "Abatimento Concedido (sem motivo)",
        "13" => "Abatimento Cancelado (sem motivo)",
        "14" => "Vencimento Alterado (sem motivo)",
        "15" => "Liquidação em Cartório (sem motivo)",
        "16" => "Título Pago em Cheque - Vinculado",
        "17" => "Liquidação após baixa ou Título não registrado (sem motivo)",
        "18" => "Acerto de Depositária (sem motivo)",
        "19" => "Confirmação Receb. Inst. de Protesto (verificar motivo pos.295 a 295)",
        "20" => "Confirmação Recebimento Instrução Sustação de Protesto (sem motivo)",
        "21" => "Acerto do Controle do Participante (sem motivo)",
        "22" => "Título Com Pagamento Cancelado",
        "23" => "Entrada do Título em Cartório (sem motivo)",
        "24" => "Entrada rejeitada por CEP Irregular (verificar motivo pos.319 a 328)",
        "27" => "Baixa Rejeitada (verificar motivo posição 319 a 328)",
        "28" => "Débito de tarifas/custas (verificar motivo na posição 319 a 328)",
        "30" => "Alteração de Outros Dados Rejeitados (verificar motivo pos.319 a 328)",
        "32" => "Instrução Rejeitada (verificar motivo posição 319 a 328)",
        "33" => "Confirmação Pedido Alteração Outros Dados (sem motivo)",
        "34" => "Retirado de Cartório e Manutenção Carteira (sem motivo)",
        "35" => "Desagendamento do débito automático (verificar motivos pos. 319 a 328)",
        "40" => "Estorno de pagamento (Novo)",
        "55" => "Sustado judicial (Novo)",
        "68" => "Acerto dos dados do rateio de Crédito (verificar motivo posição de status do registro tipo 3)",
        "69" => "Cancelamento dos dados do rateio (verificar motivo posição de status do registro tipo 3)",
        "XX" => "Desconhecida",
    );

    public function __construct($file)
    {
        parent::__construct($file);

        $this->banco = self::COD_BANCO_BRADESCO;
        $this->agencia = (int)substr($this->file[0], 26, 4);
        $this->conta = (int)substr($this->file[0], 32, 5);
    }

    protected function processarHeader(array $header)
    {
        $this->header->operacaoCodigo = $this->rem(2, 2, $header);
        $this->header->operacao = $this->rem(3, 9, $header);
        $this->header->servicoCodigo = $this->rem(10, 11, $header);
        $this->header->servico = $this->rem(12, 26, $header);
        $this->header->cedenteCodigo = (int)$this->rem(27, 46, $header);
        $this->header->cedenteNome = $this->rem(47, 76, $header);
        $this->header->data = $this->rem(95, 100, $header);
        $this->header->dataCredito = $this->rem(380, 394, $header);

        $this->header->data = trim($this->header->data, '0 ') == "" ? null : Carbon::createFromFormat('dmy', $this->header->data)->setTime(0, 0, 0);
        $this->header->data = $this->header->get('dataCredito', false, true) ?  Carbon::createFromFormat('dmy', $this->header->get('dataCredito'))->setTime(0, 0, 0) : null;
    }

    protected function processarDetalhe(array $detalhe)
    {
        $i = $this->i;
        $this->detalhe[$i] = new Detalhe($detalhe);
        $this->detalhe[$i]->numeroControle = Util::controle2array($this->rem(38, 62, $detalhe));
        $this->detalhe[$i]->nossoNumero = $this->rem(71, 82, $detalhe);
        $this->detalhe[$i]->ocorrencia = $this->rem(109, 110, $detalhe);
        $this->detalhe[$i]->numeroDocumento = $this->rem(117, 126, $detalhe);
        $this->detalhe[$i]->numero = $this->rem(127, 146, $detalhe);
        $this->detalhe[$i]->dataOcorrencia = $this->rem(111, 116, $detalhe);
        $this->detalhe[$i]->dataVencimento = $this->rem(147, 152, $detalhe);
        $this->detalhe[$i]->dataCredito = $this->rem(296, 301, $detalhe);
        $this->detalhe[$i]->bancoCobrador = $this->rem(166, 168, $detalhe);
        $this->detalhe[$i]->agenciaCobradora = $this->rem(169, 173, $detalhe);
        $this->detalhe[$i]->especie = $this->rem(174, 175, $detalhe);
        $this->detalhe[$i]->valor = Util::nFloat($this->rem(153, 165, $detalhe)/100);
        $this->detalhe[$i]->valorTarifa = Util::nFloat($this->rem(176, 188, $detalhe)/100);
        $this->detalhe[$i]->valorCustaProtesto = Util::nFloat($this->rem(189, 201, $detalhe)/100);
        $this->detalhe[$i]->valorAtraso = Util::nFloat($this->rem(202, 214, $detalhe)/100);
        $this->detalhe[$i]->valorIOF = Util::nFloat($this->rem(215, 227, $detalhe)/100);
        $this->detalhe[$i]->valorAbatimento = Util::nFloat($this->rem(228, 240, $detalhe)/100);
        $this->detalhe[$i]->valorDesconto = Util::nFloat($this->rem(241, 253, $detalhe)/100);
        $this->detalhe[$i]->valorRecebido = Util::nFloat($this->rem(254, 266, $detalhe)/100);
        $this->detalhe[$i]->valorMora = Util::nFloat($this->rem(267, 279, $detalhe)/100);
        $this->detalhe[$i]->valorOutrosCreditos = Util::nFloat($this->rem(280, 292, $detalhe)/100);

        $this->detalhe[$i]->motivoProtesto = $this->rem(295, 295, $detalhe);
        $this->detalhe[$i]->rejeicao = str_split($this->rem(319, 328, $detalhe), 2);
        $this->detalhe[$i]->numeroCartorio = $this->rem(369, 370, $detalhe);
        $this->detalhe[$i]->numeroProtocolo = $this->rem(371, 380, $detalhe);

        $this->detalhe[$i]->bancoCobradorNome = $this->bancos[$this->detalhe[$i]->get('bancoCobrador', 'XXX', true)];
        $this->detalhe[$i]->motivoProtestoNome = $this->motivosProtesto[$this->detalhe[$i]->get('motivoProtesto', 'X', true)];
        $this->detalhe[$i]->especieNome = 'Desconhecida';
        $this->detalhe[$i]->ocorrenciaNome = $this->ocorrencias[$this->detalhe[$i]->get('ocorrencia', 'XX', true)];

        $this->detalhe[$i]->dataOcorrencia = $this->detalhe[$i]->get('dataOcorrencia', false, true) ? Carbon::createFromFormat('dmy', $this->detalhe[$i]->get('dataOcorrencia'))->setTime(0, 0, 0) : null;
        $this->detalhe[$i]->dataVencimento = $this->detalhe[$i]->get('dataVencimento', false, true) ? Carbon::createFromFormat('dmy', $this->detalhe[$i]->get('dataVencimento'))->setTime(0, 0, 0) : null;
        $this->detalhe[$i]->dataCredito = $this->detalhe[$i]->get('dataCredito', false, true) ? Carbon::createFromFormat('dmy', $this->detalhe[$i]->get('dataCredito'))->setTime(0, 0, 0) : null;

        if(in_array($this->detalhe[$i]->get('ocorrencia'), ['06','15','17']))
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
        elseif(in_array($this->detalhe[$i]->get('ocorrencia'), ['03','24','27','30','32']))
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
        $this->trailer->quantidadeTitulos = (int)$this->rem(18, 25, $trailer);
        $this->trailer->valorTitulos = Util::nFloat($this->rem(26, 39, $trailer)/100);
        $this->trailer->avisos = (int)$this->rem(40, 47, $trailer);
        $this->trailer->quantidadeErros = (int)$this->totais['erros'];
        $this->trailer->quantidadeEntradas = (int)$this->totais['entradas'];
        $this->trailer->quantidadeLiquidados = (int)$this->totais['liquidados'];
        $this->trailer->quantidadeBaixados = (int)$this->totais['baixados'];
        $this->trailer->quantidadeAlterados = (int)$this->totais['alterados'];
    }


}