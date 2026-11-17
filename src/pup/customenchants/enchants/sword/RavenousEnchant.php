<?php

declare(strict_types=1);

namespace pup\customenchants\enchants\sword;

use pocketmine\entity\Living;
use pocketmine\player\Player;
use pup\customenchants\types\ChanceWeaponEnchant;

class RavenousEnchant extends ChanceWeaponEnchant
{
    protected function trigger(Player $attacker, Living $victim, int $level): void
    {
        $attacker->getHungerManager()->addFood((float) $level);
    }
}
