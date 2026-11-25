<?php

namespace api\modules\Socio;

class SocioConfigurationService
{
    public const VIDEO_DEMONSTRATION = 'video_demonstration';

    private SocioConfigurationRepository $repository;

    public function __construct(SocioConfigurationRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAll(): array
    {
        return $this->repository->findAll();
    }

    public function getVideoDemonstration(): ?array
    {
        return $this->repository->findByType(self::VIDEO_DEMONSTRATION);
    }

    public function create(string $type, string $value): array
    {
        $this->validateConfiguration($type, $value);

        return $this->repository->create($type, $value);
    }

    public function update(string $type, string $value): ?array
    {
        $this->validateConfiguration($type, $value);

        if ($this->repository->findByType($type) === null) {
            return null;
        }

        if (!$this->repository->update($type, $value)) {
            throw new \RuntimeException('Não foi possível atualizar a configuração.');
        }

        return $this->repository->findByType($type);
    }

    public function delete(string $type): bool
    {
        $this->validateType($type);

        return $this->repository->delete($type);
    }

    private function validateConfiguration(string $type, string $value): void
    {
        $this->validateType($type);

        if (strlen($value) > 512 || filter_var($value, FILTER_VALIDATE_URL) === false) {
            throw new \InvalidArgumentException('O valor deve ser uma URL válida de até 512 caracteres.');
        }

        $scheme = strtolower((string)parse_url($value, PHP_URL_SCHEME));
        if (!in_array($scheme, ['http', 'https'], true)) {
            throw new \InvalidArgumentException('A URL deve utilizar o protocolo HTTP ou HTTPS.');
        }
    }

    private function validateType(string $type): void
    {
        if ($type !== self::VIDEO_DEMONSTRATION) {
            throw new \InvalidArgumentException('Tipo de configuração inválido.');
        }
    }
}
