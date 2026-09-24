<?php

class Orcamento
{
    private ?int $id_orcamento = null;
    private int $id_cotacao;
    private int $id_fornecedor;
    private ?string $prazo_entrega = null;
    private string $valor;

    public function __construct(
        $id_cotacao,
        $id_fornecedor,
        $prazo_entrega = null,
        $valor = null
    ) {
        $this->setId_cotacao($id_cotacao);
        $this->setId_fornecedor($id_fornecedor);
        $this->setPrazo_entrega($prazo_entrega);
        $this->setValor($valor);
    }

    public function getId_orcamento(): ?int
    {
        return $this->id_orcamento;
    }

    public function getId_cotacao(): int
    {
        return $this->id_cotacao;
    }

    public function getId_fornecedor(): int
    {
        return $this->id_fornecedor;
    }

    public function getPrazo_entrega(): ?string
    {
        return $this->prazo_entrega;
    }

    public function getValor(): string
    {
        return $this->valor;
    }

    public function setId_orcamento($id_orcamento): void
    {
        $this->id_orcamento = $this->validarId($id_orcamento, 'orçamento');
    }

    public function setId_cotacao($id_cotacao): void
    {
        $this->id_cotacao = $this->validarId($id_cotacao, 'cotação');
    }

    public function setId_fornecedor($id_fornecedor): void
    {
        $this->id_fornecedor = $this->validarId($id_fornecedor, 'fornecedor');
    }

    public function setPrazo_entrega($prazo_entrega): void
    {
        if ($prazo_entrega === null || $prazo_entrega === '') {
            $this->prazo_entrega = null;
            return;
        }

        if (!is_string($prazo_entrega)) {
            throw new InvalidArgumentException('O prazo de entrega deve ser um texto.', 400);
        }

        $prazo_entrega = trim($prazo_entrega);
        if (mb_strlen($prazo_entrega, 'UTF-8') > 50) {
            throw new InvalidArgumentException('O prazo de entrega deve ter no máximo 50 caracteres.', 400);
        }

        $this->prazo_entrega = $prazo_entrega;
    }

    public function setValor($valor): void
    {
        if ($valor === null || (is_string($valor) && trim($valor) === '')) {
            throw new InvalidArgumentException('O valor do orçamento é obrigatório.', 400);
        }

        if (is_string($valor)) {
            $valor = str_replace(',', '.', trim($valor));
        }

        if (!is_numeric($valor) || (float) $valor < 0) {
            throw new InvalidArgumentException('O valor do orçamento deve ser um número maior ou igual a zero.', 400);
        }

        if ((float) $valor > 99999999.99) {
            throw new InvalidArgumentException('O valor do orçamento excede o limite permitido.', 400);
        }

        $this->valor = number_format((float) $valor, 2, '.', '');
    }

    private function validarId($id, string $campo): int
    {
        if (filter_var($id, FILTER_VALIDATE_INT) === false || (int) $id < 1) {
            throw new InvalidArgumentException("O id de {$campo} deve ser um inteiro maior que zero.", 400);
        }

        return (int) $id;
    }
}
