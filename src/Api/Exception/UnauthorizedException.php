<?php

namespace Eduardokum\LaravelBoleto\Api\Exception;

use Eduardokum\LaravelBoleto\Exception\BaseException;

class UnauthorizedException extends BaseException
{
    private $baseUrl;

    private $conta;

    private $certificado;

    private $certificadoChave;

    /**
     * @var null
     */
    private $certificadoSenha;

    public function __construct($baseUrl, $conta, $certificado, $certificadoChave, $certificadoSenha = null)
    {
        parent::__construct('Unauthorized', 401);
        $this->baseUrl = $baseUrl;
        $this->conta = $conta;
        $this->certificado = $certificado;
        $this->certificadoChave = $certificadoChave;
        $this->certificadoSenha = $certificadoSenha;
    }

    /**
     * @return mixed
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * @param mixed $baseUrl
     *
     * @return UnauthorizedException
     */
    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = $baseUrl;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getConta()
    {
        return $this->conta;
    }

    /**
     * @param mixed $conta
     *
     * @return UnauthorizedException
     */
    public function setConta($conta)
    {
        $this->conta = $conta;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCertificado()
    {
        return $this->certificado;
    }

    /**
     * @param mixed $certificado
     *
     * @return UnauthorizedException
     */
    public function setCertificado($certificado)
    {
        $this->certificado = $certificado;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCertificadoChave()
    {
        return $this->certificadoChave;
    }

    /**
     * @param mixed $certificadoChave
     *
     * @return UnauthorizedException
     */
    public function setCertificadoChave($certificadoChave)
    {
        $this->certificadoChave = $certificadoChave;

        return $this;
    }

    /**
     * @return null
     */
    public function getCertificadoSenha()
    {
        return $this->certificadoSenha;
    }

    /**
     * @param null $certificadoSenha
     *
     * @return UnauthorizedException
     */
    public function setCertificadoSenha($certificadoSenha)
    {
        $this->certificadoSenha = $certificadoSenha;

        return $this;
    }
}
