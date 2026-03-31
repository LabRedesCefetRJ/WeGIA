<?php

require_once 'Conexao.php';
require_once '../classes/Aviso.php';

class AvisoDAO
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Conexao::connect();
    }

    /**
     * Recebe um objeto do tipo Aviso como parâmetro, extrai suas propriedades e realiza os procedimentos necessários para realizar o cadastro dele no banco de dados da aplicação
     */
    public function cadastrar(Aviso $aviso)
    {
        $sql = 'INSERT INTO aviso(
                    id_funcionario_aviso,
                    id_pessoa_atendida,
                    descricao,
                    data,
                    id_saude_equipe_plantao,
                    id_saude_escala_dia,
                    data_plantao,
                    turno_plantao
                ) VALUES (
                    :idFuncionario,
                    :idPessoaAtendida,
                    :descricao,
                    :data,
                    :idEquipePlantao,
                    :idEscalaDia,
                    :dataPlantao,
                    :turnoPlantao
                );';

        $idFuncionario = $aviso->getIdFuncionario();
        $idPessoaAtendida = $aviso->getIdPessoaAtendida();
        $descricao = $aviso->getDescricao();
        $idEquipePlantao = $aviso->getIdEquipePlantao();
        $idEscalaDia = $aviso->getIdEscalaDia();
        $dataPlantao = $aviso->getDataPlantao();
        $turnoPlantao = $aviso->getTurnoPlantao();

        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare($sql);

            $stmt->bindParam(':idFuncionario', $idFuncionario);
            $stmt->bindParam(':idPessoaAtendida', $idPessoaAtendida);
            $stmt->bindParam(':descricao', $descricao);
            $stmt->bindValue(':idEquipePlantao', $idEquipePlantao, is_null($idEquipePlantao) ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindValue(':idEscalaDia', $idEscalaDia, is_null($idEscalaDia) ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindValue(':dataPlantao', $dataPlantao, is_null($dataPlantao) ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->bindValue(':turnoPlantao', $turnoPlantao, is_null($turnoPlantao) ? PDO::PARAM_NULL : PDO::PARAM_STR);

            date_default_timezone_set('America/Sao_Paulo');
            $data = date('Y-m-d H:i:s');
            $stmt->bindParam(':data', $data);

            $stmt->execute();

            $ultimoID = $this->pdo->lastInsertId();
            $informacoesUltimoId = $this->procuraPorID($ultimoID);

            if ($informacoesUltimoId['id_funcionario_aviso'] == $idFuncionario && $informacoesUltimoId['id_pessoa_atendida'] == $idPessoaAtendida && $informacoesUltimoId['descricao'] == $descricao) {

                $this->pdo->commit();
                return $ultimoID;
            } else {
                $this->pdo->rollBack();
            }
        } catch (PDOException $e) {
            echo 'Erro ao cadastrar uma intercorrência no banco de dados: '.$e->getMessage();
            throw $e;
        }
    }

    /**
     * Recebe como parâmetro um número inteiro, retorna as informações de um aviso no banco de dados que possua o mesmo id que o parâmetro recebido;
     */
    public function procuraPorID($id)
    {
        $sql = 'SELECT id_funcionario_aviso, id_pessoa_atendida, descricao, id_saude_equipe_plantao, id_saude_escala_dia, data_plantao, turno_plantao FROM aviso WHERE id_aviso=:id';

        try {

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado;
        } catch (PDOException $e) {
            echo 'Erro ao procurar uma intercorrência com o id fornecido: '.$e->getMessage();
        }
    }

     public function listarIntercorrenciaPorIdDaFichaMedica($id, ?int $idEquipePlantao = null){
        try{
            $sql = "
                SELECT
                    a.id_aviso,
                    a.descricao,
                    a.data,
                    a.data_plantao,
                    COALESCE(sed.turno, a.turno_plantao) AS turno_plantao,
                    COALESCE(sed.id_equipe_plantao, a.id_saude_equipe_plantao) AS id_saude_equipe_plantao,
                    a.id_saude_escala_dia,
                    sep.nome AS equipe_nome
                FROM aviso a
                JOIN saude_fichamedica sf on ( sf.id_fichamedica = :id_fichamedica)
                LEFT JOIN saude_escala_dia sed ON sed.id_escala_dia = a.id_saude_escala_dia
                LEFT JOIN saude_equipe_plantao sep ON sep.id_equipe_plantao = COALESCE(sed.id_equipe_plantao, a.id_saude_equipe_plantao)
                WHERE a.id_pessoa_atendida = sf.id_pessoa
            ";

            if (!is_null($idEquipePlantao) && $idEquipePlantao > 0) {
                $sql .= " AND COALESCE(sed.id_equipe_plantao, a.id_saude_equipe_plantao) = :idEquipePlantao ";
            }

            $sql .= " ORDER BY a.data DESC ";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id_fichamedica', $id, PDO::PARAM_INT);

            if (!is_null($idEquipePlantao) && $idEquipePlantao > 0) {
                $stmt->bindValue(':idEquipePlantao', $idEquipePlantao, PDO::PARAM_INT);
            }

            $stmt->execute();
            $intercorrencias = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $intercorrencias;
        } catch (PDOException $e){
            echo 'Erro ao procurar uma intercorrência com o id da ficha médica fornecida: '.$e->getMessage();
        }
        
    }
}
