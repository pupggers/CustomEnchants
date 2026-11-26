<?php

declare(strict_types=1);

namespace pup\customenchants\enchants\durability;

use pup\customenchants\types\DurabilityEnchant;

class ReforgedEnchant extends DurabilityEnchant
{
    public function refund(int $level, int $damageTaken): int
    {
        return $this->rollEach($level, $damageTaken);
    }
}
