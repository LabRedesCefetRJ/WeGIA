# Arquitetura com Interfaces - Guia de Uso

## Visão Geral

Este projeto utiliza **interfaces (contratos) para desacoplar módulos** e seguir o **Princípio da Inversão de Dependência** (Dependency Inversion Principle - DIP).

## Estrutura

```
api/src/
├── contracts/          # Interfaces que definem contratos entre módulos
│   ├── PessoaInterface.php
│   ├── SocioInterface.php
│   └── [OutrasInterfaces].php
├── modules/           # Implementações concretas de cada módulo
│   ├── Pessoa/
│   │   └── Pessoa.php (implementa PessoaInterface)
│   ├── Socio/
│   │   └── Socio.php (implementa SocioInterface, usa PessoaInterface)
│   └── [OutrosModulos]/
└── [Outras pastas]
```

## Como Usar

### 1. **Criar uma Interface para um Novo Módulo**

Em `api/src/contracts/`, crie uma interface que defina o contrato público do seu módulo:

```php
<?php

namespace api\contracts;

interface MeuModuloInterface
{
    public function getId(): int;
    public function getNome(): string;
    // ... outros métodos públicos
}
```

### 2. **Implementar a Interface na Classe Concreta**

Na pasta `api/src/modules/MeuModulo/`, crie a implementação:

```php
<?php

namespace api\modules\MeuModulo;

use api\contracts\MeuModuloInterface;

class MeuModulo implements MeuModuloInterface
{
    private int $id;
    private string $nome;

    public function __construct(int $id, string $nome)
    {
        $this->id = $id;
        $this->nome = $nome;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getNome(): string
    {
        return $this->nome;
    }
}
```

### 3. **Usar Interfaces Entre Módulos (Não Acoplar)**

Quando um módulo precisa usar outro módulo, **sempre dependa da interface**, não da classe concreta:

❌ **Errado - Acoplado:**
```php
use api\modules\Pessoa\Pessoa;

class Socio {
    public function __construct(Pessoa $pessoa) { }
}
```

✅ **Correto - Desacoplado:**
```php
use api\contracts\PessoaInterface;

class Socio implements SocioInterface {
    public function __construct(PessoaInterface $pessoa) { }
}
```

## Benefícios

| Benefício | Descrição |
|-----------|-----------|
| **Desacoplamento** | Módulos não dependem uns dos outros, apenas das interfaces |
| **Testabilidade** | Fácil criar mocks e stubs para testes unitários |
| **Extensibilidade** | Novos módulos podem implementar as mesmas interfaces |
| **Manutenibilidade** | Mudanças internas de um módulo não afetam outros |
| **Flexibilidade** | Trocar implementações sem modificar código cliente |

## Exemplo Prático: Socio → Pessoa

### Interface (Contrato)
```php
// api/src/contracts/PessoaInterface.php
interface PessoaInterface {
    public function getId(): ?int;
    public function getNome(): string;
    public function getCpf(): string;
}
```

### Implementação
```php
// api/src/modules/Pessoa/Pessoa.php
class Pessoa implements PessoaInterface { ... }
```

### Uso em Outro Módulo
```php
// api/src/modules/Socio/Socio.php
use api\contracts\PessoaInterface;

class Socio implements SocioInterface {
    private PessoaInterface $pessoa; // Depende da interface!
    
    public function __construct(int $id, PessoaInterface $pessoa) {
        $this->id = $id;
        $this->pessoa = $pessoa;
    }
}
```

## Padrão de Injeção de Dependência

Sempre injete dependências pelo construtor:

```php
// ✅ Bom - Injeção pelo construtor
$pessoa = new Pessoa(...);
$socio = new Socio(1, $pessoa);

// ❌ Evitar - Dependência global/estática
Socio::setPessoa($pessoa);
```

## Quando Criar uma Interface

Crie uma interface quando:
- ✅ Múltiplos módulos precisam usar essa abstração
- ✅ A classe será usada como dependência em outras classes
- ✅ Você quer permitir diferentes implementações da mesma funcionalidade
- ✅ Você quer facilitar testes unitários com mocks

Não é necessário criar interfaces para:
- ❌ Classes internas/auxiliares do módulo
- ❌ Value Objects simples
- ❌ Classes que nunca são usadas por outros módulos

## Referências

- [SOLID - Dependency Inversion Principle](https://en.wikipedia.org/wiki/Dependency_inversion_principle)
- [Dependency Injection Pattern](https://refactoring.guru/design-patterns/dependency-injection)
- [Interface Segregation Principle](https://en.wikipedia.org/wiki/Interface_segregation_principle)
