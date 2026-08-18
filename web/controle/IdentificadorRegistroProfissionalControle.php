<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'IdentificadorRegistroProfissional.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'IdentificadorRegistroProfissionalDAO.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';

class TipoRegistroProfissionalControle
{
    public function inserir()
    {
        if (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
            $numeroRegistro = trim($data['numeroRegistro']);
            $uf = ($data['uf']);
        } else {
            $numeroRegistro = trim(INPUT_POST, 'numeroRegistro', FILTER_UNSAFE_RAW) ?? '';
            $numeroRegistro = (INPUT_POST, 'uf');
        }

        try {
            $IdentificadorRegistroProfissional = new IdentificadorRegistroProfissional((string)$numeroRegistro,$uf);
            $tipoRegistroProfissionalDAO = new TipoRegistroProfissionalDAO();
            $tipoRegistroProfissionalDAO->incluir($tipoRegistroProfissional);

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'sucesso' => true,
                'mensagem' => 'Tipo de registro cadastrado com sucesso!'
            ]);
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }