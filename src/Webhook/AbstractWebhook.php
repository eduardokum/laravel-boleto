<?php

namespace Eduardokum\LaravelBoleto\Webhook;

use Eduardokum\LaravelBoleto\MagicTrait;
use Eduardokum\LaravelBoleto\Contracts\Webhook\Webhook;
use Eduardokum\LaravelBoleto\Exception\ValidationException;

abstract class AbstractWebhook implements Webhook
{
    use MagicTrait;

    /**
     * @var array
     */
    private $post;

    /**
     * @var array
     */
    private $headers;

    /**
     * @var string|null
     */
    public $agencia;

    /**
     * @var string|null
     */
    public $agenciaDv;

    /**
     * @var string|null
     */
    public $conta;

    /**
     * @var string|null
     */
    public $contaDv;

    /**
     * @param $post
     * @param array $headers
     * @throws ValidationException
     */
    public function __construct($post, array $headers = [])
    {
        if (! is_array($post)) {
            throw new ValidationException('Parâmetro deve ser um array contendo os valores enviado pelo banco');
        }

        $this->setPost($post);
        $this->setPost($headers);
    }

    /**
     * @return array
     */
    public function getPost()
    {
        return $this->post;
    }

    /**
     * @param array $post
     * @return $this
     */
    public function setPost(array $post)
    {
        $this->post = $post;

        return $this;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param array $headers
     * @return AbstractWebhook
     */
    public function setHeaders(array $headers)
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * @param string|null $agencia
     * @return AbstractWebhook
     */
    public function setAgencia($agencia)
    {
        $this->agencia = $agencia;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getAgencia()
    {
        return $this->agencia;
    }

    /**
     * @param string|null $agenciaDv
     * @return AbstractWebhook
     */
    public function setAgenciaDv($agenciaDv)
    {
        $this->agenciaDv = $agenciaDv;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getAgenciaDv()
    {
        return $this->agenciaDv;
    }

    /**
     * @param string|null $conta
     * @return AbstractWebhook
     */
    public function setConta($conta)
    {
        $this->conta = $conta;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getConta()
    {
        return $this->conta;
    }

    /**
     * @param string|null $contaDv
     * @return AbstractWebhook
     */
    public function setContaDv($contaDv)
    {
        $this->contaDv = $contaDv;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getContaDv()
    {
        return $this->contaDv;
    }

    /**
     * @return Boleto[]
     */
    abstract public function processar();
}
