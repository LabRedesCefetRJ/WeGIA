<?php

require_once ROOT . '/dao/Conexao.php';

final class PermissaoFornecedor
{
    // Fornecedores são compartilhados por Entrada (23) e Processo de Compra (26).
    public static function permite(int $idPessoa, int $acao): bool
    {
        $stmt = Conexao::connect()->prepare(
            'SELECT p.id_acao FROM permissao p
             INNER JOIN funcionario f ON f.id_cargo = p.id_cargo
             WHERE f.id_pessoa = :id_pessoa AND p.id_recurso IN (23, 26)'
        );
        $stmt->bindValue(':id_pessoa', $idPessoa, PDO::PARAM_INT);
        $stmt->execute();
        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $concedida) {
            $concedida = (int) $concedida;
            if ($concedida !== 1 && ($concedida & $acao) === $acao) {
                return true;
            }
        }
        return false;
    }

    public static function exigir(int $idPessoa, int $acao): void
    {
        if (!self::permite($idPessoa, $acao)) {
            header('Location: ' . WWW . 'html/home.php?msg_c=' . urlencode(
                'Você não tem permissão para realizar esta ação em fornecedores.'
            ));
            exit;
        }
    }
}
