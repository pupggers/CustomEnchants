<?php

declare(strict_types=1);

namespace pup\customenchants\enchants\armor;

use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Armor;
use pocketmine\player\Player;
use pup\customenchants\types\DefenseArmorEnchant;

class DodgeEnchant extends DefenseArmorEnchant
{
    public function execute(Player $player, ?Entity $damager, Armor $armor, EntityDamageEvent $event, int $level): void
    {

        if ($event->getCause() !== EntityDamageEvent::CAUSE_ENTITY_ATTACK) {
            return;
        }
        if ($this->roll($level, $player->isSneaking() ? 5 : 0)) {
            $event->cancel();
        }
    }
}
