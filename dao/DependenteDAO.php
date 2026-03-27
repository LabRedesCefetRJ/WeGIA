<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Dependente.php';

class DependenteDAO
{
    private $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?: Conexao::connect();
    }

    public function alterarInfoPessoal(Dependente $dependente)
    {
        $stmt = $this->pdo->prepare("UPDATE pessoa p
            JOIN funcionario_dependentes fd 
                ON p.id_pessoa = fd.id_pessoa
            SET p.nome = :nome,
                p.sobrenome = :sobrenome,
                p.sexo = :sexo,
                p.data_nascimento = :nascimento,
                p.telefone = :telefone,
                p.nome_pai = :nome_pai,
                p.nome_mae = :nome_mae
            WHERE fd.id_dependente = :id_dependente;
        ");

        $stmt->bindValue(':nome', $dependente->getNome(), PDO::PARAM_STR);
        $stmt->bindValue(':sobrenome', $dependente->getSobrenome(), PDO::PARAM_STR);
        $stmt->bindValue(':sexo', $dependente->getSexo(), PDO::PARAM_STR_CHAR);
        $stmt->bindValue(':nascimento', $dependente->getDataNascimento()->format('Y-m-d'), PDO::PARAM_STR);
        $stmt->bindValue(':telefone', $dependente->getTelefone());
        $stmt->bindValue(':nome_pai', $dependente->getNomePai());
        $stmt->bindValue(':nome_mae', $dependente->getNomeMae());
        $stmt->bindValue(':id_dependente', $dependente->getId(), PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function buscarPorId(int $id_dependente): ?array
    {
        $sql = "SELECT fdep.*, 
                   p.cpf, p.nome, p.sobrenome, p.data_nascimento, p.sexo, p.telefone, p.data_nascimento, p.cep, p.estado, p.cidade, p.bairro, p.logradouro, p.numero_endereco, p.complemento, p.ibge, p.registro_geral, p.orgao_emissor, p.data_expedicao, p.nome_pai, p.nome_mae, 
                   par.descricao AS parentesco,
                   f2.nome AS nomefuncionario, f2.sobrenome AS sobrenomefuncionario
            FROM funcionario_dependentes fdep
            LEFT JOIN pessoa p ON p.id_pessoa = fdep.id_pessoa
            LEFT JOIN funcionario_dependente_parentesco par ON par.id_parentesco = fdep.id_parentesco
            JOIN funcionario f ON fdep.id_funcionario = f.id_funcionario
            JOIN pessoa f2 ON f.id_pessoa = f2.id_pessoa
            WHERE fdep.id_dependente = :id_dependente"; //pegar restante das informações

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id_dependente', $id_dependente, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function alterarDocumentacao(int $id_dependente, string $rg, string $orgao_emissor, ?string $data_expedicao, string $cpf): bool
    {
        $sql = "UPDATE pessoa p 
            JOIN funcionario_dependentes fd 
                ON p.id_pessoa = fd.id_pessoa
            SET 
                p.registro_geral = :rg, 
                p.orgao_emissor = :orgao_emissor, 
                p.data_expedicao = :data_expedicao,
                p.cpf = :cpf
            WHERE fd.id_dependente = :id_dependente;
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':rg', $rg);
        $stmt->bindParam(':orgao_emissor', $orgao_emissor);
        $stmt->bindParam(':data_expedicao', $data_expedicao);
        $stmt->bindParam(':cpf', $cpf);
        $stmt->bindParam(':id_dependente', $id_dependente);

        return $stmt->execute();
    }

    //adaptar para receber DTO
    public function editarEndereco(int $id_dependente, string $cep, string $uf, string $cidade, string $bairro, string $rua, string $complemento, int $ibge, ?int $numero_residencia)
    {
        $query = 'UPDATE pessoa p 
            JOIN funcionario_dependentes fd 
                ON p.id_pessoa = fd.id_pessoa 
            SET 
                p.cep=:cep, 
                p.estado=:uf, 
                p.cidade=:cidade, 
                p.bairro=:bairro, 
                p.logradouro=:rua, 
                p.complemento=:complemento, 
                p.ibge=:ibge, 
                p.numero_endereco=:numero_residencia 
            WHERE fd.id_dependente=:id_dependente
        ';

        $stmt = $this->pdo->prepare($query);

        $stmt->bindParam(':cep', $cep);
        $stmt->bindParam(':uf', $uf);
        $stmt->bindParam(':cidade', $cidade);
        $stmt->bindParam(':bairro', $bairro);
        $stmt->bindParam(':rua', $rua);
        $stmt->bindParam(':complemento', $complemento);
        $stmt->bindParam(':ibge', $ibge);
        $stmt->bindParam(':numero_residencia', $numero_residencia);
        $stmt->bindParam(':id_dependente', $id_dependente);

        $stmt->execute();
    }
}
