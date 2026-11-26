<?php

declare(strict_types=1);

namespace pup\customenchants\enchants\durability;

use pup\customenchants\types\DurabilityEnchant;

class UnbreakableEnchant extends DurabilityEnchant
{
    public function refund(int $level, int $damageTaken): int
    {
        return $damageTaken;
    }

    public function makesUnbreakable(): bool
    {
        return true;
    }
}
