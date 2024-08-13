<?php

namespace Eduardokum\LaravelBoleto;

use Carbon\Carbon;
use Eduardokum\LaravelBoleto\Exception\ValidationException;
use Eduardokum\LaravelBoleto\Contracts\NotaFiscal as NotaFiscalContract;

class NotaFiscal implements NotaFiscalContract
{
    /**
     * @var string
     */
    protected $chave;

    /**
     * @var string|null
     */
    protected $valor = null;

    /**
     * @var Carbon|null
     */
    protected $data = null;

    /**
     * @var string|null
     */
    protected $numero = null;

    /**
     * @param $chave
     * @param null $numero
     * @param null $data
     * @param null $valor
     * @return NotaFiscal
     */
    public static function create($chave, $numero = null, $data = null, $valor = null)
    {
        return new static([
            'chave'  => $chave,
            'numero' => $numero,
            'data'   => $data,
            'valor'  => $valor,
        ]);
    }

    /**
     * Construtor
     *
     * @param array $params
     */
    public function __construct($params = [])
    {
        Util::fillClass($this, $params);
    }

    /**
     * @return string
     */
    public function getChave()
    {
        if (strlen($this->chave) != 44) {
            return null;
        }

        return $this->chave;
    }

    /**
     * @param $chave
     * @return NotaFiscal
     * @throws ValidationException
     */
    public function setChave($chave)
    {
        if (strlen($chave) != 44) {
            throw new ValidationException('Chave da nfe não é válida: ' . $chave);
        }
        $this->chave = $chave;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getValor()
    {
        return $this->valor;
    }

    /**
     * @param string|null $valor
     */
    public function setValor($valor): NotaFiscal
    {
        $this->valor = $valor;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getData($format = 'dmy')
    {
        return $this->data ? $this->data->format($format) : null;
    }

    /**
     * @param Carbon $data
     * @return NotaFiscal
     */
    public function setData(Carbon $data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getNumero()
    {
        if (is_null($this->numero) && ! is_null($this->chave)) {
            return ltrim(substr($this->chave, 25, 9), 0);
        }

        return $this->numero;
    }

    /**
     * @param string|null $numero
     * @return NotaFiscal
     */
    public function setNumero($numero)
    {
        $this->numero = $numero;

        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'chave'  => $this->getChave(),
            'numero' => $this->getNumero(),
            'data'   => $this->getData(),
            'valor'  => $this->getValor(),
        ];
    }
}
