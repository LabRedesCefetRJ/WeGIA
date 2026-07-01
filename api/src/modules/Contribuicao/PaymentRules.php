<?php

namespace api\Modules\Contribuicao;

class PaymentRules {
    private int $id;
    private string $description;

    //getters and setters
    public function getId(): int {
        return $this->id;
    }

    public function getDescription(): string {
        return $this->description;
    }

    public function setId(int $id): PaymentRules {
        $this->id = $id;
        return $this;
    }

    public function setDescription(string $description): PaymentRules {
        $this->description = $description;
        return $this;
    }
}