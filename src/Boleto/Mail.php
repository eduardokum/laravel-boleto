<?php

namespace Eduardokum\LaravelBoleto\Boleto;

use Throwable;
use Swift_Mailer;
use Swift_SmtpTransport;
use Illuminate\Support\Arr;
use Illuminate\Mail\Message;
use Illuminate\Config\Repository;
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
    private function getTo()
    {
        return $this->to;
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

            if (is_string($config['from'])) {
                $this->from = ['address' => $config['from'], 'name' => $config['from']];
            } elseif (is_array($config['from']) && isset($config['from']['address'])) {
                $this->from = ['address' => $config['from']['address'], 'name' => Arr::get($config['from'], 'name', $config['from']['address'])];
            } elseif (is_array($config['from']) && isset($config['from'][0])) {
                if (filter_var($config['from'][0], FILTER_VALIDATE_EMAIL)) {
                    $this->from['address'] = $config['from'][0];
                    $this->from['name'] = Arr::get($config['from'], 1, $config['from'][0]);
                } elseif (filter_var($config['from'][1], FILTER_VALIDATE_EMAIL)) {
                    $this->from['address'] = $config['from'][1];
                    $this->from['name'] = Arr::get($config['from'], 0, $config['from'][1]);
                }
                if (! isset($this->from['address'])) {
                    throw new ValidationException('Email do Sender informado não é válido');
                }
            } else {
                throw new ValidationException('Email do Sender informado não é válido');
            }
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
     * @param $template
     * @return string|void|null
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
     */
    public function setTo($to)
    {
        $this->to = is_array($to) ? $to : [$to];

        foreach ($this->to as $i => $email) {
            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                unset($this->to[$i]);
            }
        }

        return $this;
    }

    /**
     * @param Boleto $boleto
     * @return Mail
     */
    public function setBoleto(Boleto $boleto)
    {
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
                        ->from($this->from['address'], $this->from['name'])
                        ->subject($subject)
                        ->to($this->getTo());
                } else {
                    $message
                        ->attachData($this->getPdf(), 'boleto.pdf', [
                            'mime' => 'application/pdf',
                        ])
                        ->setFrom($this->from['address'], $this->from['name'])
                        ->setSubject($subject)
                        ->setTo($this->getTo());
                }
            });

            return true;
        } catch (Throwable $e) {
            return false;
        }
    }
}
