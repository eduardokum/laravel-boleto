<?php
namespace Eduardokum\LaravelBoleto\Api\Banco;

use Carbon\Carbon;
use Eduardokum\LaravelBoleto\Api\AbstractAPI;
use Eduardokum\LaravelBoleto\Contracts\Boleto\BoletoAPI as BoletoAPIContract;
use Eduardokum\LaravelBoleto\Util;
use Illuminate\Support\Arr;

class Inter extends AbstractAPI
{
    protected $baseUrl = 'https://apis.bancointer.com.br';

    private $version = 1;

    /**
     * Campos que são necessários para o boleto
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
        if (isset($params['versao']) && $params['versao'] == 2) {
            $this->version = 2;
            $this->camposObrigatorios = [
                'certificado',
                'certificadoChave',
                'client_id',
                'client_secret'
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
        $grant = $this->post('/oauth/v2/token', [
            'client_id'     => $this->getClientId(),
            'client_secret' => $this->getClientSecret(),
            'scope'         => 'boleto-cobranca.read boleto-cobranca.write',
            'grant_type'    => 'client_credentials'
        ], true)->body;
        return $this->setAccessToken('Bearer ' . $grant->access_token);
    }

    /**
     * @return array
     */
    protected function headers()
    {
        if ($this->version == 2) {
            return array_filter([
                'Authorization' => $this->getAccessToken(),
            ]);
        }
        return [
            'x-inter-conta-corrente' => $this->getConta(),
        ];
    }

    /**
     * @param BoletoAPIContract $boleto
     *
     * @return BoletoAPIContract
     * @throws \Eduardokum\LaravelBoleto\Api\Exception\CurlException
     * @throws \Eduardokum\LaravelBoleto\Api\Exception\HttpException
     * @throws \Eduardokum\LaravelBoleto\Api\Exception\UnauthorizedException
     */
    public function createBoleto(BoletoAPIContract $boleto)
    {
        $data = $boleto->toAPI();
        if ($this->version == 2) {
            unset($data['dataEmissao']);
            unset($data['dataLimite']);
            $data['numDiasAgenda'] = (int) $boleto->getDiasBaixaAutomatica();
            $data['pagador']['cpfCnpj'] = $data['pagador']['cnpjCpf'];
            unset($data['pagador']['cnpjCpf']);
        }
        $retorno = $this->oAuth2()->post(
            $this->version == 1
                ? 'openbanking/v1/certificado/boletos'
                : 'cobranca/v2/boletos',
            $data
        );
        $boleto->setNossoNumero($retorno->body->nossoNumero);
        return $boleto;
    }

    /**
     * @param array $inputedParams
     *
     * @return array
     * @throws \Eduardokum\LaravelBoleto\Api\Exception\CurlException
     * @throws \Eduardokum\LaravelBoleto\Api\Exception\HttpException
     * @throws \Eduardokum\LaravelBoleto\Api\Exception\UnauthorizedException
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
        ], function($v) { return !is_null($v); });

        $aRetorno = [];
        do {
            $retorno = $this->oAuth2()->get(
                ($this->version == 1
                    ? 'openbanking/v1/certificado/boletos?'
                    : 'cobranca/v2/boletos?')
                . http_build_query($params)
            );
            array_push($aRetorno, ...$retorno->body->content);
            if ($this->version == 1) {
                $params['page'] += 1;
            } else {
                $params['paginaAtual'] += 1;
            }
        } while (!$retorno->body->last);
        return array_map([$this, 'arrayToBoleto'], $aRetorno);
    }

    /**
     * @param $nossoNumero
     *
     * @return mixed
     * @throws \Eduardokum\LaravelBoleto\Api\Exception\CurlException
     * @throws \Eduardokum\LaravelBoleto\Api\Exception\HttpException
     * @throws \Eduardokum\LaravelBoleto\Api\Exception\UnauthorizedException
     */
    public function retrieveNossoNumero($nossoNumero)
    {
        return $this->oAuth2()->get(
            $this->version == 1
                ? 'openbanking/v1/certificado/boletos/' . $nossoNumero
                : 'cobranca/v2/boletos/' . $nossoNumero
        )->body;
    }

    /**
     * @param        $nossoNumero
     * @param string $motivo
     *
     * @return mixed
     * @throws \Eduardokum\LaravelBoleto\Api\Exception\CurlException
     * @throws \Eduardokum\LaravelBoleto\Api\Exception\HttpException
     * @throws \Eduardokum\LaravelBoleto\Api\Exception\UnauthorizedException
     */
    public function cancelNossoNumero($nossoNumero, $motivo = 'ACERTOS')
    {
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

        if (!in_array(Util::upper($motivo), $motivosValidos)) {
            $motivo = 'ACERTOS';
        }
        return $this->oAuth2()->post(
            $this->version == 1
                ? 'openbanking/v1/certificado/boletos/' . $nossoNumero . '/baixas'
                : 'cobranca/v2/boletos/' . $nossoNumero . '/cancelar',
            $this->version == 1
                ? ['codigoBaixa' => $motivo]
                : ['motivoCancelamento' => $motivo]
        )->body;
    }

    /**
     * @param $nossoNumero
     *
     * @return mixed
     * @throws \Eduardokum\LaravelBoleto\Api\Exception\CurlException
     * @throws \Eduardokum\LaravelBoleto\Api\Exception\HttpException
     * @throws \Eduardokum\LaravelBoleto\Api\Exception\UnauthorizedException
     */
    public function getPdfNossoNumero($nossoNumero)
    {
        return $this->oAuth2()->get(
            $this->version == 1
                ? 'openbanking/v1/certificado/boletos/' . $nossoNumero . '/pdf'
                : 'cobranca/v2/boletos/' . $nossoNumero . '/pdf'
        )->body;
    }

    /**
     * @param $boleto
     *
     * @return \Eduardokum\LaravelBoleto\Boleto\Banco\Inter
     * @throws \Exception
     */
    private function arrayToBoleto($boleto)
    {
        return \Eduardokum\LaravelBoleto\Boleto\Banco\Inter::fromAPI($boleto, [
            'conta'        => $this->getConta(),
            'beneficiario' => $this->getBeneficiario(),
        ]);
    }
}