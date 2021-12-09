<?php
namespace Eduardokum\LaravelBoleto\Api;

use Eduardokum\LaravelBoleto\Contracts\Boleto\BoletoAPI as BoletoAPIContract;

abstract class AbstractAPI
{
    protected $baseUrl = null;
    protected $conta = null;
    protected $certificado = null;
    protected $certificadoChave = null;
    protected $certificadoSenha = null;
    protected $debug = false;
    protected $log = null;
    private $responseHttpCode = null;
    private $requestInfo = null;

    protected $curl = null;

    public function __construct($baseUrl, $conta, $certificado, $certificadoChave, $certificadoSenha = null)
    {
        $this->baseUrl = $baseUrl;
        $this->conta = $conta;
        $this->certificado = $certificado;
        $this->certificadoChave = $certificadoChave;
        $this->certificadoSenha = $certificadoSenha;
    }

    abstract protected function headers();
    abstract public function createBoleto(BoletoAPIContract $boleto);
    abstract public function retrieveNossoNumero($nossoNumero);
    abstract public function cancelNossoNumero($nossoNumero, $motivo);
    abstract public function retrieveList($inputedParams = []);
    abstract public function getPdfNossoNumero($nossoNumero);

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
     * @return string|null
     */
    public function getCertificado()
    {
        return $this->certificado;
    }

    /**
     * @return string|null
     */
    public function getCertificadoChave()
    {
        return $this->certificadoChave;
    }

    /**
     * @return string|null
     */
    public function getCertificadoSenha()
    {
        return $this->certificadoSenha;
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
        return stream_get_contents($this->log);
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
     * @throws Exception\CurlException
     * @throws Exception\HttpException|Exception\UnauthorizedException
     */
    protected function post($url, array $post)
    {
        $url = ltrim($url, '/');
        $this->init()
            ->setHeaders([
                'Accept' => 'application/json',
                'Content-type' => 'application/json'
            ]);
        curl_setopt($this->curl, CURLOPT_URL, $this->getBaseUrl() . $url);
        curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, json_encode($post));
        return $this->execute();
    }

    /**
     * @param $url
     *
     * @return \stdClass
     * @throws Exception\HttpException
     * @throws Exception\CurlException|Exception\UnauthorizedException
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
        curl_setopt($curl, CURLOPT_CAPATH, "/etc/ssl/certs/");
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
        $headers = array_unique(
            array_merge(
                $this->convertHeaders($headers),
                $this->convertHeaders($this->headers())
            )
        );

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
     * @return \stdClass
     */
    private function parseResponse($response)
    {
        $retorno = new \stdClass();
        $retorno->headers_text = substr($response, 0, strpos($response, "\r\n\r\n"));
        $retorno->body_text = substr($response, strpos($response, "\r\n\r\n"));

        $retorno->headers = [];
        foreach (explode("\r\n", $retorno->headers_text) as $i => $line) {
            if ($i === 0) {
                $retorno->headers['http_code'] = $line;
            } else {
                list ($key, $value) = explode(': ', $line);
                $retorno->headers[$key] = $value;
            }
        }
        $retorno->body = json_decode($retorno->body_text);

        return $retorno;
    }

    /**
     * @param $retorno
     *
     * @throws Exception\HttpException
     * @throws Exception\UnauthorizedException
     */
    private function handleException($retorno)
    {
        if ($this->getResponseHttpCode() < 200 || $this->getResponseHttpCode() > 299) {
            if (in_array($this->getResponseHttpCode(), [401, 403]) && empty($retorno->body_text)) {
                throw new Exception\UnauthorizedException(
                    $this->getBaseUrl(),
                    $this->getCertificado(),
                    $this->getCertificadoChave(),
                    $this->getCertificadoSenha()
                );
            }

            throw new Exception\HttpException(
                $this->getResponseHttpCode(),
                $this->getRequestInfo(),
                $retorno->body_text
            );
        }
    }

    /**
     * @return false|\stdClass
     * @throws Exception\CurlException
     * @throws Exception\HttpException
     * @throws Exception\UnauthorizedException
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
            $loop ++;
        } while($keep == true);

        $error = curl_error($this->curl);
        curl_close($this->curl);
        $this->curl = null;
        if (!$this->getResponseHttpCode() && $error) {
            throw new Exception\CurlException($error);
        }
        return false;
    }
}