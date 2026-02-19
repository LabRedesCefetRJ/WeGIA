<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'config.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';

class ReleaseControle
{
    /**
     * Busca o conteúdo salvo no arquivo de release do servidor da aplicação e retorna um JSON do seu inteiro.
     */
    public function getRelease()
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $releasePath = ROOT . DIRECTORY_SEPARATOR . '.release';

            if (!file_exists($releasePath))
                throw new Exception('O arquivo padrão para a release não foi encontrado.', 500);

            $localRelease = file_get_contents($releasePath);

            if ($localRelease === false)
                throw new Exception('Erro ao ler o arquivo de release.', 500);

            echo json_encode(intval($localRelease));
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }
}
