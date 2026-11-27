<?php

namespace api\Container;

use Psr\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class ContainerException extends \Exception implements ContainerExceptionInterface
{
}

class NotFoundException extends ContainerException implements NotFoundExceptionInterface
{
}

class AppContainer implements ContainerInterface
{
    private array $entries;
    private array $resolved = [];

    public function __construct(array $entries = [])
    {
        $this->entries = $entries;
    }

    public function get($id)
    {
        if (array_key_exists($id, $this->resolved)) {
            return $this->resolved[$id];
        }

        if (!array_key_exists($id, $this->entries)) {
            throw new NotFoundException("Entry '{$id}' not found in container");
        }

        $entry = $this->entries[$id];

        $value = is_callable($entry) ? $entry($this) : $entry;

        $this->resolved[$id] = $value;

        return $value;
    }

    public function has($id): bool
    {
        return array_key_exists($id, $this->entries);
    }
}
