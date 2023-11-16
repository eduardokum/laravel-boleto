<?php

namespace Eduardokum\LaravelBoleto\Api\Exception;

use Eduardokum\LaravelBoleto\Exception\BaseException;

class HttpException extends BaseException
{
    private $http_code;

    private $info;

    public function __construct($http_code, $info, $message = '', $code = 0, $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->http_code = $http_code;
        $this->info = $info;
    }

    /**
     * @return mixed
     */
    public function getHttpCode()
    {
        return $this->http_code;
    }

    /**
     * @return mixed
     */
    public function getInfo()
    {
        return $this->info;
    }

    public function __toString()
    {
        return sprintf("\nMessage:%s\nHttpCode: %s\nInfo:%s\n\nTrace:%s", trim($this->getMessage()), $this->getHttpCode(), is_array($this->getInfo()) ? print_r($this->getInfo(), true) : $this->getInfo(), $this->getTraceAsString());
    }
}
