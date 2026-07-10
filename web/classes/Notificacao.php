<?php

class Notificacao
{
    private ?int $idNotificacao;
    private int $idRecurso;
    private string $titulo;
    private string $mensagem;
    private ?string $tipo;
    private ?string $link;
    private ?string $dataCriacao;

    public function __construct(
        int $idRecurso,
        string $titulo,
        string $mensagem,
        ?string $tipo = null,
        ?string $link = null
    ) {
        $this->setIdRecurso($idRecurso);
        $this->setTitulo($titulo);
        $this->setMensagem($mensagem);
        $this->tipo = $tipo;
        $this->link = $link;
        $this->idNotificacao = null;
        $this->dataCriacao = null;
    }

    public function getIdNotificacao(): ?int
    {
        return $this->idNotificacao;
    }

    public function setIdNotificacao(int $idNotificacao): void
    {
        if ($idNotificacao < 1) {
            throw new InvalidArgumentException('O id da notificação não pode ser menor que 1.');
        }

        $this->idNotificacao = $idNotificacao;
    }

    public function getIdRecurso(): int
    {
        return $this->idRecurso;
    }

    public function setIdRecurso(int $idRecurso): void
    {
        if ($idRecurso < 1) {
            throw new InvalidArgumentException('O recurso da notificação é inválido.');
        }

        $this->idRecurso = $idRecurso;
    }

    public function getTitulo(): string
    {
        return $this->titulo;
    }

    public function setTitulo(string $titulo): void
    {
        if (empty(trim($titulo))) {
            throw new InvalidArgumentException('O título da notificação não pode ser vazio.');
        }

        $this->titulo = $titulo;
    }

    public function getMensagem(): string
    {
        return $this->mensagem;
    }

    public function setMensagem(string $mensagem): void
    {
        if (empty(trim($mensagem))) {
            throw new InvalidArgumentException('A mensagem da notificação não pode ser vazia.');
        }

        $this->mensagem = $mensagem;
    }

    public function getTipo(): ?string
    {
        return $this->tipo;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function getDataCriacao(): ?string
    {
        return $this->dataCriacao;
    }
}