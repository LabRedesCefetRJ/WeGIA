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
require_once ROOT . "/classes/Funcionario.php";
require_once ROOT . "/Functions/funcoes.php";

class PermissaoDAO
{
    private PDO $pdo;
    public function __construct(PDO $pdo = null)
    {
        if (!is_null($pdo)) {
            $this->pdo = $pdo;
        } else {
            $this->pdo = Conexao::connect();
        }
    }
    
    public function adicionarPermissao(int $cargo, int $acao, array $recursos): bool
    {
        if (empty($recursos)) {
            return false;
        }

        // Criar placeholders: (?,?,?), (?,?,?), ...
        $placeholders = implode(',', array_fill(0, count($recursos), '(?,?,?)'));

        $sql = "INSERT IGNORE INTO permissao (id_cargo, id_acao, id_recurso) VALUES $placeholders";
        $stmt = $this->pdo->prepare($sql);

        // Montar parâmetros: [cargo, acao, recurso1, cargo, acao, recurso2, ...]
        $params = [];
        foreach ($recursos as $recursoId) {
            $params[] = $cargo;
            $params[] = $acao;
            $params[] = $recursoId;
        }

        $stmt->execute($params);

        // rowCount retorna quantas linhas realmente foram inseridas (ignora duplicatas)
        return $stmt->rowCount() > 0;
    }


    public function getPermissoesByCargo(int $idCargo)
    {
        $sql = 'SELECT * FROM permissao WHERE id_cargo=:idCargo';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':idCargo', $idCargo);
        $stmt->execute();

        if ($stmt->rowCount() < 1) {
            return null;
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function removePermissoesByCargo(int $idCargo, array $recursos)
    {
        if (empty($recursos)) {
            return;
        }

        // placeholders dinâmicos para os recursos
        $placeholders = implode(',', array_fill(0, count($recursos), '?'));

        $sql = "DELETE FROM permissao 
            WHERE id_cargo = ? 
            AND id_recurso IN ($placeholders)";

        $stmt = $this->pdo->prepare($sql);

        // primeiro parâmetro é o idCargo, depois todos os recursos
        $params = array_merge([$idCargo], $recursos);

        $stmt->execute($params);
    }
}
