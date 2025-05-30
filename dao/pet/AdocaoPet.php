<?php
$conexao = 'dao/Conexao.php';

if (file_exists($conexao)) {
    require_once $conexao;
} else {
    while (true) {
        $conexao = '../' . $conexao;
        if (file_exists($conexao)) {
            break;
        }
    }
    require_once $conexao;
}

class AdocaoPet {

    public function exibirAdotante($idPet) {
        $pdo = Conexao::connect();

        // Verifica se o pet foi adotado
        $stmt = $pdo->prepare("SELECT id_pessoa, data_adocao FROM pet_adocao WHERE id_pet = :id_pet");
        $stmt->bindValue(':id_pet', $idPet);
        $stmt->execute();
        $adocao = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$adocao) {
            return ['adotado' => false];
        }

        $id_pessoa = $adocao['id_pessoa'];
        $data_adocao = $adocao['data_adocao'];

        // Busca o nome do adotante
        $stmt = $pdo->prepare("SELECT nome, sobrenome FROM pessoa WHERE id_pessoa = :id");
        $stmt->bindValue(':id', $id_pessoa);
        $stmt->execute();
        $pessoa = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$pessoa) {
            return ['adotado' => false];
        }

        $nome_completo = $pessoa['nome'] . ' ' . $pessoa['sobrenome'];

        return [
            'adotado' => true,
            'nome' => $nome_completo,
            'id_pessoa' => $id_pessoa,
            'data_adocao' => $data_adocao
        ];
    }

    public function inserirAdocao($id_pet, $id_pessoa, $data_adocao) {
        $pdo = Conexao::connect();

        // Verifica se já existe registro de adoção
        $pd = $pdo->prepare("SELECT COUNT(*) AS total FROM pet_adocao WHERE id_pet = :id_pet");
        $pd->bindValue(':id_pet', $id_pet);
        $pd->execute();
        $total = $pd->fetchColumn();

        if ($total == 0) {
            $pd = $pdo->prepare("INSERT INTO pet_adocao (id_pessoa, id_pet, data_adocao) 
                                 VALUES (:id_pessoa, :id_pet, :data_adocao)");
        } else {
            $pd = $pdo->prepare("UPDATE pet_adocao 
                                 SET id_pessoa = :id_pessoa, data_adocao = :data_adocao 
                                 WHERE id_pet = :id_pet");
        }

        $pd->bindValue(":id_pessoa", $id_pessoa);
        $pd->bindValue(":id_pet", $id_pet);
        $pd->bindValue(":data_adocao", $data_adocao);
        $pd->execute();
    }

    public function nomeAdotantePorId($id_pessoa) {
        $pdo = Conexao::connect();
        $pd = $pdo->prepare("SELECT nome, sobrenome FROM pessoa WHERE id_pessoa = :id_pessoa");
        $pd->bindValue(":id_pessoa", $id_pessoa);
        $pd->execute();
        $p = $pd->fetch(PDO::FETCH_ASSOC);

        if ($p) {
            return $p['nome'] . ' ' . $p['sobrenome'];
        }

        return null;
    }

    public function excluirAdocao($id_pet) {
        $pdo = Conexao::connect();

        $pd = $pdo->prepare("DELETE FROM pet_adocao WHERE id_pet = :id_pet");
        $pd->bindValue(':id_pet', $id_pet);
        $pd->execute();
        return 1;
    }
}

$c = new AdocaoPet();
