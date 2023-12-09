<?php

namespace Eduardokum\LaravelBoleto\Webhook\Banco;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Eduardokum\LaravelBoleto\Webhook\Boleto;
use Eduardokum\LaravelBoleto\Webhook\AbstractWebhook;

class Inter extends AbstractWebhook
{
    /**
     * @return array
     */
    public function processar()
    {
        $this->setConta(Arr::get($this->getHeaders(), 'x-conta-corrente'));

        $aRet = [];
        foreach ($this->getPost() as $item) {
            $boleto = new Boleto();
            $boleto->setNossoNumero(Arr::get($item, 'nossoNumero'));
            $boleto->setNumeroDocumento(Arr::get($item, 'seuNumero'));
            $boleto->setNumero($boleto->getNumeroDocumento());
            $boleto->setDataOcorrencia(new Carbon(Arr::get($item, 'dataHoraSituacao', Arr::get($item, 'horario', 'now'))));
            $boleto->setValor(Arr::get($item, 'valorNominal', Arr::get($item, 'componentesValor.original.valor')));
            $boleto->setValorRecebido(Arr::get($item, 'valorTotalRecebimento', Arr::get($item, 'valor')));
            $boleto->setCodigoBarras(Arr::get($item, 'codigoBarras'));
            $boleto->setLinhaDigitavel(Arr::get($item, 'linhaDigitavel'));
            $boleto->setMotivo(Arr::get($item, 'motivoCancelamento'));
            $boleto->setOcorrenciaTipo(
                match (Arr::get($item, 'situacao')) {
                    'A_RECEBER' => Boleto::OCORRENCIA_ENTRADA,
                    'PAGO', 'MARCADO_RECEBIDO', 'RECEBIDO' => Boleto::OCORRENCIA_LIQUIDADA,
                    'CANCELADO', 'EXPIRADO' => Boleto::OCORRENCIA_BAIXADA,
                    default => Boleto::OCORRENCIA_OUTROS,
                }
            );
            $boleto->setOcorrenciaOrigem(
                Arr::get($item, 'origemRecebimento', 'BOLETO') == 'PIX' || ! is_null(Arr::get($item, 'txid'))
                    ? Boleto::OCORRENCIA_ORIGEM_PIX
                    : Boleto::OCORRENCIA_ORIGEM_BOLETO
            );
            $boleto->setTxid(Arr::get($item, 'txid'));
            $boleto->setPix(Arr::get($item, 'pixCopiaECola'));
            $aRet[] = $boleto;
        }

        return $aRet;
    }
}
