<?php
namespace Eduardokum\LaravelBoleto\Boleto\Render;

use Eduardokum\LaravelBoleto\Boleto\AbstractPdf;
use Eduardokum\LaravelBoleto\Boleto\Contracts\Boleto;
use Eduardokum\LaravelBoleto\Util;

class Pdf extends AbstractPdf
{

    const OUTPUT_STANDARD   = 'I';
    const OUTPUT_DOWNLOAD   = 'D';
    const OUTPUT_SAVE       = 'F';
    const OUTPUT_STRING     = 'S';

    private $PadraoFont = 'Arial';
    private $logoPath;
    /**
     * @var Boleto[]
     */
    private $boleto = array();

    private $desc  =   3;  // tamanho célula descrição
    private $cell  =   4;  // tamanho célula dado
    private $fdes  =   6;  // tamanho fonte descrição
    private $fcel  =   8;  // tamanho fonte célula
    private $small = 0.2;  // tamanho barra fina
    private $large = 0.6;  // tamanho barra larga
    private $totalBoletos = 0;

    function __construct() {
        parent::__construct('P','mm','A4');
        $this->SetLeftMargin(20);
        $this->SetTopMargin(5);
        $this->SetRightMargin(20);
        $this->SetLineWidth($this->small);

        $this->logoPath = realpath( dirname( __FILE__) . '/../../../logos/' ) . DIRECTORY_SEPARATOR ;
    }

    public function addBoleto(Boleto $boleto){
        $this->totalBoletos += 1;
        $this->boleto[] = $boleto;
    }

    public function instrucoes($i){

        $this->SetFont($this->PadraoFont,'', 8);
        if( $this->totalBoletos > 1 ){
            $this->Cell(30, 10, date('d/m/Y H:i:s'));
            $this->Cell(0, 10, "Boleto " . ($i+1) . " de " . $this->totalBoletos, 0, 1, 'R');
        }

        $this->SetFont($this->PadraoFont,'B', 8);
        $this->Cell(0, 5, $this->_('Instruções de Impressão'), 0, 1, 'C');
        $this->Ln(3);
        $this->SetFont($this->PadraoFont,'',6);
        $this->Cell(0, $this->desc, $this->_('- Imprima em impressora jato de tinta (ink jet) ou laser em qualidade normal ou alta (Não use modo econômico).'), 0, 1, 'L');
        $this->Cell(0, $this->desc, $this->_('- Utilize folha A4 (210 x 297 mm) ou Carta (216 x 279 mm) e margens mínimas à esquerda e à direita do formulário.'), 0, 1, 'L');
        $this->Cell(0, $this->desc, $this->_('- Corte na linha indicada. Não rasure, risque, fure ou dobre a região onde se encontra o código de barras.'), 0, 1, 'L');
        $this->Cell(0, $this->desc, $this->_('- Caso não apareça o código de barras no final, clique em F5 para atualizar esta tela.'), 0, 1, 'L');
        $this->Cell(0, $this->desc, $this->_('- Caso tenha problemas ao imprimir, copie a seqüencia numérica abaixo e pague no caixa eletrônico ou no internet banking:'), 0, 1, 'L');
        $this->Ln(4);

        $this->SetFont($this->PadraoFont, '', $this->fcel);
        $this->Cell(25, $this->cell, $this->_('Linha Digitável: '), 0, 0);
        $this->SetFont($this->PadraoFont, 'B', $this->fcel);
        $this->Cell( 0, $this->cell, $this->_( $this->boleto[$i]->getLinha() ), 0, 1);
        $this->SetFont($this->PadraoFont, '', $this->fcel);
        $this->Cell(25, $this->cell, $this->_('Valor: '), 0, 0);
        $this->SetFont($this->PadraoFont, 'B', $this->fcel);
        $this->Cell( 0, $this->cell, $this->_( Util::nReal($this->boleto[$i]->getValor()) ));
        $this->SetFont($this->PadraoFont, '', $this->fcel);

        $this->traco('Recibo do Pagador', 4);

        return $this;
    }

    public function logoEmpresa($i){

        $this->Ln(10);
        $this->SetFont($this->PadraoFont, '', $this->fdes);

        $this->Image( $this->boleto[$i]->getLogo() ,20, ($this->GetY()), 0 , 12, 'PNG');
        $this->Cell(56);
        $this->Cell(0, $this->desc, $this->_( $this->boleto[$i]->getIdentificacao() ),0,1);
        $this->Cell(56);
        $this->Cell(0, $this->desc, $this->_( Util::maskString($this->boleto[$i]->getCedenteDocumento(), '##.###.###/####-##') ),0,1);
        $this->Cell(56);
        $endereco = explode("_", $this->boleto[$i]->getCedenteEndereco());
        $this->Cell(0, $this->desc, $this->_( $endereco[0] ),0,1);
        $this->Cell(56);
        $this->Cell(0, $this->desc, $this->_( $endereco[1] ),0,1);
        $this->Cell(56);
        $this->Cell(0, $this->desc, $this->_( $endereco[2] ),0,1);
        $this->Cell(56);
        $this->Cell(0, $this->desc, $this->_( $this->boleto[$i]->getCedenteCidadeUF() ),0,1);
        $this->Ln(10);

        return $this;
    }

    public function Topo($i) {

        $this->Image( $this->logoPath . $this->boleto[$i]->getBanco() . '.png', 20, ($this->GetY()-2), 28);
        $this->Cell(29, 6, '', 'B');
        $this->SetFont('','B',13);
        $this->Cell(15, 6, $this->boleto[$i]->getBanco(true), 'LBR', 0, 'C');
        $this->SetFont('','B',10);
        $this->Cell(0, 6, $this->boleto[$i]->getLinha() ,'B',1,'R');
        $this->risco();

        $this->SetFont($this->PadraoFont, '', $this->fdes);
        $this->Cell(75, $this->desc, $this->_('Beneficiário'), 'TLR');
        $this->Cell(35, $this->desc, $this->_('Agencia/Codigo do beneficiário'), 'TR');
        $this->Cell(10, $this->desc, $this->_('Espécie'), 'TR');
        $this->Cell(20, $this->desc, $this->_('Quantidade'), 'TR');
        $this->Cell(30, $this->desc, $this->_('Nosso Numero'), 'TR', 1);

        $this->SetFont($this->PadraoFont, 'B', $this->fcel);

        $this->textFitCell(75, $this->cell, $this->_( $this->boleto[$i]->getCedenteNome() ), 'LR', 0, 'L');

        $this->Cell(35, $this->cell, $this->_( $this->boleto[$i]->getAgenciaConta() ), 'R');
        $this->Cell(10, $this->cell, $this->_( 'R$' ), 'R');
        $this->Cell(20, $this->cell, $this->_( '1' ), 'R');
        $this->Cell(30, $this->cell, $this->_( $this->boleto[$i]->getNossoNumero() ), 'R' , 1, 'R');

        $this->SetFont($this->PadraoFont, '', $this->fdes);
        $this->Cell(50, $this->desc, $this->_('Número do Documento'), 'TLR');
        $this->Cell(40, $this->desc, $this->_('CPF/CNPJ'), 'TR');
        $this->Cell(30, $this->desc, $this->_('Vencimento'), 'TR');
        $this->Cell(50, $this->desc, $this->_('Valor do Documento'), 'TR' , 1);

        $this->SetFont($this->PadraoFont, 'B', $this->fcel);
        $this->Cell(50, $this->cell, $this->_( $this->boleto[$i]->getNumero() ), 'LR');
        $this->Cell(40, $this->cell, $this->_( Util::maskString($this->boleto[$i]->getCedenteDocumento() , '##.###.###/####-##' ) ), 'R');
        $this->Cell(30, $this->cell, $this->_( $this->boleto[$i]->getDataVencimento()->format('d/m/Y') ), 'R');
        $this->Cell(50, $this->cell, $this->_( Util::nReal($this->boleto[$i]->getValor())), 'R', 1, 'R');

        $this->SetFont($this->PadraoFont, '', $this->fdes);
        $this->Cell(30, $this->desc, $this->_('(-) Descontos/Abatimentos'), 'TLR');
        $this->Cell(30, $this->desc, $this->_('(-) Outras Deduções'), 'TR');
        $this->Cell(30, $this->desc, $this->_('(+) Mora Multa'), 'TR');
        $this->Cell(30, $this->desc, $this->_('(+) Acréscimos'), 'TR');
        $this->Cell(50, $this->desc, $this->_('(=) Valor Cobrado'), 'TR' , 1);

        $this->SetFont($this->PadraoFont, 'B', $this->fcel);
        $this->Cell(30, $this->cell, $this->_(''), 'LR');
        $this->Cell(30, $this->cell, $this->_(''), 'R');
        $this->Cell(30, $this->cell, $this->_(''), 'R');
        $this->Cell(30, $this->cell, $this->_(''), 'R');
        $this->Cell(50, $this->cell, $this->_(''), 'R' , 1, 'R');

        $this->SetFont($this->PadraoFont, '', $this->fdes);
        $this->Cell(0, $this->desc, $this->_('Pagador'), 'TLR', 1);

        $this->SetFont($this->PadraoFont, 'B', $this->fcel);
        $this->Cell(0, $this->cell, $this->_( $this->boleto[$i]->getSacadoNome() ), 'BLR', 1);

        $this->SetFont($this->PadraoFont, '', $this->fdes);
        $this->Cell(100, $this->desc, $this->_('Demonstrativo'), 0, 0,'L');
        $this->Cell(0, $this->desc, $this->_('Autenticação mecânica'), 0, 1,'R');
        $this->Ln(2);

        $pulaLinha = 16;

        $this->SetFont($this->PadraoFont, 'B', $this->fcel);
        if(count($this->boleto[$i]->getDemonstrativos()) > 0)
        {
            foreach ($this->boleto[$i]->getDemonstrativos() as $d) {
                $pulaLinha -= 2;
                $this->Cell (0, $this->cell, $this->_ ( $d ), 0, 1);
            }
        }

        $this->traco('Corte na linha pontilhada',$pulaLinha,5);

        return $this;

    }

    public function TopoBB($i) {

        $this->SetFillColor(255,255,204);

        $this->SetDrawColor('00','00', '80');
        $this->Image($this->logoPath . $this->boleto[$i]->getBanco() . '.png', 20, ($this->GetY()-2), 28);
        $this->Cell(29, 6, '', '');
        $this->SetFont('','',13);
        $this->Cell(15, 6, $this->boleto[$i]->getBanco(true), 'LR', 0, 'C');
        $this->SetFont('','',10);
        $this->Cell(0, 6, $this->boleto[$i]->getLinha() ,'',1,'R');
        $this->Ln(1);
        $this->risco();

        $this->Ln(1);

        $this->SetFont($this->PadraoFont, '', $this->fdes);

        $this->riscoBB($this->GetX(),$this->GetY());
        $this->Cell(75, $this->desc, $this->_('Beneficiário'), '');

        $this->riscoBB($this->GetX(),$this->GetY());
        $this->Cell(35, $this->desc, $this->_('Agencia/Codigo do beneficiário'), '');

        $this->riscoBB($this->GetX(),$this->GetY());
        $this->Cell(10, $this->desc, $this->_('Espécie'), '');

        $this->riscoBB($this->GetX(),$this->GetY());
        $this->Cell(20, $this->desc, $this->_('Quantidade'), '');

        $this->riscoBB($this->GetX(),$this->GetY());
        $this->Cell(30, $this->desc, $this->_('Nosso Numero'), '', 1);

        $this->SetFont($this->PadraoFont, 'B', $this->fcel);

        $this->textFitCell(75, $this->cell, $this->_( $this->boleto[$i]->getCedenteNome() ), '', 0, 'L');

        $this->Cell(35, $this->cell, $this->_( $this->boleto[$i]->getAgenciaConta() ), '');
        $this->Cell(10, $this->cell, $this->_( 'R$' ), '');
        $this->Cell(20, $this->cell, $this->_( '1' ), '');
        $this->Cell(30, $this->cell, $this->_( $this->boleto[$i]->getNossoNumero() ), '' , 1, '');

        $this->SetFont($this->PadraoFont, '', $this->fdes);
        $this->riscoBB($this->GetX(),$this->GetY());
        $this->Cell(50, $this->desc, $this->_('Número do Documento'), 'T');

        $this->riscoBB($this->GetX(),$this->GetY());
        $this->Cell(40, $this->desc, $this->_('CPF/CNPJ'), 'T');

        $this->riscoBB($this->GetX(),$this->GetY());
        $this->Cell(30, $this->desc, $this->_('Vencimento'), 'T');

        $this->riscoBB($this->GetX(),$this->GetY());
        $this->Cell(50, $this->desc, $this->_('Valor do Documento'), 'T' , 1);

        $this->SetFont($this->PadraoFont, 'B', $this->fcel);
        $this->Cell(50, $this->cell, $this->_( $this->boleto[$i]->getNumero() ), '');
        $this->Cell(40, $this->cell, $this->_( Util::maskString($this->boleto[$i]->getCedenteDocumento(),'##.###.###/####-##') ), '');
        $this->Cell(30, $this->cell, $this->_( $this->boleto[$i]->getDataVencimento()->format('d/m/Y') ), '');
        $this->Cell(50, $this->cell, $this->_( Util::nReal($this->boleto[$i]->getValor()) ), '', 1, 'R');

        $this->SetFont($this->PadraoFont, '', $this->fdes);
        $this->riscoBB($this->GetX(),$this->GetY());
        $this->Cell(30, $this->desc, $this->_('(-) Descontos/Abatimentos'), 'T');

        $this->riscoBB($this->GetX(),$this->GetY());
        $this->Cell(30, $this->desc, $this->_('(-) Outras Deduções'), 'T');

        $this->riscoBB($this->GetX(),$this->GetY());
        $this->Cell(30, $this->desc, $this->_('(+) Mora Multa'), 'T');

        $this->riscoBB($this->GetX(),$this->GetY());
        $this->Cell(30, $this->desc, $this->_('(+) Acréscimos'), 'T');

        $x = $this->GetX();$y = $this->GetY();
        $this->Cell(50, $this->desc, $this->_('(=) Valor Cobrado'), 'T' , 1, 'L', true);

        $this->SetFont($this->PadraoFont, 'B', $this->fcel);
        $this->Cell(30, $this->cell, $this->_(''), '');
        $this->Cell(30, $this->cell, $this->_(''), '');
        $this->Cell(30, $this->cell, $this->_(''), '');
        $this->Cell(30, $this->cell, $this->_(''), '');
        $this->Cell(50, $this->cell, $this->_(''), '' , 1, 'R', true );

        $this->riscoBB($x,$y);

        $this->SetFont($this->PadraoFont, '', $this->fdes);
        $this->riscoBB($this->GetX(),$this->GetY());
        $this->Cell(0, $this->desc, $this->_('Pagador'), 'T', 1);

        $this->SetFont($this->PadraoFont, 'B', $this->fcel);
        $this->Cell(0, $this->cell, $this->_( $this->boleto[$i]->getSacadoNome() ), 'B', 1);

        $this->SetFont($this->PadraoFont, '', $this->fdes);
        $this->Cell(100, $this->desc, $this->_('Demonstrativo'), 0, 0,'L');
        $this->Cell(0, $this->desc, $this->_('Autenticação mecânica'), 0, 1,'R');
        $this->Ln(2);

        $pulaLinha = 16;

        $this->SetFont($this->PadraoFont, 'B', $this->fcel);
        if(count($this->boleto[$i]->getDemonstrativos()) > 0 )
        {
            foreach ($this->boleto[$i]->getDemonstrativos() as $d) {
                $pulaLinha -= 2;
                $this->Cell (0, $this->cell, $this->_ ( $d ), 0, 1);
            }
        }

        $this->traco('Corte na linha pontilhada',$pulaLinha,5);

        return $this;

    }

    public function Bottom($i){

        $this->Image($this->logoPath . $this->boleto[$i]->getBanco() . '.png', 20, ($this->GetY()-2), 28);
        $this->Cell(29, 6, '', 'B');
        $this->SetFont($this->PadraoFont, 'B', 13);
        $this->Cell(15, 6, $this->boleto[$i]->getBanco(true), 'LBR', 0, 'C');
        $this->SetFont($this->PadraoFont, 'B', 10);
        $this->Cell(0, 6, $this->boleto[$i]->getLinha() ,'B',1,'R');
        $this->risco();

        $this->SetFont($this->PadraoFont, '', $this->fdes);
        $this->Cell(120, $this->desc, $this->_('Local de pagamento'), 'TLR');
        $this->Cell(50, $this->desc, $this->_('Vencimento'), 'TR', 1);

        $this->SetFont($this->PadraoFont, 'B', $this->fcel);
        $this->Cell(120, $this->cell, $this->_( $this->boleto[$i]->getLocalPagamento() ), 'LR');
        $this->Cell(50, $this->cell, $this->_( $this->boleto[$i]->getDataVencimento()->format('d/m/Y') ), 'R', 1, 'R');

        $this->SetFont($this->PadraoFont, '', $this->fdes);
        $this->Cell(120, $this->desc, $this->_('Beneficiário'), 'TLR');
        $this->Cell(50, $this->desc, $this->_('Agência/Código beneficiário'), 'TR', 1);

        $this->SetFont($this->PadraoFont, 'B', $this->fcel);
        $this->Cell(60, $this->cell, $this->_( $this->boleto[$i]->getCedenteNome() ), 'L');
        $this->Cell(60, $this->cell, $this->_( 'CPF/CNPJ - ' . Util::maskString($this->boleto[$i]->getCedenteDocumento(), sizeof($this->boleto[$i]->getCedenteDocumento()) > 11 ? '##.###.###/####-##' : '###.###.###-##')), 'R');
        $this->Cell(50, $this->cell, $this->_( $this->boleto[$i]->getAgenciaConta() ), 'R', 1, 'R');

        $this->SetFont($this->PadraoFont, '', $this->fdes);
        $this->Cell(30, $this->desc, $this->_('Data do documento'), 'TLR');
        $this->Cell(40, $this->desc, $this->_('Número do documento'), 'TR');
        $this->Cell(15, $this->desc, $this->_('Espécie Doc.'), 'TR');
        $this->Cell(10, $this->desc, $this->_('Aceite'), 'TR');
        $this->Cell(25, $this->desc, $this->_('Data processamento'), 'TR');
        $this->Cell(50, $this->desc, $this->_('Nosso número'), 'TR', 1);

        $this->SetFont($this->PadraoFont, 'B', $this->fcel);
        $this->Cell(30, $this->cell, $this->_( $this->boleto[$i]->getDataDocumento()->format('d/m/Y') ), 'LR');
        $this->Cell(40, $this->cell, $this->_( $this->boleto[$i]->getNumero() ), 'R');
        $this->Cell(15, $this->cell, $this->_( $this->boleto[$i]->getEspecieDocumento() ), 'R');
        $this->Cell(10, $this->cell, $this->_( $this->boleto[$i]->getAceite() ), 'R');
        $this->Cell(25, $this->cell, $this->_( $this->boleto[$i]->getDataProcessamento()->format('d/m/Y') ), 'R');
        $this->Cell(50, $this->cell, $this->_( $this->boleto[$i]->getNossoNumero() ), 'R', 1, 'R');

        $this->SetFont($this->PadraoFont, '', $this->fdes);

        if( $this->boleto[$i]->getBanco() == '033' ) {
            $this->Cell(55, $this->desc, $this->_('Carteira'), 'TLR');
        } else {
            $this->Cell(30, $this->desc, $this->_('Uso do Banco'), 'TLR');
            $this->Cell(25, $this->desc, $this->_('Carteira') , 'TR');
        }

        $this->Cell(12, $this->desc, $this->_('Espécie'), 'TR');
        $this->Cell(28, $this->desc, $this->_('Quantidade'), 'TR');
        $this->Cell(25, $this->desc, $this->_('Valor Documento'), 'TR');
        $this->Cell(50, $this->desc, $this->_('Valor Documento'), 'TR', 1);

        $this->SetFont($this->PadraoFont, 'B', $this->fcel);
        if( $this->boleto[$i]->getBanco() == '033' ) {
            $this->TextFitCell(55, $this->cell, $this->_( $this->boleto[$i]->getCarteira(true)), 'LR', 0, 'L');
        } else {
            $this->Cell(30, $this->cell, $this->_( '' ), 'LR');
            $this->Cell(25, $this->cell, $this->_( strtoupper( $this->boleto[$i]->getCarteira() ) ), 'R');
        }
        $this->Cell(12, $this->cell, $this->_( 'R$' ), 'R');
        $this->Cell(28, $this->cell, $this->_( '1' ), 'R');
        $this->Cell(25, $this->cell, $this->_( Util::nReal($this->boleto[$i]->getValor()) ), 'R');
        $this->Cell(50, $this->cell, $this->_( Util::nReal($this->boleto[$i]->getValor()) ), 'R', 1, 'R');

        $this->SetFont($this->PadraoFont, '', $this->fdes);
        $this->Cell(120, $this->desc, $this->_('Instruções (Texto de responsabilidade do beneficiário)'), 'TLR');
        $this->Cell(50, $this->desc, $this->_('(-) Desconto / Abatimentos)'), 'TR', 1);

        $xInstrucoes = $this->GetX();
        $yInstrucoes = $this->GetY();

        $this->SetFont($this->PadraoFont, '', $this->fdes);
        $this->Cell(120, $this->cell, $this->_(''), 'LR');
        $this->Cell(50, $this->cell, $this->_(''), 'R', 1);

        $this->Cell(120, $this->desc, $this->_(''), 'LR');
        $this->Cell(50, $this->desc, $this->_('(-) Outras deduções'), 'TR', 1);

        $this->Cell(120, $this->cell, $this->_(''), 'LR');
        $this->Cell(50, $this->cell, $this->_(''), 'R', 1);

        $this->Cell(120, $this->desc, $this->_(''), 'LR');
        $this->Cell(50, $this->desc, $this->_('(+) Mora / Multa'), 'TR', 1);

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
        $this->Cell(0, $this->desc, $this->_('Pagador'), 'LR', 1);

        $this->SetFont($this->PadraoFont, 'B', $this->fcel);
        $this->Cell(60, $this->cell, $this->_($this->boleto[$i]->getSacadoNome()), 'L');
        $this->Cell(0, $this->cell, $this->_('CPF/CNPJ - ' . Util::maskString($this->boleto[$i]->getSacadoDocumento(), sizeof($this->boleto[$i]->getSacadoDocumento()) > 11 ? '##.###.###/####-##' : '###.###.###-##')), 'R', 1);
        $endereco = explode('_', $this->boleto[$i]->getSacadoEndereco());
        $this->Cell(60, $this->cell, $this->_($endereco[0]), 'L');
        $this->Cell(0, $this->cell, $this->_(!empty($endereco[1])?'CEP - ' . Util::maskString($endereco[1], '#####-###'): ""), 'R' );
        $this->Ln();
        $this->Cell(60, $this->cell, $this->_($endereco[2]), 'L');
        $this->Cell(0, $this->cell, $this->_($this->boleto[$i]->getSacadoCidadeUF()), 'R', 1);

        $this->SetFont($this->PadraoFont, '', $this->fdes);
        $this->Cell(120, $this->cell, $this->_(''), 'BLR');
        $this->Cell(12, $this->cell, $this->_( 'Cód. Baixa' ), 'B');
        $this->SetFont($this->PadraoFont,'B',  $this->fcel);
        $this->Cell(38  , $this->cell, $this->_( $this->boleto[$i]->getCodigoBaixa() ), 'BR', 1);


        $this->SetFont($this->PadraoFont,'',  $this->fdes);
        $this->Cell(118, $this->desc, $this->_( 'Pagador/Avalista' ), 0);
        $this->Cell(52, $this->desc, $this->_( 'Autenticação mecânica - Ficha de Compensação' ),0,1);

        $xOriginal = $this->GetX();
        $yOriginal = $this->GetY();

        if(count($this->boleto[$i]->getInstrucoes()) > 0)
        {
            $this->SetXY($xInstrucoes, $yInstrucoes);
            $this->Ln(4);
            $this->SetFont($this->PadraoFont,'B',  $this->fcel);
            foreach($this->boleto[$i]->getInstrucoes() as $in )
                $this->Cell (0, $this->cell, $this->_ ($in), 0, 1);

            $this->SetXY($xOriginal, $yOriginal);
        }
        return $this;
    }

    public function BottomBB($i){

        $this->SetDrawColor('00','00', '80');
        $this->Image($this->logoPath . $this->boleto[$i]->getBanco() . '.png', 20, ($this->GetY()-2), 28);
        $this->Cell(29, 6, '', '');
        $this->SetFont('','',13);
        $this->Cell(15, 6, $this->boleto[$i]->getBanco(true), 'LR', 0, 'C');
        $this->SetFont('','',10);
        $this->Cell(0, 6, $this->boleto[$i]->getLinha() ,'',1,'R');
        $this->Ln(1);
        $this->risco();

        $this->Ln(1);

        $this->SetFont($this->PadraoFont, '', $this->fdes);
        $this->riscoBB($this->GetX(),$this->GetY());
        $this->Cell(120, $this->desc, $this->_('Local de pagamento'), '');
        $x = $this->GetX(); $y = $this->GetY();
        $this->Cell(50, $this->desc, $this->_('Vencimento'), '', 1,'L',true);

        $this->SetFont($this->PadraoFont, 'B', $this->fcel);
        $this->Cell(120, $this->cell, $this->_( $this->boleto[$i]->getLocalPagamento() ), '');
        $this->Cell(50, $this->cell, $this->_( $this->boleto[$i]->getDataVencimento()->format('d/m/Y') ), '', 1, 'R',true);
        $this->riscoBB($x,$y);

        $this->SetFont($this->PadraoFont, '', $this->fdes);
        $this->riscoBB($this->GetX(),$this->GetY());
        $this->Cell(120, $this->desc, $this->_('Beneficiário'), 'T');

        $this->riscoBB($this->GetX(),$this->GetY());
        $this->Cell(50, $this->desc, $this->_('Agência/Código beneficiário'), 'T', 1);

        $this->SetFont($this->PadraoFont, 'B', $this->fcel);
        $this->Cell(120, $this->cell, $this->_( $this->boleto[$i]->getCedenteNome() ), '');
        $this->Cell(50, $this->cell, $this->_( $this->boleto[$i]->getBanco(true) ), '', 1, '');

        $this->SetFont($this->PadraoFont, '', $this->fdes);
        $this->riscoBB($this->GetX(),$this->GetY());
        $this->Cell(30, $this->desc, $this->_('Data do documento'), 'T');

        $this->riscoBB($this->GetX(),$this->GetY());
        $this->Cell(40, $this->desc, $this->_('Número do documento'), 'T');

        $this->riscoBB($this->GetX(),$this->GetY());
        $this->Cell(15, $this->desc, $this->_('Espécie Doc.'), 'T');

        $this->riscoBB($this->GetX(),$this->GetY());
        $this->Cell(10, $this->desc, $this->_('Aceite'), 'T');

        $this->riscoBB($this->GetX(),$this->GetY());
        $this->Cell(25, $this->desc, $this->_('Data processamento'), 'T');

        $this->riscoBB($this->GetX(),$this->GetY());
        $this->Cell(50, $this->desc, $this->_('Nosso número'), 'T', 1);

        $this->SetFont($this->PadraoFont, 'B', $this->fcel);
        $this->Cell(30, $this->cell, $this->_( $this->boleto[$i]->getDataDocumento()->format('d/m/Y') ), '');
        $this->Cell(40, $this->cell, $this->_( $this->boleto[$i]->getNumero() ), '');
        $this->Cell(15, $this->cell, $this->_( $this->boleto[$i]->getEspecieDocumento() ), '');
        $this->Cell(10, $this->cell, $this->_( $this->boleto[$i]->getAceite() ), '');
        $this->Cell(25, $this->cell, $this->_( $this->boleto[$i]->getDataProcessamento()->format('d/m/Y') ), '');
        $this->Cell(50, $this->cell, $this->_( $this->boleto[$i]->getNossoNumero() ), '', 1, 'R');

        $this->SetFont($this->PadraoFont, '', $this->fdes);
        $x = $this->GetX(); $y = $this->GetY();
        $this->Cell(30, $this->desc, $this->_('Uso do Banco'), 'T', 0, '', true);

        $this->riscoBB($this->GetX(),$this->GetY());
        $this->Cell(25, $this->desc, $this->_('Carteira'), 'T');

        $this->riscoBB($this->GetX(),$this->GetY());
        $this->Cell(12, $this->desc, $this->_('Espécie'), 'T');

        $this->riscoBB($this->GetX(),$this->GetY());
        $this->Cell(28, $this->desc, $this->_('Quantidade'), 'T');

        $this->riscoBB($this->GetX(),$this->GetY());
        $this->Cell(25, $this->desc, $this->_('Valor Documento'), 'T');

        $this->riscoBB($this->GetX(),$this->GetY());
        $this->Cell(50, $this->desc, $this->_('Valor Documento'), 'T', 1);

        $this->SetFont($this->PadraoFont, 'B', $this->fcel);
        $this->Cell(30, $this->cell, $this->_( '' ), '', 0, '', true);
        $this->riscoBB($x, $y);
        $this->Cell(25, $this->cell, $this->_( $this->boleto[$i]->getCarteira() ), '');
        $this->Cell(12, $this->cell, $this->_( 'R$' ), '');
        $this->Cell(28, $this->cell, $this->_(  '1' ), '');
        $this->Cell(25, $this->cell, $this->_( Util::nReal($this->boleto[$i]->getValor()) ), '');
        $this->Cell(50, $this->cell, $this->_( Util::nReal($this->boleto[$i]->getValor()) ), '', 1, 'R');

        $this->SetFont($this->PadraoFont, '', $this->fdes);
        $this->Cell(120, $this->desc, $this->_('Instruções (Texto de responsabilidade do beneficiário)'), 'T');
        $this->riscoBB($this->GetX(),$this->GetY());
        $this->Cell(50, $this->desc, $this->_('(-) Desconto / Abatimentos)'), 'T', 1);

        $xInstrucoes = $this->GetX();
        $yInstrucoes = $this->GetY();

        $this->SetFont($this->PadraoFont, '', $this->fdes);
        $this->Cell(120, $this->cell, $this->_(''), '');
        $this->Cell(50, $this->cell, $this->_(''), '', 1);

        $this->Cell(120, $this->desc, $this->_(''), '');
        $this->riscoBB($this->GetX(),$this->GetY());
        $this->Cell(50, $this->desc, $this->_('(-) Outras deduções'), 'T', 1);

        $this->Cell(120, $this->cell, $this->_(''), '');
        $this->Cell(50, $this->cell, $this->_(''), '', 1);

        $this->Cell(120, $this->desc, $this->_(''), '');
        $this->riscoBB($this->GetX(),$this->GetY());
        $this->Cell(50, $this->desc, $this->_('(+) Mora / Multa'), 'T', 1);

        $this->Cell(120, $this->cell, $this->_(''), '');
        $this->Cell(50, $this->cell, $this->_(''), '', 1);

        $this->Cell(120, $this->desc, $this->_(''), '');
        $this->riscoBB($this->GetX(),$this->GetY());
        $this->Cell(50, $this->desc, $this->_('(+) Outros acréscimos'), 'T', 1);

        $this->Cell(120, $this->cell, $this->_(''), '');
        $this->Cell(50, $this->cell, $this->_(''), '', 1);

        $this->Cell(120, $this->desc, $this->_(''), '');
        $x = $this->GetX(); $y = $this->GetY();
        $this->Cell(50, $this->desc, $this->_('(=) Valor cobrado'), 'T', 1,'',true);

        $this->Cell(120, $this->cell, $this->_(''), 'B');
        $this->Cell(50, $this->cell, $this->_(''), 'B', 1,'',true);
        $this->riscoBB($x, $y);

        $this->SetFont($this->PadraoFont, '', $this->fdes);
        $this->riscoBB($this->GetX(),$this->GetY(),18);
        $this->Cell(0, $this->desc, $this->_('Pagador'), '', 1);

        $this->SetFont($this->PadraoFont, 'B', $this->fcel);
        $this->Cell(0, $this->cell, $this->_( $this->boleto[$i]->getSacadoNome() ), '', 1);
        $this->Cell(0, $this->cell, $this->_( $this->boleto[$i]->getSacadoEndereco() ), '', 1);
        $this->Cell(0, $this->cell, $this->_( $this->boleto[$i]->getSacadoCidadeUF() ), '', 1);

        $this->SetFont($this->PadraoFont, '', $this->fdes);
        $this->Cell(120, $this->cell, $this->_('Pagador/Avalista'), 'TB');
        $this->riscoBB($this->GetX(),$this->GetY(),3);
        $this->Cell(12, $this->cell, $this->_( 'Cód. Baixa' ), 'BT');
        $this->SetFont($this->PadraoFont,'B',  $this->fcel);
        $this->Cell(38  , $this->cell, $this->_( $this->boleto[$i]->getCodigoBaixa() ), 'BT', 1);


        $this->SetFont($this->PadraoFont,'',  $this->fdes);
        $this->Cell(118, $this->desc, $this->_( '' ), 0);
        $this->Cell(52, $this->desc, $this->_( 'Autenticação mecânica - Ficha de Compensação' ),0,1);

        $xOriginal = $this->GetX();
        $yOriginal = $this->GetY();

        if(count($this->boleto[$i]->getInstrucoes()) > 0)
        {
            $this->SetXY($xInstrucoes, $yInstrucoes);
            $this->Ln(1);
            $this->SetFont($this->PadraoFont,'B',  $this->fcel);
            foreach($this->boleto[$i]->getInstrucoes() as $in )
                $this->Cell (0, $this->cell, $this->_ ($in), 0, 1);

        }

        $this->SetXY($xOriginal, $yOriginal);
        return $this;
    }

    public function codigoBarras($i){
        $this->Ln(2);
        $this->Cell(0, 15, '', 0, 1, 'L');
        $this->i25($this->GetX(),  $this->GetY()-15, $this->boleto[$i]->getCodigoBarras(),0.89,14);
        $this->traco('Corte na linha pontilhada',2);
    }

    /**
     * função para gerar o boleto
     * @param string $dest tipo de destino const BOLETOPDF_DEST_STANDARD | BOLETOPDF_DEST_DOWNLOAD | BOLETOPDF_DEST_SAVE | BOLETOPDF_DEST_STRING
     * @param bool $print se vai solicitar impressão
     * @return string
     * @throws \Exception
     */
    public function gerarBoleto( $dest = self::OUTPUT_STANDARD, $print = false ){

        $nomeBoleto = "boletosPDF/". date('dmYHis') .".pdf";

        if($this->totalBoletos == 0)
        {
            throw new \Exception ('Nenhum Boleto adicionado');
        }

        for( $i = 0 ; $i < $this->totalBoletos ; $i ++ ){
            if( $this->boleto[$i]->getBanco() == '001' ) {
                $this->AddPage();
                $this->instrucoes($i)->logoEmpresa($i)->TopoBB($i)->BottomBB($i)->codigoBarras($i);
            } else {
                $this->SetDrawColor('0','0','0');
                $this->AddPage();
                $this->instrucoes($i)->logoEmpresa($i)->Topo($i)->Bottom($i)->codigoBarras($i);
            }
        }
        $this->Output($nomeBoleto, $dest, $print);
        return $nomeBoleto;
    }

    private function traco($texto,$ln= null,$ln2 = null) {
        if($ln) $this->Ln ($ln);
        $this->SetFont($this->PadraoFont, '', $this->fdes);
        if($texto){
            $this->Cell(0, 2, $this->_($texto), 0, 1, 'R');
        }
        $this->Cell(0, 2, str_pad('-', '261', ' -', STR_PAD_RIGHT), 0, 1);
        if($ln2) $this->Ln ($ln2);
    }

    private function risco(){
        $this->SetLineWidth($this->large);
        $this->Line(20.3, $this->GetY(), 189.7, $this->GetY());
        $this->SetLineWidth($this->small);
    }

    private function riscoBB($a,$b,$t = 6){
        $this->SetDrawColor('243','211','30');
        $this->SetLineWidth($this->large);
        $this->Rect(($a+0.2), ($b+0.3),'0.2',  $t);
        $this->SetLineWidth($this->small);
        $this->SetDrawColor('00','00', '80');
    }

}