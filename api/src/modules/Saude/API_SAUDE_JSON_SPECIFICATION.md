# API Saúde - Especificação JSON

## Visão Geral

Documentação das endpoints e formatos JSON do módulo de Saúde na WeGIA API.

---

## Listar Especialidades

### Endpoint
```
GET /saude/especialidades
```

### Autenticação
**Obrigatório** - Bearer Token (JWT)

```http
Authorization: Bearer <seu_token_jwt>
```

### Descrição
Retorna o conjunto de especialidades cadastradas na tabela `saude_especialidade` da aplicação.

### Exemplo de requisição
```bash
curl -X GET "http://localhost:8000/saude/especialidades" \
  -H "Authorization: Bearer <seu_token_jwt>"
```

### Resposta com sucesso (200 OK)
```json
[
  {
    "id": 1,
    "descricao": "Fisioterapia"
  },
  {
    "id": 2,
    "descricao": "Fonoaudiologia"
  },
  {
    "id": 3,
    "descricao": "Nutrição"
  }
]
```

### Respostas de erro

#### 401 Unauthorized - Sem autenticação
```json
{
  "error": "Token de autenticação não informado"
}
```

#### 500 Internal Server Error
```json
{
  "error": "Descrição do erro"
}
```

---

## Observações

- A rota utiliza o mesmo padrão de autenticação da API e exige que o cliente envie um JWT válido.
- A resposta é uma lista de especialidades em formato JSON, ordenada alfabeticamente por `descricao`.
- Os registros são lidos diretamente da tabela `saude_especialidade`.
