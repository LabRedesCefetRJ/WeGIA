<?php

namespace api\modules\Contribuicao;

class PaymentMethod implements \JsonSerializable
{
    private int $id;

    /**
     * @var PaymentRules[]
     */
    private array $rules = [];

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return PaymentRules[]
     */
    public function getRules(): array
    {
        return $this->rules;
    }

    public function setId(int $id): PaymentMethod
    {
        $this->id = $id;
        return $this;
    }

    public function setRules(PaymentRules $rule): PaymentMethod
    {
        $this->rules[] = $rule;
        return $this;
    }

    public function jsonSerialize(): array
    {
        return [
            'rules' => $this->rules
        ];
    }
}