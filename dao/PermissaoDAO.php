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
    public function adicionarPermissao(int $cargo, int $acao, array $recursos):bool
    {
        $query = "INSERT INTO `permissao` (`id_cargo`, `id_acao`, `id_recurso`) VALUES (:cargo, :acao, :recursoId)";

        foreach ($recursos as $recursoId) {
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':cargo', $cargo, PDO::PARAM_INT);
            $stmt->bindParam(':acao', $acao, PDO::PARAM_INT);
            $stmt->bindParam(':recursoId', $recursoId, PDO::PARAM_INT);
            $stmt->execute();
        }

        if($stmt->rowCount() < 1){
            return false;
        }

        return true;
    }
}
