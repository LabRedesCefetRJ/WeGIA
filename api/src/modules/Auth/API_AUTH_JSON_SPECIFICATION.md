# Especificação JSON - Rotas de Autenticação

Documento de referência dos JSONs esperados de requisição e resposta para as rotas do módulo de Autenticação da API WeGIA.

---

## 1. POST `/login`

Realiza o login de um usuário no sistema, validando as credenciais fornecidas e gerando tokens JWT de acesso e atualização.

### Requisição
```json
{
  "login": "usuario@example.com",
  "senha": "SenhaSegura@123"
}
```

| Campo | Tipo | Obrigatório | Descrição |
|-------|------|-------------|-----------|
| `login` | string | Sim | Login do usuário (email ou nome de usuário) |
| `senha` | string | Sim | Senha do usuário |

### Resposta - 200 OK (Login Realizado com Sucesso)
```json
{
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
  "refresh_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
  "expires_in": 900,
  "token_type": "Bearer"
}
```

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `access_token` | string | Token JWT de acesso (válido por 15 minutos) |
| `refresh_token` | string | Token JWT de atualização (válido por 7 dias) |
| `expires_in` | integer | Tempo de expiração do token de acesso em segundos (900 = 15 minutos) |
| `token_type` | string | Tipo de token (sempre "Bearer") |

### Resposta - 401 Unauthorized (Credenciais Inválidas)
```json
{
  "error": "Credenciais inválidas"
}
```

### Resposta - 401 Unauthorized (Usuário Não Encontrado)
```json
{
  "error": "Credenciais inválidas"
}
```

---

## 2. POST `/register`

Registra um novo usuário no sistema com login e senha. A senha deve atender aos critérios de segurança estabelecidos.

### Requisição
```json
{
  "login": "novo.usuario@example.com",
  "senha": "SenhaSegura@123"
}
```

| Campo | Tipo | Obrigatório | Descrição |
|-------|------|-------------|-----------|
| `login` | string | Sim | Login único do usuário |
| `senha` | string | Sim | Senha que deve atender aos critérios de segurança |

### Critérios de Senha Obrigatórios
- Mínimo de 8 caracteres
- Pelo menos uma letra maiúscula (A-Z)
- Pelo menos uma letra minúscula (a-z)
- Pelo menos um número (0-9)
- Pelo menos um caractere especial (!@#$%^&*()_+-=[]{};\':\",./<>?/\\|`~)

### Resposta - 201 Created (Usuário Registrado com Sucesso)
```json
{
  "id_pessoa": 1,
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
  "refresh_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
  "expires_in": 900,
  "token_type": "Bearer"
}
```

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `id_pessoa` | integer | ID único da pessoa criada |
| `access_token` | string | Token JWT de acesso (válido por 15 minutos) |
| `refresh_token` | string | Token JWT de atualização (válido por 7 dias) |
| `expires_in` | integer | Tempo de expiração do token de acesso em segundos (900 = 15 minutos) |
| `token_type` | string | Tipo de token (sempre "Bearer") |

### Resposta - 400 Bad Request (Usuário Já Existe)
```json
{
  "error": "Usuário já existe"
}
```

### Resposta - 400 Bad Request (Senha Vazia)
```json
{
  "error": "Password cannot be empty"
}
```

### Resposta - 400 Bad Request (Senhas Não Coincitem)
```json
{
  "error": "Passwords do not match"
}
```

### Resposta - 400 Bad Request (Senha Muito Curta)
```json
{
  "error": "Password must be at least 8 characters long"
}
```

### Resposta - 400 Bad Request (Falta Letra Maiúscula)
```json
{
  "error": "Password must contain at least one uppercase letter"
}
```

### Resposta - 400 Bad Request (Falta Letra Minúscula)
```json
{
  "error": "Password must contain at least one lowercase letter"
}
```

### Resposta - 400 Bad Request (Falta Número)
```json
{
  "error": "Password must contain at least one number"
}
```

### Resposta - 400 Bad Request (Falta Caractere Especial)
```json
{
  "error": "Password must contain at least one special character (!@#$%^&*()_+-=[]{};\':\",./<>?/\\|`~)"
}
```

---

## 3. POST `/refresh`

Renova o token de acesso utilizando um token de atualização (refresh token) válido.

### Requisição
```json
{
  "refresh_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
}
```

| Campo | Tipo | Obrigatório | Descrição |
|-------|------|-------------|-----------|
| `refresh_token` | string | Sim | Token JWT de atualização válido |

### Resposta - 200 OK (Token Renovado com Sucesso)
```json
{
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
  "refresh_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
  "expires_in": 900,
  "token_type": "Bearer"
}
```

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `access_token` | string | Novo token JWT de acesso (válido por 15 minutos) |
| `refresh_token` | string | Novo token JWT de atualização (válido por 7 dias) |
| `expires_in` | integer | Tempo de expiração do token de acesso em segundos (900 = 15 minutos) |
| `token_type` | string | Tipo de token (sempre "Bearer") |

### Resposta - 400 Bad Request (Refresh Token Ausente)
```json
{
  "error": "Refresh token é obrigatório"
}
```

### Resposta - 401 Unauthorized (Token Inválido ou Expirado)
```json
{
  "error": "Token inválido ou expirado"
}
```

### Resposta - 401 Unauthorized (Token Expirado)
```json
{
  "error": "Token expirado"
}
```

### Resposta - 401 Unauthorized (Assinatura Inválida)
```json
{
  "error": "Assinatura inválida"
}
```

---

## 4. POST `/logout`

Realiza o logout de um usuário validando o token fornecido no header de autorização. Este é um logout lógico sem persistência de estado (stateless).

### Parâmetros
- **Authorization** (header, obrigatório): Token JWT no formato `Bearer <token>`

### Exemplos de Requisição
```
POST /logout
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
```

### Resposta - 200 OK (Logout Realizado com Sucesso)
```json
{
  "message": "Logout realizado com sucesso",
  "user_id": 1
}
```

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `message` | string | Mensagem confirmando o logout |
| `user_id` | integer | ID do usuário que realizou logout |

### Resposta - 401 Unauthorized (Token Não Fornecido)
```json
{
  "error": "Token não fornecido"
}
```

### Resposta - 401 Unauthorized (Token Inválido ou Expirado)
```json
{
  "error": "Token inválido ou expirado"
}
```

### Resposta - 401 Unauthorized (Token Expirado)
```json
{
  "error": "Token expirado"
}
```

### Resposta - 401 Unauthorized (Assinatura Inválida)
```json
{
  "error": "Assinatura inválida"
}
```

---

## Observações Gerais

### Headers Recomendados
Todas as requisições devem incluir o header:
```
Content-Type: application/json
```

### Autenticação
Todas as rotas que requerem autenticação devem incluir o token JWT no header:
```
Authorization: Bearer <access_token>
```

### Validade dos Tokens
- **Access Token**: 15 minutos (900 segundos)
- **Refresh Token**: 7 dias (604800 segundos)

### Estrutura do JWT
Os tokens JWT codificados contêm as seguintes informações no payload:
```json
{
  "iss": "wegia",
  "aud": "wegia-users",
  "iat": 1234567890,
  "exp": 1234568790,
  "sub": 1,
  "type": "access"
}
```

| Campo | Descrição |
|-------|-----------|
| `iss` | Emissor do token (sempre "wegia") |
| `aud` | Público alvo (sempre "wegia-users") |
| `iat` | Timestamp de emissão (issued at) |
| `exp` | Timestamp de expiração |
| `sub` | ID do usuário (subject) |
| `type` | Tipo de token ("access" ou "refresh") |

### Tratamento de Erros
- **400 Bad Request**: Dados inválidos ou ausentes na requisição
- **401 Unauthorized**: Credenciais inválidas, token ausente ou expirado
- **500 Internal Server Error**: Erro do servidor (raro, não documentado especificamente)
