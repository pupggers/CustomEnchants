<?php

declare(strict_types=1);

namespace pup\customenchants\enchants\armor\boots;

use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Armor;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pup\customenchants\types\DefenseArmorEnchant;

class ConvulseEnchant extends DefenseArmorEnchant
{
    public function execute(Player $player, ?Entity $damager, Armor $armor, EntityDamageEvent $event, int $level): void
    {

        if ($event->getCause() !== EntityDamageEvent::CAUSE_ENTITY_ATTACK || $damager === null || !$this->roll($level)) {
            return;
        }
        $damager->setMotion(new Vector3(0.0, 0.45 + 0.08 * $level, 0.0));
    }
}
