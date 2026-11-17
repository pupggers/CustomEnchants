<?php

declare(strict_types=1);

namespace pup\customenchants\enchants\armor\chestplate;

use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Armor;
use pocketmine\player\Player;
use pup\customenchants\types\DefenseArmorEnchant;

class ChunkyEnchant extends DefenseArmorEnchant
{
    public function execute(Player $player, ?Entity $damager, Armor $armor, EntityDamageEvent $event, int $level): void
    {

        $cause = $event->getCause();
        if (($cause === EntityDamageEvent::CAUSE_ENTITY_ATTACK || $cause === EntityDamageEvent::CAUSE_PROJECTILE) && $this->roll($level)) {
            $this->reduceDamage($event, 0.10 + 0.03 * $level);
        }
    }
}
