<?php
namespace Eduardokum\LaravelBoleto\Cnab\Remessa\Banco;

use Eduardokum\LaravelBoleto\Cnab\AbstractCnab;
use Eduardokum\LaravelBoleto\Cnab\Contracts\Remessa;
use Eduardokum\LaravelBoleto\Cnab\Contracts\Remessa\Detalhe;
use Eduardokum\LaravelBoleto\Util;

class Bb extends AbstractCnab implements Remessa
{

    public $agencia;
    public $conta;
    public $multa = false;
    public $cedenteDocumento;
    public $cedenteNome;
    public $convenio;
    public $convenioLider;

    public function __construct()
    {
        $this->fimLinha = chr(13).chr(10);
        $this->fimArquivo = chr(13).chr(10);
    }


    protected function header()
    {
        $this->inicia(self::HEADER);

        $this->convenio = preg_replace('/^0+/', '', $this->convenio);
//        if(empty($this->convenio))
//        {
//            throw new \Exception('Necessita informar o convenio');
//        }

        if(!isset($this->convenioLider) || empty($this->convenioLider))
        {
            $this->convenioLider = $this->convenio;
        }

        $this->add(1,         1,      '0' ,                                       'Identificação do Registro Header: “0” (zero)');
        $this->add(2,         2,      '1',                                        'Tipo de Operação: “1” (um)');
        $this->add(3,         9,      'REMESSA',                                  'Identificação por Extenso do Tipo de Operação');
        $this->add(10,        11,     '01',                                       'Identificação do Tipo de Serviço: “01”');
        $this->add(12,        19,     Util::formatCnab('X', 'COBRANCA', 8),         'Identificação por Extenso do Tipo de Serviço: “COBRANCA”');
        $this->add(20,        26,     '',                                         'Complemento do Registro: “Brancos”');
        $this->add(27,        30,     Util::formatCnab('9', $this->agencia, 4),     'Prefixo da Agência: Número da Agência onde está cadastrado o convênio líder do cedente');
        $this->add(31,        31,     Util::modulo11($this->agencia),           'Dígito Verificador - D.V. - do Prefixo da Agência.');
        $this->add(32,        39,     Util::formatCnab('9', $this->conta,8),'Número da Conta Corrente: Número da conta onde está cadastrado o Convênio Líder do Cedente');
        $this->add(40,        40,     Util::modulo11($this->conta),     'Dígito V erificador - D.V . - da Conta Corrente do Cedente');
        $this->add(41,        46,     '000000',                                   'Uso do Banco');
        $this->add(47,        76,     Util::formatCnab('X', $this->cedenteNome, 30),'Nome do Cedente');
        $this->add(77,        94,     Util::formatCnab('X', '001BANCODOBRASIL', 18),'001BANCODOBRASIL');
        $this->add(95,        100,    date('dmy'),                                'Data da Gravação: Informe no formado “DDMMAA”');
        $this->add(101,       107,    Util::formatCnab('9', $this->idremessa, 7),   'Seqüencial da Remessa');

        if( strlen($this->convenio) < 7 )
        {
            $this->add(108,       394,    '',                                         'Complemento do Registro: “Brancos”');
        }
        else
        {
            $this->add(108,       129,    '',                                         'Complemento do Registro: “Brancos”');
            $this->add(130,       136, Util::formatCnab('9', $this->convenioLider, 7),  'Número do Convênio Líder');
            $this->add(137,       394,    '',                                         'Complemento do Registro: “Brancos”');
        }

        $this->add(395,       400,    Util::formatCnab('N', 1, 6),                  'Seqüencial do Registro:”000001”');

        return $this;
    }

    protected function adicionaDetalhe(Detalhe $boleto)
    {
    }

    protected function trailer()
    {
        $this->inicia('trailer');

        //                   INICIO     FIM     VALOR                                       DESCRIÇÃO
        $this->add(1,         1,      '9' ,                                       'Identificação do Registro Trailer: “9”');
        $this->add(2,         394,    '',                                         'Complemento do Registro: “Brancos”');
        $this->add(395,       400,    Util::formatCnab('N', $this->getCount(), 6),            'Número Seqüencial do Registro no Arquivo');

        return $this;
    }
}