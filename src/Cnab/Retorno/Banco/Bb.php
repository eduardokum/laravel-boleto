<?php
namespace Eduardokum\LaravelBoleto\Cnab\Retorno\Banco;

use Carbon\Carbon;
use Eduardokum\LaravelBoleto\Cnab\Contracts\Retorno;
use Eduardokum\LaravelBoleto\Cnab\Retorno\AbstractCnab;
use Eduardokum\LaravelBoleto\Cnab\Retorno\Detalhe\Bb as Detalhe;
use Eduardokum\LaravelBoleto\Cnab\Retorno\Header\Bb as Header;
use Eduardokum\LaravelBoleto\Cnab\Retorno\Trailer\Bb as Trailer;
use Eduardokum\LaravelBoleto\Util;

class Bb extends AbstractCnab implements Retorno
{

    private $i = 0;
    public $agencia;
    public $conta;

    public function __construct($file)
    {
        parent::__construct($file);

        $this->banco = self::COD_BANCO_BB;
        $this->bancoDesc = $this->bancos[self::COD_BANCO_BB];
        $this->agencia = (int) substr($this->file[0], 26, 4);
        $this->conta = (int) substr($this->file[0], 31, 8);
    }

    protected function processarHeader(array $header)
    {
        $this->header = new Header();
        $this->header->operacaoCodigo = $this->rem(2, 2, $header);
        $this->header->operacao = $this->rem(3, 9, $header);
        $this->header->servicoCodigo = $this->rem(10, 11, $header);
        $this->header->servico = $this->rem(12, 19, $header);
        $this->header->agencia = $this->rem(27, 30, $header);
        $this->header->agenciaDigito = $this->rem(31, 31, $header);
        $this->header->conta = $this->rem(32, 39, $header);
        $this->header->contaDigito = $this->rem(40, 40, $header);
        $this->header->cedenteNome = $this->rem(47, 76, $header);
        $this->header->data = Carbon::createFromFormat('dmy', $this->rem(95, 100, $header))->setTime(0, 0, 0);
        $this->header->convenio = $this->rem(150, 156, $header);
    }

    protected function processarDetalhe(array $detalhe)
    {
        $i = $this->i;
        $this->detalhe[$i] = new Detalhe();

        $this->detalhe[$i]->id = $this->rem(1, 1, $detalhe);

        switch($this->detalhe[$i]->id)
        {
            case 5:
                $this->detalhe[$i]->auxiliar = $detalhe;
                break;
            case 2:
                $this->i++;
                $this->processarDetalheCompartilhada($detalhe);
                break;
            case 3:
                $this->i++;
                $this->processarDetalheVendor($detalhe);
                break;
            case 7:
                $this->i++;
                $this->processarDetalheNormal($detalhe);
                break;
        }

    }

    protected function processarTrailer(array $trailer)
    {
        $this->trailer = new Trailer();
        $this->trailer->simplesQuantidade = Util::nFloat($this->rem(18, 25, $trailer));
        $this->trailer->simplesValor = Util::nFloat($this->rem(26, 39, $trailer)/100);
        $this->trailer->simplesAvisos = Util::nFloat($this->rem(40, 47, $trailer));

        $this->trailer->vinculadaQuantidade = Util::nFloat($this->rem(58, 65, $trailer));
        $this->trailer->vinculadaValor = Util::nFloat($this->rem(66, 79, $trailer)/100);
        $this->trailer->vinculadaAvisos = Util::nFloat($this->rem(80, 87, $trailer));

        $this->trailer->caucionadaQuantidade = Util::nFloat($this->rem(98, 105, $trailer));
        $this->trailer->caucionadaValor = Util::nFloat($this->rem(106, 119, $trailer)/100);
        $this->trailer->caucionadaAvisos = Util::nFloat($this->rem(120, 127, $trailer));

        $this->trailer->descontadaQuantidades = Util::nFloat($this->rem(138, 145, $trailer));
        $this->trailer->descontadaValor = Util::nFloat($this->rem(146, 159, $trailer)/100);
        $this->trailer->descontadaAvisos = Util::nFloat($this->rem(160, 167, $trailer));

        $this->trailer->vendorQuantidade = Util::nFloat($this->rem(218, 225, $trailer));
        $this->trailer->vendorValor = Util::nFloat($this->rem(226, 239, $trailer)/100);
        $this->trailer->vendorAvisos = Util::nFloat($this->rem(240, 247, $trailer));
    }

    private function processarDetalheCompartilhada($detalhe)
    {
        $i = $this->i;
    }

    private function processarDetalheVendor($detalhe)
    {
        $i = $this->i;
    }

    private function processarDetalheNormal($detalhe)
    {
        $i = $this->i;
    }


}