<?php

class CaptchaDTO
{
    public ?int $id = null;
    public ?string $descriptionApi = null;
    public ?string $publicKey = null;
    public ?string $privateKey = null;

    public function __construct(?array $properities = null)
    {
        if (isset($properities) && !empty($properities))
            $this->make($properities);
    }

    private function make(array $properties): void
    {
        if (array_key_exists('id', $properties))
            $this->id = (int) $properties['id'];

        if (array_key_exists('descriptionApi', $properties))
            $this->descriptionApi = (string) $properties['descriptionApi'];
        elseif (array_key_exists('description_api', $properties))
            $this->descriptionApi = (string) $properties['description_api'];

        if (array_key_exists('publicKey', $properties))
            $this->publicKey = (string) $properties['publicKey'];
        elseif (array_key_exists('public_key', $properties))
            $this->publicKey = (string) $properties['public_key'];

        if (array_key_exists('privateKey', $properties))
            $this->privateKey = (string) $properties['privateKey'];
        elseif (array_key_exists('private_key', $properties))
            $this->privateKey = (string) $properties['private_key'];
    }
}
