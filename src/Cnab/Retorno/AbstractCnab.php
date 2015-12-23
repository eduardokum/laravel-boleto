<?php
namespace Eduardokum\LaravelBoleto\Cnab\Retorno;

use Eduardokum\LaravelBoleto\Cnab\Contracts\Cnab;

abstract class AbstractCnab implements Cnab, \Countable, \SeekableIterator
{

    protected $bancos = [
        '246' => 'Banco ABC Brasil S.A.',
        '025' => 'Banco Alfa S.A.',
        '641' => 'Banco Alvorada S.A.',
        '029' => 'Banco Banerj S.A.',
        '000' => 'Banco Bankpar S.A.',
        '740' => 'Banco Barclays S.A.',
        '107' => 'Banco BBM S.A.',
        '031' => 'Banco Beg S.A.',
        '739' => 'Banco BGN S.A.',
        '096' => 'Banco BM&F de Serviços de Liquidação e Custódia S.A',
        '318' => 'Banco BMG S.A.',
        '752' => 'Banco BNP Paribas Brasil S.A.',
        '248' => 'Banco Boavista Interatlântico S.A.',
        '218' => 'Banco Bonsucesso S.A.',
        '065' => 'Banco Bracce S.A.',
        '036' => 'Banco Bradesco BBI S.A.',
        '204' => 'Banco Bradesco Cartões S.A.',
        '394' => 'Banco Bradesco Financiamentos S.A.',
        '237' => 'Banco Bradesco S.A.',
        '225' => 'Banco Brascan S.A.',
        '208' => 'Banco BTG Pactual S.A.',
        '044' => 'Banco BVA S.A.',
        '263' => 'Banco Cacique S.A.',
        '473' => 'Banco Caixa Geral - Brasil S.A.',
        '040' => 'Banco Cargill S.A.',
        '233' => 'Banco Cifra S.A.',
        '745' => 'Banco Citibank S.A.',
        'M08' => 'Banco Citicard S.A.',
        'M19' => 'Banco CNH Capital S.A.',
        '215' => 'Banco Comercial e de Investimento Sudameris S.A.',
        '756' => 'Banco Cooperativo do Brasil S.A. - BANCOOB',
        '748' => 'Banco Cooperativo Sicredi S.A.',
        '222' => 'Banco Credit Agricole Brasil S.A.',
        '505' => 'Banco Credit Suisse (Brasil) S.A.',
        '229' => 'Banco Cruzeiro do Sul S.A.',
        '003' => 'Banco da Amazônia S.A.',
        '083' => 'Banco da China Brasil S.A.',
        '707' => 'Banco Daycoval S.A.',
        'M06' => 'Banco de Lage Landen Brasil S.A.',
        '024' => 'Banco de Pernambuco S.A. - BANDEPE',
        '456' => 'Banco de Tokyo-Mitsubishi UFJ Brasil S.A.',
        '214' => 'Banco Dibens S.A.',
        '001' => 'Banco do Brasil S.A.',
        '047' => 'Banco do Estado de Sergipe S.A.',
        '037' => 'Banco do Estado do Pará S.A.',
        '041' => 'Banco do Estado do Rio Grande do Sul S.A.',
        '004' => 'Banco do Nordeste do Brasil S.A.',
        '265' => 'Banco Fator S.A.',
        'M03' => 'Banco Fiat S.A.',
        '224' => 'Banco Fibra S.A.',
        '626' => 'Banco Ficsa S.A.',
        '394' => 'Banco Finasa BMC S.A.',
        'M18' => 'Banco Ford S.A.',
        'M07' => 'Banco GMAC S.A.',
        '612' => 'Banco Guanabara S.A.',
        'M22' => 'Banco Honda S.A.',
        '063' => 'Banco Ibi S.A. Banco Múltiplo',
        'M11' => 'Banco IBM S.A.',
        '604' => 'Banco Industrial do Brasil S.A.',
        '320' => 'Banco Industrial e Comercial S.A.',
        '653' => 'Banco Indusval S.A.',
        '249' => 'Banco Investcred Unibanco S.A.',
        '184' => 'Banco Itaú BBA S.A.',
        '479' => 'Banco ItaúBank S.A',
        'M09' => 'Banco Itaucred Financiamentos S.A.',
        '376' => 'Banco J. P. Morgan S.A.',
        '074' => 'Banco J. Safra S.A.',
        '217' => 'Banco John Deere S.A.',
        '600' => 'Banco Luso Brasileiro S.A.',
        '389' => 'Banco Mercantil do Brasil S.A.',
        '746' => 'Banco Modal S.A.',
        '045' => 'Banco Opportunity S.A.',
        '079' => 'Banco Original do Agronegócio S.A.',
        '623' => 'Banco Panamericano S.A.',
        '611' => 'Banco Paulista S.A.',
        '643' => 'Banco Pine S.A.',
        '638' => 'Banco Prosper S.A.',
        '747' => 'Banco Rabobank International Brasil S.A.',
        '356' => 'Banco Real S.A.',
        '633' => 'Banco Rendimento S.A.',
        'M16' => 'Banco Rodobens S.A.',
        '072' => 'Banco Rural Mais S.A.',
        '453' => 'Banco Rural S.A.',
        '422' => 'Banco Safra S.A.',
        '033' => 'Banco Santander (Brasil) S.A.',
        '749' => 'Banco Simples S.A.',
        '366' => 'Banco Société Générale Brasil S.A.',
        '637' => 'Banco Sofisa S.A.',
        '012' => 'Banco Standard de Investimentos S.A.',
        '464' => 'Banco Sumitomo Mitsui Brasileiro S.A.',
        '082' => 'Banco Topázio S.A.',
        'M20' => 'Banco Toyota do Brasil S.A.',
        '634' => 'Banco Triângulo S.A.',
        'M14' => 'Banco Volkswagen S.A.',
        'M23' => 'Banco Volvo (Brasil) S.A.',
        '655' => 'Banco Votorantim S.A.',
        '610' => 'Banco VR S.A.',
        '119' => 'Banco Western Union do Brasil S.A.',
        '370' => 'Banco WestLB do Brasil S.A.',
        '021' => 'BANESTES S.A. Banco do Estado do Espírito Santo',
        '719' => 'Banif-Banco Internacional do Funchal (Brasil)S.A.',
        '755' => 'Bank of America Merrill Lynch Banco Múltiplo S.A.',
        '073' => 'BB Banco Popular do Brasil S.A.',
        '250' => 'BCV - Banco de Crédito e Varejo S.A.',
        '078' => 'BES Investimento do Brasil S.A.-Banco de Investimento',
        '069' => 'BPN Brasil Banco Múltiplo S.A.',
        '070' => 'BRB - Banco de Brasília S.A.',
        '104' => 'Caixa Econômica Federal',
        '477' => 'Citibank S.A.',
        '081' => 'Concórdia Banco S.A.',
        '487' => 'Deutsche Bank S.A. - Banco Alemão',
        '064' => 'Goldman Sachs do Brasil Banco Múltiplo S.A.',
        '062' => 'Hipercard Banco Múltiplo S.A.',
        '399' => 'HSBC Bank Brasil S.A.',
        '492' => 'ING Bank N.V.',
        '652' => 'Itaú Unibanco Holding S.A.',
        '341' => 'Itaú Unibanco S.A.',
        '488' => 'JPMorgan Chase Bank',
        '751' => 'Scotiabank Brasil S.A. Banco Múltiplo',
        '409' => 'UNIBANCO - União de Bancos Brasileiros S.A.',
        '230' => 'Unicard Banco Múltiplo S.A.',
        'XXX' => 'Desconhecido',
    ];

    protected $i = 0;
    protected $file;
    protected $banco;
    protected $isRetorno = false;
    protected $totais = [
        'valor' => 0,
        'liquidados' => 0,
        'baixados' => 0,
        'entradas' => 0,
        'alterados' => 0,
        'erros' => 0,
    ];
    private $_position = 0;

    /**
     * @var Header
     */
    protected $header;

    /**
     * @var Trailer
     */
    protected $trailer;

    /**
     * @var Detalhe[]
     */
    protected $detalhe = [];

    /**
     *
     * @param String $file
     * @throws \Exception
     */
    public function __construct($file) {
        $this->_position = 0;
        if(is_array($file) && strlen(rtrim($file[0], chr(10).chr(13)."\n"."\r")) == 400)
        {
            $this->file = $file;
        }
        else if(is_file($file) && file_exists($file))
        {
            $this->file = file($file);
        }
        else if(is_string($file)) {
            $this->file = preg_split('/\r\n|\r|\n/', $file);
            if(empty(last($this->file)))
            {
                array_pop($this->file);
            }
        }
        else
        {
            throw new \Exception("Arquivo: não existe");
        }

        $this->isRetorno = (substr($this->file[0], 0, 9) == '02RETORNO') ? true : false;
        if(!in_array(substr($this->file[0], 76, 3), array_keys($this->bancos)))
        {
            throw new \Exception(sprintf("Banco: %s, inválido", substr($this->file[0], 76, 3)));
        }

        $this->header = new Header();
        $this->trailer = new Trailer();
    }

    protected abstract function processarHeader(array $header);

    protected abstract function processarDetalhe(array $detalhe);

    protected abstract function processarTrailer(array $trailer);

    public function processar() {
        if(!$this->isRetorno)
        {
            throw new \Exception("Arquivo de retorno inválido");
        }

        foreach($this->file as $linha) {
            $aLinha = str_split(rtrim($linha, chr(10).chr(13)."\n"."\r"), 1);
            if( $aLinha[0] == '0' ) {
                $this->processarHeader($aLinha);
            } else if( $aLinha[0] == '9' ) {
                $this->processarTrailer($aLinha);
            } else {
                $this->processarDetalhe($aLinha);
            }
        }
        if(method_exists($this,'finalize')) {
            $this->finalize();
        }
        unset($this->cnab);
    }

    protected function rem($i, $f, $array)
    {
        $i--;

        if ($i > 398 || $f > 400) {
            throw new \Exception('$ini ou $fim ultrapassam o limite máximo de 400');
        }

        if ($f < $i) {
            throw new \Exception('$ini é maior que o $fim');
        }

        $t = $f - $i;

        return trim(implode('',array_splice($array, $i, $t)));
    }

    public function getBanco() {
        return $this->banco;
    }

    public function getBancoNome() {
        return $this->bancos[$this->banco];
    }

    public function getDetalhes() {
        return $this->detalhe;
    }

    public function getHeader() {
        return $this->header;
    }

    public function getTrailer() {
        return $this->trailer;
    }

    public function current() {
        return $this->detalhe[$this->_position];
    }

    public function next() {
        ++$this->_position;
    }

    public function prev() {
        --$this->_position;
    }

    public function key() {
        return $this->_position;
    }

    public function valid() {
        return isset($this->detalhe[$this->_position]);
    }

    public function rewind() {
        $this->_position = 0;
    }

    public function count() {
        return count($this->detalhe);
    }

    public function seek($position) {
        $this->_position = $position;
        if (!$this->valid()) {
            throw new \OutOfBoundsException('"Posição inválida "$position"');
        }
    }
}