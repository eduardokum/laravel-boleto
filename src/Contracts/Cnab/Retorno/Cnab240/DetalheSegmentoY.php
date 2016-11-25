<?php

namespace Eduardokum\LaravelBoleto\Contracts\Cnab\Retorno\Cnab240;


interface DetalheSegmentoY
{

    /**
     * @return mixed
     */
    public function getCodigoBancoCompensacao();

    /**
     * @return mixed
     */
    public function getCodigoOcorrencia();

    /**
     * @return mixed
     */
    public function getCodigoSegmentoRegistroDetalhe();

    /**
     * @return array
     */
    public function getIdentificacaoCheque();

    /**
     * @return mixed
     */
    public function getIdentificacaoRegistroOpcional();

    /**
     * @return mixed
     */
    public function getLoteServico();

    /**
     * @return mixed
     */
    public function getNumeroSequencialRegistroLote();

    /**
     * @return mixed
     */
    public function getTipoRegistro();

    /**
     * @return array
     */
    public function toArray();
}