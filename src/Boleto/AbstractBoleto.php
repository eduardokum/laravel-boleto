<?php
namespace Eduardokum\LaravelBoleto\Boleto;

use Carbon\Carbon;
use Eduardokum\LaravelBoleto\Boleto\Render\Html;
use Eduardokum\LaravelBoleto\Boleto\Render\Pdf;
use Eduardokum\LaravelBoleto\Contracts\Pessoa as PessoaContract;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;
use Eduardokum\LaravelBoleto\Util;

/**
 * Class AbstractBoleto
 *
 * @package Eduardokum\LaravelBoleto\Boleto
 */
abstract class AbstractBoleto
{

    /**
     * Código do banco
     *
     * @var string
     */
    protected $codigoBanco;
    /**
     * Moeda
     *
     * @var int
     */
    protected $moeda = 9;
    /**
     * Valor total do boleto
     *
     * @var float
     */
    protected $valor;
    /**
     * Valor de descontos e abatimentos
     *
     * @var float
     */
    protected $descontosAbatimentos;
    /**
     * Valor para outras deduções
     *
     * @var float
     */
    protected $outrasDeducoes;
    /**
     * Valor para multa
     *
     * @var float
     */
    protected $multa = 0;
    /**
     * Valor para mora juros
     *
     * @var float
     */
    protected $juros = 0;
    /**
     * Dias apos vencimento do juros
     *
     * @var int
     */
    protected $jurosApos = 0;
    /**
     * Dias para protesto
     *
     * @var integer
     */
    protected $diasProtesto = 0;
    /**
     * Dias para baixa automática
     *
     * @var integer
     */
    protected $diasBaixaAutomatica;
    /**
     * Valor para outros acréscimos
     *
     * @var float
     */
    protected $outrosAcrescimos;
    /**
     * Valor cobrado
     *
     * @var
     */
    protected $valorCobrado;
    /**
     * Campo valor do boleto
     *
     * @var
     */
    protected $valorUnitario;
    /**
     * Campo quantidade
     *
     * @var
     */
    protected $quantidade;
    /**
     * Data do documento
     *
     * @var \Carbon\Carbon
     */
    protected $dataDocumento;
    /**
     * Data de emissão
     *
     * @var \Carbon\Carbon
     */
    protected $dataProcessamento;
    /**
     * Data de vencimento
     *
     * @var \Carbon\Carbon
     */
    protected $dataVencimento;
    /**
     * Campo de aceite
     *
     * @var string
     */
    protected $aceite = 'N';
    /**
     * Espécie do documento, geralmente DM (Duplicata Mercantil)
     *
     * @var string
     */
    protected $especieDoc = 'DM';
    /**
     * Espécie do documento, coódigo para remessa
     *
     * @var string
     */
    protected $especiesCodigo = [];
    /**
     * Número do documento
     *
     * @var int
     */
    protected $numeroDocumento;
    /**
     * Define o número definido pelo cliente para compor o Nosso Número
     *
     * @var int
     */
    protected $numero;
    /**
     * Campo de uso do banco no boleto
     *
     * @var string
     */
    protected $usoBanco;
    /**
     * Agência
     *
     * @var string
     */
    protected $agencia;
    /**
     * Dígito da agência
     *
     * @var string
     */
    protected $agenciaDv;
    /**
     * Conta
     *
     * @var string
     */
    protected $conta;
    /**
     * Dígito da conta
     *
     * @var string
     */
    protected $contaDv;
    /**
     * Modalidade de cobrança do cliente, geralmente Cobrança Simples ou Registrada
     *
     * @var string
     */
    protected $carteira;
    /**
     * Define as carteiras disponíveis para cada banco
     *
     * @var array
     */
    protected $carteiras = [];
    /**
     * Define as carteiras disponíveis para cada banco
     *
     * @var array
     */
    protected $carteirasNomes = [];
    /**
     * Entidade beneficiario (quem emite o boleto)
     *
     * @var PessoaContract
     */
    protected $beneficiario;
    /**
     * Entidade pagadora (de quem se cobra o boleto)
     *
     * @var PessoaContract
     */
    protected $pagador;
    /**
     * Entidade sacador avalista
     *
     * @var PessoaContract
     */
    protected $sacadorAvalista;
    /**
     * Array com as linhas do demonstrativo (descrição do pagamento)
     *
     * @var array
     */
    protected $descricaoDemonstrativo;
    /**
     * Linha de local de pagamento
     *
     * @var string
     */
    protected $localPagamento = 'Pagável em qualquer agência bancária até o vencimento.';
    /**
     * Array com as linhas de instruções
     *
     * @var array
     */
    protected $instrucoes = ['Pagar até a data do vencimento.'];
    /**
     * Localização do logotipo do banco, referente ao diretório de imagens
     *
     * @var string
     */
    protected $logo;
    /**
     * Variáveis adicionais.
     *
     * @var array
     */
    public $variaveis_adicionais = [];
    /**
     * Cache do campo livre para evitar processamento desnecessário.
     *
     * @var string
     */
    protected $campoLivre;
    /**
     * Cache do nosso numero para evitar processamento desnecessário.
     *
     * @var string
     */
    protected $campoNossoNumero;

    /**
     * Cache da linha digitabel para evitar processamento desnecessário.
     *
     * @var string
     */
    protected $campoLinhaDigitavel;

    /**
     * Cache do codigo de barras para evitar processamento desnecessário.
     *
     * @var string
     */
    protected $campoCodigoBarras;

    /**
     * Status do boleto, se vai criar alterar ou baixa no banco.
     *
     * @var int
     */
    protected $status = BoletoContract::STATUS_REGISTRO;

    /**
     * Construtor
     *
     * @param array $params Parâmetros iniciais para construção do objeto
     */
    public function __construct($params = [])
    {
        foreach ($params as $param => $value) {
            if (method_exists($this, 'set' . ucwords($param))) {
                $this->{'set' . ucwords($param)}($value);
            }
        }
        // Marca a data de emissão para hoje, caso não especificada
        if (! $this->getDataDocumento()) {
            $this->setDataDocumento(new Carbon());
        }
        // Marca a data de processamento para hoje, caso não especificada
        if (! $this->getDataProcessamento()) {
            $this->setDataProcessamento(new Carbon());
        }
        // Marca a data de vencimento para daqui a 5 dias, caso não especificada
        if (! $this->getDataVencimento()) {
            $this->setDataVencimento(new Carbon(date('Y-m-d', strtotime('+5 days'))));
        }
    }

    /**
     * Define a agência
     *
     * @param  string $agencia
     *
     * @return AbstractBoleto
     */
    public function setAgencia($agencia)
    {
        $this->agencia = (string) $agencia;

        return $this;
    }

    /**
     * Retorna a agência
     *
     * @return string
     */
    public function getAgencia()
    {
        return $this->agencia;
    }

    /**
     * Define o dígito da agência
     *
     * @param  string $agenciaDv
     *
     * @return AbstractBoleto
     */
    public function setAgenciaDv($agenciaDv)
    {
        $this->agenciaDv = $agenciaDv;

        return $this;
    }

    /**
     * Retorna o dígito da agência
     *
     * @return string
     */
    public function getAgenciaDv()
    {
        return $this->agenciaDv;
    }

    /**
     * Define o código da carteira (Com ou sem registro)
     *
     * @param  string $carteira
     *
     * @return AbstractBoleto
     * @throws \Exception
     */
    public function setCarteira($carteira)
    {
        if (! in_array($carteira, $this->getCarteiras())) {
            throw new \Exception("Carteira não disponível!");
        }
        $this->carteira = $carteira;

        return $this;
    }

    /**
     * Retorna o código da carteira (Com ou sem registro)
     *
     * @return string
     */
    public function getCarteira()
    {
        return $this->carteira;
    }

    /**
     * Retorna as carteiras disponíveis para este banco
     *
     * @return array
     */
    public function getCarteiras()
    {
        return $this->carteiras;
    }

    /**
     * Define a entidade beneficiario
     *
     * @param  PessoaContract $beneficiario
     *
     * @return AbstractBoleto
     */
    public function setBeneficiario(PessoaContract $beneficiario)
    {
        $this->beneficiario = $beneficiario;

        return $this;
    }

    /**
     * Retorna a entidade beneficiario
     *
     * @return PessoaContract
     */
    public function getBeneficiario()
    {
        return $this->beneficiario;
    }

    /**
     * Retorna o código do banco
     *
     * @return string
     */
    public function getCodigoBanco()
    {
        return $this->codigoBanco;
    }

    /**
     * Define o número da conta
     *
     * @param  string $conta
     *
     * @return AbstractBoleto
     */
    public function setConta($conta)
    {
        $this->conta = (string) $conta;

        return $this;
    }

    /**
     * Retorna o número da conta
     *
     * @return string
     */
    public function getConta()
    {
        return $this->conta;
    }

    /**
     * Define o dígito verificador da conta
     *
     * @param  string $contaDv
     *
     * @return AbstractBoleto
     */
    public function setContaDv($contaDv)
    {
        $this->contaDv = $contaDv;

        return $this;
    }

    /**
     * Retorna o dígito verificador da conta
     *
     * @return string
     */
    public function getContaDv()
    {
        return $this->contaDv;
    }

    /**
     * Define a data de vencimento
     *
     * @param  \Carbon\Carbon $dataVencimento
     *
     * @return AbstractBoleto
     */
    public function setDataVencimento(Carbon $dataVencimento)
    {
        $this->dataVencimento = $dataVencimento;

        return $this;
    }

    /**
     * Retorna a data de vencimento
     *
     * @return \Carbon\Carbon
     */
    public function getDataVencimento()
    {
        return $this->dataVencimento;
    }

    /**
     * Define a data do documento
     *
     * @param  \Carbon\Carbon $dataDocumento
     *
     * @return AbstractBoleto
     */
    public function setDataDocumento(Carbon $dataDocumento)
    {
        $this->dataDocumento = $dataDocumento;

        return $this;
    }

    /**
     * Retorna a data do documento
     *
     * @return \Carbon\Carbon
     */
    public function getDataDocumento()
    {
        return $this->dataDocumento;
    }

    /**
     * Define o campo aceite
     *
     * @param  string $aceite
     *
     * @return AbstractBoleto
     */
    public function setAceite($aceite)
    {
        $this->aceite = $aceite;

        return $this;
    }

    /**
     * Retorna o campo aceite
     *
     * @return string
     */
    public function getAceite()
    {
        return is_numeric($this->aceite) ? ($this->aceite ? 'A' : 'N') : $this->aceite;
    }

    /**
     * Define o campo Espécie Doc, geralmente DM (Duplicata Mercantil)
     *
     * @param  string $especieDoc
     *
     * @return AbstractBoleto
     */
    public function setEspecieDoc($especieDoc)
    {
        $this->especieDoc = $especieDoc;

        return $this;
    }

    /**
     * Retorna o campo Espécie Doc, geralmente DM (Duplicata Mercantil)
     *
     * @return string
     */
    public function getEspecieDoc()
    {
        return $this->especieDoc;
    }

    /**
     * Retorna o codigo da Espécie Doc
     *
     * @return string
     */
    public function getEspecieDocCodigo($default = 99)
    {
        return key_exists(strtoupper($this->especieDoc), $this->especiesCodigo)
            ? $this->especiesCodigo[strtoupper($this->getEspecieDoc())]
            : $default;
    }

    /**
     * Define o campo Número do documento
     *
     * @param  int $numeroDocumento
     *
     * @return AbstractBoleto
     */
    public function setNumeroDocumento($numeroDocumento)
    {
        $this->numeroDocumento = $numeroDocumento;

        return $this;
    }

    /**
     * Retorna o campo Número do documento
     *
     * @return int
     */
    public function getNumeroDocumento()
    {
        return $this->numeroDocumento;
    }

    /**
     * Define o número  definido pelo cliente para compor o nosso número
     *
     * @param  int $numero
     *
     * @return AbstractBoleto
     */
    public function setNumero($numero)
    {
        $this->numero = $numero;

        return $this;
    }

    /**
     * Retorna o número definido pelo cliente para compor o nosso número
     *
     * @return int
     */
    public function getNumero()
    {
        return $this->numero;
    }

    /**
     * Define o campo Uso do banco
     *
     * @param  string $usoBanco
     *
     * @return AbstractBoleto
     */
    public function setUsoBanco($usoBanco)
    {
        $this->usoBanco = $usoBanco;

        return $this;
    }

    /**
     * Retorna o campo Uso do banco
     *
     * @return string
     */
    public function getUsoBanco()
    {
        return $this->usoBanco;
    }

    /**
     * Define a data de geração do boleto
     *
     * @param  \Carbon\Carbon $dataProcessamento
     *
     * @return AbstractBoleto
     */
    public function setDataProcessamento(Carbon $dataProcessamento)
    {
        $this->dataProcessamento = $dataProcessamento;

        return $this;
    }

    /**
     * Retorna a data de geração do boleto
     *
     * @return \Carbon\Carbon
     */
    public function getDataProcessamento()
    {
        return $this->dataProcessamento;
    }

    /**
     * Adiciona uma instrução (máximo 5)
     *
     * @param string $instrucao
     *
     * @return AbstractBoleto
     * @throws \Exception
     */
    public function addInstrucao($instrucao)
    {
        if (count($this->getInstrucoes()) > 8) {
            throw new \Exception('Atingido o máximo de 5 instruções.');
        }
        array_push($this->instrucoes, $instrucao);

        return $this;
    }

    /**
     * Define um array com instruções (máximo 8) para pagamento
     *
     * @param array $instrucoes
     *
     * @return AbstractBoleto
     * @throws \Exception
     */
    public function setInstrucoes(array $instrucoes)
    {
        if (count($instrucoes) > 8) {
            throw new \Exception('Máximo de 8 instruções.');
        }
        $this->instrucoes = $instrucoes;

        return $this;
    }

    /**
     * Retorna um array com instruções (máximo 8) para pagamento
     *
     * @return array
     */
    public function getInstrucoes()
    {
        return array_slice((array) $this->instrucoes + [null, null, null, null, null, null, null, null], 0, 8);
    }

    /**
     * Adiciona um demonstrativo (máximo 5)
     *
     * @param string $descricaoDemonstrativo
     *
     * @return AbstractBoleto
     * @throws \Exception
     */
    public function addDescricaoDemonstrativo($descricaoDemonstrativo)
    {
        if (count($this->getDescricaoDemonstrativo()) > 5) {
            throw new \Exception('Atingido o máximo de 5 demonstrativos.');
        }
        array_push($this->descricaoDemonstrativo, $descricaoDemonstrativo);

        return $this;
    }

    /**
     * Define um array com a descrição do demonstrativo (máximo 5)
     *
     * @param array $descricaoDemonstrativo
     *
     * @return AbstractBoleto
     * @throws \Exception
     */
    public function setDescricaoDemonstrativo(array $descricaoDemonstrativo)
    {
        if (count($descricaoDemonstrativo) > 5) {
            throw new \Exception('Máximo de 5 demonstrativos.');
        }
        $this->descricaoDemonstrativo = $descricaoDemonstrativo;

        return $this;
    }

    /**
     * Retorna um array com a descrição do demonstrativo (máximo 5)
     *
     * @return array
     */
    public function getDescricaoDemonstrativo()
    {
        return array_slice((array) $this->descricaoDemonstrativo + [null, null, null, null, null], 0, 5);
    }

    /**
     * Define o local de pagamento do boleto
     *
     * @param  string $localPagamento
     *
     * @return AbstractBoleto
     */
    public function setLocalPagamento($localPagamento)
    {
        $this->localPagamento = $localPagamento;

        return $this;
    }

    /**
     * Retorna o local de pagamento do boleto
     *
     * @return string
     */
    public function getLocalPagamento()
    {
        return $this->localPagamento;
    }

    /**
     * Define a moeda utilizada pelo boleto
     *
     * @param  int $moeda
     *
     * @return AbstractBoleto
     */
    public function setMoeda($moeda)
    {
        $this->moeda = $moeda;

        return $this;
    }

    /**
     * Retorna a moeda utilizada pelo boleto
     *
     * @return int
     */
    public function getMoeda()
    {
        return $this->moeda;
    }

    /**
     * Define o objeto do pagador
     *
     * @param  PessoaContract $pagador
     *
     * @return AbstractBoleto
     */
    public function setPagador(PessoaContract $pagador)
    {
        $this->pagador = $pagador;

        return $this;
    }

    /**
     * Retorna o objeto do pagador
     *
     * @return PessoaContract
     */
    public function getPagador()
    {
        return $this->pagador;
    }

    /**
     * Define o objeto sacador avalista do boleto
     *
     * @param  PessoaContract $sacadorAvalista
     *
     * @return AbstractBoleto
     */
    public function setSacadorAvalista(PessoaContract $sacadorAvalista)
    {
        $this->sacadorAvalista = $sacadorAvalista;

        return $this;
    }

    /**
     * Retorna o objeto sacador avalista do boleto
     *
     * @return PessoaContract
     */
    public function getSacadorAvalista()
    {
        return $this->sacadorAvalista;
    }

    /**
     * Define o valor total do boleto (incluindo taxas)
     *
     * @param  string $valor
     *
     * @return AbstractBoleto
     */
    public function setValor($valor)
    {
        $this->valor = Util::nFloat($valor, 2, false);

        return $this;
    }

    /**
     * Retorna o valor total do boleto (incluindo taxas)
     *
     * @return string
     */
    public function getValor()
    {
        return Util::nFloat($this->valor, 2, false);
    }

    /**
     * Define o campo Descontos / Abatimentos
     *
     * @param  float $descontosAbatimentos
     *
     * @return AbstractBoleto
     */
    public function setDescontosAbatimentos($descontosAbatimentos)
    {
        $this->descontosAbatimentos = $descontosAbatimentos;

        return $this;
    }

    /**
     * Retorna o campo Descontos / Abatimentos
     *
     * @return string
     */
    public function getDescontosAbatimentos()
    {
        return Util::nFloat($this->descontosAbatimentos, 2, false);
    }

    /**
     * Seta a % de multa
     *
     * @param  float $multa
     *
     * @return AbstractBoleto
     */
    public function setMulta($multa)
    {
        $this->multa = (float) ($multa > 0.00 ? $multa : 0.00);

        return $this;
    }

    /**
     * Retorna % de multa
     *
     * @return float
     */
    public function getMulta()
    {
        return $this->multa;
    }

    /**
     * Seta a % de juros
     *
     * @param  float $juros
     *
     * @return AbstractBoleto
     */
    public function setJuros($juros)
    {
        $this->juros = (float) ($juros > 0.00 ? $juros : 0.00);

        return $this;
    }

    /**
     * Retorna % juros
     *
     * @return float
     */
    public function getJuros()
    {
        return $this->juros;
    }

    /**
     * Seta a quantidade de dias apos o vencimento que cobra o juros
     *
     * @param  int $jurosApos
     *
     * @return AbstractBoleto
     */
    public function setJurosApos($jurosApos)
    {
        $jurosApos       = (int) $jurosApos;
        $this->jurosApos = $jurosApos > 0 ? $jurosApos : 0;

        return $this;
    }

    /**
     * Retorna a quantidade de dias apos o vencimento que cobrar a juros
     *
     * @return int
     */
    public function getJurosApos()
    {
        return $this->jurosApos;
    }

    /**
     * Seta dias para protesto
     *
     * @param int $diasProtesto
     *
     * @return AbstractBoleto
     * @throws \Exception
     */
    public function setDiasProtesto($diasProtesto)
    {
        if ($this->getDiasBaixaAutomatica() > 0) {
            throw new \Exception('Você deve usar dias de protesto ou dias de baixa, nunca os 2');
        }
        $diasProtesto       = (int) $diasProtesto;
        $this->diasProtesto = $diasProtesto > 0 ? $diasProtesto : 0;

        return $this;
    }

    /**
     * Retorna os diasProtesto
     *
     * @param int $default
     *
     * @return int
     */
    public function getDiasProtesto($default = 0)
    {
        return $this->diasProtesto > 0 ? $this->diasProtesto : $default;
    }

    /**
     * Seta dias para baixa automática
     *
     * @param int $baixaAutomatica
     *
     * @return AbstractBoleto
     * @throws \Exception
     */
    public function setDiasBaixaAutomatica($baixaAutomatica)
    {
        throw new \Exception('O Banco ' . basename(get_class($this)) . ' não suporta baixa automática');
    }

    /**
     * Retorna os diasProtesto
     *
     * @param int $default
     *
     * @return int
     */
    public function getDiasBaixaAutomatica($default = 0)
    {
        return $this->diasBaixaAutomatica > 0 ? $this->diasBaixaAutomatica : $default;
    }

    /**
     * Define o campo outras deduções do boleto
     *
     * @param  float $outrasDeducoes
     *
     * @return AbstractBoleto
     */
    public function setOutrasDeducoes($outrasDeducoes)
    {
        $this->outrasDeducoes = $outrasDeducoes;

        return $this;
    }

    /**
     * Retorna o campo outras deduções do boleto
     *
     * @return float
     */
    public function getOutrasDeducoes()
    {
        return $this->outrasDeducoes;
    }

    /**
     * Define o campo outros acréscimos do boleto
     *
     * @param  float $outrosAcrescimos
     *
     * @return AbstractBoleto
     */
    public function setOutrosAcrescimos($outrosAcrescimos)
    {
        $this->outrosAcrescimos = $outrosAcrescimos;

        return $this;
    }

    /**
     * Retorna o campo outros acréscimos do boleto
     *
     * @return float
     */
    public function getOutrosAcrescimos()
    {
        return $this->outrosAcrescimos;
    }

    /**
     * Define o campo quantidade do boleto
     *
     * @param  $quantidade
     *
     * @return AbstractBoleto
     */
    public function setQuantidade($quantidade)
    {
        $this->quantidade = $quantidade;

        return $this;
    }

    /**
     * Retorna o campo quantidade do boleto
     *
     * @return int
     */
    public function getQuantidade()
    {
        return $this->quantidade;
    }

    /**
     * Define o campo valor cobrado do boleto
     *
     * @param  $valorCobrado
     *
     * @return AbstractBoleto
     */
    public function setValorCobrado($valorCobrado)
    {
        $this->valorCobrado = Util::nFloat($valorCobrado, 2, false);

        return $this;
    }

    /**
     * Retorna o campo valor cobrado do boleto
     *
     * @return string
     */
    public function getValorCobrado()
    {
        return Util::nFloat($this->valorCobrado, 2, false);
    }

    /**
     * Define o campo "valor" do boleto
     *
     * @param  $valorUnitario
     *
     * @return AbstractBoleto
     */
    public function setValorUnitario($valorUnitario)
    {
        $this->valorUnitario = Util::nFloat($valorUnitario, 2, false);

        return $this;
    }

    /**
     * Retorna o campo "valor" do boleto
     *
     * @return string
     */
    public function getValorUnitario()
    {
        return Util::nFloat($this->valorUnitario, 2, false);
    }

    /**
     * Define a localização do logotipo
     *
     * @param  string $logo
     *
     * @return AbstractBoleto
     */
    public function setLogo($logo)
    {
        $this->logo = $logo;

        return $this;
    }

    /**
     * Retorna a localização do logotipo
     *
     * @return string
     */
    public function getLogo()
    {
        return $this->logo ? $this->logo : "http://dummyimage.com/300x70/f5/0.png&text=Sem+Logo";
    }

    /**
     * Retorna o logotipo em Base64, pronto para ser inserido na página
     *
     * @return string
     */
    public function getLogoBase64()
    {
        return 'data:image/' . pathinfo($this->getLogo(), PATHINFO_EXTENSION) .
            ';base64,' . base64_encode(file_get_contents($this->getLogo()));
    }

    /**
     * Retorna a localização do logotipo do banco relativo à pasta de imagens
     *
     * @return string
     */
    public function getLogoBanco()
    {
        return realpath(__DIR__ . '/../../logos/' . $this->getCodigoBanco() . '.png');
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Marca o boleto para ser alterado no banco
     *
     * @return AbstractBoleto
     */
    public function alterarBoleto()
    {
        $this->status = BoletoContract::STATUS_ALTERACAO;

        return $this;
    }

    /**
     * Marca o boleto para ser baixado no banco
     *
     * @return AbstractBoleto
     */
    public function baixarBoleto()
    {
        $this->status = BoletoContract::STATUS_BAIXA;

        return $this;
    }

    /**
     * Retorna o logotipo do banco em Base64, pronto para ser inserido na página
     *
     * @return string
     */
    public function getLogoBancoBase64()
    {
        return 'data:image/' . pathinfo($this->getLogoBanco(),
                PATHINFO_EXTENSION) . ';base64,' . base64_encode(file_get_contents($this->getLogoBanco()));
    }

    /**
     * Mostra exception ao erroneamente tentar setar o nosso número
     *
     * @throws \Exception
     */
    public final function setNossoNumero()
    {
        throw new \Exception('Não é possível definir o nosso número diretamente. Utilize o método setNumero.');
    }

    /**
     * Retorna o Nosso Número calculado.
     *
     * @param  bool $incluirFormatacao Incluir formatação ou não (pontuação, espaços e barras)
     *
     * @return string
     */
    public function getNossoNumero($incluirFormatacao = true)
    {
        if (empty($this->campoNossoNumero)) {
            $this->campoNossoNumero = $this->gerarNossoNumero();
        }
        $numero = $this->campoNossoNumero;
        // Remove a formatação, caso especificado
        if (! $incluirFormatacao) {
            return Util::onlyNumbers($numero);
        }

        return $numero;
    }

    /**
     * Método que retorna o nosso numero usado no boleto. alguns bancos possuem algumas diferenças.
     *
     * @return string
     */
    public function getNossoNumeroBoleto()
    {
        return $this->getNossoNumero();
    }

    /**
     * Método onde o Boleto deverá gerar o Nosso Número.
     *
     * @return string
     */
    abstract protected function gerarNossoNumero();

    /**
     * Método onde qualquer boleto deve extender para gerar o código da posição de 20 a 44
     *
     * @return string
     */
    abstract protected function getCampoLivre();

    /**
     * Método que valida se o banco tem todos os campos obrigadotorios preenchidos
     *
     * @return boolean
     */
    public function isValid()
    {
        if ($this->numeroDocumento == ''
            || $this->agencia == ''
            || $this->conta == ''
            || $this->carteira == ''
        ) {
            return false;
        }

        return true;
    }

    /**
     * Retorna o campo Agência/Beneficiário do boleto
     *
     * @return string
     */
    public function getAgenciaCodigoBeneficiario()
    {
        $agencia = $this->getAgenciaDv() !== null ? $this->getAgencia() . '-' . $this->getAgenciaDv() : $this->getAgencia();
        $conta   = $this->getContaDv() !== null ? $this->getConta() . '-' . $this->getContaDv() : $this->getConta();

        return $agencia . ' / ' . $conta;
    }

    /**
     * Retorna o nome da carteira para impressão no boleto
     *
     * Caso o nome da carteira a ser impresso no boleto seja diferente do número
     * Então crie uma variável na classe do banco correspondente $carteirasNomes
     * sendo uma array cujos índices sejam os números das carteiras e os valores
     * seus respectivos nomes
     *
     * @return string
     */
    public function getCarteiraNome()
    {
        return isset($this->carteirasNomes[$this->getCarteira()]) ? $this->carteirasNomes[$this->getCarteira()] : $this->getCarteira();
    }

    /**
     * Retorna o codigo de barras
     *
     * @return string
     * @throws \Exception
     */
    public function getCodigoBarras()
    {
        if (! empty($this->campoCodigoBarras)) {
            return $this->campoCodigoBarras;
        }

        if (! $this->isValid()) {
            throw new \Exception('Campos requeridos pelo banco, aparentam estar ausentes');
        }

        $codigo = Util::numberFormatGeral($this->getCodigoBanco(), 3)
            . $this->getMoeda()
            . Util::fatorVencimento($this->getDataVencimento())
            . Util::numberFormatGeral($this->getValor(), 10)
            . $this->getCampoLivre();

        $resto = Util::modulo11($codigo, 2, 9, 0);
        $dv    = (in_array($resto, [0, 10, 11])) ? 1 : $resto;

        return $this->campoCodigoBarras = substr($codigo, 0, 4) . $dv . substr($codigo, 4);
    }

    /**
     * Retorna o código do banco com o dígito verificador
     *
     * @return string
     */
    public function getCodigoBancoComDv()
    {
        $codigoBanco = $this->getCodigoBanco();

        return $codigoBanco . '-' . Util::modulo11($codigoBanco);
    }

    /**
     * Retorna a linha digitável do boleto
     *
     * @return string
     * @throws \Exception
     */
    public function getLinhaDigitavel()
    {
        if (! empty($this->campoLinhaDigitavel)) {
            return $this->campoLinhaDigitavel;
        }

        $codigo = $this->getCodigoBarras();

        $s1 = substr($codigo, 0, 4) . substr($codigo, 19, 5);
        $s1 = $s1 . Util::modulo10($s1);
        $s1 = substr_replace($s1, '.', 5, 0);

        $s2 = substr($codigo, 24, 10);
        $s2 = $s2 . Util::modulo10($s2);
        $s2 = substr_replace($s2, '.', 5, 0);

        $s3 = substr($codigo, 34, 10);
        $s3 = $s3 . Util::modulo10($s3);
        $s3 = substr_replace($s3, '.', 5, 0);

        $s4 = substr($codigo, 4, 1);

        $s5 = substr($codigo, 5, 14);

        return $this->campoLinhaDigitavel = sprintf('%s %s %s %s %s', $s1, $s2, $s3, $s4, $s5);
    }

    /**
     * Render PDF
     *
     * @param bool $print
     *
     * @return string
     * @throws \Exception
     */
    public function renderPDF($print = false)
    {
        $pdf = new Pdf();
        $pdf->addBoleto($this);

        return $pdf->gerarBoleto('S', null, $print);
    }

    /**
     * Render HTML
     *
     * @param bool $print
     *
     * @return string
     * @throws \Exception
     */
    public function renderHTML($print = false)
    {
        $html = new Html($this->toArray());

        return $html->gerarBoleto($print);
    }

    /**
     * Return Boleto Array.
     *
     * @return array
     * @throws \Exception
     */
    public function toArray()
    {
        return array_merge(
            [
                'linha_digitavel'             => $this->getLinhaDigitavel(),
                'codigo_barras'               => $this->getCodigoBarras(),
                'beneficiario'                => $this->getBeneficiario()->getNome(),
                'beneficiario_nome_documento' => $this->getBeneficiario()->getNomeDocumento(),
                'beneficiario_cpf_cnpj'       => $this->getBeneficiario()->getDocumento(),
                'beneficiario_endereco1'      => $this->getBeneficiario()->getEndereco(),
                'beneficiario_endereco2'      => $this->getBeneficiario()->getCepCidadeUf(),
                'logo_base64'                 => $this->getLogoBase64(),
                'logo'                        => $this->getLogo(),
                'logo_banco_base64'           => $this->getLogoBancoBase64(),
                'logo_banco'                  => $this->getLogoBanco(),
                'codigo_banco_com_dv'         => $this->getCodigoBancoComDv(),
                'especie'                     => 'R$',
                'quantidade'                  => $this->getQuantidade(),
                'data_vencimento'             => $this->getDataVencimento()->format('d/m/Y'),
                'data_processamento'          => $this->getDataProcessamento()->format('d/m/Y'),
                'data_documento'              => $this->getDataDocumento()->format('d/m/Y'),
                'valor_documento'             => Util::nReal($this->getValor(), 2, false),
                'desconto_abatimento'         => Util::nReal($this->getDescontosAbatimentos(), 2, false),
                'outras_deducoes'             => Util::nReal($this->getOutrasDeducoes(), 2, false),
                'multa'                       => Util::nReal($this->getMulta(), 2, false),
                'juros'                       => Util::nReal($this->getJuros(), 2, false),
                'juros_apos'                  => $this->getJurosApos(),
                'dias_protesto'               => $this->getDiasProtesto(),
                'outros_acrescimos'           => Util::nReal($this->getOutrosAcrescimos(), 2, false),
                'valor_cobrado'               => Util::nReal($this->getValorCobrado(), 2, false),
                'valor_unitario'              => Util::nReal($this->getValorUnitario(), 2, false),
                'sacador_avalista'            => $this->getSacadorAvalista() ? $this->getSacadorAvalista()->getNomeDocumento() : null,
                'pagador'                     => $this->getPagador()->getNome(),
                'pagador_nome_documento'      => $this->getPagador()->getNomeDocumento(),
                'pagador_documento'           => $this->getPagador()->getDocumento(),
                'pagador_endereco1'           => trim($this->getPagador()->getEndereco() . ' - ' . $this->getPagador()->getBairro(), ' -'),
                'pagador_endereco2'           => $this->getPagador()->getCepCidadeUf(),
                'demonstrativo'               => $this->getDescricaoDemonstrativo(),
                'instrucoes'                  => $this->getInstrucoes(),
                'local_pagamento'             => $this->getLocalPagamento(),
                'numero_documento'            => $this->getNumeroDocumento(),
                'agencia_codigo_beneficiario' => $this->getAgenciaCodigoBeneficiario(),
                'nosso_numero'                => $this->getNossoNumero(),
                'nosso_numero_boleto'         => $this->getNossoNumeroBoleto(),
                'especie_doc'                 => $this->getEspecieDoc(),
                'especie_doc_cod'             => $this->getEspecieDocCodigo(),
                'aceite'                      => $this->getAceite(),
                'carteira'                    => $this->getCarteiraNome(),
                'uso_banco'                   => $this->getUsoBanco(),
                'status'                      => $this->getStatus(),
            ], $this->variaveis_adicionais
        );
    }

}
