<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Conexao.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'CaptchaDAO.php';

class CaptchaMySQL implements CaptchaDAO
{
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

    public function updateKeys(Captcha $captcha, bool $updatePrivateKey = true): bool
    {
        $fields = ['public_key = :publicKey'];
        $params = [
            ':publicKey' => $captcha->getPublicKey(false),
            ':id'        => $captcha->getId()
        ];

        if ($updatePrivateKey) {
            $fields[] = 'private_key = :privateKey';
            $params[':privateKey'] = $captcha->getPrivateKey();
        }

        $query = '
            UPDATE captcha
            SET ' . implode(', ', $fields) . '
            WHERE id = :id
        ';

        $stmt = $this->pdo->prepare($query);

        foreach ($params as $param => $value) {
            $stmt->bindValue($param, $value);
        }

        return $stmt->execute();
    }


    public function getAll()
    {
        $search = 'SELECT * FROM captcha';
        $query = $this->pdo->query($search);

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
}
