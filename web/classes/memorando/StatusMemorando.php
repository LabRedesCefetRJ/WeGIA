<?php

class StatusMemorando
{
    //Atributos privados
    private string $status;
    private int $idStatus;

    //Método construtor
    public function __construct(string $status, int $idStatus = null)
    {
        $this->setStatus($status);

        if ($idStatus) {
            $this->setIdStatus($idStatus);
        }
    }

    /**
     * Retorna o nome do status de um memorando
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Retorna o id do status de um memorando
     */
    public function getIdStatus()
    {
        return $this->idStatus;
    }

    /**
     * Define o nome do status de um memorando
     */
    public function setStatus(string $status)
    {
        if (empty($status)) {
            throw new InvalidArgumentException('O nome do status de um memorando não pode ser vazio.');
        }
        $this->status = $status;
    }

    /**
     * Define o id do status de um memorando, somente inteiros maiores ou iguais a 1 são permitidos
     */
    public function setIdStatus(int $idStatus)
    {
        if ($idStatus < 1) {
            throw new InvalidArgumentException('O id de um status de um memorando não pode ser menor que 1.');
        }
        $this->idStatus = $idStatus;
    }
}
