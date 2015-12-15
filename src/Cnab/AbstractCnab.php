<?php
namespace Eduardokum\LaravelBoleto\Cnab;

use Eduardokum\LaravelBoleto\Cnab\Contracts\Remessa\Detalhe;
use Eduardokum\LaravelBoleto\Util;

abstract class AbstractCnab
{

    const HEADER = 'header';
    const DETALHE = 'detalhe';
    const TRAILER = 'trailer';

    private $iRegistros = 0;
    private $aRegistros = [
        self::HEADER => [],
        self::DETALHE => [],
        self::TRAILER => [],
    ];
    private $atual;
    protected $fimLinha = "\n";
    protected $fimArquivo = null;

    public $idremessa;

    protected abstract function header();

    protected abstract function adicionaDetalhe(Detalhe $boleto);

    protected abstract function trailer();

    protected function getCount()
    {
        return count($this->aRegistros[self::DETALHE]) + 2;
    }

    protected function add($i, $f, $value)
    {
        $i--;

        if (!in_array($this->atual, [self::DETALHE, self::TRAILER, self::HEADER])) {
            throw new \Exception('Ultilize antes ' . __CLASS__ . '->iniciaLinha($tipo)');
        }

        if ($i > 398 || $f > 400) {
            throw new \Exception('$ini ou $fim ultrapassam o limite máximo de 400');
        }

        if ($f < $i) {
            throw new \Exception('$ini é maior que o $fim');
        }

        $t = $f - $i;

        if (strlen($value) > $t) {
            throw new \Exception('String $valor maior que o tamanho definido em $ini e $fim: $valor= ' . strlen($value) . ' e tamanho é de: ' . $t);
        }

        $value = sprintf("%{$t}s", $value);
        $value = str_split($value, 1);
        if($this->atual == self::DETALHE)
        {
            return array_splice($this->aRegistros[$this->atual][$this->iRegistros], $i, $t, $value);
        }
        return array_splice($this->aRegistros[$this->atual], $i, $t, $value);
    }

    protected function rem()
    {

    }

    protected function inicia($tipo)
    {
        $tipo = Util::lower($tipo);
        if(in_array($tipo, [self::DETALHE, self::TRAILER, self::HEADER])) {
            $this->atual = $tipo;
            if($this == self::DETALHE)
            {
                $this->iRegistros++;
                $this->aRegistros[$tipo][$this->iRegistros] = array_fill(0,400, ' ');
            }
            else
            {
                $this->aRegistros[$tipo] = array_fill(0,400, ' ');
            }
        } else {
            throw new \Exception('$tipo é inválido, aceito: {detalhe,trailer,header}');
        }
    }



    public function gerar()
    {
        $stringRemessa = '';
//        if(!$this->aRegistros) { throw new \Exception('Nenhuma linha detalhe foi adicionada'); }

        $this->header();
        $stringRemessa .= $this->get(self::HEADER).$this->fimLinha;

//        for($i=1;$i<=$this->aRegistros;$i++){
//            $stringRemessa .= $this->get('detalhe',$i).$this->fimLinha;
//        }
//
        $this->trailer();
        $stringRemessa .= $this->get('trailer');

        if(!empty($this->fim_arquivo)) {
            $stringRemessa .= $this->fimArquivo;
        }
//        dd($this->aRegistros);

        return $stringRemessa;
    }


    private function get($tipo, $count = null) {

        if( !in_array($tipo, array('detalhe','trailer','header') ) ) {
            throw new \Exception('$tipo: '.$tipo.', incorreto');
        }

        $arrayWork = array_filter(!isset($count) ? $this->aRegistros[$tipo] : $this->aRegistros[$tipo][$count], 'strlen');
        if(isset($arrayWork) ) {
            if (count($arrayWork) != 400) {
                throw new \Exception('$a não possui 400 posições, possui: ' . count($arrayWork));
            }
            return implode('', $arrayWork);
        } else {
            throw new \Exception('Campo '.$tipo.' não gerado ou inválido');
        }
    }

}