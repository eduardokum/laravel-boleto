<?php
/**
 *   Copyright (c) 2016 Eduardo Gusmão
 *
 *   Permission is hereby granted, free of charge, to any person obtaining a
 *   copy of this software and associated documentation files (the "Software"),
 *   to deal in the Software without restriction, including without limitation
 *   the rights to use, copy, modify, merge, publish, distribute, sublicense,
 *   and/or sell copies of the Software, and to permit persons to whom the
 *   Software is furnished to do so, subject to the following conditions:
 *
 *   The above copyright notice and this permission notice shall be included in all
 *   copies or substantial portions of the Software.
 *
 *   THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 *   INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR
 *   PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 *   COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 *   WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR
 *   IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace Eduardokum\LaravelBoleto\Cnab\Retorno;

use Eduardokum\LaravelBoleto\Contracts\Cnab\Retorno\Trailer as TrailerContract;

class Trailer implements TrailerContract
{

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

    /**
     * @return array
     */
    public function toArray()
    {
        $vars = array_keys(get_class_vars(self::class));
        $aRet = [];
        foreach($vars as $var)
        {
            $methodName = 'get' . ucfirst($var);
            $aRet[$var] = method_exists($this, $methodName)
                ? $this->$methodName()
                : $this->$var;
        }
        return $aRet;
    }

    /**
     * Fast set method.
     *
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        if(property_exists($this, $name))
        {
            $this->$name = $value;
        }
    }
}