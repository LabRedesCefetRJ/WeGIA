<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Dependente.php';

class DependenteDAO
{
    public function alterarInfoPessoal(Dependente $dependente)
    {
        $pdo = Conexao::connect();

        $stmt = $pdo->prepare("UPDATE pessoa p
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
        $pdo = Conexao::connect();

        $sql = "SELECT fdep.*, 
                   p.nome, p.sobrenome, p.data_nascimento, p.sexo, p.telefone, p.data_nascimento, p.cep, p.estado, p.cidade, p.bairro, p.logradouro, p.numero_endereco, p.complemento, p.ibge, p.registro_geral, p.orgao_emissor, p.data_expedicao, p.nome_pai, p.nome_mae, 
                   par.descricao AS parentesco,
                   f2.nome AS nomefuncionario, f2.sobrenome AS sobrenomefuncionario
            FROM funcionario_dependentes fdep
            LEFT JOIN pessoa p ON p.id_pessoa = fdep.id_pessoa
            LEFT JOIN funcionario_dependente_parentesco par ON par.id_parentesco = fdep.id_parentesco
            JOIN funcionario f ON fdep.id_funcionario = f.id_funcionario
            JOIN pessoa f2 ON f.id_pessoa = f2.id_pessoa
            WHERE fdep.id_dependente = :id_dependente"; //pegar restante das informações

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id_dependente', $id_dependente, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function alterarDocumentacao(int $id_pessoa, string $rg, string $orgao_emissor, ?string $data_expedicao, string $cpf): bool
    {
        $pdo = Conexao::connect();

        $sql = "UPDATE pessoa SET 
        registro_geral = :rg, 
        orgao_emissor = :orgao_emissor, 
        data_expedicao = :data_expedicao, 
        cpf = :cpf 
        WHERE id_pessoa = :id_pessoa";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':rg', $rg);
        $stmt->bindParam(':orgao_emissor', $orgao_emissor);
        $stmt->bindParam(':data_expedicao', $data_expedicao);
        $stmt->bindParam(':cpf', $cpf);
        $stmt->bindParam(':id_pessoa', $id_pessoa);

        return $stmt->execute();
    }
}
