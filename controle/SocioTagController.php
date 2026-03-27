<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'SocioTag.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';

class SocioTagController
{
    public function delete()
    {
        try {
            // força resposta em JSON
            header('Content-Type: application/json');

            // pega o corpo da requisição (raw)
            $input = json_decode(file_get_contents('php://input'), true);

            if (!isset($input['id_tag'])) {
                throw new Exception('ID da tag não informado.', 400);
            }

            $id = filter_var($input['id_tag'], FILTER_SANITIZE_NUMBER_INT);

            if (!$id) {
                throw new Exception('ID inválido.', 400);
            }

            if (!SocioTag::delete($id)) {
                throw new Exception('Erro ao deletar tag.', 500);
            }

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'msg' => 'Sucesso ao deletar tag.'
            ]);
        } catch (Exception $e) {
            http_response_code($e->getCode() ?: 500);
            
            $msg = $e instanceof PDOException ? 'Erro no banco de dados ao deletar uma tag.' : $e->getMessage();

            echo json_encode([
                'success' => false,
                'msg' => $e->getMessage()
            ]);

            Util::tratarException($e);
        }
    }
}
