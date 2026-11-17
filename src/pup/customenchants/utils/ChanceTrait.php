<?php

declare(strict_types=1);

namespace pup\customenchants\utils;

trait ChanceTrait
{
    private float $baseChance = 10.0;

    public function setBaseChance(float $chance): void
    {
        $percent = $chance <= 1.0 ? $chance * 100.0 : $chance;
        $this->baseChance = max(0.0, min(100.0, $percent));
    }

    public function getChance(int $currentLevel, int $maxLevel): float
    {
        $maxLevel = max(1, $maxLevel);
        $currentLevel = max(1, min($currentLevel, $maxLevel));

        if ($currentLevel === $maxLevel) {
            return 100.0;
        }

        $perLevel = (100.0 - $this->baseChance) / ($maxLevel - 1);

        return min(100.0, $this->baseChance + ($currentLevel - 1) * $perLevel);
    }

    public function roll(int $level, float $bonusPercent = 0.0): bool
    {
        $chance = min(100.0, $this->getChance($level, $this->getMaxLevel()) + $bonusPercent);

        return (mt_rand(0, 9999) / 100.0) < $chance;
    }

    public function hasChanceTrait(): bool
    {
        return true;
    }
}