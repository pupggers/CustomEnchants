<?php

declare(strict_types=1);

namespace pup\customenchants\enchants\armor;

use pocketmine\entity\Entity;
use pocketmine\entity\Living;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Armor;
use pocketmine\player\Player;
use pup\customenchants\types\DefenseArmorEnchant;

class ReflectEnchant extends DefenseArmorEnchant
{
    public function execute(Player $player, ?Entity $damager, Armor $armor, EntityDamageEvent $event, int $level): void
    {

        $cause = $event->getCause();
        if ($cause !== EntityDamageEvent::CAUSE_ENTITY_ATTACK && $cause !== EntityDamageEvent::CAUSE_PROJECTILE) {
            return;
        }
        if (!$damager instanceof Living || !$this->roll($level)) {
            return;
        }
        $fraction = min(0.5, 0.1 * $level);
        $amount = $event->getFinalDamage() * $fraction;
        $this->reduceDamage($event, $fraction);
        $damager->attack(new EntityDamageByEntityEvent($player, $damager, EntityDamageEvent::CAUSE_CUSTOM, $amount));
    }
}
