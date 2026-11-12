# Especificação JSON - Rotas de Contribuição

Documento de referência dos JSONs esperados de requisição e resposta para as rotas do módulo de Contribuição da API WeGIA.

### Respostas de erro do servidor (5xx)

Todas as rotas retornam uma mensagem genérica para status HTTP entre 500 e 599. Esse formato prevalece sobre exemplos específicos de erro 5xx apresentados abaixo:

```json
{
  "success": false,
  "error": "Ocorreu um erro interno. Tente novamente mais tarde."
}
```

Os detalhes técnicos da falha são registrados em `api/logs/api.log` e não são enviados ao cliente.

---

## 1. GET `/socios/{id}/contribuicoes`

Retorna todas as contribuições de um sócio específico. Requer autenticação via token JWT. O usuário autenticado só pode acessar as contribuições do seu próprio sócio.

### Parâmetros
- **id** (path, obrigatório): ID do sócio
- **Authorization** (header, obrigatório): Token JWT no formato `Bearer <token>`

### Exemplos de Requisição
```
GET /socios/1/contribuicoes
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
```

### Resposta - 200 OK (Contribuições Encontradas)
```json
{
  "contribuicoes": [
    {
      "id": 1,
      "codigo": "abc123xyz456",
      "valor": 50.00,
      "dataGeracao": "2024-05-20",
      "dataVencimento": "2024-05-27",
      "dataPagamento": "2024-05-25",
      "statusPagamento": true,
      "plataforma": "PagSeguro",
      "meioPagamento": "Boleto"
    },
    {
      "id": 2,
      "codigo": "def789uvw012",
      "valor": 50.00,
      "dataGeracao": "2024-06-20",
      "dataVencimento": "2024-06-27",
      "dataPagamento": null,
      "statusPagamento": false,
      "plataforma": "PagSeguro",
      "meioPagamento": "Pix"
    }
  ],
  "resume": {
    "totalContributions": 12,
    "paidCount": 10,
    "pendingCount": 2,
    "paidTotal": 500.00,
    "pendingTotal": 100.00
  }
}
```

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `contribuicoes` | array | Array de contribuições |
| `resume` | object | Resumo das contribuições |
| `resume.totalContributions` | integer | Total de contribuições |
| `resume.paidCount` | integer | Quantidade de contribuições pagas |
| `resume.pendingCount` | integer | Quantidade de contribuições pendentes |
| `resume.paidTotal` | number | Valor total de contribuições pagas |
| `resume.pendingTotal` | number | Valor total de contribuições pendentes |

### Resposta - 200 OK (Nenhuma Contribuição Encontrada)
```json
{
  "data": [],
  "message": "Nenhuma contribuição encontrada para este sócio."
}
```

### Resposta - 400 Bad Request (ID Inválido)
```json
{
  "error": "ID do sócio inválido."
}
```

### Resposta - 401 Unauthorized (Token Não Fornecido)
```json
{
  "error": "Token inválido"
}
```

### Resposta - 403 Forbidden (Acesso Negado)
```json
{
  "error": "Acesso negado. Você não tem permissão para acessar os dados de outro sócio."
}
```

### Resposta - 500 Internal Server Error
```json
{
  "error": "Erro ao recuperar contribuições: <mensagem de erro>"
}
```

---

## 2. GET `/socios/{id}/contribuicoes/filter`

Retorna as contribuições de um sócio filtradas por status de pagamento. Requer autenticação via token JWT. O usuário autenticado só pode acessar as contribuições do seu próprio sócio.

### Parâmetros
- **id** (path, obrigatório): ID do sócio
- **status** (query, opcional): Status de pagamento (`paid` para pagas, `pending` para pendentes, omitir para todas)
- **Authorization** (header, obrigatório): Token JWT no formato `Bearer <token>`

### Exemplos de Requisição
```
GET /socios/1/contribuicoes/filter?status=paid
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
```

```
GET /socios/1/contribuicoes/filter?status=pending
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
```

### Resposta - 200 OK (Contribuições Encontradas - Status Paid)
```json
{
  "contribuicoes": [
    {
      "id": 1,
      "codigo": "abc123xyz456",
      "valor": 50.00,
      "dataGeracao": "2024-05-20",
      "dataVencimento": "2024-05-27",
      "dataPagamento": "2024-05-25",
      "statusPagamento": true,
      "plataforma": "PagSeguro",
      "meioPagamento": "Boleto"
    }
  ],
  "resume": {
    "totalContributions": 10,
    "paidCount": 10,
    "pendingCount": 0,
    "paidTotal": 500.00,
    "pendingTotal": 0.00
  }
}
```

### Resposta - 200 OK (Contribuições Encontradas - Status Pending)
```json
{
  "contribuicoes": [
    {
      "id": 2,
      "codigo": "def789uvw012",
      "valor": 50.00,
      "dataGeracao": "2024-06-20",
      "dataVencimento": "2024-06-27",
      "dataPagamento": null,
      "statusPagamento": false,
      "plataforma": "PagSeguro",
      "meioPagamento": "Pix"
    }
  ],
  "resume": {
    "totalContributions": 2,
    "paidCount": 0,
    "pendingCount": 2,
    "paidTotal": 0.00,
    "pendingTotal": 100.00
  }
}
```

### Resposta - 200 OK (Nenhuma Contribuição Encontrada)
```json
{
  "data": [],
  "message": "Nenhuma contribuição encontrada com os filtros especificados."
}
```

### Resposta - 400 Bad Request (ID Inválido)
```json
{
  "error": "ID do sócio inválido."
}
```

### Resposta - 401 Unauthorized (Token Não Fornecido)
```json
{
  "error": "Token inválido"
}
```

### Resposta - 403 Forbidden (Acesso Negado)
```json
{
  "error": "Acesso negado. Você não tem permissão para acessar os dados de outro sócio."
}
```

### Resposta - 500 Internal Server Error
```json
{
  "error": "Erro ao recuperar contribuições: <mensagem de erro>"
}
```

---

## 3. GET `/socios/{id}/contribuicoes/resume`

Retorna apenas o resumo das contribuições de um sócio (totais e subtotais por status). Requer autenticação via token JWT. O usuário autenticado só pode acessar o resumo do seu próprio sócio.

### Parâmetros
- **id** (path, obrigatório): ID do sócio
- **Authorization** (header, obrigatório): Token JWT no formato `Bearer <token>`

### Exemplos de Requisição
```
GET /socios/1/contribuicoes/resume
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
```

### Resposta - 200 OK (Resumo Encontrado)
```json
{
  "resume": {
    "totalContributions": 12,
    "paidCount": 10,
    "pendingCount": 2,
    "paidTotal": 500.00,
    "pendingTotal": 100.00
  }
}
```

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `resume` | object | Resumo das contribuições |
| `resume.totalContributions` | integer | Total de contribuições |
| `resume.paidCount` | integer | Quantidade de contribuições pagas |
| `resume.pendingCount` | integer | Quantidade de contribuições pendentes |
| `resume.paidTotal` | number | Valor total de contribuições pagas |
| `resume.pendingTotal` | number | Valor total de contribuições pendentes |

### Resposta - 200 OK (Nenhuma Contribuição - Resumo Zerado)
```json
{
  "resume": {
    "totalContributions": 0,
    "paidCount": 0,
    "pendingCount": 0,
    "paidTotal": 0,
    "pendingTotal": 0
  }
}
```

### Resposta - 400 Bad Request (ID Inválido)
```json
{
  "error": "ID do sócio inválido."
}
```

### Resposta - 401 Unauthorized (Token Não Fornecido)
```json
{
  "error": "Token inválido"
}
```

### Resposta - 403 Forbidden (Acesso Negado)
```json
{
  "error": "Acesso negado. Você não tem permissão para acessar os dados de outro sócio."
}
```

### Resposta - 500 Internal Server Error
```json
{
  "error": "Erro ao recuperar resumo de contribuições: <mensagem de erro>"
}
```

---

## 4. GET `/socios/{id}/contribuicoes/pdf`

Gera e retorna um arquivo PDF com o extrato das contribuições de um sócio específico. Requer autenticação via token JWT. O usuário autenticado só pode gerar o extrato do seu próprio sócio.

### Parâmetros
- **id** (path, obrigatório): ID do sócio
**data_pagamento_inicial** (query, opcional): Data de pagamento inicial (`YYYY-mm-dd`)
**data_pagamento_final** (query, opcional): Data de pagamento final (`YYYY-mm-dd`)
- **Authorization** (header, obrigatório): Token JWT no formato `Bearer <token>`

### Exemplos de Requisição
```
GET /socios/1/contribuicoes/pdf
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
```

### Resposta - 200 OK
- **Content-Type:** `application/pdf`
- **Content-Disposition:** `attachment; filename="extrato_contribuicoes_socio_1.pdf"`

O corpo da resposta contém o arquivo PDF pronto para download.

### Resposta - 400 Bad Request (ID Inválido)
```json
{
  "error": "ID do sócio inválido."
}
```

### Resposta - 401 Unauthorized (Token Não Fornecido)
```json
{
  "error": "Token inválido"
}
```

### Resposta - 403 Forbidden (Acesso Negado)
```json
{
  "error": "Acesso negado. Você não tem permissão para acessar os dados de outro sócio."
}
```

### Resposta - 404 Not Found (Sem Contribuições)
```json
{
  "error": "Nenhuma contribuição encontrada para este sócio."
}
```

### Resposta - 500 Internal Server Error
```json
{
  "error": "Erro ao gerar PDF do extrato: <mensagem de erro>"
}
```

---

## 5. GET `/socios/{id}/contribuicoes/{contribuicao_id}/pdf`

Gera e retorna um arquivo PDF com o comprovante de uma contribuição específica do sócio. Requer autenticação via token JWT. O usuário autenticado só pode gerar o comprovante da própria contribuição vinculada ao seu sócio.

### Parâmetros
- **id** (path, obrigatório): ID do sócio
- **contribuicao_id** (path, obrigatório): ID da contribuição
- **Authorization** (header, obrigatório): Token JWT no formato `Bearer <token>`

### Exemplos de Requisição
```
GET /socios/1/contribuicoes/10/pdf
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
```

### Resposta - 200 OK
- **Content-Type:** `application/pdf`
- **Content-Disposition:** `attachment; filename="comprovante_contribuicao_10.pdf"`

O corpo da resposta contém o arquivo PDF pronto para download.

### Resposta - 400 Bad Request (IDs Inválidos)
```json
{
  "error": "ID do sócio ou da contribuição inválido."
}
```

### Resposta - 401 Unauthorized (Token Não Fornecido)
```json
{
  "error": "Token inválido"
}
```

### Resposta - 403 Forbidden (Acesso Negado)
```json
{
  "error": "Acesso negado. Você não tem permissão para acessar os dados de outro sócio."
}
```

### Resposta - 404 Not Found (Contribuição Não Encontrada)
```json
{
  "error": "Contribuição não encontrada para este sócio."
}
```

### Resposta - 500 Internal Server Error
```json
{
  "error": "Erro ao gerar PDF do comprovante: <mensagem de erro>"
}
```

---

## Estrutura de Dados - Contribuição

Cada contribuição retornada possui a seguinte estrutura:

```json
{
  "id": 1,
  "codigo": "abc123xyz456",
  "valor": 50.00,
  "dataGeracao": "2024-05-20",
  "dataVencimento": "2024-05-27",
  "dataPagamento": "2024-05-25",
  "statusPagamento": true,
  "plataforma": "PagSeguro",
  "meioPagamento": "Boleto"
}
```

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `id` | integer | ID único da contribuição |
| `codigo` | string | Código único da contribuição |
| `valor` | number | Valor da contribuição em reais |
| `dataGeracao` | string | Data de geração da contribuição (formato: YYYY-MM-DD) |
| `dataVencimento` | string | Data de vencimento da contribuição (formato: YYYY-MM-DD) |
| `dataPagamento` | string\|null | Data de pagamento da contribuição (formato: YYYY-MM-DD) ou null se não pago |
| `statusPagamento` | boolean | Status do pagamento (true = pago, false = pendente) |
| `plataforma` | string\|null | Plataforma de pagamento (ex: PagSeguro, PagarMe) ou null |
| `meioPagamento` | string\|null | Meio de pagamento (ex: Boleto, Pix, Cartão) ou null |

---

## 6. GET `/contribuicoes/{contribuicao_id}/pdf`

Retorna o PDF de uma contribuição pelo UUIDv7 da contribuição. Requer autenticação via token JWT. Para compatibilidade, valores que não sejam UUIDv7 são tratados como nomes de arquivos históricos em `/web/html/contribuicao/pdfs`; nesse caso, o CPF extraído do nome do arquivo deve corresponder ao CPF da pessoa autenticada, após normalização e formatação.

### Parâmetros
- **contribuicao_id** (path, obrigatório): UUIDv7 textual da contribuição; como fallback retrocompatível, nome do arquivo PDF no formato `codigo_cpf_data_valor.pdf`
- **Authorization** (header, obrigatório): Token JWT no formato `Bearer <token>`

### Exemplo de Requisição
```
GET /contribuicoes/0190f5c2-7b00-7abc-8def-0123456789ab/pdf
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
```

O fallback por arquivo histórico continua aceitando, por exemplo, `GET /contribuicoes/or-abc123_12345678900_20260706_50.pdf`.

### Resposta - 200 OK
- **Content-Type:** `application/pdf`
- **Content-Disposition:** `attachment; filename="<nome_do_arquivo>.pdf"`

### Resposta - 400 Bad Request (Identificador Inválido)
```json
{
  "error": "Identificador da contribuição inválido."
}
```

### Resposta - 401 Unauthorized (Token Não Fornecido)
```json
{
  "error": "Token inválido"
}
```

### Resposta - 403 Forbidden (Acesso Negado)
```json
{
  "error": "Acesso negado. Você não tem permissão para acessar este arquivo."
}
```

### Resposta - 404 Not Found (Arquivo Não Encontrado)
```json
{
  "error": "Arquivo PDF não encontrado."
}
```

### Resposta - 500 Internal Server Error
```json
{
  "error": "Erro ao recuperar o PDF da contribuição: <mensagem de erro>"
}
```

---

## 4. POST `/contribuicoes/boleto`

Gera um boleto de contribuição para o sócio associado ao usuário autenticado. O `user_id` obtido da autenticação localiza o sócio; o serviço e as regras de valor dependem da configuração do meio de pagamento.

### Autenticação e cabeçalhos
- Envie `Authorization: Bearer <JWT>` ou autentique-se pelo cookie `access_token`.
- **Content-Type**: `application/json`.

### Corpo da Requisição
```json
{
  "valor": 50.0
}
```

| Campo | Tipo | Obrigatório | Descrição |
|-------|------|-------------|-----------|
| `valor` | number | sim | Deve ser numérico e maior que zero. Também deve respeitar os limites mínimo e máximo configurados para o meio de pagamento, quando definidos. |
| `dia` | string | não | Data de vencimento esperada no formato `YYYY-MM-DD`; omitida ou vazia, a API usa a data atual mais 7 dias. |

> **Comportamento atual:** no PHP 8.2 ou superior, `DateTimeImmutable::getLastErrors()` retorna `false` para uma data válida sem avisos. Como o handler trata esse retorno como erro, o envio de `dia` resulta em `400 Data de vencimento inválida.`; omita o campo para usar o vencimento padrão.

### Resposta - 201 Created
```json
{
  "link": "https://link-do-boleto-retornado-pelo-gateway",
  "codigo": "codigo-retornado-pelo-gateway",
  "contribuicao_id": "0190f5c2-7b00-7abc-8def-0123456789ab"
}
```

### Resposta - 400 Bad Request
```json
{
  "error": "Valor inválido."
}
```
Também pode retornar uma mensagem informando que o valor está abaixo ou acima do limite configurado, ou `Data de vencimento inválida.` quando `dia` for informado.

### Resposta - 401 Unauthorized
```json
{
  "error": "Token inválido"
}
```
O handler também retorna `Usuário não identificado.` se não houver `user_id` autenticado.

### Resposta - 404 Not Found
```json
{
  "error": "Sócio não encontrado para o usuário autenticado."
}
```
Também pode retornar `Sócio não encontrado.`, `Meio de pagamento não encontrado.` ou `Gateway de pagamento não encontrado.`.

### Resposta - 502 Bad Gateway
```json
{
  "error": "Não foi possível gerar o boleto."
}
```
### Resposta - 500 Internal Server Error
Ocorre se o arquivo ou a classe do serviço de pagamento não estiver disponível, ou se houver outra exceção. O corpo contém `error`, com mensagens como `Arquivo de serviço de pagamento não encontrado.`, `Classe de serviço de pagamento não encontrada.` ou `Erro ao gerar boleto: <mensagem>`.

---

## 5. POST `/contribuicoes/carne`

Gera um carnê de contribuição para o sócio associado ao usuário autenticado. O valor informado é o valor de cada parcela; as regras de valor dependem da configuração do meio de pagamento.

### Autenticação e cabeçalhos
- Envie `Authorization: Bearer <JWT>` ou autentique-se pelo cookie `access_token`.
- **Content-Type**: `application/json`.

### Corpo da Requisição
```json
{
  "valor": 50.0,
  "parcelas": 6,
  "dia": 10,
  "tipoGeracao": 1
}
```

| Campo | Tipo | Obrigatório | Descrição |
|-------|------|-------------|-----------|
| `valor` | number | sim | Valor de cada parcela em reais. Deve ser numérico, maior que zero e respeitar os limites mínimo e máximo configurados, quando definidos. |
| `parcelas` | integer | sim | Quantidade de parcelas, entre 2 e 12 |
| `dia` | integer | sim | Dia de vencimento. Valores aceitos: 1, 5, 10, 15, 20 e 25 |
| `tipoGeracao` | integer | não | Intervalo entre parcelas em meses: 1, 2, 3 ou 6. Se omitido ou vazio, usa 1 mês. |

As parcelas começam no mês atual se o dia informado for maior que o dia atual do mês; caso contrário, começam no mês seguinte. As parcelas seguintes respeitam o intervalo indicado por `tipoGeracao`.

### Resposta - 201 Created
```json
{
  "link": "https://<base-da-aplicacao>/html/contribuicao/controller/control.php?nomeClasse=ContribuicaoLogController&metodo=downloadPdfPorId&id=0190f5c2-7b00-7abc-8def-0123456789ab",
  "parcelas": 6,
  "contribuicao_id": "0190f5c2-7b00-7abc-8def-0123456789ab"
}
```
`contribuicao_id` identifica a primeira contribuição do carnê e é também o UUID usado no link.

### Resposta - 400 Bad Request
```json
{
  "error": "A quantidade de parcelas deve ser um número entre 2 e 12."
}
```
Outras mensagens de validação possíveis: `Valor inválido.`, `Dia de vencimento inválido.`, `O tipo de geração é inválido.` e mensagens de valor abaixo/acima dos limites configurados.

### Resposta - 401 Unauthorized
```json
{
  "error": "Token inválido"
}
```
O handler também retorna `Usuário não identificado.` se não houver `user_id` autenticado.

### Resposta - 404 Not Found
```json
{
  "error": "Sócio não encontrado para o usuário autenticado."
}
```
Também pode retornar `Sócio não encontrado.`, `Meio de pagamento não encontrado.` ou `Gateway de pagamento não encontrado.`.

### Resposta - 502 Bad Gateway
```json
{
  "error": "Não foi possível gerar o carnê."
}
```
### Resposta - 500 Internal Server Error
Pode ocorrer se o arquivo ou a classe do serviço não estiver disponível, se não for possível preparar o carnê ou se houver outra exceção. O corpo contém `error`, com mensagens como `Não foi possível preparar o carnê.` ou `Erro ao gerar carnê: <mensagem>`.

---

## 6. POST `/contribuicoes/pix`

Gera uma cobrança Pix para o sócio associado ao usuário autenticado. As regras de valor e o conteúdo do QR Code dependem da configuração do meio de pagamento e do serviço de gateway utilizado.

### Autenticação e cabeçalhos
- Envie `Authorization: Bearer <JWT>` ou autentique-se pelo cookie `access_token`.
- **Content-Type**: `application/json`.

### Corpo da Requisição
```json
{
  "valor": 50.0
}
```

| Campo | Tipo | Obrigatório | Descrição |
|-------|------|-------------|-----------|
| `valor` | number | sim | Valor em reais. Deve ser numérico, maior que zero e respeitar os limites mínimo e máximo configurados, quando definidos. |

### Resposta - 201 Created
```json
{
  "qrcode": "conteúdo do QR Code retornado pelo serviço do gateway",
  "copiaCola": "texto-pix-copia-e-cola",
  "codigo": "codigo-retornado-pelo-gateway",
  "contribuicao_id": "0190f5c2-7b00-7abc-8def-0123456789ab"
}
```
O formato de `qrcode` depende do gateway configurado (por exemplo, pode ser uma imagem codificada em Base64). `codigo` é o identificador retornado pelo gateway.

### Resposta - 400 Bad Request
```json
{
  "error": "Valor inválido."
}
```
Também pode informar que o valor está abaixo ou acima dos limites configurados.

### Resposta - 401 Unauthorized
```json
{
  "error": "Token inválido"
}
```
O handler também retorna `Usuário não identificado.` se não houver `user_id` autenticado.

### Resposta - 404 Not Found
```json
{
  "error": "Sócio não encontrado para o usuário autenticado."
}
```
Também pode retornar `Sócio não encontrado.`, `Meio de pagamento não encontrado.` ou `Gateway de pagamento não encontrado.`.

### Resposta - 502 Bad Gateway
```json
{
  "error": "Não foi possível gerar o Pix."
}
```
### Resposta - 500 Internal Server Error
Ocorre se o arquivo ou a classe do serviço de pagamento não estiver disponível, ou se houver outra exceção. O corpo contém `error`, com mensagens como `Arquivo de serviço de pagamento não encontrado.`, `Classe de serviço de pagamento não encontrada.` ou `Erro ao gerar Pix: <mensagem>`.

---

## 7. POST `/contribuicoes/credito`

Processa um pagamento com cartão de crédito para o sócio associado ao usuário autenticado. Envie o token de cartão já criado pelo fluxo de tokenização do gateway; o backend não recebe os dados completos do cartão. As regras de valor dependem da configuração do meio de pagamento.

### Autenticação e cabeçalhos
- Envie `Authorization: Bearer <JWT>` ou autentique-se pelo cookie `access_token`.
- **Content-Type**: `application/json`.

### Corpo da Requisição
```json
{
  "valor": 50.0,
  "card_token": "token-de-cartao-retornado-pelo-gateway"
}
```

| Campo | Tipo | Obrigatório | Descrição |
|-------|------|-------------|-----------|
| `valor` | number | sim | Valor em reais. Deve ser numérico, maior que zero e respeitar os limites mínimo e máximo configurados, quando definidos. |
| `card_token` | string | sim | Token de cartão não vazio, previamente obtido no gateway. |

### Resposta - 201 Created
```json
{
  "sucesso": true,
  "mensagem": "Pagamento processado com sucesso!",
  "transacao_id": "tx_123",
  "contribuicao_id": "0190f5c2-7b00-7abc-8def-0123456789ab"
}
```

### Resposta - 400 Bad Request
```json
{
  "error": "Os dados do cartão são obrigatórios."
}
```
Também pode retornar `Valor inválido.` ou uma mensagem de valor abaixo/acima dos limites configurados. O código verifica se `card_token` está presente e não vazio; recusas do gateway não são descritas como erro de validação do número do cartão.

### Resposta - 401 Unauthorized
```json
{
  "error": "Token inválido"
}
```
O handler também retorna `Usuário não identificado.` se não houver `user_id` autenticado.

### Resposta - 404 Not Found
```json
{
  "error": "Sócio não encontrado para o usuário autenticado."
}
```
Também pode retornar `Sócio não encontrado.`, `Meio de pagamento não encontrado.` ou `Gateway de pagamento não encontrado.`.

### Resposta - 502 Bad Gateway
```json
{
  "error": "Não foi possível processar o cartão de crédito."
}
```
Este status indica que o serviço não retornou o identificador da transação.
### Resposta - 500 Internal Server Error
Ocorre se o arquivo ou a classe do serviço de pagamento não estiver disponível, ou se houver outra exceção. O corpo contém `error`, com mensagens como `Arquivo de serviço de pagamento não encontrado.`, `Classe de serviço de pagamento não encontrada.` ou `Erro ao processar cartão de crédito: <mensagem>`.

---

## 8. POST `/contribuicoes/recorrencia`

Cria uma assinatura mensal para o sócio associado ao usuário autenticado. Envie o token de cartão já criado pelo fluxo de tokenização do gateway; o backend não recebe os dados completos do cartão. As regras de valor dependem da configuração do meio de pagamento.

### Autenticação e cabeçalhos
- Envie `Authorization: Bearer <JWT>` ou autentique-se pelo cookie `access_token`.
- **Content-Type**: `application/json`.

### Corpo da Requisição
```json
{
  "valor": 50.0,
  "card_token": "token-de-cartao-retornado-pelo-gateway"
}
```

| Campo | Tipo | Obrigatório | Descrição |
|-------|------|-------------|-----------|
| `valor` | number | sim | Valor mensal em reais. Deve ser numérico, maior que zero e respeitar os limites mínimo e máximo configurados, quando definidos. |
| `card_token` | string | sim | Token de cartão não vazio, previamente obtido no gateway. |

### Resposta - 201 Created
```json
{
  "sucesso": true,
  "mensagem": "Assinatura criada com sucesso! Cobranças mensais no dia <dia atual>.",
  "assinatura_id": "sub_123",
  "recorrencia_id": 123
}
```
O dia de cobrança informado na mensagem é o dia do mês em que a assinatura foi criada; não é um dia fixo configurado na API.

### Resposta - 400 Bad Request
```json
{
  "error": "Os dados do cartão são obrigatórios."
}
```
Também pode retornar `Valor inválido.`, `Meio de pagamento indisponível.` ou uma mensagem de valor abaixo/acima dos limites configurados. O código verifica se `card_token` está presente e não vazio.

### Resposta - 401 Unauthorized
```json
{
  "error": "Token inválido"
}
```
O handler também retorna `Usuário não identificado.` se não houver `user_id` autenticado.

### Resposta - 404 Not Found
```json
{
  "error": "Sócio não encontrado para o usuário autenticado."
}
```
Também pode retornar `Sócio não encontrado.`, `Meio de pagamento não encontrado.` ou `Gateway de pagamento não encontrado.`.

### Resposta - 502 Bad Gateway
```json
{
  "error": "Não foi possível criar a assinatura."
}
```
Este status indica que o serviço não retornou o identificador da assinatura.

### Resposta - 500 Internal Server Error
Pode ocorrer se o arquivo ou a classe do serviço não estiver disponível, se não for possível criar o registro da recorrência ou se houver outra exceção. O corpo contém `error`, com mensagens como `Arquivo de serviço de pagamento não encontrado.`, `Classe de serviço de pagamento não encontrada.`, `Não foi possível criar a recorrência.` ou `Erro ao criar assinatura: <mensagem>`.

## GET /contribuicoes/payments_rules

- Description: Retorna todas as formas de pagamento com suas regras (description, value).
- Method: `GET`
- Path: `/contribuicoes/payments_rules`
- Authentication: Bearer token (via middleware de autenticação)

### Success Response (200)
Content-Type: `application/json`

Example:

```JSON
{
    "payment_methods": {
        "boleto": {
            "rules": [
                {
                    "description": "max_value",
                    "value": 1000
                }
            ]
        },
        "pix": {
            "rules": [
                {
                    "description": "min_value",
                    "value": 10
                }
            ]
        },
        "cartao_credito": {
            "rules": [
                {
                    "description": "min_value",
                    "value": 1
                }
            ]
        }
    }
}
```

### Error Response (500)
Content-Type: `application/json`

Example:

```JSON
{
    "error": "Erro ao buscar regras de pagamento: <mensagem>"
}
```

Notes:
- `description` values are normalized to `snake_case` (ex: `max_value`, `min_value`).
- Endpoint implemented in `api/src/modules/Contribuicao/PaymentController.php`.

## GET /contribuicoes/payments_gateway/{payment_method}

- Description: Retorna os dados públicos da plataforma de pagamento associada ao meio de pagamento informado.
- Method: `GET`
- Path: `/contribuicoes/payments_gateway/{payment_method}`
- Authentication: Bearer token (via middleware de autenticação)

### Parâmetros
- `payment_method` (path, obrigatório): Meio de pagamento cadastrado no sistema, como `Boleto`, `Pix`, `Carne`, `Recorrencia` ou `CartaoCredito`

### Success Response (200)
Content-Type: `application/json`

Example:

```JSON
{
    "payment_gateway": {
        "id": 1,
        "description": "PagSeguro",
        "endpoint": "https://api.pagseguro.com",
        "publicToken": "pub_123456789",
        "status": true
    }
}
```

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `payment_gateway` | object | Dados públicos da plataforma de pagamento encontrada |
| `payment_gateway.id` | integer \| null | ID da plataforma de pagamento |
| `payment_gateway.description` | string | Nome/descrição da plataforma |
| `payment_gateway.endpoint` | string | Endpoint da plataforma de pagamento |
| `payment_gateway.publicToken` | string | Token público exposto para integração |
| `payment_gateway.status` | boolean | Status da plataforma de pagamento |

### Resposta - 401 Unauthorized
Content-Type: `application/json`

Example:

```JSON
{
    "error": "Token inválido"
}
```

### Resposta - 500 Internal Server Error
Content-Type: `application/json`

Example:

```JSON
{
    "error": "Erro ao buscar token público: No payment gateway found for the specified payment method."
}
```

Notes:
- A rota é protegida pelo `AuthMiddleware`, então a ausência ou invalidez do token resulta em `401`.
- O controller retorna `500` para qualquer exceção ao buscar a plataforma de pagamento, incluindo quando não há gateway ativo para o meio informado.
- Endpoint implementado em `api/src/modules/Contribuicao/PaymentController.php`.


---

## GET `/contribuicoes/payment_methods`

Retorna os meios de pagamento ativos disponíveis para registros manuais. Requer autenticação via token JWT.

### Resposta - 200 OK
```json
{
  "payment_methods": [
    {
      "id": 1,
      "meio": "Dinheiro"
    }
  ]
}
```

### Campos

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `payment_methods[].id` | integer | ID usado no campo `id_meio_pagamento` |
| `payment_methods[].meio` | string | Nome do meio de pagamento |

## 4. POST `/contribuicoes/import`

Importa contribuições históricas a partir de uma planilha. Requer autenticação via JWT e permissão de funcionário para o recurso de sócios.

### Requisição

`Content-Type: multipart/form-data`

| Campo | Obrigatório | Descrição |
|-------|-------------|-----------|
| `arquivo` | Sim | Arquivo `.xlsx`, `.ods` ou `.csv` |
| `ano` | Sim | Ano de referência das colunas mensais |
| `modelo` | Não | Identificador do serviço; padrão `amigos_laje_xlsx` |
| `id_meio_pagamento` | Não | ID do meio de pagamento ou vazio para `NULL` |

O modelo `amigos_laje_xlsx` interpreta as abas `BRADESCO`, `CAIXA`, `ITAÚ` e `TESOURARIA`. A aba `Modelo` é ignorada. As colunas devem conter `NOME`, `VALOR` e os meses de `JAN` a `DEZ`; somente células mensais com dia numérico geram contribuições pagas. Células `X`, vazias ou não numéricas são ignoradas.

O serviço XLSX está disponível nesta versão. ODS e CSV são formatos reconhecidos pela rota, mas retornam `415 Unsupported Media Type` até que um serviço específico seja registrado.

### Exemplo

```bash
curl -X POST https://example.test/contribuicoes/import \
  -H "Authorization: Bearer <access_token>" \
  -F "arquivo=@Amigos do LAJE.xlsx" \
  -F "ano=2026" \
  -F "modelo=amigos_laje_xlsx"
```

### Resposta - 200 OK

```json
{
  "sucesso": true,
  "mensagem": "Importação de contribuições processada com sucesso.",
  "resultado": {
    "importados": 12,
    "duplicados": 3,
    "rejeitados": []
  }
}
```

Duplicatas são identificadas por sócio, valor e data de pagamento e são ignoradas. Linhas rejeitadas são retornadas com aba, linha, mês quando aplicável e motivo.

### Erros

- `400 Bad Request`: arquivo ausente, ano/modelo inválido, planilha incompatível ou dados inválidos;
- `401 Unauthorized`: token ausente ou inválido;
- `403 Forbidden`: usuário sem permissão de funcionário;
- `415 Unsupported Media Type`: extensão ou tipo MIME inválido, ou ODS/CSV ainda não implementado;
- `500 Internal Server Error`: falha inesperada durante a leitura ou persistência.

## 5. POST `/contribuicoes/manual`

Registra manualmente o pagamento de uma contribuição. Requer autenticação via token JWT. Na aplicação web, o JWT é enviado pelo cookie `access_token` usando `credentials: include`; clientes que não utilizam o fluxo web devem enviá-lo no cabeçalho `Authorization` como `Bearer <token>`.

### Headers
- **Content-Type** (obrigatório): `application/json`
- **Cookie `access_token`** (obrigatório no cliente web): Token JWT mantido pelo fluxo de autenticação web
- **X-Client-Type** (obrigatório no cliente web): `web`
- **Authorization** (obrigatório para clientes que não usam o fluxo web): Token JWT no formato `Bearer <token>`

### Exemplo de Requisição
```
POST /contribuicoes/manual
Content-Type: application/json
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
```

```json
{
  "id_socio": 1,
  "id_meio_pagamento": 1,
  "valor": 50.00,
  "data_pagamento": "2024-05-25 10:00:00",
  "data_vencimento": "2024-05-27 00:00:00",
  "data_geracao": "2024-05-20 00:00:00",
  "status": "paid"
}
```

### Payload

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `id_socio` | integer | ID do sócio associado à contribuição |
| `id_meio_pagamento` | integer \| null | ID do meio de pagamento ativo associado à contribuição, ou `null` para contribuição sem meio cadastrado |
| `valor` | number | Valor da contribuição |
| `data_pagamento` | string | Data e hora do pagamento |
| `data_vencimento` | string | Data e hora do vencimento |
| `data_geracao` | string | Data e hora da geração |
| `status` | string | Status do pagamento |

### Resposta - 201 Created
```json
{
  "sucesso": true,
  "mensagem": "Pagamento manual registrado com sucesso!"
}
```

### Resposta - 500 Internal Server Error
```json
{
  "error": "Erro ao registrar pagamento manual: <mensagem de erro> em <arquivo> na linha <linha>"
}
```

---

## Observações Gerais

### Headers Recomendados
Todas as requisições devem incluir o header:
```
Content-Type: application/json
Authorization: Bearer <access_token>
```

### Autenticação
Todas as rotas requerem autenticação via token JWT no header:
```
Authorization: Bearer <access_token>
```

O usuário autenticado só pode acessar as contribuições do seu próprio sócio. Tentativas de acessar contribuições de outros sócios resultarão em erro **403 Forbidden**.

### Validação de Segurança
Antes de retornar qualquer dado, o sistema valida que:
1. O token JWT é válido
2. O `id_pessoa` extraído do token corresponde ao `id_pessoa` do sócio solicitado

### Filtros Disponíveis
Na rota `/socios/{id}/contribuicoes/filter`, o parâmetro `status` aceita os seguintes valores:
- `paid` - Retorna apenas contribuições pagas (statusPagamento = true)
- `pending` - Retorna apenas contribuições pendentes (statusPagamento = false)
- Omitido - Retorna todas as contribuições

### Ordenação
As contribuições são sempre retornadas ordenadas por data de geração em ordem decrescente (mais recentes primeiro).

### Erros Comuns

#### 401 Unauthorized
Ocorre quando:
- Token JWT não foi fornecido
- Token JWT é inválido ou expirado
- Token JWT não segue o formato `Bearer <token>`

#### 403 Forbidden
Ocorre quando:
- O usuário autenticado tenta acessar contribuições de outro sócio
- O sócio solicitado não pertence ao usuário autenticado

#### 404 Not Found
Ocorre quando:
- O sócio não existe (na rota GET `/socios/{cpf}`)
- A contribuição não existe

#### 500 Internal Server Error
Ocorre quando:
- Erro no banco de dados
- Erro na lógica de processamento
- Erro não tratado na aplicação
