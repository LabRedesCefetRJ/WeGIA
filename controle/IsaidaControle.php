<?php
include_once '../classes/Isaida.php';
include_once '../dao/IsaidaDAO.php';
include_once '../dao/SaidaDAO.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';

class IsaidaControle
{
    public function listarId()
    {
        // ✅ Sanitização e validação de entrada
        $id_saida = isset($_REQUEST['id_saida']) ? filter_var($_REQUEST['id_saida'], FILTER_VALIDATE_INT) : null;
        $nextPage = isset($_REQUEST['nextPage']) ? filter_var($_REQUEST['nextPage'], FILTER_SANITIZE_URL) : '../index.php';

        // Verifica se o ID é válido
        if ($id_saida === false || $id_saida === null || $id_saida <= 0) {
            echo "ID de saída inválido.";
            exit;
        }

        try {
            $isaidaDAO = new IsaidaDAO();
            $saidaDAO  = new SaidaDAO();

            // ✅ Uso seguro de prepared statements deve estar garantido dentro do DAO
            $isaida = $isaidaDAO->listarId($id_saida);
            $saida  = $saidaDAO->listarUmCompletoPorId($id_saida);

            session_start();

            // ✅ Evita armazenar dados muito grandes na sessão
            $_SESSION['isaida'] = $isaida;
            $_SESSION['saidaUnica'] = $saida;

            // ✅ Escapa a URL antes do redirecionamento para evitar XSS
            header('Location: ' . htmlspecialchars($nextPage, ENT_QUOTES, 'UTF-8'));
            exit;
        } 
        // Captura específica para erros de banco
        catch (PDOException $e) {
            error_log("Erro de banco ao listar saída: " . $e->getMessage());
            echo "Erro ao acessar o banco de dados. Tente novamente mais tarde.";
        } 
        // Captura genérica para outros erros
        catch (Exception $e) {
            error_log("Erro geral em IsaidaControle::listarId: " . $e->getMessage());
            echo "Ocorreu um erro inesperado. Contate o suporte técnico.";
        }
    }
}
/*
class IsaidaControle
{
    public function listarId(){
        extract($_REQUEST);
        try{
            $isaidaDAO = new IsaidaDAO();
            $isaida = $isaidaDAO->listarId($id_saida);
            $saidaDAO = new SaidaDAO();
            $saida = $saidaDAO->listarUmCompletoPorId($id_saida);
            session_start();
            $_SESSION['isaida'] = $isaida;
            $_SESSION['saidaUnica'] = $saida;
            header('Location: ' . $nextPage);
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }
}*/
?>
