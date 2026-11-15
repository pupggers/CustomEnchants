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
		return $this->calculateChance($currentLevel, $maxLevel, $this->baseChance);
	}

	private function calculateChance(int $currentLevel, int $maxLevel, float $baseChance): float
	{
		$maxLevel = max(1, $maxLevel);
		$currentLevel = max(1, min($currentLevel, $maxLevel));

		if ($currentLevel === $maxLevel) {
			return 100.0;
		}

		$increasePerLevel = (100.0 - $baseChance) / ($maxLevel - 1);

		return min(100.0, $baseChance + ($currentLevel - 1) * $increasePerLevel);
	}

	public function hasChanceTrait(): bool
	{
		return true;
	}
}