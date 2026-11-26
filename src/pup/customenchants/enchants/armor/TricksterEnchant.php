<?php

declare(strict_types=1);

namespace pup\customenchants\enchants\armor;

use pocketmine\entity\Entity;
use pocketmine\entity\Living;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Armor;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pup\customenchants\types\DefenseArmorEnchant;

class TricksterEnchant extends DefenseArmorEnchant
{
    public function execute(Player $player, ?Entity $damager, Armor $armor, EntityDamageEvent $event, int $level): void
    {

        if ($event->getCause() !== EntityDamageEvent::CAUSE_ENTITY_ATTACK || !$damager instanceof Living || !$this->roll($level)) {
            return;
        }
        $world = $player->getWorld();
        if ($damager->getWorld() !== $world) {
            return;
        }
        $location = $damager->getLocation();
        $direction = $damager->getDirectionVector();
        $target = new Vector3($location->x - $direction->x * 1.5, $location->y, $location->z - $direction->z * 1.5);
        if ($world->getBlock($target)->isSolid() || $world->getBlock($target->add(0, 1, 0))->isSolid()) {
            return;
        }
        $player->teleport($target, $location->getYaw(), 0.0);
    }
}
