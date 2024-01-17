<?php

namespace Eduardokum\LaravelBoleto\Api\Exception;

use Eduardokum\LaravelBoleto\Exception\BaseException;

class MissingDataException extends BaseException
{
    private $data;

    public function __construct(array $data)
    {
        $this->data = $data;
        parent::__construct('Os seguinte campos são obrigatórios: ' . implode(', ', $this->data));
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     *
     * @return MissingDataException
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }
}
