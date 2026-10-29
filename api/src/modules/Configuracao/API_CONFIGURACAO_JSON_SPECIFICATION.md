# API Configuração - Especificação JSON

## Visão Geral

Documentação das endpoints e formatos JSON do módulo de Configuração da WeGIA API, incluindo o cadastro e consulta de modelos de documento.

---

## Listar Modelos de Documento

### Endpoint
```
GET /configuracao/documento/modelo
```

### Autenticação
**Obrigatório** - ****** (JWT)

```http
Authorization: ******
```

### Descrição
Retorna os registros armazenados na tabela `modelo_documento` sem incluir o conteúdo binário da coluna `documento`.

### Exemplo de requisição
```bash
curl -X GET "http://localhost:8000/configuracao/documento/modelo" \
  -H "Authorization: ******"
```

### Resposta com sucesso (200 OK)
```json
[
  {
    "id": 1,
    "descricao": "Modelo de avaliação inicial",
    "extensao": "odt",
    "created_at": "2026-09-30 10:00:00",
    "updated_at": "2026-09-30 10:00:00",
    "global": 0
  }
]
```

---

## Baixar Arquivo de Modelo de Documento

### Endpoint
```
GET /configuracao/documento/modelo/{id}/file
```

### Autenticação
**Obrigatório** - ****** (JWT)

### Descrição
Retorna o arquivo binário associado ao modelo de documento informado na tabela `modelo_documento`.

### Exemplo de requisição
```bash
curl -X GET "http://localhost:8000/configuracao/documento/modelo/1/file" \
  -H "Authorization: ******" \
  -o modelo-1.odt
```

---

## Criar Modelo de Documento

### Endpoint
```
POST /configuracao/documento/modelo
```

### Autenticação
**Obrigatório** - ****** (JWT)

### Content-Type
```http
Content-Type: multipart/form-data
```

### Descrição
Cria um novo modelo de documento na tabela `modelo_documento` com a descrição informada, o arquivo enviado e o funcionário responsável pelo cadastro.

### Requisição
```text
multipart/form-data
descricao: Modelo de avaliação inicial
arquivo: <arquivo.odt>
global: true
```

| Campo | Tipo | Obrigatório | Descrição |
|-------|------|-------------|-----------|
| `descricao` | string | Sim | Descrição do modelo de documento. |
| `arquivo` | file | Sim | Arquivo do modelo no formato `.odt` ou `.docx`. |
| `global` | boolean | Não | Só pode ser informado por administradores. Quando `true`, disponibiliza o modelo para todos os usuários do sistema. |

### Exemplo de requisição
```bash
curl -X POST "http://localhost:8000/configuracao/documento/modelo" \
  -H "Authorization: ******" \
  -F "descricao=Modelo de avaliação inicial" \
  -F "arquivo=@/caminho/para/modelo.odt" \
  -F "global=true"
```

### Resposta com sucesso (201 Created)
```json
{
  "success": true,
  "message": "Modelo de documento salvo com sucesso",
  "modelo": {
    "id": 1
  }
}
```

### Respostas de erro

#### 400 Bad Request
```json
{
  "success": false,
  "error": "Descrição do modelo de documento é obrigatória"
}
```

#### 403 Forbidden
```json
{
  "success": false,
  "error": "Apenas administradores podem criar modelos globais."
}
```

---

## Observações

- O registro salva automaticamente o `id_funcionario` do usuário autenticado.
- O campo `global` é opcional e somente pode ser preenchido por usuários com cargo de Administrador.
- O conteúdo binário do documento continua acessível pela rota de download do arquivo.
