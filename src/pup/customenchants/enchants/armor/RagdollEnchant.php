<?php

declare(strict_types=1);

namespace pup\customenchants\enchants\armor;

use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Armor;
use pocketmine\player\Player;
use pup\customenchants\types\DefenseArmorEnchant;

class RagdollEnchant extends DefenseArmorEnchant
{
    public function execute(Player $player, ?Entity $damager, Armor $armor, EntityDamageEvent $event, int $level): void
    {

        if ($event->getCause() !== EntityDamageEvent::CAUSE_ENTITY_ATTACK || $damager === null || !$this->roll($level)) {
            return;
        }
        $from = $damager->getLocation();
        $self = $player->getLocation();
        $player->knockBack($self->x - $from->x, $self->z - $from->z, 0.3 + 0.1 * $level);
    }
}
