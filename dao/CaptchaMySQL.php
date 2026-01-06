<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Conexao.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'CaptchaDAO.php';

class CaptchaMySQL implements CaptchaDAO{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        isset($pdo) ? $this->pdo = $pdo : $this->pdo = Conexao::connect();
    }

    public function getInfoById(int $id)
    {
        $search = 'SELECT * FROM captcha WHERE id=:id';

        $stmt = $this->pdo->prepare($search);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}