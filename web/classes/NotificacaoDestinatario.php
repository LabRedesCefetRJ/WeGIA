<?php

class NotificacaoDestinatario
{
    private ?int $idNotificacaoDestinatario;
    private int $idNotificacao;
    private int $idPessoa;
    private bool $visualizada;
    private ?string $dataVisualizacao;

    public function __construct(int $idNotificacao, int $idPessoa)
    {
        $this->setIdNotificacao($idNotificacao);
        $this->setIdPessoa($idPessoa);
        $this->visualizada = false;
        $this->idNotificacaoDestinatario = null;
        $this->dataVisualizacao = null;
    }

    public function getIdNotificacao(): int
    {
        return $this->idNotificacao;
    }

    public function setIdNotificacao(int $idNotificacao): void
    {
        if ($idNotificacao < 1) {
            throw new InvalidArgumentException('O id da notificação é inválido.');
        }

        $this->idNotificacao = $idNotificacao;
    }

    public function getIdPessoa(): int
    {
        return $this->idPessoa;
    }

    public function setIdPessoa(int $idPessoa): void
    {
        if ($idPessoa < 1) {
            throw new InvalidArgumentException('O destinatário da notificação é inválido.');
        }

        $this->idPessoa = $idPessoa;
    }
}