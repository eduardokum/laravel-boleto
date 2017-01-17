<?php
/**
 * Created by PhpStorm.
 * User: Eduardo
 * Date: 05/01/2017
 * Time: 16:49
 */

namespace Eduardokum\LaravelBoleto;

trait MagicTrait
{
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
        return isset($this->$key);
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
