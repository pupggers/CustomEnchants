<?php

declare(strict_types=1);

namespace pup\customenchants\enchants\sword;

use pocketmine\entity\Living;
use pocketmine\player\Player;
use pup\customenchants\types\ChanceWeaponEnchant;

class AmbitEnchant extends ChanceWeaponEnchant
{
    protected function trigger(Player $attacker, Living $victim, int $level): void
    {
        foreach ($this->nearby($attacker, $victim, 1.5 + 0.5 * $level) as $entity) {
            $this->dealExtraDamage($attacker, $entity, 1.0 + 0.5 * $level);
        }
    }
}
