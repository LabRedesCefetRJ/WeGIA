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
    public function registrar(SistemaLog $sistemaLog)
    {
        $sql = 'INSERT INTO sistema_log (id_pessoa, id_recurso, id_acao, descricao, data) VALUES (:idPessoa, :idRecurso, :idAcao, :descricao, :data)';

        $stmt = $this->pdo->prepare($sql);

        $stmt->bindValue(':idPessoa', $sistemaLog->getIdPessoa());
        $stmt->bindValue(':idRecurso', $sistemaLog->getIdRecurso());
        $stmt->bindValue(':idAcao', $sistemaLog->getIdAcao());
        $stmt->bindValue(':descricao', $sistemaLog->getDescricao());
        $stmt->bindValue(':data', $sistemaLog->getData());

        return $stmt->execute();
    }

    /**
     * Retorna o conjunto de logs do sistema armazenado no banco de dados de acordo com o recurso informado.
     * @param $somenteUltimo TRUE para pegar o log com a data mais recente.
     */
    public function getLogsPorRecurso(int $idRecurso, bool $somenteUltimo = false):mixed
    {
        $sql = 'SELECT * FROM sistema_log  WHERE id_recurso=:idRecurso';

        if ($somenteUltimo) {
            $sql .= ' ORDER BY data DESC LIMIT 1';
        } else {
            $sql .= ' ORDER BY data DESC';
        }

        $stmt = $this->pdo->prepare($sql);

        $stmt->bindParam(':idRecurso', $idRecurso);

        $stmt->execute();

        if ($stmt->rowCount() < 1) {
            return null;
        }

        $sistemaLogs = [];
        $logsArray = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($logsArray as $log) {
            $sistemaLog []= new SistemaLog($log['id_pessoa'], $log['id_recurso'], $log['id_acao'], $log['data'], $log['descricao']);
        }

        return $sistemaLogs;
    }
}
