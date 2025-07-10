<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Aviso.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'AvisoNotificacaoControle.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'AvisoDAO.php';

class AvisoControle
{
    /**
     * Extrai as informações enviadas pelo formulário via POST e realiza as operações necessárias para criar um aviso e enviar suas respectivas notificações para os funcionários cadastrados no sistema
     */
    public function incluir()
    {
        //sanitizar parâmetros
        $idFuncionario = filter_input(INPUT_POST, 'idfuncionario', FILTER_SANITIZE_NUMBER_INT);
        $idPessoaAtendida = filter_input(INPUT_POST, 'idpaciente', FILTER_SANITIZE_NUMBER_INT);
        $idfichamedica = filter_input(INPUT_POST, 'idfichamedica', FILTER_SANITIZE_NUMBER_INT);
        $descricao = filter_input(INPUT_POST, 'descricao_emergencia', FILTER_SANITIZE_SPECIAL_CHARS);

        try {
            if (!$idfichamedica || $idfichamedica < 1) {
                throw new InvalidArgumentException('Erro, o id da ficha médica não pode ser menor que 1.', 400);
            }

            if (!$idPessoaAtendida || $idPessoaAtendida < 1) {
                throw new InvalidArgumentException('Erro, o id de um atendido não pode ser menor que 1.', 400);
            }

            if (!$idFuncionario || $idFuncionario < 1) {
                throw new InvalidArgumentException('Erro, o id de um funcionário não pode ser menor que 1.', 400);
            }

            if (!$descricao || strlen($descricao) < 1) {
                throw new InvalidArgumentException('Erro, a descrição informada não é válida.', 400);
            }

            $aviso = new Aviso($idFuncionario, $idPessoaAtendida, $descricao);
        } catch (InvalidArgumentException $e) {
            error_log("[ERRO] {$e->getMessage()} em {$e->getFile()} na linha {$e->getLine()}");
            http_response_code($e->getCode());
            echo json_encode(['erro' => 'Erro ao tentar cadastrar uma intercorrência: ' . $e->getMessage()]);
            exit();
        }

        $avisoNotificacaoControle = new AvisoNotificacaoControle();

        try {
            $avisoDAO = new AvisoDAO();
            $ultimaInsercao = $avisoDAO->cadastrar($aviso);
            if (!$ultimaInsercao) {
                throw new PDOException();
            } else {
                $aviso->setIdAviso($ultimaInsercao);
                $avisoNotificacaoControle->incluir($aviso);
                header("Location: ../html/saude/cadastrar_intercorrencias.php?id_fichamedica=$idfichamedica");
            }
        } catch (PDOException $e) {
            error_log("[ERRO] {$e->getMessage()} em {$e->getFile()} na linha {$e->getLine()}");
            http_response_code($e->getCode());
            echo json_encode(['erro' => 'Erro ao registrar intercorrência no banco de dados.']);
        }
    }

    public function listarIntercorrenciaPorIdDaFichaMedica()
    {
        header('Content-Type: application/json');
        try {
            $idfichamedica = filter_input(INPUT_POST, 'idfichamedica', FILTER_SANITIZE_NUMBER_INT);

            if (!$idfichamedica || $idfichamedica < 1) {
                throw new InvalidArgumentException('Erro, o id da ficha médica não pode ser menor que 1.', 400);
            }

            $avisoDAO = new AvisoDAO();
            $intercorrencias = $avisoDAO->listarIntercorrenciaPorIdDaFichaMedica($idfichamedica);

            foreach ($intercorrencias as $key => $value) {
                $data = new DateTime($value['data']);
                $intercorrencias[$key]['data'] = $data->format('d/m/Y H:i:s');
            }

            echo json_encode($intercorrencias);
        } catch (Exception $e) {
            error_log("[ERRO] {$e->getMessage()} em {$e->getFile()} na linha {$e->getLine()}");
            http_response_code($e->getCode());
            if ($e instanceof PDOException) {
                echo json_encode(['erro' => 'Erro no servidor ao listar intercorrências.']);
            } else {
                echo json_encode(['erro' => $e->getMessage()]);
            }
        }
    }
}
