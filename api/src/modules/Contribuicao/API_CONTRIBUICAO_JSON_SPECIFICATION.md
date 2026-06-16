# Especificação JSON - Rotas de Contribuição

Documento de referência dos JSONs esperados de requisição e resposta para as rotas do módulo de Contribuição da API WeGIA.

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
