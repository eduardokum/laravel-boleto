<?php
namespace Eduardokum\LaravelBoleto\Api\Banco;

use Carbon\Carbon;
use Eduardokum\LaravelBoleto\Api\AbstractAPI;
use Eduardokum\LaravelBoleto\Boleto\AbstractBoleto;
use Eduardokum\LaravelBoleto\Contracts\Boleto\BoletoAPI as BoletoAPIContract;
use Illuminate\Support\Arr;

class Inter extends AbstractAPI
{

    public function __construct($conta, $certificado, $certificadoChave, $certificadoSenha = null)
    {
        parent::__construct(
            'https://apis.bancointer.com.br',
            $conta,
            $certificado,
            $certificadoChave,
            $certificadoSenha
        );
    }

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
     * @return AbstractBoleto
     * @throws \Eduardokum\LaravelBoleto\Api\Exception\HttpException
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
     * @throws \Eduardokum\LaravelBoleto\Api\Exception\HttpException
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
                    : 'NOSSONUMERO',
            'page'           =>
                array_key_exists('page', $inputedParams)
                    ? $inputedParams['page']
                    : 0,
            'size'           =>
                array_key_exists('size', $inputedParams)
                    ? (($inputedParams['size'] > 5 && $inputedParams['size'] < 20) ? $inputedParams['size'] : 20)
                    : 20,
        ];

        $aRetorno = [];
        do {
            $retorno = $this->get(
                '/openbanking/v1/certificado/boletos?' . http_build_query($params)
            );
            array_push($aRetorno, ...$retorno->body->content);
        } while (!$retorno->body->last);
        return $aRetorno;
    }

    public function retrieveNossoNumero($nossoNumero)
    {
        return $this->get(
            '/openbanking/v1/certificado/boletos/' . $nossoNumero
        );
    }

    public function getPdfNossoNumero($nossoNumero)
    {
        return $this->get(
            '/openbanking/v1/certificado/boletos/' . $nossoNumero . '/pdf'
        );
    }
}