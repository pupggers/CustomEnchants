<?php

declare(strict_types=1);

namespace pup\customenchants\enchants\axe;

use pocketmine\entity\Living;
use pocketmine\player\Player;
use pup\customenchants\types\ChanceWeaponEnchant;

class BarbarianEnchant extends ChanceWeaponEnchant
{
    protected function trigger(Player $attacker, Living $victim, int $level): void
    {
        $this->dealExtraDamage($attacker, $victim, $level * 1.5);
    }
}
