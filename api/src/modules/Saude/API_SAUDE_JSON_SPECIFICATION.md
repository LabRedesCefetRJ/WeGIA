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

## Criar Modelo de Parecer

### Endpoint
```
POST /saude/parecer/modelo
```

### Autenticação
**Obrigatório** - Bearer Token (JWT)

```http
Authorization: Bearer <seu_token_jwt>
```

### Descrição
Cria um novo modelo de parecer na tabela `saude_parecer_modelo` com a descrição informada e o arquivo enviado pelo cliente. O arquivo deve estar no formato `.odt` ou `.docx`.

### Content-Type
```http
Content-Type: multipart/form-data
```

### Requisição
```text
multipart/form-data
descricao: Modelo de avaliação inicial
arquivo: <arquivo.odt>
```

| Campo | Tipo | Obrigatório | Descrição |
|-------|------|-------------|-----------|
| `descricao` | string | Sim | Descrição do modelo de parecer. Deve ser preenchida e ter até 128 caracteres |
| `arquivo` | file | Sim | Arquivo do modelo no formato `.odt` ou `.docx` |

### Exemplo de requisição
```bash
curl -X POST "http://localhost:8000/saude/parecer/modelo" \
  -H "Authorization: Bearer <seu_token_jwt>" \
  -F "descricao=Modelo de avaliação inicial" \
  -F "arquivo=@/caminho/para/modelo.odt"
```

### Resposta com sucesso (201 Created)
```json
{
  "success": true,
  "message": "Modelo de parecer salvo com sucesso",
  "modelo": {
    "id": 1
  }
}
```

### Respostas de erro

#### 400 Bad Request - Arquivo inválido
```json
{
  "success": false,
  "error": "Formato do arquivo inválido. Envie um arquivo .odt ou .docx."
}
```

#### 401 Unauthorized - Sem autenticação
```json
{
  "error": "Token de autenticação não informado"
}
```

#### 500 Internal Server Error
```json
{
  "success": false,
  "error": "Descrição do erro"
}
```

---

## Observações

- A rota utiliza o mesmo padrão de autenticação da API e exige que o cliente envie um JWT válido.
- A resposta é uma lista de especialidades em formato JSON, ordenada alfabeticamente por `descricao`.
- Os registros são lidos diretamente da tabela `saude_especialidade`.
- O modelo de parecer é persistido na tabela `saude_parecer_modelo` com a descrição e o conteúdo binário do arquivo em `arquivo`, além da extensão em `extensao`.
