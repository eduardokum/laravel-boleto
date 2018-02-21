<?php
namespace Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab400\Banco;

use Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab400\AbstractRetorno;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;
use Eduardokum\LaravelBoleto\Contracts\Cnab\RetornoCnab400;
use Eduardokum\LaravelBoleto\Util;

class Santander extends AbstractRetorno implements RetornoCnab400
{
    /**
     * Código do banco
     *
     * @var string
     */
    protected $codigoBanco = BoletoContract::COD_BANCO_SANTANDER;

    /**
     * Array com as ocorrencias do banco;
     *
     * @var array
     */
    private $ocorrencias = [
        '01' => 'Título não existe',
        '02' => 'Entrada título confirmada',
        '03' => 'Entrada título rejeitada',
        '06' => 'Liquidação',
        '07' => 'Liquidação por conta',
        '08' => 'Liquidação por saldo',
        '09' => 'Baixa automática',
        '10' => 'Títutlo baixado conforme instrução ou por título protestado',
        '11' => 'Em ser',
        '12' => 'Abatimento concedido',
        '13' => 'Abatimento cancelado',
        '14' => 'Prorrogação de vencimento',
        '15' => 'Enviado para Cartório',
        '16' => 'Título já baixado/liquidado',
        '17' => 'Liquidado em cartório',
        '21' => 'Entrada em Cartório',
        '22' => 'Retirado de cartório',
        '24' => 'Custas de Cartório',
        '25' => 'Protestar Título',
        '26' => 'Sustar Protesto',
    ];

    /**
     * Array com as possiveis rejeicoes do banco.
     *
     * @var array
     */
    private $rejeicoes = [
        '001' => 'Nosso numero nao numerico',
        '002' => 'Valor do abatimento nao numerico',
        '003' => 'Data vencimento nao numerica',
        '004' => 'Conta cobranca nao numerica',
        '005' => 'Codigo da carteira nao numerico',
        '006' => 'Codigo da carteira invalido',
        '007' => 'Especie do documento invalida',
        '008' => 'Unidade de valor nao numerica',
        '009' => 'Unidade de valor invalida',
        '010' => 'Codigo primeira instrucao nao numerica',
        '011' => 'Codigo segunda instrucao nao numerica',
        '012' => 'Valor do titulo em outra unidade',
        '013' => 'Valor do titulo nao numerico',
        '014' => 'Valor de mora nao numerico',
        '015' => 'Data emissao não numerica ',
        '016' => 'Data de vencimento invalida',
        '017' => 'Codigo da agencia cobradora nao numerica',
        '018' => 'Valor do ioc nao numerico',
        '019' => 'Numero do cep nao numerico',
        '020' => 'Tipo inscricao nao numerico',
        '021' => 'Numero do cgc ou cpf nao numerico',
        '022' => 'Codigo ocorrencia invalido',
        '024' => 'Total parcela nao numerico',
        '025' => 'Valor desconto nao numerico',
        '026' => 'Codigo banco cobrador invalido',
        '027' => 'Numero parcelas carne nao numerico',
        '028' => 'Numero parcelas carne zerado',
        '029' => 'Valor de mora invalido',
        '030' => 'Dt venc menor de 15 dias da dt proces',
        '039' => 'Perfil nao aceita titulo em bco corresp',
        '041' => 'Agencia cobradora nao encontrada',
        '042' => 'Conta cobranca invalida',
        '043' => 'Nao baixar,  compl. informado invalido',
        '044' => 'Nao protestar, compl. informado invalido',
        '045' => 'Qtd de dias de baixa nao preenchido',
        '046' => 'Qtd de dias protesto nao preenchido',
        '047' => 'Tot parc. inf. nao bate c/ qtd parc ger',
        '048' => 'Carne com parcelas com erro',
        '049' => 'Seu numero nao confere com o carne',
        '051' => 'Titulo nao encontrado',
        '052' => 'Ocor.  nao acatada, titulo  liquidado',
        '053' => 'Ocor. nao acatada, titulo baixado',
        '054' => 'Titulo com ordem de protesto ja emitida',
        '055' => 'Ocor. nao acatada, titulo ja protestado',
        '056' => 'Ocor. nao acatada, tit. nao vencido',
        '057' => 'Cep do sacado incorreto',
        '058' => 'Cgc/cpf incorreto',
        '059' => 'Instrucao aceita so p/ cobranca simples',
        '060' => 'Especie documento nao protestavel',
        '061' => 'Cedente sem carta de protesto',
        '062' => 'Sacado nao protestavel',
        '063' => 'Cep nao encontrado na tabela de pracas',
        '064' => 'Tipo de cobranca nao permite protesto',
        '065' => 'Pedido sustacao ja solicitado',
        '066' => 'Sustacao protesto fora de prazo',
        '067' => 'Cliente nao transmite reg. de ocorrencia',
        '068' => 'Tipo de vencimento invalido',
        '069' => 'Produto diferente de cobranca simples',
        '070' => 'Data prorrogacao menor que data vencto',
        '071' => 'Data antecipacao maior que data vencto',
        '072' => 'Data documento superior a data instrucao',
        '073' => 'Abatimento maior/igual ao valor titulo',
        '074' => 'Prim. desconto maior/igual valor titulo',
        '075' => 'Seg. desconto maior/igual valor titulo',
        '076' => 'Terc. desconto maior/igual valor titulo',
        '077' => 'Desc. por antec. maior/igual vlr titulo',
        '078' => 'Nao existe abatimento p/ cancelar',
        '079' => 'Nao existe prim. desconto p/ cancelar',
        '080' => 'Nao existe seg. desconto p/ cancelar',
        '081' => 'Nao existe terc. desconto p/ cancelar',
        '082' => 'Nao existe desc. por antec. p/ cancelar',
        '084' => 'Ja existe segundo desconto',
        '085' => 'Ja existe terceiro desconto',
        '086' => 'Data segundo desconto invalida',
        '087' => 'Data terceiro desconto invalida',
        '089' => 'Data multa menor/igual que vencimento',
        '090' => 'Ja existe desconto por dia antecipacao',
        '091' => 'Ja existe concessao de desconto',
        '092' => 'Nosso numero ja cadastrado',
        '093' => 'Valor do titulo nao informado',
        '094' => 'Valor tit. em outra moeda nao informado',
        '095' => 'Perfil nao aceita valor titulo zerado',
        '096' => 'Especie docto nao permite protesto',
        '097' => 'Especie docto nao permite ioc zerado',
        '098' => 'Data emissao invalida',
        '099' => 'Registro duplicado no movimento diario',
        '100' => 'Data emissao maior que a data vencimento',
        '101' => 'Nome do sacado não informado ',
        '102' => 'Endereco do sacado não informado',
        '103' => 'Municipio do sacado nao informado',
        '104' => 'Unidade da federacao nao informada',
        '105' => 'Tipo inscricao não existe',
        '106' => 'Cgc/cpf nao informado',
        '107' => 'Unidade da federacao incorreta',
        '108' => 'Digito cgc/cpf incorreto',
        '109' => 'Valor mora tem que ser zero (tit = zero)',
        '110' => 'Data primeiro desconto invalida',
        '111' => 'Data  desconto nao numerica',
        '112' => 'Valor desconto nao informado',
        '113' => 'Valor desconto invalido',
        '114' => 'Valor abatimento nao informado',
        '115' => 'Valor abatimento maior valor titulo',
        '116' => 'Data multa nao numerica',
        '117' => 'Valor desconto maior valor titulo',
        '118' => 'Data multa nao informada',
        '119' => 'Data multa maior que data de vencimento',
        '120' => 'Percentual multa nao numerico',
        '121' => 'Percentual multa nao informado',
        '122' => 'Valor iof maior que valor titulo',
        '123' => 'Cep do sacado nao numerico',
        '124' => 'Cep sacado nao encontrado',
        '126' => 'Codigo p. baixa / devol. invalido',
        '127' => 'Codigo p. baixa / devol. nao numerica',
        '128' => 'Codigo protesto invalido',
        '129' => 'Espec de documento nao numerica',
        '130' => 'Forma de cadastramento nao numerica',
        '131' => 'Forma de cadastramento invalida',
        '132' => 'Forma cadast. 2 invalida para carteira 3',
        '133' => 'Forma cadast. 2 invalida para carteira 4',
        '134' => 'Codigo do mov. remessa nao numerico',
        '135' => 'Codigo do mov. remessa invalido',
        '136' => 'Codigo bco na compensacao nao numerico',
        '138' => 'Num. lote remessa(detalhe) nao numerico',
        '140' => 'Cod. sequec.do reg. detalhe invalido',
        '141' => 'Num. seq. reg. do lote nao numerico',
        '142' => 'Num.ag.cedente/dig.nao numerico',
        '144' => 'Tipo de documento nao numerico',
        '145' => 'Tipo de documento invalido',
        '146' => 'Codigo p. protesto nao numerico',
        '147' => 'Qtde de dias p. protesto invalido',
        '148' => 'Qtde de dias p. protesto nao numerico',
        '149' => 'Codigo de mora invalido',
        '150' => 'Codigo de mora nao numerico',
        '151' => 'Vl.mora igual a zeros p. cod.mora 1',
        '152' => 'Vl. taxa mora igual a zeros p.cod mora 2',
        '154' => 'Vl. mora nao numerico p. cod mora 2',
        '155' => 'Vl. mora invalido p. cod.mora 4',
        '156' => 'Qtde dias p.baixa/devol. nao numerico',
        '157' => 'Qtde dias baixa/dev. invalido p. cod. 1',
        '158' => 'Qtde dias baixa/dev. invalido p.cod. 2',
        '160' => 'Bairro do sacado nao informado',
        '161' => 'Tipo insc.cpf/cgc sacador/aval.nao num.',
        '162' => 'Indicador de carne nao numerico',
        '163' => 'Num. total de parc.carne nao numerico',
        '164' => 'Numero do plano nao numerico',
        '165' => 'Indicador de parcelas carne invalido',
        '166' => 'N.seq. parcela inv.p.indic. maior 0',
        '167' => 'N. seq.parcela inv.p.indic.dif.zeros',
        '168' => 'N.tot.parc.inv.p.indic. maior zeros',
        '169' => 'Num.tot.parc.inv.p.indic.difer.zeros',
        '170' => 'Forma de cadastramento 2 inv.p.cart.5',
        '199' => 'Tipo insc.cgc/cpf sacador.aval.inval.',
        '200' => 'Num.insc.(cgc)sacador/aval.nao numerico',
        '201' => 'Alt. do contr. participante invalido',
        '202' => 'Alt. do seu numero invalida',
        '218' => 'Bco compensacao nao numerico (d3q)',
        '219' => 'Bco compensacao invalido (d3q)',
        '220' => 'Num. do lote remessa nao numerico(d3q)',
        '221' => 'Num. seq. reg. no lote (d3q)',
        '222' => 'Tipo insc.sacado nao numerico (d3q)',
        '223' => 'Tipo insc.sacado invalido (d3q)',
        '224' => 'Num.insc.sacado nao numerico (d3q)',
        '225' => 'Num.insc.sac.inv.p.tipo insc.0 e 9(d3q)',
        '226' => 'Num.bco compensacao nao numerico (d3r)',
        '228' => 'Num. lote remessa nao numerico (d3r)',
        '229' => 'Num. seq. reg. lote nao numerico (d3r)',
        '246' => 'Cod.bco compensacao nao numerico (d3s)',
        '247' => 'Cod. banco compensacao invalido (d3s)',
        '248' => 'Num.lote remessa nao numerico (d3s)',
        '249' => 'Num.seq.do reg.lote nao numerico (d3s)',
        '250' => 'Num.ident.de impressao nao numerico(d3s)',
        '251' => 'Num.ident.de impressao invalido (d3s)',
        '252' => 'Num.linha impressa nao numerico(d3s)',
        '253' => 'Cod.msg. p.rec. sac. nao numerico (d3s)',
        '254' => 'Cod.msg.p.rec.sacado invalido(d3s)',
        '258' => 'Vl.mora nao numerico p.cod=4(d3p)',
        '259' => 'Cad.txperm.sk.inv.p.cod.mora=4(d3p)',
        '260' => 'Vl.tit(real).inv.p.cod.mora = 1(dep)',
        '261' => 'Vl.outros inv.p.cod.mora = 1(d3p)',
    ];

    /**
     * Roda antes dos metodos de processar
     */
    protected function init()
    {
        $this->totais = [
            'valor_recebido' => 0,
            'liquidados' => 0,
            'entradas' => 0,
            'baixados' => 0,
            'protestados' => 0,
            'erros' => 0,
            'alterados' => 0,
        ];
    }

    protected function processarHeader(array $header)
    {
        $this->getHeader()
            ->setOperacaoCodigo($this->rem(2, 2, $header))
            ->setOperacao($this->rem(3, 9, $header))
            ->setServicoCodigo($this->rem(10, 11, $header))
            ->setServico($this->rem(12, 26, $header))
            ->setAgencia($this->rem(27, 30, $header))
            ->setConta($this->rem(39, 46, $header))
            ->setData($this->rem(95, 100, $header));

        return true;
    }

    protected function processarDetalhe(array $detalhe)
    {
        if ($this->count() == 1) {
            if (trim($this->rem(384, 385, $detalhe), '') != '') {
                $this->getHeader()
                    ->setConta(
                        $this->getHeader()->getConta()
                        . $this->rem(384, 385, $detalhe)
                    );
            }
        }

        $d = $this->detalheAtual();
        $d->setCarteira($this->rem(108, 108, $detalhe))
            ->setNossoNumero($this->rem(63, 70, $detalhe))
            ->setNumeroDocumento($this->rem(117, 126, $detalhe))
            ->setNumeroControle($this->rem(38, 62, $detalhe))
            ->setOcorrencia($this->rem(109, 110, $detalhe))
            ->setOcorrenciaDescricao(array_get($this->ocorrencias, $d->getOcorrencia(), 'Desconhecida'))
            ->setDataOcorrencia($this->rem(111, 116, $detalhe))
            ->setDataVencimento($this->rem(147, 152, $detalhe))
            ->setDataCredito($this->rem(296, 301, $detalhe))
            ->setValor(Util::nFloat($this->rem(153, 165, $detalhe)/100, 2, false))
            ->setValorTarifa(Util::nFloat($this->rem(176, 188, $detalhe)/100, 2, false))
            ->setValorIOF(Util::nFloat($this->rem(215, 227, $detalhe)/100, 2, false))
            ->setValorAbatimento(Util::nFloat($this->rem(228, 240, $detalhe)/100, 2, false))
            ->setValorDesconto(Util::nFloat($this->rem(241, 253, $detalhe)/100, 2, false))
            ->setValorRecebido(Util::nFloat($this->rem(254, 266, $detalhe)/100, 2, false))
            ->setValorMora(Util::nFloat($this->rem(267, 279, $detalhe)/100, 2, false))
            ->setValorMulta(Util::nFloat($this->rem(280, 292, $detalhe)/100, 2, false));

        $this->totais['valor_recebido'] += $d->getValorRecebido();

        if ($d->hasOcorrencia('06', '07', '08', '16', '17')) {
            $this->totais['liquidados']++;
            $d->setOcorrenciaTipo($d::OCORRENCIA_LIQUIDADA);
        } elseif ($d->hasOcorrencia('02')) {
            $this->totais['entradas']++;
            $d->setOcorrenciaTipo($d::OCORRENCIA_ENTRADA);
        } elseif ($d->hasOcorrencia('09')) {
            $this->totais['baixados']++;
            $d->setOcorrenciaTipo($d::OCORRENCIA_BAIXADA);
        } elseif ($d->hasOcorrencia('10')) {
            $this->totais['protestados']++;
            $d->setOcorrenciaTipo($d::OCORRENCIA_PROTESTADA);
        } elseif ($d->hasOcorrencia('14')) {
            $this->totais['alterados']++;
            $d->setOcorrenciaTipo($d::OCORRENCIA_ALTERACAO);
        } elseif ($d->hasOcorrencia('03')) {
            $this->totais['erros']++;
            $errorsRetorno = str_split(sprintf('%09s', $this->rem(137, 145, $detalhe)), 3) + array_fill(0, 3, '');
            $error = [];
            $error[] = array_get($this->rejeicoes, $errorsRetorno[0], '');
            $error[] = array_get($this->rejeicoes, $errorsRetorno[1], '');
            $error[] = array_get($this->rejeicoes, $errorsRetorno[2], '');

            $d->setError(implode(PHP_EOL, $error));
        } else {
            $d->setOcorrenciaTipo($d::OCORRENCIA_OUTROS);
        }

        return true;
    }

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
