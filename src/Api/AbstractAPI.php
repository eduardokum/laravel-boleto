<?php

namespace Eduardokum\LaravelBoleto\Api;

use stdClass;
use Illuminate\Support\Str;
use Eduardokum\LaravelBoleto\Util;
use Eduardokum\LaravelBoleto\Pessoa;
use Eduardokum\LaravelBoleto\Contracts\Api\Api;
use Eduardokum\LaravelBoleto\Api\Exception\CurlException;
use Eduardokum\LaravelBoleto\Api\Exception\HttpException;
use Eduardokum\LaravelBoleto\Exception\ValidationException;
use Eduardokum\LaravelBoleto\Api\Exception\MissingDataException;
use Eduardokum\LaravelBoleto\Contracts\Pessoa as PessoaContract;
use Eduardokum\LaravelBoleto\Api\Exception\UnauthorizedException;
use Eduardokum\LaravelBoleto\Contracts\Boleto\BoletoAPI as BoletoAPIContract;

abstract class AbstractAPI implements Api
{
    protected $baseUrl = null;

    protected $conta = null;

    protected $certificado = null;

    protected $certificadoChave = null;

    protected $certificadoSenha = null;

    protected $identificador = null;

    protected $client_id = null;

    protected $client_secret = null;

    protected $senha = null;

    protected $cnpj = null;

    protected $access_token = null;

    protected $debug = false;

    protected $log = null;

    protected $beneficiario;

    private $curl = null;

    private $responseHttpCode = null;

    private $requestInfo = null;

    private $temps = [];

    /**
     * Campos necessários para o boleto
     *
     * @var array
     */
    protected $camposObrigatorios = [
        'conta',
        'cnpj',
        'certificado',
        'certificadoChave',
        'certificadoSenha',
        'identificador',
        'senha',
    ];

    /**
     * AbstractBoleto constructor.
     *
     * @param array $params
     *
     * @throws MissingDataException
     */
    public function __construct($params = [])
    {
        Util::fillClass($this, $params);
        $missing = [];
        foreach ($this->camposObrigatorios as $campo) {
            $test = call_user_func([$this, 'get' . Str::camel($campo)]);
            if ($test === '' || is_null($test)) {
                $missing[] = $campo;
            }
        }
        if (count($missing) > 0) {
            throw new MissingDataException($missing);
        }
    }

    public function __destruct()
    {
        foreach ($this->temps as $temp) {
            @unlink($temp);
        }
    }

    abstract protected function headers();

    abstract public function createBoleto(BoletoAPIContract $boleto);

    abstract public function retrieveNossoNumero($nossoNumero);

    abstract public function retrieveID($id);

    abstract public function cancelNossoNumero($nossoNumero, $motivo);

    abstract public function cancelID($id, $motivo);

    abstract public function retrieveList($inputedParams = []);

    abstract public function getPdfNossoNumero($nossoNumero);

    abstract public function getPdfID($id);

    /**
     * @param $url
     * @param $type
     * @return mixed
     * @throws ValidationException
     */
    public function createWebhook($url, $type = 'all')
    {
        throw new ValidationException('Método não disponível no banco');
    }

    public function retrieve(BoletoAPIContract $boleto)
    {
        return $this->retrieveNossoNumero($boleto->getNossoNumero());
    }

    public function cancel(BoletoAPIContract $boleto, $motivo)
    {
        return $this->cancelNossoNumero($boleto->getNossoNumero(), $motivo);
    }

    public function getPdf(BoletoAPIContract $boleto)
    {
        return $this->getPdfNossoNumero($boleto->getNossoNumero());
    }

    /**
     * Get API Base URL
     *
     * @return string|null
     */
    public function getBaseUrl()
    {
        return rtrim($this->baseUrl, '/') . '/';
    }

    /**
     * @return string|null
     */
    public function getConta()
    {
        return $this->conta;
    }

    /**
     * @param $conta
     *
     * @return $this
     */
    public function setConta($conta)
    {
        $this->conta = $conta;

        return $this;
    }

    /**
     * @return null
     */
    public function getCertificado()
    {
        return $this->certificado;
    }

    /**
     * @param null $certificado
     *
     * @return AbstractAPI
     */
    public function setCertificado($certificado)
    {
        $this->certificado = $certificado;

        return $this;
    }

    /**
     * @return null
     */
    public function getCertificadoChave()
    {
        return $this->certificadoChave;
    }

    /**
     * @param null $certificadoChave
     *
     * @return AbstractAPI
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
     * @return AbstractAPI
     */
    public function setCertificadoSenha($certificadoSenha)
    {
        $this->certificadoSenha = $certificadoSenha;

        return $this;
    }

    /**
     * @return null
     */
    public function getIdentificador()
    {
        return $this->identificador;
    }

    /**
     * @param null $identificador
     *
     * @return AbstractAPI
     */
    public function setIdentificador($identificador)
    {
        $this->identificador = $identificador;

        return $this;
    }

    /**
     * @return null
     */
    public function getSenha()
    {
        return $this->senha;
    }

    /**
     * @param null $senha
     *
     * @return AbstractAPI
     */
    public function setSenha($senha)
    {
        $this->senha = $senha;

        return $this;
    }

    /**
     * @return null
     */
    public function getCnpj()
    {
        return $this->cnpj;
    }

    /**
     * @param null $cnpj
     *
     * @return AbstractAPI
     */
    public function setCnpj($cnpj)
    {
        $this->cnpj = $cnpj;

        return $this;
    }

    /**
     * @return null
     */
    public function getClientId()
    {
        return $this->client_id;
    }

    /**
     * @param null $client_id
     *
     * @return AbstractAPI
     */
    public function setClientId($client_id)
    {
        $this->client_id = $client_id;

        return $this;
    }

    /**
     * @return null
     */
    public function getClientSecret()
    {
        return $this->client_secret;
    }

    /**
     * @param null $client_secret
     *
     * @return AbstractAPI
     */
    public function setClientSecret($client_secret)
    {
        $this->client_secret = $client_secret;

        return $this;
    }

    /**
     * @return null
     */
    public function getAccessToken()
    {
        return $this->access_token;
    }

    /**
     * @param $access_token
     *
     * @return AbstractAPI
     */
    public function setAccessToken($access_token)
    {
        $this->access_token = $access_token;

        return $this;
    }

    /**
     * @return PessoaContract
     */
    public function getBeneficiario()
    {
        return is_array($this->beneficiario) || is_null($this->beneficiario) ? new Pessoa() : $this->beneficiario;
    }

    /**
     * @param array $beneficiario
     *
     * @return AbstractAPI
     * @throws ValidationException
     */
    public function setBeneficiario($beneficiario)
    {
        Util::addPessoa($this->beneficiario, $beneficiario);

        return $this;
    }

    /**
     * @return $this
     */
    public function setDebug()
    {
        $this->debug = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function unsetDebug()
    {
        $this->debug = false;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDebug()
    {
        return $this->debug;
    }

    /**
     * @return null
     */
    public function getLog()
    {
        return $this->log .
            print_r($this->getRequestInfo(), true);
    }

    /**
     * @return $this
     */
    public function clearLog()
    {
        $this->log = null;

        return $this;
    }

    /**
     * @return null
     */
    protected function getResponseHttpCode()
    {
        return $this->responseHttpCode;
    }

    /**
     * @param null $responseHttpCode
     *
     * @return AbstractAPI
     */
    protected function setResponseHttpCode($responseHttpCode)
    {
        $this->responseHttpCode = $responseHttpCode;

        return $this;
    }

    /**
     * @return null
     */
    protected function getRequestInfo()
    {
        return $this->requestInfo;
    }

    /**
     * @param null $requestInfo
     *
     * @return AbstractAPI
     */
    protected function setRequestInfo($requestInfo)
    {
        $this->requestInfo = $requestInfo;

        return $this;
    }

    /**
     * @throws HttpException
     * @throws UnauthorizedException
     * @throws CurlException
     */
    protected function post($url, array $post, $raw = false)
    {
        $url = ltrim($url, '/');
        $this->init()
            ->setHeaders(array_filter([
                'Accept'       => $raw ? null : 'application/json',
                'Content-type' => $raw ? 'application/x-www-form-urlencoded' : 'application/json',
            ]));

        // clean string
        $post = $this->arrayMapRecursive(function ($data) {
            return Util::normalizeChars($data);
        }, $post);

        curl_setopt($this->curl, CURLOPT_URL, $this->getBaseUrl() . $url);
        curl_setopt($this->curl, CURLOPT_POST, 1);
        curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $raw ? http_build_query($post) : json_encode($post));

        return $this->execute();
    }

    /**
     * @throws HttpException
     * @throws UnauthorizedException
     * @throws CurlException
     */
    protected function put($url, array $post, $raw = false)
    {
        $url = ltrim($url, '/');
        $this->init()
            ->setHeaders(array_filter([
                'Accept'       => $raw ? null : 'application/json',
                'Content-type' => $raw ? 'application/x-www-form-urlencoded' : 'application/json',
            ]));

        // clean string
        $post = $this->arrayMapRecursive(function ($data) {
            return Util::normalizeChars($data);
        }, $post);

        curl_setopt($this->curl, CURLOPT_URL, $this->getBaseUrl() . $url);
        curl_setopt($this->curl, CURLOPT_POST, 1);
        curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $raw ? http_build_query($post) : json_encode($post));

        return $this->execute();
    }

    /**
     * @param $url
     *
     * @return stdClass
     * @throws HttpException
     * @throws UnauthorizedException
     * @throws CurlException
     */
    protected function get($url)
    {
        $url = ltrim($url, '/');
        $this->init()
            ->setHeaders([
                'Accept' => 'application/json',
            ]);

        curl_setopt($this->curl, CURLOPT_URL, $this->getBaseUrl() . $url);
        curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'GET');

        return $this->execute();
    }

    /**
     * @return $this
     */
    private function init()
    {
        if ($this->getCertificado()
            && ! file_exists($this->getCertificado())
            && openssl_x509_read($this->getCertificado())) {
            $this->setCertificado($this->tempFile($this->getCertificado()));
        }

        if ($this->getCertificadoChave()
            && ! file_exists($this->getCertificadoChave())
            && openssl_pkey_get_private($this->getCertificadoChave())) {
            $this->setCertificadoChave($this->tempFile($this->getCertificadoChave()));
        }

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_HEADER, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSLCERT, $this->getCertificado());
        curl_setopt($curl, CURLOPT_SSLKEY, $this->getCertificadoChave());
        if ($senha = $this->getCertificadoSenha()) {
            curl_setopt($curl, CURLOPT_KEYPASSWD, $senha);
        }
        curl_setopt($curl, CURLOPT_CAPATH, '/etc/ssl/certs/');
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        $this->curl = $curl;

        return $this;
    }

    /**
     * @param array $headers
     *
     * @return $this
     */
    private function setHeaders($headers = [])
    {
        $headers = array_unique(array_merge($this->convertHeaders($headers), $this->convertHeaders($this->headers())));

        curl_setopt($this->curl, CURLOPT_HTTPHEADER, $headers);

        return $this;
    }

    /**
     * @param array $headers
     *
     * @return array
     */
    private function convertHeaders(array $headers)
    {
        $compiledHeader = [];
        foreach ($headers as $param => $value) {
            if (is_integer($param) && preg_match('/([\w-]+): ?(.*)/g', $value)) {
                $compiledHeader[] = $value;
            } else {
                $compiledHeader[] = "$param: $value";
            }
        }

        return $compiledHeader;
    }

    /**
     * @param $response
     *
     * @return stdClass
     */
    private function parseResponse($response)
    {
        $retorno = new stdClass();
        $retorno->headers_text = substr($response, 0, strpos($response, "\r\n\r\n"));
        $retorno->body_text = substr($response, strpos($response, "\r\n\r\n"));

        $retorno->headers = [];
        foreach (explode("\r\n", $retorno->headers_text) as $i => $line) {
            if ($i === 0) {
                $retorno->headers['http_code'] = $line;
            } else {
                [$key, $value] = explode(': ', $line);
                $retorno->headers[$key] = $value;
            }
        }
        $retorno->body = json_decode($retorno->body_text);

        return $retorno;
    }

    /**
     * @param $retorno
     *
     * @throws HttpException
     * @throws UnauthorizedException
     */
    private function handleException($retorno)
    {
        if ($this->getResponseHttpCode() < 200 || $this->getResponseHttpCode() > 299) {
            if (in_array($this->getResponseHttpCode(), [401, 403]) && empty($retorno->body_text)) {
                throw new UnauthorizedException($this->getBaseUrl(), $this->getCertificado(), $this->getCertificadoChave(), $this->getCertificadoSenha());
            }

            throw new HttpException($this->getResponseHttpCode(), $this->getRequestInfo(), $retorno->body_text);
        }
    }

    /**
     * @return false|stdClass
     * @throws CurlException
     * @throws HttpException
     * @throws UnauthorizedException
     */
    private function execute()
    {
        $loop = 0;
        if ($this->isDebug()) {
            ob_start();
            $this->log = fopen('php://output', 'w');
            curl_setopt($this->curl, CURLOPT_VERBOSE, true);
            curl_setopt($this->curl, CURLOPT_STDERR, $this->log);
        }
        do {
            if ($exec = curl_exec($this->curl)) {
                $this->setResponseHttpCode(curl_getinfo($this->curl, CURLINFO_HTTP_CODE));
                $this->setRequestInfo(curl_getinfo($this->curl));
                curl_close($this->curl);
                $this->curl = null;

                if ($this->isDebug()) {
                    fclose($this->log);
                    $this->log = ob_get_clean();
                }
                $retorno = $this->parseResponse($exec);
                $this->handleException($retorno);

                return $retorno;
            }

            if ($this->isDebug()) {
                fclose($this->log);
                $this->log = ob_get_clean();
            }

            if ($this->getResponseHttpCode() == 503 && $loop < 5) {
                $keep = true;
                usleep(200000);  // 0.2 segundos
            } else {
                $keep = false;
            }
            $loop++;
        } while ($keep == true);

        $error = curl_error($this->curl);
        curl_close($this->curl);
        $this->curl = null;
        if (! $this->getResponseHttpCode() && $error) {
            throw new CurlException($error);
        }

        return false;
    }

    /**
     * @param $content
     *
     * @return false|string
     */
    private function tempFile($content)
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'certificate');
        $this->temps[] = $tmpFile;
        file_put_contents($tmpFile, $content);

        return $tmpFile;
    }

    /**
     * @param $callback
     * @param $input
     *
     * @return array
     */
    private function arrayMapRecursive($callback, $input)
    {
        $output = [];
        foreach ($input as $key => $data) {
            if (is_array($data)) {
                $output[$key] = $this->arrayMapRecursive($callback, $data);
            } else {
                $output[$key] = $callback($data);
            }
        }

        return $output;
    }
}
