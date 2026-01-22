<?php
final class Arquivo
{
    private string $nome;
    private string $extensao;
    private string $mime;
    private string $conteudo;

    private function __construct() {}

    public static function fromUpload(array $file): self
    {
        $obj = new self();

        $obj->validarUpload($file);
        $obj->carregarFromUpload($file);

        return $obj;
    }

    public static function fromDatabase(
        string $conteudo,
        string $mime,
        string $nome,
        string $extensao
    ): self {
        $obj = new self();

        $obj->conteudo = $conteudo;
        $obj->mime = $mime;
        $obj->nome = $nome;
        $obj->extensao = $extensao;

        return $obj;
    }


    private function validarUpload(array $file): void
    {
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            throw new InvalidArgumentException('Erro no upload do arquivo.');
        }

        if (!is_uploaded_file($file['tmp_name'])) {
            throw new InvalidArgumentException('Arquivo inválido.');
        }
    }

    private function carregarFromUpload(array $file): void
    {
        $this->nome = basename($file['name']);
        $this->extensao = strtolower(pathinfo($this->nome, PATHINFO_EXTENSION));

        $permitidas = ['pdf', 'jpg', 'jpeg', 'png'];

        if (!in_array($this->extensao, $permitidas, true)) {
            throw new InvalidArgumentException('Extensão não permitida.');
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $this->mime = $finfo->file($file['tmp_name']);

        $mimes = [
            'pdf'  => 'application/pdf',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png'  => 'image/png',
        ];

        if ($this->mime !== ($mimes[$this->extensao] ?? null)) {
            throw new InvalidArgumentException('MIME inválido.');
        }

        $this->conteudo = file_get_contents($file['tmp_name']);
    }


    // Getters
    public function getNome(): string
    {
        return $this->nome;
    }

    public function getExtensao(): string
    {
        return $this->extensao;
    }

    public function getMime(): string
    {
        return $this->mime;
    }

    public function getConteudo(): string
    {
        return $this->conteudo;
    }

    public function getTamanho(): int
    {
        return strlen($this->conteudo);
    }
}
