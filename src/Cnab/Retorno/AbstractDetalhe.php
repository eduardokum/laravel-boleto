<?php
namespace Eduardokum\LaravelBoleto\Cnab\Retorno;

class AbstractDetalhe
{
    public function __call($name, $arguments)
    {
        if(strtolower(substr($name, 0, 3)) == 'get')
        {
            $name = lcfirst(substr($name, 3));
            if(property_exists($this, $name))
            {
                return empty($this->$name) ? $arguments[0] : $this->$name;
            }
        }

        throw new \Exception('Método ' . $name . ' não existe');
    }
}