<?php

declare(strict_types=1);

namespace pup\customenchants\enchants\sword;

use pocketmine\entity\Living;
use pocketmine\player\Player;
use pup\customenchants\types\ChanceWeaponEnchant;

class CriticalEnchant extends ChanceWeaponEnchant
{
    protected function requiresRoll(): bool
    {
        return false;
    }

    protected function trigger(Player $attacker, Living $victim, int $level): void
    {
        if ($attacker->fallDistance > 0.0 && !$attacker->isOnGround()) {
            $this->dealExtraDamage($attacker, $victim, $level * 1.5);
        }
    }
}
