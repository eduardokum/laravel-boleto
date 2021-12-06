<?php
namespace Eduardokum\LaravelBoleto\Api;

use Eduardokum\LaravelBoleto\Contracts\Boleto\BoletoAPI as BoletoAPIContract;
use Symfony\Component\HttpKernel\Exception\HttpException;

abstract class AbstractAPI
{
    protected $baseUrl = null;
    protected $conta = null;
    protected $certificado = null;
    protected $certificadoChave = null;
    protected $certificadoSenha = null;

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

    public function getPdf(BoletoAPIContract $boleto)
    {
        return $this->getPdfNossoNumero($boleto->getNossoNumero());
    }

    /**
     * @return string|null
     */
    protected function getCertificado()
    {
        return $this->certificado;
    }

    /**
     * @return string|null
     */
    protected function getCertificadoChave()
    {
        return $this->certificadoChave;
    }

    /**
     * @return string|null
     */
    protected function getCertificadoSenha()
    {
        return $this->certificadoSenha;
    }

    protected function post($url, array $post)
    {
        $url = ltrim($url, '/');
        $this->init([
            'Accept' => 'application/json',
            'Content-type' => 'application/json'
        ]);

        $loop = 0;
        $error = null;
        do {
            $loop ++;
            curl_setopt($this->curl, CURLOPT_URL, $this->getBaseUrl() . $url);
            curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($this->curl, CURLOPT_POSTFIELDS, json_encode($post));

            if (!$exec = curl_exec($this->curl)) {
                $error = curl_error($this->curl);
            }
            $httpCode = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
            $info = curl_getinfo($this->curl);
            curl_close($this->curl);
            $this->curl = null;

            if ($httpCode == 503 && $loop <= 5) {
                $keep = true;
            } else {
                $keep = false;
            }
        } while($keep == true);

        if (!$httpCode && $error) {
            throw new \Exception("Curl error: " . $error);
        }

        $retorno = $this->parseResponse($exec);
        if ($httpCode < 200 || $httpCode > 299) {
            throw new \Eduardokum\LaravelBoleto\Api\Exception\HttpException(
                $httpCode,
                $info,
                $retorno->body_text
            );
        }
        return $retorno;
    }

    /**
     * @param $url
     *
     * @return \stdClass
     * @throws Exception\HttpException
     */
    protected function get($url)
    {
        $url = ltrim($url, '/');
        $this->init([
            'Accept' => 'application/json',
        ]);

        $loop = 0;
        $error = null;
        do {
            $loop ++;
            curl_setopt($this->curl, CURLOPT_URL, $this->getBaseUrl() . $url);
            curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'GET');
            if (!$exec = curl_exec($this->curl)) {
                $error = curl_error($this->curl);
            }
            $httpCode = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
            $info = curl_getinfo($this->curl);
            curl_close($this->curl);
            $this->curl = null;
            if ($httpCode == 503 && $loop <= 5) {
                $keep = true;
            } else {
                $keep = false;
            }
        } while($keep == true);

        if (!$httpCode && $error) {
            throw new \Exception("Curl error: " . $error);
        }

        $retorno = $this->parseResponse($exec);
        if ($httpCode < 200 || $httpCode > 299) {
            throw new \Eduardokum\LaravelBoleto\Api\Exception\HttpException(
                $httpCode,
                $info,
                $retorno->body_text
            );
        }
        return $retorno;
    }

    private function init($headers = [])
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

//        curl_setopt($curl, CURLOPT_VERBOSE, true);
//        curl_setopt($curl, CURLOPT_STDERR, fopen('php://stderr', 'w'));

        $headers = array_unique(
            array_merge(
                $this->convertHeaders($headers),
                $this->convertHeaders($this->headers())
            )
        );

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        $this->curl = $curl;
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
}