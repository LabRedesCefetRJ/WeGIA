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
