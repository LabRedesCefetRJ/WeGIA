<?php

class SaudeLogEquipePlantao
{
    private int $idUsuario;
    private DateTime $dataHora;
    private string $acao;
    private string $descricao;
    private ?int $idEquipePlantao;
    private ?int $idFuncionario;
    private ?int $idEscalaMensal;
    private ?int $idEscalaDia;
    private ?array $dados;

    public function __construct(
        int $idUsuario,
        string $acao,
        string $descricao,
        ?int $idEquipePlantao = null,
        ?int $idFuncionario = null,
        ?int $idEscalaMensal = null,
        ?int $idEscalaDia = null,
        ?array $dados = null
    ) {
        if ($idUsuario < 1) {
            throw new InvalidArgumentException('O id do usuário do log deve ser maior que zero.', 400);
        }

        $acao = trim($acao);
        $descricao = trim($descricao);

        if ($acao === '') {
            throw new InvalidArgumentException('A ação do log não pode ser vazia.', 400);
        }

        if ($descricao === '') {
            throw new InvalidArgumentException('A descrição do log não pode ser vazia.', 400);
        }

        $this->idUsuario = $idUsuario;
        $this->dataHora = new DateTime('now', new DateTimeZone('America/Sao_Paulo'));
        $this->acao = mb_substr($acao, 0, 80);
        $this->descricao = mb_substr($descricao, 0, 500);
        $this->idEquipePlantao = $idEquipePlantao;
        $this->idFuncionario = $idFuncionario;
        $this->idEscalaMensal = $idEscalaMensal;
        $this->idEscalaDia = $idEscalaDia;
        $this->dados = $dados;
    }

    public function getIdUsuario(): int
    {
        return $this->idUsuario;
    }

    public function getDataHora(): DateTime
    {
        return $this->dataHora;
    }

    public function getAcao(): string
    {
        return $this->acao;
    }

    public function getDescricao(): string
    {
        return $this->descricao;
    }

    public function getIdEquipePlantao(): ?int
    {
        return $this->idEquipePlantao;
    }

    public function getIdFuncionario(): ?int
    {
        return $this->idFuncionario;
    }

    public function getIdEscalaMensal(): ?int
    {
        return $this->idEscalaMensal;
    }

    public function getIdEscalaDia(): ?int
    {
        return $this->idEscalaDia;
    }

    public function getDadosJson(): ?string
    {
        if (is_null($this->dados)) {
            return null;
        }

        return json_encode($this->dados, JSON_UNESCAPED_UNICODE);
    }
}
