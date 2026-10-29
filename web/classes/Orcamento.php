<?php

class Orcamento
{
    public const CONDICOES_PAGAMENTO = [
        'avista' => 'À vista',
        'aprazo' => 'A prazo',
        'parcelado' => 'Parcelado',
    ];

    private ?int $id_orcamento = null;
    private int $id_cotacao;
    private int $id_fornecedor;
    private string $condicao_pagamento;
    private ?string $prazo_entrega = null;
    private string $valor;

    public function __construct(
        $id_cotacao,
        $id_fornecedor,
        $condicao_pagamento,
        $prazo_entrega = null,
        $valor = null
    ) {
        $this->setId_cotacao($id_cotacao);
        $this->setId_fornecedor($id_fornecedor);
        $this->setCondicao_pagamento($condicao_pagamento);
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

    public function getCondicao_pagamento(): string
    {
        return $this->condicao_pagamento;
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

    public function setCondicao_pagamento($condicao_pagamento): void
    {
        if (
            !is_string($condicao_pagamento) ||
            !in_array(
                $condicao_pagamento,
                array_keys(self::CONDICOES_PAGAMENTO),
                true
            )
        ) {
            throw new InvalidArgumentException(
                'A condição de pagamento informada é inválida.',
                400
            );
        }

        $this->condicao_pagamento = $condicao_pagamento;
    }

    public function setPrazo_entrega($prazo_entrega): void
    {
        if ($prazo_entrega === null || $prazo_entrega === '') {
            $this->prazo_entrega = null;
            return;
        }

        if (!is_string($prazo_entrega) || !preg_match('/^\d{4}-\d{2}-\d{2}$/D', $prazo_entrega)) {
            throw new InvalidArgumentException('O prazo de entrega deve ser uma data válida no formato AAAA-MM-DD.', 400);
        }

        [$ano, $mes, $dia] = array_map('intval', explode('-', $prazo_entrega));
        if ($ano < 1000 || !checkdate($mes, $dia, $ano)) {
            throw new InvalidArgumentException('O prazo de entrega deve ser uma data válida.', 400);
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
