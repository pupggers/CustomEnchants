<?php

declare(strict_types=1);

namespace pup\customenchants\enchants\sword;

use pocketmine\entity\Living;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\player\Player;
use pup\customenchants\types\ChanceWeaponEnchant;

class ReboundEnchant extends ChanceWeaponEnchant
{
    protected function requiresRoll(): bool
    {
        return false;
    }

    protected function trigger(Player $attacker, Living $victim, int $level): void
    {
        if ($victim->isAlive()) {
            return;
        }
        $attacker->heal(new EntityRegainHealthEvent($attacker, $level * 2.0, EntityRegainHealthEvent::CAUSE_CUSTOM));
    }
}
