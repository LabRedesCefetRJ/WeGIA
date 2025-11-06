<?php

$config_path = "config.php";
if (file_exists($config_path)) {
    require_once($config_path);
} else {
    while (true) {
        $config_path = "../" . $config_path;
        if (file_exists($config_path)) break;
    }
    require_once($config_path);
}
require_once ROOT . "/dao/Conexao.php";
require_once ROOT . "/classes/Saude.php";
require_once ROOT . "/Functions/funcoes.php";

class SaudeDAO
{
    public function incluir($saude)
    {
        try {
            $sql = "INSERT INTO saude_fichamedica(id_pessoa) VALUES (:id_pessoa)"; //continuar daqui...
            $pdo = Conexao::connect();

            $pdo->beginTransaction();

            $stmt = $pdo->prepare($sql);

            $idPessoa = $saude->getNome();
            $stmt->bindParam(':id_pessoa', $idPessoa);

            if ($stmt->execute()) {
                $pdo->commit();
            } else {
                $pdo->rollBack();
            }

        } catch (PDOException $e) {
            echo 'Error: <b>  na tabela pessoas = ' . $sql . '</b> <br /><br />' . $e->getMessage();
        } finally {
            $pdo = null;
        }
    }
    public function alterarImagem($id_fichamedica, $imagem)
    {
        $imagem = base64_encode($imagem);
        try {
            $pdo = Conexao::connect();
            $id_pessoa = (($pdo->query("SELECT id_pessoa FROM saude_fichamedica WHERE id_fichamedica=$id_fichamedica"))->fetch(PDO::FETCH_ASSOC))["id_pessoa"];

            $sql = "UPDATE pessoa SET imagem = :imagem WHERE id_pessoa = :id_pessoa;";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id_pessoa', $id_pessoa);
            $stmt->bindValue(':imagem', $imagem);
            $stmt->execute();
        } catch (PDOException $e) {
            echo 'Error: <b>  na tabela pessoa = ' . $sql . '</b> <br /><br />' . $e->getMessage();
        }
    }
    public function alterar($saude)
    { 
        try {
            $sql = 'update pessoa as p inner join saude_fichamedica as sf on p.id_pessoa=sf.id_pessoa set p.imagem=:imagem where sf.id_pessoa=:id_pessoa';

            $sql = str_replace("'", "\'", $sql);
            $pdo = Conexao::connect();
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        } catch (PDOException $e) {
            echo 'Error: <b>  na tabela pessoas = ' . $sql . '</b> <br /><br />' . $e->getMessage();
        }
    }


    public function listarTodos()
    {

        try {
            $pacientes = array();
            $pdo = Conexao::connect();
            $consulta = $pdo->query("SELECT s.id_fichamedica, p.imagem, p.nome, p.sobrenome 
                FROM pessoa p 
                INNER JOIN saude_fichamedica s ON s.id_pessoa = p.id_pessoa 
                LEFT JOIN atendido a ON a.pessoa_id_pessoa = p.id_pessoa 
                LEFT JOIN funcionario f ON f.id_pessoa = p.id_pessoa
                WHERE 
                (a.atendido_status_idatendido_status IS NULL OR a.atendido_status_idatendido_status != 2)
                AND (f.id_situacao IS NULL OR f.id_situacao != 2);
            ");
            $x = 0;
            while ($linha = $consulta->fetch(PDO::FETCH_ASSOC)) {

                $pacientes[$x] = array('id_fichamedica' => $linha['id_fichamedica'], 'imagem' => $linha['imagem'], 'nome' => $linha['nome'], 'sobrenome' => $linha['sobrenome']);
                $x++;
            }
        } catch (PDOException $e) {
            echo 'Error:' . $e->getMessage();
        }
        return json_encode($pacientes);
    }

    public function listar($id)
    {
        try {
            echo $id;
            $pdo = Conexao::connect();

            $sql = "SELECT p.nome,p.sobrenome,p.imagem,p.sexo,p.data_nascimento,p.tipo_sanguineo FROM pessoa p 
            JOIN saude_fichamedica sf ON p.id_pessoa = sf.id_pessoa 
            WHERE sf.id_fichamedica=:id";

            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id', $id);

            $stmt->execute();
            $paciente = array();
            while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {

                $paciente[] = array('nome' => $linha['nome'], 'imagem' => $linha['imagem'], 'sobrenome' => $linha['sobrenome'], 'sexo' => $linha['sexo'], 'data_nascimento' => $linha['data_nascimento'], 'tipo_sanguineo' => $linha['tipo_sanguineo']);

            }
        } catch (PDOException $e) {
            echo 'Error: ' .  $e->getMessage();
        }
        return json_encode($paciente);
    }

    public function alterarInfPessoal($paciente)
    {
        try {
            $sql = 'update pessoa as p inner join saude_fichamedica as s on p.id_pessoa=s.id_pessoa set tipo_sanguineo=:tipo_sanguineo where id_fichamedica=:id_fichamedica';

            $sql = str_replace("'", "\'", $sql);
            $pdo = Conexao::connect();
            $stmt = $pdo->prepare($sql);
            $stmt = $pdo->prepare($sql);
            $id_fichamedica = $paciente->getId_pessoa();
            $tipoSanguineo = $paciente->getTipoSanguineo();
            $stmt->bindParam(':id_fichamedica', $id_fichamedica);
            $stmt->bindParam(':tipo_sanguineo', $tipoSanguineo);
            $stmt->execute();
        } catch (PDOException $e) {
            echo 'Error: <b>  na tabela pessoas = ' . $sql . '</b> <br /><br />' . $e->getMessage();
        }
    }

    public function adicionarProntuarioAoHistorico($idFicha, $idPaciente)
    {
        $sql2 = "INSERT INTO saude_fichamedica_historico (id_pessoa, data) VALUES (:idPessoa, :data)";

        try {
            $pdo = Conexao::connect();

            date_default_timezone_set('America/Sao_Paulo');
            $data = date('Y-m-d H:i:s');

            $pdo->beginTransaction();
            $stmt2 = $pdo->prepare($sql2);
            $stmt2->bindParam(':idPessoa', $idPaciente);
            $stmt2->bindParam(':data', $data);
            $stmt2->execute();

            $ultimoID = $pdo->lastInsertId();

            if ($this->insercaoDescricaoHistoricoEmCadeia($idFicha, $pdo, $ultimoID)) {
                $pdo->commit();
            } else {
                $pdo->rollBack();
            }
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function insercaoDescricaoHistoricoEmCadeia($idFicha, PDO $pdo, $idFichaHistorico)
    {
        $sql1 = "SELECT descricao from saude_fichamedica_descricoes WHERE id_fichamedica=:idFicha";
        $sql2 = "INSERT INTO saude_fichamedica_historico_descricoes (id_fichamedica_historico, descricao) VALUES (:idFichaHistorico, :descricao)";
        try {
            $stmt1 = $pdo->prepare($sql1);
            $stmt1->bindParam(':idFicha', $idFicha);
            $stmt1->execute();

            $descricoes = $stmt1->fetchAll(PDO::FETCH_ASSOC);
            $stmt2 = $pdo->prepare($sql2);

            foreach ($descricoes as $descricao) {
                $texto = $descricao['descricao'];
                $stmt2->bindParam(":idFichaHistorico", $idFichaHistorico);
                $stmt2->bindParam(":descricao", $texto);
                $stmt2->execute();
            }
            return true;
        } catch (PDOException $e) {
            echo $e->getMessage();
            return false;
        }
    }

    public function listarProntuariosDoHistorico($idPaciente)
    {
        $sql = 'SELECT id_fichamedica_historico as idHistorico, data FROM saude_fichamedica_historico WHERE id_pessoa=:idPaciente';

        try {
            $pdo = Conexao::connect();

            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':idPaciente', $idPaciente);
            $stmt->execute();

            $prontuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $prontuarios;
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function listarDescricoesHistoricoPorId($idHistorico)
    {
        $sql = 'SELECT descricao FROM saude_fichamedica_historico_descricoes WHERE id_fichamedica_historico=:idHistorico';

        try {
            $pdo = Conexao::connect();

            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':idHistorico', $idHistorico);
            $stmt->execute();

            $descricoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $descricoes;
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
}
