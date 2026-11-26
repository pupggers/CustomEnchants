<?php

declare(strict_types=1);

namespace pup\customenchants\enchants\sword;

use pocketmine\entity\Living;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\player\Player;
use pup\customenchants\types\ChanceWeaponEnchant;
use pup\customenchants\utils\EnchantTasks;

class VampireEnchant extends ChanceWeaponEnchant
{
    protected function trigger(Player $attacker, Living $victim, int $level): void
    {
        EnchantTasks::later(60, static function () use ($attacker, $level): void {
            if (!$attacker->isConnected() || !$attacker->isAlive()) {
                return;
            }
            $attacker->heal(new EntityRegainHealthEvent($attacker, min(3.0, 1.0 + $level), EntityRegainHealthEvent::CAUSE_CUSTOM));
        });
    }
}
