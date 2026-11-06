<?php

namespace Eduardokum\LaravelBoleto\PessoaLookup;

use Eduardokum\LaravelBoleto\Util;
use Eduardokum\LaravelBoleto\Contracts\PessoaLookup;
use Eduardokum\LaravelBoleto\Exception\ValidationException;

/**
 * Implementacao de referencia do contrato PessoaLookup usando a API da CPF.CNPJ.
 *
 * Faz GET em https://api.cpfcnpj.com.br/{token}/{pacote}/{documento} e devolve
 * os dados ja normalizados para o formato aceito por Pessoa::create.
 *
 * O transporte HTTP e injetavel: por padrao usa a extensao cURL (ja exigida
 * pelo pacote), mas qualquer callable no formato function ($url) { return
 * string; } pode ser passado, o que permite reaproveitar um cliente ja
 * presente na aplicacao (Guzzle, PSR-18) ou simular respostas em testes.
 */
class CpfCnpjComBrLookup implements PessoaLookup
{
    /**
     * @var string
     */
    protected $token;

    /**
     * @var int
     */
    protected $pacoteCpf;

    /**
     * @var int
     */
    protected $pacoteCnpj;

    /**
     * @var callable|null
     */
    protected $transporte;

    /**
     * @var string
     */
    protected $baseUrl = 'https://api.cpfcnpj.com.br';

    /**
     * @param string        $token      Token da API, obtido no painel em API > Tokens.
     * @param int           $pacoteCpf  Pacote usado nas consultas de CPF (3 traz nome e endereco).
     * @param int           $pacoteCnpj Pacote usado nas consultas de CNPJ (5 traz cadastro e endereco, 6 acrescenta situacao/porte/Simples Nacional).
     * @param callable|null $transporte Callable opcional no formato function ($url) { return string; }.
     *
     * @throws ValidationException
     */
    public function __construct($token, $pacoteCpf = 3, $pacoteCnpj = 5, callable $transporte = null)
    {
        $token = trim((string) $token);
        if ($token === '') {
            throw new ValidationException('Token da API CPF/CNPJ nao informado');
        }

        $this->token = $token;
        $this->pacoteCpf = (int) $pacoteCpf;
        $this->pacoteCnpj = (int) $pacoteCnpj;
        $this->transporte = $transporte;
    }

    /**
     * Define a URL base da API (util para ambientes de teste ou proxy).
     *
     * @param string $baseUrl
     *
     * @return CpfCnpjComBrLookup
     */
    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = rtrim($baseUrl, '/');

        return $this;
    }

    /**
     * @inheritdoc
     *
     * @throws ValidationException
     */
    public function consultarCpf($cpf)
    {
        $documento = Util::onlyNumbers($cpf);
        if (strlen($documento) != 11) {
            throw new ValidationException('CPF invalido');
        }

        $dados = $this->consultar($this->pacoteCpf, $documento);

        return $this->normalizarCpf($dados, $documento);
    }

    /**
     * @inheritdoc
     *
     * @throws ValidationException
     */
    public function consultarCnpj($cnpj)
    {
        $documento = Util::onlyNumbers($cnpj);
        if (strlen($documento) != 14) {
            throw new ValidationException('CNPJ invalido');
        }

        $dados = $this->consultar($this->pacoteCnpj, $documento);

        return $this->normalizarCnpj($dados, $documento);
    }

    /**
     * Executa a consulta e devolve o payload decodificado.
     *
     * @param int    $pacote
     * @param string $documento
     *
     * @return array
     * @throws ValidationException
     */
    protected function consultar($pacote, $documento)
    {
        $url = sprintf('%s/%s/%d/%s', $this->baseUrl, $this->token, $pacote, $documento);

        $corpo = call_user_func($this->obterTransporte(), $url);
        if (! is_string($corpo) || trim($corpo) === '') {
            throw new ValidationException('Resposta vazia da API CPF/CNPJ');
        }

        $dados = json_decode($corpo, true);
        if (! is_array($dados)) {
            throw new ValidationException('Resposta invalida da API CPF/CNPJ');
        }

        if (! isset($dados['status']) || (int) $dados['status'] !== 1) {
            $mensagem = isset($dados['erro']) ? $dados['erro'] : 'Falha na consulta a API CPF/CNPJ';
            $codigo = isset($dados['erroCodigo']) ? (int) $dados['erroCodigo'] : 0;

            throw new ValidationException($mensagem . ($codigo ? ' (codigo ' . $codigo . ')' : ''));
        }

        return $dados;
    }

    /**
     * Normaliza a resposta de um CPF (campos no nivel raiz).
     *
     * @param array  $dados
     * @param string $documento
     *
     * @return array
     */
    protected function normalizarCpf(array $dados, $documento)
    {
        return [
            'nome'         => $this->valor($dados, 'nome'),
            'nomeFantasia' => null,
            'documento'    => $documento,
            'logradouro'   => $this->valor($dados, 'endereco'),
            'numero'       => $this->valor($dados, 'numero'),
            'complemento'  => $this->valor($dados, 'complemento'),
            'bairro'       => $this->valor($dados, 'bairro'),
            'cep'          => $this->valor($dados, 'cep'),
            'cidade'       => $this->valor($dados, 'cidade'),
            'uf'           => $this->valor($dados, 'uf'),
            'ibge'         => $this->valor($dados, 'ibge'),
        ];
    }

    /**
     * Normaliza a resposta de um CNPJ (endereco em matrizEndereco).
     *
     * @param array  $dados
     * @param string $documento
     *
     * @return array
     */
    protected function normalizarCnpj(array $dados, $documento)
    {
        $endereco = isset($dados['matrizEndereco']) && is_array($dados['matrizEndereco'])
            ? $dados['matrizEndereco']
            : [];

        $ibge = null;
        if (isset($dados['ibge']['cidade']['ibge_id'])) {
            $ibge = $dados['ibge']['cidade']['ibge_id'];
        }

        return [
            'nome'         => $this->valor($dados, 'razao'),
            'nomeFantasia' => $this->valor($dados, 'fantasia'),
            'documento'    => $documento,
            'logradouro'   => $this->montarLogradouro($endereco),
            'numero'       => $this->valor($endereco, 'numero'),
            'complemento'  => $this->valor($endereco, 'complemento'),
            'bairro'       => $this->valor($endereco, 'bairro'),
            'cep'          => $this->valor($endereco, 'cep'),
            'cidade'       => $this->valor($endereco, 'cidade'),
            'uf'           => $this->valor($endereco, 'uf'),
            'ibge'         => $ibge,
        ];
    }

    /**
     * Monta o logradouro do CNPJ juntando o tipo (Rua, Av., etc) ao nome.
     *
     * O pacote de CNPJ devolve o tipo do logradouro em um campo proprio (tipo)
     * separado do nome (logradouro); sem juntar os dois o endereco perde o
     * "Rua"/"Av." e fica incompleto no pagador do boleto.
     *
     * @param array $endereco
     *
     * @return string|null
     */
    protected function montarLogradouro(array $endereco)
    {
        $tipo = $this->valor($endereco, 'tipo');
        $logradouro = $this->valor($endereco, 'logradouro');

        $completo = trim(($tipo !== null ? $tipo . ' ' : '') . ($logradouro !== null ? $logradouro : ''));

        return $completo !== '' ? $completo : null;
    }

    /**
     * Le uma chave do array tratando string vazia como ausencia.
     *
     * @param array  $dados
     * @param string $chave
     *
     * @return string|null
     */
    protected function valor(array $dados, $chave)
    {
        if (! isset($dados[$chave])) {
            return null;
        }

        $valor = is_string($dados[$chave]) ? trim($dados[$chave]) : $dados[$chave];

        return ($valor === '' || $valor === null) ? null : $valor;
    }

    /**
     * Devolve o transporte configurado ou o transporte cURL padrao.
     *
     * @return callable
     */
    protected function obterTransporte()
    {
        if ($this->transporte !== null) {
            return $this->transporte;
        }

        return function ($url) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);

            $corpo = curl_exec($ch);
            $erro = curl_error($ch);
            $codigo = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($corpo === false || $erro !== '') {
                throw new ValidationException('Erro de conexao com a API CPF/CNPJ: ' . $erro);
            }

            if ($codigo < 200 || $codigo >= 300) {
                throw new ValidationException('API CPF/CNPJ respondeu com HTTP ' . $codigo);
            }

            return $corpo;
        };
    }
}
