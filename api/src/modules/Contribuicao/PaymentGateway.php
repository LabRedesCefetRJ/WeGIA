<?php

namespace api\modules\Contribuicao;

class PaymentGateway{
    private ?int $id = null;
    private string $description;
    private string $endpoint;
    private string $privateToken;
    private string $publicToken;
    private bool $status;

    public function __construct(string $description, string $endpoint, string $privateToken, string $publicToken, bool $status, ?int $id = null) {
        $this->description = $description;
        $this->endpoint = $endpoint;
        $this->privateToken = $privateToken;
        $this->publicToken = $publicToken;
        $this->status = $status;
        $this->id = $id;
    }
    // Behavioral Methods

    /**
     * Returns the public data of the payment gateway.
     *
     * @return array
     */
    public function getPublicData(): array {
        return [
            'id' => $this->id,
            'description' => $this->description,
            'endpoint' => $this->endpoint,
            'publicToken' => $this->publicToken,
            'status' => $this->status
        ];
    }

    // Getters and Setters
    public function getId(): ?int {
        return $this->id;
    }

    public function getDescription(): string {
        return $this->description;
    }

    public function getEndpoint(): string {
        return $this->endpoint;
    }

    public function getPrivateToken(): string {
        return $this->privateToken;
    }

    public function getPublicToken(): string {
        return $this->publicToken;
    }

    public function isActive(): bool {
        return $this->status;
    }

    public function setId(int $id): PaymentGateway {
        if($id <= 0) {
            throw new \InvalidArgumentException("ID must be a positive integer.");
        }

        $this->id = $id;
        return $this;
    }

    public function setDescription(string $description):PaymentGateway {
        if(empty($description)) {
            throw new \InvalidArgumentException("Description cannot be empty.");
        }

        if(strlen($description) > 50) {
            throw new \InvalidArgumentException("Description cannot exceed 50 characters.");
        }

        $this->description = $description;
        return $this;
    }

    public function setEndpoint(string $endpoint): PaymentGateway {
        if(empty($endpoint)) {
            throw new \InvalidArgumentException("Endpoint cannot be empty.");
        }

        if(strlen($endpoint) > 255) {
            throw new \InvalidArgumentException("Endpoint cannot exceed 255 characters.");
        }

        $this->endpoint = $endpoint;
        return $this;
    }

    public function setPrivateToken(string $privateToken): PaymentGateway {
        if(empty($privateToken)) {
            throw new \InvalidArgumentException("Private token cannot be empty.");
        }

        if(strlen($privateToken) > 100) {
            throw new \InvalidArgumentException("Private token cannot exceed 100 characters.");
        }
        $this->privateToken = $privateToken;
        return $this;
    }

    public function setPublicToken(string $publicToken): PaymentGateway {
        if(empty($publicToken)) {
            throw new \InvalidArgumentException("Public token cannot be empty.");
        }

        if(strlen($publicToken) > 100) {
            throw new \InvalidArgumentException("Public token cannot exceed 100 characters.");
        }

        $this->publicToken = $publicToken;
        return $this;
    }

    public function setStatus(bool $status): PaymentGateway {
        $this->status = $status;
        return $this;
    }
}