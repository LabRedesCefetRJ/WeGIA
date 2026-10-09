<?php

require_once ROOT . '/dao/PermissaoDAO.php';

class PermissaoFornecedorService
{
    private PermissaoDAO $dao;

    public function __construct(?PermissaoDAO $dao = null)
    {
        $this->dao = $dao ?? new PermissaoDAO();
    }

    public function permite(int $idPessoa, int $acao): bool
    {
        // Fornecedores são compartilhados por Entrada (23) e Processo de Compra (26).
        $acoes = $this->dao->listarAcoesPorPessoaERecursos($idPessoa, [23, 26]);

        foreach ($acoes as $concedida) {
            $concedida = (int) $concedida;

            if (
                $concedida !== 1 &&
                ($concedida & $acao) === $acao
            ) {
                return true;
            }
        }

        return false;
    }
}
