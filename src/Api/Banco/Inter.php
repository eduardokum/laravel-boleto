<?php

namespace Eduardokum\LaravelBoleto\Api\Banco;

use Exception;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Eduardokum\LaravelBoleto\Util;
use Eduardokum\LaravelBoleto\Api\AbstractAPI;
use Eduardokum\LaravelBoleto\Api\Exception\CurlException;
use Eduardokum\LaravelBoleto\Api\Exception\HttpException;
use Eduardokum\LaravelBoleto\Exception\ValidationException;
use Eduardokum\LaravelBoleto\Boleto\Banco\Inter as BoletoInter;
use Eduardokum\LaravelBoleto\Api\Exception\UnauthorizedException;
use Eduardokum\LaravelBoleto\Contracts\Boleto\BoletoAPI as BoletoAPIContract;

class Inter extends AbstractAPI
{
    protected $baseUrl = 'https://apis.bancointer.com.br';

    private $version = 1;

    /**
     * Campos necessários para o boleto
     *
     * @var array
     */
    protected $camposObrigatorios = [
        'conta',
        'certificado',
        'certificadoChave',
    ];

    public function __construct($params = [])
    {
        if (isset($params['versao']) && in_array($params['versao'], [2, 3])) {
            $this->version = $params['versao'];
            $this->camposObrigatorios = [
                'certificado',
                'certificadoChave',
                'client_id',
                'client_secret',
            ];
            $this->baseUrl = 'https://cdpj.partners.bancointer.com.br';
        }
        parent::__construct($params);
    }

    protected function oAuth2()
    {
        if ($this->version == 1 || $this->getAccessToken()) {
            return $this;
        }
        $grant = $this->post($this->url('auth'), [
            'client_id'     => $this->getClientId(),
            'client_secret' => $this->getClientSecret(),
            'scope'         => 'boleto-cobranca.read boleto-cobranca.write',
            'grant_type'    => 'client_credentials',
        ], true)->body;

        return $this->setAccessToken('Bearer ' . $grant->access_token);
    }

    /**
     * @return array
     */
    protected function headers()
    {
        if ($this->version != 1) {
            return array_filter([
                'Authorization' => $this->getAccessToken(),
            ]);
        }

        return [
            'x-inter-conta-corrente' => $this->getConta(),
        ];
    }

    /**
     * @param $url
     * @param $type
     * @return bool
     * @throws ValidationException
     */
    public function createWebhook($url, $type = 'all')
    {
        if ($this->version == 1) {
            throw new ValidationException('Somente versão 2 e 3 da API permite criação de webhooks');
        }
        try {
            $this->oAuth2()->put($this->url('webhook'), ['webhookUrl' => $url]);

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @param BoletoInter $boleto
     *
     * @return BoletoAPIContract
     * @throws CurlException
     * @throws HttpException
     * @throws UnauthorizedException
     * @throws ValidationException
     */
    public function createBoleto(BoletoAPIContract $boleto)
    {
        $data = $boleto->toAPI();
        if ($this->version != 1) {
            unset($data['dataEmissao']);
            unset($data['dataLimite']);
            $data['numDiasAgenda'] = (int) $boleto->getDiasBaixaAutomatica();
            $data['pagador']['cpfCnpj'] = $data['pagador']['cnpjCpf'];
            unset($data['pagador']['cnpjCpf']);
        }
        if ($this->version == 3 && isset($data['desconto'])) {
            $data['desconto1'] = $data['desconto'];
            $data['desconto1']['codigoDesconto'] = $data['desconto']['codigo'];
            unset($data['desconto1']['codigo'], $data['desconto']);
        }
        if ($this->version == 2 && isset($data['desconto1'])) {
            $data['desconto'] = $data['desconto1'];
            $data['desconto']['codigo'] = $data['desconto1']['codigoDesconto'];
            unset($data['desconto']['codigoDesconto'], $data['desconto1']);
        }

        $retorno = $this->oAuth2()->post($this->url('create'), $data);

        if ($this->version == 3) {
            $retorno = $this->oAuth2()->get($this->url('show', $retorno->body->codigoCobranca));
            $boleto->setID($retorno->body->codigoCobranca);
            $boleto->setNossoNumero($retorno->body->boleto->nossoNumero);
            $boleto->setPixQrCode($retorno->body->pix->pixCopiaECola);
        } else {
            $boleto->setNossoNumero($retorno->body->nossoNumero);
        }

        return $boleto;
    }

    /**
     * @param array $inputedParams
     *
     * @return array
     * @throws CurlException
     * @throws HttpException
     * @throws UnauthorizedException
     */
    public function retrieveList($inputedParams = [])
    {
        $params = array_filter([
            'situacao'       => $this->version == 1 ? null : Arr::get($inputedParams, 'situacao', 'EXPIRADO,PAGO,EMABERTO,VENCIDO,CANCELADO'),
            'filtrarPor'     => $this->version == 2 ? null : Arr::get($inputedParams, 'filtrarPor', 'TODOS'),
            'filtrarDataPor' => Arr::get($inputedParams, 'filtrarDataPor', 'VENCIMENTO'),
            'dataInicial'    => Arr::get($inputedParams, 'dataInicial', Carbon::now()->startOfMonth()->format('Y-m-d')),
            'dataFinal'      => Arr::get($inputedParams, 'dataFinal', Carbon::now()->endOfMonth()->format('Y-m-d')),
            'ordenarPor'     => Arr::get($inputedParams, 'ordenarPor', 'NOSSONUMERO'),
            'page'           => $this->version == 1 ? 0 : null,
            'size'           => $this->version == 1 ? 100 : null,
            'paginaAtual'    => $this->version == 2 ? 0 : null,
            'itensPorPagina' => $this->version == 2 ? 1000 : null,
            'paginacao'      => $this->version == 3 ? ['paginaAtual' => 0, 'itensPorPagina' => 1000] : null,
        ], function ($v) {
            return ! is_null($v);
        });

        $aRetorno = [];
        if (in_array($this->version, [1, 2])) {
            do {
                $retorno = $this->oAuth2()->get($this->url('search') . http_build_query($params));
                array_push($aRetorno, ...$retorno->body->content);
                if ($this->version == 1) {
                    $params['page'] += 1;
                } else {
                    $params['paginaAtual'] += 1;
                }
            } while (! $retorno->body->last);
        } else {
            do {
                $retorno = $this->oAuth2()->get($this->url('search') . http_build_query($params));
                array_push($aRetorno, ...$retorno->body->cobrancas);
                $params['paginacao']['paginaAtual'] += 1;
            } while (! $retorno->body->ultimaPagina);
        }

        return array_map([$this, 'arrayToBoleto'], $aRetorno);
    }

    /**
     * @param $nossoNumero
     *
     * @return mixed
     * @throws CurlException
     * @throws HttpException
     * @throws UnauthorizedException
     * @throws ValidationException
     */
    public function retrieveNossoNumero($nossoNumero)
    {
        if ($this->version == 3) {
            throw new ValidationException('Versão 3 da API somente recupera boleto pelo ID da cobrança');
        }
        $response = $this->oAuth2()->get($this->url('show', $nossoNumero));

        return $this->version == 1
            ? $response
            : $response->body;
    }

    /**
     * @param $id
     *
     * @return mixed
     * @throws CurlException
     * @throws HttpException
     * @throws UnauthorizedException
     * @throws ValidationException
     */
    public function retrieveID($id)
    {
        if ($this->version != 3) {
            throw new ValidationException('Versão 1 e 2 da API somente recupera boleto pelo nosso número');
        }

        return $this->oAuth2()->get($this->url('show', $id))->body;
    }

    /**
     * @param        $nossoNumero
     * @param string $motivo
     *
     * @return mixed
     * @throws CurlException
     * @throws HttpException
     * @throws UnauthorizedException
     * @throws ValidationException
     */
    public function cancelNossoNumero($nossoNumero, $motivo = 'ACERTOS')
    {
        if ($this->version == 3) {
            throw new ValidationException('Versão 3 da API somente cancela boleto pelo ID da cobrança');
        }
        $motivosValidos = [
            'ACERTOS',
            'PAGODIRETOAOCLIENTE',
            'SUBSTITUICAO',
            'FALTADESOLUCAO',
            'APEDIDODOCLIENTE',
        ];
        if ($this->version == 2) {
            $motivosValidos = [
                'ACERTOS',
                'APEDIDODOCLIENTE',
                'DEVOLUCAO',
                'PAGODIRETOAOCLIENTE',
                'SUBSTITUICAO',
            ];
        }

        if (! in_array(Util::upper($motivo), $motivosValidos)) {
            $motivo = 'ACERTOS';
        }

        return $this->oAuth2()->post(
            $this->url('cancel', $nossoNumero),
            $this->version == 1
                ? ['codigoBaixa' => $motivo]
                : ['motivoCancelamento' => $motivo]
        )->body;
    }

    /**
     * @param        $id
     * @param string $motivo
     *
     * @return mixed
     * @throws CurlException
     * @throws HttpException
     * @throws UnauthorizedException
     * @throws ValidationException
     */
    public function cancelID($id, $motivo)
    {
        if ($this->version != 3) {
            throw new ValidationException('Versão 1 e 2 da API somente cancela boleto pelo nosso número');
        }

        return $this->oAuth2()->post($this->url('cancel', $id), ['motivoCancelamento' => $motivo])->body;
    }

    /**
     * @param $nossoNumero
     *
     * @return mixed
     * @throws CurlException
     * @throws HttpException
     * @throws UnauthorizedException
     * @throws ValidationException
     */
    public function getPdfNossoNumero($nossoNumero)
    {
        if ($this->version == 3) {
            throw new ValidationException('Versão 3 da API somente recupera PDF pelo ID da cobrança');
        }

        return $this->oAuth2()->get($this->url('pdf', $nossoNumero))->body;
    }

    /**
     * @param $id
     *
     * @return mixed
     * @throws CurlException
     * @throws HttpException
     * @throws UnauthorizedException
     * @throws ValidationException
     */
    public function getPdfID($id)
    {
        if ($this->version == 3) {
            throw new ValidationException('Versão 1, 2 da API somente recupera PDF pelo nosso número');
        }

        return $this->oAuth2()->get($this->url('pdf', $id))->body;
    }

    /**
     * @param $boleto
     *
     * @return BoletoInter
     * @throws ValidationException
     */
    private function arrayToBoleto($boleto)
    {
        return BoletoInter::fromAPI($boleto, [
            'conta'        => $this->getConta(),
            'beneficiario' => $this->getBeneficiario(),
        ]);
    }

    /**
     * @param $type
     * @param $param
     * @return string
     */
    private function url($type, $param = null)
    {
        $aUrls = [
            1 => [
                'create' => 'openbanking/v1/certificado/boletos',
                'show'   => 'openbanking/v1/certificado/boletos/' . $param,
                'cancel' => 'openbanking/v1/certificado/boletos/' . $param . '/baixas',
                'pdf'    => 'openbanking/v1/certificado/boletos/' . $param . '/pdf',
                'search' => 'openbanking/v1/certificado/boletos?',
            ],
            2 => [
                'create'  => 'cobranca/v2/boletos',
                'show'    => 'cobranca/v2/boletos/' . $param,
                'cancel'  => 'cobranca/v2/boletos/' . $param . '/cancelar',
                'pdf'     => 'cobranca/v2/boletos/' . $param . '/pdf',
                'search'  => 'cobranca/v2/boletos?',
                'auth'    => '/oauth/v2/token',
                'webhook' => 'cobranca/v2/boletos/webhook',
            ],
            3 => [
                'create'  => 'cobranca/v3/cobrancas',
                'show'    => 'cobranca/v3/cobrancas/' . $param,
                'cancel'  => 'cobranca/v3/cobrancas/' . $param . '/cancelar',
                'pdf'     => 'cobranca/v3/cobrancas/' . $param . '/pdf',
                'search'  => 'cobranca/v3/cobrancas?',
                'auth'    => '/oauth/v2/token',
                'webhook' => 'cobranca/v3/cobrancas/webhook',
            ],
        ];

        return Arr::get($aUrls, "$this->version.$type");
    }
}
