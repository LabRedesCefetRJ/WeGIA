<?php
require_once dirname(__DIR__) . '/classes/Enfermidade.php';

class EnfermidadeDAO{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    private function criarEnfermidade(array $enfermidade){
        return new Enfermidade($enfermidade['data_diagnostico'], $enfermidade['descricao'], $enfermidade['id_CID']);
    }

    public function getEnfermidadesAtivasPorFichaMedica($idFichaMedica){
        $enfermidades = [];

        $sql = "SELECT sf.id_CID, sf.data_diagnostico, sf.status, stc.descricao FROM saude_enfermidades sf JOIN saude_tabelacid stc ON sf.id_CID = stc.id_CID WHERE stc.CID NOT LIKE 'T78.4%' AND sf.status = 1 AND id_fichamedica=:idFichaMedica";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':idFichaMedica', $idFichaMedica);
        $stmt->execute();

        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach($resultado as $enfermidade){
            $enfermidades []= $this->criarEnfermidade($enfermidade);
        }

        return $enfermidades;
    }

    public function listarTodasAsEnfermidades() {
        $sql = "SELECT * FROM saude_tabelacid WHERE CID NOT LIKE 'T78.4%'";
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
}