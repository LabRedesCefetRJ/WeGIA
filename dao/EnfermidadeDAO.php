<?php
class EnfermidadeDAO
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function cadastrarEnfermidadeNaFichaMedica($id_CID, $id_fichamedica, $data_diagnostico, $intStatus)
    {
        $stmt = $this->pdo->prepare("INSERT INTO saude_enfermidades(id_fichamedica, id_CID, data_diagnostico, status) VALUES (:id_fichamedica, :id_CID, :data_diagnostico, :status)");

        $stmt->bindValue(":id_fichamedica", $id_fichamedica);
        $stmt->bindValue(":id_CID", $id_CID);
        $stmt->bindValue(":data_diagnostico", $data_diagnostico);
        $stmt->bindValue(":status", $intStatus);

        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return true;
        }
        return false;
    }

    public function getEnfermidadesAtivasPorFichaMedica($idFichaMedica)
    {
        $sql = "SELECT sf.id_enfermidade, sf.id_CID, sf.data_diagnostico, sf.status, stc.descricao FROM saude_enfermidades sf JOIN saude_tabelacid stc ON sf.id_CID = stc.id_CID WHERE stc.CID NOT LIKE 'T78.4%' AND sf.status = 1 AND id_fichamedica=:idFichaMedica";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':idFichaMedica', $idFichaMedica);
        $stmt->execute();

        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $resultado;
    }

    public function adicionarEnfermidade($enfermidadeNome, $enfermidadeCid)
    {
        $sql = "INSERT INTO saude_tabelacid(CID, descricao) VALUES (:enfermidadeCid, :enfermidadeNome)";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':enfermidadeCid', $enfermidadeCid);
        $stmt->bindParam('enfermidadeNome', $enfermidadeNome);
        $stmt->execute();
        if ($stmt->rowCount() > 0)
            return true;
        return false;
    }

    public function listarTodasAsEnfermidades()
    {
        $sql = "SELECT * FROM saude_tabelacid WHERE CID NOT LIKE 'T78.4%'ORDER BY descricao ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        $resultado = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $resultado[] = array(
                'id_CID' => $row['id_CID'],
                'CID' => $row['CID'],
                'descricao' => htmlspecialchars($row['descricao'])
            );
        }

        return $resultado;
    }

    public function tornarEnfermidadeInativa($id_enfermidade)
    {
        $sql = "UPDATE saude_enfermidades SET status = 0 WHERE id_enfermidade = :id_enfermidade";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id_enfermidade', $id_enfermidade);

        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return true;
        }
        return false;
    }

    public function inativarByIdCid(int $idEnfermidade, int $idFichaMedica): bool
    {
        $sql = "UPDATE saude_enfermidades
                SET status = 0
                WHERE id_enfermidade = :idEnfermidade
                  AND id_fichamedica = :idFichaMedica";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':idEnfermidade', $idEnfermidade, PDO::PARAM_INT);
        $stmt->bindParam(':idFichaMedica', $idFichaMedica, PDO::PARAM_INT);

        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return true;
        }
        return false;
    }
}
