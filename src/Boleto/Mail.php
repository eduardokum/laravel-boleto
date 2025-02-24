<?php

namespace Eduardokum\LaravelBoleto\Boleto;

use Throwable;
use Swift_Mailer;
use Swift_SmtpTransport;
use Illuminate\Support\Arr;
use Illuminate\Mail\Message;
use Illuminate\Config\Repository;
use JetBrains\PhpStorm\ArrayShape;
use Eduardokum\LaravelBoleto\Blade;
use Illuminate\Container\Container;
use Symfony\Component\Mailer\Transport\Dsn;
use Illuminate\View\Compilers\BladeCompiler;
use Eduardokum\LaravelBoleto\Boleto\Render\Pdf;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto;
use Illuminate\Contracts\Mail\Factory as MailFactory;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Eduardokum\LaravelBoleto\Exception\ValidationException;
use Eduardokum\LaravelMailAutoEmbed\Listeners\SwiftEmbedImages;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransportFactory;
use Eduardokum\LaravelMailAutoEmbed\Contracts\Listeners\EmbedImages;

class Mail
{
    /**
     * @var Boleto
     */
    private $boleto;

    /**
     * @var array
     */
    private $from = [];

    /**
     * @var array
     */
    private $to = [];

    /**
     * @var ViewFactory
     */
    private $view;

    /**
     * @var LaravelBoletoMailer
     */
    private $mailer;

    /**
     * @var BladeCompiler
     */
    private $blade;

    /**
     * @param array $mailerConfigs
     * @throws ValidationException
     */
    public function __construct($mailerConfigs = [])
    {
        $this->makeBlade();
        $this->makeMailer($mailerConfigs);
    }

    /**
     * @return string|null
     * @throws ValidationException
     */
    private function getPdf()
    {
        $pdf = new Pdf();
        $pdf->addBoleto($this->getBoleto());

        return $pdf->gerarBoleto($pdf::OUTPUT_STRING);
    }

    /**
     * @return Boleto
     */
    private function getBoleto()
    {
        return $this->boleto;
    }

    /**
     * @return array
     */
    #[ArrayShape([
        'address' => 'string',
        'name'    => 'string',
    ])]
    private function getTo()
    {
        return $this->to;
    }

    /**
     * @return array
     */
    #[ArrayShape([
        'address' => 'string',
        'name'    => 'string',
    ])]
    private function getFrom()
    {
        return $this->from;
    }

    /**
     * @return BladeCompiler
     */
    private function getBlade()
    {
        return $this->blade;
    }

    /**
     * @return LaravelBoletoMailer
     */
    private function getMailer()
    {
        return $this->mailer;
    }

    /**
     * @return void
     */
    private function makeBlade()
    {
        $instance = Container::getInstance();
        if (! is_null($instance) && $instance->bound(ViewFactory::class)) {
            $this->blade = $this->view = view();
        } else {
            $blade = new Blade(realpath(__DIR__ . '/Render/view/'), realpath(__DIR__ . '/Render/cache/'));
            $this->blade = $this->view = $blade->view();
            $instance->bind(ViewFactory::class, function () {
                return $this->view;
            });
            $instance->bind('view', function () {
                return $this->view;
            });
            $instance->bind('config', function () {
                return new Repository([
                    'view'            => ['compiled' => realpath(__DIR__ . '/Render/compiled/')],
                    'mail-auto-embed' => [
                        'enabled' => true,
                        'method'  => 'attachment',
                        'curl'    => [
                            'connect_timeout' => 5,
                            'timeout'         => 10,
                        ],
                    ],
                ]);
            });
        }
        $this->blade = $this->blade->getEngineResolver()->resolve('blade')->getCompiler();
    }

    /**
     * @param $config
     * @return void
     * @throws ValidationException
     */
    private function makeMailer($config)
    {
        $instance = Container::getInstance();
        if (! is_null($instance) && $instance->bound(MailFactory::class)) {
            $this->mailer = app(MailFactory::class);
        } else {
            $configs = ['host', 'port', 'username', 'password', 'from'];
            if (count(array_intersect_key(array_flip($configs), $config)) !== count($configs)) {
                throw new ValidationException('Mailer não existe, para configurar um é necessário informar ' . implode(', ', $configs));
            }

            $scheme = Arr::get($config, 'scheme');
            if (! $scheme) {
                $scheme = ! empty($config['encryption']) && $config['encryption'] === 'tls'
                    ? (($config['port'] == 465) ? 'smtps' : 'smtp')
                    : '';
            }

            $this->setFrom($config['from']);
            if (LaravelBoletoMailer::isLaravel9Plus()) {
                $factory = new EsmtpTransportFactory();
                $transport = $factory->create(new Dsn(
                    $scheme,
                    $config['host'],
                    $config['username'] ?? null,
                    $config['password'] ?? null,
                    ((int) $config['port']) ?? null,
                    $config
                ));
                $this->mailer = new LaravelBoletoMailer('default', $this->view, $transport);
            } else {
                $transport = new Swift_SmtpTransport(
                    $config['host'],
                    $config['port']
                );

                if (! empty($config['encryption'])) {
                    $transport->setEncryption($config['encryption']);
                }
                if (isset($config['username'])) {
                    $transport->setUsername($config['username']);

                    $transport->setPassword($config['password']);
                }
                $this->mailer = new LaravelBoletoMailer('default', $this->view, new Swift_Mailer($transport));
            }
        }
    }

    /**
     * @param array|string $from
     * @return Mail
     * @throws ValidationException
     */
    private function setFrom($from)
    {
        if (is_string($from)) {
            $this->from = ['address' => $from, 'name' => $from];
        } elseif (is_array($from) && isset($from['address'])) {
            $this->from = ['address' => $from['address'], 'name' => Arr::get($from, 'name', $from['address'])];
        } elseif (is_array($from) && isset($from[0])) {
            if (filter_var($from[0], FILTER_VALIDATE_EMAIL)) {
                $this->from['address'] = $from[0];
                $this->from['name'] = Arr::get($from, 1, $from[0]);
            } elseif (filter_var($from[1], FILTER_VALIDATE_EMAIL)) {
                $this->from['address'] = $from[1];
                $this->from['name'] = Arr::get($from, 0, $from[1]);
            }
        } else {
            throw new ValidationException('Email do Sender informado não é válido');
        }

        if (! $this->from['address'] || ! filter_var($this->from['address'], FILTER_VALIDATE_EMAIL)) {
            throw new ValidationException('Email do Sender informado não é válido');
        }

        return $this;
    }

    /**
     * @param $template
     * @return string
     * @throws ValidationException
     */
    private function build($template)
    {
        if (is_string($template)) {
            return $template;
        }

        if (is_array($template)) {
            $view = Arr::get($template, 'view', Arr::get($template, 'template', Arr::get($template, 0)));
            $data = Arr::get($template, 'data', Arr::get($template, 'vars', Arr::get($template, 1, [])));

            if (is_null($view)) {
                throw new ValidationException("View não informada, Utilizar ['view' => 'template.blade.php', 'data'=> []]");
            }

            if (is_null($data)) {
                throw new ValidationException("Data não informada, Utilizar ['view' => 'template.blade.php', 'data'=> []]");
            }

            $data['boleto'] = $this->getBoleto()->toArray();

            if (file_exists($view)) {
                return $this->getBlade()->render(file_get_contents($view), $data);
            }

            if (file_exists(resource_path("views.$view"))) {
                return $this->getBlade()->render(file_get_contents(resource_path("views.$view")), $data);
            }

            return $this->getBlade()->render($view, $data);
        }

        throw new ValidationException("Formato de texto inválido utilize o html completo ou ['view' => 'template.blade.php', 'data'=> []]");
    }

    /**
     * @param array|string $to
     * @return Mail
     * @throws ValidationException
     */
    public function setTo($to)
    {
        if (is_string($to)) {
            $this->to = ['address' => $to, 'name' => $to];
        } elseif (is_array($to) && isset($to['address'])) {
            $this->to = ['address' => $to['address'], 'name' => Arr::get($to, 'name', $to['address'])];
        } elseif (is_array($to) && isset($to[0])) {
            if (filter_var($to[0], FILTER_VALIDATE_EMAIL)) {
                $this->to['address'] = $to[0];
                $this->to['name'] = Arr::get($to, 1, $to[0]);
            } elseif (filter_var($to[1], FILTER_VALIDATE_EMAIL)) {
                $this->to['address'] = $to[1];
                $this->to['name'] = Arr::get($to, 0, $to[1]);
            }
        } else {
            throw new ValidationException('Email do destinatário informado não é válido');
        }

        if (! $this->to['address'] || ! filter_var($this->to['address'], FILTER_VALIDATE_EMAIL)) {
            throw new ValidationException('Email do destinatário informado não é válido');
        }

        return $this;
    }

    /**
     * @param $boleto
     * @return Mail
     * @throws ValidationException
     */
    public function setBoleto($boleto)
    {
        if (! $boleto instanceof Boleto) {
            throw new ValidationException('Boleto não é uma instancia válida de Boleto');
        }
        $this->boleto = $boleto;

        return $this;
    }

    /**
     * @param $template
     * @param $subject
     * @param Boleto|null $boleto
     * @param null $to
     * @return bool
     * @throws ValidationException
     */
    public function send($template, $subject, Boleto $boleto = null, $to = null)
    {
        if ($to) {
            $this->setTo($to);
        }
        if ($boleto) {
            $this->setBoleto($boleto);
        }

        if (! $this->getBoleto()) {
            throw new ValidationException('Informe o boleto a ser enviado utilizando o método ->setBoleto ou passando #3 parâmetro no método ->send');
        }
        if (! $this->getTo()) {
            throw new ValidationException('Informe o destinatário utilizando o método ->setTo ou passando #4 parâmetro no método ->send');
        }

        try {
            $html = $this->build($template);

            if (! LaravelBoletoMailer::isLaravel9Plus() && ! app()->bound(EmbedImages::class)) {
                $this->getMailer()->getSwiftMailer()->registerPlugin(new SwiftEmbedImages(config()->get('mail-auto-embed')));
            }

            $this->getMailer()->html($html, function (Message $message) use ($subject) {
                if (LaravelBoletoMailer::isLaravel9Plus()) {
                    $message
                        ->attachData($this->getPdf(), 'boleto.pdf', [
                            'mime' => 'application/pdf',
                        ])
                        ->from($this->getFrom()['address'], $this->getFrom()['name'])
                        ->subject($subject)
                        ->to($this->getTo()['address'], $this->getTo()['name']);
                } else {
                    $message
                        ->attachData($this->getPdf(), 'boleto.pdf', [
                            'mime' => 'application/pdf',
                        ])
                        ->setFrom($this->getFrom()['address'], $this->getFrom()['name'])
                        ->setSubject($subject)
                        ->setTo($this->getTo()['address'], $this->getTo()['name']);
                }
            });

            return true;
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * @param $template
     * @param $subject
     * @param $boletos
     * @return array
     * @throws ValidationException
     */
    public function sendLote($template, $subject, $boletos)
    {
        $aRet = [];
        foreach ($boletos as $email => $boleto) {
            $aRet[$email] = $this->send($template, $subject, $boleto, $email);
        }

        return $aRet;
    }
}
