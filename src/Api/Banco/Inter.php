<?php
namespace Eduardokum\LaravelBoleto\Api\Banco;

use Carbon\Carbon;
use Eduardokum\LaravelBoleto\Api\AbstractAPI;
use Eduardokum\LaravelBoleto\Contracts\Boleto\BoletoAPI as BoletoAPIContract;
use Eduardokum\LaravelBoleto\Util;

class Inter extends AbstractAPI
{
    protected $baseUrl = 'https://apis.bancointer.com.br';

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

    /**
     * @return array
     */
    protected function headers()
    {
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
        $data = $boleto->toArrayAPI();
        $retorno = $this->post('openbanking/v1/certificado/boletos', $data);
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
        $params = [
            'filtrarPor'     =>
                array_key_exists('filtrarPor', $inputedParams)
                    ? $inputedParams['filtrarPor']
                    : 'TODOS',
            'filtrarDataPor' =>
                array_key_exists('filtrarDataPor', $inputedParams)
                    ? $inputedParams['filtrarDataPor']
                    : 'VENCIMENTO',
            'dataInicial'    =>
                array_key_exists('dataInicial', $inputedParams)
                    ? $inputedParams['dataInicial']
                    : Carbon::now()->startOfMonth()->format('Y-m-d'),
            'dataFinal'      =>
                array_key_exists('dataFinal', $inputedParams)
                    ? $inputedParams['dataFinal']
                    : Carbon::now()->endOfMonth()->format('Y-m-d'),
            'ordenarPor'     =>
                array_key_exists('ordenarPor', $inputedParams)
                    ? $inputedParams['ordenarPor']
                    : 'NOSSONUMERO'
        ];

        $params['size'] = 50;
        $params['page'] = 0;
        $aRetorno = [];
        do {
            $retorno = $this->get(
                '/openbanking/v1/certificado/boletos?' . http_build_query($params)
            );
            array_push($aRetorno, ...$retorno->body->content);
            $params['page'] += 1;
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
        return $this->get(
            '/openbanking/v1/certificado/boletos/' . $nossoNumero
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
            'SUBISTITUICAO',
            'FALTADESOLUCAO',
            'APEDIDODOCLIENTE',
        ];

        if (!in_array(Util::upper($motivo), $motivosValidos)) {
            $motivo = 'ACERTOS';
        }
        return $this->post(
            '/openbanking/v1/certificado/boletos/' . $nossoNumero . '/baixas',
            ['codigoBaixa' => $motivo]
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
        return $this->get(
            '/openbanking/v1/certificado/boletos/' . $nossoNumero . '/pdf'
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
        return \Eduardokum\LaravelBoleto\Boleto\Banco\Inter::createFromAPI($boleto, [
            'conta'        => $this->getConta(),
            'beneficiario' => $this->getBeneficiario(),
        ]);
    }
}