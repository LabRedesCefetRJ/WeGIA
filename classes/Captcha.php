<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'CaptchaDTO.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'CaptchaDAO.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'CaptchaMySQL.php';

class Captcha
{
    //properities
    private int $id;
    private string $descriptionApi;
    private string $publicKey;
    private string $privateKey;
    private CaptchaDAO $dao;

    public function __construct(CaptchaDTO $dto, ?CaptchaDAO $dao = null)
    {
        $this->setDescriptionApi($dto->descriptionApi)->setPublicKey($dto->publicKey)->setPrivateKey($dto->privateKey);

        if (!is_null($dto->id))
            $this->setId($dto->id);

        isset($dao) ? $this->dao = $dao : $this->dao = new CaptchaMySQL();
    }

    //Behavior methods

    public static function getInfoById(int $id, ?CaptchaDAO $dao = null):?CaptchaDTO{
        if(is_null($dao))
            $dao = new CaptchaMySQL();

        $captchaArray = $dao->getInfoById($id);

        if(!$captchaArray)
            return null;

        return new CaptchaDTO($captchaArray);
    }

    //access methods

    public function getId(): int
    {
        return $this->id;
    }

    public function getDescriptionApi(): string
    {
        return $this->descriptionApi;
    }

    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    public function getPrivateKey(): string
    {
        return $this->privateKey;
    }

    /**
     * Verifica se o id informado está dentro dos limites permitidos.
     */
    public function setId(int $id)
    {
        if ($id < 1)
            throw new InvalidArgumentException('O id de um captcha deve ser um inteiro maior ou igual a 1.', 412);

        if (strlen((string)$id) > 11)
            throw new InvalidArgumentException('O id fornecido excede o tamanho máximo de armazenamento.', 412);

        $this->id = $id;

        return $this;
    }

    /**
     * Verifica se a descrição informada está dentro dos limites permitidos.
     */
    public function setDescriptionApi(string $descriptionApi)
    {
        if (strlen($descriptionApi) > 255)
            throw new InvalidArgumentException('A descrição excede o tamanho máximo de armazenamento.', 412);

        $this->descriptionApi = $descriptionApi;

        return $this;
    }

    /**
     * Verifica se a chave pública informada está dentro dos limites permitidos.
     */
    public function setPublicKey(string $publicKey)
    {
        if (strlen($publicKey) > 255)
            throw new InvalidArgumentException('A chave pública excede o tamanho máximo de armazenamento.', 412);

        $this->publicKey = $publicKey;

        return $this;
    }

    /**
     * Verifica se a chave privada informada está dentro dos limites permitidos.
     */
    public function setPrivateKey(string $privateKey)
    {
         if (strlen($privateKey) > 255)
            throw new InvalidArgumentException('A chave privada excede o tamanho máximo de armazenamento.', 412);

        $this->privateKey = $privateKey;

        return $this;
    }
}
