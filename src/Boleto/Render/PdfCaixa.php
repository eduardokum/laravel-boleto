<?php

namespace Eduardokum\LaravelBoleto\Boleto\Render;

use Illuminate\Support\Str;
use Eduardokum\LaravelBoleto\Util;
use Eduardokum\LaravelBoleto\Exception\ValidationException;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Render\Pdf as PdfContract;

class PdfCaixa extends AbstractPdf implements PdfContract
{
    const OUTPUT_STANDARD = 'I';
    const OUTPUT_DOWNLOAD = 'D';
    const OUTPUT_SAVE = 'F';
    const OUTPUT_STRING = 'S';
    const PIX_INSTRUCAO = 'instrucao';
    const PIX_COD_BARRAS = 'barras';

    private $PadraoFont = 'Arial';

    /**
     * @var BoletoContract[]
     */
    private $boleto = [];

    /**
     * @var bool
     */
    private $print = false;

    /**
     * @var bool
     */
    private $showInstrucoes = true;

    private $desc = 3; // tamanho célula descrição

    private $cell = 4; // tamanho célula dado

    private $fdes = 6; // tamanho fonte descrição

    private $fcel = 8; // tamanho fonte célula

    private $small = 0.2; // tamanho barra fina

    private $totalBoletos = 0;

    protected $localizacao_pix = self::PIX_INSTRUCAO;

    public function __construct()
    {
        parent::__construct('P', 'mm', 'A4');
        $this->SetAutoPageBreak(false);
        $this->SetLeftMargin(20);
        $this->SetTopMargin(15);
        $this->SetRightMargin(20);
        $this->SetLineWidth($this->small);
    }

    /**
     * @param $localizacao
     * @return PdfCaixa
     * @throws ValidationException
     */
    public function setLocalizacaoPix($localizacao)
    {
        if (! in_array($localizacao, [self::PIX_COD_BARRAS, self::PIX_INSTRUCAO])) {
            throw new ValidationException('Pix deve ser Pdf::PIX_COD_BARRAS ou Pdf::PIX_INSTRUCAO');
        }

        $this->localizacao_pix = $localizacao;

        return $this;
    }

    /**
     * @param int $i
     *
     * @return PdfCaixa
     */
    protected function instrucoes($i)
    {
        $this->SetFont($this->PadraoFont, '', 8);
        if ($this->totalBoletos > 1) {
            $this->SetAutoPageBreak(true);
            $this->SetY(5);
            $this->Cell(30, 10, date('d/m/Y H:i:s'));
            $this->Cell(0, 10, 'Boleto ' . ($i + 1) . ' de ' . $this->totalBoletos, 0, 1, 'R');
        }

        $this->SetFont($this->PadraoFont, 'B', 8);
        if ($this->showInstrucoes) {
            $this->Cell(0, 5, $this->_('Instruções de Impressão'), 0, 1, 'C');
            $this->Ln(5);
            $this->SetFont($this->PadraoFont, '', 6);
            if (count($this->boleto[$i]->getInstrucoesImpressao()) > 0) {
                $this->listaLinhas($this->boleto[$i]->getInstrucoesImpressao(), 0);
            } else {
                $this->Cell(0, $this->desc, $this->_('- Imprima em impressora jato de tinta (ink jet) ou laser em qualidade normal ou alta (Não use modo econômico).'), 0, 1, 'L');
                $this->Cell(0, $this->desc, $this->_('- Utilize folha A4 (210 x 297 mm) ou Carta (216 x 279 mm) e margens mínimas à esquerda e à direita do formulário.'), 0, 1, 'L');
                $this->Cell(0, $this->desc, $this->_('- Corte na linha indicada. Não rasure, risque, fure ou dobre a região onde se encontra o código de barras.'), 0, 1, 'L');
                $this->Cell(0, $this->desc, $this->_('- Caso não apareça o código de barras no final, clique em F5 para atualizar esta tela.'), 0, 1, 'L');
                $this->Cell(0, $this->desc, $this->_('- Caso tenha problemas ao imprimir, copie a seqüencia numérica abaixo e pague no caixa eletrônico ou no internet banking:'), 0, 1, 'L');
            }
            $this->Ln(4);

            $this->SetFont($this->PadraoFont, '', $this->fcel);
            $this->Cell(25, $this->cell, $this->_('Linha Digitável: '), 0, 0);
            $this->SetFont($this->PadraoFont, 'B', $this->fcel);
            $this->Cell(0, $this->cell, $this->_($this->boleto[$i]->getLinhaDigitavel()), 0, 1);
            $this->SetFont($this->PadraoFont, '', $this->fcel);
            $this->Cell(25, $this->cell, $this->_('Número: '), 0, 0);
            $this->SetFont($this->PadraoFont, 'B', $this->fcel);
            $this->Cell(0, $this->cell, $this->_($this->boleto[$i]->getNumero()), 0, 1);
            $this->SetFont($this->PadraoFont, '', $this->fcel);
            $this->Cell(25, $this->cell, $this->_('Valor: '), 0, 0);
            $this->SetFont($this->PadraoFont, 'B', $this->fcel);
            $this->Cell(0, $this->cell, $this->_(Util::nReal($this->boleto[$i]->getValor())), 0, 1);
            $this->SetFont($this->PadraoFont, '', $this->fcel);
        }

        $this->traco('Recibo do Pagador', 4);

        return $this;
    }

    /**
     * @param int $i
     *
     * @return PdfCaixa
     */
    protected function logoEmpresa($i)
    {
        $this->Ln(2);
        $this->SetFont($this->PadraoFont, '', $this->fdes);

        $logo = preg_replace('/\&.*/', '', $this->boleto[$i]->getLogo());
        $ext = pathinfo($logo, PATHINFO_EXTENSION);

        if ($this->boleto[$i]->getLogo() && ! empty($this->boleto[$i]->getLogo())) {
            $this->Image($this->boleto[$i]->getLogo(), 20, ($this->GetY()), 0, 12, $ext);
        }
        $this->Cell(56);
        $this->Cell(0, $this->desc, $this->_($this->boleto[$i]->getBeneficiario()->getNome()), 0, 1);
        $this->Cell(56);
        $this->Cell(0, $this->desc, $this->_($this->boleto[$i]->getBeneficiario()->getDocumento(), '##.###.###/####-##'), 0, 1);
        $this->Cell(56);
        $this->Cell(0, $this->desc, $this->_($this->boleto[$i]->getBeneficiario()->getEndereco()), 0, 1);
        $this->Cell(56);
        $this->Cell(0, $this->desc, $this->_($this->boleto[$i]->getBeneficiario()->getCepCidadeUf()), 0, 1);
        $this->Ln(8);

        return $this;
    }

    /**
     * @param int $i
     *
     * @return PdfCaixa
     */
    protected function Topo($i)
    {
        //primeira linha
        $this->Ln(8);
        $this->Image($this->boleto[$i]->getLogoBanco(), 20, ($this->GetY()), 20);
        $this->Cell(29, 6, '', 'TLR');

        $this->SetFont('', 'B', 13);
        $this->Cell(15, 6, $this->boleto[$i]->getCodigoBancoComDv(), 'TLR', 0, 'C');

        $this->SetFont('', 'B', 10);
        $this->Cell(104, 6, $this->boleto[$i]->getLinhaDigitavel(), 'TLR', 0, 'D');

        $logo = preg_replace('/\&.*/', '', $this->boleto[$i]->getLogo());
        $ext = pathinfo($logo, PATHINFO_EXTENSION);

        if ($this->boleto[$i]->getLogo() && ! empty($this->boleto[$i]->getLogo())) {
            $this->Image($this->boleto[$i]->getLogo(), 170, ($this->GetY() + 1), 0, 5, $ext);
            $this->Cell(0, 6, '', 'TLR', 1);
        }

        //segunda linha
        $this->SetFont($this->PadraoFont, '', $this->fdes);
        $this->Cell(101, $this->desc, $this->_('Beneficiário'), 'TLR');
        $this->Cell(34, $this->desc, $this->_('CPF/CNPJ'), 'TR');
        $this->Cell(35, $this->desc, $this->_('Agência/Código do beneficiário'), 'TR', 1);

        $this->SetFont($this->PadraoFont, 'B', $this->fcel);
        $this->textFitCell(101, $this->cell, $this->_($this->boleto[$i]->getBeneficiario()->getNome()), 'LR', 0, 'L');
        $this->Cell(34, $this->cell, $this->_($this->boleto[$i]->getBeneficiario()->getDocumento(), '##.###.###/####-##'), 'R');

        //algoritmo que calcula digito verificador beneficiario
        $codBenefCaixa = strval($this->boleto[$i]->getConta());
        if (! empty($codBenefCaixa)) {
            $j = 2;
            $aux = -1;
            $soma = 0;
            $comprimento = strlen($codBenefCaixa);
            for ($i = $comprimento; $i > 0; $i--) {
                $calculo = substr($codBenefCaixa, $aux, 1);
                $soma = $soma + (int) $calculo * (int) $j;
                $j++;
                $aux--;
            }
            $divisao = $soma % 11;
            $resultado = 11 - $divisao;
            if ($resultado > 9) {
                $codVerificador = 0;
            } else {
                $codVerificador = $resultado;
            }
        }

        $this->Cell(35, $this->cell, $this->_($this->boleto[$i]->getAgencia() . '/' . $this->boleto[$i]->getConta() . '-' . $codVerificador), 'R', 1);

        //terceira linha
        $this->SetFont($this->PadraoFont, '', $this->fdes);
        $this->Cell(128, $this->desc, $this->_('Endereço do Beneficiário'), 'TLR');
        $this->Cell(7, $this->desc, $this->_('UF'), 'TLR');
        $this->Cell(35, $this->desc, $this->_('CEP'), 'TLR', 1);

        $this->SetFont($this->PadraoFont, 'B', $this->fcel);
        $this->Cell(128, $this->cell, $this->_($this->boleto[$i]->getBeneficiario()->getEndereco() . ' - ' . $this->boleto[$i]->getBeneficiario()->getBairro()), 'LR');
        $this->Cell(7, $this->cell, $this->_($this->boleto[$i]->getBeneficiario()->getUf()), 'R');
        $this->Cell(35, $this->cell, $this->_($this->boleto[$i]->getBeneficiario()->getCep()), 'R', 1);

        //quarta linha

        $this->SetFont($this->PadraoFont, '', $this->fdes);
        $this->Cell(33, $this->desc, $this->_('Data do documento'), 'TLR');
        $this->Cell(34, $this->desc, $this->_('Nr. do documento'), 'TLR');
        $this->Cell(34, $this->desc, $this->_('Aceite'), 'TLR');
        $this->Cell(34, $this->desc, $this->_('Data do processamento'), 'TLR');
        $this->Cell(35, $this->desc, $this->_('Nosso Número'), 'TLR', 1);

        $this->SetFont($this->PadraoFont, 'B', $this->fcel);
        $this->Cell(33, $this->cell, $this->_($this->boleto[$i]->getDataDocumento()->format('d/m/Y')), 'LR');
        $this->Cell(34, $this->cell, $this->_($this->boleto[$i]->getNumeroDocumento()), 'R');
        $this->Cell(34, $this->cell, $this->_($this->boleto[$i]->getAceite()), 'R');
        $this->Cell(34, $this->cell, $this->_($this->boleto[$i]->getDataProcessamento()->format('d/m/Y')), 'R');
        $this->Cell(35, $this->cell, $this->_($this->boleto[$i]->getNossoNumeroBoleto()), 'R', 1, 'R');

        //quinta linha
        $this->SetFont($this->PadraoFont, '', $this->fdes);
        $this->Cell(0, $this->desc, $this->_('Instruções (Texto de responsabilidade do Beneficiário):'), 'TLR');
        $this->SetFont($this->PadraoFont, 'B', $this->fcel);

        $xInstrucoes = $this->GetX();
        $yInstrucoes = $this->GetY();
        $xOriginal = $this->GetX();
        $yOriginal = $this->GetY();

        if (count($this->boleto[$i]->getInstrucoes()) > 0) {
            $this->SetXY($xInstrucoes, $yInstrucoes);
            $this->Ln(3);
            $this->SetFont($this->PadraoFont, 'B', $this->fcel);

            $this->listaLinhas($this->boleto[$i]->getInstrucoes(), 0);

            $this->SetXY($xOriginal, $yOriginal);
            $this->Cell(0, 0, $this->_(''), 'LR', 1);
            $this->Cell(0, 20, $this->_(''), 'LR', 1);
        }

        //sexta linha
        $this->SetFont($this->PadraoFont, '', $this->fdes);
        $this->Cell(100, $this->desc, $this->_('Pagador: '), 'TL');
        $this->Cell(30, $this->desc, $this->_('CPF/CNPJ: '), 'T');
        $this->Cell(10, $this->desc, $this->_('UF: '), 'T');
        $this->Cell(30, $this->desc, $this->_('CEP: '), 'TR', 1);

        $this->SetFont($this->PadraoFont, 'B', $this->fcel);
        $this->Cell(100, $this->cell, $this->_($this->boleto[$i]->getPagador()->getNome()), 'L');
        $this->Cell(30, $this->cell, $this->_($this->boleto[$i]->getPagador()->getDocumento()));
        $this->Cell(10, $this->cell, $this->_($this->boleto[$i]->getPagador()->getUf()));
        $this->Cell(30, $this->cell, $this->_($this->boleto[$i]->getPagador()->getCep()), 'R', 1);

        $this->SetFont($this->PadraoFont, '', $this->fcel);
        $this->Cell(170, $this->desc, $this->_(trim($this->boleto[$i]->getPagador()->getEndereco() . ' - ' . $this->boleto[$i]->getPagador()->getBairro()), ' -'), 'BLR', 1);
        //setima linha
        $this->SetFont($this->PadraoFont, '', $this->fdes);
        $this->Cell(34, $this->desc, $this->_('Carteira'), 'LR');
        $this->Cell(34, $this->desc, $this->_('Espécie'), 'LR');
        $this->Cell(33, $this->desc, $this->_('Vencimento'), 'LR');
        $this->Cell(35, $this->desc, $this->_('Valor do Documento'), 'LR');
        $this->Cell(34, $this->desc, $this->_('Valor Cobrado'), 'LR', 1);

        $this->SetFont($this->PadraoFont, 'B', $this->fcel);
        $this->Cell(34, $this->cell, $this->_($this->boleto[$i]->getCarteiraNome()), 'LR');
        $this->Cell(34, $this->cell, $this->_('R$'), 'R');
        $this->Cell(33, $this->cell, $this->_($this->boleto[$i]->getDataVencimento()->format('d/m/Y')), 'R');
        $this->Cell(35, $this->cell, $this->_(Util::nReal($this->boleto[$i]->getValor())), 'R');
        $this->Cell(34, $this->cell, $this->_(''), 'R', 1, 'R');

        //oitava linha
        $msgSac = 'SAC CAIXA: 0800 726 0101 (informações, reclamações, sugestões e elogios) Para pessoas com deficiência auditiva ou de fala: 0800 726 2492 Ouvidoria: 0800 725 7474 caixa.gov.br';

        $this->SetFont($this->PadraoFont, '', $this->fdes);
        $this->Cell(101, $this->desc, $this->_('SAC CAIXA: 0800 726 0101 (informações, reclamações, sugestões e elogios)'), 'TLR', 0, 'C');
        $this->Cell(69, $this->desc, $this->_('Autenticação Mecânica - Recibo do Pagador'), 'TR', 1);

        $this->Cell(101, $this->cell, $this->_('Para pessoas com deficiência auditiva ou de fala: 0800 726 2492 '), 'LR', 0, 'C');
        $this->Cell(69, $this->cell, $this->_(''), 'R', 1);
        $this->Cell(101, $this->cell, $this->_('Ouvidoria: 0800 725 7474'), 'LR', 0, 'C');
        $this->Cell(69, $this->cell, $this->_(''), 'R', 1);
        $this->Cell(101, $this->cell, $this->_('caixa.gov.br'), 'BLR', 0, 'C');
        $this->Cell(69, $this->cell, $this->_(''), 'BR', 1);

        $this->traco('Corte na linha pontilhada', 5, 10);

        return $this;
    }

    /**
     * @param int $i
     *
     * @return PdfCaixa
     */
    protected function Bottom($i)
    {
        $this->Image($this->boleto[$i]->getLogoBanco(), 20, ($this->GetY()), 20);
        $this->Cell(29, 6, '', 'B');
        $this->SetFont($this->PadraoFont, 'B', 13);
        $this->Cell(15, 6, $this->boleto[$i]->getCodigoBancoComDv(), 'LBR', 0, 'C');
        $this->SetFont($this->PadraoFont, 'B', 10);
        $this->Cell(0, 6, $this->boleto[$i]->getLinhaDigitavel(), 'B', 1, 'R');

        $this->SetFont($this->PadraoFont, '', $this->fdes);
        $this->Cell(120, $this->desc, $this->_('Local de pagamento'), 'TLR');
        $this->Cell(50, $this->desc, $this->_('Vencimento'), 'TR', 1);

        $this->SetFont($this->PadraoFont, 'B', $this->fcel);
        $this->Cell(120, $this->cell, $this->_($this->boleto[$i]->getLocalPagamento()), 'LR');
        $this->Cell(50, $this->cell, $this->_($this->boleto[$i]->getDataVencimento()->format('d/m/Y')), 'R', 1, 'R');

        $this->SetFont($this->PadraoFont, '', $this->fdes);
        $this->Cell(120, $this->desc, $this->_('Beneficiário'), 'TLR');
        $this->Cell(50, $this->desc, $this->_('Agência/Código beneficiário'), 'TR', 1);

        $this->SetFont($this->PadraoFont, 'B', $this->fcel);
        $this->Cell(120, $this->cell, $this->_($this->boleto[$i]->getBeneficiario()->getNome() . '                ' . $this->boleto[$i]->getBeneficiario()->getDocumento()), 'LR');

        //algoritmo que calcula digito verificador beneficiario
        $codBenefCaixa = strval($this->boleto[$i]->getConta());
        if (! empty($codBenefCaixa)) {
            $j = 2;
            $aux = -1;
            $soma = 0;
            $comprimento = strlen($codBenefCaixa);
            for ($i = $comprimento; $i > 0; $i--) {
                $calculo = substr($codBenefCaixa, $aux, 1);
                $soma = $soma + (int) $calculo * (int) $j;
                $j++;
                $aux--;
            }
            $divisao = $soma % 11;
            $resultado = 11 - $divisao;
            if ($resultado > 9) {
                $codVerificador = 0;
            } else {
                $codVerificador = $resultado;
            }
        }

        $this->Cell(50, $this->cell, $this->_($this->boleto[$i]->getAgencia() . '/' . $this->boleto[$i]->getConta() . '-' . $codVerificador), 'LR', 1, 'R');
        $this->SetFont($this->PadraoFont, 'B', $this->fcel);
        $this->Cell(120, $this->desc, $this->_($this->boleto[$i]->getBeneficiario()->getEndereco() . ' - ' . $this->boleto[$i]->getBeneficiario()->getBairro()), 'LR');
        $this->Cell(50, $this->desc, $this->_(''), 'LR', 1);

        $this->SetFont($this->PadraoFont, '', $this->fdes);
        $this->Cell(30, $this->desc, $this->_('Data do documento'), 'TLR');
        $this->Cell(25, $this->desc, $this->_('Nr. do documento'), 'TR');
        $this->Cell(20, $this->desc, $this->_('Espécie Doc.'), 'TR');
        $this->Cell(20, $this->desc, $this->_('Aceite'), 'TR');
        $this->Cell(25, $this->desc, $this->_('Data processamento'), 'TR');
        $this->Cell(50, $this->desc, $this->_('Nosso número'), 'TR', 1);

        $this->SetFont($this->PadraoFont, 'B', $this->fcel);
        $this->Cell(30, $this->cell, $this->_($this->boleto[$i]->getDataDocumento()->format('d/m/Y')), 'LR');
        $this->Cell(25, $this->cell, $this->_($this->boleto[$i]->getNumeroDocumento()), 'R');
        $this->Cell(20, $this->cell, $this->_($this->boleto[$i]->getEspecieDoc()), 'R');
        $this->Cell(20, $this->cell, $this->_($this->boleto[$i]->getAceite()), 'R');
        $this->Cell(25, $this->cell, $this->_($this->boleto[$i]->getDataProcessamento()->format('d/m/Y')), 'R');
        $this->Cell(50, $this->cell, $this->_($this->boleto[$i]->getNossoNumeroBoleto()), 'R', 1, 'R');

        $this->SetFont($this->PadraoFont, '', $this->fdes);

        if (isset($this->boleto[$i]->variaveis_adicionais['esconde_uso_banco']) && $this->boleto[$i]->variaveis_adicionais['esconde_uso_banco']) {
            $this->Cell(55, $this->desc, $this->_('Carteira'), 'TLR');
        } else {
            $cip = isset($this->boleto[$i]->variaveis_adicionais['mostra_cip']) && $this->boleto[$i]->variaveis_adicionais['mostra_cip'];

            $this->Cell(($cip ? 23 : 30), $this->desc, $this->_('Uso do Banco'), 'TLR');
            if ($cip) {
                $this->Cell(7, $this->desc, $this->_('CIP'), 'TLR');
            }
            $this->Cell(25, $this->desc, $this->_('Carteira'), 'TR');
        }

        $this->Cell(20, $this->desc, $this->_('Espécie Moeda'), 'TR');
        $this->Cell(20, $this->desc, $this->_('Qtde Moeda'), 'TR');
        $this->Cell(25, $this->desc, $this->_(($this->boleto[$i]->getCodigoBanco() == '104') ? 'Valor' : 'Valor Documento'), 'TR');
        $this->Cell(50, $this->desc, $this->_('(=) Valor Documento'), 'TR', 1);

        $this->SetFont($this->PadraoFont, 'B', $this->fcel);

        if (isset($this->boleto[$i]->variaveis_adicionais['esconde_uso_banco']) && $this->boleto[$i]->variaveis_adicionais['esconde_uso_banco']) {
            $this->TextFitCell(55, $this->cell, $this->_($this->boleto[$i]->getCarteiraNome()), 'LR', 0, 'L');
        } else {
            $cip = isset($this->boleto[$i]->variaveis_adicionais['mostra_cip']) && $this->boleto[$i]->variaveis_adicionais['mostra_cip'];
            $this->Cell(($cip ? 23 : 30), $this->cell, $this->_(''), 'LR');
            if ($cip) {
                $this->Cell(7, $this->cell, $this->_($this->boleto[$i]->getCip()), 'LR');
            }
            $this->Cell(25, $this->cell, $this->_(strtoupper($this->boleto[$i]->getCarteiraNome())), 'R');
        }

        $this->Cell(20, $this->cell, $this->_('R$'), 'R');
        $this->Cell(20, $this->cell, $this->_(''), 'R');
        $this->Cell(25, $this->cell, $this->_(''), 'R');
        $this->Cell(50, $this->cell, $this->_(Util::nReal($this->boleto[$i]->getValor())), 'R', 1, 'R');

        $yStartPix = $this->GetY();
        $this->SetFont($this->PadraoFont, '', $this->fdes);
        $this->Cell(95, $this->desc, $this->_('Instruções de responsabilidade do beneficiário. '), 'TL');
        $xStartPix = $this->GetX();
        $this->Cell(25, $this->desc, '', 'TR');
        $this->Cell(50, $this->desc, $this->_('(-) Desconto)'), 'TR', 1);

        $this->SetFont($this->PadraoFont, '', $this->fdes);
        $this->Cell(120, $this->cell, $this->_('Qualquer dúvida sobre este boleto, contate o beneficiário'), 'LR');
        $this->Cell(50, $this->cell, $this->_(''), 'R', 1);

        $xInstrucoes = $this->GetX();
        $yInstrucoes = $this->GetY();

        $this->SetFont($this->PadraoFont, '', $this->fdes);
        $this->Cell(120, $this->cell, $this->_(''), 'LR');
        $this->Cell(50, $this->cell, $this->_(''), 'R', 1);

        $this->Cell(120, $this->desc, $this->_(''), 'LR');
        $this->Cell(50, $this->desc, $this->_('(-) Outras deduções / Abatimentos'), 'TR', 1);

        $this->Cell(120, $this->cell, $this->_(''), 'LR');
        $this->Cell(50, $this->cell, $this->_(''), 'R', 1);

        $this->Cell(120, $this->desc, $this->_(''), 'LR');
        $this->Cell(50, $this->desc, $this->_('(+) Mora / Multa / Juros'), 'TR', 1);

        $this->Cell(120, $this->cell, $this->_(''), 'LR');
        $this->Cell(50, $this->cell, $this->_(''), 'R', 1);

        $this->Cell(120, $this->desc, $this->_(''), 'LR');
        $this->Cell(50, $this->desc, $this->_('(+) Outros acréscimos'), 'TR', 1);

        $this->Cell(120, $this->cell, $this->_(''), 'LR');
        $this->Cell(50, $this->cell, $this->_(''), 'R', 1);

        $this->Cell(120, $this->desc, $this->_(''), 'LR');
        $this->Cell(50, $this->desc, $this->_('(=) Valor cobrado'), 'TR', 1);

        $this->Cell(120, $this->cell, $this->_(''), 'BLR');
        $this->Cell(50, $this->cell, $this->_(''), 'BR', 1);

        $this->SetFont($this->PadraoFont, '', $this->fdes);
        $this->Cell(110, $this->desc, $this->_('Pagador:'), 'L');
        $this->Cell(30, $this->desc, $this->_('CPF/CNPJ:'));
        $this->Cell(10, $this->desc, $this->_('UF:'));
        $this->Cell(20, $this->desc, $this->_('CEP:'), 'R', 1);

        $this->SetFont($this->PadraoFont, 'B', $this->fcel);
        $this->Cell(110, $this->cell, $this->_($this->boleto[$i]->getPagador()->getNome()), 'L');
        $this->Cell(30, $this->cell, $this->_($this->boleto[$i]->getPagador()->getDocumento()));
        $this->Cell(10, $this->cell, $this->_($this->boleto[$i]->getPagador()->getUf()));
        $this->Cell(20, $this->cell, $this->_($this->boleto[$i]->getPagador()->getCep()), 'R', 1);

        $this->SetFont($this->PadraoFont, '', $this->fcel);
        $this->Cell(170, $this->desc, $this->_($this->boleto[$i]->getPagador()->getEndereco()), 'L', 1);

        $this->SetFont($this->PadraoFont, '', $this->fdes);
        $this->Cell(110, $this->desc, $this->_('Beneficiário Final:'), 'L');
        $this->Cell(60, $this->desc, $this->_('CPF/CNPJ:'), 'R', 1);
        $this->SetFont($this->PadraoFont, 'B', $this->fcel);
        $this->Cell(110, $this->cell, $this->_($this->boleto[$i]->getSacadorAvalista() ? $this->boleto[$i]->getSacadorAvalista()->getNome() : ''), 'BL');
        $this->Cell(60, $this->cell, $this->_($this->boleto[$i]->getSacadorAvalista() ? $this->boleto[$i]->getSacadorAvalista()->getDocumento() : ''), 'BR', 1);

        $xOriginal = $this->GetX();
        $yOriginal = $this->GetY();

        if (count($this->boleto[$i]->getInstrucoes()) > 0) {
            $this->SetXY($xInstrucoes, $yInstrucoes);
            $this->Ln(1);
            $this->SetFont($this->PadraoFont, 'B', $this->fcel);

            $this->listaLinhas($this->boleto[$i]->getInstrucoes(), 0);

            $this->SetXY($xOriginal, $yOriginal);
        }

        if ($this->boleto[$i]->getPixQrCode() !== null) {
            $this->SetXY($xStartPix, $yStartPix);
            $this->SetFont($this->PadraoFont, '', $this->fdes);
            $this->Cell(25, $this->cell, 'Pague via PIX', '', '', 'C');
            $this->Image($this->boleto[$i]->getPixQrCodeBase64(), $xStartPix + 1, $yStartPix + 5, 23, 23, 'png');
            $this->Line($xStartPix, $yStartPix, $xStartPix, $yEndPix);

            $this->SetXY($xOriginal, $yOriginal);
        }

        $this->SetFont($this->PadraoFont, '', $this->fdes);
        $this->Cell(120, $this->desc, $this->_(''));
        $this->Cell(50, $this->desc, $this->_('Autenticação Mecânica - Ficha de Compensação'), 'LR', 1);

        $this->Cell(120, 15, '', 0, 1, 'LR');
        $this->i25($this->GetX(), $this->GetY() - 15, $this->boleto[$i]->getCodigoBarras(), 0.8, 17);
        //$this->Cell(170, $this->desc, $this->_(''), 'LR',1,'R');

        $msgSac = 'SAC CAIXA: 0800 726 0101 (informações, reclamações, sugestões e elogios)  Para pessoas com deficiência auditiva ou de fala: 0800 726 2492 Ouvidoria: 0800 725 7474 caixa.gov.br';
        $this->SetFont($this->PadraoFont, '', $this->fdes);
        $this->Cell(0, 10, $this->_($msgSac));

        return $this;
    }

    /**
     * @param string $texto
     * @param int $ln
     * @param int $ln2
     */
    protected function traco($texto, $ln = null, $ln2 = null)
    {
        if ($ln == 1 || $ln) {
            $this->Ln($ln);
        }
        $this->SetFont($this->PadraoFont, '', $this->fdes);
        if ($texto) {
            $this->Cell(0, 2, $this->_($texto), 0, 1, 'R');
        }
        $this->Cell(0, 2, str_pad('-', '261', ' -', STR_PAD_RIGHT), 0, 1);
        if ($ln2 == 1 || $ln2) {
            $this->Ln($ln2);
        }
    }

    /**
     * @param int $i
     */
    protected function codigoBarras($i)
    {
        $this->Ln(3);
        $this->Cell(0, 15, '', 0, 1, 'L');
        $this->i25($this->GetX(), $this->GetY() - 15, $this->boleto[$i]->getCodigoBarras(), 0.8, 17);
    }

    /**
     * Addiciona o boletos
     *
     * @param array $boletos
     * @param bool $withGroup
     *
     * @return PdfCaixa
     * @throws ValidationException
     */
    public function addBoletos(array $boletos, $withGroup = true)
    {
        if ($withGroup) {
            $this->StartPageGroup();
        }

        foreach ($boletos as $boleto) {
            $this->addBoleto($boleto);
        }

        return $this;
    }

    /**
     * Addiciona o boleto
     *
     * @param BoletoContract $boleto
     *
     * @return PdfCaixa
     */
    public function addBoleto(BoletoContract $boleto)
    {
        if (! $boleto->imprimeBoleto()) {
            throw new ValidationException('Boleto com modalidade/carteira não disponível para impressão');
        }
        $this->totalBoletos += 1;
        $this->boleto[] = $boleto;

        return $this;
    }

    /**
     * @return PdfCaixa
     */
    public function hideInstrucoes()
    {
        $this->showInstrucoes = false;

        return $this;
    }

    /**
     * @return PdfCaixa
     */
    public function showPrint()
    {
        $this->print = true;

        return $this;
    }

    /**
     * função para gerar o boleto
     *
     * @param string $dest tipo de destino const BOLETOPDF_DEST_STANDARD | BOLETOPDF_DEST_DOWNLOAD | BOLETOPDF_DEST_SAVE | BOLETOPDF_DEST_STRING
     * @param null $save_path
     *
     * @return string
     * @throws ValidationException
     */
    public function gerarBoleto($dest = self::OUTPUT_STANDARD, $save_path = null, $nameFile = null)
    {
        if ($this->totalBoletos == 0) {
            throw new ValidationException('Nenhum Boleto adicionado');
        }

        for ($i = 0; $i < $this->totalBoletos; $i++) {
            $this->SetDrawColor('0', '0', '0');
            $this->AddPage();
            $this->instrucoes($i)->Topo($i)->Bottom($i); //->codigoBarras($i);
        }
        if ($dest == self::OUTPUT_SAVE) {
            $this->Output($save_path, $dest, $this->print);

            return $save_path;
        }
        if ($nameFile == null) {
            $nameFile = Str::random(32);
        }

        return $this->Output($nameFile . '.pdf', $dest, $this->print);
    }

    /**
     * @param $lista
     * @param int $pulaLinha
     *
     * @return int
     */
    private function listaLinhas($lista, $pulaLinha)
    {
        foreach ($lista as $d) {
            $pulaLinha -= 2;
            $this->MultiCell(0, $this->cell - 0.2, $this->_(preg_replace('/(%)/', '%$1', $d)), 0, 1);
        }

        return $pulaLinha;
    }
}
