<?php

declare(strict_types=1);

namespace pup\customenchants\enchants\armor\chestplate;

use pocketmine\entity\Entity;
use pocketmine\entity\Living;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Armor;
use pocketmine\player\Player;
use pup\customenchants\types\DefenseArmorEnchant;

class ShockwaveEnchant extends DefenseArmorEnchant
{
    public function execute(Player $player, ?Entity $damager, Armor $armor, EntityDamageEvent $event, int $level): void
    {

        if ($event->getCause() !== EntityDamageEvent::CAUSE_ENTITY_ATTACK || !$damager instanceof Living || $player->getHealth() > $player->getMaxHealth() * 0.4 || !$this->roll($level)) {
            return;
        }
        $from = $player->getLocation();
        $to = $damager->getLocation();
        $damager->knockBack($to->x - $from->x, $to->z - $from->z, 0.5 + 0.1 * $level);
    }
}
