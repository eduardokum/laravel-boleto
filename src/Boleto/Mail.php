<?php

namespace Eduardokum\LaravelBoleto\Boleto;

use Throwable;
use Swift_Mailer;
use Swift_SmtpTransport;
use Illuminate\Support\Arr;
use Illuminate\Mail\Message;
use Illuminate\View\Factory;
use Illuminate\Config\Repository;
use Eduardokum\LaravelBoleto\Blade;
use Illuminate\Container\Container;
use Symfony\Component\Mailer\Transport\Dsn;
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
    private $boleto;

    private $from = [];

    private $emails = [];

    private $view;

    private $mailer;

    private $blade;

    /**
     * @param Boleto $boleto
     * @param $emails
     * @param array $mailerConfigs
     * @throws ValidationException
     */
    public function __construct(Boleto $boleto, $emails, $mailerConfigs = [])
    {
        $this->setBoleto($boleto);
        $this->setEmails($emails);
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
    private function getEmails()
    {
        return $this->emails;
    }

    /**
     * @return Factory
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
                    $config['port'] ?? null,
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
     * @param $texto
     * @return string|void|null
     * @throws ValidationException
     */
    private function build($texto)
    {
        if (is_string($texto)) {
            return $texto;
        }

        if (is_array($texto)) {
            $view = Arr::get($texto, 'view', Arr::get($texto, 'template', Arr::get($texto, 0)));
            $data = Arr::get($texto, 'data', Arr::get($texto, 'vars', Arr::get($texto, 1, [])));

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
     * @param mixed $boleto
     * @return Mail
     */
    public function setBoleto($boleto)
    {
        $this->boleto = $boleto;

        return $this;
    }

    /**
     * @param array $emails
     * @return Mail
     */
    public function setEmails($emails)
    {
        $this->emails = is_array($emails) ? $emails : [$emails];

        foreach ($this->emails as $i => $email) {
            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                unset($this->emails[$i]);
            }
        }

        return $this;
    }

    /**
     * @param $texto
     * @param $subject
     * @return bool
     */
    public function send($texto, $subject)
    {
        try {
            $texto = $this->build($texto);

            if (! LaravelBoletoMailer::isLaravel9Plus() && ! app()->bound(EmbedImages::class)) {
                $this->getMailer()->getSwiftMailer()->registerPlugin(new SwiftEmbedImages(config()->get('mail-auto-embed')));
            }

            $this->getMailer()->html($texto, function (Message $message) use ($subject) {
                if (LaravelBoletoMailer::isLaravel9Plus()) {
                    $message
                        ->attachData($this->getPdf(), 'boleto.pdf', [
                            'mime' => 'application/pdf',
                        ])
                        ->from($this->from['address'], $this->from['name'])
                        ->subject($subject)
                        ->to($this->getEmails());
                } else {
                    $message
                        ->attachData($this->getPdf(), 'boleto.pdf', [
                            'mime' => 'application/pdf',
                        ])
                        ->setFrom($this->from['address'], $this->from['name'])
                        ->setSubject($subject)
                        ->setTo($this->getEmails());
                }
            });

            return true;
        } catch (Throwable $e) {
            return false;
        }
    }
}
