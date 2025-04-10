<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'SistemaLog.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Conexao.php';

class SistemaLogDAO
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        if (is_null($pdo)) {
            $this->pdo = Conexao::connect();
            return;
        }

        $this->pdo = $pdo;
    }

    /**
     * Realiza o registro de uma ação no sistema no banco de dados da aplicação
     */
    public function registrar(SistemaLog $sistemaLog){
        $sql = 'INSERT INTO sistema_log (id_pessoa, id_recurso, id_acao, descricao, data) VALUES (:idPessoa, :idRecurso, :idAcao, :descricao, :data)';

        $stmt = $this->pdo->prepare($sql);

        $stmt->bindValue(':idPessoa', $sistemaLog->getIdPessoa());
        $stmt->bindValue(':idRecurso', $sistemaLog->getIdRecurso());
        $stmt->bindValue(':idAcao', $sistemaLog->getIdAcao());
        $stmt->bindValue(':descricao', $sistemaLog->getDescricao());
        $stmt->bindValue(':data', $sistemaLog->getData());

        return $stmt->execute();
    }
}
