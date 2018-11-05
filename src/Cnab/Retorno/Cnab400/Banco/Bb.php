<?php
namespace Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab400\Banco;

use Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab400\AbstractRetorno;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;
use Eduardokum\LaravelBoleto\Contracts\Cnab\RetornoCnab400;
use Eduardokum\LaravelBoleto\Util;

class Bb extends AbstractRetorno implements RetornoCnab400
{
    /**
     * Código do banco
     *
     * @var string
     */
    protected $codigoBanco = BoletoContract::COD_BANCO_BB;

    /**
     * Array com as ocorrencias do banco;
     *
     * @var array
     */
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
    ];

    /**
     * Array com as possiveis rejeicoes do banco.
     *
     * @var array
     */
    private $rejeicoes = [
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
        '84' => 'Titulo não localizado na existência',
        '99' => 'Outros motivos',
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
            'protestados' => 0,
            'erros' => 0,
            'alterados' => 0,
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
            ->setServico($this->rem(12, 19, $header))
            ->setAgencia($this->rem(27, 30, $header))
            ->setAgenciaDv($this->rem(31, 31, $header))
            ->setConta($this->rem(32, 39, $header))
            ->setContaDv($this->rem(40, 40, $header))
            ->setConvenio($this->rem(150, 156, $header))
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
        if ($this->rem(1, 1, $detalhe) != '7') {
            return false;
        }

        $d = $this->detalheAtual();

        $d->setCarteira($this->rem(107, 108, $detalhe))
            ->setNossoNumero($this->rem(64, 80, $detalhe))
            ->setNumeroDocumento($this->rem(117, 126, $detalhe))
            ->setNumeroControle($this->rem(39, 63, $detalhe))
            ->setOcorrencia($this->rem(109, 110, $detalhe))
            ->setOcorrenciaDescricao(array_get($this->ocorrencias, $d->getOcorrencia(), 'Desconhecida'))
            ->setDataOcorrencia($this->rem(111, 116, $detalhe))
            ->setDataVencimento($this->rem(147, 152, $detalhe))
            ->setDataCredito($this->rem(176, 181, $detalhe))
            ->setValor(Util::nFloat($this->rem(153, 165, $detalhe)/100, 2, false))
            ->setValorTarifa(Util::nFloat($this->rem(182, 188, $detalhe)/100, 2, false))
            ->setValorIOF(Util::nFloat($this->rem(215, 227, $detalhe)/100, 2, false))
            ->setValorAbatimento(Util::nFloat($this->rem(228, 240, $detalhe)/100, 2, false))
            ->setValorDesconto(Util::nFloat($this->rem(241, 253, $detalhe)/100, 2, false))
            ->setValorRecebido(Util::nFloat($this->rem(254, 266, $detalhe)/100, 2, false))
            ->setValorMora(Util::nFloat($this->rem(267, 279, $detalhe)/100, 2, false))
            ->setValorMulta(Util::nFloat($this->rem(280, 292, $detalhe)/100, 2, false));

        if ($d->hasOcorrencia('05', '06', '07', '08', '15')) {
            $this->totais['liquidados']++;
            $d->setOcorrenciaTipo($d::OCORRENCIA_LIQUIDADA);
        } elseif ($d->hasOcorrencia('02')) {
            $this->totais['entradas']++;
            $d->setOcorrenciaTipo($d::OCORRENCIA_ENTRADA);
        } elseif ($d->hasOcorrencia('09', '10')) {
            $this->totais['baixados']++;
            $d->setOcorrenciaTipo($d::OCORRENCIA_BAIXADA);
        } elseif ($d->hasOcorrencia('61')) {
            $this->totais['protestados']++;
            $d->setOcorrenciaTipo($d::OCORRENCIA_PROTESTADA);
        } elseif ($d->hasOcorrencia('14')) {
            $this->totais['alterados']++;
            $d->setOcorrenciaTipo($d::OCORRENCIA_ALTERACAO);
        } elseif ($d->hasOcorrencia('03')) {
            $this->totais['erros']++;
            $d->setError(array_get($this->rejeicoes, $this->rem(383, 392, $detalhe), 'Consulte seu Internet Banking'));
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
            ->setValorTitulos(Util::nFloat($this->rem(26, 39, $trailer)/100, 2, false))
            ->setQuantidadeTitulos((int) $this->rem(18, 25, $trailer))
            ->setQuantidadeErros((int) $this->totais['erros'])
            ->setQuantidadeEntradas((int) $this->totais['entradas'])
            ->setQuantidadeLiquidados((int) $this->totais['liquidados'])
            ->setQuantidadeBaixados((int) $this->totais['baixados'])
            ->setQuantidadeAlterados((int) $this->totais['alterados']);

        return true;
    }
}
