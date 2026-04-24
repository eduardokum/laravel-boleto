<?php

namespace Eduardokum\LaravelBoleto\Cnab\Pagamento\Retorno\Cnab240;

use Eduardokum\LaravelBoleto\Exception\ValidationException;
use Eduardokum\LaravelBoleto\Cnab\Pagamento\Retorno\AbstractRetorno as AbstractRetornoGeneric;

abstract class AbstractRetorno extends AbstractRetornoGeneric
{
    /**
     * @var HeaderLote
     */
    private $headerLote;

    /**
     * @var TrailerLote
     */
    private $trailerLote;

    /**
     * Array de lotes processados
     * @var array
     */
    protected $lotes = [];

    /**
     * Lote atual sendo processado
     * @var int
     */
    protected $loteAtual = 0;

    /**
     * @param string|array $file
     * @throws ValidationException
     */
    public function __construct($file)
    {
        parent::__construct($file);

        $this->header = new Header();
        $this->headerLote = new HeaderLote();
        $this->trailerLote = new TrailerLote();
        $this->trailer = new Trailer();
    }

    /**
     * @return HeaderLote
     */
    public function getHeaderLote()
    {
        return $this->headerLote;
    }

    /**
     * @return TrailerLote
     */
    public function getTrailerLote()
    {
        return $this->trailerLote;
    }

    /**
     * @return array
     */
    public function getLotes()
    {
        return $this->lotes;
    }

    /**
     * @param array $header
     * @return bool
     */
    abstract protected function processarHeader(array $header);

    /**
     * @param array $headerLote
     * @return bool
     */
    abstract protected function processarHeaderLote(array $headerLote);

    /**
     * @param array $detalhe
     * @return bool
     */
    abstract protected function processarDetalhe(array $detalhe);

    /**
     * @param array $trailerLote
     * @return bool
     */
    abstract protected function processarTrailerLote(array $trailerLote);

    /**
     * @param array $trailer
     * @return bool
     */
    abstract protected function processarTrailer(array $trailer);

    /**
     * Incrementa o detalhe
     */
    protected function incrementDetalhe()
    {
        $this->increment++;
        $detalhe = new Detalhe();
        $this->detalhe[$this->increment] = $detalhe;
    }

    /**
     * Processa o arquivo
     *
     * @return $this
     * @throws ValidationException
     */
    public function processar()
    {
        if ($this->isProcessado())
            return $this;

        if (method_exists($this, 'init'))
            call_user_func([$this, 'init']);

        $detalhes = false;
        $trailer = false;

        foreach ($this->file as $linha) {
            $recordType = $this->rem(8, 8, $linha);

            if ($recordType == '0') {
                // Header do arquivo
                $this->processarHeader($linha);
            } elseif ($recordType == '1') {
                // Header do lote
                $this->loteAtual = (int) $this->rem(4, 7, $linha);
                $this->processarHeaderLote($linha);
            } elseif ($recordType == '3') {
                // Detalhe (Segmento A/B, J/J-52, N, O, Z, etc.)
                $segmentType = $this->getSegmentType($linha);

                if ($this->isStartOfDetalhe($segmentType, $linha))
                    $this->incrementDetalhe();

                $detalhes = true;
                if ($this->processarDetalhe($linha) === false) {
                    unset($this->detalhe[$this->increment]);
                    $this->increment--;
                }
            } elseif ($recordType == '5') {
                // Trailer do lote
                $this->processarTrailerLote($linha);

                // Armazena informações do lote
                if (!isset($this->lotes[$this->loteAtual])) {
                    $this->lotes[$this->loteAtual] = [
                        'header' => clone $this->headerLote,
                        'trailer' => clone $this->trailerLote,
                        'detalhes' => []
                    ];
                }
            } elseif ($recordType == '9') {
                // Trailer do arquivo
                $trailer = true;
                $this->processarTrailer($linha);
            }
        }

        if (!$detalhes)
            throw new ValidationException('Nenhum registro do tipo detalhe encontrado no arquivo de retorno');

        if (!$trailer)
            throw new ValidationException('Trailer do arquivo não encontrado');

        $this->processado = true;

        return $this;
    }

    /**
     * Determina se o segmento atual inicia um novo detalhe (incrementando o
     * índice) ou apenas complementa o anterior (B, J-52, W, Z, etc.).
     *
     * Por padrão, seguimos o layout FEBRABAN CNAB 240:
     *  - A (créditos em conta, TED, DOC, PIX)
     *  - J (liquidação de boletos) - exceto quando posições 18-19 = "52" (J-52)
     *  - N (tributos sem código de barras / FGTS)
     *  - O (concessionárias e tributos com código de barras)
     *
     * @param string $segmentType
     * @param string $linha
     * @return bool
     */
    protected function isStartOfDetalhe($segmentType, $linha)
    {
        if ($segmentType === 'A' || $segmentType === 'N' || $segmentType === 'O') {
            return true;
        }

        if ($segmentType === 'J') {
            // J-52 identifica o registro opcional complementar do segmento J.
            return $this->rem(18, 19, $linha) !== '52';
        }

        return false;
    }
}
