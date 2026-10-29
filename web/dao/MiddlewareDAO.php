<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Conexao.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'PessoaDAO.php';

class MiddlewareDAO
{

    private $pdo;

    public function __construct()
    {
        $this->pdo = Conexao::connect();
    }

    public function verificarPermissao($idPessoa, $controladora, $controladorasRecursos): bool
    {

        $permissao = false;

        $controladoraRecursos = $controladorasRecursos[$controladora];

        $pessoaDAO = new PessoaDAO();

        try {
            $idCargo = $pessoaDAO->getCargoPorPessoa($idPessoa);
        } catch (Exception $e) {
            return false;
        }


        if (is_null($idCargo)) {
            return false;
        }

        if (!empty($controladoraRecursos)) {
            foreach ($controladoraRecursos as $recurso) {
                $sqlRecurso = 'SELECT * FROM permissao WHERE id_cargo=:idCargo and id_recurso=:idRecurso';

                $stmtRecurso = $this->pdo->prepare($sqlRecurso);
                $stmtRecurso->bindParam(':idCargo', $idCargo, PDO::PARAM_INT);
                $stmtRecurso->bindParam(':idRecurso', $recurso, PDO::PARAM_INT);

                $stmtRecurso->execute();

                if ($stmtRecurso->rowCount() > 0) {
                    $permissao = true;
                    break;
                }
            }
        } else {
            $permissao = true;
        }

        return $permissao;
    }
}
