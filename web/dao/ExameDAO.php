<?php

require_once 'Conexao.php';

class ExameDAO
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Conexao::connect();
    }

    public function inserirTipoExame($exame)
    {
        try {
            $pdo = Conexao::connect();
            $sql = "INSERT INTO saude_exame_tipos(descricao) VALUES(:situacao)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':situacao', $exame);
            $stmt->execute();

            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function retornaArquivoPorId($id){
        try{
            $sql = 'SELECT arquivo_nome, arquivo_extensao, arquivo FROM saude_exames WHERE id_exame = :id';
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->execute();

            $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($resultado) {
                return $resultado;
            }

        }catch(PDOException $e){
            echo json_encode(["erro" => $e->getMessage()]);
        }
    }

    public function listarTodosTiposDeExame(){
            $sql = 'SELECT * FROM saude_exame_tipos ORDER BY descricao';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            
            $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $resultado;
    }

    public function listarExamesPorId($id_fichamedica){

        $sql = "SELECT * FROM saude_exames se JOIN saude_exame_tipos st ON se.id_exame_tipos  = st.id_exame_tipo WHERE id_fichamedica=:idFichamedica";
        $stmt = $this->pdo->prepare($sql);

        $stmt->bindValue(':idFichamedica', $id_fichamedica, PDO::PARAM_INT);
        $stmt->execute();

        $docfuncional = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $docfuncional;
    }

    public function inserirExame($id_fichamedica, $id_exame_tipos, $dataExame, $arquivo_nome, $extensao_nome, $arquivo_b64){
        try{
            $sql = "INSERT INTO saude_exames 
                (id_fichamedica, id_exame_tipos, data, arquivo_nome, arquivo_extensao, arquivo) 
                VALUES (:id_fichamedica, :id_exame_tipos, :data, :arquivo_nome, :arquivo_extensao, :arquivo)";
            $stmt = $this->pdo->prepare($sql);

            $stmt->bindValue(":id_fichamedica", $id_fichamedica);
            $stmt->bindValue(":id_exame_tipos", $id_exame_tipos);
            $stmt->bindValue(":data", $dataExame);
            $stmt->bindValue(":arquivo_nome", $arquivo_nome);
            $stmt->bindValue(":arquivo_extensao", $extensao_nome);
            $stmt->bindValue(":arquivo", gzcompress($arquivo_b64));
            $stmt->execute();

            return true;
        }catch(PDOException $e){
            return false;
        }
    }

    public function removerExame($id_exame){
        try{
            $sql = "DELETE FROM saude_exames WHERE id_exame = :id_exame";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(":id_exame", $id_exame);
            $stmt->execute();

            return true;
        }catch(PDOException $e){
            return false;
        }
    }

}
