@extends('BoletoHtmlRender::layout')
@section('boleto')
    @foreach($boletos as $i => $boleto)
        @php extract($boleto); @endphp
        <div style="width: 863px">
    <div style="float: left">
        <table class="table-boleto" style="width: 180px" cellpadding="0" cellspacing="0" border="0">
            <tr>
                <td>
                    <div class="titulo">Vencimento</div>
                    <div class="conteudo">{{ $data_vencimento }}</div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="titulo">Ag/Cód. Beneficiário</div>
                    <div class="conteudo">{{ $agencia_codigo_beneficiario }}</div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="titulo">Nosso número</div>
                    <div class="conteudo">{{ $nosso_numero_boleto }}</div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="titulo">Nº documento</div>
                    <div class="conteudo">{{ $numero_documento }}</div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="titulo">Espécie</div>
                    <div class="conteudo">{{ $especie }}</div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="titulo">Quantidade</div>
                    <div class="conteudo"></div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="titulo">(=) Valor Documento</div>
                    <div class="conteudo">{{ $valor }}</div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="titulo">(-) Descontos / Abatimentos</div>
                    <div class="conteudo"></div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="titulo">(-) Outras deduções</div>
                    <div class="conteudo"></div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="titulo">(+) Mora / Multa</div>
                    <div class="conteudo">{{ $mora_multa }}</div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="titulo">(+) Outros acréscimos</div>
                    <div class="conteudo"></div>
                </td>
            </tr>
            <tr>
                <td class="bottomborder">
                    <div class="titulo">(=) Valor cobrado</div>
                    <div class="conteudo"></div>
                </td>
            </tr>
        </table>
        <span class="header">Recibo do Sacado</span>
    </div>
    <div style="float: left; margin-left: 15px">
        <!-- Ficha de compensação -->
        @include('BoletoHtmlRender::partials/ficha-compensacao')
    </div>
    <div style="clear: both"></div>
    <div class="linha-pontilhada">Corte na linha pontilhada</div>
</div>
    @endforeach
@endsection