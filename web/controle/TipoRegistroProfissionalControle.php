<?php
    require_once '../classes/TipoRegistroProfissional.php';
    require_once '../dao/TipoRegistroProfissionalDAO.php';
    require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';

    class TipoRegistroProfissionalControle
    {
        public function inserir()
        {
            if(isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
                $json = file_get_contents('php://input');
                $data = json_decode($json, true);

                $descricaoBruta = filter_var($data['descricao'] ?? '', FILTER_UNSAFE_RAW);
                $tipoRegistroDescricao = mb_strtoupper(trim(strip_tags($descricaoBruta)), 'UTF-8');
            }else {
                $descricaoBruta = filter_input(INPUT_POST, 'descricao', FILTER_UNSAFE_RAW) ?? '';
                $tipoRegistroDescricao = mb_strtoupper(trim(strip_tags($descricaoBruta)), 'UTF-8');
            }

            try {
                $tipoRegistroProfissional = new TipoRegistroProfissional((string)($tipoRegistroDescricao));

                $tipoRegistroProfissionalDAO = new TipoRegistroProfissionalDAO();
                $tipoRegistroProfissionalDAO->incluir($tipoRegistroProfissional);
            } catch (Exception $e) {
                Util::tratarException($e);
            }
        }
    }
?>