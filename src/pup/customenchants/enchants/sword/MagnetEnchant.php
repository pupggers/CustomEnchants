<?php

declare(strict_types=1);

namespace pup\customenchants\enchants\sword;

use pocketmine\entity\Living;
use pocketmine\player\Player;
use pup\customenchants\types\ChanceWeaponEnchant;

class MagnetEnchant extends ChanceWeaponEnchant
{
    protected function canTarget(Living $victim): bool
    {
        return $victim instanceof Player;
    }

    protected function trigger(Player $attacker, Living $victim, int $level): void
    {
        $from = $attacker->getLocation();
        $to = $victim->getLocation();
        $victim->knockBack($from->x - $to->x, $from->z - $to->z, 0.3 + 0.1 * $level);
    }
}
