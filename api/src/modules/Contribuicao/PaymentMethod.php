<?php
namespace api\Modules\Contribuicao;

class PaymentMethod{
    private int $id;
    private string $description;
    private array $rules;

    //getters and setters
    public function getId(): int {
        return $this->id;
    }

    public function getDescription(): string {
        return $this->description;
    }

    public function getRules(): array {
        return $this->rules;
    }

    public function setId(int $id): PaymentMethod {
        $this->id = $id;
        return $this;
    }

    public function setDescription(string $description): PaymentMethod {
        $this->description = $description;
        return $this;
    }

    public function setRules(PaymentRules $rules): PaymentMethod {
        $this->rules []= $rules;
        return $this;
    }
}