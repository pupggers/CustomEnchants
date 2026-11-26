<?php

declare(strict_types=1);

namespace pup\customenchants\enchants\armor;

use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Armor;
use pocketmine\item\Axe;
use pocketmine\player\Player;
use pup\customenchants\types\DefenseArmorEnchant;

class TankEnchant extends DefenseArmorEnchant
{
    public function execute(Player $player, ?Entity $damager, Armor $armor, EntityDamageEvent $event, int $level): void
    {

        if ($event->getCause() !== EntityDamageEvent::CAUSE_ENTITY_ATTACK
            || !$damager instanceof Player
            || !$damager->getInventory()->getItemInHand() instanceof Axe
            || !$this->roll($level)) {
            return;
        }
        $this->reduceDamage($event, 0.02 * $level);
    }
}
