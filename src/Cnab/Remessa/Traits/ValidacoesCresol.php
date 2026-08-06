<?php

namespace Eduardokum\LaravelBoleto\Cnab\Remessa\Traits;

use Eduardokum\LaravelBoleto\Exception\ValidationException;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;

/**
 * Validações comuns aos layouts de remessa da Cresol (CNAB 240 e CNAB 400).
 */
trait ValidacoesCresol
{
    /**
     * Limites da faixa de nosso número liberada pela cooperativa.
     *
     * @var int|null
     */
    protected $nossoNumeroInicial = null;

    /**
     * @var int|null
     */
    protected $nossoNumeroFinal = null;

    /**
     * Define a faixa de nosso número liberada pela cooperativa.
     *
     * A faixa não consta em manual, é informada apenas no portal (Integrada > Validação
     * Integrada > Gerar modelos de arquivos).
     *
     * @param int $inicial
     * @param int $final
     *
     * @return $this
     */
    public function setFaixaNossoNumero($inicial, $final)
    {
        $this->nossoNumeroInicial = (int) $inicial;
        $this->nossoNumeroFinal = (int) $final;

        return $this;
    }

    /**
     * Valida se o boleto está dentro da faixa liberada, quando a faixa foi informada.
     *
     * Um único título fora dela faz a Cresol descartar o arquivo de remessa inteiro
     * (FAQ 11 do manual CNAB 240), por isso a validação acontece na inclusão do boleto e
     * não na geração do arquivo: assim o consumidor descobre o problema no título
     * culpado, e não no arquivo pronto.
     *
     * @param BoletoContract $boleto
     *
     * @return void
     * @throws ValidationException
     */
    protected function validaFaixaNossoNumero(BoletoContract $boleto)
    {
        if ($this->nossoNumeroInicial === null || $this->nossoNumeroFinal === null) {
            return;
        }

        $numero = (int) $boleto->getNumero();
        if ($numero < $this->nossoNumeroInicial || $numero > $this->nossoNumeroFinal) {
            throw new ValidationException(sprintf(
                'Nosso número %s fora da faixa liberada pela cooperativa (%s a %s). A Cresol rejeita o arquivo de remessa inteiro quando há um título fora da faixa.',
                $numero,
                $this->nossoNumeroInicial,
                $this->nossoNumeroFinal
            ));
        }
    }

    /**
     * A Cresol só aceita multa percentual, limitada a 99,99% (CNAB 240 segmento R,
     * pos. 075-089; CNAB 400 detalhe, pos. 067-070).
     *
     * No CNAB 400 o campo tem apenas 4 posições com 2 decimais: sem esta validação uma
     * multa de 150% seria gravada como 15,00% silenciosamente, porque a formatação corta
     * o excedente em vez de falhar.
     *
     * @param BoletoContract $boleto
     *
     * @return void
     * @throws ValidationException
     */
    protected function validaMultaPercentual(BoletoContract $boleto)
    {
        if ($boleto->getMulta() > 99.99) {
            throw new ValidationException(sprintf(
                'Multa de %s%% excede o limite de 99,99%% aceito pela Cresol. O campo de multa dos layouts Cresol é percentual e não comporta o valor informado.',
                $boleto->getMulta()
            ));
        }
    }
}
