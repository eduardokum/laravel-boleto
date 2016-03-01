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
        <td colspan="7" class="top-2">
            <div class="titulo">Local de pagamento</div>
            <div class="conteudo">{{ $local_pagamento }}</div>
        </td>
        <td width="180" class="top-2">
            <div class="titulo">Vencimento</div>
            <div class="conteudo rtl">{{ $data_vencimento }}</div>
        </td>
    </tr>
    <tr>
        <td colspan="7">
            <div class="titulo">Beneficiário</div>
            <div class="conteudo">{{ $beneficiario_nome_documento }}</div>
        </td>
        <td>
            <div class="titulo">Agência/Código beneficiário</div>
            <div class="conteudo rtl">{{ $agencia_codigo_beneficiario }}</div>
        </td>
    </tr>
    <tr>
        <td width="110" colspan="2">
            <div class="titulo">Data do documento</div>
            <div class="conteudo">{{ $data_documento }}</div>
        </td>
        <td width="120" colspan="2">
            <div class="titulo">Nº documento</div>
            <div class="conteudo">{{ $numero_documento }}</div>
        </td>
        <td width="60">
            <div class="titulo">Espécie doc.</div>
            <div class="conteudo">{{ $especie_doc }}</div>
        </td>
        <td>
            <div class="titulo">Aceite</div>
            <div class="conteudo">{{ $aceite }}</div>
        </td>
        <td width="110">
            <div class="titulo">Data processamento</div>
            <div class="conteudo">{{ $data_processamento }}</div>
        </td>
        <td>
            <div class="titulo">Nosso número</div>
            <div class="conteudo rtl">{{ $nosso_numero_boleto }}</div>
        </td>
    </tr>
    <tr>
        @if(!isset($esconde_uso_banco) || !$esconde_uso_banco)
            <td {{ !isset($mostra_cip) || !$mostra_cip ? 'colspan=2' : ''}}>
                <div class="titulo">Uso do banco</div>
                <div class="conteudo">{{ $uso_banco }}</div>
            </td>
            @endif
            @if (isset($mostra_cip) && $mostra_cip)
                    <!-- Campo exclusivo do Bradesco -->
            <td width="20">
                <div class="titulo">CIP</div>
                <div class="conteudo">{{ $cip }}</div>
            </td>
        @endif

        <td {{isset($esconde_uso_banco) && $esconde_uso_banco ? 'colspan=3': '' }}>
            <div class="titulo">Carteira</div>
            <div class="conteudo">{{ $carteira }}</div>
        </td>
        <td width="35">
            <div class="titulo">Espécie</div>
            <div class="conteudo">{{ $especie }}</div>
        </td>
        <td colspan="2">
            <div class="titulo">Quantidade</div>
            <div class="conteudo">{{ $quantidade }}</div>
        </td>
        <td width="110">
            <div class="titulo">Valor</div>
            <div class="conteudo">{{ $valor_unitario }}</div>
        </td>
        <td>
            <div class="titulo">(=) Valor do Documento</div>
            <div class="conteudo rtl">{{ $valor_documento }}</div>
        </td>
    </tr>
    <tr>
        <td colspan="7">
            <div class="titulo">Instruções (Texto de responsabilidade do beneficiário)</div>
        </td>
        <td>
            <div class="titulo">(-) Descontos / Abatimentos</div>
            <div class="conteudo rtl"></div>
        </td>
    </tr>
    <tr>
        <td colspan="7" class="notopborder">
            <div class="conteudo">{{ $instrucoes[0] }}</div>
            <div class="conteudo">{{ $instrucoes[1] }}</div>
        </td>
        <td>
            <div class="titulo">(-) Outras deduções</div>
            <div class="conteudo rtl"></div>
        </td>
    </tr>
    <tr>
        <td colspan="7" class="notopborder">
            <div class="conteudo">{{ $instrucoes[2] }}</div>
            <div class="conteudo">{{ $instrucoes[3] }}</div>
        </td>
        <td>
            <div class="titulo">(+) Mora / Multa</div>
            <div class="conteudo rtl"></div>
        </td>
    </tr>
    <tr>
        <td colspan="7" class="notopborder">
            <div class="conteudo">{{ $instrucoes[4] }}</div>
            <div class="conteudo">{{ $instrucoes[5] }}</div>
        </td>
        <td>
            <div class="titulo">(+) Outros acréscimos</div>
            <div class="conteudo rtl"></div>
        </td>
    </tr>
    <tr>
        <td colspan="7" class="notopborder">
            <div class="conteudo">{{ $instrucoes[6] }}</div>
            <div class="conteudo">{{ $instrucoes[7] }}</div>
        </td>
        <td>
            <div class="titulo">(=) Valor cobrado</div>
            <div class="conteudo rtl"></div>
        </td>
    </tr>
    <tr>
        <td colspan="7">
            <div class="titulo">Pagador</div>
            <div class="conteudo">{{ $pagador_nome_documento }}</div>
            <div class="conteudo">{{ $pagador_endereco1 }}</div>
            <div class="conteudo">{{ $pagador_endereco2 }}</div>

        </td>
        <td class="noleftborder">
            <div class="titulo" style="margin-top: 50px">Cód. Baixa</div>
        </td>
    </tr>

    <tr>
        <td colspan="6" class="noleftborder">
            <div class="titulo">Sacador/Avalista
                <div class="conteudo sacador"></div>
            </div>
        </td>
        <td colspan="2" class="norightborder noleftborder">
            <div class="conteudo noborder rtl">Autenticação mecânica - Ficha de Compensação</div>
        </td>
    </tr>

    <tr>
        <td colspan="8" class="noborder">
            {!! $codigo_barras !!}
        </td>
    </tr>

    </tbody>
</table>