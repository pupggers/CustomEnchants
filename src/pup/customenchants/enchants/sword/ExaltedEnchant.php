<?php

declare(strict_types=1);

namespace pup\customenchants\enchants\sword;

use pocketmine\entity\Living;
use pocketmine\player\Player;
use pup\customenchants\types\ChanceWeaponEnchant;

class ExaltedEnchant extends ChanceWeaponEnchant
{
    protected function trigger(Player $attacker, Living $victim, int $level): void
    {
        $effects = $attacker->getEffects();
        foreach ($effects->all() as $instance) {
            if ($instance->getType()->isBad()) {
                $effects->remove($instance->getType());
            }
        }
    }
}
