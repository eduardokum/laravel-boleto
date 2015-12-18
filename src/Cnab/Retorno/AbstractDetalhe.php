<?php
namespace Eduardokum\LaravelBoleto\Cnab\Retorno;

abstract class AbstractDetalhe
{
    private $dados = [];

    public function getDados()
    {
        return $this->dados + ['tipoOcorrencia' => $this->getTipoOcorrencia()];
    }

    public function getCampos()
    {
        return array_keys($this->dados);
    }

    public function has($key)
    {
        return array_key_exists($key, $this->dados);
    }

    public function get($key, $default = '', $valida = false)
    {

        if(property_exists($this, $key))
        {
            $check = $this->$key;
            if($valida) {
                $check = $check && !empty($this->$key) && !empty(trim($this->$key,'0 '));
            }
            return $check ? $this->$key : $default;
        }

        $check = $this->has($key);
        if($valida) {
            $check = $check && !empty($this->dados[$key]) && !empty(trim($this->dados[$key],'0 '));
        }
        return  $check ? $this->dados[$key] : $default;
    }

    public function set($key, $value)
    {
        $this->dados[$key] = $value;
    }

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

    function __get($name)
    {
        if(!$this->has($name))
        {
            throw new \Exception('Propriedade ' . $name . ' não existe');
        }

        return $this->get($name);
    }

    function __set($name, $value)
    {
        $this->set($name, $value);
    }
}