<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Conexao.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Notificacao.php';

class NotificacaoDAO
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Conexao::connect();
    }

    public function criar(Notificacao $notificacao, array $destinatarios): int
    {
        if (empty($destinatarios)) {
            throw new InvalidArgumentException('A notificação precisa ter destinatário.');
        }

        $this->pdo->beginTransaction();

        try {
            $sql = "
                INSERT INTO notificacao
                    (id_recurso, titulo, mensagem, tipo, link)
                VALUES
                    (:id_recurso, :titulo, :mensagem, :tipo, :link)
            ";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id_recurso', $notificacao->getIdRecurso(), PDO::PARAM_INT);
            $stmt->bindValue(':titulo', $notificacao->getTitulo(), PDO::PARAM_STR);
            $stmt->bindValue(':mensagem', $notificacao->getMensagem(), PDO::PARAM_STR);
            $stmt->bindValue(':tipo', $notificacao->getTipo(), PDO::PARAM_STR);
            $stmt->bindValue(':link', $notificacao->getLink(), PDO::PARAM_STR);
            $stmt->execute();

            $idNotificacao = (int) $this->pdo->lastInsertId();

            $sqlDestinatario = "
                INSERT IGNORE INTO notificacao_destinatario
                    (id_notificacao, id_pessoa)
                VALUES
                    (:id_notificacao, :id_pessoa)
            ";

            $stmtDest = $this->pdo->prepare($sqlDestinatario);

            foreach ($destinatarios as $idPessoa) {
                $stmtDest->bindValue(':id_notificacao', $idNotificacao, PDO::PARAM_INT);
                $stmtDest->bindValue(':id_pessoa', (int) $idPessoa, PDO::PARAM_INT);
                $stmtDest->execute();
            }

            $this->pdo->commit();

            return $idNotificacao;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function contarPendentes(int $idPessoa): int
    {
        $sql = "
            SELECT COUNT(*)
            FROM notificacao_destinatario
            WHERE id_pessoa = :id_pessoa
              AND visualizada = 0
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id_pessoa', $idPessoa, PDO::PARAM_INT);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    public function listarPorUsuario(int $idPessoa, int $limite = 100): array
    {
        $sql = "
            SELECT
                n.id_notificacao,
                n.id_recurso,
                n.titulo,
                n.mensagem,
                n.tipo,
                n.link,
                n.data_criacao,
                nd.visualizada,
                nd.data_visualizacao,
                r.descricao AS recurso
            FROM notificacao_destinatario nd
            INNER JOIN notificacao n ON n.id_notificacao = nd.id_notificacao
            INNER JOIN recurso r ON r.id_recurso = n.id_recurso
            WHERE nd.id_pessoa = :id_pessoa
            ORDER BY nd.visualizada ASC, n.data_criacao DESC
            LIMIT :limite
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id_pessoa', $idPessoa, PDO::PARAM_INT);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function marcarComoVisualizada(int $idNotificacao, int $idPessoa): void
    {
        $sql = "
            UPDATE notificacao_destinatario
            SET visualizada = 1,
                data_visualizacao = NOW()
            WHERE id_notificacao = :id_notificacao
              AND id_pessoa = :id_pessoa
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id_notificacao', $idNotificacao, PDO::PARAM_INT);
        $stmt->bindValue(':id_pessoa', $idPessoa, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function marcarTodasComoVisualizadas(int $idPessoa): void
    {
        $sql = "
            UPDATE notificacao_destinatario
            SET visualizada = 1,
                data_visualizacao = NOW()
            WHERE id_pessoa = :id_pessoa
              AND visualizada = 0
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id_pessoa', $idPessoa, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function existePendente(int $idPessoa, int $idRecurso, string $tipo, string $link): bool
    {
        $sql = "
            SELECT n.id_notificacao
            FROM notificacao n
            INNER JOIN notificacao_destinatario nd ON nd.id_notificacao = n.id_notificacao
            WHERE nd.id_pessoa = :id_pessoa
              AND n.id_recurso = :id_recurso
              AND n.tipo = :tipo
              AND n.link = :link
              AND nd.visualizada = 0
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id_pessoa', $idPessoa, PDO::PARAM_INT);
        $stmt->bindValue(':id_recurso', $idRecurso, PDO::PARAM_INT);
        $stmt->bindValue(':tipo', $tipo, PDO::PARAM_STR);
        $stmt->bindValue(':link', $link, PDO::PARAM_STR);
        $stmt->execute();

        return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function marcarPendentesComoVisualizadasPorReferencia(int $idRecurso, string $tipo, string $link): void
    {
        $sql = "
            UPDATE notificacao_destinatario nd
            INNER JOIN notificacao n ON n.id_notificacao = nd.id_notificacao
            SET nd.visualizada = 1,
                nd.data_visualizacao = NOW()
            WHERE n.id_recurso = :id_recurso
              AND n.tipo = :tipo
              AND n.link = :link
              AND nd.visualizada = 0
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id_recurso', $idRecurso, PDO::PARAM_INT);
        $stmt->bindValue(':tipo', $tipo, PDO::PARAM_STR);
        $stmt->bindValue(':link', $link, PDO::PARAM_STR);
        $stmt->execute();
    }
}