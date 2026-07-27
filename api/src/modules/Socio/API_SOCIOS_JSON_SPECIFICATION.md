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
  "idSocioTipo": 0,
  "uuid": "019f7118-9242-70ba-b2d5-54a360512623"
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

## 6. GET `/socios/{uuid}/validar_beneficios`

Valida a situação do sócio pelo UUID v7 armazenado em binário no banco e retorna os dados necessários para a liberação de benefícios de parceiros institucionais e para exibição do resumo na página pública.

### Parâmetros
- **uuid** (path parameter, obrigatório): UUID v7 do sócio em formato textual

### Exemplo de Requisição
```
GET /socios/018f3c30-3c0f-7b3f-8a53-b7b8a9f3f2f1/validar_beneficios
```

### Resposta - 200 OK
```json
{
  "nome": "João",
  "sobrenome": "Silva",
  "email": "joao@example.com",
  "telefone": "11987654321",
  "dataNascimento": "15/**/**90",
  "cpf": "***.***.***-01",
  "dataReferenciaContribuicao": "2024-01-01",
  "dataUltimaContribuicao": "2024-06-15",
  "benefit_points": 12
}
```

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `nome` | string | Nome do sócio |
| `sobrenome` | string | Sobrenome do sócio |
| `email` | string \| null | E-mail cadastrado do sócio, sem censura, para exibição no resumo público |
| `telefone` | string \| null | Telefone cadastrado do sócio, sem censura, para exibição no resumo público |
| `dataNascimento` | string \| null | Data de nascimento parcialmente censurada, exibindo apenas o dia e os dois últimos dígitos do ano |
| `cpf` | string \| null | CPF parcialmente censurado, exibindo apenas os dois últimos dígitos |
| `dataReferenciaContribuicao` | string \| null | Data de referência da contribuição do sócio no formato `YYYY-MM-DD` |
| `dataUltimaContribuicao` | string \| null | Data da última contribuição paga no formato `YYYY-MM-DD` |
| `benefit_points` | integer | Total de pontos de benefício calculados para o sócio |

### Resposta - 400 Bad Request
```json
{
  "message": "UUID inválido."
}
```

Se o UUID estiver em formato válido, mas não for v7, a resposta de erro será:
```json
{
  "message": "UUID v7 inválido."
}
```

### Resposta - 404 Not Found
```json
{
  "message": "Sócio não localizado."
}
```

### Resposta - 500 Internal Server Error
```json
{
  "error": "Mensagem de erro detalhada | Código do erro"
}
```

---

## 7. POST `/socios/alter-password`

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

## 7. GET `/socios/{cpf}`

Retorna os dados de um sócio específico pelo CPF. Requer autenticação via token JWT. O usuário autenticado só pode acessar seus próprios dados.

### Parâmetros
- **cpf** (path parameter, obrigatório): CPF do sócio a buscar
- **Authorization** (header, obrigatório): Token JWT no formato `Bearer <token>`

### Exemplos de Requisição
```
GET /socios/12345678901
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
```

### Resposta - 200 OK (Sócio Encontrado)
```json
{
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
  "idSocioTipo": 0,
  "uuid": "019f7118-9242-70ba-b2d5-54a360512623"
}
```

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `id` | integer | ID único do sócio |
| `pessoa` | object | Dados da pessoa associada ao sócio |
| `inicioContribuicao` | string | Data de início da contribuição (YYYY-MM-DD) |
| `valorMensalidade` | float | Valor da mensalidade em reais |
| `status` | integer | Status do sócio |
| `autoStatusContribuicao` | boolean | Atualiza status automaticamente conforme contribuição |
| `idSocioTipo` | integer | ID do tipo de sócio |
| `uuid` | string \| null | UUID v7 do sócio em formato textual |

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

### Resposta - 404 Not Found (Sócio Não Encontrado)
```json
{
  "message": "Sócio não localizado."
}
```

### Resposta - 500 Internal Server Error
```json
{
  "error": "Mensagem de erro detalhada | Código do erro"
}
```

---

## 8. POST `/socios/parceiros`

Cadastra um novo parceiro institucional no sistema, criando primeiro uma pessoa jurídica e depois o registro de sócio parceiro. Requer autenticação via token JWT e permissão de acesso ao recurso de sócios.

### Parâmetros
- **Authorization** (header, obrigatório): Token JWT no formato `Bearer <token>`
- **Content-Type** (header, obrigatório): `application/json`

### Requisição
```json
{
  "cnpj": "12345678000195",
  "razao_social": "Empresa Exemplo LTDA",
  "email": "contato@empresa.com.br",
  "telefone": "1133334444",
  "endereco": {
    "cep": "01000-000",
    "estado": "SP",
    "cidade": "São Paulo",
    "bairro": "Centro",
    "logradouro": "Rua Exemplo",
    "numero": "100",
    "complemento": "Sala 1"
  },
  "localizacao": "São Paulo - SP",
  "divulgacao": "Presencial"
}
```

| Campo | Tipo | Obrigatório | Descrição |
|-------|------|-------------|-----------|
| `cnpj` | string | Sim | CNPJ da instituição parceiro |
| `razao_social` | string | Sim | Razão social da instituição |
| `email` | string | Não | E-mail de contato |
| `telefone` | string | Não | Telefone de contato |
| `endereco` | object | Não | Objeto com dados de endereço |
| `localizacao` | string | Não | Localização do parceiro |
| `divulgacao` | string | Não | Forma de divulgação |

### Resposta - 201 Created (Sucesso)
```json
{
  "success": true,
  "socio_parceiro": {
    "success": true,
    "message": "Socio parceiro inserted successfully"
  }
}
```

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `success` | boolean | Indica sucesso do cadastro |
| `socio_parceiro` | object | Resultado do registro do parceiro |

### Resposta - 400 Bad Request (Dados Obrigatórios Ausentes)
```json
{
  "success": false,
  "message": "CNPJ e razão social são obrigatórios"
}
```

### Resposta - 400 Bad Request (CNPJ Inválido)
```json
{
  "success": false,
  "error": "CNPJ inválido",
  "code": 400
}
```

### Resposta - 403 Forbidden (Sem Permissão)
```json
{
  "error": "Usuário não possui permissão para acessar este recurso",
  "status": "forbidden"
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

## 9. GET `/socios/parceiros`

Lista os parceiros institucionais cadastrados no sistema.

### Parâmetros
Nenhum

### Requisição
Não há corpo de requisição.

### Resposta - 200 OK
```json
{
  "success": true,
  "socio_parceiros": [
    {
      "id": 1,
      "divulgacao": "Presencial",
      "localizacao": "São Paulo - SP",
      "razao_social": "Empresa Exemplo LTDA",
      "cnpj": "12345678000195",
      "telefone": "1133334444",
      "email": "contato@empresa.com.br",
      "cep": "01000-000",
      "estado": "SP",
      "cidade": "São Paulo",
      "bairro": "Centro",
      "logradouro": "Rua Exemplo",
      "numero_endereco": "100",
      "complemento": "Sala 1"
    }
  ]
}
```

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `success` | boolean | Indica que a listagem foi concluída com sucesso |
| `socio_parceiros` | array<object> | Lista de parceiros institucionais cadastrados |
| `socio_parceiros[].id` | integer | ID do registro do parceiro na tabela de parceiros institucionais |
| `socio_parceiros[].divulgacao` | string | Forma de divulgação do parceiro |
| `socio_parceiros[].localizacao` | string | Localização informada para o parceiro |
| `socio_parceiros[].razao_social` | string | Razão social da pessoa jurídica cadastrada |
| `socio_parceiros[].cnpj` | string | CNPJ do parceiro, armazenado no campo `cpf` da tabela `pessoa` |
| `socio_parceiros[].telefone` | string | Telefone de contato |
| `socio_parceiros[].email` | string | E-mail de contato |
| `socio_parceiros[].cep` | string | CEP do endereço cadastrado |
| `socio_parceiros[].estado` | string | Estado do endereço cadastrado |
| `socio_parceiros[].cidade` | string | Cidade do endereço cadastrado |
| `socio_parceiros[].bairro` | string | Bairro do endereço cadastrado |
| `socio_parceiros[].logradouro` | string | Logradouro do endereço cadastrado |
| `socio_parceiros[].numero_endereco` | string | Número do endereço cadastrado |
| `socio_parceiros[].complemento` | string | Complemento do endereço cadastrado |

### Resposta - 500 Internal Server Error
```json
{
  "success": false,
  "error": "Mensagem de erro detalhada",
  "code": 500
}
```

---

## 10. GET `/socios/{id}/beneficios`

Retorna a quantidade de pontos de benefício de um sócio específico. Requer autenticação via token JWT. O usuário autenticado só pode acessar os próprios benefícios.

### Parâmetros
- **id** (path parameter, obrigatório): ID do sócio
- **Authorization** (header, obrigatório): Token JWT no formato `Bearer <token>`

### Exemplos de Requisição
```
GET /socios/1/beneficios
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
```

### Resposta - 200 OK
```json
{
  "benefit_points": 12
}
```

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `benefit_points` | integer | Quantidade de pontos de benefício calculada para o sócio |

### Resposta - 403 Forbidden (Acesso Negado)
```json
{
  "error": "Acesso negado. Você não tem permissão para acessar os dados de outro sócio."
}
```

### Resposta - 500 Internal Server Error
```json
{
  "error": "Mensagem de erro detalhada | Código do erro"
}
```

---

## Observações Gerais

1. **Autenticação das rotas de parceiros**: 
   - A rota POST `/socios/parceiros` exige um token JWT válido e permissão para o recurso de sócios.
   - A rota GET `/socios/parceiros` é pública e não exige autenticação.
   - Em ambiente de desenvolvimento, o teste da rota protegida deve ser feito com um usuário que tenha acesso ao recurso configurado no middleware.

2. **Validação de dados**:
   - O endpoint exige `cnpj` e `razao_social` como campos mínimos.
   - O CNPJ é validado pela regra interna da API antes da criação da pessoa jurídica.

3. **Persistência**:
   - A rota cria uma pessoa jurídica e, em seguida, registra o cadastro na tabela de parceiros institucionais.
   - O valor de `idSocioBenefitRule` está atualmente fixado no repositório, então esse ponto deve ser revisado em uma segunda fase.

1. **Content-Type**: Todas as respostas são em JSON com header `Content-Type: application/json`

2. **Autenticação**: 
   - A rota GET `/socios/{cpf}` requer autenticação via token JWT
   - O usuário autenticado só pode acessar seus próprios dados
   - Tentativas de acessar dados de outro sócio resultarão em erro 403 Forbidden

3. **Códigos de Verificação**: 
   - Formato: Sempre 6 dígitos
   - Validade: 15 minutos por padrão
   - Invalidação: Um novo código invalida todos os anteriores

4. **Tratamento de Erros**: 
   - Erros 400: Requisição mal formada ou validação falhou
   - Erros 403: Acesso negado (não autorizado para acessar o recurso)
   - Erros 404: Recurso não encontrado
   - Erros 500: Erro interno do servidor

5. **Nota sobre Typos**: 
   - Campo `contatct` em `/socios/support-contact` contém um typo (deveria ser `contact`)

6. **Fluxo de Benefícios**:
   - Cliente chama GET `/socios/{id}/beneficios` com token JWT válido
   - O sistema valida se o `user_id` do token pertence ao sócio informado
   - O sistema busca as regras de benefício e calcula os pontos com base nas contribuições dentro da janela de análise
   - A resposta retorna o campo `benefit_points` com um valor inteiro

7. **Fluxo para Solicitar um Novo Código de Verificação**:
   - Cliente chama GET `/socios/verify-code?cpf=12345678901` para solicitar um novo código
   - O sistema valida o CPF, localiza o sócio e envia um novo código para seu email
   - Qualquer código anterior é automaticamente invalidado
   - O cliente pode usar o novo código para validar na rota POST `/socios/verify-code` ou para alterar senha na rota POST `/socios/alter-password`

8. **Fluxo Típico para Alterar Senha**:
   - Cliente chama GET `/socios/verify-code?cpf=12345678901` para solicitar um código de verificação
   - Cliente recebe o código por email
   - Cliente chama POST `/socios/verify-code` com o código para validá-lo (opcional, para confirmação prévia)
   - Cliente chama POST `/socios/alter-password` com a nova senha e o código de verificação
   - O sistema valida novamente o código e atualiza a senha

9. **Fluxo de Registro de Sócio**:
   - Cliente chama POST `/socios/register` com os dados do novo sócio
   - O sistema verifica se a pessoa (por CPF) já existe no banco; se não, cria uma nova
   - O sistema verifica se a pessoa tem email; se não, retorna erro 400
   - O sistema cria o sócio associado à pessoa
   - O sistema envia automaticamente um código de verificação por email (código com 6 dígitos, válido por 15 minutos)
   - A resposta inclui os dados do sócio criado e o resultado do envio do código de verificação

10. **Validação de CPF**:
   - O CPF é usado como identificador único para verificar se a pessoa já existe
   - Se a pessoa já existe, os dados de `nome`, `sobrenome` etc. fornecidos no request são ignorados

---

**Data de Geração**: 21 de maio de 2026  
**Versão API**: 1.1  
**Status**: Desenvolvimento
