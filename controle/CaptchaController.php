<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Captcha.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'SistemaLogDAO.php';
class CaptchaController
{
    public function getAll()
    {
        try {
            $captchas = Captcha::getAll();

            if (is_null($captchas)) {
                echo json_encode([]);
            }

            echo json_encode($captchas);
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    public function updateKeys()
    {
        try {
            $captchaDto = new CaptchaDTO();
            $pdo = Conexao::connect();

            $captchaDto->id = filter_input(INPUT_POST, 'captcha-id', FILTER_SANITIZE_NUMBER_INT);
            $captchaDto->descriptionApi = '';
            $captchaDto->publicKey = filter_input(INPUT_POST, 'public-key', FILTER_SANITIZE_SPECIAL_CHARS);
            $captchaDto->privateKey = filter_input(INPUT_POST, 'private-key', FILTER_SANITIZE_SPECIAL_CHARS);

            $captcha = new Captcha($captchaDto, new CaptchaMySQL($pdo));

            $pdo->beginTransaction();

            $update = false;

            if ($captcha->updateKeys() === true) {
                $update = true;
                $msg = "Alteração das chaves do captcha de id {$captcha->getId()}.";
            } else {
                http_response_code(500);
                $msg = "Tentativa com falha da alteração das chaves do captcha de id {$captcha->getId()}.";
            }

            $sistemaLog = new SistemaLog($_SESSION['id_pessoa'], 9, 3, new DateTime('now', new DateTimeZone('America/Sao_Paulo')), $msg);

            $sistemaLogDao = new SistemaLogDAO($pdo);
            if (!$sistemaLogDao->registrar($sistemaLog)) {
                $pdo->rollBack();
                header("Location: ../html/contribuicao/view/captcha.php?msg=editar-falha#mensagem-tabela");
                exit();
            }

            $pdo->commit();

            if($update)
                header("Location: ../html/contribuicao/view/captcha.php?msg=editar-sucesso#mensagem-tabela");
            else
                header("Location: ../html/contribuicao/view/captcha.php?msg=editar-falha#mensagem-tabela");
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            Util::tratarException($e);
            header("Location: ../html/contribuicao/view/captcha.php?msg=editar-falha#mensagem-tabela");
        }
    }
}
