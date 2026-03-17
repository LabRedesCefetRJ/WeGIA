<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'SocioTag.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';

class SocioTagController
{
    public function delete()
    {
        try {
            $id = filter_input(INPUT_POST, 'id_tag', FILTER_SANITIZE_NUMBER_INT);

            if (!SocioTag::delete($id))
                throw new Exception('Erro ao deletar tag.', 500);

            http_response_code(200);
            echo json_encode(['msg' => 'Sucesso ao deletar tag.']);
        } catch (Exception $e) {
            if($e instanceof PDOException)
                $e = new Exception($e->getMessage(), $e->getCode());

            Util::tratarException($e);
        }
    }
}
