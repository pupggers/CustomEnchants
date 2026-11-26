<?php

declare(strict_types=1);

namespace pup\customenchants\enchants\sword;

use pocketmine\entity\Living;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\player\Player;
use pup\customenchants\types\ChanceWeaponEnchant;

class LifestealEnchant extends ChanceWeaponEnchant
{
    protected function trigger(Player $attacker, Living $victim, int $level): void
    {
        $attacker->heal(new EntityRegainHealthEvent($attacker, (float) $level, EntityRegainHealthEvent::CAUSE_CUSTOM));
    }
}
