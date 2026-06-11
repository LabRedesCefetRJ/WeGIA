# API Pessoa - Especificação JSON

## Visão Geral

Documentação completa das endpoints e formatos JSON para o módulo de Pessoa na WeGIA API.

---

## 1. Atualizar Perfil do Usuário

### Endpoint
```
PUT /pessoas/profile
```

### Autenticação
**Obrigatório** - Bearer Token (JWT)

```
Authorization: Bearer <seu_token_jwt>
```

### Descrição
Atualiza os dados pessoais e de endereço do perfil de um usuário autenticado. Apenas o proprietário dos dados pode fazer alterações em seu próprio perfil.

### Request Body

```json
{
  "id": 1,
  "nome": "João",
  "sobrenome": "Silva",
  "cpf": "123.456.789-10",
  "data_nascimento": "1990-05-15",
  "sexo": "M",
  "telefone": "(11) 98765-4321",
  "email": "joao.silva@example.com",
  "endereco": {
    "cep": "01001-000",
    "estado": "SP",
    "cidade": "São Paulo",
    "bairro": "Sé",
    "logradouro": "Praça da Sé",
    "numero": "100",
    "complemento": "Apto 12"
  }
}
```

### Campos

| Campo | Tipo | Obrigatório | Descrição |
|-------|------|-------------|-----------|
| `id` | Integer | Sim | ID da pessoa a ser atualizada (deve ser igual ao ID do usuário autenticado) |
| `nome` | String | Sim | Primeiro nome da pessoa (máx. 255 caracteres) |
| `sobrenome` | String | Sim | Sobrenome da pessoa (máx. 255 caracteres) |
| `cpf` | String | Sim | CPF válido (com ou sem formatação: XXX.XXX.XXX-XX ou XXXXXXXXXXX) |
| `data_nascimento` | String | Não | Data de nascimento no formato `YYYY-MM-DD` |
| `sexo` | String | Não | Sexo da pessoa (ex: "M", "F", "O") |
| `telefone` | String | Não | Telefone de contato com formatação (ex: "(11) 98765-4321") |
| `email` | String | Não | Endereço de e-mail válido |
| `endereco` | Object | Não | Dados de endereço salvos na tabela `pessoa` |

Nota: para compatibilidade, os campos de endereço também podem ser enviados no nível raiz do JSON (`cep`, `estado`, `cidade`, `bairro`, `logradouro`, `numero`, `complemento`).

### Campos de `endereco`

| Campo | Tipo | Obrigatório | Descrição |
|-------|------|-------------|-----------|
| `cep` | String | Não | CEP do endereço |
| `estado` | String | Não | UF do endereço |
| `cidade` | String | Não | Cidade |
| `bairro` | String | Não | Bairro |
| `logradouro` | String | Não | Logradouro |
| `numero` | String | Não | Número do endereço |
| `complemento` | String | Não | Complemento do endereço |

### Resposta com Sucesso (200 OK)

```json
{
  "message": "Perfil atualizado com sucesso",
  "data": {
    "id": 1,
    "nome": "João",
    "sobrenome": "Silva",
    "cpf": "123.456.789-10",
    "data_nascimento": "1990-05-15",
    "sexo": "M",
    "telefone": "(11) 98765-4321",
    "email": "joao.silva@example.com",
    "endereco": {
      "logradouro": "Praça da Sé",
      "numero": "100",
      "complemento": "Apto 12",
      "bairro": "Sé",
      "cidade": "São Paulo",
      "estado": "SP",
      "cep": "01001-000"
    }
  }
}
```

### Respostas de Erro

#### 400 Bad Request - Campos Obrigatórios Faltando
```json
{
  "error": "Campos obrigatórios faltando: nome, sobrenome, cpf"
}
```

#### 400 Bad Request - Data Inválida
```json
{
  "error": "Formato de data inválido. Use: YYYY-MM-DD"
}
```

#### 400 Bad Request - CPF Inválido
```json
{
  "error": "CPF inválido"
}
```

#### 401 Unauthorized - Sem Autenticação
```json
{
  "error": "Usuário não autenticado"
}
```

#### 403 Forbidden - Sem Permissão
```json
{
  "error": "Você não tem permissão para editar este perfil"
}
```

Nota: Um usuário não pode editar o perfil de outro usuário. Se o `id` fornecido for diferente do ID do usuário autenticado, a requisição será rejeitada com status 403.

#### 404 Not Found - Usuário Não Encontrado
```json
{
  "error": "Pessoa não encontrada"
}
```

#### 400 Bad Request - Endereço Inválido
```json
{
  "error": "O campo endereco deve ser um objeto JSON"
}
```

#### 500 Internal Server Error
```json
{
  "error": "Erro ao atualizar pessoa"
}
```

---

## 2. Modelos de Dados

### Pessoa

```typescript
interface Pessoa {
  id?: number;
  nome: string;
  sobrenome: string;
  cpf: string;
  data_nascimento?: string; // Format: YYYY-MM-DD
  sexo?: string;
  telefone?: string;
  email?: string;
  endereco?: Endereco;
}
```

### Endereco

```typescript
interface Endereco {
  id?: number;
  logradouro?: string;
  numero?: string;
  complemento?: string;
  bairro?: string;
  cidade?: string;
  estado?: string;
  cep?: string;
}
```

---

## 3. Validações

### Validação de CPF
- O CPF é validado usando o algoritmo oficial de check-digit
- Aceita formatação: `XXX.XXX.XXX-XX` ou `XXXXXXXXXXX`
- CPF é normalizado automaticamente durante o armazenamento

### Validação de Email
- Email deve ser um endereço válido (validação básica)

### Validação de Data de Nascimento
- Formato obrigatório: `YYYY-MM-DD`
- Exemplo: `1990-05-15`

### Validação de Telefone
- Aceita diferentes formatos
- Recomendado: `(XX) 9XXXX-XXXX` para celular ou `(XX) XXXX-XXXX` para fixo

### Validação de Endereço
- O objeto `endereco` deve ser JSON válido quando informado
- Os campos podem ser enviados parcialmente; apenas os informados são atualizados na tabela `pessoa`

---

## 4. Exemplos de Uso

### cURL

```bash
curl -X PUT http://localhost:8000/pessoas/profile \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer seu_token_jwt_aqui" \
  -d '{
    "id": 1,
    "nome": "João",
    "sobrenome": "Silva",
    "cpf": "123.456.789-10",
    "data_nascimento": "1990-05-15",
    "sexo": "M",
    "telefone": "(11) 98765-4321",
    "email": "joao.silva@example.com",
    "endereco": {
      "cep": "01001-000",
      "estado": "SP",
      "cidade": "São Paulo",
      "bairro": "Sé",
      "logradouro": "Praça da Sé",
      "numero": "100",
      "complemento": "Apto 12"
    }
  }'
```

### JavaScript/Fetch

```javascript
const token = 'seu_token_jwt_aqui';
const userId = 1;

const dadosAtualizados = {
  id: userId,
  nome: "João",
  sobrenome: "Silva",
  cpf: "123.456.789-10",
  data_nascimento: "1990-05-15",
  sexo: "M",
  telefone: "(11) 98765-4321",
  email: "joao.silva@example.com",
  endereco: {
    cep: "01001-000",
    estado: "SP",
    cidade: "São Paulo",
    bairro: "Sé",
    logradouro: "Praça da Sé",
    numero: "100",
    complemento: "Apto 12"
  }
};

fetch('http://localhost:8000/pessoas/profile', {
  method: 'PUT',
  headers: {
    'Content-Type': 'application/json',
    'Authorization': `Bearer ${token}`
  },
  body: JSON.stringify(dadosAtualizados)
})
  .then(response => response.json())
  .then(data => console.log(data))
  .catch(error => console.error('Erro:', error));
```

### Python/Requests

```python
import requests
import json

token = 'seu_token_jwt_aqui'
user_id = 1

headers = {
    'Content-Type': 'application/json',
    'Authorization': f'Bearer {token}'
}

dados = {
    'id': user_id,
    'nome': 'João',
    'sobrenome': 'Silva',
    'cpf': '123.456.789-10',
    'data_nascimento': '1990-05-15',
    'sexo': 'M',
    'telefone': '(11) 98765-4321',
    'email': 'joao.silva@example.com',
    'endereco': {
        'cep': '01001-000',
        'estado': 'SP',
        'cidade': 'São Paulo',
        'bairro': 'Sé',
        'logradouro': 'Praça da Sé',
        'numero': '100',
        'complemento': 'Apto 12'
    }
}

response = requests.put(
    'http://localhost:8000/pessoas/profile',
    headers=headers,
    json=dados
)

print(response.status_code)
print(response.json())
```

---

## 5. Segurança e Permissões

### Autenticação (AuthMiddleware)
- Todas as requisições para `/pessoas/profile` requerem um token JWT válido
- O token deve ser enviado no header `Authorization` com o prefixo `Bearer `
- O ID do usuário é extraído do claim `sub` do token JWT

### Autorização
- **Regra Principal**: Um usuário só pode atualizar seu próprio perfil
- Comparação: `user_id_do_token === id_na_requisicao`
- Se um usuário tentar editar o perfil de outro usuário, a requisição retorna **403 Forbidden**

### Dados Sensíveis
- CPF é normalizado antes do armazenamento
- As senhas não são modificáveis através desta rota (são gerenciadas através de outras rotas de autenticação)

---

## 6. Fluxo de Requisição

```
┌─────────────────────────────────────────────────────────┐
│ Cliente envia requisição PUT /pessoas/profile            │
│ com token JWT no header Authorization                   │
└──────────────────┬──────────────────────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────────────────────┐
│ AuthMiddleware valida o token JWT                       │
│ Extrai user_id do claim 'sub'                           │
└──────────────────┬──────────────────────────────────────┘
                   │
                   ▼
        ┌──────────────────────┐
        │ Token válido?        │
        └──┬───────────────┬───┘
           │ Não           │ Sim
           ▼               ▼
      (401 Error)  ┌──────────────────────┐
                   │ PessoaController     │
                   │ recebe a requisição  │
                   └──────┬───────────────┘
                          │
                          ▼
                   ┌──────────────────────┐
                   │ Valida permissão:    │
                   │ user_id == body.id?  │
                   └──┬───────────────┬───┘
                      │ Não           │ Sim
                      ▼               ▼
                 (403 Error)   ┌────────────────────┐
                               │ Valida campos      │
                               │ obrigatórios       │
                               └──┬────────────┬────┘
                                  │ Faltando   │ OK
                                  ▼            ▼
                             (400 Error)  ┌──────────────────┐
                                          │ PessoaService    │
                                          │ atualizarPessoa()│
                                          └──┬───────────────┘
                                             │
                                             ▼
                                  ┌────────────────────┐
                                  │ PessoaRepository   │
                                  │ update()           │
                                  └──┬────────────────┘
                                     │
                                     ▼
                                ┌─────────────┐
                                │ BD: UPDATE  │
                                │ pessoa      │
                                └──┬──────────┘
                                   │
                                   ▼
                            ┌──────────────────┐
                            │ (200 Success)    │
                            │ com dados        │
                            │ atualizados      │
                            └──────────────────┘
```

---

## 7. Status HTTP

| Status | Descrição |
|--------|-----------|
| `200` | Sucesso - Perfil atualizado com sucesso |
| `400` | Bad Request - Dados inválidos ou incompletos |
| `401` | Unauthorized - Token ausente ou inválido |
| `403` | Forbidden - Usuário não tem permissão para editar este perfil |
| `404` | Not Found - Pessoa não encontrada |
| `500` | Internal Server Error - Erro no servidor |

---

## 8. Estrutura de Código

### Hierarquia de Arquivos
```
api/src/modules/Pessoa/
├── Pessoa.php                    (Entity/Model)
├── PessoaController.php          (Controller)
├── PessoaService.php             (Service/Business Logic)
├── PessoaRepository.php          (Data Access)
├── Endereco.php                  (Related Entity)
└── ...

api/contracts/services/
└── PessoaServiceInterface.php    (Interface)

api/modules/Auth/
├── AuthMiddleware.php            (Authentication)
└── ...
```

### Fluxo de Dados
1. **Request** → PessoaController
2. **Validação** → PessoaController
3. **Permissão** → PessoaController
4. **Negócio** → PessoaService
5. **Persistência** → PessoaRepository
6. **Banco de Dados** → MySQL
7. **Response** ← PessoaController

---

## 9. Tabela de Banco de Dados

```sql
CREATE TABLE pessoa (
  id_pessoa INT PRIMARY KEY AUTO_INCREMENT,
  nome VARCHAR(255) NOT NULL,
  sobrenome VARCHAR(255) NOT NULL,
  cpf VARCHAR(14) NOT NULL UNIQUE,
  data_nascimento DATE,
  sexo CHAR(1),
  telefone VARCHAR(20),
  email VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

---

## 10. Notas Importantes

- **Imutabilidade do CPF**: O CPF é um identificador único e não deve ser alterado após a criação da pessoa
- **Validação de Dados**: Todos os dados são validados tanto no controller quanto no serviço
- **Normalização**: CPF é normalizado automaticamente (formatação padronizada)
- **Permissões Granulares**: A validação de permissão ocorre no controller para evitar chamadas desnecessárias ao banco de dados
- **Tratamento de Erros**: Todos os erros retornam mensagens JSON estruturadas com status HTTP apropriados

---

## 11. Changelog

| Data | Versão | Alteração |
|------|--------|-----------|
| 2026-06-10 | 1.0 | Documentação inicial da API de Pessoa |

---

## 12. Contato e Suporte

Para questões sobre esta API, consulte a documentação principal do projeto WeGIA ou entre em contato com a equipe de desenvolvimento.
