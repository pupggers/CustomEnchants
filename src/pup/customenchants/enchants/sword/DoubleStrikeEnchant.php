<?php

declare(strict_types=1);

namespace pup\customenchants\enchants\sword;

use pocketmine\entity\Living;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\player\Player;
use pup\customenchants\types\ChanceWeaponEnchant;

class DoubleStrikeEnchant extends ChanceWeaponEnchant
{
    protected function trigger(Player $attacker, Living $victim, int $level): void
    {
        if (!$victim->isAlive()) {
            return;
        }
        $points = $attacker->getInventory()->getItemInHand()->getAttackPoints();
        $victim->attack(new EntityDamageByEntityEvent($attacker, $victim, EntityDamageEvent::CAUSE_ENTITY_ATTACK, (float) $points));
    }
}
