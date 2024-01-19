<?php

namespace Eduardokum\LaravelBoleto\Contracts\Webhook;

interface Webhook
{
    public function getPost();

    public function setPost(array $post);

    public function getHeaders();

    public function setHeaders(array $headers);

    public function setAgencia($agencia);

    public function getAgencia();

    public function setAgenciaDv($agenciaDv);

    public function getAgenciaDv();

    public function setConta($conta);

    public function getConta();

    public function setContaDv($contaDv);

    public function getContaDv();

    public function processar();
}
