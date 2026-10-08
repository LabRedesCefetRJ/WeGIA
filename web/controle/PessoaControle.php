<?php
require_once dirname(__FILE__, 2) . '/dao/PessoaDAO.php';
require_once dirname(__FILE__, 2) . '/classes/Util.php';

class PessoaControle
{

    private PessoaDAO $pessoaDAO;

    public function __construct()
    {
        $this->pessoaDAO = new PessoaDAO();
    }

    public function buscarPorDocumento()
    {
        header('Content-Type: application/json');

        try {
            //pegar o documento da requisição
            $documento = filter_input(INPUT_GET, 'documento', FILTER_SANITIZE_SPECIAL_CHARS);

            if (!$documento || $documento === NULL) {
                http_response_code(400);
                echo json_encode(['error' => 'O documento não pode ser vazio']);
                exit;
            }

            //buscar a pessoa no banco de dados
            $pessoa = $this->pessoaDAO->verificarExistencia($documento);

            //retornar a pessoa encontrada
            if ($pessoa === null) {
                http_response_code(404);
                echo json_encode(['error' => 'Pessoa não encontrada']);
                exit;
            }

            echo json_encode($pessoa);
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }
}
