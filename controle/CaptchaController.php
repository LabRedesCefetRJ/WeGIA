<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Captcha.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';
class CaptchaController
{
    public function getAll()
    {
        try {
            $captchas = Captcha::getAll();

            if(is_null($captchas)){
                echo json_encode([]);
            }

            echo json_encode($captchas);
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    public function updateKeys(){
        try{
            $captchaDto = new CaptchaDTO();

            $captchaDto->id = filter_input(INPUT_POST, 'captcha-id', FILTER_SANITIZE_NUMBER_INT);
            $captchaDto->descriptionApi = '';
            $captchaDto->publicKey = filter_input(INPUT_POST, 'public-key', FILTER_SANITIZE_SPECIAL_CHARS);
            $captchaDto->privateKey = filter_input(INPUT_POST, 'private-key', FILTER_SANITIZE_SPECIAL_CHARS);

            $captcha = new Captcha($captchaDto);

            if($captcha->updateKeys() === true){
                echo json_encode(['status' => 'success']);
                exit();
            }else{
                http_response_code(500);
                echo json_encode(['status' => 'failed']);
            }

        }catch(Exception $e){
            Util::tratarException($e);
        }
    }
}
