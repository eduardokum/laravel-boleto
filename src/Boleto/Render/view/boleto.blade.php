<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>{{ $beneficiario }}</title>
    <style type="text/css">
        {!! $css !!}
    </style>
</head>
<body>

<div style="width: 666px">
    <div class="noprint info">
        <h2>Instruções de Impressão</h2>
        <ul>
            <li>Imprima em impressora jato de tinta (ink jet) ou laser em qualidade normal ou alta (Não use modo econômico).</li>
            <li>Utilize folha A4 (210 x 297 mm) ou Carta (216 x 279 mm) e margens mínimas à esquerda e à direita do formulário.</li>
            <li>Corte na linha indicada. Não rasure, risque, fure ou dobre a região onde se encontra o código de barras.</li>
            <li>Caso não apareça o código de barras no final, pressione F5 para atualizar esta tela.</li>
            <li>Caso tenha problemas ao imprimir, copie a sequencia numérica abaixo e pague no caixa eletrônico ou no internet banking:</li>
        </ul>
        <span class="header">Linha Digitável: {{ $linha_digitavel }}</span>
        {!! $valor_documento ? '<span class="header">Valor: R$' . $valor_documento . '</span>' : '' !!}
        <br>
        <div class="linha-pontilhada" style="margin-bottom: 20px;">Recibo do pagador</div>
    </div>

    <div class="info-empresa">
        @if ($logo)
        <div style="display: inline-block;">
            <img alt="logo" src="{{ $logo_base64 }}" />
        </div>
        @endif
        <div style="display: inline-block; vertical-align: super;">
            <div><strong>{{ $beneficiario }}</strong></div>
            <div>{{ $beneficiario_cpf_cnpj }}</div>
            <div>{{ $beneficiario_endereco1 }}</div>
            <div>{{ $beneficiario_endereco2 }}</div>
        </div>
    </div>
    <br>

    <table class="table-boleto" cellpadding="0" cellspacing="0" border="0">
        <tbody>
        <tr>
            <td valign="bottom" colspan="8" class="noborder nopadding">
                <div class="logocontainer">
                    <div class="logobanco">
                        <img src="{{ $logo_banco_base64 }}" alt="logo do banco">
                    </div>
                    <div class="codbanco">{{ $codigo_banco_com_dv }}</div>
                </div>
                <div class="linha-digitavel">{{ $linha_digitavel }}</div>
            </td>
        </tr>
        <tr>
            <td colspan="2" width="250" class="top-2">
                <div class="titulo">Beneficiário</div>
                <div class="conteudo">{{ $beneficiario }}</div>
            </td>
            <td class="top-2">
                <div class="titulo">CPF/CNPJ</div>
                <div class="conteudo">{{ $beneficiario_cpf_cnpj }}</div>
            </td>
            <td width="120" class="top-2">
                <div class="titulo">Ag/Cod. Beneficiário</div>
                <div class="conteudo rtl">{{ $agencia_codigo_beneficiario }}</div>
            </td>
            <td width="120" class="top-2">
                <div class="titulo">Vencimento</div>
                <div class="conteudo rtl">{{ $data_vencimento }}</div>
            </td>
        </tr>
        <tr>
            <td colspan="3">
                <div class="titulo">Pagador</div>
                <div class="conteudo">{{ $pagador_nome_documento }} </div>
            </td>
            <td>
                <div class="titulo">Nº documento</div>
                <div class="conteudo rtl">{{ $numero_documento }}</div>
            </td>
            <td>
                <div class="titulo">Nosso número</div>
                <div class="conteudo rtl">{{ $nosso_numero_boleto }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="titulo">Espécie</div>
                <div class="conteudo">{{ $especie }}</div>
            </td>
            <td>
                <div class="titulo">Quantidade</div>
                <div class="conteudo rtl">{{ $quantidade }}</div>
            </td>
            <td>
                <div class="titulo">Valor</div>
                <div class="conteudo rtl">{{ $valor_unitario }}</div>
            </td>
            <td>
                <div class="titulo">(-) Descontos / Abatimentos</div>
                <div class="conteudo rtl">{{ $desconto_abatimento }}</div>
            </td>
            <td>
                <div class="titulo">(=) Valor Documento</div>
                <div class="conteudo rtl">{{ $valor_documento }}</div>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <div class="conteudo"></div>
                <div class="titulo">Demonstrativo</div>
            </td>
            <td>
                <div class="titulo">(-) Outras deduções</div>
                <div class="conteudo">{{ $outras_deducoes }}</div>
            </td>
            <td>
                <div class="titulo">(+) Outros acréscimos</div>
                <div class="conteudo rtl">{{ $outros_acrescimos }}</div>
            </td>
            <td>
                <div class="titulo">(=) Valor cobrado</div>
                <div class="conteudo rtl">{{ $valor_cobrado }}</div>
            </td>
        </tr>
        <tr>
            <td colspan="4"><div style="margin-top: 10px" class="conteudo">{{ $demonstrativo[0] }}</div></td>
            <td class="noleftborder"><div class="titulo">Autenticação mecânica</div></td>
        </tr>
        <tr>
            <td colspan="5" class="notopborder"><div class="conteudo">{{ $demonstrativo[1] }}</div></td>
        </tr>
        <tr>
            <td colspan="5" class="notopborder"><div class="conteudo">{{ $demonstrativo[2] }}</div></td>
        </tr>
        <tr>
            <td colspan="5" class="notopborder"><div class="conteudo">{{ $demonstrativo[3] }}</div></td>
        </tr>
        <tr>
            <td colspan="5" class="notopborder bottomborder"><div style="margin-bottom: 10px;" class="conteudo">{{ $demonstrativo[4] }}</div></td>
        </tr>
        </tbody>
    </table>
    <br>
    <div class="linha-pontilhada">Corte na linha pontilhada</div>
    <br>

    <!-- Ficha de compensação -->
    @include('BoletoHtmlRender::partials/ficha-compensacao')
</div>
<script type="text/javascript">
    window.onload = function() { window.print(); }
</script>
</body>
</html>