<?php

$config_path = "config.php";
if (file_exists($config_path)) {
    require_once($config_path);
} else {
    while (true) {
        $config_path = "../" . $config_path;
        if (file_exists($config_path)) break;
    }
    require_once($config_path);
}

require_once ROOT . '/classes/Aviso.php';
require_once ROOT . '/controle/AvisoNotificacaoControle.php';
require_once ROOT . '/dao/AvisoDAO.php';

class AvisoControle
{
    /**
     * Extrai as informações enviadas pelo formulário via POST e realiza as operações necessárias para criar um aviso e enviar suas respectivas notificações para os funcionários cadastrados no sistema
     */
    public function incluir()
    {
        $idFuncionario = intval(trim($_POST['idfuncionario']));
        $idPessoaAtendida = intval(trim($_POST['idpaciente']));
        $idfichamedica = intval(trim($_POST['idfichamedica']));
        $descricao = trim($_POST['descricao_emergencia']);

        try {
            if ($idfichamedica < 1) {
                throw new InvalidArgumentException('Erro, o id da ficha médica não pode ser menor que 1.');
            }

            $aviso = new Aviso($idFuncionario, $idPessoaAtendida, $descricao);
        } catch (InvalidArgumentException $e) {
            http_response_code(400);
            exit('Erro ao tentar cadastrar uma intercorrência: ' . $e->getMessage());
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
            echo 'Erro ao registrar intercorrência: ' . $e->getMessage();
        }
    }
}
