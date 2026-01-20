<?php

class DependenteDAO
{
   public function alterarInfoPessoal(
    int $id_dependente, 
    string $nome, 
    string $sobrenome, 
    string $sexo, 
    ?string $nascimento, 
    ?string $telefone, 
    ?string $nome_pai, 
    ?string $nome_mae
) {
    $pdo = Conexao::connect();
    
    $stmt = $pdo->prepare("SELECT id_pessoa FROM funcionario_dependentes WHERE id_dependente = :id");
    $stmt->bindValue(':id', $id_dependente, PDO::PARAM_INT);
    $stmt->execute();
    $id_pessoa = $stmt->fetchColumn();
    
    if (!$id_pessoa) {
        throw new PDOException("Dependente não encontrado");
    }
    
    $stmt = $pdo->prepare("UPDATE pessoa SET 
        nome = :nome, 
        sobrenome = :sobrenome, 
        sexo = :sexo, 
        data_nascimento = :nascimento 
        WHERE id_pessoa = :id_pessoa");
    $stmt->bindValue(':nome', trim($nome));
    $stmt->bindValue(':sobrenome', trim($sobrenome));
    $stmt->bindValue(':sexo', $sexo);
    $stmt->bindValue(':nascimento', $nascimento);
    $stmt->bindValue(':id_pessoa', $id_pessoa, PDO::PARAM_INT);
    
    $pessoa_ok = $stmt->execute();
    
    $stmt = $pdo->prepare("UPDATE funcionario_dependentes SET 
        telefone = :telefone, 
        nome_pai = :nome_pai, 
        nome_mae = :nome_mae 
        WHERE id_dependente = :id_dependente");
    $stmt->bindValue(':telefone', $telefone);
    $stmt->bindValue(':nome_pai', trim($nome_pai));
    $stmt->bindValue(':nome_mae', trim($nome_mae));
    $stmt->bindValue(':id_dependente', $id_dependente, PDO::PARAM_INT);
    
    $dependente_ok = $stmt->execute();
    
    return $pessoa_ok && $dependente_ok;
}


    public function buscarPorId(int $id_dependente): ?array
    {
        $pdo = Conexao::connect();

        $sql = "SELECT fdep.*, 
                   p.nome, p.sobrenome, p.data_nascimento,
                   par.descricao AS parentesco,
                   f2.nome AS nomefuncionario, f2.sobrenome AS sobrenomefuncionario
            FROM funcionario_dependentes fdep
            LEFT JOIN pessoa p ON p.id_pessoa = fdep.id_pessoa
            LEFT JOIN funcionario_dependentes_parentesco par ON par.idparentesco = fdep.id_parentesco
            JOIN funcionario f ON fdep.id_funcionario = f.id_funcionario
            JOIN pessoa f2 ON f.id_pessoa = f2.id_pessoa
            WHERE fdep.id_dependente = :id_dependente";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id_dependente', $id_dependente, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}
