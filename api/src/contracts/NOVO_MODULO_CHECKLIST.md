# Checklist para Criar Novos Módulos com Arquitetura Desacoplada

Siga este checklist ao criar um novo módulo na API para manter a arquitetura consistente e desacoplada.

## 📋 Passos para um Novo Módulo

### 1. **Planeje a Interface** ✓
- [ ] Identifique os métodos públicos necessários
- [ ] Defina tipos de retorno e parâmetros
- [ ] Documente o propósito de cada método

```php
// api/src/contracts/MeuModuloInterface.php
namespace api\contracts;

interface MeuModuloInterface
{
    public function getId(): int;
    public function getDescricao(): string;
    // ... métodos públicos necessários
}
```

### 2. **Crie a Interface** ✓
- [ ] Localize em `api/src/contracts/MeuModuloInterface.php`
- [ ] Documente com PHPDoc
- [ ] Inclua apenas métodos públicos (não implementação)

### 3. **Implemente a Classe** ✓
- [ ] Localize em `api/src/modules/MeuModulo/MeuModulo.php`
- [ ] Implemente a interface: `class MeuModulo implements MeuModuloInterface`
- [ ] Implemente todos os métodos da interface
- [ ] Use dependency injection no construtor

```php
namespace api\modules\MeuModulo;

use api\contracts\MeuModuloInterface;
use api\contracts\OutraInterface; // Dependa de interfaces, não de classes!

class MeuModulo implements MeuModuloInterface
{
    private OutraInterface $dependencia;
    
    public function __construct(OutraInterface $dependencia)
    {
        $this->dependencia = $dependencia;
    }
}
```

### 4. **Use em Outros Módulos** ✓
- [ ] Importe a **interface**, não a classe: `use api\contracts\MeuModuloInterface;`
- [ ] Injete a interface nos construtores
- [ ] Nunca use `use api\modules\MeuModulo\MeuModulo;`

```php
class OutroModulo
{
    private MeuModuloInterface $meuModulo; // Interface!
    
    public function __construct(MeuModuloInterface $meuModulo)
    {
        $this->meuModulo = $meuModulo;
    }
}
```

### 5. **Teste** ✓
- [ ] Crie testes unitários
- [ ] Use mocks que implementem a interface
- [ ] Teste sem dependências reais

```php
class MeuModuloMock implements MeuModuloInterface { ... }

// Teste sem precisar de classes reais
$moduloReal = new OutroModulo(new MeuModuloMock());
```

---

## 🚫 Antipadrões a Evitar

### ❌ Errado: Acoplar classes concretas
```php
use api\modules\MeuModulo\MeuModulo; // ERRADO!

class Outro {
    public function __construct(MeuModulo $modulo) { } // Acoplado!
}
```

### ✅ Correto: Depender de interfaces
```php
use api\contracts\MeuModuloInterface; // Correto!

class Outro {
    public function __construct(MeuModuloInterface $modulo) { } // Desacoplado!
}
```

---

## 🏗️ Estrutura de Pastas

```
api/src/
├── contracts/
│   ├── PessoaInterface.php
│   ├── SocioInterface.php
│   ├── MeuModuloInterface.php      ← Novas interfaces aqui
│   └── ARCHITECTURE.md
│
├── modules/
│   ├── Pessoa/
│   │   ├── Pessoa.php              ← Implementa PessoaInterface
│   │   └── [outras classes]
│   ├── Socio/
│   │   ├── Socio.php               ← Implementa SocioInterface
│   │   └── [outras classes]
│   └── MeuModulo/                  ← Novo módulo
│       ├── MeuModulo.php           ← Implementa MeuModuloInterface
│       ├── Auxiliar.php            ← Classes internas
│       └── [outras classes]
│
├── Infrastructure/
│   ├── ObjectFactory.php            ← Factory para criar objetos
│   └── [Serviços compartilhados]
│
└── [Outras pastas]
```

---

## 🔄 Fluxo de Criação de Módulo (Resumido)

1. **Defina a interface** em `contracts/`
2. **Implemente a classe** em `modules/SeuModulo/`
3. **Injete interfaces** em dependências
4. **Use o Factory** ou injeção manual
5. **Escreva testes** com mocks

---

## 💡 Exemplo Prático Completo

### Passo 1: Interface
```php
// api/src/contracts/RepositorioInterface.php
namespace api\contracts;

interface RepositorioInterface
{
    public function buscarPorId(int $id): mixed;
    public function salvar(object $entidade): int;
}
```

### Passo 2: Implementação
```php
// api/src/modules/Repositorio/RepositorioDB.php
namespace api\modules\Repositorio;

use api\contracts\RepositorioInterface;

class RepositorioDB implements RepositorioInterface
{
    public function buscarPorId(int $id): mixed { ... }
    public function salvar(object $entidade): int { ... }
}
```

### Passo 3: Uso em Outro Módulo
```php
// api/src/modules/Socio/Socio.php
use api\contracts\RepositorioInterface;

class Socio
{
    private RepositorioInterface $repositorio;
    
    public function __construct(RepositorioInterface $repositorio)
    {
        $this->repositorio = $repositorio; // Desacoplado!
    }
    
    public function buscar(int $id)
    {
        return $this->repositorio->buscarPorId($id);
    }
}
```

### Passo 4: Teste com Mock
```php
class RepositorioMock implements RepositorioInterface
{
    public function buscarPorId(int $id): mixed
    {
        return (object)['id' => $id, 'nome' => 'Mock'];
    }
    
    public function salvar(object $entidade): int { return 1; }
}

// Teste isolado
$socio = new Socio(new RepositorioMock());
$resultado = $socio->buscar(1);
```

---

## 📚 Referências

- [SOLID Principles](https://en.wikipedia.org/wiki/SOLID)
- [Dependency Inversion Principle](https://en.wikipedia.org/wiki/Dependency_inversion_principle)
- [Interface Segregation](https://en.wikipedia.org/wiki/Interface_segregation_principle)
- [Dependency Injection](https://refactoring.guru/design-patterns/dependency-injection)

---

## ❓ Dúvidas Frequentes

**P: Quando criar uma interface?**
R: Quando a classe será dependência em outro módulo ou quando você quer permitir múltiplas implementações.

**P: E se não preciso de múltiplas implementações?**
R: Mesmo assim, crie a interface. Facilita testes, manutenção futura e segue o padrão da arquitetura.

**P: Devo criar interface para Value Objects?**
R: Não, Value Objects simples (como Data, CPF) não precisam de interfaces.

**P: Como estruturar o Factory?**
R: Crie métodos estáticos para cada tipo de objeto que precisa injeção complexa.

**P: Posso usar instâncias globais/singletons?**
R: Evite. A injeção de dependência é mais testável e mantível.
