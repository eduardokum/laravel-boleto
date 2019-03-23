<?php
namespace Eduardokum\LaravelBoleto;

trait MagicTrait
{
    protected $trash = [];

    /**
     * Fast set method.
     *
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        if (property_exists($this, $name)) {
            $this->$name = $value;
        } else {
            $this->trash[$name] = $value;
        }
    }

    /**
     * Fast get method.
     *
     * @param $name
     *
     * @return null
     */
    public function __get($name)
    {
        if (property_exists($this, $name)) {
            $method = 'get' . ucwords($name);
            return $this->{$method}();
        } elseif (isset($this->trash[$name])) {
            return $this->trash[$name];
        }

        return null;
    }

    /**
     * Determine if an attribute exists
     *
     * @param  string $key
     * @return bool
     */
    public function __isset($key)
    {
        return isset($this->$key) || isset($this->trash[$key]);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $vars = array_keys(get_class_vars(self::class));
        $aRet = [];
        foreach ($vars as $var) {
            $methodName = 'get' . ucfirst($var);
            $aRet[$var] = method_exists($this, $methodName)
                ? $this->$methodName()
                : $this->$var;

            if (is_object($aRet[$var]) && method_exists($aRet[$var], 'toArray')) {
                $aRet[$var] = $aRet[$var]->toArray();
            }
        }
        return $aRet;
    }
}
