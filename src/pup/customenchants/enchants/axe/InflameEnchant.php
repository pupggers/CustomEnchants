<?php

declare(strict_types=1);

namespace pup\customenchants\enchants\axe;

use pocketmine\entity\Living;
use pocketmine\player\Player;
use pup\customenchants\types\ChanceWeaponEnchant;

class InflameEnchant extends ChanceWeaponEnchant
{
    protected function trigger(Player $attacker, Living $victim, int $level): void
    {
        foreach ($this->nearby($attacker, $victim, 2.0 + $level, true) as $entity) {
            $entity->setOnFire(2 + $level);
        }
    }
}
