<?php
namespace Eduardokum\LaravelBoleto\Cnab\Retorno;

use Eduardokum\LaravelBoleto\Cnab\Contracts\Retorno\Detalhe as DetalheContract;

class Detalhe extends AbstractDetalhe implements DetalheContract
{
    private $linha;
    protected $tipoOcorrencia;
    private $error;

    const OCORRENCIA_LIQUIDADA = 1;
    const OCORRENCIA_BAIXADA = 2;
    const OCORRENCIA_ENTRADA = 3;
    const OCORRENCIA_ALTERACAO = 4;
    const OCORRENCIA_ERRO = 9;

    public function __construct($linha)
    {
        $this->linha = is_array($linha) ? implode('', $linha) : $linha;
    }

    public function getLinha()
    {
        return $this->linha;
    }

    public function getTipoOcorrencia()
    {
        return $this->tipoOcorrencia;
    }

    public function setTipoOcorrencia($tipoOcorrencia)
    {
        $this->tipoOcorrencia = $tipoOcorrencia;
    }

    public function setErro($erro){
        $this->tipoOcorrencia = self::OCORRENCIA_ERRO;
        $this->error = $erro;
    }

    public function getErro()
    {
        return $this->error;
    }


}