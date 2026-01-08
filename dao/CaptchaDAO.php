<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Captcha.php';

interface CaptchaDAO{
    public function getInfoById(int $id);
    public function updateKeys(Captcha $captcha):bool;
}