# Como Testar Retorno de Pagamento - Banco Inter

## 📋 Pré-requisitos

1. Ter um arquivo de retorno real do Banco Inter (formato CNAB 240)
2. PHP instalado (versão compatível com o projeto)
3. Dependências do Composer instaladas

## 🚀 Forma 1: Testar com Arquivo Real

### Passo 1: Obter arquivo de retorno

Você precisa de um arquivo de retorno real do Banco Inter. Este arquivo normalmente:

-   É gerado pelo banco após o processamento de uma remessa de pagamento
-   Tem extensão `.ret` ou `.txt`
-   Segue o padrão CNAB 240
-   Tipo de serviço = `01` (Pagamento)

### Passo 2: Colocar o arquivo na pasta correta

```bash
# Copie o arquivo para a pasta de exemplos/arquivos
cp seu_arquivo_retorno.ret exemplos/arquivos/inter_pagamento.ret
```

### Passo 3: Executar o teste

```bash
cd exemplos
php teste_retorno_pagamento_inter.php
```

## 🧪 Forma 2: Criar um Arquivo Mock para Testes

Se você não tem um arquivo real, pode criar um arquivo mock básico para testar a estrutura:

### Estrutura Mínima de um Arquivo de Retorno CNAB 240 (Inter)

```
Linha 1: Header do Arquivo (Registro 0)
Linha 2: Header do Lote (Registro 1)
Linha 3: Detalhe Segmento A (Registro 3A) - Pagamento 1
Linha 4: Detalhe Segmento B (Registro 3B) - Pagamento 1
Linha 5: Detalhe Segmento A (Registro 3A) - Pagamento 2 (se houver)
Linha 6: Detalhe Segmento B (Registro 3B) - Pagamento 2 (se houver)
...
Linha N-1: Trailer do Lote (Registro 5)
Linha N: Trailer do Arquivo (Registro 9)
```

### Cada linha tem exatamente 240 caracteres!

## 🔍 O que o Teste Vai Mostrar

O script de teste irá exibir:

### 1. Header do Arquivo

-   Código do banco (077)
-   Nome do banco (BANCO INTER)
-   Dados da empresa
-   Data e hora do arquivo
-   Número sequencial

### 2. Totalizadores

-   Total de pagamentos processados
-   Quantidade de pagamentos efetivados
-   Quantidade de pagamentos rejeitados
-   Quantidade de pagamentos pendentes
-   Quantidade de pagamentos cancelados
-   Quantidade de pagamentos com erro
-   Valor total pago

### 3. Informações dos Lotes

-   Número do lote
-   Tipo de operação
-   Tipo de serviço
-   Forma de lançamento (identifica se é TED ou PIX)
-   Quantidade de registros
-   Valor total do lote

### 4. Detalhes de Cada Pagamento

**Para pagamentos bem-sucedidos:**

-   Número do documento (Seu Número)
-   Nosso número (do banco)
-   Tipo de pagamento (TED/PIX)
-   Valor solicitado e valor efetivado
-   Datas (programada e efetivação)
-   Dados do favorecido
-   Status e ocorrência

**Para pagamentos com erro:**

-   Todas as informações acima +
-   Mensagem de erro
-   Lista de rejeições (código + descrição)

## 📊 Códigos de Ocorrência Testados

### Sucesso:

-   `00` - Crédito ou Débito Efetivado
-   `BD` - Inclusão Efetuada com Sucesso

### Cancelamento:

-   `02` - Crédito ou Débito Cancelado pelo Pagador/Credor

### Erros TED/DOC:

-   `AR` - Valor do Lançamento Inválido
-   `AG` - Agência/Conta Corrente/DV Inválido
-   `ZI` - Beneficiário divergente
-   `AP` - Data Lançamento Inválido
-   `HF` - Conta Corrente da Empresa com Saldo Insuficiente
-   `AB` - Tipo de Operação Inválido
-   `AC` - Tipo de Serviço Inválido
-   `AL` - Código do Banco Favorecido Inválido
-   `AS` - Aviso ao Favorecido - Identificação Inválida
-   `HE` - Tipo de Serviço Inválido para o Contrato
-   `HA` - Lote Não Aceito

### Erros PIX:

-   `PA` - Pix não efetivado
-   `PJ` - Chave não cadastrada no DICT
-   `PM` - Chave de pagamento inválida
-   `PN` - Chave de pagamento não informada
-   `PC` - QR Code inválido/vencido
-   `PB` - Transação interrompida devido a erro no PSP do Recebedor
-   `PD` - Tipo incorreto para a conta transacional especificada
-   `PP` - Tipo de transação não suportado
-   `PH` - Ordem rejeitada pelo PSP do Recebedor
-   `PG` - CPF/CNPJ do usuário recebedor incorreto
-   `PI` - ISPB do PSP do Pagador Inválido

## 🐛 Troubleshooting

### Erro: "Arquivo não encontrado"

-   Verifique se o arquivo está na pasta `exemplos/arquivos/`
-   Verifique se o nome do arquivo é `inter_pagamento.ret`

### Erro: "Arquivo não é um arquivo de retorno válido"

-   Verifique se o arquivo tem o formato CNAB 240
-   Verifique se a primeira linha tem 240 caracteres
-   Verifique se o tipo de registro é `0` (header)

### Erro: "Banco não possui implementação"

-   Verifique se o código do banco no arquivo é `077` (Inter)
-   Verifique se as posições 1-3 do arquivo contêm `077`

### Erro: "Nenhum registro do tipo detalhe encontrado"

-   Verifique se o arquivo tem registros tipo `3` (detalhes)
-   Verifique se há segmentos A e B no arquivo

## 💡 Dicas

1. **Use um arquivo real**: Para testes mais confiáveis, sempre use um arquivo de retorno real do banco
2. **Verifique o encoding**: O arquivo deve estar em UTF-8
3. **Valide o tamanho**: Cada linha deve ter exatamente 240 caracteres
4. **Confira os códigos**: Compare os códigos de ocorrência retornados com a documentação do banco

## 📚 Próximos Passos

Após testar com sucesso:

1. Integre com sua aplicação principal
2. Implemente tratamento de erros específico do seu negócio
3. Configure notificações para pagamentos rejeitados
4. Crie logs para auditoria
5. Implemente retry logic para pagamentos com erro temporário

## 🆘 Suporte

Se encontrar problemas:

1. Verifique a documentação do Banco Inter (manual CNAB 240)
2. Confira os logs de erro completos
3. Valide o arquivo de retorno com ferramentas de validação CNAB
4. Compare com outros arquivos de retorno que funcionaram
