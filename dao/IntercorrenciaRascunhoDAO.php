<?php
require_once 'Conexao.php';

class IntercorrenciaRascunhoDAO
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

    public function salvar(int $idFichaMedica, int $idFuncionario, string $descricao): void
    {
        $sql = "INSERT INTO intercorrencia_rascunho (id_fichamedica, id_funcionario, descricao, data_atualizacao)
                VALUES (:id_fichamedica, :id_funcionario, :descricao, NOW())
                ON DUPLICATE KEY UPDATE descricao = VALUES(descricao), data_atualizacao = NOW()";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id_fichamedica', $idFichaMedica, PDO::PARAM_INT);
        $stmt->bindValue(':id_funcionario', $idFuncionario, PDO::PARAM_INT);
        $stmt->bindValue(':descricao', $descricao, PDO::PARAM_STR);
        $stmt->execute();
    }

    public function obter(int $idFichaMedica, int $idFuncionario): ?array
    {
        $sql = "SELECT descricao, data_atualizacao
                FROM intercorrencia_rascunho
                WHERE id_fichamedica = :id_fichamedica AND id_funcionario = :id_funcionario";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id_fichamedica', $idFichaMedica, PDO::PARAM_INT);
        $stmt->bindValue(':id_funcionario', $idFuncionario, PDO::PARAM_INT);
        $stmt->execute();

        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado ?: null;
    }

    public function limpar(int $idFichaMedica, int $idFuncionario): void
    {
        $sql = "DELETE FROM intercorrencia_rascunho
                WHERE id_fichamedica = :id_fichamedica AND id_funcionario = :id_funcionario";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id_fichamedica', $idFichaMedica, PDO::PARAM_INT);
        $stmt->bindValue(':id_funcionario', $idFuncionario, PDO::PARAM_INT);
        $stmt->execute();
    }
}
