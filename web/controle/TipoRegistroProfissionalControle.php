<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'TipoRegistroProfissional.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'TipoRegistroProfissionalDAO.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';

class TipoRegistroProfissionalControle
{
    public function incluir()
    {
        if (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
            $descricaoBruta = filter_var($data['descricao'] ?? '', FILTER_UNSAFE_RAW);
        } else {
            $descricaoBruta = filter_input(INPUT_POST, 'descricao', FILTER_UNSAFE_RAW) ?? '';
        }
        $tipoRegistroDescricao = mb_strtoupper(trim(strip_tags($descricaoBruta)), 'UTF-8');

        try {
            $tipoRegistroProfissional = new TipoRegistroProfissional((string)$tipoRegistroDescricao);
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

       public function listarTodos()
    {
        try {
            $status = isset($_GET['status']) ? intval($_GET['status']) : 1;

            $tipoRegistroProfissionalDAO = new TipoRegistroProfissionalDAO();
            $lista = $tipoRegistroProfissionalDAO->listarTodos($status);

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($lista);
        } catch (Throwable $e) {
            Util::tratarException($e);
        }
    }

        public function listarUm()
        {
            try {
                $idTipoRegistro = filter_input(INPUT_GET, 'id_tipo_registro_profissional', FILTER_SANITIZE_NUMBER_INT);

                if(!$idTipoRegistro || $idTipoRegistro < 1){
                    throw new InvalidArgumentException('O id do registro profissional informado não é válido', 400);
                }

                $tipoRegistroProfissionalDAO = new TipoRegistroProfissionalDAO();
                $tipoRegistroProfissional = $tipoRegistroProfissionalDAO->listarUm($idTipoRegistro);

                header('Content-Type: application/json; charset=utf-8');
                echo json_encode($tipoRegistro);
            }catch (Exception $e){
                Util::tratarException($e);
            }
        }

        public function alterarStatus()
        {
            $idTipoRegistro = filter_input(INPUT_POST, 'id_tipo_registro_profissional', FILTER_SANITIZE_NUMBER_INT);
            $operacao = filter_input(INPUT_POST, 'operacao', FILTER_SANITIZE_SPECIAL_CHARS);

            try {
                if (!$idTipoRegistro || $idTipoRegistro < 1)
                throw new InvalidArgumentException('O id do registro profissional informado não é válido', 400);

                if ($operacao !== 'desativar' && $operacao !== 'ativar')
                throw new InvalidArgumentException('A operação informada é inválida.', 400);

                $status = null;

                switch ($operacao) {
                    case 'desativar':
                        $status = 0;
                        break;
                    case 'ativar':
                        $status = 1;
                        break;
                }

                $tipoRegistroProfissionalDAO = new TipoRegistroProfissionalDAO();
                $tipoRegistroProfissional = $tipoRegistroProfissionalDAO->alterarStatus($idTipoRegistro,$status);

                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'sucesso' => true,
                    'mensagem' => "Status alterado para {$operacao} com sucesso!"
                ]);
            } catch (Exception $e) {
                Util::tratarException($e);
            }
        }
        
        public function excluir()
        {
            try {
                $idTipoRegistro = filter_input(INPUT_POST, 'id_tipo_registro_profissional', FILTER_SANITIZE_NUMBER_INT);

                if (!$idTipoRegistro || $idTipoRegistro < 1) {
                    throw new InvalidArgumentException('O id informado para exclusão é inválido.', 400);
                }

                $tipoRegistroProfissionalDAO = new TipoRegistroProfissionalDAO();
                $tipoRegistroProfissionalDAO->excluir($idTipoRegistro);

                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'sucesso' => true,
                    'mensagem' => 'Registro profissional excluído com sucesso!'
                ]);
            } catch (Throwable $e) {
                Util::tratarException($e);
            }
        }
    }
?>