@extends('BoletoHtmlRender::layout')
@section('boleto')
    @foreach($boletos as $i => $boleto)
        @php extract($boleto); @endphp
        @if($mostrar_instrucoes)
            <div class="noprint info">
                <h2>Instruções de Impressão</h2>
                <ul>
                    <li>Imprima em impressora jato de tinta (ink jet) ou laser em qualidade normal ou alta (Não use modo
                        econômico).
                    </li>
                    <li>Utilize folha A4 (210 x 297 mm) ou Carta (216 x 279 mm) e margens mínimas à esquerda e à direita
                        do formulário.
                    </li>
                    <li>Corte na linha indicada. Não rasure, risque, fure ou dobre a região onde se encontra o código de
                        barras.
                    </li>
                    <li>Caso não apareça o código de barras no final, pressione F5 para atualizar esta tela.</li>
                    <li>Caso tenha problemas ao imprimir, copie a sequencia numérica abaixo e pague no caixa eletrônico
                        ou no internet banking:
                    </li>
                </ul>
                <span class="header">Linha Digitável: {{ $linha_digitavel }}</span>
                <span class="header">Número: {{ $numero }}</span>
                {!! $valor ? '<span class="header">Valor: R$' . $valor . '</span>' : '' !!}
                <br>
                <div class="linha-pontilhada" style="margin-bottom: 20px;">Recibo do pagador</div>
            </div>
        @endif

        <div class="info-empresa">
            @if ($logo)
                <div style="display: inline-block;">
                    <img alt="logo" src="{{ $logo_base64 }}"/>
                </div>
            @endif
            <div style="display: inline-block; vertical-align: super;">
                <div><strong>{{ $beneficiario['nome'] }}</strong></div>
                <div>{{ $beneficiario['documento'] }}</div>
                <div>{{ $beneficiario['endereco'] }}</div>
                <div>{{ $beneficiario['endereco2'] }}</div>
            </div>
        </div>
        <br>

        <table class="table-boleto" cellpadding="0" cellspacing="0" border="0">
            <tbody>
            <tr>
                <td valign="bottom" colspan="8" class="noborder nopadding">
                    <div class="logocontainer">
                        <div class="logobanco">
                            <img src="{{ isset($logo_banco_base64) && !empty($logo_banco_base64) ? $logo_banco_base64 : 'https://dummyimage.com/150x75/fff/000000.jpg&text=+' }}"
                                 alt="logo do banco">
                        </div>
                        <div class="codbanco">{{ $codigo_banco_com_dv }}</div>
                    </div>
                    <div class="linha-digitavel">{{ $linha_digitavel }}</div>
                </td>
            </tr>
            <tr>
                <td width="520" colspan="4">
                    <div class="titulo">Pagador</div>
                    <div class="conteudo">{{ $pagador['nome'] }}</div>
                </td>
                <td>
                    <div class="titulo">CPF/CNPJ do Pagador</div>
                    <div class="conteudo rtl">{{ $pagador['documento'] }}</div>
                </td>
            </tr>
            <tr>
                <td width="130">
                    <div class="titulo">Nosso Número</div>
                    <div class="conteudo">{{ $nosso_numero_boleto }}</div>
                </td>
                <td width="130">
                    <div class="titulo">Nr. Documento</div>
                    <div class="conteudo">{{ $numero_documento }}</div>
                </td>
                <td colspan="2">
                    <div class="titulo">Agência/Código do Beneficiário</div>
                    <div class="conteudo">{{ $agencia_codigo_beneficiario }}</div>
                </td>
                <td width="130" class="caixa-gray-bg">
                    <div class="titulo">Vencimento</div>
                    <div class="conteudo rtl">{{ $data_vencimento }}</div>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <div class="titulo">Beneficiário</div>
                    <div class="conteudo">{{ $beneficiario['nome'] }}</div>
                </td>
                <td colspan="2">
                    <div class="titulo">CPF/CNPJ do Beneficiário</div>
                    <div class="conteudo">{{ $beneficiario['documento'] }}</div>
                </td>
                <td width="130" class="caixa-gray-bg">
                    <div class="titulo">Valor do Documento</div>
                    <div class="conteudo rtl">{{ $valor }}</div>
                </td>
            </tr>
            <tr>
                <td colspan="4">
                    <div class="titulo">Endereço do Beneficiário</div>
                    <div class="conteudo">{{ $beneficiario['endereco'] }} | {{ $beneficiario['endereco2'] }}</div>
                </td>
                <td width="130">
                    <div class="titulo">(-) Descontos / Abatimentos</div>
                    <div class="conteudo rtl"></div>
                </td>
            </tr>
            <tr>
                <td colspan="4" rowspan="3" style="vertical-align: top;">
                    <div class="titulo"><b>Demonstrativo</b></div>
                    <div style="margin-top: 10px" class="conteudo">{{ $demonstrativo[0] }}</div>
                    <div class="conteudo">{{ $demonstrativo[1] }}</div>
                    <div class="conteudo">{{ $demonstrativo[2] }}</div>
                    <div class="conteudo">{{ $demonstrativo[3] }}</div>
                    <div style="margin-bottom: 10px;" class="conteudo">{{ $demonstrativo[4] }}</div>
                </td>
                <td width="130">
                    <div class="titulo">(-) Outras deduções</div>
                    <div class="conteudo rtl"></div>
                </td>
            </tr>
            <tr>
                <td width="130">
                    <div class="titulo">(+) Outros acréscimos</div>
                    <div class="conteudo rtl"></div>
                </td>
            </tr>
            <tr>
                <td width="130">
                    <div class="titulo">(=) Valor cobrado</div>
                    <div class="conteudo rtl"></div>
                </td>
            </tr>
            <tr>
                <td colspan="3" class="bottomborder">
                    <b>SAC CAIXA</b>: 0800 726 0101 (informações, reclamações, sugestões e elogios) <br>
                    <b>Para pessoas com deficiência auditiva ou de fala</b>: 0800 726 2492 <br>
                    <b>Ouvidoria</b>: 0800 725 7474 <br>
                    <b>caixa.gov.br</b>
                </td>
                <td width="400" colspan="2" style="vertical-align: top;" class="bottomborder">
                    <div class="titulo">
                        <center>Autenticação Mecânica - <b>Recibo do Pagador</b></center>
                    </div>
                    <div class="conteudo"></div>
                </td>
            </tr>
            </tbody>
        </table>
        <br>
        <div class="linha-pontilhada">Corte na linha pontilhada</div>
        <br>

        <!-- Ficha de compensação -->
        @include('BoletoHtmlRender::partials/ficha-compensacao')
    @endforeach
@endsection