<?php
if (session_status() === PHP_SESSION_NONE)
    session_start();

extract($_REQUEST);

if (!isset($_SESSION["usuario"])) {
    header("Location: ../../index.php");
    exit();
}else{
    session_regenerate_id();
}

require_once '../permissao/permissao.php';
permissao($_SESSION['id_pessoa'], 5, 7);

if ($_POST) {
    require_once "../../dao/Conexao.php";
    $pdo = Conexao::connect();

    try {
        // Obter ID do funcionário
        $stmt = $pdo->prepare("SELECT id_funcionario FROM pessoa p JOIN funcionario f ON p.id_pessoa = f.id_pessoa WHERE f.id_pessoa = :id_pessoa");
        $stmt->bindValue(':id_pessoa', $_SESSION['id_pessoa']);
        $stmt->execute();
        $funcionario = $stmt->fetchColumn();

        if (!$funcionario) {
            throw new Exception("Funcionário não encontrado.");
        }

        date_default_timezone_set('America/Sao_Paulo');
        $data_registro = date('Y-m-d');

        // Buscar dados completos do funcionário
        $stmt = $pdo->prepare("SELECT * FROM funcionario WHERE id_funcionario = :id_funcionario");
        $stmt->bindValue(':id_funcionario', $funcionario);
        $stmt->execute();
        $registro_funcionario = $stmt->fetch(PDO::FETCH_NUM);
        $id_funcionario = $registro_funcionario[0];
        $nome_funcionario = $registro_funcionario[1];

        // Inserir atendimento
        $stmt = $pdo->prepare("INSERT INTO saude_atendimento (id_fichamedica, id_funcionario, data_atendimento, descricao, id_medico, data_registro)
                               VALUES (:id_fichamedica, :id_funcionario, :data_atendimento, :descricao, :id_medico, :data_registro)");
        $stmt->bindValue(':id_fichamedica', $id_fichamedica);
        $stmt->bindValue(':id_funcionario', $id_funcionario);
        $stmt->bindValue(':data_atendimento', $data_atendimento);
        $stmt->bindValue(':descricao', $texto);
        $stmt->bindValue(':id_medico', $medicos);
        $stmt->bindValue(':data_registro', $data_registro);
        $stmt->execute();

        // Buscar id do atendimento recém-inserido
        $stmt = $pdo->prepare("SELECT id_atendimento FROM saude_atendimento WHERE id_fichamedica = :id_fichamedica AND id_funcionario = :id_funcionario AND data_atendimento = :data_atendimento AND descricao = :descricao ORDER BY id_atendimento DESC LIMIT 1");
        $stmt->bindValue(':id_fichamedica', $id_fichamedica);
        $stmt->bindValue(':id_funcionario', $id_funcionario);
        $stmt->bindValue(':data_atendimento', $data_atendimento);
        $stmt->bindValue(':descricao', $texto);
        $stmt->execute();
        $id_atendimento = $stmt->fetchColumn();

        if (!$id_atendimento) {
            throw new Exception("Atendimento não encontrado após inserção.");
        }

        // Inserir medicações
        $obj_post = $_POST['acervo'];
        $obj = json_decode($obj_post, true);

        foreach ($obj as $med) {
            $stmt = $pdo->prepare("INSERT INTO saude_medicacao (id_atendimento, medicamento, dosagem, horario, duracao, saude_medicacao_status_idsaude_medicacao_status)
                                   VALUES (:id_atendimento, :medicamento, :dosagem, :horario, :duracao, :status)");
            $stmt->bindValue(':id_atendimento', $id_atendimento);
            $stmt->bindValue(':medicamento', $med["nome_medicacao"]);
            $stmt->bindValue(':dosagem', $med["dosagem"]);
            $stmt->bindValue(':horario', $med["horario"]);
            $stmt->bindValue(':duracao', $med["tempo"]);
            $stmt->bindValue(':status', 1);
            $stmt->execute();
        }

        header("Location: profile_paciente.php?id_fichamedica=$id_fichamedica");
        exit();
    } catch (Exception $e) {
        echo "Erro ao cadastrar atendimento e medicação: <br>" . $e->getMessage();
        exit();
    }
} else {
    header("Location: profile_paciente.php");
    exit();
}
