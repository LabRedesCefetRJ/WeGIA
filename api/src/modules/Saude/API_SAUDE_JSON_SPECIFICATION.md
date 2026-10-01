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

## Baixar Arquivo de Modelo de Parecer

### Endpoint
```
GET /saude/parecer/modelo/{id}/file
```

### Autenticação
**Obrigatório** - Bearer Token (JWT) e permissão de acesso ao módulo de Saúde.

```http
Authorization: Bearer <seu_token_jwt>
```

### Descrição
Retorna o arquivo binário associado ao modelo de parecer informado. O formato e o MIME type são definidos pela extensão armazenada (`odt` ou `docx`). A resposta é enviada como download.

### Parâmetros

| Parâmetro | Tipo | Obrigatório | Descrição |
|-----------|------|-------------|-----------|
| `id` | integer | Sim | Identificador do modelo de parecer |

### Exemplo de requisição
```bash
curl -X GET "http://localhost:8000/saude/parecer/modelo/1/file" \
  -H "Authorization: Bearer <seu_token_jwt>" \
  -o modelo-1.odt
```

### Resposta com sucesso (200 OK)
O corpo contém os bytes originais do arquivo. Os headers incluem `Content-Type`, `Content-Length` e `Content-Disposition: attachment; filename="modelo-{id}.{extensao}"`.

MIME types suportados:
- `.odt`: `application/vnd.oasis.opendocument.text`
- `.docx`: `application/vnd.openxmlformats-officedocument.wordprocessingml.document`

### Respostas de erro

#### 400 Bad Request - ID inválido
```json
{
  "error": "ID do modelo de parecer inválido."
}
```

#### 401 Unauthorized - Sem autenticação
```json
{
  "error": "Token de autenticação não informado"
}
```

#### 403 Forbidden - Sem permissão de acesso ao módulo
```json
{
  "error": "Usuário não possui permissão para acessar este módulo de saúde",
  "status": "forbidden"
}
```

#### 404 Not Found - Modelo ou arquivo indisponível
```json
{
  "error": "Arquivo do modelo de parecer não encontrado."
}
```

#### 500 Internal Server Error
```json
{
  "error": "Descrição do erro"
}
```

---

## Listar Modelos de Parecer

### Endpoint
```
GET /saude/parecer/modelo
```

### Autenticação
**Obrigatório** - Bearer Token (JWT)

```http
Authorization: Bearer <seu_token_jwt>
```

### Descrição
Retorna os registros armazenados na tabela `saude_parecer_modelo` sem incluir o conteúdo binário do arquivo. A resposta contém somente os metadados do modelo para não sobrecarregar a consulta.

### Exemplo de requisição
```bash
curl -X GET "http://localhost:8000/saude/parecer/modelo" \
  -H "Authorization: Bearer <seu_token_jwt>"
```

### Resposta com sucesso (200 OK)
```json
[
  {
    "id": 1,
    "descricao": "Modelo de avaliação inicial",
    "extensao": "odt",
    "created_at": "2026-09-30 10:00:00",
    "updated_at": "2026-09-30 10:00:00"
  },
  {
    "id": 2,
    "descricao": "Modelo de parecer final",
    "extensao": "docx",
    "created_at": "2026-09-29 15:30:00",
    "updated_at": "2026-09-29 15:30:00"
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
- A listagem de modelos retorna apenas metadados e não inclui o campo `arquivo` em `saude_parecer_modelo` para evitar payload pesado.
- Os registros são lidos diretamente da tabela `saude_parecer_modelo`; o conteúdo binário é obtido pela rota `GET /saude/parecer/modelo/{id}/file`.
