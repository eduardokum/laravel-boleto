<?php

namespace Eduardokum\LaravelBoleto\Api\Banco;

use Illuminate\Support\Arr;
use Eduardokum\LaravelBoleto\Api\AbstractAPI;
use Eduardokum\LaravelBoleto\Api\Exception\CurlException;
use Eduardokum\LaravelBoleto\Api\Exception\HttpException;
use Eduardokum\LaravelBoleto\Exception\ValidationException;
use Eduardokum\LaravelBoleto\Api\Exception\UnauthorizedException;
use Eduardokum\LaravelBoleto\Boleto\Banco\Unicred as BoletoUnicred;
use Eduardokum\LaravelBoleto\Contracts\Boleto\BoletoAPI as BoletoAPIContract;

class Unicred extends AbstractAPI
{
    protected $baseUrl = 'https://api.e-unicred.com.br/';

    /** 
     * @var int $version
     **/
    private $version = 2;

    /**
     * Usuario Autenticação
     *
     * @var string|null
     */
    protected $usuario = null;

    /**
     * Cooperativa Autenticação
     *
     * @var string|null
     */
    protected $cooperativa = null;

    /**
     * Chave da API Autenticação
     *
     * @var string|null
     */
    protected $api_key = null;

    /**
     * Campos que são necessários para o boleto
     *
     * @var array
     */
    protected $camposObrigatorios = [
        'usuario',
        'senha',
        'identificador',
        'cooperativa',
        'apiKey',
    ];

    public function __construct($params=[])
    {
        if (isset($params['versao']) && $params['versao'] != 2) {
            throw new ValidationException('Somente Versão 2 da Api homologada');
        }

        if (isset($params['ambiente']) && $params['ambiente'] == 'H') {
            $this->baseUrl = 'https://api.e-unicred.com.br/homolog/';
        }

        parent::__construct($params);
    }

    /**
     * @return string|null
     */
    public function getUsuario()
    {
        return $this->usuario;
    }

    /**
     * @param string|null $usuario
     *
     * @return AbstractAPI
     */
    public function setUsuario($usuario)
    {
        $this->usuario = $usuario;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCooperativa()
    {
        return $this->cooperativa;
    }

    /**
     * @param string|null $cooperativa
     *
     * @return AbstractAPI
     */
    public function setCooperativa($cooperativa)
    {
        $this->cooperativa = $cooperativa;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getApiKey()
    {
        return $this->api_key;
    }

    /**
     * @param string|null $api_key
     *
     * @return AbstractAPI
     */
    public function setApiKey($api_key)
    {
        $this->api_key = $api_key;

        return $this;
    }

    /**
     * @return Unicred
     * 
     * @throws CurlException
     * @throws HttpException
     * @throws UnauthorizedException
     */
    protected function oAuth2()
    {
        if ($this->version !== 2) {
            throw new ValidationException('Somente Versão 2 da Api homologada');
        }

        $grant = $this->post($this->url('auth'),
            $post=[
                'nomeUsuario'     => $this->getUsuario(),
                'senha'           => $this->getSenha(),
            ],
            $raw=false,
            $clear=false
        )->body;

        return $this->setAccessToken('Bearer ' . $grant->accessToken);
    }

    /**
     * @return array
     */
    protected function headers()
    {
        if ($this->version == 2) {
            return array_filter([
                'Authorization' => $this->getAccessToken(),
                'Cooperativa' => $this->getCooperativa(),
                'apiKey' => $this->getApiKey()
            ]);
        }

        return [];
    }

    /**
     * @param BoletoAPIContract $boleto
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

        $retorno = $this->oAuth2()->post($this->url('create'), $data);

        $boleto->setID(trim($retorno->body_text));

        $retorno = $this->oAuth2()->get($this->url('show', $boleto->getID()))->body;

        $boleto->setNossoNumero($retorno->nossoNumero);

        return $boleto;
    }

    /**
     * @param BoletoAPIContract $boleto
     * @return mixed
     */
    public function retrieve(BoletoAPIContract $boleto)
    {
        return $this->retrieveID($boleto->getId());
    }

    /**
     * @param array $inputedParams
     *
     * @return mixed
     * @throws ValidationException
     */
    public function retrieveList($inputedParams=[])
    {
        throw new ValidationException('Método não disponível no banco');
    }
    
    /**
     * @param $nossoNumero
     *
     * @return mixed
     * @throws ValidationException
     */
    public function retrieveNossoNumero($nossoNumero)
    {
        throw new ValidationException('Método não disponível no banco');
    }

    /**
     * @param string $id
     * 
     * @return mixed
     * @throws CurlException
     * @throws HttpException
     * @throws UnauthorizedException
     * @throws ValidationException
     */
    public function retrieveID($id)
    {
        if ($this->version != 2) {
            throw new ValidationException('Somente versão 2 API recupera boleto pelo nosso id');
        }

        $retorno = $this->oAuth2()->get($this->url('show', $id))->body;

        return $this->arrayToBoleto($retorno);
    }

    /**
     * @param string $nossoNumero
     * @param string $motivo
     *
     * @return mixed
     * @throws ValidationException
     */
    public function cancelNossoNumero($nossoNumero, $motivo = 'ACERTOS')
    {
        throw new ValidationException('Método não disponível no banco');
    }

    /**
     * @param        $id
     * @param string $motivo
     *
     * @return mixed
     * @throws ValidationException
     */
    public function cancelID($id, $motivo)
    {
        throw new ValidationException('Método não disponível no banco');
    }

    /**
     * @param $nossoNumero
     *
     * @return mixed
     * @throws ValidationException
     */
    public function getPdfNossoNumero($nossoNumero)
    {
        throw new ValidationException('Método não disponível no banco');
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
        if ($this->version != 2) {
            throw new ValidationException('Somente versão 2 API permite busca de PDF');
        }

        return $this->oAuth2()->get($this->url('pdf', $id))->body_text;
    }

    /**
     * @param $boleto
     *
     * @return BoletoUnicred
     * @throws ValidationException
     */
    private function arrayToBoleto($boleto)
    {
        return BoletoUnicred::fromAPI($boleto, [
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
        $codigoBeneficiario = $this->getIdentificador();

        $aUrls = [
            2 => [
                'create'  => "cobranca/v2/beneficiarios/{$codigoBeneficiario}/titulos",
                'show'    => "cobranca/v2/beneficiarios/{$codigoBeneficiario}/titulos/{$param}/status",
                'pdf'     => "cobranca/v2/beneficiarios/{$codigoBeneficiario}/titulos/{$param}",
                'auth'    => 'oauth2/v2/grant-token',
            ]
        ];

        return Arr::get($aUrls, "{$this->version}.{$type}");
    }
}