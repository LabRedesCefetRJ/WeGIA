<?php
class AlergiaDAO
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function listarTodasAsAlergias()
    {
        $sql = "SELECT * FROM saude_tabelacid WHERE CID LIKE 'T78.4.%' ORDER BY descricao ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        $resultado = array();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as $row) {
            $resultado[] = array(
                'id_CID' => $row['id_CID'],
                'CID' => $row['CID'],
                'descricao' => htmlspecialchars($row['descricao'])
            );
        }

        return $resultado;
    }
}
