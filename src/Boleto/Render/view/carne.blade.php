@extends('BoletoHtmlRender::layout')
@section('boleto')
    <style>
        .table-boleto .conteudo {
            height: 11px;
        }

        .barcode {
            height: 45px !important;
        }

        .logocontainer {
            width: 253px !important
        }

        .logobanco img {
            max-height: 30px !important;
            height: 30px !important;
        }
    </style>
    @foreach($boletos as $i => $boleto)
        @php extract($boleto, EXTR_OVERWRITE); @endphp
        <div style="width: 900px">
            <div style="float: left; margin-top: 0px; margin-right: 5px;">
                @if (isset($logo))
                    <div style="display: inline-block; width: 160px; text-align: center">
                        <img style=" margin: auto 0; width: 80px; float: left; border-right: 2px solid #000; padding-right: 3px;"
                             alt="logo" src="{{ $logo_banco_base64 }}"/>
                        <div class="codbanco"
                             style="font: 300 20px Arial; float: left; margin-left: 3px;">{{ $codigo_banco_com_dv }}</div>
                    </div>
                @endif

                <table class="table-boleto" style="width: 160px" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td>
                            <table class="table-boleto" style="width: 100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="border: none; border-right: 1px solid #000;">
                                        <div class="titulo">Parcela/Plano</div>
                                        <div class="conteudo">{{ $numero_controle }}</div>
                                    </td>
                                    <td style="border: none;">
                                        <div class="titulo">Vencimento</div>
                                        <div class="conteudo">{{ $data_vencimento->format('d/m/Y') }}</div>
                                    </td>
                                </tr>
                            </table>

                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="titulo">Ag/Cód. Beneficiário</div>
                            <div class="conteudo" style="text-align: center;">{{ $agencia_codigo_beneficiario }}</div>
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
                            <div class="conteudo" style="text-align: right;">{{ $valor }}</div>
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
                            <div class="conteudo">{{--{{ $mora_multa }}--}}</div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="titulo">(+) Outros acréscimos</div>
                            <div class="conteudo"></div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="titulo">(=) Valor cobrado</div>
                            <div class="conteudo"></div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="titulo">Nosso número</div>
                            <div class="conteudo" style="text-align: center;">{{ $nosso_numero_boleto }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="titulo">Nº documento</div>
                            <div class="conteudo">{{ $numero_documento }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td class="bottomborder">
                            <div class="titulo">Pagador</div>
                            <div class="conteudo">{{ $pagador['nome'] }}</div>
                        </td>
                    </tr>
                </table>
                <span class="header">Recibo do Sacado</span>
            </div>
            <div style="display: flex; align-items: justify-content; margin-left: 5px; max-height: 100px;">
                <!-- Ficha de compensação -->
                @include('BoletoHtmlRender::partials/ficha-compensacao')
            </div>
            <div style="clear: both"></div>
            <div class="linha-pontilhada" style="margin-top: 10px;">Corte na linha pontilhada</div>
        </div>

        @if(count($boletos) > 3 && $i > 0 && ($i+1) % 3 === 0)
            <div style="page-break-before:always"></div>
        @endif
    @endforeach
@endsection
