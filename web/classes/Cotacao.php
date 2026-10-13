<?php

class Cotacao
{
    private ?int $id_cotacao = null;
    private int $id_responsavel;
    private ?int $id_orcamento_escolhido = null;
    private string $status;
    private ?string $descricao = null;
    private ?string $justificativa = null;
    private const STATUS_PERMITIDOS = [
        'concluido',
        'analise'
    ];

    public function __construct(
        $id_responsavel,
        $status = null,
        $descricao = null,
        $justificativa = null,
        $id_orcamento_escolhido = null
    ) {
        $this->setId_responsavel($id_responsavel);
        $this->setStatus($status);
        $this->setDescricao($descricao);
        $this->setJustificativa($justificativa);
        $this->setId_orcamento_escolhido($id_orcamento_escolhido);
    }

    public function getId_cotacao(): ?int
    {
        return $this->id_cotacao;
    }

    public function getId_responsavel(): int
    {
        return $this->id_responsavel;
    }

    public function getId_orcamento_escolhido(): ?int
    {
        return $this->id_orcamento_escolhido;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getDescricao(): ?string
    {
        return $this->descricao;
    }

    public function getJustificativa(): ?string
    {
        return $this->justificativa;
    }

    public function setId_cotacao($id_cotacao): void
    {
        $this->id_cotacao = $this->validarId($id_cotacao, 'cotação');
    }

    public function setId_responsavel($id_responsavel): void
    {
        $this->id_responsavel = $this->validarId($id_responsavel, 'responsável');
    }

    public function setId_orcamento_escolhido($id_orcamento_escolhido): void
    {
        if ($id_orcamento_escolhido === null || $id_orcamento_escolhido === '') {
            $this->id_orcamento_escolhido = null;
            return;
        }

        $this->id_orcamento_escolhido = $this->validarId(
            $id_orcamento_escolhido,
            'orçamento escolhido'
        );
    }

    public function setStatus($status): void
    {
        if ($status === null || $status === '') {
            $this->status = 'analise';
            return;
        }

        if (!in_array($status, self::STATUS_PERMITIDOS, true)) {
            throw new InvalidArgumentException('Status da cotação inválido.', 400);
        }

        $this->status = $status;
    }

    public function setDescricao($descricao): void
    {
        $this->descricao = $this->validarTextoOpcional($descricao, 'descrição');
    }

    public function setJustificativa($justificativa): void
    {
        $this->justificativa = $this->validarTextoOpcional($justificativa, 'justificativa');
    }

    private function validarId($id, string $campo): int
    {
        if (filter_var($id, FILTER_VALIDATE_INT) === false || (int) $id < 1) {
            throw new InvalidArgumentException("O id de {$campo} deve ser um inteiro maior que zero.", 400);
        }

        return (int) $id;
    }

    private function validarTextoOpcional($texto, string $campo): ?string
    {
        if ($texto === null || $texto === '') {
            return null;
        }

        if (!is_string($texto)) {
            throw new InvalidArgumentException("O campo {$campo} deve ser um texto.", 400);
        }

        $texto = trim($texto);
        if (mb_strlen($texto, 'UTF-8') > 255) {
            throw new InvalidArgumentException("O campo {$campo} deve ter no máximo 255 caracteres.", 400);
        }

        return $texto;
    }
}
