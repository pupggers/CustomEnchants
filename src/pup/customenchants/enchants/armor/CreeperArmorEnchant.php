<?php

declare(strict_types=1);

namespace pup\customenchants\enchants\armor;

use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\item\Armor;
use pocketmine\player\Player;
use pup\customenchants\types\DefenseArmorEnchant;

class CreeperArmorEnchant extends DefenseArmorEnchant
{
    public function execute(Player $player, ?Entity $damager, Armor $armor, EntityDamageEvent $event, int $level): void
    {

        $cause = $event->getCause();
        if ($cause !== EntityDamageEvent::CAUSE_BLOCK_EXPLOSION && $cause !== EntityDamageEvent::CAUSE_ENTITY_EXPLOSION) {
            return;
        }
        if (!$this->roll($level)) {
            return;
        }
        $event->cancel();
        if ($level >= 2) {
            $player->heal(new EntityRegainHealthEvent($player, 2.0, EntityRegainHealthEvent::CAUSE_CUSTOM));
        }
    }
}
