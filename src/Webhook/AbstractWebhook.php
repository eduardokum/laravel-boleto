<?php

namespace Eduardokum\LaravelBoleto\Webhook;

use Eduardokum\LaravelBoleto\Exception\ValidationException;

abstract class AbstractWebhook
{

    /**
     * @var array
     */
    private $post;

    /**
     * @param $post
     * @throws Exception
     */
    public function __construct($post)
    {
        if (!is_array($post)) {
            throw new ValidationException('Parâmetro deve ser um array contendo os valores enviado pelo banco');
        }

        $this->setPost($post);
    }

    /**
     * @return array
     */
    public function getPost(): array
    {
        return $this->post;
    }

    /**
     * @param array $post
     * @return $this
     */
    public function setPost(array $post): AbstractWebhook
    {
        $this->post = $post;
        return $this;
    }

    abstract public function processar();
}
