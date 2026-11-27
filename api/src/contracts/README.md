# Contratos da API - Interfaces Desacopladas

## 📌 Visão Geral

Esta pasta contém **todas as interfaces (contratos) públicas** da API. Cada interface define o comportamento esperado de um módulo, permitindo que diferentes módulos trabalhem juntos **sem acoplamento direto**.

## 🗂️ Estrutura

```
contracts/
├── PessoaInterface.php          → Define contrato de Pessoa
├── SocioInterface.php           → Define contrato de Socio
├── ARCHITECTURE.md              → Guia completo da arquitetura
├── NOVO_MODULO_CHECKLIST.md    → Como criar novos módulos
├── README.md                    → Este arquivo
└── [NovasInterfaces].php        → Adicione novas interfaces aqui
```

## 🎯 Princípio Fundamental

```
┌─────────────────────────────────────────────┐
│  Módulo A → Depende de Interface → Módulo B │
│                                             │
│  Módulo A NÃO conhece detalhes de Módulo B │
│  Apenas segue o contrato (interface)       │
└─────────────────────────────────────────────┘
```

## 📊 Diagrama de Relações Atuais

```
┌──────────────┐         ┌──────────────────┐
│   Socio      │────────→│ PessoaInterface  │
│   (módulo)   │ depende │ (contrato)       │
└──────────────┘         └──────────────────┘
       ↑                         ↑
       │                         │
       │ implementa              │ implementa
       │                         │
  ┌────────────────┐      ┌────────────┐
  │ SocioInterface │      │  Pessoa    │
  │ (contrato)     │      │  (módulo)  │
  └────────────────┘      └────────────┘
```

## 🔗 Como Interfaces Funcionam

### Sem Interfaces (Acoplado ❌)
```
Socio.php → import Pessoa.php → if Pessoa.php changes, Socio breaks
```

### Com Interfaces (Desacoplado ✅)
```
Socio.php → import PessoaInterface.php → Pessoa.php implements PessoaInterface.php
                                      → qualquer implementação funciona!
```

## 📝 Interfaces Disponíveis

### `PessoaInterface`
Define o contrato mínimo para uma Pessoa na aplicação.

**Métodos:**
- `getId(): ?int`
- `getNome(): string`
- `getSobrenome(): string`
- `getDataNascimento(): ?DateTime`
- `getSexo(): ?string`
- `getTelefone(): ?string`
- `getCpf(): string`

**Implementado por:** `api\modules\Pessoa\Pessoa`

---

### `SocioInterface`
Define o contrato mínimo para um Socio na aplicação.

**Métodos:**
- `getId(): int`
- `getPessoa(): PessoaInterface`

**Implementado por:** `api\modules\Socio\Socio`

---

## 🚀 Como Usar

### 1️⃣ Módulo que Precisa de Outra Classe
```php
// ✅ Correto - Depende de interface
use api\contracts\PessoaInterface;

class Socio {
    public function __construct(PessoaInterface $pessoa) { }
}
```

### 2️⃣ Instanciar e Injetar
```php
$pessoa = new Pessoa(...);
$socio = new Socio($pessoa);  // Pessoa implementa PessoaInterface
```

### 3️⃣ Usar com Diferentes Implementações
```php
// Qualquer classe que implemente PessoaInterface funciona
$pessoa = obterPessoaDoBancoDados();  // retorna PessoaInterface
$pessoaMock = new PessoaMock();       // retorna PessoaInterface
$pessoaAPI = chamarApiExterna();      // retorna PessoaInterface

// Tudo funciona transparentemente
$socio = new Socio($pessoa);
$socio = new Socio($pessoaMock);
$socio = new Socio($pessoaAPI);
```

## 📚 Documentação Detalhada

- **[ARCHITECTURE.md](ARCHITECTURE.md)** - Guia completo com exemplos
- **[NOVO_MODULO_CHECKLIST.md](NOVO_MODULO_CHECKLIST.md)** - Passo a passo para novos módulos
- **[../EXEMPLOS_USO.php](../EXEMPLOS_USO.php)** - Exemplos práticos de uso

## ✨ Benefícios Desta Arquitetura

| Benefício | Descrição |
|-----------|-----------|
| 🎯 **Desacoplamento** | Módulos não dependem uns dos outros |
| 🧪 **Testabilidade** | Fácil criar mocks para testes |
| 🔄 **Extensibilidade** | Múltiplas implementações da mesma interface |
| 🛠️ **Manutenibilidade** | Mudanças isoladas por módulo |
| 🔌 **Flexibilidade** | Trocar implementações sem quebrar código |
| 📖 **Clareza** | Contrato claro do que cada módulo oferece |

## 🏗️ Adicionando Nova Interface

### Passo 1: Crie a Interface
```php
// api/src/contracts/NovoModuloInterface.php
<?php
namespace api\contracts;

interface NovoModuloInterface
{
    public function getId(): int;
    public function getData(): array;
}
```

### Passo 2: Implemente no Módulo
```php
// api/src/modules/NovoModulo/NovoModulo.php
<?php
namespace api\modules\NovoModulo;

use api\contracts\NovoModuloInterface;

class NovoModulo implements NovoModuloInterface
{
    public function getId(): int { return 1; }
    public function getData(): array { return []; }
}
```

### Passo 3: Use em Outro Módulo
```php
// Qualquer outro módulo
use api\contracts\NovoModuloInterface;

class OutroModulo
{
    public function __construct(NovoModuloInterface $novo) { }
}
```

## ⚠️ Regras Importantes

✅ **Faça:**
- Crie interfaces para classes que serão dependências
- Injete interfaces (não classes concretas)
- Documente a interface com PHPDoc
- Use o Factory para criação complexa

❌ **Não Faça:**
- Importe classes concretas de outros módulos
- Use dependências globais ou estáticas
- Misture implementação na interface
- Crie interfaces para tudo (use bom senso)

## 🔍 Verificando Dependências

Para verificar se uma classe está acoplada:

```bash
# Procure por "use api\modules" em arquivos de módulos
# Isso indica acoplamento direto ❌
grep -r "use api\\modules" api/src/modules/

# Procure por "use api\contracts" em arquivos de módulos
# Isso indica desacoplamento ✅
grep -r "use api\\contracts" api/src/modules/
```

## 📞 Suporte

- Consulte a [documentação da arquitetura](ARCHITECTURE.md)
- Revise o [checklist para novos módulos](NOVO_MODULO_CHECKLIST.md)
- Veja [exemplos práticos](../EXEMPLOS_USO.php)

---

**Versão:** 1.0  
**Última atualização:** 2026-04-30  
**Padrão:** SOLID - Dependency Inversion Principle
