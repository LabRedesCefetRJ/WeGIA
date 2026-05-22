# Especificação JSON - Rotas de Sócios

Documento de referência dos JSONs esperados de requisição e resposta para as rotas do módulo de Sócios da API WeGIA.

---

## 1. POST `/socios/register`

Registra um novo sócio no sistema. Se a pessoa com o CPF fornecido já existe, utiliza-a para criar o sócio. Caso contrário, cria uma nova pessoa e depois o sócio.

### Requisição
```json
{
  "nome": "João",
  "sobrenome": "Silva",
  "cpf": "12345678901",
  "email": "joao@example.com",
  "dataNascimento": "1990-05-15",
  "sexo": "M",
  "telefone": "11987654321",
  "inicioContribuicao": "2024-01-01",
  "valorMensalidade": 50.00,
  "status": 1,
  "autoStatusContribuicao": true,
  "idSocioTipo": 0
}
```

| Campo | Tipo | Obrigatório | Padrão | Descrição |
|-------|------|-------------|--------|-----------|
| `nome` | string | Sim | - | Nome do sócio |
| `sobrenome` | string | Sim | - | Sobrenome do sócio |
| `cpf` | string | Sim | - | CPF (única forma de identificação para busca de pessoa existente) |
| `email` | string | Sim* | - | Email do sócio (obrigatório para envio do código de verificação) |
| `dataNascimento` | string (ISO 8601) | Não | null | Data de nascimento no formato YYYY-MM-DD |
| `sexo` | string | Não | null | Sexo (ex: M, F) |
| `telefone` | string | Não | null | Telefone de contato |
| `inicioContribuicao` | string (ISO 8601) | Sim | - | Data de início da contribuição no formato YYYY-MM-DD |
| `valorMensalidade` | float | Não | 10.00 | Valor da mensalidade em reais |
| `status` | integer | Não | 1 | Status do sócio (1 = ativo) |
| `autoStatusContribuicao` | boolean | Não | true | Atualizar status automaticamente conforme contribuição |
| `idSocioTipo` | integer | Não | 0 | ID do tipo de sócio |

### Resposta - 201 Created (Sucesso)
```json
{
  "socio": {
    "id": 1,
    "pessoa": {
      "id": 1,
      "nome": "João",
      "sobrenome": "Silva",
      "cpf": "12345678901",
      "email": "joao@example.com",
      "dataNascimento": "1990-05-15",
      "sexo": "M",
      "telefone": "11987654321"
    },
    "inicioContribuicao": "2024-01-01",
    "valorMensalidade": 50.00,
    "status": 1,
    "autoStatusContribuicao": true,
    "idSocioTipo": 0
  },
  "email_verification": {
    "success": true,
    "message": "Code sent successfully to joao@example.com",
    "validity_minutes": 15
  }
}
```

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `socio` | object | Objeto contendo os dados do sócio criado |
| `socio.id` | integer | ID único do sócio |
| `socio.pessoa` | object | Dados da pessoa associada ao sócio |
| `email_verification` | object | Resultado do envio do código de verificação |
| `email_verification.success` | boolean | Se o código foi enviado com sucesso |
| `email_verification.message` | string | Mensagem descritiva do resultado |
| `email_verification.validity_minutes` | integer | Validade do código em minutos |

### Resposta - 400 Bad Request (Email Ausente)
```json
{
  "error": "Pessoa deve possuir um e-mail para registro de sócio."
}
```

### Resposta - 500 Internal Server Error
```json
{
  "error": "Mensagem de erro detalhada | Código do erro"
}
```

---

## 2. GET `/socios/exists/{cpf}`

Verifica se um sócio existe no sistema pelo CPF.

### Parâmetros
- **cpf** (path parameter, obrigatório): CPF do sócio a verificar

### Resposta - 200 OK (Sócio Existe)
```json
{
  "exists": true,
  "hasEmail": true
}
```

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `exists` | boolean | Indica se o sócio existe |
| `hasEmail` | boolean | Indica se a pessoa associada possui email cadastrado |

### Resposta - 404 Not Found (Sócio Não Existe)
```json
{
  "exists": false,
  "hasEmail": false,
  "message": "Pessoa não localizada."
}
```

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `exists` | boolean | Sempre `false` |
| `hasEmail` | boolean | Indica se possui email (se a pessoa foi encontrada) |
| `message` | string | Mensagem descritiva do resultado |

### Resposta - 500 Internal Server Error
```json
{
  "error": "Mensagem de erro detalhada | Código do erro"
}
```

---

## 3. GET `/socios/support-contact`

Obtém o contato de suporte da instituição.

### Parâmetros
Nenhum

### Resposta - 200 OK
```json
{
  "contatct": "contato@instituicao.com.br"
}
```

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `contatct` | string | Email ou dados de contato para suporte (nota: há um typo no campo) |

### Resposta - 404 Not Found
```json
{
  "message": "Contato de suporte não localizado."
}
```

### Resposta - 500 Internal Server Error
```json
{
  "error": "Mensagem de erro detalhada | Código do erro"
}
```

---

## 4. GET `/socios/verify-code`

Envia um novo código de verificação para o e-mail cadastrado do sócio. Qualquer código anterior será automaticamente invalidado.

### Parâmetros
- **cpf** (query parameter, obrigatório): CPF do sócio para o qual enviar o código de verificação

### Exemplos de Requisição
```
GET /socios/verify-code?cpf=12345678901
```

### Resposta - 200 OK (Código Enviado com Sucesso)
```json
{
  "success": true,
  "message": "Code sent successfully to joao@example.com",
  "validity_minutes": 15
}
```

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `success` | boolean | Indica se o código foi enviado com sucesso |
| `message` | string | Mensagem descritiva do resultado |
| `validity_minutes` | integer | Validade do código em minutos |

### Resposta - 400 Bad Request (CPF Ausente)
```json
{
  "success": false,
  "message": "CPF é obrigatório"
}
```

### Resposta - 400 Bad Request (CPF Inválido)
```json
{
  "success": false,
  "message": "CPF inválido."
}
```

### Resposta - 400 Bad Request (Sócio Sem Email)
```json
{
  "success": false,
  "message": "Sócio não possui e-mail cadastrado"
}
```

### Resposta - 404 Not Found (Sócio Não Encontrado)
```json
{
  "success": false,
  "message": "Pessoa não localizada."
}
```

### Resposta - 500 Internal Server Error
```json
{
  "success": false,
  "error": "Mensagem de erro detalhada",
  "code": 500
}
```

---

## 5. POST `/socios/verify-code`

Valida um código de verificação enviado por email.

### Requisição
```json
{
  "cpf": "12345678901",
  "code": "123456"
}
```

| Campo | Tipo | Obrigatório | Descrição |
|-------|------|-------------|-----------|
| `cpf` | string | Sim | CPF do sócio |
| `code` | string | Sim | Código de verificação com 6 dígitos |

### Resposta - 200 OK (Código Válido)
```json
{
  "success": true,
  "message": "Code is valid"
}
```

### Resposta - 400 Bad Request (Código Inválido ou Expirado)
```json
{
  "success": false,
  "message": "Invalid or expired code"
}
```

### Resposta - 400 Bad Request (Parâmetros Ausentes)
```json
{
  "success": false,
  "message": "CPF e código são obrigatórios"
}
```

### Resposta - 400 Bad Request (Formato de Código Inválido)
```json
{
  "success": false,
  "message": "Code must contain 6 digits"
}
```

### Resposta - 404 Not Found (Sócio Não Encontrado)
```json
{
  "success": false,
  "message": "Sócio não localizado"
}
```

### Resposta - 500 Internal Server Error
```json
{
  "success": false,
  "error": "Mensagem de erro detalhada",
  "code": 500
}
```

---

## 6. POST `/socios/alter-password`

Altera a senha de um sócio utilizando um código de verificação.

### Requisição
```json
{
  "cpf": "12345678901",
  "senha": "novasenha123",
  "confirmacao_senha": "novasenha123",
  "codigo_verificacao": "123456"
}
```

| Campo | Tipo | Obrigatório | Descrição |
|-------|------|-------------|-----------|
| `cpf` | string | Sim | CPF do sócio |
| `senha` | string | Sim | Nova senha |
| `confirmacao_senha` | string | Sim | Confirmação da nova senha |
| `codigo_verificacao` | string | Sim | Código de verificação com 6 dígitos |

### Resposta - 200 OK (Senha Alterada com Sucesso)
```json
{
  "success": true,
  "message": "Password altered successfully"
}
```

### Resposta - 400 Bad Request (Senhas Não Correspondem)
```json
{
  "success": false,
  "message": "Passwords do not match"
}
```

### Resposta - 400 Bad Request (Código Inválido ou Expirado)
```json
{
  "success": false,
  "message": "Invalid or expired code"
}
```

### Resposta - 400 Bad Request (Parâmetros Ausentes)
```json
{
  "success": false,
  "message": "cpf, senha, confirmacao_senha e codigo_verificacao são obrigatórios"
}
```

### Resposta - 400 Bad Request (Pessoa Não Encontrada)
```json
{
  "success": false,
  "message": "Pessoa not found for the given socio ID"
}
```

### Resposta - 400 Bad Request (Erro ao Atualizar Senha)
```json
{
  "success": false,
  "message": "Error updating password: Mensagem de erro específico"
}
```

### Resposta - 400 Bad Request (Erro ao Marcar Código como Usado)
```json
{
  "success": false,
  "message": "Password updated but error marking code as used: Mensagem de erro específico"
}
```

### Resposta - 404 Not Found (Sócio Não Encontrado)
```json
{
  "success": false,
  "message": "Sócio não localizado"
}
```

### Resposta - 500 Internal Server Error
```json
{
  "success": false,
  "error": "Mensagem de erro detalhada",
  "code": 500
}
```

---

## Observações Gerais

1. **Content-Type**: Todas as respostas são em JSON com header `Content-Type: application/json`

2. **Códigos de Verificação**: 
   - Formato: Sempre 6 dígitos
   - Validade: 15 minutos por padrão
   - Invalidação: Um novo código invalida todos os anteriores

3. **Tratamento de Erros**: 
   - Erros 400: Requisição mal formada ou validação falhou
   - Erros 404: Recurso não encontrado
   - Erros 500: Erro interno do servidor

4. **Nota sobre Typos**: 
   - Campo `contatct` em `/socios/support-contact` contém um typo (deveria ser `contact`)

5. **Fluxo para Solicitar um Novo Código de Verificação**:
   - Cliente chama GET `/socios/verify-code?cpf=12345678901` para solicitar um novo código
   - O sistema valida o CPF, localiza o sócio e envia um novo código para seu email
   - Qualquer código anterior é automaticamente invalidado
   - O cliente pode usar o novo código para validar na rota POST `/socios/verify-code` ou para alterar senha na rota POST `/socios/alter-password`

6. **Fluxo Típico para Alterar Senha**:
   - Cliente chama GET `/socios/verify-code?cpf=12345678901` para solicitar um código de verificação
   - Cliente recebe o código por email
   - Cliente chama POST `/socios/verify-code` com o código para validá-lo (opcional, para confirmação prévia)
   - Cliente chama POST `/socios/alter-password` com a nova senha e o código de verificação
   - O sistema valida novamente o código e atualiza a senha

7. **Fluxo de Registro de Sócio**:
   - Cliente chama POST `/socios/register` com os dados do novo sócio
   - O sistema verifica se a pessoa (por CPF) já existe no banco; se não, cria uma nova
   - O sistema verifica se a pessoa tem email; se não, retorna erro 400
   - O sistema cria o sócio associado à pessoa
   - O sistema envia automaticamente um código de verificação por email (código com 6 dígitos, válido por 15 minutos)
   - A resposta inclui os dados do sócio criado e o resultado do envio do código de verificação

8. **Validação de CPF**:
   - O CPF é usado como identificador único para verificar se a pessoa já existe
   - Se a pessoa já existe, os dados de `nome`, `sobrenome` etc. fornecidos no request são ignorados

---

**Data de Geração**: 21 de maio de 2026  
**Versão API**: 1.1  
**Status**: Desenvolvimento
