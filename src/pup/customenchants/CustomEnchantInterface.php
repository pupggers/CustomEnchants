<?php

declare(strict_types=1);

namespace pup\customenchants;

interface CustomEnchantInterface
{
    public function getDescription(): string;

    public function setBaseChance(float $chance): void;

    public function setMaxChance(float $chance): void;

    public function getChance(int $currentLevel, int $maxLevel): float;

    public function roll(int $level, float $bonusPercent = 0.0): bool;
}