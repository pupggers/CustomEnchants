<?php

namespace pup\customenchants\utils;

trait ChanceTrait
{
    /** Stored and returned as a percentage between 0 and 100. */
    private float $baseChance = 10.0;

    public function setBaseChance(float $chance): void {
        $this->baseChance = max(0, min(100, $chance <= 1 ? $chance * 100 : $chance));
    }

    public function getChance(int $currentLevel, int $maxLevel): float {
        return $this->calculateChance($currentLevel, $maxLevel, $this->baseChance);
    }

    private function calculateChance($currentLevel, $maxLevel, $baseChance)
    {
        $currentLevel = max(1, min($currentLevel, $maxLevel));

        if ($currentLevel === $maxLevel) {
            return 100;
        }
        $chanceIncreasePerLevel = (100 - $baseChance) / ($maxLevel - 1);
        $adjustedChance = $baseChance + ($currentLevel - 1) * $chanceIncreasePerLevel;

        return min(100, $adjustedChance);
    }

    public function hasChanceTrait(): bool{
        return true;
    }
}
