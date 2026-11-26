<?php

declare(strict_types=1);

namespace pup\customenchants\enchants\sword;

use pocketmine\entity\Living;
use pocketmine\player\Player;
use pup\customenchants\types\ChanceWeaponEnchant;

class ForcefieldEnchant extends ChanceWeaponEnchant
{
    protected function trigger(Player $attacker, Living $victim, int $level): void
    {
        $from = $attacker->getLocation();
        $to = $victim->getLocation();
        $victim->knockBack($to->x - $from->x, $to->z - $from->z, 0.3 + 0.1 * $level);
    }
}
