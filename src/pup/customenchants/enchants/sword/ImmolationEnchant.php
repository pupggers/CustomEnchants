<?php

declare(strict_types=1);

namespace pup\customenchants\enchants\sword;

use pocketmine\entity\Living;
use pocketmine\player\Player;
use pup\customenchants\types\ChanceWeaponEnchant;

class ImmolationEnchant extends ChanceWeaponEnchant
{
    protected function trigger(Player $attacker, Living $victim, int $level): void
    {
        foreach ($this->nearby($attacker, $victim, 2.0 + $level) as $entity) {
            $entity->setOnFire(2 + $level);
        }
    }
}
