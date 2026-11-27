<?php

namespace api\modules\Contribuicao;

class PaymentRules implements \JsonSerializable
{
    private int $id;
    private string $description;
    private float $value;

    public function getId(): int
    {
        return $this->id;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getValue(): float
    {
        return $this->value;
    }

    public function setId(int $id): PaymentRules
    {
        $this->id = $id;
        return $this;
    }

    public function setDescription(string $description): PaymentRules
    {
        $this->description = $description;
        return $this;
    }

    public function setValue(float $value): PaymentRules
    {
        $this->value = $value;
        return $this;
    }

    public function jsonSerialize(): array
    {
        return [
            'description' => $this->description,
            'value' => $this->value
        ];
    }
}