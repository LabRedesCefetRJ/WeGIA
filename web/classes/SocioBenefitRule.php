<?php

class SocioBenefitRule implements JsonSerializable
{
    private ?int $id;
    private float $valuePerPoint;
    private int $maxPointsConcurrent;
    private int $durationPointMonths;
    private int $analysisWindowMonths;
    private bool $active;

    public function __construct(float $valuePerPoint, int $maxPointsConcurrent, int $durationPointMonths, int $analysisWindowMonths, bool $active, ?int $id = null)
    {
        $this->valuePerPoint = $valuePerPoint;
        $this->maxPointsConcurrent = $maxPointsConcurrent;
        $this->durationPointMonths = $durationPointMonths;
        $this->analysisWindowMonths = $analysisWindowMonths;
        $this->active = $active;
        $this->id = $id;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'valuePerPoint' => $this->valuePerPoint,
            'maxPointsConcurrent' => $this->maxPointsConcurrent,
            'durationPointMonths' => $this->durationPointMonths,
            'analysisWindowMonths' => $this->analysisWindowMonths,
            'active' => $this->active
        ];
    }

    // Getters e setters para cada propriedade
    public function getId(): int|null
    {
        return $this->id;
    }

    public function getValuePerPoint(): float
    {
        return $this->valuePerPoint;
    }

    public function getMaxPointsConcurrent(): int
    {
        return $this->maxPointsConcurrent;
    }

    public function getDurationPointMonths(): int
    {
        return $this->durationPointMonths;
    }

    public function getAnalysisWindowMonths(): int
    {
        return $this->analysisWindowMonths;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setValuePerPoint(float $valuePerPoint): void
    {
        if ($valuePerPoint <= 0) {
            throw new InvalidArgumentException('O valor por ponto deve ser maior que zero.', 400);
        }

        $this->valuePerPoint = $valuePerPoint;
    }

    public function setMaxPointsConcurrent(int $maxPointsConcurrent): void
    {
        if ($maxPointsConcurrent < 1) {
            throw new InvalidArgumentException('O número máximo de pontos simultâneos não pode ser menor que 1.', 400);
        }

        $this->maxPointsConcurrent = $maxPointsConcurrent;
    }

    public function setDurationPointMonths(int $durationPointMonths): void
    {
        if ($durationPointMonths < 1) {
            throw new InvalidArgumentException('A duração dos pontos em meses deve ser maior que zero.', 400);
        }

        $this->durationPointMonths = $durationPointMonths;
    }

    public function setAnalysisWindowMonths(int $analysisWindowMonths): void
    {
        if ($analysisWindowMonths < 1) {
            throw new InvalidArgumentException('A janela de análise em meses deve ser maior que zero.', 400);
        }

        $this->analysisWindowMonths = $analysisWindowMonths;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }
}