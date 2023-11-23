<?php

namespace Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab400;

use Eduardokum\LaravelBoleto\MagicTrait;
use Eduardokum\LaravelBoleto\Contracts\Cnab\Retorno\Cnab400\Trailer as TrailerContract;

class Trailer implements TrailerContract
{
    use MagicTrait;

    /**
     * @var float
     */
    protected $valorTitulos;

    /**
     * @var int
     */
    protected $avisos = 0;

    /**
     * @var int
     */
    protected $quantidadeTitulos;

    /**
     * @var int
     */
    protected $quantidadeLiquidados = 0;

    /**
     * @var int
     */
    protected $quantidadeBaixados = 0;

    /**
     * @var int
     */
    protected $quantidadeEntradas = 0;

    /**
     * @var int
     */
    protected $quantidadeAlterados = 0;

    /**
     * @var int
     */
    protected $quantidadeErros = 0;

    /**
     * @return float
     */
    public function getValorTitulos()
    {
        return $this->valorTitulos;
    }

    /**
     * @param float $valorTitulos
     *
     * @return Trailer
     */
    public function setValorTitulos($valorTitulos)
    {
        $this->valorTitulos = $valorTitulos;

        return $this;
    }

    /**
     * @return int
     */
    public function getAvisos()
    {
        return $this->avisos;
    }

    /**
     * @param int $avisos
     *
     * @return Trailer
     */
    public function setAvisos($avisos)
    {
        $this->avisos = $avisos;

        return $this;
    }

    /**
     * @return int
     */
    public function getQuantidadeTitulos()
    {
        return $this->quantidadeTitulos;
    }

    /**
     * @param int $quantidadeTitulos
     *
     * @return Trailer
     */
    public function setQuantidadeTitulos($quantidadeTitulos)
    {
        $this->quantidadeTitulos = $quantidadeTitulos;

        return $this;
    }

    /**
     * @return int
     */
    public function getQuantidadeLiquidados()
    {
        return $this->quantidadeLiquidados;
    }

    /**
     * @param int $quantidadeLiquidados
     *
     * @return Trailer
     */
    public function setQuantidadeLiquidados($quantidadeLiquidados)
    {
        $this->quantidadeLiquidados = $quantidadeLiquidados;

        return $this;
    }

    /**
     * @return int
     */
    public function getQuantidadeBaixados()
    {
        return $this->quantidadeBaixados;
    }

    /**
     * @param int $quantidadeBaixados
     *
     * @return Trailer
     */
    public function setQuantidadeBaixados($quantidadeBaixados)
    {
        $this->quantidadeBaixados = $quantidadeBaixados;

        return $this;
    }

    /**
     * @return int
     */
    public function getQuantidadeEntradas()
    {
        return $this->quantidadeEntradas;
    }

    /**
     * @param int $quantidadeEntradas
     *
     * @return Trailer
     */
    public function setQuantidadeEntradas($quantidadeEntradas)
    {
        $this->quantidadeEntradas = $quantidadeEntradas;

        return $this;
    }

    /**
     * @return int
     */
    public function getQuantidadeAlterados()
    {
        return $this->quantidadeAlterados;
    }

    /**
     * @param int $quantidadeAlterados
     *
     * @return Trailer
     */
    public function setQuantidadeAlterados($quantidadeAlterados)
    {
        $this->quantidadeAlterados = $quantidadeAlterados;

        return $this;
    }

    /**
     * @return int
     */
    public function getQuantidadeErros()
    {
        return $this->quantidadeErros;
    }

    /**
     * @param int $quantidadeErros
     *
     * @return Trailer
     */
    public function setQuantidadeErros($quantidadeErros)
    {
        $this->quantidadeErros = $quantidadeErros;

        return $this;
    }
}
