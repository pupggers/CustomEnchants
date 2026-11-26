<?php

declare(strict_types=1);

namespace pup\customenchants\enchants\armor\boots;

use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Armor;
use pocketmine\player\Player;
use pup\customenchants\types\DefenseArmorEnchant;

class JellyLegsEnchant extends DefenseArmorEnchant
{
    public function execute(Player $player, ?Entity $damager, Armor $armor, EntityDamageEvent $event, int $level): void
    {

        if ($event->getCause() === EntityDamageEvent::CAUSE_FALL && $this->roll($level)) {
            $event->cancel();
        }
    }
}
