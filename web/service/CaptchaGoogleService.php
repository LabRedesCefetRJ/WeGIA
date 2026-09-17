<?php
if(session_status() === PHP_SESSION_NONE)
    session_start();

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'CaptchaService.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Captcha.php';

class CaptchaGoogleService implements CaptchaService
{
    private int $id = 1;
    private string $api = 'https://www.google.com/recaptcha/api.js';
    private Captcha $captcha;

    public function __construct()
    {
        $captchaDto = Captcha::getInfoById($this->id);

        if (is_null($captchaDto))
            throw new Exception('O captcha não foi encontrado no BD da aplicação.', 500);

        $this->captcha = new Captcha($captchaDto);
    }
    

    /**
     * Retorna a URL da API para requisição do script no frontend da aplicação.
     */
    public function getApi(): string
    {
        return $this->api;
    }

    /**
     * Retorna o elemento para renderizar o captcha no formulário do frontend.
     */
    public function getWidget(): string
    {
        return "<div class='g-recaptcha' data-sitekey='{$this->captcha->getPublicKey()}' style='margin-top:15px;'></div>";
    }

    public function validate(): bool
    {
        if ($this->temSessaoValidada()) {
            unset($_SESSION['captcha']);
            return true;
        }

        return $this->validarTokenFresco();
    }

    /**
     * Igual a validate(), mas ignora qualquer sessão de captcha já armada e
     * sempre exige um token novo, verificado agora com o Google. Usada no
     * ponto de entrada de um fluxo (ex: confirmação do CPF na contribuição),
     * que precisa de uma confirmação de verdade — não pode reaproveitar a
     * própria sessão que ele mesmo arma.
     */
    public function validarTokenFresco(): bool
    {
        if (!isset($_POST['g-recaptcha-response']))
            throw new Exception('reCAPTCHA não enviado', 412);

        $response = $_POST['g-recaptcha-response'];

        $verifyUrl = 'https://www.google.com/recaptcha/api/siteverify';

        $data = [
            'secret'   => $this->captcha->getPrivateKey(),
            'response' => $response,
            'remoteip' => $_SERVER['REMOTE_ADDR']
        ];

        $options = [
            'http' => [
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data),
                'timeout' => 10
            ]
        ];

        $context  = stream_context_create($options);
        $result   = file_get_contents($verifyUrl, false, $context);

        $resultJson = json_decode($result, true);

        if ($resultJson['success'] != true)
            return false;

        return true;
    }

    private function temSessaoValidada(): bool
    {
        return isset($_SESSION['captcha']) && $_SESSION['captcha']['timeout'] > time() && $_SESSION['captcha']['validated'] === true;
    }
}
